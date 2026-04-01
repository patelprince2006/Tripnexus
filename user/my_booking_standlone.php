<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Handle status filtering
$selected_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$status_filter = "";
if ($selected_status !== 'all') {
    $status_filter = " AND b.status = ?";
}

$stats = [
    'total_bookings' => 0,
    'confirmed_bookings' => 0,
    'pending_bookings' => 0,
    'total_spent' => 0
];
$bookings = [];
$db_error = null;

if (defined('DB_CONNECTED') && DB_CONNECTED) {
    // Defensive check: Try to see if required columns exist
    $check_cols = db_query($conn, "SHOW COLUMNS FROM bookings");
    $existing_cols = [];
    while ($col = db_fetch_assoc($check_cols)) {
        $existing_cols[] = $col['Field'];
    }

    $missing_cols = array_diff(['booking_type', 'reference_id', 'total_amount'], $existing_cols);
    if (!empty($missing_cols)) {
        // Redirection or auto-fix link
        $db_error = "Database Schema Error: Missing columns (" . implode(', ', $missing_cols) . ") in 'bookings' table. Please run the setup script: <a href='../fix_bookings_schema.php'>Fix Database Schema</a>";
    }

    if (!$db_error) {
        $stats_query = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                COALESCE(SUM(CASE WHEN status = 'confirmed' THEN total_amount ELSE 0 END), 0) as total_spent
            FROM bookings
            WHERE user_id = ?";
        $stats_res = db_query($conn, $stats_query, array($user_id));
        if ($stats_res) {
            $row = db_fetch_assoc($stats_res);
            if ($row) {
                $stats = array_merge($stats, $row);
            }
        }

        $list_query = "SELECT 
                b.*,
                CASE 
                    WHEN b.booking_type = 'flight' THEN f.flight_number
                    WHEN b.booking_type = 'hotel' THEN h.name
                    WHEN b.booking_type = 'tour' THEN t.name
                    ELSE NULL
                END as item_name
            FROM bookings b
            LEFT JOIN flights f ON b.booking_type = 'flight' AND b.reference_id = f.flight_id
            LEFT JOIN hotels h ON b.booking_type = 'hotel' AND b.reference_id = h.hotel_id
            LEFT JOIN tour_packages t ON b.booking_type = 'tour' AND b.reference_id = t.id
            WHERE b.user_id = ?" . $status_filter . "
            ORDER BY b.booking_date DESC";
            
        $params = array($user_id);
        if ($selected_status !== 'all') {
            $params[] = $selected_status;
        }
        
        $list_res = db_query($conn, $list_query, $params);
        if ($list_res) {
            while ($row = db_fetch_assoc($list_res)) {
                $bookings[] = $row;
            }
        }
    }
} else {
    $db_error = "Unable to connect to database. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .booking-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
        }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .service-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .flight-icon { background-color: #e3f2fd; color: #1976d2; }
        .bus-icon { background-color: #fff3e0; color: #f57c00; }
        .train-icon { background-color: #e8f5e9; color: #388e3c; }
        .hotel-icon { background-color: #fce4ec; color: #c2185b; }
        .booking-reference {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .timeline-step {
            position: relative;
            padding-left: 30px;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #28a745;
        }
        .timeline-step::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 20px;
            bottom: -20px;
            width: 2px;
            background-color: #e9ecef;
        }
        .timeline-step:last-child::after {
            display: none;
        }
        .no-data-banner {
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
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

    <div class="container">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="fw-bold mb-1">My Bookings</h1>
                        <p class="text-muted mb-0">Track and manage your travel reservations</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="../index.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Book New
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="service-icon flight-icon me-3">
                            <i class="bi bi-airplane"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Bookings</h6>
                            <h4 class="fw-bold mb-0"><?php echo (int)$stats['total_bookings']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="service-icon train-icon me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Confirmed</h6>
                            <h4 class="fw-bold mb-0 text-success"><?php echo (int)$stats['confirmed_bookings']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="service-icon bus-icon me-3">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Pending</h6>
                            <h4 class="fw-bold mb-0 text-warning"><?php echo (int)$stats['pending_bookings']; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="service-icon hotel-icon me-3">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Spent</h6>
                            <h4 class="fw-bold mb-0">₹<?php echo number_format((float)$stats['total_spent'], 2); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Recent Bookings</h5>
                            <div class="d-flex gap-2">
                                <a href="?status=all" class="badge text-decoration-none <?php echo $selected_status == 'all' ? 'bg-primary' : 'bg-light text-dark'; ?>">All</a>
                                <a href="?status=confirmed" class="badge text-decoration-none <?php echo $selected_status == 'confirmed' ? 'bg-success' : 'bg-light text-dark'; ?>">Confirmed</a>
                                <a href="?status=pending" class="badge text-decoration-none <?php echo $selected_status == 'pending' ? 'bg-warning' : 'bg-light text-dark'; ?>">Pending</a>
                                <a href="?status=cancelled" class="badge text-decoration-none <?php echo $selected_status == 'cancelled' ? 'bg-danger' : 'bg-light text-dark'; ?>">Cancelled</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($db_error): ?>
                            <div class="alert alert-warning m-3" role="alert">
                                <?php echo htmlspecialchars($db_error); ?>
                            </div>
                        <?php elseif (empty($bookings)): ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="bi bi-ticket-detailed display-4 text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">No bookings found</h5>
                                <p class="text-muted mb-4">You haven't made any bookings yet. Start planning your next adventure!</p>
                                <a href="../index.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>Find Services
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($bookings as $booking): ?>
                                    <?php
                                        $type = $booking['booking_type'] ?? 'service';
                                        $status = $booking['status'] ?? 'pending';
                                        $item_name = $booking['item_name'] ?? '';
                                        $travel_date = $booking['travel_date'] ? date('d M Y', strtotime($booking['travel_date'])) : 'N/A';
                                        $booked_on = $booking['booking_date'] ? date('d M Y', strtotime($booking['booking_date'])) : 'N/A';

                                        $status_class = 'status-pending';
                                        if ($status === 'confirmed') {
                                            $status_class = 'status-confirmed';
                                        } elseif ($status === 'cancelled') {
                                            $status_class = 'status-cancelled';
                                        }

                                        $icon_class = 'bi-ticket-detailed';
                                        $service_class = 'flight-icon';
                                        if ($type === 'hotel') {
                                            $icon_class = 'bi-building';
                                            $service_class = 'hotel-icon';
                                        } elseif ($type === 'tour') {
                                            $icon_class = 'bi-geo-alt';
                                            $service_class = 'bus-icon';
                                        } elseif ($type === 'flight') {
                                            $icon_class = 'bi-airplane';
                                            $service_class = 'flight-icon';
                                        }
                                    ?>
                                    <div class="list-group-item booking-card">
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="service-icon <?php echo $service_class; ?>">
                                                    <i class="bi <?php echo $icon_class; ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-capitalize"><?php echo htmlspecialchars($type); ?> Booking</div>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars($item_name !== '' ? $item_name : ('Reference #' . $booking['reference_id'])); ?>
                                                    </div>
                                                    <div class="text-muted small">Travel Date: <?php echo htmlspecialchars($travel_date); ?></div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold mb-1">₹<?php echo number_format((float)$booking['total_amount'], 2); ?></div>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                                                <div class="text-muted small mt-1">Booked: <?php echo htmlspecialchars($booked_on); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Trips Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h5 class="fw-bold mb-3">Upcoming Trips</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-calendar-plus display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">No upcoming trips</h6>
                                <p class="text-muted small">Book your next adventure to see it here</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-geo-alt display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">Plan your journey</h6>
                                <p class="text-muted small">Explore destinations and book your trip</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-airplane display-4 text-muted mb-3"></i>
                                <h6 class="text-muted">Ready to travel</h6>
                                <p class="text-muted small">Your next adventure awaits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>