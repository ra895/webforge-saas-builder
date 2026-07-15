<?php
/**
 * AuthController Class
 * Manages Login, Register, Logouts, Forgot, Reset and Email Verification workflows.
 */

require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    
    /**
     * Handle Login GET/POST
     */
    public function login() {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check CSRF
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF Token validation failed. Please refresh and try again.";
            } else {
                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'] ?? '';
                $remember = isset($_POST['remember']);

                if (empty($email) || empty($password)) {
                    $errors[] = "Please fill in all details.";
                } else {
                    $user = User::authenticate($email, $password);
                    if ($user) {
                        if ($user['status'] === 'suspended') {
                            $errors[] = "Your account has been suspended. Please contact support.";
                        } else {
                            Auth::login($user, $remember);
                            redirect('/dashboard');
                        }
                    } else {
                        $errors[] = "Invalid login credentials.";
                    }
                }
            }
        }

        $this->render('auth/login', [
            'title' => 'Sign In',
            'errors' => $errors,
            'no_layout' => true // Renders direct card login page
        ]);
    }

    /**
     * Handle Register GET/POST
     */
    public function register() {
        if (Auth::check()) {
            redirect('/dashboard');
        }

        $errors = [];
        $success = "";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF Token validation failed.";
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['password_confirm'] ?? '';

                if (empty($name) || empty($email) || empty($password)) {
                    $errors[] = "All fields are required.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid email format.";
                } elseif (strlen($password) < 6) {
                    $errors[] = "Password must be at least 6 characters long.";
                } elseif ($password !== $confirm) {
                    $errors[] = "Passwords do not match.";
                } else {
                    try {
                        User::create($name, $email, $password);
                        $success = "Registration successful! A verification email has been sent to your address. Please verify your email before logging in.";
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }

        $this->render('auth/register', [
            'title' => 'Sign Up',
            'errors' => $errors,
            'success' => $success,
            'no_layout' => true
        ]);
    }

    /**
     * Handle Forgot Password GET/POST
     */
    public function forgot() {
        $errors = [];
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF verification failed.";
            } else {
                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                if (empty($email)) {
                    $errors[] = "Please provide an email address.";
                } else {
                    if (User::sendResetLink($email)) {
                        $success = "If your account exists, a password reset link has been dispatched to your email.";
                    } else {
                        // Keep messaging obscure for security
                        $success = "If your account exists, a password reset link has been dispatched to your email.";
                    }
                }
            }
        }

        $this->render('auth/forgot', [
            'title' => 'Forgot Password',
            'errors' => $errors,
            'success' => $success,
            'no_layout' => true
        ]);
    }

    /**
     * Handle Password Reset link click
     */
    public function reset() {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        if (empty($token)) {
            die("Reset token missing.");
        }

        $errors = [];
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors[] = "CSRF verification failed.";
            } else {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['password_confirm'] ?? '';

                if (strlen($password) < 6) {
                    $errors[] = "Password must be at least 6 characters.";
                } elseif ($password !== $confirm) {
                    $errors[] = "Passwords do not match.";
                } else {
                    if (User::resetPassword($token, $password)) {
                        $success = "Your password has been reset successfully! You can now log in.";
                    } else {
                        $errors[] = "This reset link is invalid or has expired.";
                    }
                }
            }
        }

        $this->render('auth/reset', [
            'title' => 'Reset Password',
            'token' => $token,
            'errors' => $errors,
            'success' => $success,
            'no_layout' => true
        ]);
    }

    /**
     * Handle Verify Email address
     */
    public function verify() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            die("Verification token is missing.");
        }

        $success = User::verifyEmail($token);
        $this->render('auth/verify', [
            'title' => 'Email Verification',
            'success' => $success,
            'no_layout' => true
        ]);
    }

    /**
     * Log user out
     */
    public function logout() {
        Auth::logout();
        redirect('/auth/login');
    }
}
