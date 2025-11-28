<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to make a booking.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

if (isAdmin()) {
    $_SESSION['error'] = 'Administrators cannot create bookings.';
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$pickup_location = trim(filter_input(INPUT_POST, 'pickup_location', FILTER_SANITIZE_STRING));
$pickup_date = $_POST['pickup_date'] ?? '';
$return_date = $_POST['return_date'] ?? '';
$car_id = (int)($_POST['car_id'] ?? 0);
$uid = (int)($_SESSION['id'] ?? 0);

if (empty($pickup_location) || empty($pickup_date) || empty($return_date) || $car_id <= 0) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}

// Parse dates
try {
    $pickup_dt = new DateTimeImmutable($pickup_date);
    $return_dt = new DateTimeImmutable($return_date);
} catch (Exception $e) {
    $_SESSION['error'] = 'Invalid date format.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}

$pickup_ts = (int)$pickup_dt->setTime(0,0)->format('U');
$return_ts = (int)$return_dt->setTime(0,0)->format('U');
$today_ts = (int)(new DateTimeImmutable('today'))->format('U');

// Validate date ranges
if ($pickup_ts < $today_ts) {
    $_SESSION['error'] = 'Pickup date cannot be in the past.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}

if ($return_ts <= $pickup_ts) {
    $_SESSION['error'] = 'Return date must be after pickup date.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}

// Verify the car exists and get its engine type
$stmt = mysqli_prepare($conn, "SELECT id, engine FROM cars WHERE id = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['error'] = 'Internal error.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}
mysqli_stmt_bind_param($stmt, 'i', $car_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$car = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);
if (!$car) {
    $_SESSION['error'] = 'Car not found.';
    redirect('vehicles.php');
}

// Determine number of rental days
$diff = $pickup_dt->diff($return_dt);
$days = (int)$diff->days; // diff->days is integer number of days (return > pickup ensured)

if ($days <= 0) {
    $_SESSION['error'] = 'Invalid booking length.';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}

// Rate mapping (normalize engine value to lowercase for matching)
// Rates: gasoline, petrol, electric, hybrid
$engineVal = strtolower(trim((string)$car['engine'] ?? ''));

// If engine empty or unrecognized, default to gasoline
if ($engineVal === '') $engineVal = 'gasoline';

// Set rates per engine type
switch ($engineVal) {
    case 'petrol':
        $dayRate = 100;
        $weekRate = 300;
        $monthRate = 500;
        break;
    case 'electric':
        $dayRate = 50;
        $weekRate = 150;
        $monthRate = 250;
        break;
    case 'hybrid':
        $dayRate = 200;
        $weekRate = 400;
        $monthRate = 600;
        break;
    case 'diesel':
        $dayRate = 200;
        $weekRate = 500;
        $monthRate = 800;
        break;
    case 'gasoline':
    default:
        $dayRate = 200;
        $weekRate = 500;
        $monthRate = 800;
        break;
}

// Greedy breakdown: use months (30 days), weeks (7 days), then days
$remaining = $days;
$months = intdiv($remaining, 30);
$remaining -= $months * 30;
$weeks = intdiv($remaining, 7);
$remaining -= $weeks * 7;
$daysLeft = $remaining;

// Compute total price
$total_price = ($months * $monthRate) + ($weeks * $weekRate) + ($daysLeft * $dayRate);

// Insert booking with total_price
$stmt = mysqli_prepare($conn, "INSERT INTO bookings (user_id, car_id, pickup_date, return_date, total_price) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    $_SESSION['error'] = 'Failed to create booking (prepare).';
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}
$pickup_date_sql = $pickup_dt->format('Y-m-d');
$return_date_sql = $return_dt->format('Y-m-d');
mysqli_stmt_bind_param($stmt, 'iissi', $uid, $car_id, $pickup_date_sql, $return_date_sql, $total_price);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    $_SESSION['error'] = 'Failed to create booking: ' . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    redirect($_SERVER['HTTP_REFERER'] ?? "car_details.php?id={$car_id}");
}
$booking_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// Keep the success message
$_SESSION['message'] = 'Booking confirmed! Total: $' . number_format($total_price);

// Redirect to a confirmation page (not directly to review) so the review page is not opened automatically.
redirect("booking_success.php?booking_id={$booking_id}");
?>