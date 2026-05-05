<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_GET['id'])) {
    header('Location: search_tour.php');
    exit;
}

$tour_id = intval($_GET['id']);
$tour_date = $_GET['date'] ?? date('Y-m-d', strtotime('+1 week'));
$passengers = intval($_GET['pax'] ?? 1);

// Fetch tour details
$tour_res = db_query($conn, "SELECT * FROM tour_packages WHERE id = ?", [$tour_id]);
if (!$tour_res || !($tour = db_fetch_assoc($tour_res))) {
    header('Location: search_tour.php');
    exit;
}

// Fetch available fixed dates
$schedules_res = db_query($conn, "SELECT * FROM tour_schedules WHERE tour_id = ? AND start_date >= CURDATE() ORDER BY start_date ASC", [$tour_id]);
$schedules = [];
while ($s = db_fetch_assoc($schedules_res)) {
    $schedules[] = $s;
}

// Fetch detailed itinerary
$itinerary_res = db_query($conn, "SELECT i.*, h.name as hotel_name, h.main_image as hotel_image FROM tour_itinerary i LEFT JOIN hotels h ON i.hotel_id = h.hotel_id WHERE i.tour_id = ? ORDER BY i.day_number ASC", [$tour_id]);
$itinerary = [];
while ($row = db_fetch_assoc($itinerary_res)) {
    $itinerary[] = $row;
}

// Calculate total amount
$total_amount = $tour['price'] * $passengers;

// Handle Booking Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $tour_date = $_POST['tour_date'] ?? $tour_date;
    $passengers = intval($_POST['pax'] ?? $passengers);
    $total_amount = $tour['price'] * $passengers;

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = "tours/booking.php?id=$tour_id&date=$tour_date&pax=$passengers";
        header('Location: ../user/login.html');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $booking_type = 'tour';
    $reference_id = $tour_id;
    $status = 'pending';

    $insert_query = "INSERT INTO bookings (user_id, booking_type, reference_id, total_amount, travel_date, passengers, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $res = db_query($conn, $insert_query, [$user_id, $booking_type, $reference_id, $total_amount, $tour_date, $passengers, $status]);

    if ($res) {
        $booking_id = mysqli_insert_id($conn);
        header("Location: ../flights/checkout.php?booking_id=$booking_id");
        exit;
    } else {
        $error = "Booking failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tour['name']); ?> | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        .navbar { background-color: #0d2137 !important; }
        .tour-hero {
            height: 400px;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('<?php 
                $tour_images = [
                    1 => '../photos/Agra.jpg',
                    2 => '../photos/Manali.jpg',
                    3 => '../photos/Goa.jpg',
                    4 => '../photos/Manali2.jpg',
                    5 => '../photos/Mumbai.jpg',
                    6 => '../photos/Agra.jpg'
                ];
                $tour_id = $tour['id'];
                echo isset($tour_images[$tour_id]) ? htmlspecialchars($tour_images[$tour_id]) : '../photos/Manali.jpg';
            ?>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;
            color: white;
            padding-bottom: 50px;
        }
        .booking-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: sticky;
            top: 100px;
        }
        .itinerary-item {
            border-left: 3px solid #198754;
            padding-left: 20px;
            margin-bottom: 25px;
            position: relative;
        }
        .itinerary-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 13px;
            height: 13px;
            background: #198754;
            border-radius: 50%;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="../index.php">
            <img src="../photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
            <span>Trip<span class="text-warning">Nexus</span></span>
        </a>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="search_tour.php">Tours</a></li>
            </ul>
        </div>
    </nav>

    <div class="tour-hero">
        <div class="container">
            <div class="badge bg-success mb-2 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($tour['duration']); ?> Days</div>
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($tour['name']); ?></h1>
            <p class="lead"><i class="bi bi-geo-alt-fill me-2"></i><?php echo htmlspecialchars($tour['location']); ?></p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
                    <h3 class="fw-bold mb-4">About this tour</h3>
                    <p class="text-muted lead"><?php echo nl2br(htmlspecialchars($tour['description'])); ?></p>
                </div>

                <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
                    <h3 class="fw-bold mb-4">Itinerary</h3>
                    <div class="itinerary-list">
                        <?php if (count($itinerary) > 0): ?>
                            <?php foreach ($itinerary as $day): ?>
                                <div class="itinerary-item pb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="fw-bold text-success mb-0">Day <?php echo $day['day_number']; ?>: <?php echo htmlspecialchars($day['route_from']); ?> to <?php echo htmlspecialchars($day['route_to']); ?></h5>
                                        <?php if ($day['transport_type'] != 'None'): ?>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-<?php 
                                                    echo ($day['transport_type'] == 'Flight') ? 'airplane' : 
                                                        (($day['transport_type'] == 'Train') ? 'train-front' : 'bus-front'); 
                                                ?> me-1"></i> 
                                                <?php echo $day['transport_type']; ?> at <?php echo date('h:i A', strtotime($day['transport_time'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($day['activities'])); ?></p>
                                    
                                    <?php if ($day['hotel_id']): ?>
                                        <div class="d-flex align-items-center bg-light p-3 rounded-3 mt-2">
                                            <i class="bi bi-building text-primary fs-4 me-3"></i>
                                            <div>
                                                <div class="small text-muted">Night Stay</div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($day['hotel_name']); ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php 
                            $itinerary_lines = explode("\n", $tour['itinerary']);
                            foreach ($itinerary_lines as $line):
                                if (trim($line) == '') continue;
                            ?>
                                <div class="itinerary-item">
                                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($line); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card booking-card">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Book Your Tour</h4>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label small text-muted text-uppercase fw-bold">
                                    <i class="bi bi-calendar-event me-1"></i>Travel Date
                                </label>
                                <?php if (count($schedules) > 0): ?>
                                    <div class="mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="useCustomDate">
                                            <label class="form-check-label small text-muted" for="useCustomDate">
                                                Use custom date instead of fixed departure
                                            </label>
                                        </div>
                                    </div>
                                    <div id="fixedDepartureSection">
                                        <select name="tour_date" id="fixedTourDate" class="form-select border-2" required>
                                            <?php foreach ($schedules as $s): ?>
                                                <option value="<?php echo $s['start_date']; ?>" <?php echo ($tour_date == $s['start_date']) ? 'selected' : ''; ?>>
                                                    <?php echo date('d M Y', strtotime($s['start_date'])); ?> 
                                                    (<?php echo $s['available_seats']; ?> seats left)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div id="customDateSection" style="display: none;">
                                        <input type="date" name="tour_date" id="customTourDate" class="form-control border-2" 
                                               value="<?php echo $tour_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Custom dates may require confirmation
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info small mb-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        No fixed departures available, but you can select any custom date!
                                    </div>
                                    <input type="date" name="tour_date" class="form-control border-2" 
                                           value="<?php echo $tour_date; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                <?php endif; ?>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small text-muted text-uppercase fw-bold">Travelers</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0"><i class="bi bi-people text-success"></i></span>
                                    <input type="number" name="pax" id="paxInput" class="form-control border-2 border-start-0 fw-bold" value="<?php echo $passengers; ?>" min="1" max="10">
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Price per person</span>
                                <span class="fw-bold">₹<?php echo number_format($tour['price'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="text-muted">Total Price</span>
                                <span class="fw-bold text-success fs-4" id="totalPriceDisplay">₹<?php echo number_format($total_amount, 2); ?></span>
                            </div>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger small mb-3"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <button type="submit" name="confirm_booking" class="btn btn-success w-100 py-3 rounded-pill fw-bold fs-5 shadow-sm">
                                Confirm & Book Now
                            </button>
                            <p class="text-center text-muted small mt-3">No hidden charges. Secure payment.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const basePrice = <?php echo $tour['price']; ?>;
        const paxInput = document.getElementById('paxInput');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');

        paxInput.addEventListener('input', function() {
            let pax = parseInt(this.value) || 1;
            if (pax < 1) pax = 1;
            if (pax > 10) pax = 10;
            const total = basePrice * pax;
            totalPriceDisplay.innerText = '₹' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        });

        const useCustomDateCheckbox = document.getElementById('useCustomDate');
        if (useCustomDateCheckbox) {
            const fixedSection = document.getElementById('fixedDepartureSection');
            const customSection = document.getElementById('customDateSection');
            const fixedDateInput = document.getElementById('fixedTourDate');
            const customDateInput = document.getElementById('customTourDate');

            useCustomDateCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    fixedSection.style.display = 'none';
                    customSection.style.display = 'block';
                    fixedDateInput.removeAttribute('required');
                    customDateInput.setAttribute('required', 'required');
                } else {
                    fixedSection.style.display = 'block';
                    customSection.style.display = 'none';
                    customDateInput.removeAttribute('required');
                    fixedDateInput.setAttribute('required', 'required');
                }
            });
        }
    </script>
</body>

</html>