<?php
$pageTitle = 'RentGo';
$homeLink = 'index.php';
include 'includes/header.php';
include 'includes/messages.php';
include 'includes/modal_styles.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<section class="home" id="home">
    <div class="home-content container">
        <div class="home-img">
            <img src="img/home.png" alt="Home">
        </div>
        <form action="#" class="input-form" aria-label="Search form">
            <div class="input-box input-border">
                <span>Location</span>
                <input type="search" name="location" id="location" placeholder="Search Places">
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

<section class="search-section container" id="car-search-section" style="padding: 50px 0 25px;">
    <h2 class="section-title" style="margin-bottom: 25px; text-align: center;">Search Available Rentals</h2>
    <div class="search-box" style="position: relative; max-width: 600px; margin: 0 auto;">
        <input type="search" id="car-search-input" placeholder="Search car name (e.g., Rolls-Royce)"
               style="width: 100%; padding: 12px 45px 12px 15px; border: 2px solid var(--main-color); border-radius: 8px; font-size: 1rem; outline: none;">

        <i class='bx bx-search' style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--main-color); cursor: pointer;" id="search-button"></i>

        <ul id="autocomplete-results" style="list-style: none; padding: 0; margin: 0; position: absolute; top: 100%; width: 100%; background: #fff; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); z-index: 50; max-height: 200px; overflow-y: auto;">
        </ul>
    </div>
</section>

<section class="trending container" id="trending">
    <div class="heading">
        <h2>Trending Vehicles</h2>
    </div>
    <div class="trending-content">
        <div class="trend-box">
            <h4>Electric</h4>
            <img src="img/trend-1.png" alt="Taycan S">
            <h3>Taycan S</h3>
            <p>From $299/day</p>
        </div>
        <div class="trend-box">
            <h4>Electric</h4>
            <img src="img/trend-2.png" alt="Taycan">
            <h3>Taycan</h3>
            <p>From $299/day</p>
        </div>
        <div class="trend-box">
            <h4>Electric</h4>
            <img src="img/trend-3.png" alt="Taycan Turbo">
            <h3>Taycan Turbo</h3>
            <p>From $299/day</p>
        </div>
        <div class="trend-box">
            <h4>Electric</h4>
            <img src="img/trend-4.png" alt="Taycan Turismo">
            <h3>Taycan Turismo</h3>
            <p>From $299/day</p>
        </div>
    </div>
</section>

<section class="rentals container" id="rentals">
    <div class="heading">
        <h2>Our Top Rentals</h2>
        <p>Discover our top car rentals providing comfort, affordability, and convenience to make your next journey smooth and enjoyable!</p>
    </div>

    <div class="rentals-content" id="car-list-container">
        <div class="rental-box car-item" data-name="Rolls-Royce" data-car-id="6">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-1.png" alt="Rolls-Royce">
            <h2>Rolls-Royce</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=6" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Macan 4" data-car-id="7">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-2.png" alt="Macan 4">
            <h2>Macan 4</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=7" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Cayenne S E-Hybrid" data-car-id="8">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-3.png" alt="Cayenne S E-Hybrid">
            <h2>Cayenne S E-Hybrid</h2>
            <h4>Automatic/Manual</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=8" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Nissan GT-R" data-car-id="9">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-4.png" alt="Nissan GT-R">
            <h2>Nissan GT-R</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=9" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Panamera Turbo" data-car-id="10">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-5.png" alt="Panamera Turbo">
            <h2>Panamera Turbo</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=10" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Nissan Ariya" data-car-id="11">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-6.png" alt="Nissan Ariya">
            <h2>Nissan Ariya</h2>
            <h4>Automatic/Manual</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=11" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="Canyenne Turbo" data-car-id="12">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-7.png" alt="Canyenne Turbo">
            <h2>Canyenne Turbo</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=12" class="rental-btn">Rent</a>
            </div>
        </div>

        <div class="rental-box car-item" data-name="718 Boxster S" data-car-id="13">
            <div class="rental-top">
                <h3>Sedan</h3>
                <i class='bx bxs-heart'></i>
            </div>
            <img src="img/rental-8.png" alt="718 Boxster S">
            <h2>718 Boxster S</h2>
            <h4>Automatic</h4>
            <div class="price-btn">
                <p>$299<span>/day</span></p>
                <a href="car_details.php?id=13" class="rental-btn">Rent</a>
            </div>
        </div>
    </div>
</section>

<section class="team container" id="team">
    <div class="heading">
        <h2>Meet Our Experts</h2>
    </div>

    <div class="team-content">
        <div class="team-box">
            <img src="img/team-1.png" alt="Clark Kent">
            <h2>Clark Kent</h2>
            <span>Co-Head</span>
            <p>As a car rental company, we seek out top manufacturers to provide our customers with the best vehicles.</p>
        </div>
    </div>

    <div class="team-content">
        <div class="team-box">
            <img src="img/team-2.png" alt="Reed Richards">
            <h2>Reed Richards</h2>
            <span>CEO</span>
            <p>As a car rental company, we seek out top manufacturers to provide our customers with the best vehicles.</p>
        </div>
    </div>

    <div class="team-content">
        <div class="team-box">
            <img src="img/team-3.png" alt="Peter Parker">
            <h2>Peter Parker</h2>
            <span>Director</span>
            <p>As a car rental company, we seek out top manufacturers to provide our customers with the best vehicles.</p>
        </div>
    </div>
</section>

<section class="reviews" id="reviews">
    <div class="heading">
        <h2>Our Customers</h2>
    </div>

    <div class="reviews-content container">
        <div class="t-box">
            <div class="stars">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>
            </div>
            <p>"Fantastic service from start to finish! The car was clean, comfortable, and ready on time. The staff was friendly and helpful, and the prices were very reasonable. Highly recommend this company!"</p>
            <div class="profile">
                <img src="img/profile-1.png" alt="McQueen">
                <div class="profile-data">
                    <h3>McQueen</h3>
                    <span>Texas</span>
                </div>
            </div>
        </div>

        <div class="t-box">
            <div class="stars">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>
            </div>
            <p>"Fantastic service from start to finish! The car was clean, comfortable, and ready on time. The staff was friendly and helpful, and the prices were very reasonable. Highly recommend this company!"</p>
            <div class="profile">
                <img src="img/profile-2.png" alt="Mcloven">
                <div class="profile-data">
                    <h3>Mcloven</h3>
                    <span>New York</span>
                </div>
            </div>
        </div>

        <div class="t-box">
            <div class="stars">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>
            </div>
            <p>"Fantastic service from start to finish! The car was clean, comfortable, and ready on time. The staff was friendly and helpful, and the prices were very reasonable. Highly recommend this company!"</p>
            <div class="profile">
                <img src="img/profile-3.png" alt="Xhang">
                <div class="profile-data">
                    <h3>Xhang</h3>
                    <span>Texas</span>
                </div>
            </div>
        </div>

        <div class="t-box">
            <div class="stars">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>
            </div>
            <p>"Fantastic service from start to finish! The car was clean, comfortable, and ready on time. The staff was friendly and helpful, and the prices were very reasonable. Highly recommend this company!"</p>
            <div class="profile">
                <img src="img/profile-4.png" alt="Larry">
                <div class="profile-data">
                    <h3>Larry</h3>
                    <span>Tampa</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="newsletter container" id="newsletter">
    <h2>Subscribe and Get 20% Discount</h2>
    <p>Be the first to get the latest news, promotions and much more.</p>
    <form action="process_newsletter.php" method="POST">
        <input type="email" name="email" class="email" placeholder="RentGo@example.com" required>
        <input type="submit" value="Subscribe" class="s-btn">
    </form>
</section>

<?php
// include the modal markup once (server-side). app.js will bind to it.
include 'includes/modal.php';

// footer (includes closing body and other site footer content)
include 'includes/footer.php';
?>