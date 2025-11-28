<?php
require_once 'config.php';

// Only allow admin users
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'Unauthorized.';
    redirect('login.php');
}

// Helper: redirect back to dashboard
function back($msg = null, $isError = false) {
    if ($msg) {
        if ($isError) $_SESSION['error'] = $msg;
        else $_SESSION['message'] = $msg;
    }
    redirect('dashboard.php');
}

// Validate CSRF token
$csrf = $_REQUEST['csrf_token'] ?? '';
if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    back('Invalid form submission (CSRF).', true);
}

// Ensure uploads directory exists
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
$uploadWebDir = 'uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Small helper to execute prepared statements safely
function stmt_execute_safe($stmt, $targetPath = null) {
    try {
        $ok = mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $e) {
        error_log('DB exception: ' . $e->getMessage());
        if (!empty($targetPath) && file_exists($targetPath)) @unlink($targetPath);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
    if (!$ok) {
        $err = mysqli_stmt_error($stmt);
        if (!empty($targetPath) && file_exists($targetPath)) @unlink($targetPath);
        return ['ok' => false, 'error' => $err];
    }
    return ['ok' => true];
}

$action = $_REQUEST['action'] ?? '';

/* ------------------ ADD ------------------ */
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $gear = trim($_POST['gear'] ?? '');
    $engine = trim($_POST['engine'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    // Validate gear values (only Automatic or Manual)
    if ($gear !== 'Automatic' && $gear !== 'Manual') {
        back('Invalid gear value. Allowed: Automatic, Manual.', true);
    }

    if ($name === '' || $gear === '' || $price < 0) {
        back('Please provide valid name, gear and price.', true);
    }

    // Image handling (required for add)
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        back('Please upload a valid image (JPG/PNG).', true);
    }

    $img = $_FILES['image'];
    if ($img['size'] > 2 * 1024 * 1024) back('Image too large (max 2MB).', true);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($img['tmp_name']);
    $ext = ($mime === 'image/jpeg') ? '.jpg' : (($mime === 'image/png') ? '.png' : '');
    if ($ext === '') back('Only JPG and PNG images allowed.', true);

    $basename = time() . '_' . bin2hex(random_bytes(6)) . $ext;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $basename;
    if (!move_uploaded_file($img['tmp_name'], $targetPath)) {
        back('Failed to move uploaded file. Check uploads/ folder permissions.', true);
    }

    $imageDbPath = $uploadWebDir . '/' . $basename;

    $stmt = mysqli_prepare($conn, "INSERT INTO cars (name, type, gear, price, image, engine, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        @unlink($targetPath);
        back('Internal error (prepare): ' . mysqli_error($conn), true);
    }
    mysqli_stmt_bind_param($stmt, 'sssisss', $name, $type, $gear, $price, $imageDbPath, $engine, $description);

    $res = stmt_execute_safe($stmt, $targetPath);
    if (!$res['ok']) {
        mysqli_stmt_close($stmt);
        back('Database insert failed: ' . $res['error'], true);
    }
    mysqli_stmt_close($stmt);

    back('Car added successfully.');
}

/* ------------------ UPDATE ------------------ */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) back('Invalid car ID.', true);

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $gear = trim($_POST['gear'] ?? '');
    $engine = trim($_POST['engine'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($gear !== 'Automatic' && $gear !== 'Manual') back('Invalid gear value.', true);
    if ($name === '' || $gear === '' || $price < 0) back('Please provide valid name, gear and price.', true);

    // Get existing car
    $stmt = mysqli_prepare($conn, "SELECT image FROM cars WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$existing) back('Car not found.', true);

    $imageDbPath = $existing['image'];

    // If new image uploaded, process it and remove old file
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img = $_FILES['image'];
        if ($img['size'] > 2 * 1024 * 1024) back('Image too large (max 2MB).', true);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($img['tmp_name']);
        $ext = ($mime === 'image/jpeg') ? '.jpg' : (($mime === 'image/png') ? '.png' : '');
        if ($ext === '') back('Only JPG and PNG images allowed.', true);

        $basename = time() . '_' . bin2hex(random_bytes(6)) . $ext;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $basename;
        if (!move_uploaded_file($img['tmp_name'], $targetPath)) {
            back('Failed to move uploaded file.', true);
        }

        // Delete old file if exists and inside uploads dir
        if (!empty($imageDbPath)) {
            $oldPath = __DIR__ . DIRECTORY_SEPARATOR . $imageDbPath;
            if (file_exists($oldPath) && strpos(realpath($oldPath), realpath($uploadDir)) === 0) {
                @unlink($oldPath);
            }
        }

        $imageDbPath = $uploadWebDir . '/' . $basename;
    }

    // Update record
    $stmt = mysqli_prepare($conn, "UPDATE cars SET name = ?, type = ?, gear = ?, price = ?, image = ?, engine = ?, description = ? WHERE id = ? LIMIT 1");
    if (!$stmt) back('Internal error (prepare).', true);

    // Correct type string: name(s), type(s), gear(s), price(i), image(s), engine(s), description(s), id(i)
    mysqli_stmt_bind_param($stmt, 'sssisssi', $name, $type, $gear, $price, $imageDbPath, $engine, $description, $id);

    $res = stmt_execute_safe($stmt, $targetPath ?? null);
    if (!$res['ok']) {
        mysqli_stmt_close($stmt);
        back('Database update failed: ' . $res['error'], true);
    }
    mysqli_stmt_close($stmt);

    back('Car updated successfully.');
}

/* ------------------ DELETE ------------------ */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id <= 0) back('Invalid car id.', true);

    // Get existing image to delete from disk
    $stmt = mysqli_prepare($conn, "SELECT image FROM cars WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Delete DB row
    $stmt = mysqli_prepare($conn, "DELETE FROM cars WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $res = stmt_execute_safe($stmt);
    if (!$res['ok']) {
        mysqli_stmt_close($stmt);
        back('Failed to delete car: ' . $res['error'], true);
    }
    mysqli_stmt_close($stmt);

    // Delete image file if present
    if (!empty($existing['image'])) {
        $oldPath = __DIR__ . DIRECTORY_SEPARATOR . $existing['image'];
        if (file_exists($oldPath) && strpos(realpath($oldPath), realpath($uploadDir)) === 0) {
            @unlink($oldPath);
        }
    }

    back('Car deleted.');
}

back('Invalid request.', true);
?>