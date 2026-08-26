<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Load user's wishlist for buses
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wl_res = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'bus'", [$_SESSION['user_id']]);
    if ($wl_res) {
        while ($wl = db_fetch_assoc($wl_res)) {
            $user_wishlist[] = (int)$wl['item_id'];
        }
    }
}

// Load bus locations from DB for dropdowns
$locations = [];
$loc_res = db_query($conn, "SELECT city_name FROM bus_locations ORDER BY city_name ASC");
if ($loc_res) {
    while ($loc = db_fetch_assoc($loc_res)) {
        $locations[] = $loc['city_name'];
    }
}

$results = [];
$search_performed = false;
$from = '';
$to = '';
$date = date('Y-m-d');
$trip_type = 'oneWay';
$sort_price = $_POST['sort_price'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trip_type = $_POST['trip_type'] ?? 'oneWay';
    $from = $_POST['bus_from'] ?? '';
    $to = $_POST['bus_to'] ?? '';
    $date = $_POST['bus_date'] ?? date('Y-m-d');

    $search_query = "SELECT * FROM buses 
                     WHERE from_location LIKE ? 
                     AND to_location LIKE ?
                     ORDER BY departure_time ASC";
    
    $res = db_query($conn, $search_query, array("%$from%", "%$to%"));

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
                return $a['price'] <=> $b['price'];
            } elseif ($sort_price === 'high_to_low') {
                return $b['price'] <=> $a['price'];
            }
            return 0;
        });
    }
} else {
    // Recommendation System: Show personalized or popular buses
    $is_personalized = false;

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        // 1. Get locations from recent bookings
        $history_res = db_query($conn, "SELECT DISTINCT from_location, to_location FROM bookings b 
                                       JOIN buses bu ON b.reference_id = bu.bus_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'bus' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_locs = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_locs[] = $h['from_location'];
            $pref_locs[] = $h['to_location'];
        }

        // 2. Get locations from wishlist
        $wish_res = db_query($conn, "SELECT DISTINCT from_location, to_location FROM wishlist w 
                                    JOIN buses bu ON w.item_id = bu.bus_id 
                                    WHERE w.user_id = ? AND w.item_type = 'bus' LIMIT 5", [$uid]);
        while ($w = db_fetch_assoc($wish_res)) {
            $pref_locs[] = $w['from_location'];
            $pref_locs[] = $w['to_location'];
        }

        $pref_locs = array_unique(array_filter($pref_locs));

        if (!empty($pref_locs)) {
            $placeholders = implode(',', array_fill(0, count($pref_locs), '?'));
            $rec_query = "SELECT * FROM buses 
                         WHERE departure_time >= NOW() 
                         AND (from_location IN ($placeholders) OR to_location IN ($placeholders))
                         ORDER BY departure_time ASC LIMIT 10";
            $rec_res = db_query($conn, $rec_query, array_merge($pref_locs, $pref_locs));
            if ($rec_res && mysqli_num_rows($rec_res) > 0) {
                while ($row = db_fetch_assoc($rec_res)) {
                    $results[] = $row;
                }
                $is_personalized = true;
            }
        }
    }

    if (empty($results)) {
        // Fallback: Show most booked or upcoming buses
        $popular_query = "SELECT * FROM buses 
                         WHERE departure_time >= NOW()
                         ORDER BY price ASC, departure_time ASC 
                         LIMIT 10";
        $pop_res = db_query($conn, $popular_query);
        if ($pop_res) {
            while ($row = db_fetch_assoc($pop_res)) {
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
    <title>Search Buses | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="../index.php">
                <img src="../photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
                <span>Trip<span class="text-warning">Nexus</span></span>
            </a>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#about-section">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact-section">Contact us</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 ms-lg-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../user/dashboard.php" class="btn btn-warning rounded-pill px-4 fw-bold">My Dashboard</a>
                    <?php else: ?>
                        <a href="../user/login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center text-white" style="background: linear-gradient(rgba(13, 33, 55, 0.8), rgba(13, 33, 55, 0.8)), url('../photos/Homepage-Background.avif'); background-size: cover; padding: 60px 0;">
        <div class="container">
            <h1 class="fw-bold mb-3">Bus Travel Made Easy</h1>
            <p class="lead mb-0 opacity-75">Book tickets for top operators across the country</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="search-container mx-auto" style="margin-top: -50px;">
            <ul class="nav nav-pills custom-tabs mb-3 justify-content-center">
                <li class="nav-item"><a href="../flights/search_flight.php" class="nav-link"><i class="bi bi-airplane me-2"></i>Flight</a></li>
                <li class="nav-item"><button class="nav-link active"><i class="bi bi-bus-front me-2"></i>Bus</button></li>
                <li class="nav-item"><a href="../trains/search_train.php" class="nav-link"><i class="bi bi-train-front me-2"></i>Train</a></li>
                <li class="nav-item"><a href="../hotels/search_hotel.php" class="nav-link"><i class="bi bi-building me-2"></i>Hotel</a></li>
            </ul>

            <div class="modern-search-wrapper shadow-lg">
                <form method="POST" action="">
                    <div class="modern-search-bar p-2 d-flex align-items-center">
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-bus-front-fill text-danger me-1"></i>From</label>
                            <select name="bus_from" id="busFrom" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select City</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo ($from === $loc) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search-swap-btn">
                            <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapBusLocations()">
                                <i class="bi bi-arrow-left-right text-danger"></i>
                            </button>
                        </div>

                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>To</label>
                            <select name="bus_to" id="busTo" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select City</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo ($to === $loc) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search-input-group border-end px-3 py-2" style="min-width: 180px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Departure Date</label>
                            <input type="date" name="bus_date" id="busDepartureDate" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo $date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Price Filter</label>
                            <select name="sort_price" id="sortPrice" class="border-0 w-100 fw-bold" style="background: none;" onchange="this.form.submit()">
                                <option value="">Default</option>
                                <option value="low_to_high" <?php echo ($sort_price === 'low_to_high') ? 'selected' : ''; ?>>Low to High</option>
                                <option value="high_to_low" <?php echo ($sort_price === 'high_to_low') ? 'selected' : ''; ?>>High to Low</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-danger btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                            Search Bus
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center my-4">
            <h4 class="fw-bold m-0"><i class="bi bi-bus-front text-danger me-2"></i><?php echo $search_performed ? count($results) . ' Buses Found' : ($is_personalized ? 'Recommended for You' : 'Popular Bus Routes'); ?></h4>
            <div class="text-muted small"><?php echo $search_performed ? 'Best prices for your route' : ($is_personalized ? 'Based on your travel history' : 'Top rated bus operators'); ?></div>
        </div>

        <?php if (empty($results) && $search_performed): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-bus-front text-muted display-1"></i>
                <p class="mt-3 text-muted">No buses found for this route.</p>
            </div>
        <?php elseif (!empty($results)): ?>
                <?php foreach ($results as $bus): ?>
                    <div class="card border-0 shadow-sm mb-3 p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($bus['operator_name']); ?></h5>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($bus['bus_type']); ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 fw-bold"><?php echo date('H:i', strtotime($bus['departure_time'])); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($bus['from_location']); ?></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="small text-muted mb-1">Duration</div>
                                <div style="height: 1px; background: #dee2e6; width: 100%;"></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 fw-bold"><?php echo date('H:i', strtotime($bus['arrival_time'])); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($bus['to_location']); ?></div>
                            </div>
                            <div class="col-md-3 text-end border-start">
                                <div class="text-muted small">Starting from</div>
                                <h4 class="text-danger fw-bold mb-2">₹<?php echo number_format($bus['price'], 0); ?></h4>
                                <div class="d-flex gap-2 justify-content-end">
                                    <?php $is_fav = in_array((int)$bus['bus_id'], $user_wishlist); ?>
                                    <button class="btn <?php echo $is_fav ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm px-3 rounded-pill" onclick="toggleWishlist(this, 'bus', <?php echo $bus['bus_id']; ?>, '<?php echo htmlspecialchars($bus['operator_name'], ENT_QUOTES); ?>')">
                                        <i class="bi <?php echo $is_fav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                    </button>
                                    <form action="../flights/booking.php" method="POST">
                                        <input type="hidden" name="service_type" value="bus">
                                        <input type="hidden" name="reference_id" value="<?php echo $bus['bus_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $bus['price']; ?>">
                                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($bus['operator_name']); ?>">
                                        <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4">Book Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
    </div>

    <script>
        function swapBusLocations() {
            const from = document.getElementById('busFrom');
            const to = document.getElementById('busTo');
            const temp = from.value;
            from.value = to.value;
            to.value = temp;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../flights/wishlist_toggle.js"></script>
    <script src="../public/script.js"></script>
</body>
</html>
