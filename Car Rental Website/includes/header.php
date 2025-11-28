<?php
// Ensure config (db + session + helpers) is loaded for every page that includes header.
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'RentGo'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo isset($additionalCSS) ? $additionalCSS : 'style.css'; ?>">
    <link rel="shortcut icon" type="x-icon" href="img/logo.jpeg">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="app.js" defer></script>
    <script src="carData.js" defer></script>

    <style>
    .nav-user { display:flex; align-items:center; gap:10px; }
    .nav-user .username { padding:6px 10px; border-radius:999px; background:var(--main-color); color:var(--bg-color); font-weight:600; }
    .nav-user .logout-icon { padding:6px; border-radius:50%; background:var(--main-color); display:inline-flex; align-items:center; justify-content:center; }
    </style>
</head>
<body>
    <header>
        <div class="nav container">
            <a href="<?php echo isset($homeLink) ? $homeLink : 'index.php'; ?>" class="logo">RentGo<span>.</span></a>
            <div class="navbar">
                <a href="<?php echo isset($homeLink) ? $homeLink : 'index.php'; ?>" class="nav-link">Home</a>
                <?php if (!isset($hideTrending) || !$hideTrending): ?>
                    <a href="<?php echo isset($homeLink) ? $homeLink : 'index.php'; ?>#trending" class="nav-link">Trending</a>
                <?php endif; ?>
                <a href="vehicles.php" class="nav-link">Cars</a>
                <a href="aboutus.php" class="nav-link">About Us</a>
                <?php if (!isset($hideReviews) || !$hideReviews): ?>
                    <a href="<?php echo isset($homeLink) ? $homeLink : 'index.php'; ?>#reviews" class="nav-link">Reviews</a>
                <?php endif; ?>
            </div>

            <div class="nav-icons">
                <a href="<?php echo isset($homeLink) ? $homeLink : 'index.php'; ?>#car-search-section"><i class='bx bx-search'></i></a>

                <?php
                // config.php already started the session; get current user via helper
                $cu = function_exists('currentUser') ? currentUser() : ['id' => $_SESSION['id'] ?? null, 'username' => $_SESSION['username'] ?? null, 'status' => $_SESSION['status'] ?? null];
                $isLogged = !empty($cu['id']);
                if ($isLogged): ?>
                    <div class="nav-user" role="navigation" aria-label="User menu">
                        <a class="profile-link" href="user_profile.php" title="Your profile">
                            <span class="username"><?php echo e($cu['username'] ?? 'User'); ?></span>
                        </a>
                        <a href="logout.php" title="Log out" aria-label="Log out" class="logout-icon">
                            <i class='bx bx-log-out' style="color:var(--bg-color);font-size:18px;"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="#" id="user-auth-trigger"><i class='bx bxs-user user-icon'></i></a>
                <?php endif; ?>

                <!--<div class="menu-icon">
                    <div class="line1"></div>
                    <div class="line2"></div>
                    <div class="line3"></div>-->
                </div>
            </div>
        </div>
    </header>