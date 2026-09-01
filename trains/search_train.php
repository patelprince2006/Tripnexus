<?php
session_start();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../database/razorpay_config.php';

if (isset($_GET['live_status'])) {
    $train_no = $_GET['live_status'];
    $journey_date = $_GET['date'] ?? date('Ymd');
    
    // Use RapidAPI Indian Railway IRCTC API for Live Status
    $url = "https://indian-railway-irctc.p.rapidapi.com/api/trains/v1/train/status?train_number=$train_no&departure_date=$journey_date&isH5=true&client=web";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . RAPIDAPI_HOST,
            "x-rapidapi-key: " . RAPIDAPI_KEY,
            "x-rapid-api: " . RAPIDAPI_DB_HEADER
        ],
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    header('Content-Type: application/json');
    if ($err) {
        echo json_encode(["status" => "error", "message" => "Connection Error: $err"]);
    } else {
        echo $response;
    }
    exit;
}

if (isset($_GET['schedule'])) {
    $train_no = $_GET['schedule'];
    
    // Use RapidAPI Indian Railway IRCTC API for Train Search/Info
    $url = "https://indian-railway-irctc.p.rapidapi.com/api/trains-search/v1/train/$train_no?isH5=true&client=web";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: " . RAPIDAPI_HOST,
            "x-rapidapi-key: " . RAPIDAPI_KEY,
            "x-rapid-api: " . RAPIDAPI_DB_HEADER
        ],
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    header('Content-Type: application/json');
    if ($err) {
        echo json_encode(["status" => "error", "message" => "Connection Error: $err"]);
    } else {
        echo $response;
    }
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
$sort_price = $_POST['sort_price'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['train_from'] ?? '';
    $to = $_POST['train_to'] ?? '';
    $date = $_POST['train_date'] ?? date('Y-m-d');
    $train_no = $_POST['train_no'] ?? '';

    // --- Search by Train Number (Using the new API as requested) ---
    if (!empty($train_no)) {
        $url = "https://indian-railway-irctc.p.rapidapi.com/api/trains-search/v1/train/$train_no?isH5=true&client=web";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: " . RAPIDAPI_HOST,
                "x-rapidapi-key: " . RAPIDAPI_KEY,
                "x-rapid-api: " . RAPIDAPI_DB_HEADER
            ],
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if (!$err) {
            $api_data = json_decode($response, true);
            if (isset($api_data['data']) && is_array($api_data['data'])) {
                $t = $api_data['data'][0] ?? null;
                if ($t) {
                    $t_num = $t['train_number'] ?? $train_no;
                    $t_name = $t['train_name'] ?? 'Unknown Train';
                    $src = $t['from_station_code'] ?? 'Unknown';
                    $dest = $t['to_station_code'] ?? 'Unknown';
                    
                    $dep_time = $date . ' ' . ($t['departure_time'] ?? '10:00') . ':00';
                    $arr_time = date('Y-m-d H:i:s', strtotime($dep_time . ' + ' . ($t['duration'] ?? '8') . ' hours'));

                    $train_check = db_query($conn, "SELECT train_id FROM trains WHERE train_number = ? AND departure_time = ?", [$t_num, $dep_time]);
                    $train_row = db_fetch_assoc($train_check);
                    
                    if (!$train_row) {
                        $price = rand(450, 3200);
                        db_query($conn, "INSERT INTO trains (train_name, train_number, from_station, to_station, departure_time, arrival_time, price, available_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                            [$t_name, $t_num, $src, $dest, $dep_time, $arr_time, $price, rand(15, 120)]);
                        $train_id = db_insert_id($conn);
                        if (!$train_id) {
                            $train_id = db_fetch_value(db_query($conn, "SELECT MAX(train_id) FROM trains"), 0, 0) ?: 1;
                        }
                    } else {
                        $train_id = $train_row['train_id'];
                        db_query($conn, "UPDATE trains SET train_name = ?, from_station = ?, to_station = ?, arrival_time = ? WHERE train_id = ?", 
                            [$t_name, $src, $dest, $arr_time, $train_id]);
                    }

                    $final_res = db_query($conn, "SELECT * FROM trains WHERE train_id = ?", [$train_id]);
                    if ($row = db_fetch_assoc($final_res)) {
                        $results[] = $row;
                    }
                }
            }
        }
    }

    // --- Primary Local Search ---
    if (empty($results)) {
        $from_details = db_fetch_assoc(db_query($conn, "SELECT city, station_name FROM train_stations WHERE station_code = ?", [$from]));
        $to_details = db_fetch_assoc(db_query($conn, "SELECT city, station_name FROM train_stations WHERE station_code = ?", [$to]));
        
        $from_name = $from_details ? $from_details['city'] : $from;
        $from_st_name = $from_details ? $from_details['station_name'] : $from;
        $to_name = $to_details ? $to_details['city'] : $to;
        $to_st_name = $to_details ? $to_details['station_name'] : $to;

        $local_query = "SELECT * FROM trains 
                        WHERE (from_station LIKE ? OR from_station LIKE ? OR from_station LIKE ?) 
                        AND (to_station LIKE ? OR to_station LIKE ? OR to_station LIKE ?)
                        ORDER BY departure_time ASC";
        $local_res = db_query($conn, $local_query, array("%$from_name%", "%$from%", "%$from_st_name%", "%$to_name%", "%$to%", "%$to_st_name%"));

        if ($local_res) {
            while ($row = db_fetch_assoc($local_res)) {
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
    }
    
    if (empty($results)) {
        $search_note = "No live trains found via API. Showing matching results from our database.";
    }
    
    $search_performed = true;

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
    $is_personalized = false;
    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        $history_res = db_query($conn, "SELECT DISTINCT from_station, to_station FROM bookings b 
                                       JOIN trains t ON b.reference_id = t.train_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'train' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_stations = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_stations[] = $h['from_station'];
            $pref_stations[] = $h['to_station'];
        }

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
                    <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                        <div class="search-input-group border-end px-3 py-2" style="flex: 1; min-width: 200px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-search text-info me-1"></i>Train Number</label>
                            <input type="text" name="train_no" placeholder="e.g. 12051" class="border-0 w-100 fw-bold" style="background: none;">
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="flex: 1; min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">From Station</label>
                            <select name="train_from" id="trainFrom" class="border-0 w-100 fw-bold" style="background: none;">
                                <option value="">Source</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo $st['station_code']; ?>" <?php echo ($from === $st['station_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['city']); ?> (<?php echo $st['station_code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="flex: 1; min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">To Station</label>
                            <select name="train_to" id="trainTo" class="border-0 w-100 fw-bold" style="background: none;">
                                <option value="">Destination</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo $st['station_code']; ?>" <?php echo ($to === $st['station_code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['city']); ?> (<?php echo $st['station_code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Date</label>
                            <input type="date" name="train_date" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo htmlspecialchars($date); ?>">
                        </div>
                        <button type="submit" class="btn btn-info btn-search rounded-pill px-4 py-3 ms-2 fw-bold text-white shadow-lg">
                            Search
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
                                <div class="mt-2 d-flex gap-2">
                                    <button class="btn btn-link btn-sm p-0 text-info text-decoration-none" onclick="viewSchedule('<?php echo $train['train_number']; ?>', '<?php echo htmlspecialchars($train['train_name'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-calendar3 me-1"></i>View Schedule
                                    </button>
                                    <button class="btn btn-link btn-sm p-0 text-success text-decoration-none" onclick="checkLiveStatus('<?php echo $train['train_number']; ?>', '<?php echo htmlspecialchars($train['train_name'], ENT_QUOTES); ?>', '<?php echo date('Ymd', $dep_ts); ?>')">
                                        <i class="bi bi-geo-alt-fill me-1"></i>Live Status
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

    <!-- Live Status Modal -->
    <div class="modal fade" id="liveStatusModal" tabindex="-1" aria-labelledby="liveStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="liveStatusModalLabel"><i class="bi bi-geo-alt-fill me-2"></i>Live Train Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="liveStatusLoading" class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Fetching live location...</p>
                    </div>
                    <div id="liveStatusContent" style="display: none;">
                        <div class="card bg-light border-0 rounded-3 mb-3">
                            <div class="card-body">
                                <h6 class="text-muted small text-uppercase fw-bold mb-2">Current Position</h6>
                                <h5 id="currentStation" class="fw-bold text-success mb-1">Station Name</h5>
                                <p id="statusMessage" class="mb-0 small">On time / Delayed by ...</p>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6 border-end">
                                <div class="text-muted small">Last Station</div>
                                <div id="lastStation" class="fw-bold">Station A</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Next Station</div>
                                <div id="nextStation" class="fw-bold">Station B</div>
                            </div>
                        </div>
                    </div>
                    <div id="liveStatusError" class="text-center py-4 text-danger" style="display: none;">
                        <i class="bi bi-exclamation-circle-fill display-5"></i>
                        <p class="mt-2" id="liveStatusErrorMessage">Live status currently unavailable for this train.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkLiveStatus(trainNumber, trainName, date) {
            const modal = new bootstrap.Modal(document.getElementById('liveStatusModal'));
            document.getElementById('liveStatusModalLabel').textContent = `${trainName} (${trainNumber}) - Live Status`;
            
            document.getElementById('liveStatusLoading').style.display = 'block';
            document.getElementById('liveStatusContent').style.display = 'none';
            document.getElementById('liveStatusError').style.display = 'none';
            
            modal.show();

            fetch(`search_train.php?live_status=${trainNumber}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('liveStatusLoading').style.display = 'none';
                    
                    // The new API response handling
                    if (data && data.data) {
                        const status = data.data;
                        document.getElementById('currentStation').textContent = status.current_station_name || status.current_station || "In Transit";
                        document.getElementById('statusMessage').textContent = status.status_as_of || status.message || "Live data retrieved.";
                        document.getElementById('lastStation').textContent = status.last_station_name || "N/A";
                        document.getElementById('nextStation').textContent = status.next_station_name || "N/A";
                        document.getElementById('liveStatusContent').style.display = 'block';
                    } else if (data.message) {
                        document.getElementById('liveStatusErrorMessage').textContent = data.message;
                        document.getElementById('liveStatusError').style.display = 'block';
                    } else {
                        document.getElementById('liveStatusError').style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Error fetching live status:', err);
                    document.getElementById('liveStatusLoading').style.display = 'none';
                    document.getElementById('liveStatusError').style.display = 'block';
                });
        }

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
                    
                    // The new API response handling for schedule
                    const schedule = (data && data.data && data.data.schedule) ? data.data.schedule : null;
                    
                    if (schedule && schedule.length > 0) {
                        const tableBody = document.getElementById('scheduleTableBody');
                        tableBody.innerHTML = '';
                        schedule.forEach(stop => {
                            const row = `
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <div class="text-dark">${stop.station_name}</div>
                                        <div class="small text-muted">${stop.station_code}</div>
                                    </td>
                                    <td>${stop.arrival_time || 'Starts'}</td>
                                    <td>${stop.departure_time || 'Ends'}</td>
                                    <td>${stop.distance || '0'} km</td>
                                    <td class="pe-4">Day ${stop.day || '1'}</td>
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

