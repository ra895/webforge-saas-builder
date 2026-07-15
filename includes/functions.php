<?php
/**
 * Global Helper Functions
 * Includes safety measures: XSS protection, CSRF, response formatting, and input filters.
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Escapes output for HTML context (Anti-XSS)
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizes input array recursively
 */
function sanitize_input(array $data): array {
    $clean = [];
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $clean[$key] = sanitize_input($val);
        } else {
            $clean[$key] = trim(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'));
        }
    }
    return $clean;
}

/**
 * Generate CSRF token and store it in session
 */
function generate_csrf_token(): string {
    if (empty($_SESSION[CSRF_SESSION_KEY])) {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_SESSION_KEY];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token(?string $token): bool {
    if (empty($_SESSION[CSRF_SESSION_KEY]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_SESSION_KEY], $token);
}

/**
 * Output CSRF Hidden Input Field
 */
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * JSON Response helper for REST APIs
 */
function json_response(bool $success, string $message, array $data = [], int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Write log entry to DB or file (simple fallback to file or table)
 */
function log_activity(?int $userId, string $action, ?string $description = null) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        Database::query(
            "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)",
            [$userId, $action, $description, $ip]
        );
    } catch (Exception $e) {
        // Fallback to error_log
        error_log("Failed logging activity: " . $e->getMessage());
    }
}

/**
 * Checks API rate limiting based on Session request timestamps
 */
function check_rate_limit(string $key, int $limit = 60, int $window = 60): bool {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    $now = time();
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // Filter timestamps within window
    $_SESSION['rate_limits'][$key] = array_filter(
        $_SESSION['rate_limits'][$key],
        fn($timestamp) => $timestamp > ($now - $window)
    );
    
    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        return false;
    }
    
    $_SESSION['rate_limits'][$key][] = $now;
    return true;
}

/**
 * Sends SMTP or PHPMailer styled mock email
 */
function send_mail(string $to, string $subject, string $body, array $smtp = []): bool {
    // In actual production, you would load SMTP configurations from the database
    // Here we wrap with a standard dynamic header
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    
    // Fallback Mocking logs for XAMPP / development
    if (APP_ENV === 'development') {
        $logDir = __DIR__ . '/../uploads/mail_logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/' . time() . '_' . md5($to . $subject) . '.html';
        file_put_contents($logFile, "<h3>To: $to</h3><h4>Subject: $subject</h4><hr>$body");
        return true;
    }
    
    return @mail($to, $subject, $body, $headers);
}

/**
 * Redirect helper
 */
function redirect(string $path) {
    header("Location: " . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Format filesizes
 */
function format_bytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
