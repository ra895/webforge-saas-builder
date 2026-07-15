<?php
/**
 * Authentication Wrapper Class
 * Manages user logins, remember me sessions, roles, and logouts.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

class Auth {
    private static ?array $currentUser = null;

    /**
     * Authenticate and initialize session
     */
    public static function login(array $user, bool $remember = false): bool {
        $_SESSION[SESSION_USER_KEY] = [
            'id' => $user['id'],
            'role_id' => $user['role_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'status' => $user['status']
        ];
        
        session_regenerate_id(true);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            // Save remember token in DB
            Database::query(
                "UPDATE users SET remember_token = ? WHERE id = ?",
                [$token, $user['id']]
            );
            // Set cookie for 30 days
            setcookie(
                REMEMBER_ME_COOKIE,
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                APP_ENV === 'production',
                true // HttpOnly
            );
        }
        
        log_activity($user['id'], 'login', 'User successfully logged in.');
        return true;
    }

    /**
     * Check if user session exists and verify cookie token if necessary
     */
    public static function check(): bool {
        if (isset($_SESSION[SESSION_USER_KEY])) {
            return true;
        }

        // Try remember me cookie
        if (isset($_COOKIE[REMEMBER_ME_COOKIE])) {
            $token = $_COOKIE[REMEMBER_ME_COOKIE];
            $user = Database::query(
                "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.remember_token = ? LIMIT 1",
                [$token]
            )->fetch();

            if ($user && $user['status'] === 'active') {
                self::login($user);
                return true;
            }
        }

        return false;
    }

    /**
     * Get details of currently logged in user
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser === null) {
            $userId = $_SESSION[SESSION_USER_KEY]['id'];
            self::$currentUser = Database::query(
                "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? LIMIT 1",
                [$userId]
            )->fetch() ?: null;
        }

        return self::$currentUser;
    }

    /**
     * Check if current user is Super Admin
     */
    public static function isAdmin(): bool {
        $user = self::user();
        return $user && ($user['role_name'] === 'super_admin' || (int)$user['role_id'] === 1);
    }

    /**
     * Force redirect to login page if unauthenticated
     */
    public static function requireLogin() {
        if (!self::check()) {
            redirect('/auth/login');
        }
    }

    /**
     * Force redirect if not super admin
     */
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            die("Unauthorized access. Admin role required.");
        }
    }

    /**
     * Clear sessions and cookies
     */
    public static function logout() {
        $user = self::user();
        if ($user) {
            log_activity($user['id'], 'logout', 'User logged out.');
            Database::query(
                "UPDATE users SET remember_token = NULL WHERE id = ?",
                [$user['id']]
            );
        }

        unset($_SESSION[SESSION_USER_KEY]);
        if (isset($_COOKIE[REMEMBER_ME_COOKIE])) {
            setcookie(REMEMBER_ME_COOKIE, '', time() - 3600, '/');
        }
        
        session_destroy();
    }
}
