<?php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to view your booking.';
    redirect('index.php');
}

$booking_id = (int)($_GET['booking_id'] ?? 0);
if ($booking_id <= 0) {
    $_SESSION['error'] = 'Invalid booking reference.';
    redirect('index.php');
}

// Fetch booking info
$stmt = mysqli_prepare($conn, "
    SELECT b.id AS booking_id, b.user_id, u.username, u.email,
           b.car_id, c.name AS car_name, c.price AS car_daily_price,
           b.pickup_date, b.return_date, b.total_price, b.created_at
    FROM bookings b
    LEFT JOIN users u ON u.id = b.user_id
    LEFT JOIN cars c ON c.id = b.car_id
    WHERE b.id = ? LIMIT 1
");
if (!$stmt) {
    $_SESSION['error'] = 'Could not load booking.';
    redirect('index.php');
}
mysqli_stmt_bind_param($stmt, 'i', $booking_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$booking = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$booking) {
    $_SESSION['error'] = 'Booking not found.';
    redirect('index.php');
}

$pageTitle = 'Booking Confirmed';
$homeLink = 'index.php';
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';
?>
<main class="container" style="padding:6rem 0 3rem;">
    <h1>Booking Confirmed</h1>
    <div style="background:#fff;padding:18px;border-radius:8px;border:1px solid #eee;max-width:900px;">
        <p><strong>Booking #<?php echo (int)$booking['booking_id']; ?></strong></p>
        <p><strong>Car:</strong> <?php echo h($booking['car_name'] ?? ''); ?> (ID: <?php echo (int)$booking['car_id']; ?>)</p>
        <p><strong>Booked by:</strong> <?php echo h($booking['username'] ?? $_SESSION['username']); ?> (<?php echo h($booking['email'] ?? ''); ?>)</p>
        <p><strong>Pickup:</strong> <?php echo h($booking['pickup_date']); ?></p>
        <p><strong>Return:</strong> <?php echo h($booking['return_date']); ?></p>
        <p><strong>Total Paid:</strong> $<?php echo number_format((float)$booking['total_price'],2); ?></p>
        <p><strong>Booked on:</strong> <?php echo h($booking['created_at']); ?></p>

        <div style="margin-top:12px;display:flex;gap:10px;">
            <a href="user_profile.php" class="auth-btn" style="text-decoration:none;">View My Bookings</a>

            <!-- CORRECTED: Link now points to reviews.php (existing page) -->
            <a href="reviews.php?car_id=<?php echo (int)$booking['car_id']; ?>&booking_id=<?php echo (int)$booking['booking_id']; ?>" class="auth-btn" style="text-decoration:none;background:#28a745;">
                Leave a Review
            </a>
        </div>
    </div>
</main>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>