<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Get and validate inputs
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

// Prepare and execute query (use get_result for reliability)
$stmt = mysqli_prepare($conn, "SELECT id, username, email, password, status FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log("Prepare failed (process_login): " . mysqli_error($conn));
    $_SESSION['error'] = 'An internal error occurred.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
mysqli_stmt_bind_param($stmt, 's', $email);
if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute failed (process_login): " . mysqli_stmt_error($stmt));
    $_SESSION['error'] = 'An internal error occurred.';
    mysqli_stmt_close($stmt);
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    error_log("Get result failed (process_login): " . mysqli_stmt_error($stmt));
    $_SESSION['error'] = 'An internal error occurred.';
    mysqli_stmt_close($stmt);
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    $_SESSION['error'] = 'Invalid email or password.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$dbHash = $user['password'] ?? '';
$status = $user['status'] ?? '';

if (!password_verify($password, $dbHash)) {
    $_SESSION['error'] = 'Invalid email or password.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

// Authentication successful — set session variables
$_SESSION['id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['status'] = $status;

// Regenerate session id for security
session_regenerate_id(true);

// Optional: log successful login for debugging (remove in production)
error_log("User logged in: id={$_SESSION['id']} status={$_SESSION['status']}");

// Redirect admin to dashboard, others back to previous page
if (strtolower($status) === 'admin') {
    $_SESSION['message'] = 'Welcome back, ' . e($_SESSION['username']) . '!';
    redirect('dashboard.php');
} else {
    $_SESSION['message'] = 'Login successful.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
?>