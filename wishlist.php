<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Fetch wishlist items (Example query assuming a 'wishlist' table exists)
// For now, we will fetch standard flights as a placeholder
$query = "SELECT f.*, a.airline_name, a.airline_logo 
          FROM flights f 
          JOIN airlines a ON f.airline_id = a.airline_id 
          LIMIT 3"; // In a real app, join with a 'wishlist' table on user_id

$result = pg_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Wishlist | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .rating-stars {
            color: #ffc107;
            font-size: 0.9rem;
        }

        .wishlist-card {
            transition: transform 0.3s;
            border-radius: 15px;
        }

        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="fw-bold mb-4"><i class="bi bi-heart-fill text-danger me-2"></i>My Wishlist</h2>

        <div class="row g-4">
            <?php while ($flight = pg_fetch_assoc($result)): ?>
                <div class="col-md-4">
                    <div class="card wishlist-card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <img src="photos/Mumbai.jpg" class="card-img-top" alt="Destination">
                            <span class="badge bg-white text-dark position-absolute top-0 end-0 m-3 shadow-sm">
                                ₹<?php echo number_format($flight['base_price'], 2); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0"><?php echo $flight['airline_name']; ?></h5>
                                <div class="rating-stars">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                    <span class="text-muted ms-1">(4.5)</span>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt me-1"></i> <?php echo $flight['departure_airport']; ?> to <?php echo $flight['arrival_airport']; ?>
                            </p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary rounded-pill fw-bold">Book Trip</button>
                                <button class="btn btn-outline-danger btn-sm border-0">Remove from List</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>