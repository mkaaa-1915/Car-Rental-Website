<?php
$pageTitle = 'About Us - RentGo';
$homeLink = 'index.php';
$hideTrending = true;
$hideReviews = true;

include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';
?>

<section class="trend" id="home">
    <div class="home-content container">
        <div class="trend-img">
            <img src="img/rental-7.png" alt="About RentGo">
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

<section class="about container">
    <div class="heading">
        <h2>About RentGo</h2>
    </div>

    <p>At RentGo, our vision is to become the leading car rental service by providing high-quality, affordable, and reliable vehicles. We aim to make transportation seamless for everyone.</p>
    <br>
    <p>Our goal is simple: <strong>to deliver the best rental experience</strong> by offering a wide selection of well-maintained cars, competitive pricing, and exceptional customer service.</p>
    <br>
    <p>Why do people always choose RentGo? We offer <strong>transparent pricing, no hidden fees, 24/7 customer support</strong>, and a hassle-free booking process.</p>
    <br>
    <p>Whether you need a car for a business trip, vacation, or special event, <strong>RentGo is your trusted partner in car rentals!</strong></p>
</section>

<?php
// Modal markup (login/signup) inserted before footer so it's inside body
include 'includes/modal.php';

// Footer includes site scripts (app.js, carData.js, etc.) and closes the page
include 'includes/footer.php';
?>