<?php
// user_profile.php - safer, robust version with clearer error handling and no duplicate helper declarations.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

// Ensure session active (config.php normally starts it)
if (session_status() === PHP_SESSION_NONE) session_start();

// If not logged in, redirect to home with message
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to view your profile.';
    redirect('index.php');
}

$uid = (int)($_SESSION['id'] ?? 0);
if ($uid <= 0) {
    $_SESSION['error'] = 'Invalid user session.';
    redirect('index.php');
}

$pageTitle = 'Your Profile - RentGo';
$homeLink = 'index.php';

// include header / messages
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

// Use shared escape helper if available; otherwise define a local guard (won't redeclare)
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

// Local helper: resolve image for profile list (non-conflicting with global resolve_image_url)
if (!function_exists('resolve_img_for_profile')) {
    function resolve_img_for_profile(?string $imgPath): ?string {
        if (!$imgPath) return null;
        $imgPath = trim($imgPath);
        if (preg_match('#^https?://#i', $imgPath)) return $imgPath;
        // candidate locations
        $cand = [
            __DIR__ . '/' . $imgPath,
            rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR) . '/' . ltrim($imgPath, '/'),
            __DIR__ . '/uploads/' . basename($imgPath),
            rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR) . '/uploads/' . basename($imgPath),
        ];
        foreach ($cand as $fs) {
            if ($fs && file_exists($fs) && is_file($fs)) {
                // prefer returning the stored relative path if that's what DB contains
                if (!preg_match('#^/#', $imgPath)) return $imgPath;
                return $imgPath;
            }
        }
        return null;
    }
}

// Fetch user row
$user = null;
$stmt = @mysqli_prepare($conn, "SELECT id, username, email, status, created_at FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    // Show debug-friendly message (admin only) and fall back to redirect
    error_log("user_profile: prepare failed: " . mysqli_error($conn));
    $_SESSION['error'] = 'There was a problem loading your account (DB prepare).';
    include 'includes/footer.php';
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $uid);
if (!mysqli_stmt_execute($stmt)) {
    error_log("user_profile: execute failed: " . mysqli_stmt_error($stmt));
    $_SESSION['error'] = 'There was a problem loading your account (DB execute).';
    mysqli_stmt_close($stmt);
    include 'includes/footer.php';
    exit;
}
$res = mysqli_stmt_get_result($stmt);
if ($res) $user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    $_SESSION['error'] = 'User not found. Please log in again.';
    redirect('index.php');
}

// Check bookings table exists
$bookings_exist = false;
$check = @mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
if ($check && mysqli_num_rows($check) > 0) $bookings_exist = true;

$bookings = [];
if ($bookings_exist) {
    $stmt = @mysqli_prepare($conn, "
        SELECT b.id AS booking_id, b.car_id, b.pickup_date, b.return_date, b.created_at AS booked_at,
               c.name AS car_name, c.image AS car_image, b.total_price
        FROM bookings b
        LEFT JOIN cars c ON c.id = b.car_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        if (mysqli_stmt_execute($stmt)) {
            $r = mysqli_stmt_get_result($stmt);
            if ($r) {
                while ($row = mysqli_fetch_assoc($r)) $bookings[] = $row;
            }
        } else {
            error_log("user_profile: bookings query execute failed: " . mysqli_stmt_error($stmt));
            echo "<div style='padding:12px;background:#fee;color:#900;border-radius:6px;margin:12px;'>Bookings query failed.</div>";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("user_profile: bookings prepare failed: " . mysqli_error($conn));
        echo "<div style='padding:12px;background:#fee;color:#900;border-radius:6px;margin:12px;'>Prepare failed for bookings.</div>";
    }
}
?>
<main class="container" style="padding: 6rem 0 3rem;">
    <h1>Your Profile</h1>

    <div style="display:flex;align-items:center;gap:12px;">
        <p style="margin:0;">Welcome back, <strong><?php echo h($user['username'] ?? $_SESSION['username']); ?></strong>.</p>

        <!-- Admin dashboard quick access button (visible only to admins) -->
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <a href="dashboard.php" class="auth-btn" style="margin-left:8px;padding:8px 12px;font-size:0.95rem;text-decoration:none;">
                Admin Dashboard
            </a>
        <?php endif; ?>
    </div>

    <section style="margin-top:20px;">
        <h2>Account Info</h2>
        <table style="width:100%;max-width:720px;border-collapse:collapse;">
            <tr><td style="padding:8px;font-weight:600;">Username</td><td style="padding:8px;"><?php echo h($user['username'] ?? ''); ?></td></tr>
            <tr><td style="padding:8px;font-weight:600;">Email</td><td style="padding:8px;"><?php echo h($user['email'] ?? ''); ?></td></tr>
            <tr><td style="padding:8px;font-weight:600;">Member since</td><td style="padding:8px;"><?php echo h($user['created_at'] ?? ''); ?></td></tr>
        </table>
    </section>

    <section style="margin-top:24px;">
        <h2>Your Rentals</h2>

        <?php if (!$bookings_exist): ?>
            <p>No bookings table found. To track rentals create the bookings table or use your booking flow to insert rows.</p>
        <?php else: ?>
            <p>You have <strong><?php echo (int)count($bookings); ?></strong> booking<?php echo (count($bookings)===1? '' : 's'); ?>.</p>
            <?php if (empty($bookings)): ?>
                <p>No bookings found. Browse cars and book one to see it here.</p>
            <?php else: ?>
                <div style="margin-top:12px;">
                    <?php foreach ($bookings as $b): ?>
                        <div style="display:flex;gap:12px;align-items:flex-start;padding:12px;border:1px solid #eee;border-radius:8px;margin-bottom:10px;">
                            <?php $imgUrl = resolve_img_for_profile($b['car_image'] ?? ''); ?>
                            <div style="width:120px;height:80px;flex:0 0 120px;">
                                <?php if ($imgUrl): ?>
                                    <img src="<?php echo h($imgUrl); ?>" alt="<?php echo h($b['car_name'] ?? 'Vehicle'); ?>" style="width:120px;height:80px;object-fit:cover;border-radius:6px;">
                                <?php else: ?>
                                    <div style="width:120px;height:80px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:6px;color:#888;">No image</div>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;">
                                <h3 style="margin:0 0 6px 0;"><?php echo h($b['car_name'] ?? 'Unknown vehicle'); ?></h3>
                                <div style="color:#666;font-size:0.95rem;margin-bottom:6px;">
                                    <?php if (!empty($b['pickup_date']) || !empty($b['return_date'])): ?>
                                        <strong>Pickup:</strong> <?php echo h($b['pickup_date'] ?: '-'); ?> &nbsp; <strong>Return:</strong> <?php echo h($b['return_date'] ?: '-'); ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($b['total_price'])): ?>
                                    <div style="font-size:0.95rem;color:#333;font-weight:600;">Total paid: $<?php echo h((string)$b['total_price']); ?></div>
                                <?php endif; ?>
                                <div style="font-size:0.9rem;color:#666;">Booked on: <?php echo h($b['booked_at'] ?? ''); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php
include 'includes/footer.php';
?>