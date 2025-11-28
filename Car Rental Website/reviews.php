<?php
require_once 'config.php';

$car_id = (int)($_GET['car_id'] ?? 0);
$booking_id = (int)($_GET['booking_id'] ?? 0);

if ($car_id <= 0) {
    $_SESSION['error'] = 'Invalid car for review.';
    redirect('vehicles.php');
}

// Ensure user logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to submit a review.';
    redirect('car_details.php?id=' . $car_id);
}

// Optional: you may check that the current user has a booking for this car_id/booking_id to allow review only for bookers.
// For simplicity this allows any logged-in user to review the car; you can add booking ownership checks later.

$stmt = mysqli_prepare($conn, "SELECT id, name, image FROM cars WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $car_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$car = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$car) {
    $_SESSION['error'] = 'Car not found.';
    redirect('vehicles.php');
}

$pageTitle = 'Leave a Review - ' . ($car['name'] ?? '');
$homeLink = 'index.php';
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';
?>
<main class="container" style="padding:6rem 0 3rem;">
    <h1>Leave a Review for <?php echo htmlspecialchars($car['name']); ?></h1>

    <div style="max-width:720px;">
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
            <?php
                $img = function_exists('resolve_image_url') ? resolve_image_url($car['image']) : (file_exists(__DIR__ . '/' . $car['image']) ? $car['image'] : null);
                if ($img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" style="width:120px;height:80px;object-fit:cover;border-radius:6px;">
                <?php else: ?>
                    <div style="width:120px;height:80px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#888;">No image</div>
                <?php endif;
            ?>
            <div>
                <h2 style="margin:0;"><?php echo htmlspecialchars($car['name']); ?></h2>
                <div style="color:#666;">Share your thoughts about your booking and our service.</div>
            </div>
        </div>

        <!-- CORRECTED: Form action now matches your handler process_reviews.php -->
        <form action="process_reviews.php" method="POST" style="max-width:600px;">
            <input type="hidden" name="car_id" value="<?php echo (int)$car_id; ?>">
            <input type="hidden" name="booking_id" value="<?php echo (int)$booking_id; ?>">

            <div class="input-group" style="margin-bottom:10px;">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" required style="padding:9px;border-radius:6px;border:1px solid #ddd;">
                    <option value="">Choose rating</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>

            <div class="input-group" style="margin-bottom:10px;">
                <label for="comment">Comment (optional)</label>
                <textarea id="comment" name="comment" rows="5" style="width:100%;padding:9px;border-radius:6px;border:1px solid #ddd;"></textarea>
            </div>

            <div>
                <button type="submit" class="auth-btn">Submit Review</button>
            </div>
        </form>
    </div>
</main>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>