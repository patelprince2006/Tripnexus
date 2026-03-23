<?php
session_start();
include '../database/db.php';

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

    $search_query = "SELECT * FROM trains 
                     WHERE from_station LIKE ? 
                     AND to_station LIKE ?
                     ORDER BY departure_time ASC";
    
    $res = db_query($conn, $search_query, array("%$from%", "%$to%"));

    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $results[] = $row;
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
                    <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-train-front-fill text-info me-1"></i>From Station</label>
                            <select name="train_from" id="trainFrom" class="border-0 w-100 fw-bold" style="background: none;" required>
                                <option value="">Select</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st); ?>" <?php echo ($from === $st) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st); ?>
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
                                <option value="">Select</option>
                                <?php foreach ($stations as $st): ?>
                                    <option value="<?php echo htmlspecialchars($st); ?>" <?php echo ($to === $st) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st); ?>
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

    <script>
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
