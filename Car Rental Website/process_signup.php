<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$fullname = trim(filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'Please fill in all fields.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

// Check if email already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($conn));
    $_SESSION['error'] = 'Internal error.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    $_SESSION['error'] = 'Email already registered.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
mysqli_stmt_close($stmt);

// Create user (status = 'user' by default)
$hash = password_hash($password, PASSWORD_DEFAULT);
$status = 'user';

$insert = mysqli_prepare($conn, "INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, ?)");
if (!$insert) {
    error_log("Prepare failed: " . mysqli_error($conn));
    $_SESSION['error'] = 'Internal error.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
mysqli_stmt_bind_param($insert, 'ssss', $fullname, $email, $hash, $status);
$ok = mysqli_stmt_execute($insert);
if (!$ok) {
    error_log("Insert failed: " . mysqli_stmt_error($insert));
    $_SESSION['error'] = 'Could not create account.';
    mysqli_stmt_close($insert);
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}
mysqli_stmt_close($insert);

// Auto-login new user (optional)
$uid = mysqli_insert_id($conn);
$_SESSION['id'] = $uid;
$_SESSION['username'] = $fullname;
$_SESSION['email'] = $email;
$_SESSION['status'] = $status;
session_regenerate_id(true);

$_SESSION['message'] = 'Account created successfully.';
redirect('index.php');
?>