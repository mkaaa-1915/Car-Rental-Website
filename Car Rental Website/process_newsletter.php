<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $_SESSION['error'] = 'Please enter your email address.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
    
    // Check if email already subscribed
    $stmt = mysqli_prepare($conn, "SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            $_SESSION['message'] = 'You are already subscribed to our newsletter!';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Insert email into newsletter table
    $stmt = mysqli_prepare($conn, "INSERT INTO newsletter_subscribers (email) VALUES (?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Thank you for subscribing! You will receive 20% discount on your next rental.';
        } else {
            $_SESSION['error'] = 'Failed to subscribe. Please try again.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Database error. Please try again.';
    }
    
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>