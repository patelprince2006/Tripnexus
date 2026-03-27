<?php
session_start();
include '../database/db.php';

if (isset($_GET['autocomplete'])) {
    // --- RailRadar Autocomplete Integration (Keyless) ---
    $query = urlencode($_GET['autocomplete']);
    $url = "https://api.railradar.org/api/v1/search/trains?query=$query";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $formatted_data = ['Station' => []];
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $t) {
            $formatted_data['Station'][] = [
                'StationName' => $t['name'] ?? 'Unknown',
                'StationCode' => $t['number'] ?? 'N/A'
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($formatted_data);
    exit;
}

if (isset($_GET['schedule'])) {
    // Note: Schedule API still requires a source if using IndianRailAPI.
    // Since we are removing IndianRailAPI, we will provide a "Schedule Coming Soon" message or similar.
    header('Content-Type: application/json');
    echo json_encode(['Schedule' => []]);
    exit;
}

// Load user's wishlist for trains
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wl_res = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'train'", [$_SESSION['user_id']]);
    if ($wl_res) {
        while ($wl = db_fetch_assoc($wl_res)) {
            $user_wishlist[] = (int)$wl['item_id'];
        }
    }
}

// Load train stations from DB for dropdowns
$stations = [];
$st_res = db_query($conn, "SELECT station_code, station_name, city FROM train_stations ORDER BY city ASC");
if ($st_res) {
    while ($st = db_fetch_assoc($st_res)) {
        $stations[] = $st;
    }
}

$results = [];
$search_performed = false;
$from = '';
$to = '';
$date = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['train_from'] ?? '';
    $to = $_POST['train_to'] ?? '';
    $date = $_POST['train_date'] ?? date('Y-m-d');

    // --- IRCTC Insight API Integration (RapidAPI) ---
    $rapid_api_key = "c549c4393c35299ce51ce61fbf77dd1917e9614c698f6761cbfd54a5748bb2ef";
    $irctc_url = "https://irctc-insight.p.rapidapi.com/api/v1/train-details?from=$from&to=$to&date=" . date('Y-m-d', strtotime($date));
    
    $ch_irctc = curl_init();
    curl_setopt_array($ch_irctc, [
        CURLOPT_URL => $irctc_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: irctc-insight.p.rapidapi.com",
            "x-rapidapi-key: $rapid_api_key"
        ],
    ]);
    
    $irctc_response = curl_exec($ch_irctc);
    $irctc_err = curl_error($ch_irctc);
    curl_close($ch_irctc);
    
    if (!$irctc_err) {
        $irctc_data = json_decode($irctc_response, true);
        if (isset($irctc_data['data']) && is_array($irctc_data['data'])) {
            foreach ($irctc_data['data'] as $t) {
                $t_num = $t['trainNumber'] ?? ($t['number'] ?? '');
                $t_name = $t['trainName'] ?? ($t['name'] ?? '');
                
                if (empty($t_num)) continue;

                $dep_time = $date . ' ' . ($t['departureTime'] ?? '10:00') . ':00';
                $arr_time = $date . ' ' . ($t['arrivalTime'] ?? '18:00') . ':00';
                if (strtotime($arr_time) < strtotime($dep_time)) {
                    $arr_time = date('Y-m-d H:i:s', strtotime($arr_time . ' +1 day'));
                }

                $train_check = db_query($conn, "SELECT train_id FROM trains WHERE train_number = ? AND departure_time = ?", [$t_num, $dep_time]);
                $train_row = db_fetch_assoc($train_check);
                
                if (!$train_row) {
                    $price = rand(450, 3200);
                    db_query($conn, "INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$t_name, $t_num, $from, $to, $dep_time, $arr_time, $price, rand(15, 120)]);
                    $train_id = mysqli_insert_id($conn);
                } else {
                    $train_id = $train_row['train_id'];
                }

                $final_res = db_query($conn, "SELECT * FROM trains WHERE train_id = ?", [$train_id]);
                if ($row = db_fetch_assoc($final_res)) {
                    $results[] = $row;
                }
            }
        }
    }

    // --- RailRadar API Integration (Keyless URL Search) ---
    // Using the public search endpoint as requested
    $railradar_url = "https://api.railradar.org/api/v1/search/trains?query=" . urlencode($from);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $railradar_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    $api_data = json_decode($response, true);

    // Process API data if available
    if (isset($api_data['data']) && is_array($api_data['data'])) {
        foreach ($api_data['data'] as $t) {
            $t_num = $t['train_number'] ?? ($t['number'] ?? '');
            $t_name = $t['train_name'] ?? ($t['name'] ?? '');
            
            if (empty($t_num)) continue;

            $dep_time = $date . ' ' . str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
            $arr_time = date('Y-m-d H:i:s', strtotime($dep_time . ' +' . rand(2, 24) . ' hours'));

            // Sync with local database to allow booking
            $train_check = db_query($conn, "SELECT train_id FROM trains WHERE train_number = ? AND departure_time = ?", [$t_num, $dep_time]);
            $train_row = db_fetch_assoc($train_check);
            
            if (!$train_row) {
                $price = rand(450, 3200);
                db_query($conn, "INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                    [$t_name, $t_num, $from, $to, $dep_time, $arr_time, $price, rand(15, 120)]);
                $train_id = mysqli_insert_id($conn);
            } else {
                $train_id = $train_row['train_id'];
            }

            $final_res = db_query($conn, "SELECT * FROM trains WHERE train_id = ?", [$train_id]);
            if ($row = db_fetch_assoc($final_res)) {
                $results[] = $row;
            }
        }
    }

    // --- Primary Local Search (Always runs as fallback or primary) ---
    // Map comprehensive station codes to names for the local DB
    $station_map = [
        'NDLS' => 'Delhi', 'BCT' => 'Mumbai', 'SBC' => 'Bangalore', 
        'AMD' => 'Ahmedabad', 'BPL' => 'Bhopal', 'MAS' => 'Chennai',
        'HWH' => 'Howrah', 'HYB' => 'Hyderabad', 'PUNE' => 'Pune', 
        'JAI' => 'Jaipur', 'LKO' => 'Lucknow', 'PNBE' => 'Patna',
        'INDB' => 'Indore', 'VSKP' => 'Visakhapatnam', 'GHY' => 'Guwahati',
        'AGC' => 'Agra', 'BSB' => 'Varanasi', 'CNB' => 'Kanpur',
        'MYS' => 'Mysuru', 'CBE' => 'Coimbatore', 'ND' => 'Nadiad',
    ];
    $from_name = $station_map[$from] ?? $from;
    $to_name = $station_map[$to] ?? $to;

    $local_query = "SELECT * FROM trains 
                    WHERE (from_station LIKE ? OR from_station LIKE ?) 
                    AND (to_station LIKE ? OR to_station LIKE ?)
                    ORDER BY departure_time ASC";
    $local_res = db_query($conn, $local_query, array("%$from_name%", "%$from%", "%$to_name%", "%$to%"));

    if ($local_res) {
        while ($row = db_fetch_assoc($local_res)) {
            // Avoid duplicates if API already added it
            $exists = false;
            foreach ($results as $r) {
                if ($r['train_number'] == $row['train_number'] && $r['departure_time'] == $row['departure_time']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $results[] = $row;
            }
        }
    }
    
    if (empty($results)) {
        $search_note = "No live trains found via API. Showing matching results from our database.";
    }
    
    $search_performed = true;
} else {
    // Recommendation System: Show personalized or popular trains
    $is_personalized = false;

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        // 1. Get stations from recent bookings
        $history_res = db_query($conn, "SELECT DISTINCT from_station, to_station FROM bookings b 
                                       JOIN trains t ON b.reference_id = t.train_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'train' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_stations = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_stations[] = $h['from_station'];
            $pref_stations[] = $h['to_station'];
        }

        // 2. Get stations from wishlist
        $wish_res = db_query($conn, "SELECT DISTINCT from_station, to_station FROM wishlist w 
                                    JOIN trains t ON w.item_id = t.train_id 
                                    WHERE w.user_id = ? AND w.item_type = 'train' LIMIT 5", [$uid]);
        while ($w = db_fetch_assoc($wish_res)) {
            $pref_stations[] = $w['from_station'];
            $pref_stations[] = $w['to_station'];
        }

        $pref_stations = array_unique(array_filter($pref_stations));

        if (!empty($pref_stations)) {
            $placeholders = implode(',', array_fill(0, count($pref_stations), '?'));
            $rec_query = "SELECT * FROM trains 
                         WHERE departure_time >= NOW() 
                         AND (from_station IN ($placeholders) OR to_station IN ($placeholders))
                         ORDER BY departure_time ASC LIMIT 10";
            $rec_res = db_query($conn, $rec_query, array_merge($pref_stations, $pref_stations));
            if ($rec_res && mysqli_num_rows($rec_res) > 0) {
                while ($row = db_fetch_assoc($rec_res)) {
                    $results[] = $row;
                }
                $is_personalized = true;
            }
        }
    }

    if (empty($results)) {
        // Fallback: Show most booked or upcoming trains
        $popular_query = "SELECT * FROM trains 
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
    <title>Search Trains | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .train-card {
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
        }
        .train-card:hover {
            border-left: 5px solid #0dcaf0;
            transform: translateX(10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
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

    <div class="hero-section text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../photos/Homepage-Background.avif'); background-size: cover; background-position: center; padding: 80px 0;">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Track Your Journey</h1>
            <p class="lead mb-0 opacity-75">Search for trains and book your tickets instantly</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="search-container mx-auto" style="margin-top: -50px;">
            <!-- Tabs -->
            <ul class="nav nav-pills custom-tabs mb-3 justify-content-center" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a href="../flights/search_flight.php" class="nav-link"><i class="bi bi-airplane me-2"></i>Flight</a>
                </li>
                <li class="nav-item">
                    <a href="../buses/search_bus.php" class="nav-link"><i class="bi bi-bus-front me-2"></i>Bus</a>
                </li>
                <li class="nav-item">
                    <button class="nav-link active"><i class="bi bi-train-front me-2"></i>Train</button>
                </li>
                <li class="nav-item">
                    <a href="../hotels/search_hotel.php" class="nav-link"><i class="bi bi-building me-2"></i>Hotel</a>
                </li>
            </ul>

            <!-- Search Bar -->
            <div class="modern-search-wrapper shadow-lg">
                <form method="POST" action="">
                    <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                        <span><i class="bi bi-info-circle"></i> PNR Status</span>
                        <span><i class="bi bi-info-circle"></i> Live Train Status</span>
                    </div>
                    <div class="d-flex align-items-center bg-white rounded-pill shadow-sm p-2">
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-train-front-fill text-info me-1"></i>From Station</label>
                            <select name="train_from" id="trainFrom" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st['station_code']); ?>" <?php echo ($from == $st['station_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['city']) . " (" . htmlspecialchars($st['station_code']) . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-swap-btn">
                            <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapTrainLocations()">
                                <i class="bi bi-arrow-left-right text-info"></i>
                            </button>
                        </div>
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-info me-1"></i>To Station</label>
                            <select name="train_to" id="trainTo" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select Station</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st['station_code']); ?>" <?php echo ($to == $st['station_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['city']) . " (" . htmlspecialchars($st['station_code']) . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-input-group px-3 py-2" style="min-width: 200px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-event text-info me-1"></i>Journey Date</label>
                            <input type="date" name="train_date" class="border-0 w-100 fw-bold" value="<?php echo htmlspecialchars($date); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-info btn-search rounded-pill px-4 py-3 ms-2 fw-bold text-white shadow-lg">
                            Search Trains
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h4 class="fw-bold m-0"><i class="bi bi-train-front text-info me-2"></i><?php echo $search_performed ? count($results) . ' Trains Found' : ($is_personalized ? 'Recommended Trains' : 'Popular Trains'); ?></h4>
                <?php if (isset($search_note)): ?>
                    <div class="text-info small mt-1"><i class="bi bi-info-circle me-1"></i><?php echo $search_note; ?></div>
                <?php endif; ?>
            </div>
            <div class="text-muted small"><?php echo $search_performed ? (htmlspecialchars($from) . ' <i class="bi bi-arrow-right"></i> ' . htmlspecialchars($to)) : ($is_personalized ? 'Based on your travel history' : 'Recommended trains for your journey'); ?></div>
        </div>

        <?php if (empty($results) && $search_performed): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-train-front text-muted display-1"></i>
                <p class="mt-3 text-muted">No trains found for this route. Try a different route!</p>
            </div>
        <?php elseif (!empty($results)): ?>
                <?php foreach ($results as $train): ?>
                    <?php
                        $dep_ts = strtotime($train['departure_time']);
                        $arr_ts = strtotime($train['arrival_time']);
                        $duration = $arr_ts - $dep_ts;
                        $hours = floor($duration / 3600);
                        $minutes = floor(($duration % 3600) / 60);
                    ?>
                    <div class="card border-0 shadow-sm mb-3 p-3 train-card bg-white rounded-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($train['train_name']); ?></h5>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($train['train_number']); ?></span>
                                <div class="mt-2">
                                    <button class="btn btn-link btn-sm p-0 text-info text-decoration-none" onclick="viewSchedule('<?php echo $train['train_number']; ?>', '<?php echo htmlspecialchars($train['train_name'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-calendar3 me-1"></i>View Schedule
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 fw-bold"><?php echo date('H:i', $dep_ts); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($train['from_station']); ?></div>
                                <span class="badge bg-info bg-opacity-10 text-info mt-1" style="font-size: 0.72rem;"><?php echo date('d M Y', $dep_ts); ?></span>
                            </div>
                            <div class="col-md-2 text-center position-relative px-4">
                                <div class="small text-muted mb-1"><?php echo "{$hours}h {$minutes}m"; ?></div>
                                <div style="height: 2px; background: #dee2e6; width: 100%; position: relative;">
                                    <i class="bi bi-train-front-fill position-absolute start-50 translate-middle text-info" style="top: 50%; font-size: 0.8rem;"></i>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0 fw-bold"><?php echo date('H:i', $arr_ts); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($train['to_station']); ?></div>
                                <span class="badge bg-light text-muted mt-1" style="font-size: 0.72rem;"><?php echo date('d M Y', $arr_ts); ?></span>
                            </div>
                            <div class="col-md-3 text-end border-start">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-success border"><?php echo $train['available_seats']; ?> seats</span>
                                    <div>
                                        <div class="text-muted small">Starting from</div>
                                        <h4 class="text-info fw-bold mb-2">
                                            ₹<?php echo number_format($train['price'], 0); ?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <?php $is_fav = in_array((int)$train['train_id'], $user_wishlist); ?>
                                    <button class="btn <?php echo $is_fav ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm px-3 rounded-pill" onclick="toggleWishlist(this, 'train', <?php echo $train['train_id']; ?>, '<?php echo htmlspecialchars($train['train_name'], ENT_QUOTES); ?>')">
                                        <i class="bi <?php echo $is_fav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                    </button>
                                    <form action="../flights/booking.php" method="POST" class="flex-grow-1">
                                        <input type="hidden" name="service_type" value="train">
                                        <input type="hidden" name="reference_id" value="<?php echo $train['train_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $train['price']; ?>">
                                        <input type="hidden" name="travel_date" value="<?php echo date('Y-m-d', $dep_ts); ?>">
                                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($train['train_name']); ?>">
                                        <button type="submit" class="btn btn-info fw-bold rounded-pill px-4 text-white w-100">Book Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php if (!$search_performed): ?>
            <div class="text-center py-5 mt-5">
                <i class="bi bi-train-front text-muted display-1 opacity-25"></i>
                <p class="mt-3 text-muted">Select your stations and date above to search for trains.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved</p>
    </footer>

    <!-- Train Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="scheduleModalLabel"><i class="bi bi-calendar3 me-2"></i>Train Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="scheduleLoading" class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Fetching latest schedule...</p>
                    </div>
                    <div id="scheduleContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light small text-uppercase fw-bold text-muted">
                                    <tr>
                                        <th class="ps-4">Station</th>
                                        <th>Arr.</th>
                                        <th>Dep.</th>
                                        <th>Dist.</th>
                                        <th class="pe-4">Day</th>
                                    </tr>
                                </thead>
                                <tbody id="scheduleTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="scheduleError" class="text-center py-5 text-danger" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill display-4"></i>
                        <p class="mt-2">Failed to load schedule. Please try again later.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewSchedule(trainNumber, trainName) {
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            document.getElementById('scheduleModalLabel').textContent = `${trainName} (${trainNumber}) - Schedule`;
            
            document.getElementById('scheduleLoading').style.display = 'block';
            document.getElementById('scheduleContent').style.display = 'none';
            document.getElementById('scheduleError').style.display = 'none';
            
            modal.show();

            fetch(`search_train.php?schedule=${trainNumber}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('scheduleLoading').style.display = 'none';
                    if (data.Schedule && data.Schedule.length > 0) {
                        const tableBody = document.getElementById('scheduleTableBody');
                        tableBody.innerHTML = '';
                        data.Schedule.forEach(stop => {
                            const row = `
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <div class="text-dark">${stop.StationName}</div>
                                        <div class="small text-muted">${stop.StationCode}</div>
                                    </td>
                                    <td>${stop.ArrivalTime === '00:00' ? 'Starts' : stop.ArrivalTime}</td>
                                    <td>${stop.DepartureTime === '00:00' ? 'Ends' : stop.DepartureTime}</td>
                                    <td>${stop.Distance} km</td>
                                    <td class="pe-4">Day ${stop.Day}</td>
                                </tr>
                            `;
                            tableBody.insertAdjacentHTML('beforeend', row);
                        });
                        document.getElementById('scheduleContent').style.display = 'block';
                    } else {
                        document.getElementById('scheduleError').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error fetching schedule:', err);
                    document.getElementById('scheduleLoading').style.display = 'none';
                    document.getElementById('scheduleError').style.display = 'block';
                });
        }

        function swapTrainLocations() {
            const from = document.getElementById('trainFrom');
            const to = document.getElementById('trainTo');
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
