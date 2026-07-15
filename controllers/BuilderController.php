<?php
/**
 * BuilderController Class
 * Renders the interactive editor canvas, preview screen, and handles dynamic hosting / rendering.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../config/database.php';

class BuilderController extends Controller {

    /**
     * Launch the interactive Drag-and-Drop Website Builder
     */
    public function editor() {
        Auth::requireLogin();
        $user = Auth::user();
        
        $websiteId = (int)($_GET['id'] ?? 0);
        $website = Website::find($websiteId, $user['id']);
        
        if (!$website) {
            die("Website not found or access denied.");
        }

        // Fetch pages of the website
        $pages = Database::query("SELECT * FROM pages WHERE website_id = ?", [$websiteId])->fetchAll();
        $pageId = (int)($_GET['page_id'] ?? ($pages[0]['id'] ?? 0));
        
        // Fetch current active page details
        $currentPage = Database::query("SELECT * FROM pages WHERE id = ? AND website_id = ?", [$pageId, $websiteId])->fetch();
        if (!$currentPage) {
            die("Page not found.");
        }

        // Fetch sections for the active page
        $sections = Database::query(
            "SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order ASC",
            [$pageId]
        )->fetchAll();

        // Decode JSON contents
        foreach ($sections as &$sect) {
            $sect['content'] = json_decode($sect['content_json'] ?? '{}', true) ?: [];
        }

        // Fetch media library for selection
        $media = Database::query("SELECT * FROM media WHERE user_id = ? ORDER BY created_at DESC", [$user['id']])->fetchAll();

        $this->render('builder/editor', [
            'title' => "Editing " . $website['name'],
            'website' => $website,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'sections' => $sections,
            'media' => $media,
            'no_layout' => true // Bypass common dashboard header/footer
        ]);
    }

    /**
     * Renders website preview screen inside an iframe
     */
    public function preview() {
        Auth::requireLogin();
        $user = Auth::user();
        
        $websiteId = (int)($_GET['id'] ?? 0);
        $website = Website::find($websiteId, $user['id']);
        
        if (!$website) {
            die("Website not found.");
        }

        $pages = Database::query("SELECT * FROM pages WHERE website_id = ?", [$websiteId])->fetchAll();
        $pageId = (int)($_GET['page_id'] ?? ($pages[0]['id'] ?? 0));
        $currentPage = Database::query("SELECT * FROM pages WHERE id = ? AND website_id = ?", [$pageId, $websiteId])->fetch();
        
        if (!$currentPage) {
            die("Page not found.");
        }

        $sections = Database::query(
            "SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order ASC",
            [$pageId]
        )->fetchAll();

        foreach ($sections as &$sect) {
            $sect['content'] = json_decode($sect['content_json'] ?? '{}', true) ?: [];
        }

        $this->render('builder/preview', [
            'website' => $website,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'sections' => $sections,
            'no_layout' => true
        ]);
    }

    /**
     * Dynamic Publisher Engine
     * Resolves matching subdomains or custom domains, logs analytics, and compiles HTML sections.
     */
    public function renderPublished(string $subdomain, string $pageSlug = 'home') {
        // Fetch website by subdomain or custom domain
        $website = Database::query(
            "SELECT w.*, d.domain_name FROM websites w 
             LEFT JOIN domains d ON d.website_id = w.id
             WHERE w.subdomain = ? OR w.custom_domain = ? OR d.domain_name = ? LIMIT 1",
            [$subdomain, $subdomain, $subdomain]
        )->fetch();

        if (!$website) {
            http_response_code(404);
            die("<h1>Site Not Found</h1><p>The requested website is not registered on this server.</p>");
        }

        if ($website['status'] === 'suspended') {
            http_response_code(403);
            die("<h1>Website Suspended</h1><p>This website has been temporarily suspended by the administration.</p>");
        }

        // Fetch page details
        $page = Database::query(
            "SELECT * FROM pages WHERE website_id = ? AND slug = ? LIMIT 1",
            [$website['id'], $pageSlug]
        )->fetch();

        if (!$page) {
            // Fallback to homepage
            $page = Database::query(
                "SELECT * FROM pages WHERE website_id = ? AND is_homepage = 1 LIMIT 1",
                [$website['id']]
            )->fetch();
            
            if (!$page) {
                http_response_code(404);
                die("<h1>Page Not Found</h1><p>This page doesn't exist on this website.</p>");
            }
        }

        // Log Dynamic Visitor Analytics
        $this->logVisitor($website['id'], $page['slug']);

        // Fetch sections
        $sections = Database::query(
            "SELECT * FROM sections WHERE page_id = ? ORDER BY sort_order ASC",
            [$page['id']]
        )->fetchAll();

        foreach ($sections as &$sect) {
            $sect['content'] = json_decode($sect['content_json'] ?? '{}', true) ?: [];
        }

        // Fetch other pages for dynamic navigation links
        $pages = Database::query("SELECT title, slug FROM pages WHERE website_id = ?", [$website['id']])->fetchAll();

        // Render page using standard Bootstrap 5 template structure
        $this->render('site/render', [
            'website' => $website,
            'page' => $page,
            'sections' => $sections,
            'pages' => $pages,
            'no_layout' => true
        ]);
    }

    /**
     * Simple internal analytics log
     */
    private function logVisitor(int $websiteId, string $pagePath) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
            
            // Basic User Agent parser
            $browser = 'Other';
            if (stripos($ua, 'firefox') !== false) $browser = 'Firefox';
            elseif (stripos($ua, 'chrome') !== false) $browser = 'Chrome';
            elseif (stripos($ua, 'safari') !== false) $browser = 'Safari';
            elseif (stripos($ua, 'edge') !== false) $browser = 'Edge';

            $device = 'desktop';
            if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $ua)) {
                $device = 'mobile';
            } elseif (preg_match('/ipad|playbook|silk/i', $ua)) {
                $device = 'tablet';
            }

            Database::query(
                "INSERT INTO analytics (website_id, visitor_ip, user_agent, browser, device_type, country, referer, page_path) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$websiteId, $ip, $ua, $browser, $device, 'US', $referer, $pagePath]
            );
        } catch (Exception $e) {
            error_log("Failed writing visitor logs: " . $e->getMessage());
        }
    }
}
