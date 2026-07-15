<?php
/**
 * WebForge Installation Verification Utility
 * Run this script in your browser or CLI to test PHP environment and database migrations status.
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=====================================================\n";
echo "       WebForge SaaS System Verification Tool        \n";
echo "=====================================================\n\n";

$errors = 0;

// 1. Check PHP Version
echo "[1] Checking PHP Version... ";
if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
    echo "PASS (Current version: " . PHP_VERSION . ")\n";
} else {
    echo "WARNING (Current version: " . PHP_VERSION . " is below recommended 8.3.0)\n";
}

// 2. Check extensions
echo "[2] Checking Required PHP Extensions... \n";
$extensions = ['pdo', 'pdo_mysql', 'zip', 'json', 'openssl'];
foreach ($extensions as $ext) {
    echo "    - $ext: ";
    if (extension_loaded($ext)) {
        echo "OK\n";
    } else {
        echo "MISSING\n";
        $errors++;
    }
}

// 3. Test database connectivity
echo "[3] Testing MySQL Connectivity... ";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getConnection();
    echo "PASS\n";

    // 4. Test table existence
    echo "[4] Auditing Database Table Registries... \n";
    $requiredTables = [
        'users', 'roles', 'permissions', 'subscriptions', 'plans', 'payments', 
        'orders', 'websites', 'pages', 'sections', 'components', 'templates', 
        'media', 'blogs', 'posts', 'comments', 'products', 'categories', 
        'customers', 'forms', 'enquiries', 'analytics', 'domains', 'backups', 
        'smtp_settings', 'site_settings', 'notifications', 'support_tickets', 
        'activity_logs'
    ];

    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredTables as $table) {
        echo "    - $table: ";
        if (in_array($table, $existingTables)) {
            echo "OK\n";
        } else {
            echo "NOT FOUND (Make sure you successfully seeded database/schema.sql)\n";
            $errors++;
        }
    }
} catch (PDOException $e) {
    echo "FAILED\n";
    echo "    Error Details: " . $e->getMessage() . "\n";
    echo "    Please verify database credentials in config/config.php and ensure MySQL is running.\n";
    $errors++;
}

echo "\n=====================================================\n";
if ($errors === 0) {
    echo "STATUS: SUCCESS! Your environment is completely ready.\n";
    echo "Launch the application at: " . APP_URL . "\n";
} else {
    echo "STATUS: FAILED ($errors issue(s) detected)\n";
    echo "Please resolve the errors above before launching the portal.\n";
}
echo "=====================================================\n";
