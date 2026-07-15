<?php
/**
 * Application Configuration File
 * SaaS Website Builder Configuration
 */

// Basic Settings
define('APP_NAME', 'WebForge SaaS Builder');
define('APP_URL', 'http://localhost/antigravity'); // Adjust to your local development URI
define('APP_ENV', 'development'); // 'development' or 'production'

// DB Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webforge_db');

// SMTP Settings (Default Fallback)
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'no-reply@webforge.local');
define('SMTP_FROM_NAME', 'WebForge SaaS');

// Subscriptions & Usage Limits (defaults, actual verified via subscription plans)
define('DEFAULT_PLAN_ID', 1); // Free plan

// File Upload Limits
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_UPLOAD_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'application/pdf', 'image/x-icon', 'image/svg+xml']);

// Security Salts / Keys
define('CSRF_SESSION_KEY', 'wf_csrf_token');
define('SESSION_USER_KEY', 'wf_logged_user');
define('REMEMBER_ME_COOKIE', 'wf_remember_token');

// Session configurations
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (APP_ENV === 'production') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Error reporting settings
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
