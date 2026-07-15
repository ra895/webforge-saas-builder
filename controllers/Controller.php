<?php
/**
 * Base Controller Class
 * Handles templating view injection, headers, and dynamic data passing.
 */

abstract class Controller {
    /**
     * Render HTML view template
     * 
     * @param string $view e.g. 'auth/login' or 'dashboard/index'
     * @param array $data Variables to expose inside the template scope
     */
    protected function render(string $view, array $data = []) {
        // Expose variables locally
        extract($data);
        
        // Inject auth state
        $currentUser = Auth::user();
        
        // Check if layout needs to be bypassed (e.g. Builder Editor)
        $noLayout = $data['no_layout'] ?? false;
        
        if ($noLayout) {
            $viewFile = __DIR__ . '/../views/' . $view . '.php';
            if (file_exists($viewFile)) {
                require_once $viewFile;
            } else {
                echo "View file '$viewFile' not found.";
            }
            return;
        }

        // Standard Layout wrapper
        $headerPath = __DIR__ . '/../views/includes/header.php';
        $viewFile   = __DIR__ . '/../views/' . $view . '.php';
        $footerPath = __DIR__ . '/../views/includes/footer.php';

        if (file_exists($headerPath)) require_once $headerPath;
        if (file_exists($viewFile))   require_once $viewFile;
        if (file_exists($footerPath)) require_once $footerPath;
    }

    /**
     * Helper to return standard JSON data
     */
    protected function json(bool $success, string $message, array $data = [], int $status = 200) {
        json_response($success, $message, $data, $status);
    }
}
