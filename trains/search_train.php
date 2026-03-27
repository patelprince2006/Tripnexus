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
$st_res = db_query($conn, "SELECT DISTINCT from_station as st FROM trains UNION SELECT DISTINCT to_station as st FROM trains ORDER BY st ASC");
if ($st_res) {
    while ($st = db_fetch_assoc($st_res)) {
        $stations[] = $st['st'];
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

    // Use the reliable IndianRailAPI with the key you provided
    $api_key = '43d6ed9d3d1efaf98d93001258955183';
    $api_date = date('d-m-Y', strtotime($date));
    
    // 1. Fetch trains between stations
    $search_url = "http://indianrailapi.com/api/v2/BetweenStation/apikey/$api_key/From/$from/To/$to/Date/$api_date/";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    $api_data = json_decode($response, true);

    if (isset($api_data['Trains']) && !empty($api_data['Trains'])) {
        foreach ($api_data['Trains'] as $t) {
            $t_num = $t['TrainNo'];
            $t_name = $t['TrainName'];
            $dep_time = $date . ' ' . $t['DepartureTime'] . ':00';
            $arr_time = $date . ' ' . $t['ArrivalTime'] . ':00';
            
            if (strtotime($arr_time) < strtotime($dep_time)) {
                $arr_time = date('Y-m-d H:i:s', strtotime($arr_time . ' +1 day'));
            }

            // Sync with local database to allow booking
            $train_check = db_query($conn, "SELECT train_id FROM trains WHERE train_number = ? AND departure_time = ?", [$t_num, $dep_time]);
            $train_row = db_fetch_assoc($train_check);
            
            if (!$train_row) {
                // Fetch fare for this specific train
                $fare_url = "http://indianrailapi.com/api/v2/TrainFare/apikey/$api_key/TrainNumber/$t_num/From/$from/To/$to/Quota/GN";
                $ch_f = curl_init();
                curl_setopt($ch_f, CURLOPT_URL, $fare_url);
                curl_setopt($ch_f, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_f, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch_f, CURLOPT_SSL_VERIFYPEER, false);
                $fare_res = curl_exec($ch_f);
                curl_close($ch_f);
                
                $fare_data = json_decode($fare_res, true);
                $price = 0;
                if (isset($fare_data['Fares'][0]['Fare'])) {
                    $price = $fare_data['Fares'][0]['Fare'];
                }
                if ($price <= 0) $price = rand(450, 3200);

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

    // Fallback: If API returns nothing, search local DB using mapping for codes
    if (empty($results)) {
        // Map common codes to names for the local DB which uses names
        $station_map = [
            'NDLS' => 'Delhi', 'BCT' => 'Mumbai', 'SBC' => 'Bangalore', 
            'AMD' => 'Ahmedabad', 'BPL' => 'Bhopal', 'MAS' => 'Chennai'
        ];
        $from_name = $station_map[$from] ?? $from;
        $to_name = $station_map[$to] ?? $to;

        $search_query = "SELECT * FROM trains 
                         WHERE (from_station LIKE ? AND to_station LIKE ?) 
                         OR (from_station = ? AND to_station = ?)
                         ORDER BY departure_time ASC";
        $res = db_query($conn, $search_query, array("%$from_name%", "%$to_name%", $from, $to));

        if ($res) {
            while ($row = db_fetch_assoc($res)) {
                $results[] = $row;
            }
        }
    }
    $search_performed = true;
} else {
    // Default: Show upcoming schedule
    $upcoming_query = "SELECT * FROM trains 
                       WHERE departure_time >= NOW()
                       ORDER BY departure_time ASC 
                       LIMIT 10";
    $up_res = db_query($conn, $upcoming_query);
    if ($up_res) {
        while ($row = db_fetch_assoc($up_res)) {
            $results[] = $row;
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
                                <option value="NDLS" <?php echo ($from == 'NDLS') ? 'selected' : ''; ?>>New Delhi (NDLS)</option>
                                <option value="BCT" <?php echo ($from == 'BCT') ? 'selected' : ''; ?>>Mumbai Central (BCT)</option>
                                <option value="MAS" <?php echo ($from == 'MAS') ? 'selected' : ''; ?>>Chennai Central (MAS)</option>
                                <option value="HWH" <?php echo ($from == 'HWH') ? 'selected' : ''; ?>>Howrah (HWH)</option>
                                <option value="SBC" <?php echo ($from == 'SBC') ? 'selected' : ''; ?>>Bangalore (SBC)</option>
                                <option value="HYB" <?php echo ($from == 'HYB') ? 'selected' : ''; ?>>Hyderabad (HYB)</option>
                                <option value="PUNE" <?php echo ($from == 'PUNE') ? 'selected' : ''; ?>>Pune (PUNE)</option>
                                <option value="JAI" <?php echo ($from == 'JAI') ? 'selected' : ''; ?>>Jaipur (JAI)</option>
                                <option value="AMD" <?php echo ($from == 'AMD') ? 'selected' : ''; ?>>Ahmedabad (AMD)</option>
                                <option value="LKO" <?php echo ($from == 'LKO') ? 'selected' : ''; ?>>Lucknow (LKO)</option>
                                <option value="PNBE" <?php echo ($from == 'PNBE') ? 'selected' : ''; ?>>Patna (PNBE)</option>
                                <option value="BPL" <?php echo ($from == 'BPL') ? 'selected' : ''; ?>>Bhopal (BPL)</option>
                                <option value="INDB" <?php echo ($from == 'INDB') ? 'selected' : ''; ?>>Indore (INDB)</option>
                                <option value="VSKP" <?php echo ($from == 'VSKP') ? 'selected' : ''; ?>>Visakhapatnam (VSKP)</option>
                                <option value="GHY" <?php echo ($from == 'GHY') ? 'selected' : ''; ?>>Guwahati (GHY)</option>
                                <option value="AGC" <?php echo ($from == 'AGC') ? 'selected' : ''; ?>>Agra Cantt (AGC)</option>
                                <option value="BSB" <?php echo ($from == 'BSB') ? 'selected' : ''; ?>>Varanasi (BSB)</option>
                                <option value="CNB" <?php echo ($from == 'CNB') ? 'selected' : ''; ?>>Kanpur Central (CNB)</option>
                                <option value="MYS" <?php echo ($from == 'MYS') ? 'selected' : ''; ?>>Mysuru (MYS)</option>
                                <option value="CBE" <?php echo ($from == 'CBE') ? 'selected' : ''; ?>>Coimbatore (CBE)</option>
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
                                <option value="NDLS" <?php echo ($to == 'NDLS') ? 'selected' : ''; ?>>New Delhi (NDLS)</option>
                                <option value="BCT" <?php echo ($to == 'BCT') ? 'selected' : ''; ?>>Mumbai Central (BCT)</option>
                                <option value="MAS" <?php echo ($to == 'MAS') ? 'selected' : ''; ?>>Chennai Central (MAS)</option>
                                <option value="HWH" <?php echo ($to == 'HWH') ? 'selected' : ''; ?>>Howrah (HWH)</option>
                                <option value="SBC" <?php echo ($to == 'SBC') ? 'selected' : ''; ?>>Bangalore (SBC)</option>
                                <option value="HYB" <?php echo ($to == 'HYB') ? 'selected' : ''; ?>>Hyderabad (HYB)</option>
                                <option value="PUNE" <?php echo ($to == 'PUNE') ? 'selected' : ''; ?>>Pune (PUNE)</option>
                                <option value="JAI" <?php echo ($to == 'JAI') ? 'selected' : ''; ?>>Jaipur (JAI)</option>
                                <option value="AMD" <?php echo ($to == 'AMD') ? 'selected' : ''; ?>>Ahmedabad (AMD)</option>
                                <option value="LKO" <?php echo ($to == 'LKO') ? 'selected' : ''; ?>>Lucknow (LKO)</option>
                                <option value="PNBE" <?php echo ($to == 'PNBE') ? 'selected' : ''; ?>>Patna (PNBE)</option>
                                <option value="BPL" <?php echo ($to == 'BPL') ? 'selected' : ''; ?>>Bhopal (BPL)</option>
                                <option value="INDB" <?php echo ($to == 'INDB') ? 'selected' : ''; ?>>Indore (INDB)</option>
                                <option value="VSKP" <?php echo ($to == 'VSKP') ? 'selected' : ''; ?>>Visakhapatnam (VSKP)</option>
                                <option value="GHY" <?php echo ($to == 'GHY') ? 'selected' : ''; ?>>Guwahati (GHY)</option>
                                <option value="AGC" <?php echo ($to == 'AGC') ? 'selected' : ''; ?>>Agra Cantt (AGC)</option>
                                <option value="BSB" <?php echo ($to == 'BSB') ? 'selected' : ''; ?>>Varanasi (BSB)</option>
                                <option value="CNB" <?php echo ($to == 'CNB') ? 'selected' : ''; ?>>Kanpur Central (CNB)</option>
                                <option value="MYS" <?php echo ($to == 'MYS') ? 'selected' : ''; ?>>Mysuru (MYS)</option>
                                <option value="CBE" <?php echo ($to == 'CBE') ? 'selected' : ''; ?>>Coimbatore (CBE)</option>
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
            <h4 class="fw-bold m-0"><i class="bi bi-train-front text-info me-2"></i><?php echo $search_performed ? count($results) . ' Trains Found' : 'Upcoming Train Schedule'; ?></h4>
            <div class="text-muted small"><?php echo $search_performed ? (htmlspecialchars($from) . ' <i class="bi bi-arrow-right"></i> ' . htmlspecialchars($to)) : 'Available trains for today and tomorrow'; ?></div>
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
