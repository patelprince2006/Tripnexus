<?php
session_start();
include 'db.php';

$results = [];
$search_performed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $city = $_POST['hotel_city'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];

    // Search query for hotels
    // Searching by city or name
    $search_query = "SELECT * FROM hotels 
                     WHERE city ILIKE $1 
                     OR name ILIKE $1
                     ORDER BY rating DESC";
    
    $res = pg_query_params($conn, $search_query, array("%$city%"));

    if ($res) {
        while ($row = pg_fetch_assoc($res)) {
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
    <title>Search Hotels | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php"><img src="photos/logo.jpeg" alt="TripNexus Logo" height="40" class="me-2"><span>Trip<span class="text-warning">Nexus</span></span></a>
        </div>
    </nav>
    <div class="container my-5">
        <h2 class="fw-bold mb-4">Hotel Search Results</h2>
        
        <?php if ($search_performed): ?>
            <?php if (empty($results)): ?>
                <div class="alert alert-warning">No hotels found for this location.</div>
            <?php else: ?>
                <div class="row">
                <?php foreach ($results as $hotel): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold card-title mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill small"></i> <?php echo htmlspecialchars($hotel['rating']); ?></span>
                                </div>
                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($hotel['address']); ?></p>
                                <p class="small mb-3"><?php echo htmlspecialchars($hotel['amenities']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <h4 class="text-warning text-dark fw-bold mb-0">₹<?php echo number_format($hotel['price_per_night'], 2); ?></h4>
                                    <button class="btn btn-outline-dark fw-bold px-4 rounded-pill">View Room</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">Please perform a search from the home page.</div>
        <?php endif; ?>
    </div>
</body>
</html>
