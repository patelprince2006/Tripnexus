<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
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
            <a class="navbar-brand fw-bold" href="index.php">
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
                        <a class="btn btn-sm btn-outline-light rounded-pill px-3" href="index.php">Home</a>
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
                        <a href="index.php" class="btn btn-primary">
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
                            <h4 class="fw-bold mb-0">0</h4>
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
                            <h4 class="fw-bold mb-0 text-success">0</h4>
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
                            <h4 class="fw-bold mb-0 text-warning">0</h4>
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
                            <h4 class="fw-bold mb-0">₹0.00</h4>
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
                                <span class="badge bg-light text-dark">All Services</span>
                                <span class="badge bg-primary">Latest First</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- No bookings found message -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-ticket-detailed display-4 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">No bookings found</h5>
                            <p class="text-muted mb-4">You haven't made any bookings yet. Start planning your next adventure!</p>
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-search me-2"></i>Find Services
                            </a>
                        </div>
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

        <!-- Feature Highlights -->
        <div class="row mt-5">
            <div class="col-12">
                <h5 class="fw-bold mb-3">Features Coming Soon</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="service-icon flight-icon me-3">
                                        <i class="bi bi-airplane"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Flight Bookings</h6>
                                        <p class="text-muted small">Book domestic and international flights</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-primary">Coming Soon</span>
                                    <span class="small text-muted">Save up to 40%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="service-icon hotel-icon me-3">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Hotel Reservations</h6>
                                        <p class="text-muted small">Find and book hotels worldwide</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-success">Coming Soon</span>
                                    <span class="small text-muted">Best price guarantee</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="service-icon train-icon me-3">
                                        <i class="bi bi-train-front"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Train Tickets</h6>
                                        <p class="text-muted small">Book train tickets across India</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-info">Coming Soon</span>
                                    <span class="small text-muted">Instant confirmation</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetails(bookingId) {
            alert('Booking details for ID: ' + bookingId);
        }

        function cancelBooking(bookingId) {
            if(confirm('Are you sure you want to cancel this booking?')) {
                alert('Booking ' + bookingId + ' cancellation requested');
            }
        }

        function downloadTicket(bookingId) {
            alert('Downloading ticket for booking ' + bookingId);
        }
    </script>
</body>

</html>