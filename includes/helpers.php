<?php
/**
 * HireGenius - Helper Functions
 * 
 * Common utility functions used across the application
 */

/**
 * Start secure session
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'use_strict_mode' => true,
        ]);
    }
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Escape HTML output
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn(string $type = 'user'): bool
{
    startSecureSession();
    return isset($_SESSION[$type . '_id']);
}

/**
 * Require authentication
 */
function requireAuth(string $type, string $redirectUrl): void
{
    if (!isLoggedIn($type)) {
        redirect($redirectUrl);
    }
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    startSecureSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF hidden input field
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

/**
 * Generate random interview code
 */
function generateInterviewCode(int $length = 6): string
{
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Format datetime for display
 */
function formatDateTime(string $datetime, string $format = 'M j, Y g:i A'): string
{
    return date($format, strtotime($datetime));
}

/**
 * Check if request is POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get POST data safely
 */
function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data safely
 */
function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void
{
    startSecureSession();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array
{
    startSecureSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash(): string
{
    $flash = getFlash();
    if ($flash) {
        $class = $flash['type'] === 'error' ? 'alert-error' : 'alert-success';
        return '<div class="alert ' . $class . '">' . e($flash['message']) . '</div>';
    }
    return '';
}

/**
 * Get base URL
 */
function baseUrl(string $path = ''): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Navigate to root of project
    $baseDir = str_replace('\\', '/', $scriptDir);
    
    return rtrim("$protocol://$host$baseDir", '/') . '/' . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset(string $path): string
{
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}
