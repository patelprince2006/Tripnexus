<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Fetch wishlist items
$wishlist_items = [];
$wishlist_query = "SELECT * FROM wishlist WHERE user_id = ? ORDER BY added_at DESC";
$wishlist_result = db_query($conn, $wishlist_query, [$user_id]);

if ($wishlist_result) {
    while ($item = db_fetch_assoc($wishlist_result)) {
        $item_details = null;
        
        if ($item['item_type'] == 'flight') {
            $query = "SELECT f.*, a.airline_name, a.airline_logo FROM flights f JOIN airlines a ON f.airline_id = a.airline_id WHERE f.flight_id = ?";
            $res = db_query($conn, $query, [$item['item_id']]);
            if ($res && $row = db_fetch_assoc($res)) {
                $item_details = $row;
                $item_details['type'] = 'flight';
                $item_details['image'] = 'photos/Mumbai.jpg'; // Placeholder
            }
        } elseif ($item['item_type'] == 'hotel') {
            $query = "SELECT * FROM hotels WHERE hotel_id = ?";
            $res = db_query($conn, $query, [$item['item_id']]);
            if ($res && $row = db_fetch_assoc($res)) {
                $item_details = $row;
                $item_details['type'] = 'hotel';
                $item_details['image'] = $row['main_image'] ?: 'photos/hotel.jpg'; // Placeholder if no image
                $item_details['airline_name'] = $row['name']; // Use hotel name for display
                $item_details['base_price'] = $row['price_per_night'];
                $item_details['location'] = $row['city'];
            }
        } elseif ($item['item_type'] == 'bus') {
            $query = "SELECT * FROM buses WHERE bus_id = ?";
            $res = db_query($conn, $query, [$item['item_id']]);
            if ($res && $row = db_fetch_assoc($res)) {
                $item_details = $row;
                $item_details['type'] = 'bus';
                $item_details['image'] = 'photos/Goa.jpg'; // Placeholder
                $item_details['airline_name'] = $row['operator_name'];
                $item_details['base_price'] = $row['price'];
                $item_details['location'] = $row['from_location'] . ' to ' . $row['to_location'];
            }
        } elseif ($item['item_type'] == 'train') {
            $query = "SELECT * FROM trains WHERE train_id = ?";
            $res = db_query($conn, $query, [$item['item_id']]);
            if ($res && $row = db_fetch_assoc($res)) {
                $item_details = $row;
                $item_details['type'] = 'train';
                $item_details['image'] = 'photos/Manali.jpg'; // Placeholder
                $item_details['airline_name'] = $row['train_name'];
                $item_details['base_price'] = $row['price'];
                $item_details['location'] = $row['from_station'] . ' to ' . $row['to_station'];
            }
        }
        // Add more types as needed
        
        if ($item_details) {
            $item_details['wishlist_id'] = $item['id'];
            $wishlist_items[] = $item_details;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Wishlist | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 px-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><img src="../photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
                Trip<span class="text-warning">Nexus</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="d-flex align-items-center gap-3" id="navMenu">
               
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small d-none d-sm-inline">
                        Welcome, <?php echo htmlspecialchars($fullname); ?>!
                    </span>
                     <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-light rounded-pill px-3" href="../index.php">Home</a>
                    </li>
                </ul>
                    <a href="dashboard.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">Dashboard</a>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <h2 class="fw-bold mb-4"><i class="bi bi-heart-fill text-danger me-2"></i>My Wishlist</h2>

        <div class="row g-4">
            <?php if (empty($wishlist_items)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-heart text-muted display-1"></i>
                    <h4 class="mt-3 text-muted">Your wishlist is empty</h4>
                    <p class="text-muted">Start adding flights, hotels, and more to your wishlist!</p>
                    <a href="../index.php" class="btn btn-primary">Explore Trips</a>
                </div>
            <?php else: ?>
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-md-4">
                        <div class="card wishlist-card border-0 shadow-sm h-100">
                            <div class="position-relative">
                                <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="Item">
                                <span class="badge bg-white text-dark position-absolute top-0 end-0 m-3 shadow-sm">
                                    ₹<?php echo number_format($item['base_price'], 2); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($item['airline_name']); ?></h5>
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
                                    <i class="bi bi-geo-alt me-1"></i> 
                                    <?php if ($item['type'] == 'flight'): ?>
                                        <?php echo $item['departure_airport']; ?> to <?php echo $item['arrival_airport']; ?>
                                    <?php elseif ($item['type'] == 'hotel'): ?>
                                        <?php echo htmlspecialchars($item['location']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($item['location']); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="d-grid gap-2">
                                    <?php 
                                        $booking_link = "../flights/booking.php";
                                        if ($item['type'] == 'hotel') $booking_link = "../flights/booking.php";
                                        // The booking page seems generic enough
                                    ?>
                                    <form action="<?php echo $booking_link; ?>" method="POST">
                                        <input type="hidden" name="service_type" value="<?php echo $item['type']; ?>">
                                        <input type="hidden" name="reference_id" value="<?php echo $item[$item['type'] . '_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $item['base_price']; ?>">
                                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['airline_name']); ?>">
                                        <button type="submit" class="btn btn-primary rounded-pill fw-bold w-100">Book Trip</button>
                                    </form>
                                    <button class="btn btn-outline-danger btn-sm border-0" onclick="removeFromWishlist(<?php echo $item['wishlist_id']; ?>, this)">Remove from List</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function removeFromWishlist(wishlistId, btn) {
        if (!confirm('Remove this item from your wishlist?')) return;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-spinner"></i> Removing...';
        
        fetch('remove_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'wishlist_id=' + wishlistId
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.closest('.col-md-4').remove();
                // If no items left, reload to show empty message
                if (document.querySelectorAll('.wishlist-card').length === 0) {
                    location.reload();
                }
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = 'Remove from List';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            btn.disabled = false;
            btn.innerHTML = 'Remove from List';
        });
    }
    </script>
</body>

</html>