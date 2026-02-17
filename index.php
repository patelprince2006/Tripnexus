<?php session_start(); ?>
<div class="collapse navbar-collapse" id="navMenu">
    <ul class="navbar-nav mx-auto">
    </ul>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="btn btn-warning rounded-pill px-4 ms-3 fw-bold">My Dashboard</a>
    <?php else: ?>
        <a href="login.html" class="btn btn-outline-light rounded-pill px-4 ms-3 fw-bold">Login</a>
    <?php endif; ?>
</div>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripNexus | Travel Booking</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top px-4">
        <a class="navbar-brand fw-bold" href="#">
            Trip<span class="text-warning">Nexus</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>

                <!-- <li class="nav-item"><a class="nav-link" href="#">About</a></li> -->
                <li class="nav-item"><a class="nav-link" href="#about-section">About</a></li>

                <!-- <li class="nav-item"><a class="nav-link" href="#">Contact us</a></li> -->
                <li class="nav-item"><a class="nav-link" href="#contact-section">Contact us</a></li>
            </ul>


            <!-- <div class="d-flex gap-2 ms-lg-3">
                <a href="login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                <a href="register.html" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Register</a>
            </div> -->

            <!-- When user is logged in then this will be exectue -->
            <div class="d-flex align-items-center gap-3 ms-lg-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2 text-end d-none d-sm-block">
                               Welcome,<span class="fw-bold"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                            </div>
                            <div class="rounded-circle bg-whi d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-fill text-dark fs-5"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>My Dashboard</a></li>
                            <li><a class="dropdown-item" href="my_booking_standlone.php"><i class="bi bi-ticket-perforated me-2"></i>My Bookings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                    <a href="register.html" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Register</a>
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
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Flight Search -->
                    <div class="tab-pane fade show active" id="pills-flights" role="tabpanel">
                        <form method="POST" action="search_flight.php">
                            <div class="modern-search-wrapper shadow-lg">
                                <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="trip_type" id="oneWay" value="oneWay" checked>
                                        <label class="form-check-label" for="oneWay">One Way</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="trip_type" id="roundTrip" value="roundTrip">
                                        <label class="form-check-label" for="roundTrip">Round Trip</label>
                                    </div>
                                </div>

                                <div class="modern-search-bar p-2 d-flex align-items-center">
                                    <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">
                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>From
                                        </label>
                                        <select name="departure_city" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <option value="BOM">Mumbai (BOM)</option>
                                            <option value="DEL">Delhi (DEL)</option>
                                            <option value="BLR">Bangalore (BLR)</option>
                                            <option value="HYD">Hyderabad (HYD)</option>
                                            <option value="MAA">Chennai (MAA)</option>
                                            <option value="COK">Kochi (COK)</option>
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
                                        <select name="arrival_city" class="border-0 w-100 fw-bold" style="background: none;" required>
                                            <option value="">Select</option>
                                            <option value="BOM">Mumbai (BOM)</option>
                                            <option value="DEL">Delhi (DEL)</option>
                                            <option value="BLR">Bangalore (BLR)</option>
                                            <option value="HYD">Hyderabad (HYD)</option>
                                            <option value="MAA">Chennai (MAA)</option>
                                            <option value="COK">Kochi (COK)</option>
                                        </select>
                                    </div>

                                    <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">Departure</label>
                                        <input type="date" name="departure_date" class="border-0 w-100 fw-bold" style="background: none;" required>
                                    </div>

                                    <div class="search-input-group px-3 py-2" style="min-width: 150px;">
                                        <label class="d-block small text-uppercase fw-bold text-muted mb-1">Return</label>
                                        <input type="date" name="return_date" class="border-0 w-100 fw-bold" style="background: none;">
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-search rounded-pill px-4 py-3 ms-2 fw-bold text-white shadow-lg">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Bus Search -->
                    <div class="tab-pane fade" id="pills-bus" role="tabpanel">
                        <div class="modern-search-wrapper shadow-lg">
                            <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                <span class="badge bg-light text-dark border"><i class="bi bi-check-circle-fill text-success me-1"></i>AC / Non-AC</span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-check-circle-fill text-success me-1"></i>Sleeper / Seater</span>
                            </div>
                            <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-bus-front-fill text-danger me-1"></i>From</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="Bangalore">
                                    <div class="small text-muted">Majestic Bus Stand</div>
                                </div>
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>To</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="Hyderabad">
                                    <div class="small text-muted">MG Bus Station</div>
                                </div>
                                <div class="search-input-group px-3 py-2" style="min-width: 200px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-event text-danger me-1"></i>Travel Date</label>
                                    <input type="text" class="border-0 w-100 fw-bold" onfocus="(this.type='date')" placeholder="Select Date">
                                    <div class="small text-muted">Select Travel Date</div>
                                </div>
                                <button class="btn btn-danger btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                    Search Bus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Train Search -->
                    <div class="tab-pane fade" id="pills-train" role="tabpanel">
                        <div class="modern-search-wrapper shadow-lg">
                            <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                <span><i class="bi bi-info-circle"></i> PNR Status</span>
                                <span><i class="bi bi-info-circle"></i> Live Train Status</span>
                            </div>
                            <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-train-front-fill text-info me-1"></i>From Station</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="Chennai Central">
                                    <div class="small text-muted">MAS</div>
                                </div>
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-geo-alt-fill text-info me-1"></i>To Station</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="Coimbatore Jn">
                                    <div class="small text-muted">CBE</div>
                                </div>
                                <div class="search-input-group px-3 py-2" style="min-width: 200px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-event text-info me-1"></i>Journey Date</label>
                                    <input type="text" class="border-0 w-100 fw-bold" onfocus="(this.type='date')" placeholder="Select Date">
                                    <div class="small text-muted">Select Date</div>
                                </div>
                                <button class="btn btn-info btn-search rounded-pill px-5 py-3 ms-2 fw-bold text-white shadow-lg">
                                    Search Train
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hotel Search -->
                    <div class="tab-pane fade" id="pills-hotels" role="tabpanel">
                        <div class="modern-search-wrapper shadow-lg">
                            <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                                <span><i class="bi bi-star-fill text-warning"></i> 5-Star</span>
                                <span><i class="bi bi-star-fill text-warning"></i> Villas</span>
                                <span><i class="bi bi-star-fill text-warning"></i> Resorts</span>
                            </div>
                            <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                                <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-building-fill text-warning me-1"></i>City / Hotel</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="Goa, India">
                                    <div class="small text-muted">Search by city or hotel name</div>
                                </div>
                                <div class="search-input-group border-end px-3 py-2" style="min-width: 140px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-check text-warning me-1"></i>Check-in</label>
                                    <input type="text" class="border-0 w-100 fw-bold" onfocus="(this.type='date')" placeholder="Add Date">
                                </div>
                                <div class="search-input-group border-end px-3 py-2" style="min-width: 140px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-calendar-x text-warning me-1"></i>Check-out</label>
                                    <input type="text" class="border-0 w-100 fw-bold" onfocus="(this.type='date')" placeholder="Add Date">
                                </div>
                                <div class="search-input-group px-3 py-2" style="min-width: 150px;">
                                    <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-people-fill text-warning me-1"></i>Guests</label>
                                    <input type="text" class="border-0 w-100 fw-bold" placeholder="2 Guests, 1 Room">
                                </div>
                                <button class="btn btn-warning btn-search rounded-pill px-4 py-3 ms-2 fw-bold text-dark shadow-lg">
                                    Search Hotels
                                </button>
                            </div>
                        </div>
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
                    <!-- <img src="https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=400&q=80" -->
                    <img src="photos/Manali2.jpg" class="card-img-top" alt="Manali">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Manali</h5>
                        <p class="card-text text-muted">From ₹4,000/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <!-- <img src="https://images.unsplash.com/photo-1564507592333-c60657eea523?w=400&q=80" -->
                    <img src="photos/Agra.jpg" class="card-img-top" alt="Agra">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Agra</h5>
                        <p class="card-text text-muted">From ₹3,500/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <!-- <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=400&q=80" -->
                    <img src="photos/Goa.jpg" class="card-img-top" alt="Goa">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Goa</h5>
                        <p class="card-text text-muted">From ₹5,000/night</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <!-- <img src="https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=400&q=80" -->
                    <img src="photos/Mumbai.jpg" class="card-img-top" alt="Mumbai">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Mumbai</h5>
                        <p class="card-text text-muted">From ₹4,200/night</p>
                    </div>
                </div>
            </div>
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

    <!-- <section id="contact-section" class="container my-5 py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Contact Us</h2>
                <p class="text-muted mb-4">Have questions about your next adventure? Our team at TripNexus is here to
                    help you 24/7.</p>

                <form>
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold px-4">Send Message</button>
                </form>
            </div>

            <div class="col-lg-6 text-center mt-4 mt-lg-0">
                <img src="https://images.unsplash.com/photo-1534536281715-e28d76689b4d?w=800&q=90"
                    class="img-fluid rounded shadow" alt="Contact TripNexus">
            </div>
        </div>
    </section> -->

    <section id="contact-section" class="container my-5 py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Contact Us</h2>
            <p class="text-muted">Reach out to us through any of these support channels</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-7">
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <h4 class="fw-bold mb-3">Send us a message</h4>
                    <form action="contact_us.php" method="POST">
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
                            <div class="fw-bold">support@tripnexus.com</div>
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
        <p class="mb-0">© 2026 TripNexus | All Rights Reserved | <a href="admin/login.php" class="text-white-50 text-decoration-none small">Admin</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>
</body>

</html>