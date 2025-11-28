<?php
// Simple config.php — DB + session + shared helpers (restores prior working behavior)

require_once __DIR__ . '/includes/helpers.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_rental');

// Optional Gemini/LLM configuration (leave empty to disable remote calls).
// It's recommended to store secrets in environment variables or a .env file.
// Example (on your system): export GEMINI_API_KEY="sk_..." and export GEMINI_API_URL="https://api.example.com/v1/generate"
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_API_URL', getenv('GEMINI_API_URL') ?: ''); // e.g. "https://api.your-llm-provider/v1/generate"

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Use UTF8MB4 for full unicode support
mysqli_set_charset($conn, 'utf8mb4');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if any user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['id']);
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['status']) && $_SESSION['status'] === 'admin';
}

/**
 * Current user helper
 * @return array
 */
function currentUser(): array {
    return [
        'id' => $_SESSION['id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'status' => $_SESSION['status'] ?? null,
    ];
}

/**
 * Simple redirect helper
 */
function redirect(string $url) {
    header("Location: $url");
    exit();
}

/**
 * Escape helper for HTML output
 */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}