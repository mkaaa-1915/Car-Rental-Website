<?php
require_once 'config.php';

// Only allow admin users
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'You must be an administrator to access that page.';
    redirect('login.php');
}

$pageTitle = 'Bookings & Reviews - Admin';
$homeLink = 'index.php';
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

// ----------------------
// 1) Bookings
// ----------------------
$bookings = [];
$q = "
    SELECT b.id AS booking_id, b.user_id, u.username, u.email,
           b.car_id, c.name AS car_name, c.price AS car_daily_price,
           b.pickup_date, b.return_date, b.total_price, b.created_at
    FROM bookings b
    LEFT JOIN users u ON u.id = b.user_id
    LEFT JOIN cars c ON c.id = b.car_id
    ORDER BY b.created_at DESC
    LIMIT 500
";
$res = mysqli_query($conn, $q);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) $bookings[] = $r;
}

// ----------------------
// 2) Reviews
// ----------------------
$reviews = [];
$q2 = "
    SELECT r.id AS review_id, r.user_id, u.username, r.car_id, c.name AS car_name, r.rating, r.comment, r.created_at
    FROM reviews r
    LEFT JOIN users u ON u.id = r.user_id
    LEFT JOIN cars c ON c.id = r.car_id
    ORDER BY r.created_at DESC
    LIMIT 200
";
$res2 = @mysqli_query($conn, $q2);
if ($res2) {
    while ($r = mysqli_fetch_assoc($res2)) $reviews[] = $r;
}

// ----------------------
// 3) Newsletter subscribers detection + fetch recent rows
// ----------------------
$foundTable = null;
$subscriberCount = 0;
$recentSubscribers = [];
$subscriber_note = '';

// Prefer the canonical name first
$candidateNames = [
    'newsletter_subscribers',
    'newsletter',
    'subscribers',
    'subscriptions',
    'newsletter_subscription',
    'newsletter_subscribe'
];

// try candidates
foreach ($candidateNames as $t) {
    $safe = mysqli_real_escape_string($conn, $t);
    $check = @mysqli_query($conn, "SHOW TABLES LIKE '{$safe}'");
    if ($check && mysqli_num_rows($check) > 0) {
        $foundTable = $t;
        break;
    }
}

// if not found, fallback to information_schema search
if (!$foundTable) {
    $sql = "
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND (table_name LIKE '%news%' OR table_name LIKE '%subscr%' OR table_name LIKE '%subscription%' OR table_name LIKE '%subscribers%')
        ORDER BY table_name ASC
        LIMIT 1
    ";
    $rs = @mysqli_query($conn, $sql);
    if ($rs && ($row = mysqli_fetch_assoc($rs))) {
        $foundTable = $row['table_name'];
    }
}

if ($foundTable) {
    $safeName = mysqli_real_escape_string($conn, $foundTable);

    // Count subscribers
    $cRes = @mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM `{$safeName}`");
    if ($cRes && ($r = mysqli_fetch_assoc($cRes))) {
        $subscriberCount = (int)$r['cnt'];
    }

    // Inspect columns to find email/created_at-like columns
    $cols = [];
    $colRes = @mysqli_query($conn, "SHOW COLUMNS FROM `{$safeName}`");
    if ($colRes) {
        while ($c = mysqli_fetch_assoc($colRes)) {
            $cols[] = $c['Field'];
        }
    }

    // Decide which column to use for email and created_at
    $emailCol = null;
    $createdCol = null;
    foreach (['email','subscriber_email','e_mail','mail'] as $cand) {
        if (in_array($cand, $cols, true)) { $emailCol = $cand; break; }
    }
    // fallback: pick first varchar/text column that's not id
    if (!$emailCol) {
        foreach ($cols as $c) {
            if (in_array($c, ['id','created_at','created','timestamp','unsubscribed_at'], true)) continue;
            // We cannot easily get the type without SHOW COLUMNS (we have it), but assume first non-id is email
            $emailCol = $c;
            break;
        }
    }

    foreach (['created_at','created','subscribed_at','timestamp','added_at'] as $cand) {
        if (in_array($cand, $cols, true)) { $createdCol = $cand; break; }
    }

    // Build query to fetch recent rows. Prefer ordering by createdCol if available, else by id desc.
    $orderBy = $createdCol ? "`{$createdCol}` DESC" : (in_array('id', $cols, true) ? "`id` DESC" : "1");
    $selectCols = '*'; // select everything so admin can inspect rows
    $subRes = @mysqli_query($conn, "SELECT {$selectCols} FROM `{$safeName}` ORDER BY {$orderBy} LIMIT 50");
    if ($subRes) {
        while ($row = mysqli_fetch_assoc($subRes)) $recentSubscribers[] = $row;
    } else {
        // query failed: give note
        $subscriber_note = 'Failed to read from table `' . h($foundTable) . '`: ' . mysqli_error($conn);
    }
} else {
    $subscriber_note = 'No newsletter table detected in the current database.';
}
?>

<main class="container" style="padding:6rem 0 3rem;">
    <h1>Bookings & Reviews</h1>
    <p>Overview of bookings, user reviews, and newsletter subscribers.</p>

    <section style="margin-top:20px;">
        <h2>Summary</h2>
        <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
            <div style="background:#fff;padding:12px 16px;border-radius:8px;border:1px solid #eee;">
                <div style="font-size:0.9rem;color:#666;">Total bookings</div>
                <div style="font-size:1.4rem;font-weight:700;"><?php echo (int)count($bookings); ?></div>
            </div>

            <div style="background:#fff;padding:12px 16px;border-radius:8px;border:1px solid #eee;">
                <div style="font-size:0.9rem;color:#666;">Total reviews</div>
                <div style="font-size:1.4rem;font-weight:700;"><?php echo (int)count($reviews); ?></div>
            </div>

            <div style="background:#fff;padding:12px 16px;border-radius:8px;border:1px solid #eee;">
                <div style="font-size:0.9rem;color:#666;">Newsletter subscribers</div>
                <div style="font-size:1.4rem;font-weight:700;"><?php echo (int)$subscriberCount; ?></div>
                <?php if ($foundTable): ?>
                    <div style="font-size:0.8rem;color:#666;margin-top:6px;">Detected table: <?php echo h($foundTable); ?></div>
                <?php endif; ?>
                <?php if ($subscriber_note): ?><div style="font-size:0.8rem;color:#b33;margin-top:6px;"><?php echo h($subscriber_note); ?></div><?php endif; ?>
            </div>
        </div>
    </section>

    <section style="margin-top:28px;">
        <h2>Bookings History</h2>
        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <div style="overflow:auto;border:1px solid #eee;border-radius:8px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#fafafa;">
                    <tr>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">ID</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">User</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">Car</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">Pickup</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">Return</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">Total</th>
                        <th style="padding:8px;border-bottom:1px solid #eee;text-align:left;">Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo (int)$b['booking_id']; ?></td>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo h($b['username'] ?? ''); ?> <br><small style="color:#666;"><?php echo h($b['email'] ?? ''); ?></small></td>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo h($b['car_name'] ?? ''); ?> <br><small style="color:#666;">ID: <?php echo (int)$b['car_id']; ?></small></td>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo h($b['pickup_date']); ?></td>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo h($b['return_date']); ?></td>
                            <td style="padding:8px;border-top:1px solid #fff;">$<?php echo number_format((float)$b['total_price'],2); ?></td>
                            <td style="padding:8px;border-top:1px solid #fff;"><?php echo h($b['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </section>

    <section style="margin-top:28px;">
        <h2>Recent Reviews</h2>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <?php foreach ($reviews as $r): ?>
                    <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #eee;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div><strong><?php echo h($r['username'] ?? ''); ?></strong> on <em><?php echo h($r['car_name'] ?? ''); ?></em></div>
                            <div style="font-weight:700;color:#333;"><?php echo (int)$r['rating']; ?>/5</div>
                        </div>
                        <div style="margin-top:8px;color:#333;"><?php echo h($r['comment']); ?></div>
                        <div style="margin-top:8px;color:#666;font-size:0.85rem;"><?php echo h($r['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section style="margin-top:28px;">
        <h2>Recent Newsletter Subscribers</h2>

        <?php if (!empty($recentSubscribers)): ?>
            <div style="display:flex;flex-direction:column;gap:8px;max-width:680px;">
                <?php foreach ($recentSubscribers as $s): ?>
                    <div style="padding:10px;border:1px solid #eee;border-radius:8px;background:#fff;">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                            <div style="font-weight:600;">
                                <?php
                                    // Try common email column names first
                                    if (isset($s['email'])) {
                                        echo h($s['email']);
                                    } elseif (isset($s['subscriber_email'])) {
                                        echo h($s['subscriber_email']);
                                    } else {
                                        // fallback to first non-id/ts column
                                        $printed = false;
                                        foreach ($s as $k => $v) {
                                            if (in_array($k, ['id','created_at','unsubscribed_at','timestamp'], true)) continue;
                                            echo h((string)$v);
                                            $printed = true;
                                            break;
                                        }
                                        if (!$printed) echo '(no displayable column)';
                                    }
                                ?>
                            </div>

                            <div style="color:#666;font-size:0.9rem;">
                                <?php
                                    if (isset($s['created_at'])) echo h($s['created_at']);
                                    elseif (isset($s['created'])) echo h($s['created']);
                                    elseif (isset($s['subscribed_at'])) echo h($s['subscribed_at']);
                                    else echo '';
                                ?>
                            </div>
                        </div>
                        <!-- dump full row for debugging if admin wants; commented out by default -->
                        <!-- <pre><?php // echo htmlspecialchars(print_r($s, true)); ?></pre> -->
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php echo $subscriber_note ?: 'No newsletter subscribers found.'; ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>