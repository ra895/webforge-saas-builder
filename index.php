<?php
/**
 * Front Controller & URL Router
 * Directs traffic to the correct controllers based on request paths.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Parse the request URI relative to APP_URL
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
$path = str_replace($basePath, '', $requestUri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Auto-Loader for Controllers and Models
spl_autoload_register(function ($class) {
    if (file_exists(__DIR__ . '/controllers/' . $class . '.php')) {
        require_once __DIR__ . '/controllers/' . $class . '.php';
    } elseif (file_exists(__DIR__ . '/models/' . $class . '.php')) {
        require_once __DIR__ . '/models/' . $class . '.php';
    }
});

// Routing Registry
$routes = [
    // Auth Routes
    'auth/login'              => 'AuthController@login',
    'auth/register'           => 'AuthController@register',
    'auth/logout'             => 'AuthController@logout',
    'auth/forgot'             => 'AuthController@forgot',
    'auth/reset'              => 'AuthController@reset',
    'auth/verify'             => 'AuthController@verify',
    
    // User Dashboard Routes
    'dashboard'               => 'DashboardController@index',
    'dashboard/websites'      => 'DashboardController@websites',
    'dashboard/settings'      => 'DashboardController@settings',
    
    // Builder Routes
    'builder/editor'          => 'BuilderController@editor',
    'builder/preview'         => 'BuilderController@preview',
    
    // Admin Routes
    'admin'                   => 'AdminController@index',
    'admin/users'             => 'AdminController@users',
    'admin/subscriptions'     => 'AdminController@subscriptions',
    'admin/templates'         => 'AdminController@templates',
    'admin/settings'          => 'AdminController@settings',
    
    // REST APIs
    'api/builder/save'        => 'ApiController@savePage',
    'api/builder/upload'      => 'ApiController@uploadMedia',
    'api/ai/generate'         => 'ApiController@generateAI',
    'api/forms/submit'        => 'ApiController@submitForm',
    'api/payments/checkout'   => 'ApiController@checkout',
    'api/payments/webhook'    => 'ApiController@webhook',
    'api/github/push'         => 'ApiController@pushToGithub',
    'api/export/zip'          => 'ApiController@exportZip',
    'api/builder/seo'         => 'ApiController@getSeo'
];

// Check Route Matches
if (array_key_exists($path, $routes)) {
    list($controllerName, $methodName) = explode('@', $routes[$path]);
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        $controller->$methodName();
        exit;
    }
}

// Fallback for Dynamic Website Viewer (Subdomain or Path-based rendering)
// Example path: site/ecocorp-tech
if (preg_match('/^site\/([a-zA-Z0-9\-]+)(?:\/([a-zA-Z0-9\-]+))?$/', $path, $matches)) {
    $subdomain = $matches[1];
    $pageSlug = $matches[2] ?? 'home';
    
    $controller = new BuilderController();
    $controller->renderPublished($subdomain, $pageSlug);
    exit;
}

// Default Landing Page Route
if ($path === '') {
    // If user is already logged in, redirect to dashboard. Otherwise, show login.
    if (Auth::check()) {
        redirect('/dashboard');
    } else {
        redirect('/auth/login');
    }
}

// 404 Handler
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested path '" . e($path) . "' does not exist.</p>";
exit;
