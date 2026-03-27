<?php
session_start();
include '../database/db.php';

// Load user's wishlist for hotels
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wl_res = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'hotel'", [$_SESSION['user_id']]);
    if ($wl_res) {
        while ($wl = db_fetch_assoc($wl_res)) {
            $user_wishlist[] = (int)$wl['item_id'];
        }
    }
}

// Load hotel cities from DB for dropdown
$cities = [];
$city_res = db_query($conn, "SELECT DISTINCT city FROM hotels ORDER BY city ASC");
if ($city_res) {
    while ($c = db_fetch_assoc($city_res)) {
        $cities[] = $c['city'];
    }
}

$results = [];
$search_performed = false;
$city = '';
$check_in = date('Y-m-d');
$check_out = date('Y-m-d', strtotime('+1 day'));
$guests = 1;
$sort_price = $_POST['sort_price'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $city = $_POST['hotel_city'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];

    // Server-side validation: check-out must be after check-in
    if (strtotime($check_out) <= strtotime($check_in)) {
        echo "<script>alert('Check-out date must be after Check-in date.'); history.back();</script>";
        exit();
    }

    // --- Start Google Hotels (SerpApi) Integration ---
    $serp_api_key = "c549c4393c35299ce51ce61fbf77dd1917e9614c698f6761cbfd54a5748bb2ef";
    $query_params = [
        "engine" => "google_hotels",
        "q" => $city . " Hotels",
        "check_in_date" => $check_in,
        "check_out_date" => $check_out,
        "adults" => $guests,
        "currency" => "INR",
        "api_key" => $serp_api_key
    ];

    $api_url = "https://serpapi.com/search.json?" . http_build_query($query_params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$err) {
        $api_data = json_decode($response, true);
        if (isset($api_data['properties']) && is_array($api_data['properties'])) {
            foreach ($api_data['properties'] as $hotel) {
                $hotel_name = $hotel['name'] ?? 'Google Hotel';
                $hotel_price = $hotel['total_rate']['lowest'] ?? ($hotel['rate_per_night']['lowest'] ?? rand(2000, 8000));
                
                // Remove non-numeric characters from price if it's a string like "₹5,000"
                if (is_string($hotel_price)) {
                    $hotel_price = (int) preg_replace('/[^0-9]/', '', $hotel_price);
                }

                $rating = $hotel['overall_rating'] ?? 4.0;
                $description = $hotel['description'] ?? 'Stay at ' . $hotel_name;
                $thumbnail = $hotel['thumbnail'] ?? '../photos/hotel1.jpg';

                // Sync with local DB to allow booking
                $check_res = db_query($conn, "SELECT hotel_id FROM hotels WHERE name = ?", [$hotel_name]);
                $hotel_row = db_fetch_assoc($check_res);
                
                if (!$hotel_row) {
                    db_query($conn, "INSERT INTO hotels (name, city, address, description, price_per_night, rating, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                        [$hotel_name, $city, $city, $description, $hotel_price, $rating, $thumbnail]);
                }
            }
        }
    }

    $search_query = "SELECT * FROM hotels 
                     WHERE city LIKE ? 
                     OR name LIKE ?
                     ORDER BY rating DESC";
    
    $res = db_query($conn, $search_query, array("%$city%", "%$city%"));

    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $results[] = $row;
        }
    }
    $search_performed = true;

    // Apply Sorting
    if (!empty($results) && !empty($sort_price)) {
        usort($results, function($a, $b) use ($sort_price) {
            if ($sort_price === 'low_to_high') {
                return $a['price_per_night'] <=> $b['price_per_night'];
            } elseif ($sort_price === 'high_to_low') {
                return $b['price_per_night'] <=> $a['price_per_night'];
            }
            return 0;
        });
    }
} else {
    // Recommendation System: Show personalized or featured hotels
    $is_personalized = false;

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        // 1. Get cities from recent bookings
        $history_res = db_query($conn, "SELECT DISTINCT city FROM bookings b 
                                       JOIN hotels h ON b.reference_id = h.hotel_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'hotel' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_cities = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_cities[] = $h['city'];
        }

        // 2. Get cities from wishlist
        $wish_res = db_query($conn, "SELECT DISTINCT city FROM wishlist w 
                                    JOIN hotels h ON w.item_id = h.hotel_id 
                                    WHERE w.user_id = ? AND w.item_type = 'hotel' LIMIT 5", [$uid]);
        while ($w = db_fetch_assoc($wish_res)) {
            $pref_cities[] = $w['city'];
        }

        $pref_cities = array_unique(array_filter($pref_cities));

        if (!empty($pref_cities)) {
            $placeholders = implode(',', array_fill(0, count($pref_cities), '?'));
            $rec_query = "SELECT * FROM hotels 
                         WHERE city IN ($placeholders)
                         ORDER BY rating DESC LIMIT 6";
            $rec_res = db_query($conn, $rec_query, $pref_cities);
            if ($rec_res && mysqli_num_rows($rec_res) > 0) {
                while ($row = db_fetch_assoc($rec_res)) {
                    $results[] = $row;
                }
                $is_personalized = true;
            }
        }
    }

    if (empty($results)) {
        // Fallback: Show top-rated hotels across all cities
        $featured_res = db_query($conn, "SELECT * FROM hotels ORDER BY rating DESC LIMIT 6");
        if ($featured_res) {
            while ($row = db_fetch_assoc($featured_res)) {
                $results[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hotels | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .hotel-card {
            transition: all 0.3s ease;
        }
        .hotel-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="../index.php">
                <img src="../photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
                <span>Trip<span class="text-warning">Nexus</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#about-section">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact-section">Contact us</a></li>
                </ul>

                <div class="d-flex align-items-center gap-3 ms-lg-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-sm-inline">Welcome, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-fill text-dark fs-6"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li><a class="dropdown-item" href="../user/dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="../user/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../user/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="../user/login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                        <a href="../user/register.html" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../photos/Homepage-Background.avif'); background-size: cover; background-position: center; padding: 80px 0;">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Find Your Perfect Stay</h1>
            <p class="lead mb-0 opacity-75">Explore the best hotels at the most affordable prices</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="search-container mx-auto" style="margin-top: -50px;">
            <ul class="nav nav-pills custom-tabs mb-3 justify-content-center">
                <li class="nav-item"><a href="../flights/search_flight.php" class="nav-link"><i class="bi bi-airplane me-2"></i>Flight</a></li>
                <li class="nav-item"><a href="../buses/search_bus.php" class="nav-link"><i class="bi bi-bus-front me-2"></i>Bus</a></li>
                <li class="nav-item"><a href="../trains/search_train.php" class="nav-link"><i class="bi bi-train-front me-2"></i>Train</a></li>
                <li class="nav-item"><button class="nav-link active"><i class="bi bi-building me-2"></i>Hotel</button></li>
            </ul>

            <div class="modern-search-wrapper shadow-lg">
                <form method="POST" action="">
                    <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                        <span><i class="bi bi-info-circle"></i> Worldwide availability</span>
                        <span><i class="bi bi-info-circle"></i> Best price guarantee</span>
                    </div>
                    <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-building-fill text-warning me-1"></i>City / Hotel</label>
                            <select name="hotel_city" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select City</option>
                                <?php foreach ($cities as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($city === $c) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Check-in</label>
                            <input type="date" name="check_in" id="hotelCheckIn" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo htmlspecialchars($check_in); ?>" required>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Check-out</label>
                            <input type="date" name="check_out" id="hotelCheckOut" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo htmlspecialchars($check_out); ?>" required>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 120px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Guests</label>
                            <select name="guests" class="border-0 w-100 fw-bold" style="background: none;">
                                <?php for ($g = 1; $g <= 6; $g++): ?>
                                    <option value="<?php echo $g; ?>" <?php echo ($guests == $g) ? 'selected' : ''; ?>>
                                        <?php echo $g; ?> Guest<?php echo $g > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="search-input-group px-3 py-2" style="min-width: 140px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-filter text-warning me-1"></i>Price Sort</label>
                            <select name="sort_price" class="border-0 w-100 fw-bold" style="background: none;" onchange="this.form.submit()">
                                <option value="">Default</option>
                                <option value="low_to_high" <?php echo ($sort_price === 'low_to_high') ? 'selected' : ''; ?>>Low to High</option>
                                <option value="high_to_low" <?php echo ($sort_price === 'high_to_low') ? 'selected' : ''; ?>>High to Low</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-search rounded-pill px-4 py-3 ms-2 fw-bold shadow-lg">
                            Search Hotels
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($search_performed): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                <h4 class="fw-bold m-0"><i class="bi bi-building text-warning me-2"></i><?php echo count($results); ?> Hotels Found</h4>
                <div class="text-muted small">in <?php echo htmlspecialchars($city); ?></div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                <h4 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i><?php echo $is_personalized ? 'Recommended for You' : 'Featured Hotels'; ?></h4>
                <div class="text-muted small"><?php echo $is_personalized ? 'Based on your travel history' : 'Top rated stays across India'; ?></div>
            </div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-building text-muted display-1 opacity-25"></i>
                <p class="mt-3 text-muted">No hotels found for this location. Try a different city!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
            <?php foreach ($results as $hotel): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hotel-card overflow-hidden rounded-4">
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px; background: linear-gradient(45deg, #eee, #ddd) !important;">
                            <i class="bi bi-image fs-1 opacity-25"></i>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.75rem;"><i class="bi bi-star-fill me-1"></i><?php echo htmlspecialchars($hotel['rating']); ?></span>
                            </div>
                            <p class="text-muted small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($hotel['address']); ?>, <?php echo htmlspecialchars($hotel['city']); ?></p>
                            <p class="small text-secondary mb-4"><?php echo htmlspecialchars($hotel['amenities']); ?></p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Price per night</div>
                                    <h4 class="fw-bold mb-0 text-dark">₹<?php echo number_format($hotel['price_per_night'], 0); ?></h4>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php $is_fav = in_array((int)$hotel['hotel_id'], $user_wishlist); ?>
                                    <button class="btn <?php echo $is_fav ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm px-3 rounded-pill" onclick="toggleWishlist(this, 'hotel', <?php echo $hotel['hotel_id']; ?>, '<?php echo htmlspecialchars($hotel['name'], ENT_QUOTES); ?>')">
                                        <i class="bi <?php echo $is_fav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                    </button>
                                    <form action="../flights/booking.php" method="POST">
                                        <input type="hidden" name="service_type" value="hotel">
                                        <input type="hidden" name="reference_id" value="<?php echo $hotel['hotel_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $hotel['price_per_night']; ?>">
                                        <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($check_in); ?>">
                                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($hotel['name']); ?>">
                                        <button type="submit" class="btn btn-dark fw-bold px-4 rounded-pill">Book</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../flights/wishlist_toggle.js"></script>
    <script src="../public/script.js"></script>
</body>
</html>
