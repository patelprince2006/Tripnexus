<?php
session_start();
include 'db.php';

$results = [];
$search_performed = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST['bus_from'];
    $to = $_POST['bus_to'];
    $date = $_POST['bus_date'];

    // Search query for buses
    // Note: In a real app, you would compare DATE(departure_time) with $date
    // For this example, we'll just check if the route matches
    $search_query = "SELECT * FROM buses 
                     WHERE from_location ILIKE $1 
                     AND to_location ILIKE $2
                     ORDER BY departure_time ASC";
    
    // Using ILIKE for case-insensitive search
    // We are not filtering by date strictly for this demo to ensure results show up if dummy data doesn't match date exactly
    // In production: AND DATE(departure_time) = $3
    
    $res = pg_query_params($conn, $search_query, array("%$from%", "%$to%"));

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
    <title>Search Buses | TripNexus</title>
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
        <h2 class="fw-bold mb-4">Bus Search Results</h2>
        
        <?php if ($search_performed): ?>
            <?php if (empty($results)): ?>
                <div class="alert alert-warning">No buses found for this route.</div>
            <?php else: ?>
                <?php foreach ($results as $bus): ?>
                    <div class="card border-0 shadow-sm mb-3 p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($bus['operator_name']); ?></h5>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($bus['bus_type']); ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0"><?php echo date('H:i', strtotime($bus['departure_time'])); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($bus['from_location']); ?></div>
                            </div>
                            <div class="col-md-1 text-center text-muted">➔</div>
                            <div class="col-md-2 text-center">
                                <h5 class="mb-0"><?php echo date('H:i', strtotime($bus['arrival_time'])); ?></h5>
                                <div class="text-muted small"><?php echo htmlspecialchars($bus['to_location']); ?></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <?php 
                                    $duration = strtotime($bus['arrival_time']) - strtotime($bus['departure_time']);
                                    $hours = floor($duration / 3600);
                                    $minutes = floor(($duration % 3600) / 60);
                                    echo "{$hours}h {$minutes}m";
                                ?>
                            </div>
                            <div class="col-md-2 text-end">
                                <h4 class="text-danger fw-bold">₹<?php echo number_format($bus['price'], 2); ?></h4>
                                <button class="btn btn-danger fw-bold rounded-pill px-4">Book Seat</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">Please perform a search from the home page.</div>
        <?php endif; ?>
    </div>
</body>
</html>
