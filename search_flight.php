<?php
session_start();
include 'db.php';

$results = [];
$search_performed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['departure_city'];
    $to = $_POST['arrival_city'];
    $travel_date = $_POST['departure_date'];

    $search_query = "SELECT f.*, a.airline_name, a.airline_logo 
                    FROM flights f 
                    JOIN airlines a ON f.airline_id = a.airline_id 
                    WHERE f.departure_airport = $1 
                    AND f.arrival_airport = $2 
                    AND DATE(f.departure_time) = $3
                    ORDER BY f.departure_time ASC";

    $res = pg_query_params($conn, $search_query, array($from, $to, $travel_date));

    if ($res) {
        while ($row = pg_fetch_assoc($res)) {
            // Adding a mock rating since it's not in your DB yet
            $row['rating'] = rand(35, 50) / 10;
            $results[] = $row;
        }
    }
    $search_performed = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Flights | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Enhancing the existing style.css with interactive elements */
        .flight-card {
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
        }

        .flight-card:hover {
            border-left: 5px solid #ffc107;
            /* Warning yellow on hover */
            transform: translateX(10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .rating-stars {
            color: #ffc107;
        }

        .airline-logo-img {
            transition: transform 0.3s ease;
        }

        .flight-card:hover .airline-logo-img {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Trip<span class="text-warning">Nexus</span></a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card border-0 shadow-lg p-4 mb-5 auth-card">
            <form method="POST" action="">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">FROM</label>
                        <select name="departure_city" class="form-select border-0 bg-light p-3">
                            <option value="BOM" selected>Mumbai (BOM)</option>
                            <option value="DEL">Delhi (DEL)</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-center pb-3 d-none d-md-block">
                        <i class="bi bi-arrow-left-right text-primary fs-4"></i>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">TO</label>
                        <select name="arrival_city" class="form-select border-0 bg-light p-3">
                            <option value="DEL" selected>Delhi (DEL)</option>
                            <option value="BOM">Mumbai (BOM)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">DEPARTURE DATE</label>
                        <input type="date" name="departure_date" class="form-control border-0 bg-light p-3" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 p-3 fw-bold btn-search">
                            <i class="bi bi-search me-2"></i>Find Flights
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($search_performed): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0"><?php echo count($results); ?> Flights Found</h4>
                <div class="text-muted small">Showing best prices for your route</div>
            </div>

            <?php if (empty($results)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-airplane-engines text-muted display-1"></i>
                    <p class="mt-3 text-muted">No flights found. Try a different date!</p>
                </div>
            <?php else: ?>
                <?php foreach ($results as $flight): ?>
                    <div class="card border-0 shadow-sm mb-3 p-3 flight-card">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="<?php echo $flight['airline_logo']; ?>" class="airline-logo-img" alt="Logo" style="height: 45px;">
                                <div class="small fw-bold mt-2"><?php echo $flight['airline_name']; ?></div>
                                <div class="rating-stars small">
                                    <i class="bi bi-star-fill"></i> <?php echo $flight['rating']; ?>
                                </div>
                            </div>

                            <div class="col-md-3 text-center">
                                <h4 class="mb-0 fw-bold"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></h4>
                                <div class="badge bg-light text-dark border mt-1"><?php echo $flight['departure_airport']; ?></div>
                            </div>

                            <div class="col-md-2 text-center position-relative">
                                <div class="small text-muted mb-1">Direct</div>
                                <div style="height: 2px; background: #dee2e6; width: 100%; position: relative;">
                                    <i class="bi bi-airplane-fill position-absolute start-50 translate-middle text-primary" style="top: 50%;"></i>
                                </div>
                                <div class="small text-muted mt-1">2h 15m</div>
                            </div>

                            <div class="col-md-2 text-center">
                                <h4 class="mb-0 fw-bold"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></h4>
                                <div class="badge bg-light text-dark border mt-1"><?php echo $flight['arrival_airport']; ?></div>
                            </div>

                            <div class="col-md-3 text-end border-start">
                                <div class="text-muted small mb-1">Price per adult</div>
                                <h3 class="text-primary fw-bold mb-3">₹<?php echo number_format($flight['base_price'], 0); ?></h3>
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <button class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm">
                                        Book Now <i class="bi bi-chevron-right small"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Visual feedback when clicking search
        document.querySelector('form').onsubmit = function() {
            const btn = this.querySelector('.btn-search');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Searching...';
            btn.classList.add('disabled');
        };
    </script>
</body>

</html>