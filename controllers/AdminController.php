<?php
/**
 * AdminController Class
 * Provides management tools for SaaS admins: User lists, Subscriptions billing review,
 * Template registers, approvals, and Global site settings.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../config/database.php';

class AdminController extends Controller {

    /**
     * Checks admin permissions before handling requests
     */
    public function __construct() {
        Auth::requireAdmin();
    }

    /**
     * Admin Overview stats
     */
    public function index() {
        $user = Auth::user();

        // Admin KPI Stats
        $usersCount = (int)Database::query("SELECT COUNT(*) as cnt FROM users WHERE role_id = 2")->fetch()['cnt'];
        $websitesCount = (int)Database::query("SELECT COUNT(*) as cnt FROM websites")->fetch()['cnt'];
        $totalRevenue = (float)Database::query("SELECT SUM(amount) as rev FROM payments WHERE status = 'completed'")->fetch()['rev'];
        $pendingTickets = (int)Database::query("SELECT COUNT(*) as cnt FROM support_tickets WHERE status = 'open'")->fetch()['cnt'];

        // Approval Queue of websites
        $approvals = Database::query(
            "SELECT w.*, u.name as owner_name FROM websites w 
             JOIN users u ON w.user_id = u.id 
             WHERE w.is_approved = 0 ORDER BY w.created_at DESC"
        )->fetchAll();

        // Recent Payments
        $payments = Database::query(
            "SELECT p.*, u.name as user_name FROM payments p 
             JOIN users u ON p.user_id = u.id 
             ORDER BY p.created_at DESC LIMIT 5"
        )->fetchAll();

        $this->render('admin/dashboard', [
            'title' => 'Super Admin Console',
            'stats' => [
                'users' => $usersCount,
                'websites' => $websitesCount,
                'revenue' => number_format($totalRevenue, 2),
                'tickets' => $pendingTickets
            ],
            'approvals' => $approvals,
            'payments' => $payments
        ]);
    }

    /**
     * Manage Registered Users
     */
    public function users() {
        $errors = [];
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF error.";
            } else {
                $userId = (int)($_POST['user_id'] ?? 0);
                $action = $_POST['action'] ?? '';

                if ($action === 'suspend') {
                    Database::query("UPDATE users SET status = 'suspended' WHERE id = ? AND role_id != 1", [$userId]);
                    log_activity(Auth::user()['id'], 'suspend_user', "Suspended user ID $userId");
                    $success = "User suspended successfully.";
                } elseif ($action === 'activate') {
                    Database::query("UPDATE users SET status = 'active' WHERE id = ? AND role_id != 1", [$userId]);
                    log_activity(Auth::user()['id'], 'activate_user', "Activated user ID $userId");
                    $success = "User status restored to active.";
                }
            }
        }

        $users = Database::query(
            "SELECT u.*, r.display_name as role_name, (SELECT name FROM plans p JOIN subscriptions s ON s.plan_id = p.id WHERE s.user_id = u.id AND s.status = 'active' LIMIT 1) as plan_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.role_id != 1 
             ORDER BY u.created_at DESC"
        )->fetchAll();

        $this->render('admin/users', [
            'title' => 'Manage Users',
            'users' => $users,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    /**
     * Manage Plans and Subscription statuses
     */
    public function subscriptions() {
        $payments = Database::query(
            "SELECT p.*, u.name as user_name, u.email as user_email 
             FROM payments p 
             JOIN users u ON p.user_id = u.id 
             ORDER BY p.created_at DESC"
        )->fetchAll();

        $this->render('admin/subscriptions', [
            'title' => 'Billing & Subscriptions',
            'payments' => $payments
        ]);
    }

    /**
     * Templates Marketplace management
     */
    public function templates() {
        $errors = [];
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF verification failed.";
            } else {
                $name = trim($_POST['name'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $category = trim($_POST['category'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $layoutJson = $_POST['layout_json'] ?? '[]';

                if (empty($name) || empty($slug) || empty($category)) {
                    $errors[] = "Name, Slug, and Category fields are required.";
                } else {
                    try {
                        Database::query(
                            "INSERT INTO templates (name, slug, category, description, layout_json) 
                             VALUES (?, ?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE name=VALUES(name), category=VALUES(category), description=VALUES(description), layout_json=VALUES(layout_json)",
                            [$name, $slug, $category, $desc, $layoutJson]
                        );
                        log_activity(Auth::user()['id'], 'save_template', "Saved template '$name'");
                        $success = "Template saved successfully!";
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }

        $templates = Database::query("SELECT * FROM templates ORDER BY created_at DESC")->fetchAll();

        $this->render('admin/templates', [
            'title' => 'Template Marketplace Manager',
            'templates' => $templates,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    /**
     * Global Platform Configurations (SMTP, AI credentials)
     */
    public function settings() {
        $errors = [];
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF verification failed.";
            } else {
                foreach ($_POST as $key => $val) {
                    if ($key === 'csrf_token') continue;
                    
                    Database::query(
                        "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                        [$key, sanitize_input([$val])[0]]
                    );
                }
                log_activity(Auth::user()['id'], 'update_site_settings', 'Updated platform core configurations.');
                $success = "Platform configuration saved successfully.";
            }
        }

        $settings = Database::query("SELECT * FROM site_settings")->fetchAll();
        $siteSettings = [];
        foreach ($settings as $s) {
            $siteSettings[$s['setting_key']] = $s['setting_value'];
        }

        $this->render('admin/settings', [
            'title' => 'Global Platform Settings',
            'settings' => $siteSettings,
            'errors' => $errors,
            'success' => $success
        ]);
    }
}
