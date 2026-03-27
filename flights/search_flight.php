<?php
session_start();
include '../database/db.php';

// Load user's wishlist for flights
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wl_res = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'flight'", [$_SESSION['user_id']]);
    if ($wl_res) {
        while ($wl = db_fetch_assoc($wl_res)) {
            $user_wishlist[] = (int)$wl['item_id'];
        }
    }
}

// ---------------------------------------------------------------
// Load all airports from the database for dropdown
// ---------------------------------------------------------------
$airports = [];
$airport_res = db_query($conn, "SELECT airport_code, city FROM airports ORDER BY city ASC");
if ($airport_res) {
    while ($ap = db_fetch_assoc($airport_res)) {
        $airports[] = $ap;
    }
}

$results = [];
$search_performed = false;
$search_note = null;
$travel_date = date('Y-m-d');
$from = '';
$to = '';
$sort_price = $_POST['sort_price'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['departure_city'];
    $to = $_POST['arrival_city'];
    $travel_date = $_POST['departure_date'];

    // --- Start Aviationstack API Integration ---
    $api_key = '43d6ed9d3d1efaf98d93001258955183';
    $api_url = "http://api.aviationstack.com/v1/flights?access_key=$api_key&dep_iata=$from&arr_iata=$to";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $api_data = json_decode($response, true);

    if (isset($api_data['data']) && !empty($api_data['data'])) {
        foreach ($api_data['data'] as $f) {
            if (!isset($f['flight']['iata']) || empty($f['flight']['iata'])) continue;

            $f_num = $f['flight']['iata'];
            $airline_name = $f['airline']['name'];
            $dep_time = date('Y-m-d H:i:s', strtotime($f['departure']['scheduled']));
            $arr_time = date('Y-m-d H:i:s', strtotime($f['arrival']['scheduled']));

            // Sync with local database to allow booking
            $airline_res = db_query($conn, "SELECT airline_id FROM airlines WHERE airline_name = ?", [$airline_name]);
            $airline = db_fetch_assoc($airline_res);
            if (!$airline) {
                db_query($conn, "INSERT INTO airlines (airline_name, airline_logo) VALUES (?, ?)", [$airline_name, '../photos/indigo.jpg']);
                $airline_id = mysqli_insert_id($conn);
            } else {
                $airline_id = $airline['airline_id'];
            }

            $flight_res = db_query($conn, "SELECT flight_id FROM flights WHERE flight_number = ? AND departure_time = ?", [$f_num, $dep_time]);
            $flight = db_fetch_assoc($flight_res);
            
            if (!$flight) {
                $base_price = rand(4500, 15000); 
                db_query($conn, "INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                    [$f_num, $airline_id, $from, $to, $dep_time, $arr_time, $base_price, rand(30, 120), 'scheduled']);
                $flight_id = mysqli_insert_id($conn);
            } else {
                $flight_id = $flight['flight_id'];
            }

            $final_res = db_query($conn, "SELECT f.*, a.airline_name, a.airline_logo FROM flights f JOIN airlines a ON f.airline_id = a.airline_id WHERE f.flight_id = ?", [$flight_id]);
            if ($row = db_fetch_assoc($final_res)) {
                $row['rating'] = rand(35, 50) / 10;
                $results[] = $row;
            }
        }
    }

    // Fallback if API returned no data
    if (empty($results)) {
        // Primary search: exact date match
        $search_query = "SELECT f.*, a.airline_name, a.airline_logo 
                        FROM flights f 
                        JOIN airlines a ON f.airline_id = a.airline_id 
                        WHERE f.departure_airport = ? 
                        AND f.arrival_airport = ? 
                        AND DATE(f.departure_time) = ?
                        ORDER BY f.departure_time ASC";

        $res = db_query($conn, $search_query, array($from, $to, $travel_date));

        if ($res) {
            while ($row = db_fetch_assoc($res)) {
                $row['rating'] = rand(35, 50) / 10;
                $results[] = $row;
            }
        }
    }

    // Fallback: show flights on nearby dates for the same route
    if (empty($results)) {
        $fallback_query = "SELECT f.*, a.airline_name, a.airline_logo 
                           FROM flights f 
                           JOIN airlines a ON f.airline_id = a.airline_id 
                           WHERE f.departure_airport = ? 
                           AND f.arrival_airport = ?
                           AND DATE(f.departure_time) >= CURDATE()
                           ORDER BY ABS(DATEDIFF(DATE(f.departure_time), ?)) ASC, f.departure_time ASC";
        $fallback_res = db_query($conn, $fallback_query, array($from, $to, $travel_date));
        if ($fallback_res) {
            while ($row = db_fetch_assoc($fallback_res)) {
                $row['rating'] = rand(35, 50) / 10;
                $results[] = $row;
            }
        }
        if (!empty($results)) {
            $search_note = "No flights found on " . date('d M Y', strtotime($travel_date)) . ". Showing available flights on nearby dates for this route.";
        }
    }

    $search_performed = true;

    // Apply Sorting
    if (!empty($results) && !empty($sort_price)) {
        usort($results, function($a, $b) use ($sort_price) {
            if ($sort_price === 'low_to_high') {
                return $a['base_price'] <=> $b['base_price'];
            } elseif ($sort_price === 'high_to_low') {
                return $b['base_price'] <=> $a['base_price'];
            }
            return 0;
        });
    }
} else {
    // Recommendation System: Show personalized or popular flights
    $rec_flights = [];
    $is_personalized = false;

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        // 1. Get cities from recent bookings
        $history_res = db_query($conn, "SELECT DISTINCT departure_airport, arrival_airport FROM bookings b 
                                       JOIN flights f ON b.reference_id = f.flight_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'flight' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_cities = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_cities[] = $h['departure_airport'];
            $pref_cities[] = $h['arrival_airport'];
        }

        // 2. Get cities from wishlist
        $wish_res = db_query($conn, "SELECT DISTINCT departure_airport, arrival_airport FROM wishlist w 
                                    JOIN flights f ON w.item_id = f.flight_id 
                                    WHERE w.user_id = ? AND w.item_type = 'flight' LIMIT 5", [$uid]);
        while ($w = db_fetch_assoc($wish_res)) {
            $pref_cities[] = $w['departure_airport'];
            $pref_cities[] = $w['arrival_airport'];
        }

        $pref_cities = array_unique(array_filter($pref_cities));

        if (!empty($pref_cities)) {
            $city_list = "'" . implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($pref_cities), $conn), $pref_cities)) . "'";
            $placeholders = implode(',', array_fill(0, count($pref_cities), '?'));
            $rec_query = "SELECT f.*, a.airline_name, a.airline_logo 
                         FROM flights f 
                         JOIN airlines a ON f.airline_id = a.airline_id 
                         WHERE f.departure_time >= NOW() 
                         AND (f.departure_airport IN ($placeholders) OR f.arrival_airport IN ($placeholders))
                         ORDER BY f.departure_time ASC LIMIT 10";
            $rec_res = db_query($conn, $rec_query, array_merge($pref_cities, $pref_cities));
            if ($rec_res && mysqli_num_rows($rec_res) > 0) {
                while ($row = db_fetch_assoc($rec_res)) {
                    $row['rating'] = rand(35, 50) / 10;
                    $results[] = $row;
                }
                $is_personalized = true;
            }
        }
    }

    if (empty($results)) {
        // Fallback: Show top-rated or most affordable upcoming flights
        $popular_query = "SELECT f.*, a.airline_name, a.airline_logo 
                         FROM flights f 
                         JOIN airlines a ON f.airline_id = a.airline_id 
                         WHERE f.departure_time >= NOW()
                         ORDER BY f.base_price ASC, f.departure_time ASC 
                         LIMIT 10";
        $pop_res = db_query($conn, $popular_query);
        if ($pop_res) {
            while ($row = db_fetch_assoc($pop_res)) {
                $row['rating'] = rand(40, 50) / 10; // Higher rating for popular/featured
                $results[] = $row;
            }
        }
    }
    
    $search_note = $is_personalized ? "Recommended flights based on your travel history" : "Popular flights you might like";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Flights | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .flight-card {
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
        }

        .flight-card:hover {
            border-left: 5px solid #ffc107;
            transform: translateX(10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .airline-logo {
            width: 45px;
            height: 45px;
            object-fit: contain;
            border-radius: 8px;
        }

        .navbar {
            background-color: #0d2137 !important;
        }

        .btn-book {
            background-color: #0d2137;
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-book:hover {
            background-color: #1a3a5a;
            color: white;
            transform: translateY(-2px);
        }

        .price-tag {
            color: #0d2137;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .rating-badge {
            background-color: #fff8e1;
            color: #ffa000;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .search-bar-mini {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }

        .hero-section {
            background: linear-gradient(rgba(13, 33, 55, 0.8), rgba(13, 33, 55, 0.8)), url('../photos/Homepage-Background.avif');
            background-size: cover;
            background-position: center;
            padding: 80px 0 60px;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top px-4">
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

            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../user/dashboard.php" class="btn btn-warning rounded-pill px-4 fw-bold">My Dashboard</a>
                <?php else: ?>
                    <a href="../user/login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Header -->
    <div class="hero-section text-center text-white">
        <div class="container">
            <h1 class="fw-bold mb-3">Find Your Next Adventure</h1>
            <p class="lead mb-0 opacity-75">Search verified flights across all major airlines</p>
        </div>
    </div>

    <!-- Modern Search Section -->
    <div class="container mb-5">
        <div class="search-container mx-auto" style="margin-top: -50px;">
            <ul class="nav nav-pills custom-tabs mb-3 justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="pills-flights-tab">
                        <i class="bi bi-airplane me-2"></i>Flight
                    </button>
                </li>
                <li class="nav-item">
                    <a href="../index.php#pills-bus" class="nav-link"><i class="bi bi-bus-front me-2"></i>Bus</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#pills-train" class="nav-link"><i class="bi bi-train-front me-2"></i>Train</a>
                </li>
                <li class="nav-item">
                    <a href="../index.php#pills-hotels" class="nav-link"><i class="bi bi-building me-2"></i>Hotel</a>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" role="tabpanel">
                    <form method="POST" action="">
                        <div class="modern-search-wrapper shadow-lg">
                            <!-- Search Inputs -->
                            <div class="modern-search-bar p-2 d-flex align-items-center">
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                        <i class="bi bi-geo-alt-fill text-primary me-1"></i>From
                                    </label>
                                    <select name="departure_city" id="departureCity" class="border-0 w-100 fw-bold" style="background: none;" required>
                                        <option value="">Select</option>
                                        <?php foreach ($airports as $ap): ?>
                                            <option value="<?php echo $ap['airport_code']; ?>" <?php echo ($from === $ap['airport_code']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ap['city']) . ' (' . $ap['airport_code'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="search-swap-btn">
                                    <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapSearchLocations()">
                                        <i class="bi bi-arrow-left-right text-primary"></i>
                                    </button>
                                </div>

                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                        <i class="bi bi-geo-alt-fill text-primary me-1"></i>To
                                    </label>
                                    <select name="arrival_city" id="arrivalCity" class="border-0 w-100 fw-bold" style="background: none;" required>
                                        <option value="">Select</option>
                                        <?php foreach ($airports as $ap): ?>
                                            <option value="<?php echo $ap['airport_code']; ?>" <?php echo ($to === $ap['airport_code']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($ap['city']) . ' (' . $ap['airport_code'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="search-input-group border-end px-3 py-2" style="min-width: 180px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1">Departure Date</label>
                                    <input type="date" name="departure_date" id="flightDepartureDate" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo $travel_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div class="search-input-group border-end px-3 py-2" style="min-width: 180px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1">Price Filter</label>
                                    <select name="sort_price" id="sortPrice" class="border-0 w-100 fw-bold" style="background: none;" onchange="this.form.submit()">
                                        <option value="">Default Sort</option>
                                        <option value="low_to_high" <?php echo ($sort_price === 'low_to_high') ? 'selected' : ''; ?>>Price: Low to High</option>
                                        <option value="high_to_low" <?php echo ($sort_price === 'high_to_low') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                    Search Flights
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0"><?php echo !empty($search_note) && $search_performed ? 'Available Flights' : ($search_performed ? count($results) . ' Flights Found' : ($is_personalized ? 'Recommended for You' : 'Popular Flights')); ?></h4>
            <div class="text-muted small"><?php echo $search_performed ? 'Showing best prices for your route' : ($is_personalized ? 'Based on your travel history' : 'Recommended flights for your next trip'); ?></div>
        </div>

        <?php if (!empty($search_note)): ?>
            <div class="alert alert-info py-2">
                <i class="bi bi-info-circle me-1"></i> <?php echo htmlspecialchars($search_note); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($results) && $search_performed): ?>
            <div class="text-center py-5">
                <i class="bi bi-airplane-engines text-muted display-1"></i>
                <p class="mt-3 text-muted">No flights found for this route. Try different airports!</p>
            </div>
        <?php elseif (!empty($results)): ?>
            <?php foreach ($results as $flight): ?>
                <?php
                    // Calculate actual flight duration
                    $dep_ts = strtotime($flight['departure_time']);
                    $arr_ts = strtotime($flight['arrival_time']);
                    $duration_min = ($arr_ts - $dep_ts) / 60;
                    $dur_h = floor($duration_min / 60);
                    $dur_m = $duration_min % 60;
                    $flight_date = date('d M Y', $dep_ts);
                    $is_exact_date = (date('Y-m-d', $dep_ts) === $travel_date);
                ?>
                <div class="card border-0 shadow-sm mb-3 p-3 flight-card">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="<?php echo $flight['airline_logo']; ?>" class="airline-logo-img" alt="Logo" style="height: 45px;">
                            <div class="small fw-bold mt-2"><?php echo htmlspecialchars($flight['airline_name']); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                            <div class="rating-stars small">
                                <i class="bi bi-star-fill"></i> <?php echo $flight['rating']; ?>
                            </div>
                        </div>

                        <div class="col-md-2 text-center">
                            <h4 class="mb-0 fw-bold"><?php echo date('H:i', $dep_ts); ?></h4>
                            <div class="badge bg-light text-dark border mt-1"><?php echo $flight['departure_airport']; ?></div>
                            <div class="badge flight-date-badge <?php echo $is_exact_date ? 'bg-success' : 'bg-warning text-dark'; ?> mt-1">
                                <?php echo $flight_date; ?>
                            </div>
                        </div>

                        <div class="col-md-2 text-center position-relative">
                            <div class="small text-muted mb-1">Direct</div>
                            <div style="height: 2px; background: #dee2e6; width: 100%; position: relative;">
                                <i class="bi bi-airplane-fill position-absolute start-50 translate-middle text-primary" style="top: 50%;"></i>
                            </div>
                            <div class="small text-muted mt-1"><?php echo $dur_h . 'h ' . $dur_m . 'm'; ?></div>
                        </div>

                        <div class="col-md-2 text-center">
                            <h4 class="mb-0 fw-bold"><?php echo date('H:i', $arr_ts); ?></h4>
                            <div class="badge bg-light text-dark border mt-1"><?php echo $flight['arrival_airport']; ?></div>
                        </div>

                        <div class="col-md-4 text-end border-start">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-light text-success border"><?php echo $flight['available_seats']; ?> seats left</span>
                                </div>
                                <div>
                                    <div class="text-muted small mb-1">Price per adult</div>
                                    <h3 class="text-primary fw-bold mb-2">₹<?php echo number_format($flight['base_price'], 0); ?></h3>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <?php $is_fav = in_array((int)$flight['flight_id'], $user_wishlist); ?>
                                <button class="btn <?php echo $is_fav ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm px-3 rounded-pill" onclick="toggleWishlist(this, 'flight', <?php echo $flight['flight_id']; ?>, '<?php echo htmlspecialchars($flight['airline_name'], ENT_QUOTES); ?>')">
                                    <i class="bi <?php echo $is_fav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                </button>
                                <form action="booking.php" method="POST">
                                    <input type="hidden" name="service_type" value="flight">
                                    <input type="hidden" name="reference_id" value="<?php echo $flight['flight_id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $flight['base_price']; ?>">
                                    <input type="hidden" name="travel_date" value="<?php echo date('Y-m-d', $dep_ts); ?>">
                                    <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($flight['airline_name']); ?>">
                                    <button type="submit" class="btn btn-warning fw-bold rounded-pill px-4">Book Now</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Visual feedback when clicking search
        document.querySelector('form').onsubmit = function() {
            const btn = this.querySelector('.btn-search');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Searching...';
            btn.classList.add('disabled');
        };

        // Swap functionality
        function swapSearchLocations() {
            const fromSelect = document.getElementById('departureCity');
            const toSelect = document.getElementById('arrivalCity');
            const temp = fromSelect.value;
            fromSelect.value = toSelect.value;
            toSelect.value = temp;
        }
    </script>

    <footer class="bg-dark text-white text-center py-4">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved | <a href="admin/login.php" class="text-white-50 text-decoration-none small">Admin</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="wishlist_toggle.js"></script>
    <script src="script.js"></script>
</body>

</html>
