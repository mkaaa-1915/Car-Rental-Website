<?php
require_once 'config.php';

$pageTitle = 'Cars - RentGo';
$homeLink = 'index.php';
$hideTrending = true;
$hideReviews = true;

include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

/* ----------------------
   Fetch filter options (type, gear & engine) with counts
   ---------------------- */
$types = [];
$gears = [];
$engines = [];

$q = "SELECT type, COUNT(*) AS cnt FROM cars WHERE type <> '' GROUP BY type ORDER BY cnt DESC, type ASC";
$res = mysqli_query($conn, $q);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $types[] = $r;
    }
}

$q = "SELECT gear, COUNT(*) AS cnt FROM cars WHERE gear <> '' GROUP BY gear ORDER BY cnt DESC, gear ASC";
$res = mysqli_query($conn, $q);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $gears[] = $r;
    }
}

$q = "SELECT engine, COUNT(*) AS cnt FROM cars WHERE engine <> '' GROUP BY engine ORDER BY cnt DESC, engine ASC";
$res = mysqli_query($conn, $q);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $engines[] = $r;
    }
}

/* ----------------------
   Read current filters
   ---------------------- */
$filterType = isset($_GET['type']) ? trim((string)$_GET['type']) : '';
$filterGear = isset($_GET['gear']) ? trim((string)$_GET['gear']) : '';
$filterEngine = isset($_GET['engine']) ? trim((string)$_GET['engine']) : '';

/* ----------------------
   Build and execute dynamic query with prepared statement
   ---------------------- */
$cars = [];
$conditions = [];
$params = '';
$values = [];

if ($filterType !== '') {
    $conditions[] = 'type = ?';
    $params .= 's';
    $values[] = $filterType;
}
if ($filterGear !== '') {
    $conditions[] = 'gear = ?';
    $params .= 's';
    $values[] = $filterGear;
}
if ($filterEngine !== '') {
    $conditions[] = 'engine = ?';
    $params .= 's';
    $values[] = $filterEngine;
}

$sql = "SELECT * FROM cars";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if (!empty($values)) {
        // bind params dynamically
        $bind_names = [];
        $bind_names[] = $params;
        // references required by bind_param
        for ($i = 0; $i < count($values); $i++) {
            $bind_names[] = & $values[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cars[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    // fallback direct query
    $q = "SELECT * FROM cars ORDER BY created_at DESC";
    $res = mysqli_query($conn, $q);
    if ($res) while ($r = mysqli_fetch_assoc($res)) $cars[] = $r;
}
?>

<section class="trend" id="home">
    <div class="home-content container">
        <div class="trend-img">
            <img src="img/rental-5.png" alt="Cars">
        </div>

        <form action="#" class="input-form" aria-label="Search rentals">
            <div class="input-box input-border">
                <span>Location</span>
                <input type="search" name="location" id="location-search" placeholder="Search Places">
            </div>
            <div class="input-box input-border">
                <span>Start</span>
                <input type="date" name="start-date" id="start-date">
            </div>
            <div class="input-box">
                <span>Return</span>
                <input type="date" name="return-date" id="return-date">
            </div>
            <i class='bx bx-search search-btn' role="button" aria-label="Search"></i>
        </form>
    </div>
</section>

<section class="search-section container" id="car-search-section" style="padding: 30px 0 25px;">
    <h2 class="section-title" style="margin-bottom: 12px; text-align: center;">Filter Available Rentals</h2>

    <form id="cars-filter-form" method="get" action="vehicles.php" style="display:flex;gap:14px;align-items:flex-end;justify-content:center;margin-bottom:18px;flex-wrap:wrap;">
        <div style="min-width:220px;">
            <label for="type-select" style="display:block;margin-bottom:6px;font-weight:600;">Type</label>
            <select id="type-select" name="type" style="width:100%;padding:9px;border-radius:8px;border:1px solid #ddd;background:#fff;">
                <option value="">All Types (<?php echo array_sum(array_column($types,'cnt')) ?: 0; ?>)</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo h($t['type']); ?>" <?php echo ($filterType === $t['type']) ? 'selected' : ''; ?>>
                        <?php echo h($t['type']); ?> (<?php echo (int)$t['cnt']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:220px;">
            <label for="gear-select" style="display:block;margin-bottom:6px;font-weight:600;">Gear</label>
            <select id="gear-select" name="gear" style="width:100%;padding:9px;border-radius:8px;border:1px solid #ddd;background:#fff;">
                <option value="">All (<?php echo array_sum(array_column($gears,'cnt')) ?: 0; ?>)</option>
                <?php foreach ($gears as $g): ?>
                    <option value="<?php echo h($g['gear']); ?>" <?php echo ($filterGear === $g['gear']) ? 'selected' : ''; ?>>
                        <?php echo h($g['gear']); ?> (<?php echo (int)$g['cnt']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:220px;">
            <label for="engine-select" style="display:block;margin-bottom:6px;font-weight:600;">Engine</label>
            <select id="engine-select" name="engine" style="width:100%;padding:9px;border-radius:8px;border:1px solid #ddd;background:#fff;">
                <option value="">All (<?php echo array_sum(array_column($engines,'cnt')) ?: 0; ?>)</option>
                <?php foreach ($engines as $e): ?>
                    <option value="<?php echo h($e['engine']); ?>" <?php echo ($filterEngine === $e['engine']) ? 'selected' : ''; ?>>
                        <?php echo h($e['engine']); ?> (<?php echo (int)$e['cnt']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex;gap:8px;align-items:center;">
            <button type="submit" class="auth-btn" style="padding:10px 16px;">Apply</button>
            <a href="vehicles.php" class="auth-btn" style="background:#888;padding:10px 16px;text-decoration:none;color:#fff;border-radius:8px;">Reset</a>
        </div>
    </form>

    <div style="max-width:1060px;margin: 0 auto;">
        <div class="heading" style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Most Rented Cars</h2>
            <div style="color:#666;font-size:0.95rem;"><?php echo count($cars); ?> result<?php echo (count($cars)===1 ? '' : 's'); ?></div>
        </div>

        <div class="rentals-content" id="car-list-container" style="margin-top:14px;">
            <?php if (empty($cars)): ?>
                <p style="text-align:center; width:100%;">No cars match the selected filters.</p>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <div class="rental-box car-item" data-name="<?php echo h($car['name']); ?>" data-car-id="<?php echo h((string)$car['id']); ?>">
                        <div class="rental-top">
                            <h3><?php echo h($car['type'] ?: 'Vehicle'); ?></h3>
                            <i class='bx bxs-heart'></i>
                        </div>
                        <?php
                            $imgPath = $car['image'] ?? '';
                            $resolved = function_exists('resolve_image_url') ? resolve_image_url($imgPath) : (file_exists(__DIR__ . '/' . $imgPath) ? $imgPath : null);
                        ?>
                        <?php if ($resolved): ?>
                            <img src="<?php echo h($resolved); ?>" alt="<?php echo h($car['name']); ?>">
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/cccccc/333333?text=No+Image" alt="No image">
                        <?php endif; ?>
                        <h2><?php echo h($car['name']); ?></h2>
                        <h4><?php echo h($car['gear'] ?: 'Automatic'); ?><?php if (!empty($car['engine'])): ?> / <?php echo h($car['engine']); ?><?php endif; ?></h4>

                        <!-- DESCRIPTION REMOVED HERE: descriptions will only show on car_details.php -->
                        <div class="price-btn">
                            <p>$<?php echo h((string)$car['price']); ?><span>/day</span></p>
                            <a href="car_details.php?id=<?php echo h((string)$car['id']); ?>" class="rental-btn">Rent</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>