<?php
/**
 * ApiController Class
 * Provides REST endpoints for Saving pages, Media Uploads, AI Site Creation,
 * Form Enquiry logs, Payment validation, GitHub deploys, and ZIP exports.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AIController.php';
require_once __DIR__ . '/BuilderController.php';

class ApiController extends Controller {

    /**
     * AJAX endpoint to save page layout and section content
     */
    public function savePage() {
        Auth::requireLogin();
        $user = Auth::user();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            return $this->json(false, "Invalid JSON input.");
        }

        $pageId = (int)($input['page_id'] ?? 0);
        $sections = $input['sections'] ?? [];

        // Verify page ownership
        $page = Database::query(
            "SELECT p.* FROM pages p 
             JOIN websites w ON p.website_id = w.id 
             WHERE p.id = ? AND w.user_id = ? LIMIT 1",
            [$pageId, $user['id']]
        )->fetch();

        if (!$page) {
            return $this->json(false, "Page not found or access denied.", [], 403);
        }

        Database::beginTransaction();
        try {
            // Delete existing sections to refresh the block list
            Database::query("DELETE FROM sections WHERE page_id = ?", [$pageId]);
            
            // Insert updated blocks
            $sortOrder = 0;
            foreach ($sections as $sect) {
                $type = $sect['type'] ?? 'text';
                $content = $sect['content'] ?? [];
                
                Database::query(
                    "INSERT INTO sections (page_id, type, sort_order, content_json) VALUES (?, ?, ?, ?)",
                    [$pageId, $type, $sortOrder++, json_encode($content)]
                );
            }
            
            Database::commit();
            return $this->json(true, "Page layout saved successfully!");
        } catch (Exception $e) {
            Database::rollBack();
            return $this->json(false, "Failed saving page: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Upload Media file
     */
    public function uploadMedia() {
        Auth::requireLogin();
        $user = Auth::user();

        if (empty($_FILES['file'])) {
            return $this->json(false, "No file uploaded.");
        }

        $file = $_FILES['file'];
        $filesize = (int)$file['size'];
        $filetype = $file['type'];

        // Size check
        if ($filesize > MAX_UPLOAD_SIZE) {
            return $this->json(false, "File exceeds maximum size limits.");
        }

        // Type check
        if (!in_array($filetype, ALLOWED_UPLOAD_TYPES)) {
            return $this->json(false, "Unsupported file format.");
        }

        // Subscription Storage limit check
        if (!Subscription::canUpload($user['id'], $filesize)) {
            return $this->json(false, "Storage limit reached. Please upgrade your subscription plan.");
        }

        // Upload path setup
        $uploadDir = __DIR__ . '/../uploads/media/' . $user['id'];
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', basename($file['name']));
        $filepath = '/uploads/media/' . $user['id'] . '/' . $filename;
        $absolutePath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
            // Save in DB
            Database::query(
                "INSERT INTO media (user_id, filename, filepath, filetype, filesize, folder) VALUES (?, ?, ?, ?, ?, 'root')",
                [$user['id'], $file['name'], $filepath, $filetype, $filesize]
            );
            $mediaId = Database::lastInsertId();

            return $this->json(true, "File uploaded successfully!", [
                'id' => $mediaId,
                'url' => APP_URL . $filepath,
                'name' => $file['name']
            ]);
        }

        return $this->json(false, "File transfer failed.");
    }

    /**
     * AI Generation request endpoint
     */
    public function generateAI() {
        Auth::requireLogin();
        $user = Auth::user();

        // Limit Check
        if (!Subscription::canCreateWebsite($user['id'])) {
            return $this->json(false, "Website limits reached. Upgrade your subscription plan.");
        }

        $params = sanitize_input($_POST);
        
        if (empty($params['business_name']) || empty($params['category']) || empty($params['description'])) {
            return $this->json(false, "Business name, Category, and Description details are required.");
        }

        try {
            $websiteId = AIController::generateWebsite($user['id'], $params);
            return $this->json(true, "AI Website generated successfully!", [
                'website_id' => $websiteId
            ]);
        } catch (Exception $e) {
            return $this->json(false, "AI Generator failed: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Receives public Contact Form enquiries from rendered templates
     */
    public function submitForm() {
        $formId = (int)($_POST['form_id'] ?? 0);
        
        $form = Database::query("SELECT * FROM forms WHERE id = ? LIMIT 1", [$formId])->fetch();
        if (!$form) {
            return $this->json(false, "Destination form channel not found.");
        }

        // Filter and package submitted payload
        $fields = $_POST;
        unset($fields['form_id']);
        $sanitizedData = sanitize_input($fields);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        Database::query(
            "INSERT INTO enquiries (form_id, form_data, ip_address) VALUES (?, ?, ?)",
            [$formId, json_encode($sanitizedData), $ip]
        );

        // Send Notification Mail if configured
        if (!empty($form['email_recipient'])) {
            $body = "<h2>New Form Submission</h2><ul>";
            foreach ($sanitizedData as $key => $val) {
                $body .= "<li><strong>" . ucfirst($key) . ":</strong> $val</li>";
            }
            $body .= "</ul>";
            send_mail($form['email_recipient'], "New Enquiry: " . $form['name'], $body);
        }

        return $this->json(true, "Your enquiry has been logged successfully!");
    }

    /**
     * Processes Mock Payment Checkout
     */
    public function checkout() {
        Auth::requireLogin();
        $user = Auth::user();

        $planId = (int)($_POST['plan_id'] ?? 0);
        $cycle = $_POST['billing_cycle'] ?? 'monthly';
        $gateway = $_POST['gateway'] ?? 'stripe'; // stripe, paypal, razorpay

        $plan = Database::query("SELECT * FROM plans WHERE id = ?", [$planId])->fetch();
        if (!$plan) {
            return $this->json(false, "Invalid subscription plan.");
        }

        $amount = ($cycle === 'yearly') ? $plan['price_yearly'] : $plan['price_monthly'];
        $txId = strtoupper($gateway[0]) . "TXN" . time() . rand(100, 999);

        try {
            Subscription::upgrade($user['id'], $planId, $cycle, $gateway, $txId, $amount);
            return $this->json(true, "Checkout completed successfully!", [
                'transaction_id' => $txId,
                'amount' => $amount
            ]);
        } catch (Exception $e) {
            return $this->json(false, "Billing setup failed: " . $e->getMessage(), [], 500);
        }
    }

    /**
     * Zip Exporter: Combines page dynamic loops into static HTML/CSS files
     */
    public function exportZip() {
        Auth::requireLogin();
        $user = Auth::user();

        $websiteId = (int)($_GET['id'] ?? 0);
        $website = Website::find($websiteId, $user['id']);

        if (!$website) {
            die("Access Denied.");
        }

        // Initialize Zip
        $zip = new ZipArchive();
        $zipName = 'export_' . $website['subdomain'] . '_' . time() . '.zip';
        $zipFile = sys_get_temp_dir() . '/' . $zipName;

        if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
            die("Could not create ZIP archive on server.");
        }

        // Fetch pages
        $pages = Database::query("SELECT * FROM pages WHERE website_id = ?", [$websiteId])->fetchAll();
        
        // Compile each page
        foreach ($pages as $p) {
            // Fetch sections
            $sections = Database::query(
                "SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order ASC",
                [$p['id']]
            )->fetchAll();

            foreach ($sections as &$sect) {
                $sect['content'] = json_decode($sect['content_json'] ?? '{}', true) ?: [];
            }

            // Capture HTML Output
            ob_start();
            
            // Build simple self-contained html template
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?= e($p['meta_title'] ?: $p['title']) ?></title>
                <meta name="description" content="<?= e($p['meta_description']) ?>">
                <meta name="keywords" content="<?= e($p['meta_keywords']) ?>">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
                <style>
                    :root {
                        --primary-color: <?= $website['primary_color'] ?>;
                        --secondary-color: <?= $website['secondary_color'] ?>;
                    }
                    body {
                        font-family: '<?= $website['font_family'] ?>', sans-serif;
                    }
                    .bg-primary { background-color: var(--primary-color) !important; }
                    .text-primary { color: var(--primary-color) !important; }
                    .btn-primary { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
                </style>
            </head>
            <body>
            <?php
            // Output sections
            foreach ($sections as $sect) {
                // Renders custom blocks (normally fetched from components engine)
                $this->renderBlockStatic($sect['type'], $sect['content']);
            }
            ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            <?php
            $html = ob_get_clean();
            
            // Map home to index.html, else slug.html
            $fileName = ($p['is_homepage'] == 1) ? 'index.html' : $p['slug'] . '.html';
            
            // Simple URL mapping replacements (subdomain site links to static html pages)
            // Replace e.g. "/site/subdomain/about" to "about.html"
            $html = str_replace([
                '/site/' . $website['subdomain'] . '/home',
                '/site/' . $website['subdomain'] . '/about',
                '/site/' . $website['subdomain'] . '/contact',
                '/site/' . $website['subdomain']
            ], [
                'index.html',
                'about.html',
                'contact.html',
                'index.html'
            ], $html);

            $zip->addFromString($fileName, $html);
        }

        $zip->close();

        // Save export transaction to Activity logs
        log_activity($user['id'], 'export_zip', "Downloaded ZIP package of site '" . $website['name'] . "'");

        // Download output to browser
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $website['subdomain'] . '.zip');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
        exit;
    }

    /**
     * Mock GitHub Deploy push logic
     */
    public function pushToGithub() {
        Auth::requireLogin();
        $user = Auth::user();

        $websiteId = (int)($_POST['website_id'] ?? 0);
        $repoName = trim($_POST['repo_name'] ?? '');
        $token = trim($_POST['github_token'] ?? '');

        if (empty($repoName) || empty($token)) {
            return $this->json(false, "Repository name and OAuth token are required.");
        }

        // Log action and return mock success
        log_activity($user['id'], 'github_deploy', "Deployed website ID $websiteId to Github repository '$repoName'");
        return $this->json(true, "Deployment successful! Commited static contents to repository '$repoName' main branch.");
    }

    /**
     * Renders simplified blocks for the Zip static exports
     */
    private function renderBlockStatic(string $type, array $content) {
        $brand = e($content['brand'] ?? 'Company');
        $title = e($content['title'] ?? 'Section Title');
        $subtitle = e($content['subtitle'] ?? 'Section Subtitle');
        
        switch ($type) {
            case 'navbar':
                echo '<nav class="navbar navbar-expand-lg navbar-light bg-light py-3"><div class="container">';
                echo '<a class="navbar-brand fw-bold" href="#">' . $brand . '</a>';
                echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navStatic"><span class="navbar-toggler-icon"></span></button>';
                echo '<div class="collapse navbar-collapse" id="navStatic"><ul class="navbar-nav ms-auto mb-2 mb-lg-0">';
                if (!empty($content['links'])) {
                    foreach ($content['links'] as $lnk) {
                        echo '<li class="nav-item"><a class="nav-link" href="' . e($lnk['url']) . '">' . e($lnk['text']) . '</a></li>';
                    }
                }
                echo '</ul>';
                if (!empty($content['btn_text'])) {
                    echo '<a class="btn btn-primary ms-3" href="' . e($content['btn_url'] ?? '#') . '">' . e($content['btn_text']) . '</a>';
                }
                echo '</div></div></nav>';
                break;

            case 'hero':
                $bg = e($content['bg_color'] ?? '#0d6efd');
                echo '<section class="py-5 text-white" style="background: ' . $bg . ';"><div class="container py-5 text-center">';
                echo '<h1 class="display-3 fw-bold mb-3">' . $title . '</h1>';
                echo '<p class="lead mb-4">' . $subtitle . '</p>';
                if (!empty($content['btn_primary'])) {
                    echo '<a class="btn btn-light btn-lg me-3 px-4" href="#">' . e($content['btn_primary']) . '</a>';
                }
                if (!empty($content['btn_secondary'])) {
                    echo '<a class="btn btn-outline-light btn-lg px-4" href="#">' . e($content['btn_secondary']) . '</a>';
                }
                echo '</div></section>';
                break;

            case 'about':
                $desc = e($content['desc'] ?? '');
                echo '<section id="about" class="py-5 bg-white"><div class="container py-5"><div class="row align-items-center">';
                echo '<div class="col-lg-6"><h2 class="fw-bold mb-4">' . $title . '</h2><p class="text-muted lead">' . nl2br($desc) . '</p></div>';
                echo '<div class="col-lg-6 text-center"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" class="img-fluid rounded shadow" alt="About"></div>';
                echo '</div></div></section>';
                break;

            case 'services':
                echo '<section id="services" class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
                if (!empty($content['items'])) {
                    foreach ($content['items'] as $item) {
                        echo '<div class="col-md-4 mb-4"><div class="card h-100 border-0 shadow-sm p-4 text-center">';
                        echo '<div class="text-primary mb-3"><i class="bi ' . e($item['icon']) . ' display-5"></i></div>';
                        echo '<h4 class="fw-bold">' . e($item['title']) . '</h4>';
                        echo '<p class="text-muted">' . e($item['desc']) . '</p>';
                        echo '</div></div>';
                    }
                }
                echo '</div></div></section>';
                break;

            case 'features':
                echo '<section class="py-5 bg-white"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
                if (!empty($content['items'])) {
                    foreach ($content['items'] as $item) {
                        echo '<div class="col-md-6 mb-4 d-flex"><div class="me-3 text-primary"><i class="bi ' . e($item['icon']) . ' display-6"></i></div>';
                        echo '<div><h4 class="fw-bold">' . e($item['title']) . '</h4><p class="text-muted">' . e($item['desc']) . '</p></div></div>';
                    }
                }
                echo '</div></div></section>';
                break;

            case 'testimonials':
                echo '<section id="testimonials" class="py-5 bg-light"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row">';
                if (!empty($content['items'])) {
                    foreach ($content['items'] as $item) {
                        echo '<div class="col-md-6 mb-4"><div class="card border-0 shadow-sm p-4 h-100">';
                        echo '<p class="fst-italic text-muted mb-4">"' . e($item['quote']) . '"</p>';
                        echo '<h5 class="fw-bold mb-0">- ' . e($item['client']) . '</h5>';
                        echo '</div></div>';
                    }
                }
                echo '</div></div></section>';
                break;

            case 'faq':
                echo '<section class="py-5 bg-white"><div class="container py-5"><h2 class="text-center fw-bold mb-5">' . $title . '</h2><div class="row justify-content-center"><div class="col-lg-8">';
                if (!empty($content['items'])) {
                    $i = 0;
                    foreach ($content['items'] as $item) {
                        $id = "collapse" . $i++;
                        echo '<div class="border-bottom py-3">';
                        echo '<h4 class="fw-bold mb-2">' . e($item['q']) . '</h4>';
                        echo '<p class="text-muted">' . e($item['a']) . '</p>';
                        echo '</div>';
                    }
                }
                echo '</div></div></div></section>';
                break;

            case 'contact':
                echo '<section id="contact" class="py-5 bg-light"><div class="container py-5"><div class="row">';
                echo '<div class="col-md-6 mb-4"><h2 class="fw-bold mb-4">' . $title . '</h2>';
                echo '<p><i class="bi bi-geo-alt-fill text-primary me-2"></i> ' . e($content['address']) . '</p>';
                echo '<p><i class="bi bi-telephone-fill text-primary me-2"></i> ' . e($content['phone']) . '</p>';
                echo '<p><i class="bi bi-envelope-fill text-primary me-2"></i> ' . e($content['email']) . '</p></div>';
                echo '<div class="col-md-6"><div class="card p-4 border-0 shadow-sm"><form>';
                echo '<div class="mb-3"><input type="text" class="form-control" placeholder="Your Name" required></div>';
                echo '<div class="mb-3"><input type="email" class="form-control" placeholder="Your Email" required></div>';
                echo '<div class="mb-3"><textarea class="form-control" rows="4" placeholder="Your Message" required></textarea></div>';
                echo '<button type="submit" class="btn btn-primary w-100">Send Enquiry</button>';
                echo '</form></div></div>';
                echo '</div></div></section>';
                break;

            case 'footer':
                $copy = e($content['copyright'] ?? 'All rights reserved.');
                echo '<footer class="bg-dark text-white py-4 text-center"><div class="container"><p class="mb-0">' . $copy . '</p></div></footer>';
                break;
        }
    }

    /**
     * AJAX endpoint to fetch homepage SEO settings
     */
    public function getSeo() {
        Auth::requireLogin();
        $user = Auth::user();

        $websiteId = (int)($_GET['website_id'] ?? 0);
        
        // Verify ownership
        $website = Website::find($websiteId, $user['id']);
        if (!$website) {
            return $this->json(false, "Website not found or access denied.", [], 403);
        }

        // Fetch homepage page details
        $page = Database::query("SELECT meta_title, meta_description, meta_keywords FROM pages WHERE website_id = ? AND is_homepage = 1 LIMIT 1", [$websiteId])->fetch();
        if ($page) {
            return $this->json(true, "SEO settings loaded.", $page);
        }
        
        return $this->json(false, "Homepage page entry not found.");
    }
}
