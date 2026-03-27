<?php
session_start();
include '../database/db.php';

$results = [];
$search_performed = false;
$location = '';
$tour_date = '';
$passengers = 1;

// Prepare tour locations for dropdown
$tour_locations = [];
$tour_loc_res = db_query($conn, "SELECT DISTINCT location FROM tour_packages ORDER BY location ASC");
if ($tour_loc_res) {
    while ($tl = db_fetch_assoc($tour_loc_res)) {
        $tour_locations[] = $tl['location'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = $_POST['tour_location'] ?? '';
    $tour_date = $_POST['tour_date'] ?? '';
    $passengers = $_POST['passengers'] ?? 1;

    $query = "SELECT * FROM tour_packages WHERE location = ? ORDER BY name ASC";
    $res = db_query($conn, $query, [$location]);

    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $results[] = $row;
        }
    }
    $search_performed = true;
} else {
    // Default: Show all tours
    $res = db_query($conn, "SELECT * FROM tour_packages ORDER BY created_at DESC");
    if ($res) {
        while ($row = db_fetch_assoc($res)) {
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
    <title>Search Tours | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .tour-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
        }

        .tour-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .navbar {
            background-color: #0d2137 !important;
        }

        .hero-section {
            background: linear-gradient(rgba(13, 33, 55, 0.8), rgba(13, 33, 55, 0.8)), url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 0 80px;
            color: white;
            text-align: center;
        }

        .search-container-mini {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-top: -50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .btn-success {
            background-color: #198754;
            border: none;
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

    <header class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold">Discover Your Next Adventure</h1>
            <p class="lead">Curated tour packages for unforgettable memories</p>
        </div>
    </header>

    <div class="container mb-5">
        <div class="search-container-mini">
            <form method="POST" action="search_tour.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Destination</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-geo-alt text-success"></i></span>
                        <select name="tour_location" class="form-select bg-light border-0" required>
                            <option value="">Select Location</option>
                            <?php foreach ($tour_locations as $tl): ?>
                                <option value="<?php echo htmlspecialchars($tl); ?>" <?php echo ($location == $tl) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tl); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase text-muted">Travel Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event text-success"></i></span>
                        <input type="date" name="tour_date" class="form-control bg-light border-0" value="<?php echo $tour_date; ?>" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-uppercase text-muted">Persons</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-people text-success"></i></span>
                        <input type="number" name="passengers" class="form-control bg-light border-0" value="<?php echo $passengers; ?>" min="1">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-3">Search Tours</button>
                </div>
            </form>
        </div>
    </div>

    <main class="container mb-5">
        <?php if ($search_performed): ?>
            <h3 class="fw-bold mb-4">Search Results for "<?php echo htmlspecialchars($location); ?>"</h3>
        <?php else: ?>
            <h3 class="fw-bold mb-4">All Tour Packages</h3>
        <?php endif; ?>

        <?php if (empty($results)): ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <p class="lead mt-3 text-muted">No tours found matching your search. Try another location.</p>
                <a href="search_tour.php" class="btn btn-outline-success rounded-pill px-4 mt-2">View All Tours</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($results as $tour): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 tour-card">
                            <div class="position-relative">
                                <img src="<?php echo !empty($tour['main_image']) ? htmlspecialchars($tour['main_image']) : 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=500&q=80'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['name']); ?>" style="height: 250px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-white text-dark shadow-sm rounded-pill px-3 py-2">
                                        <i class="bi bi-clock me-1 text-success"></i> <?php echo htmlspecialchars($tour['duration']); ?> Days
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-success me-1"></i><?php echo htmlspecialchars($tour['location']); ?></div>
                                <h4 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($tour['name']); ?></h4>
                                <p class="card-text text-muted small mb-4">
                                    <?php echo htmlspecialchars(substr($tour['description'], 0, 120)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div>
                                        <div class="small text-muted">Price per person</div>
                                        <div class="fw-bold text-success fs-4">₹<?php echo number_format($tour['price'], 2); ?></div>
                                    </div>
                                    <a href="booking.php?id=<?php echo $tour['id']; ?>&date=<?php echo $tour_date; ?>&pax=<?php echo $passengers; ?>" class="btn btn-success rounded-pill px-4 py-2">Details & Book</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>