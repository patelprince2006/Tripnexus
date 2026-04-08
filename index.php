<?php 
session_start(); 
include 'database/db.php';

// Prepare airports for dropdown
$airports = [];
$airport_res = db_query($conn, "SELECT airport_code, city FROM airports ORDER BY city ASC");
if ($airport_res) {
    while ($ap = db_fetch_assoc($airport_res)) {
        $airports[] = $ap;
    }
}

// Prepare bus locations
$bus_locations = [];
$bus_loc_res = db_query($conn, "SELECT DISTINCT from_location as loc FROM buses UNION SELECT DISTINCT to_location as loc FROM buses ORDER BY loc ASC");
if ($bus_loc_res) {
    while ($loc = db_fetch_assoc($bus_loc_res)) {
        $bus_locations[] = $loc['loc'];
    }
}

// Prepare train stations
$train_stations = [];
$train_st_res = db_query($conn, "SELECT DISTINCT from_station as st FROM trains UNION SELECT DISTINCT to_station as st FROM trains ORDER BY st ASC");
if ($train_st_res) {
    while ($st = db_fetch_assoc($train_st_res)) {
        $train_stations[] = $st['st'];
    }
}

// Prepare hotel cities
$hotel_cities = [];
$hotel_city_res = db_query($conn, "SELECT DISTINCT city FROM hotels ORDER BY city ASC");
if ($hotel_city_res) {
    while ($hc = db_fetch_assoc($hotel_city_res)) {
        $hotel_cities[] = $hc['city'];
    }
}

// Prepare tour locations
$tour_locations = [];
$tour_loc_res = db_query($conn, "SELECT DISTINCT location FROM tour_packages ORDER BY location ASC");
if ($tour_loc_res) {
    while ($tl = db_fetch_assoc($tour_loc_res)) {
        $tour_locations[] = $tl['location'];
    }
}

// Fetch popular tours
$popular_tours = [];
$popular_tours_res = db_query($conn, "SELECT * FROM tour_packages LIMIT 4");
if ($popular_tours_res) {
    while ($pt = db_fetch_assoc($popular_tours_res)) {
        $popular_tours[] = $pt;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripNexus | Travel Booking</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="public/style.css">

    <style>
        /* LOGIN PAGE ONLY */
        .login-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('photos/Homepage-Background.avif');
            background-size: cover;
            background-position: center;
        }

        /* Glass effect only for login */
        .login-hero .auth-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            color: white;
        }

        /* Inputs styling */
        .login-hero .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: none;
        }

        .login-hero .form-label {
            color: #eee;
        }

        /* Button */
        .login-hero .btn-navy {
            background: #2d7ef7;
            border: none;
        }

        .login-hero .btn-navy:hover {
            background: #1a5edb;
        }

        /* Links */
        .login-hero a {
            color: #fff;
        }

        /* Heading */
        .login-hero h3 {
            color: white !important;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <img src="photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
            <span>Trip<span class="text-warning">Nexus</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#about-section">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact-section">Contact us</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3 ms-lg-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2 text-end d-none d-sm-block">
                                Welcome, <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                            </div>
                            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 2px solid black;">
                                <i class="bi bi-person-fill text-dark fs-5"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="user/dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>My Dashboard</a></li>
                            <li><a class="dropdown-item" href="user/my_booking_standlone.php"><i class="bi bi-ticket-perforated me-2"></i>My Bookings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="user/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="user/login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                    <a href="user/register.html" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero d-flex align-items-center justify-content-center">
        <div class="container text-white text-center">
            <h1 class="fw-bold display-4">Search & Book Your Next Adventure</h1>
            <p class="lead mb-5">Flights, Buses, Trains & Hotels in one place</p>

            <div class="search-container mx-auto">
                <ul class="nav nav-pills custom-tabs mb-3 justify-content-center" id="pills-tab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill"
                            data-bs-target="#pills-flights"><i class="bi bi-airplane me-2"></i>Flight</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-bus"><i
                                class="bi bi-bus-front me-2"></i>Bus</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-train"><i
                                class="bi bi-train-front me-2"></i>Train</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                            data-bs-target="#pills-hotels"><i class="bi bi-building me-2"></i>Hotel</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill"
                            data-bs-target="#pills-tours"><i class="bi bi-globe-americas me-2"></i>Tour</button></li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Flight Search -->
                    <div class="tab-pane fade show active" id="pills-flights" role="tabpanel">
                        <form method="POST" action="flights/search_flight.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="modern-search-bar p-2 d-flex align-items-center">
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>From
                                        </label>
                                        <select name="departure_city" id="flightFrom" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <?php foreach ($airports as $ap): ?>
                                                <option value="<?php echo $ap['airport_code']; ?>">
                                                    <?php echo htmlspecialchars($ap['city']) . ' (' . $ap['airport_code'] . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="search-swap-btn">
                                        <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapLocations()">
                                            <i class="bi bi-arrow-left-right text-primary"></i>
                                        </button>
                                    </div>

                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>To
                                        </label>
                                        <select name="arrival_city" id="flightTo" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <?php foreach ($airports as $ap): ?>
                                                <option value="<?php echo $ap['airport_code']; ?>">
                                                    <?php echo htmlspecialchars($ap['city']) . ' (' . $ap['airport_code'] . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 200px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">Departure Date</label>
                                        <input type="date" name="departure_date" id="flightDepartureDate" class="border-0 w-100 fw-bold" style="background: none;" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                        Search Flights
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Bus Search -->
                    <div class="tab-pane fade" id="pills-bus" role="tabpanel">
                        <form method="POST" action="buses/search_bus.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="modern-search-bar p-2 d-flex align-items-center">
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                            <i class="bi bi-bus-front-fill text-danger me-1"></i>From
                                        </label>
                                        <select name="bus_from" id="busFrom" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <?php foreach ($bus_locations as $loc): ?>
                                                <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="search-swap-btn">
                                        <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapBusLocations()">
                                            <i class="bi bi-arrow-left-right text-danger"></i>
                                        </button>
                                    </div>

                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>To
                                        </label>
                                        <select name="bus_to" id="busTo" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <?php foreach ($bus_locations as $loc): ?>
                                                <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 200px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">Departure Date</label>
                                        <input type="date" name="bus_date" id="busDepartureDate" class="border-0 w-100 fw-bold" style="background: none;" required>
                                    </div>

                                    <button type="submit" class="btn btn-danger btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                        Search Bus
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Train Search -->
                    <div class="tab-pane fade" id="pills-train" role="tabpanel">
                        <form method="POST" action="trains/search_train.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                    <span><i class="bi bi-info-circle"></i> PNR Status</span>
                                    <span><i class="bi bi-info-circle"></i> Live Train Status</span>
                                </div>
                                <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                    <div class="search-input-group border-end px-3 py-2" style="flex: 1; min-width: 180px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-search text-info me-1"></i>Train Number</label>
                                        <input type="text" name="train_no" placeholder="e.g. 12051" class="border-0 w-100 fw-bold" style="background: none;">
                                    </div>
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-train-front-fill text-info me-1"></i>From Station</label>
                                        <select name="train_from" id="trainFrom" class="border-0 w-100 fw-bold" style="background: none;">
                                            <option value="">Select Station</option>
                                            <option value="NDLS">New Delhi (NDLS)</option>
                                            <option value="BCT">Mumbai Central (BCT)</option>
                                            <option value="MAS">Chennai Central (MAS)</option>
                                            <option value="HWH">Howrah (HWH)</option>
                                            <option value="SBC">Bangalore (SBC)</option>
                                            <option value="HYB">Hyderabad (HYB)</option>
                                            <option value="PUNE">Pune (PUNE)</option>
                                            <option value="JAI">Jaipur (JAI)</option>
                                            <option value="AMD">Ahmedabad (AMD)</option>
                                            <option value="LKO">Lucknow (LKO)</option>
                                            <option value="PNBE">Patna (PNBE)</option>
                                            <option value="BPL">Bhopal (BPL)</option>
                                            <option value="INDB">Indore (INDB)</option>
                                            <option value="VSKP">Visakhapatnam (VSKP)</option>
                                            <option value="GHY">Guwahati (GHY)</option>
                                            <option value="AGC">Agra Cantt (AGC)</option>
                                            <option value="BSB">Varanasi (BSB)</option>
                                            <option value="CNB">Kanpur Central (CNB)</option>
                                            <option value="MYS">Mysuru (MYS)</option>
                                            <option value="CBE">Coimbatore (CBE)</option>
                                            <option value="ND">Nadiad (ND)</option>
                                        </select>
                                    </div>

                                    <div class="search-swap-btn">
                                        <button type="button" class="btn btn-light rounded-circle shadow-sm border" onclick="swapTrainLocations()">
                                            <i class="bi bi-arrow-left-right text-info"></i>
                                        </button>
                                    </div>

                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-info me-1"></i>To Station</label>
                                        <select name="train_to" id="trainTo" class="border-0 w-100 fw-bold" style="background: none;">
                                            <option value="">Select Station</option>
                                            <option value="NDLS">New Delhi (NDLS)</option>
                                            <option value="BCT">Mumbai Central (BCT)</option>
                                            <option value="MAS">Chennai Central (MAS)</option>
                                            <option value="HWH">Howrah (HWH)</option>
                                            <option value="SBC">Bangalore (SBC)</option>
                                            <option value="HYB">Hyderabad (HYB)</option>
                                            <option value="PUNE">Pune (PUNE)</option>
                                            <option value="JAI">Jaipur (JAI)</option>
                                            <option value="AMD">Ahmedabad (AMD)</option>
                                            <option value="LKO">Lucknow (LKO)</option>
                                            <option value="PNBE">Patna (PNBE)</option>
                                            <option value="BPL">Bhopal (BPL)</option>
                                            <option value="INDB">Indore (INDB)</option>
                                            <option value="VSKP">Visakhapatnam (VSKP)</option>
                                            <option value="GHY">Guwahati (GHY)</option>
                                            <option value="AGC">Agra Cantt (AGC)</option>
                                            <option value="BSB">Varanasi (BSB)</option>
                                            <option value="CNB">Kanpur Central (CNB)</option>
                                            <option value="MYS">Mysuru (MYS)</option>
                                            <option value="CBE">Coimbatore (CBE)</option>
                                            <option value="ND">Nadiad (ND)</option>
                                        </select>
                                    </div>
                                    <div class="search-input-group px-3 py-2" style="min-width: 200px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-event text-info me-1"></i>Journey Date</label>
                                        <input type="date" name="train_date" class="border-0 w-100 fw-bold" required>
                                    </div>
                                    <button type="submit" class="btn btn-info btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                        Search Train
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Hotel Search -->
                    <div class="tab-pane fade" id="pills-hotels" role="tabpanel">
                        <form method="POST" action="hotels/search_hotel.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                    <span><i class="bi bi-star-fill text-warning"></i> 5-Star</span>
                                    <span><i class="bi bi-star-fill text-warning"></i> Villas</span>
                                    <span><i class="bi bi-star-fill text-warning"></i> Resorts</span>
                                </div>
                                <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-building-fill text-warning me-1"></i>City / Hotel</label>
                                        <select name="hotel_city" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <?php foreach ($hotel_cities as $hc): ?>
                                                <option value="<?php echo htmlspecialchars($hc); ?>"><?php echo htmlspecialchars($hc); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="small text-muted">Search by city</div>
                                    </div>
                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 140px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-check text-warning me-1"></i>Check-in</label>
                                        <input type="date" name="check_in" id="hotelCheckIn" class="border-0 w-100 fw-bold" required>
                                    </div>
                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 140px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-x text-warning me-1"></i>Check-out</label>
                                        <input type="date" name="check_out" id="hotelCheckOut" class="border-0 w-100 fw-bold" required>
                                    </div>
                                    <div class="search-input-group px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-people-fill text-warning me-1"></i>Guests</label>
                                        <input type="text" name="guests" class="border-0 w-100 fw-bold" placeholder="2 Guests, 1 Room">
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-search rounded-pill px-4 py-3 ms-2 fw-bold text-dark shadow-lg">
                                        Search Hotels
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tour Search -->
                    <div class="tab-pane fade" id="pills-tours" role="tabpanel">
                        <form method="POST" action="tours/search_tour.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                    <span><i class="bi bi-star-fill text-success"></i> Group Tours</span>
                                    <span><i class="bi bi-star-fill text-success"></i> Private Tours</span>
                                    <span><i class="bi bi-star-fill text-success"></i> Adventure</span>
                                </div>
                                <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-success me-1"></i>Where to?</label>
                                        <select name="tour_location" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select Location</option>
                                            <?php foreach ($tour_locations as $tl): ?>
                                                <option value="<?php echo htmlspecialchars($tl); ?>"><?php echo htmlspecialchars($tl); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="small text-muted">Destination city or country</div>
                                    </div>
                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 200px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-event text-success me-1"></i>Travel Date</label>
                                        <input type="date" name="tour_date" class="border-0 w-100 fw-bold" required>
                                    </div>
                                    <div class="search-input-group px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-people-fill text-success me-1"></i>Persons</label>
                                        <input type="number" name="passengers" class="border-0 w-100 fw-bold" value="1" min="1">
                                    </div>
                                    <button type="submit" class="btn btn-success btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                        Search Tours
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <h2 class="text-center mb-4 fw-bold">Popular Destinations</h2>
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <img src="photos/Manali2.jpg" class="card-img-top" alt="Manali">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Manali</h5>
                        <p class="card-text text-muted">From ₹4,000/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <img src="photos/Agra.jpg" class="card-img-top" alt="Agra">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Agra</h5>
                        <p class="card-text text-muted">From ₹3,500/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <img src="photos/Goa.jpg" class="card-img-top" alt="Goa">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Goa</h5>
                        <p class="card-text text-muted">From ₹5,000/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <img src="photos/Mumbai.jpg" class="card-img-top" alt="Mumbai">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Mumbai</h5>
                        <p class="card-text text-muted">From ₹4,200/night</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Tour Packages -->
    <section class="container my-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-0">Exclusive Tour Packages</h2>
                <p class="text-muted mb-0">Handpicked adventures for your next trip</p>
            </div>
            <a href="tours/search_tour.php" class="btn btn-outline-success rounded-pill px-4">View All Tours</a>
        </div>
        <div class="row g-4">
            <?php foreach ($popular_tours as $tour): ?>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm border-0 tour-card overflow-hidden">
                        <div class="position-relative">
                            <img src="<?php echo !empty($tour['main_image']) ? htmlspecialchars($tour['main_image']) : 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=500&q=80'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['name']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-dark shadow-sm rounded-pill px-3 py-2">
                                    <i class="bi bi-clock me-1 text-success"></i> <?php echo htmlspecialchars($tour['duration']); ?> Days
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-success me-1"></i><?php echo htmlspecialchars($tour['location']); ?></div>
                            <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($tour['name']); ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <div class="small text-muted">Starting from</div>
                                    <div class="fw-bold text-success fs-5">₹<?php echo number_format($tour['price'], 2); ?></div>
                                </div>
                                <a href="tours/booking.php?id=<?php echo $tour['id']; ?>" class="btn btn-success rounded-pill px-4">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="about-section" class="container my-5 py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">About TripNexus</h2>
                <p class="lead">TripNexus is an intelligent travel management platform that integrates destination
                    recommendations, hotel booking, and real-time tracking into a single system.</p>
                <p>Our AI-powered chatbot acts as your virtual travel assistant, helping you find the best deals and
                    providing instant responses to your travel queries.</p>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=500&q=80"
                    class="img-fluid rounded shadow" alt="Travel Planning">
            </div>
        </div>
    </section>

    <section id="contact-section" class="container my-5 py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Contact Us</h2>
            <p class="text-muted">Reach out to us through any of these support channels</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-7">
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <h4 class="fw-bold mb-3">Send us a message</h4>
                    <form action="user/contact_us.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">First Name</label>
                                <input type="text" name="first_name" class="form-control bg-light border-0 py-2" placeholder="Your first name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control bg-light border-0 py-2" placeholder="Your last name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="Enter your email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Message</label>
                                <textarea name="message" class="form-control bg-light border-0 py-2" rows="4" placeholder="How can we help?" required></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-dark px-5 py-2 fw-bold rounded-pill">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="contact-card p-4 rounded-4 shadow text-white h-100" style="background-color: #0d2137;">
                    <h4 class="fw-bold mb-4">Hi! We are always here to help you.</h4>

                    <div
                        class="d-flex align-items-center mb-4 p-3 rounded-3 border border-secondary bg-dark bg-opacity-25">
                        <div class="me-3"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div class="small text-secondary">Hotline:</div>
                            <div class="fw-bold">+91 98765 43210</div>
                        </div>
                    </div>

                    <div
                        class="d-flex align-items-center mb-4 p-3 rounded-3 border border-secondary bg-dark bg-opacity-25">
                        <div class="me-3"><i class="bi bi-chat-dots"></i></div>
                        <div>
                            <div class="small text-secondary">Email:</div>
                            <div class="fw-bold">tripnexus.buiseness@gmail.com</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <p class="small text-secondary mb-2">Connect with us</p>
                        <div class="d-flex gap-3 fs-5">
                            <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-white"><i class="bi bi-twitter-x"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved | <a href="admin/login.php" class="text-white-50 text-decoration-none small">Admin</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/script.js"></script>
    <script>
        function swapTrainLocations() {
            const from = document.getElementById('trainFrom');
            const to = document.getElementById('trainTo');
            const temp = from.value;
            from.value = to.value;
            to.value = temp;
        }
    </script>
</body>
</html>
