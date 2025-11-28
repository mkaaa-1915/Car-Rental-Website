<?php
require_once 'config.php';

// Get car ID from URL
$car_id = (int)($_GET['id'] ?? 0);

if ($car_id <= 0) {
    $_SESSION['error'] = 'Invalid car selection.';
    redirect('vehicles.php');
}

// Fetch car details from database
$car = null;
$stmt = mysqli_prepare($conn, "SELECT * FROM cars WHERE id = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $car = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
}

if (!$car) {
    $_SESSION['error'] = 'Car not found.';
    redirect('vehicles.php');
}

$pageTitle = $car['name'] . ' - RentGo';
$homeLink = 'index.php';
$hideTrending = true;
$hideReviews = true;

include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

// Helper function for HTML escaping (guard to avoid redeclare)
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

// Compute min for date inputs (today)
$minDate = (new DateTimeImmutable('today'))->format('Y-m-d');

// Pass the engine to JS (escaped)
$engine_js = htmlspecialchars($car['engine'] ?? 'gasoline', ENT_QUOTES);
?>

<section class="trend" id="home">
    <div class="home-content container">
        <div class="trend-img">
            <?php
                $imgPath = $car['image'] ?? '';
                $resolved = function_exists('resolve_image_url') ? resolve_image_url($imgPath) : (file_exists(__DIR__ . '/' . $imgPath) ? $imgPath : null);
            ?>
            <?php if ($resolved): ?>
                <img src="<?php echo h($resolved); ?>" alt="<?php echo h($car['name']); ?>">
            <?php else: ?>
                <img src="https://placehold.co/600x400/cccccc/333333?text=No+Image" alt="No image">
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="container" style="padding: 2rem 0 3rem;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
        <!-- Car Details -->
        <div>
            <h1><?php echo h($car['name']); ?></h1>
            <div style="margin: 1rem 0;">
                <p><strong>Type:</strong> <?php echo h($car['type'] ?? 'Not specified'); ?></p>
                <p><strong>Transmission:</strong> <?php echo h($car['gear'] ?? 'Not specified'); ?></p>
                <p><strong>Engine:</strong> <?php echo h($car['engine'] ?? 'Not specified'); ?></p>
                <p><strong>Price:</strong> $<?php echo h((string)$car['price']); ?>/day</p>
            </div>

            <?php if (!empty($car['description'])): ?>
                <div style="margin-top: 1.5rem;">
                    <h3>Description</h3>
                    <p><?php echo h($car['description']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Booking Form -->
        <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 8px;">
            <h2>Book This Car</h2>

            <form id="booking-form" action="process_booking.php" method="POST">
                <input type="hidden" name="car_id" value="<?php echo h((string)$car['id']); ?>">
                <input type="hidden" name="total_price" id="total_price_input" value="0">

                <div class="input-group" style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Pickup Location</label>
                    <input type="text" name="pickup_location" required placeholder="City or address"
                           style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="input-group" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Pickup Date</label>
                        <input id="pickup_date" type="date" name="pickup_date" required
                               min="<?php echo $minDate; ?>"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Return Date</label>
                        <input id="return_date" type="date" name="return_date" required
                               min="<?php echo $minDate; ?>"
                               style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <div id="price-estimate" style="margin-top: 1rem; padding: 1rem; border-radius: 6px; background: #fff; border: 1px dashed #ddd; display: none;">
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">Estimated Price</div>
                    <div id="price-breakdown" style="color: #333; margin-bottom: 0.5rem;"></div>
                    <div id="price-total" style="font-weight: 700;"></div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="auth-btn" style="width: 100%; padding: 0.75rem; font-size: 1.1rem;">
                        Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
(function () {
    // Rates must match the server mapping (case-insensitive)
    const engine = ('<?php echo $engine_js; ?>' || 'gasoline').toLowerCase();

    let dayRate, weekRate, monthRate;
    switch (engine) {
        case 'petrol':
            dayRate = 100; weekRate = 300; monthRate = 500; break;
        case 'electric':
            dayRate = 50; weekRate = 150; monthRate = 250; break;
        case 'hybrid':
            dayRate = 200; weekRate = 400; monthRate = 600; break;
        case 'diesel':
            dayRate = 200; weekRate = 500; monthRate = 800; break;
        case 'gasoline':
        default:
            dayRate = 200; weekRate = 500; monthRate = 800; break;
    }

    const pickupInput = document.getElementById('pickup_date');
    const returnInput = document.getElementById('return_date');
    const estimateBox = document.getElementById('price-estimate');
    const breakdownEl = document.getElementById('price-breakdown');
    const totalEl = document.getElementById('price-total');
    const totalInput = document.getElementById('total_price_input');

    function parseDateVal(v) {
        if (!v) return null;
        const parts = v.split('-');
        if (parts.length !== 3) return null;
        return new Date(parseInt(parts[0],10), parseInt(parts[1],10)-1, parseInt(parts[2],10));
    }

    function daysBetween(a,b) {
        const msPerDay = 24*60*60*1000;
        const utcA = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
        const utcB = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
        return Math.floor((utcB - utcA) / msPerDay);
    }

    function computePrice(days) {
        let remaining = days;
        const months = Math.floor(remaining / 30);
        remaining -= months * 30;
        const weeks = Math.floor(remaining / 7);
        remaining -= weeks * 7;
        const daysLeft = remaining;
        const total = (months * monthRate) + (weeks * weekRate) + (daysLeft * dayRate);
        return { months, weeks, daysLeft, total };
    }

    function updateEstimate() {
        const p = parseDateVal(pickupInput.value);
        const r = parseDateVal(returnInput.value);
        if (!p || !r) {
            estimateBox.style.display = 'none';
            totalInput.value = '0';
            return;
        }
        const d = daysBetween(p, r);
        if (isNaN(d) || d <= 0) {
            estimateBox.style.display = 'none';
            totalInput.value = '0';
            return;
        }
        const res = computePrice(d);
        let lines = [];
        if (res.months > 0) lines.push(res.months + ' month(s) × $' + monthRate + ' = $' + (res.months * monthRate));
        if (res.weeks > 0) lines.push(res.weeks + ' week(s) × $' + weekRate + ' = $' + (res.weeks * weekRate));
        if (res.daysLeft > 0) lines.push(res.daysLeft + ' day(s) × $' + dayRate + ' = $' + (res.daysLeft * dayRate));
        if (lines.length === 0) lines.push('0 days');
        breakdownEl.innerHTML = lines.join('<br>');
        totalEl.innerText = 'Total: $' + res.total;
        estimateBox.style.display = 'block';
        totalInput.value = String(res.total);
    }

    if (pickupInput) pickupInput.addEventListener('change', updateEstimate);
    if (returnInput) returnInput.addEventListener('change', updateEstimate);

    document.addEventListener('DOMContentLoaded', updateEstimate);
})();
</script>

<?php
include 'includes/modal.php';
include 'includes/footer.php';
?>