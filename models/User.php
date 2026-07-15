<?php
/**
 * User Model
 * Manages user accounts, authorization, security settings, and registration.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

class User {
    /**
     * Authenticate a user by email and password
     */
    public static function authenticate(string $email, string $password): ?array {
        $user = Database::query(
            "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? LIMIT 1",
            [$email]
        )->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Create a new user with default free plan
     */
    public static function create(string $name, string $email, string $password): int {
        Database::beginTransaction();
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));
            
            // Insert user (default role user = 2)
            Database::query(
                "INSERT INTO users (role_id, name, email, password, verification_token, status) VALUES (?, ?, ?, ?, ?, ?)",
                [2, $name, $email, $hashedPassword, $token, 'pending']
            );
            $userId = (int)Database::lastInsertId();

            // Seed user default subscription (plan_id = 1, Free)
            Database::query(
                "INSERT INTO subscriptions (user_id, plan_id, status, billing_cycle) VALUES (?, 1, 'active', 'free')",
                [$userId]
            );

            // Send registration email
            $link = APP_URL . "/auth/verify?token=" . $token;
            $body = "<h2>Welcome to WebForge!</h2><p>Please click the link below to verify your email address:</p><a href='$link'>$link</a>";
            send_mail($email, "Verify Your Email Address", $body);

            Database::commit();
            log_activity($userId, 'register', 'New user account registered.');
            return $userId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Verify email with token
     */
    public static function verifyEmail(string $token): bool {
        $user = Database::query("SELECT id FROM users WHERE verification_token = ? LIMIT 1", [$token])->fetch();
        if ($user) {
            Database::query(
                "UPDATE users SET status = 'active', email_verified_at = NOW(), verification_token = NULL WHERE id = ?",
                [$user['id']]
            );
            log_activity($user['id'], 'verify_email', 'Email verified successfully.');
            return true;
        }
        return false;
    }

    /**
     * Initiate Password Reset Flow
     */
    public static function sendResetLink(string $email): bool {
        $user = Database::query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            Database::query(
                "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?",
                [$token, $expires, $user['id']]
            );

            $link = APP_URL . "/auth/reset?token=" . $token;
            $body = "<h2>Password Reset Request</h2><p>You requested a password reset. Click below to reset your password:</p><a href='$link'>$link</a>";
            send_mail($email, "Reset Your Password", $body);
            return true;
        }
        return false;
    }

    /**
     * Complete password reset
     */
    public static function resetPassword(string $token, string $newPassword): bool {
        $user = Database::query(
            "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1",
            [$token]
        )->fetch();

        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            Database::query(
                "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?",
                [$hashedPassword, $user['id']]
            );
            log_activity($user['id'], 'reset_password', 'Password reset completed.');
            return true;
        }
        return false;
    }
}
