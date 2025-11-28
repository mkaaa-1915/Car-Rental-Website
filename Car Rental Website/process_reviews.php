<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to submit a review.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$car_id = (int)($_POST['car_id'] ?? 0);
$booking_id = (int)($_POST['booking_id'] ?? 0); // optional, not used currently
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$uid = (int)($_SESSION['id'] ?? 0);

// Validate
if ($car_id <= 0 || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Please provide a valid rating.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'vehicles.php');
}

// Optional: check that user previously booked this car (recommended in production)


$stmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE id = ? AND user_id = ? AND car_id = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'iii', $booking_id, $uid, $car_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $found = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    if (!$found) {
        $_SESSION['error'] = 'You may only review cars you booked.';
        redirect('car_details.php?id=' . $car_id);
    }
}

$stmt = mysqli_prepare($conn, "INSERT INTO reviews (user_id, car_id, rating, comment) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    $_SESSION['error'] = 'Internal error (prepare).';
    redirect('car_details.php?id=' . $car_id);
}
mysqli_stmt_bind_param($stmt, 'iiis', $uid, $car_id, $rating, $comment);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    $_SESSION['error'] = 'Failed to save review: ' . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    redirect('car_details.php?id=' . $car_id);
}
mysqli_stmt_close($stmt);

$_SESSION['message'] = 'Thank you! Your review has been submitted.';
redirect('car_details.php?id=' . $car_id);
?>