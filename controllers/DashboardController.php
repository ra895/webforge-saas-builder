<?php
/**
 * DashboardController Class
 * Manages the User Dashboard workspace, site actions, and custom integrations.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../config/database.php';

class DashboardController extends Controller {

    /**
     * Dashboard Overview stats and recent user actions
     */
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        // Get subscription & plan
        $sub = Subscription::getActive($user['id']);

        // Stats queries
        $websitesCount = (int)Database::query("SELECT COUNT(*) as cnt FROM websites WHERE user_id = ?", [$user['id']])->fetch()['cnt'];
        $storageUsedBytes = (int)Database::query("SELECT SUM(filesize) as total FROM media WHERE user_id = ?", [$user['id']])->fetch()['total'];
        
        // Sum enquiries across all user websites
        $enquiriesCount = (int)Database::query(
            "SELECT COUNT(e.id) as cnt FROM enquiries e 
             JOIN forms f ON e.form_id = f.id 
             JOIN websites w ON f.website_id = w.id 
             WHERE w.user_id = ?",
            [$user['id']]
        )->fetch()['cnt'];

        // Visitor traffic sum
        $visitorsCount = (int)Database::query(
            "SELECT COUNT(a.id) as cnt FROM analytics a 
             JOIN websites w ON a.website_id = w.id 
             WHERE w.user_id = ?",
            [$user['id']]
        )->fetch()['cnt'];

        // Recent websites
        $websites = Website::getByUser($user['id']);

        // Recent activity
        $logs = Database::query(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        )->fetchAll();

        // Fetch support tickets status
        $tickets = Database::query(
            "SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        )->fetchAll();

        $this->render('dashboard/index', [
            'title' => 'Dashboard Overview',
            'sub' => $sub,
            'stats' => [
                'websites' => $websitesCount,
                'storage' => format_bytes($storageUsedBytes),
                'enquiries' => $enquiriesCount,
                'visitors' => $visitorsCount
            ],
            'websites' => $websites,
            'logs' => $logs,
            'tickets' => $tickets
        ]);
    }

    /**
     * View and modify websites list
     */
    public function websites() {
        Auth::requireLogin();
        $user = Auth::user();
        
        $errors = [];
        $success = "";

        // Action: Create Website
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF Token validation failed.";
            } else {
                $action = $_POST['action'];

                if ($action === 'create') {
                    $name = trim($_POST['name'] ?? '');
                    $subdomain = trim($_POST['subdomain'] ?? '');
                    $template = $_POST['template'] ?? null;

                    if (empty($name) || empty($subdomain)) {
                        $errors[] = "Website name and Subdomain are required.";
                    } elseif (!Subscription::canCreateWebsite($user['id'])) {
                        $errors[] = "Website limit reached for your active plan. Please upgrade.";
                    } else {
                        try {
                            Website::create($user['id'], $name, $subdomain, $template);
                            $success = "Website '$name' created successfully!";
                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    }
                } 
                
                // Action: Delete Website
                elseif ($action === 'delete') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    if (Website::delete($id, $user['id'])) {
                        $success = "Website deleted successfully.";
                    } else {
                        $errors[] = "Failed to delete website. Access denied.";
                    }
                }

                // Action: Duplicate Website
                elseif ($action === 'duplicate') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');
                    $subdomain = trim($_POST['subdomain'] ?? '');

                    if (empty($name) || empty($subdomain)) {
                        $errors[] = "Name and Subdomain are required.";
                    } else {
                        try {
                            Website::duplicate($id, $user['id'], $name, $subdomain);
                            $success = "Website duplicated successfully.";
                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }
                    }
                }

                // Action: Rename Website
                elseif ($action === 'rename') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    $name = trim($_POST['name'] ?? '');

                    if (empty($name)) {
                        $errors[] = "Website name cannot be empty.";
                    } else {
                        Database::query(
                            "UPDATE websites SET name = ? WHERE id = ? AND user_id = ?",
                            [$name, $id, $user['id']]
                        );
                        log_activity($user['id'], 'rename_website', "Renamed website ID $id to '$name'");
                        $success = "Website renamed successfully.";
                    }
                }
                
                // Action: Connect Custom Domain
                elseif ($action === 'connect_domain') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    $domainName = trim(strtolower($_POST['domain_name'] ?? ''));

                    // Verify website ownership
                    $website = Website::find($id, $user['id']);
                    if (!$website) {
                        $errors[] = "Website not found.";
                    } elseif (empty($domainName)) {
                        $errors[] = "Domain name cannot be empty.";
                    } else {
                        // Check subscription limit for custom domains
                        $sub = Subscription::getActive($user['id']);
                        if ((int)$sub['has_custom_domain'] !== 1) {
                            $errors[] = "Your current plan does not support custom domains. Please upgrade to a Starter or Pro plan.";
                        } else {
                            try {
                                Database::beginTransaction();

                                // Check if domain is already registered on another website
                                $exists = Database::query("SELECT id FROM domains WHERE domain_name = ? AND website_id != ?", [$domainName, $id])->fetch();
                                if ($exists) {
                                    throw new Exception("This domain name is already connected to another project.");
                                }

                                // Update domains table
                                Database::query(
                                    "INSERT INTO domains (website_id, domain_name, ssl_status, dns_verified) 
                                     VALUES (?, ?, 'pending', 1) 
                                     ON DUPLICATE KEY UPDATE domain_name = VALUES(domain_name), ssl_status = 'pending', dns_verified = 1",
                                    [$id, $domainName]
                                );

                                // Update websites table
                                Database::query(
                                    "UPDATE websites SET custom_domain = ? WHERE id = ?",
                                    [$domainName, $id]
                                );

                                Database::commit();
                                log_activity($user['id'], 'connect_domain', "Connected custom domain '$domainName' to website ID $id");
                                $success = "Custom domain '$domainName' connected successfully!";
                            } catch (Exception $e) {
                                Database::rollBack();
                                $errors[] = $e->getMessage();
                            }
                        }
                    }
                }

                // Action: Disconnect Custom Domain
                elseif ($action === 'disconnect_domain') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    $website = Website::find($id, $user['id']);
                    if ($website) {
                        Database::beginTransaction();
                        try {
                            Database::query("DELETE FROM domains WHERE website_id = ?", [$id]);
                            Database::query("UPDATE websites SET custom_domain = NULL WHERE id = ?", [$id]);
                            Database::commit();
                            log_activity($user['id'], 'disconnect_domain', "Disconnected custom domain from website ID $id");
                            $success = "Custom domain disconnected successfully.";
                        } catch (Exception $e) {
                            Database::rollBack();
                            $errors[] = $e->getMessage();
                        }
                    } else {
                        $errors[] = "Access denied.";
                    }
                }

                // Action: Save SEO settings
                elseif ($action === 'save_seo') {
                    $id = (int)($_POST['website_id'] ?? 0);
                    $metaTitle = trim($_POST['meta_title'] ?? '');
                    $metaDescription = trim($_POST['meta_description'] ?? '');
                    $metaKeywords = trim($_POST['meta_keywords'] ?? '');

                    $website = Website::find($id, $user['id']);
                    if ($website) {
                        // Find homepage for this website
                        $page = Database::query("SELECT id FROM pages WHERE website_id = ? AND is_homepage = 1 LIMIT 1", [$id])->fetch();
                        if ($page) {
                            Database::query(
                                "UPDATE pages SET meta_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?",
                                [$metaTitle, $metaDescription, $metaKeywords, $page['id']]
                            );
                            log_activity($user['id'], 'update_seo', "Updated SEO metadata for homepage of website ID $id");
                            $success = "SEO settings saved successfully.";
                        } else {
                            $errors[] = "Homepage not found.";
                        }
                    } else {
                        $errors[] = "Access denied.";
                    }
                }
            }
        }

        $websites = Website::getByUser($user['id']);
        $templates = Database::query("SELECT name, slug, category FROM templates WHERE is_active = 1")->fetchAll();

        $this->render('dashboard/websites', [
            'title' => 'My Websites',
            'websites' => $websites,
            'templates' => $templates,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    /**
     * Dashboard settings page: SMTP & Third-Party Code Integration (GA, Pixel)
     */
    public function settings() {
        Auth::requireLogin();
        $user = Auth::user();

        $errors = [];
        $success = "";

        // Fetch SMTP config or blank details
        $smtp = Database::query("SELECT * FROM smtp_settings WHERE user_id = ? LIMIT 1", [$user['id']])->fetch() ?: [
            'host' => '', 'port' => 587, 'username' => '', 'password' => '', 'encryption' => 'tls', 'from_email' => '', 'from_name' => ''
        ];

        // Fetch Site-wide or custom integration options if saved under user metadata (simulated using site_settings with user-prefix)
        $analyticsId = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['ga_id_' . $user['id']])->fetch()['setting_value'] ?? '';
        $pixelId = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['pixel_id_' . $user['id']])->fetch()['setting_value'] ?? '';
        $whatsappNo = Database::query("SELECT setting_value FROM site_settings WHERE setting_key = ?", ['whatsapp_' . $user['id']])->fetch()['setting_value'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF verification failed.";
            } else {
                // Update SMTP configuration
                $host = trim($_POST['smtp_host'] ?? '');
                $port = (int)($_POST['smtp_port'] ?? 587);
                $username = trim($_POST['smtp_username'] ?? '');
                $password = $_POST['smtp_password'] ?? '';
                $encryption = $_POST['smtp_encryption'] ?? 'tls';
                $fromEmail = trim($_POST['smtp_from_email'] ?? '');
                $fromName = trim($_POST['smtp_from_name'] ?? '');

                if (!empty($host)) {
                    Database::query(
                        "INSERT INTO smtp_settings (user_id, host, port, username, password, encryption, from_email, from_name) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE host = VALUES(host), port = VALUES(port), username = VALUES(username), 
                                                 password = VALUES(password), encryption = VALUES(encryption), 
                                                 from_email = VALUES(from_email), from_name = VALUES(from_name)",
                        [$user['id'], $host, $port, $username, $password, $encryption, $fromEmail, $fromName]
                    );
                }

                // Update GA, Pixel, WhatsApp
                $analyticsId = trim($_POST['analytics_id'] ?? '');
                $pixelId = trim($_POST['pixel_id'] ?? '');
                $whatsappNo = trim($_POST['whatsapp_no'] ?? '');

                // Simple upsert helper
                $settings = [
                    'ga_id_' . $user['id'] => $analyticsId,
                    'pixel_id_' . $user['id'] => $pixelId,
                    'whatsapp_' . $user['id'] => $whatsappNo
                ];

                foreach ($settings as $key => $val) {
                    Database::query(
                        "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                        [$key, $val]
                    );
                }

                log_activity($user['id'], 'update_settings', 'Updated custom SMTP and API tracker credentials.');
                $success = "Settings updated successfully.";
                
                // Refresh local data
                $smtp = Database::query("SELECT * FROM smtp_settings WHERE user_id = ? LIMIT 1", [$user['id']])->fetch() ?: $smtp;
            }
        }

        $this->render('dashboard/settings', [
            'title' => 'Dashboard Settings',
            'smtp' => $smtp,
            'analytics_id' => $analyticsId,
            'pixel_id' => $pixelId,
            'whatsapp_no' => $whatsappNo,
            'errors' => $errors,
            'success' => $success
        ]);
    }
}
