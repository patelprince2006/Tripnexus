<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Approximate coordinates for common Indian cities
$cityCoords = [
    'Mumbai' => ['lat' => 19.0760, 'lng' => 72.8777],
    'Delhi' => ['lat' => 28.6139, 'lng' => 77.2090],
    'Bangalore' => ['lat' => 12.9716, 'lng' => 77.5946],
    'Hyderabad' => ['lat' => 17.3850, 'lng' => 78.4867],
    'Chennai' => ['lat' => 13.0827, 'lng' => 80.2707],
    'Kolkata' => ['lat' => 22.5726, 'lng' => 88.3639],
    'Pune' => ['lat' => 18.5204, 'lng' => 73.8567],
    'Ahmedabad' => ['lat' => 23.0225, 'lng' => 72.5714],
    'Jaipur' => ['lat' => 26.9124, 'lng' => 75.7873],
    'Goa' => ['lat' => 15.2993, 'lng' => 74.1240],
    'Agra' => ['lat' => 27.1767, 'lng' => 78.0081],
    'Varanasi' => ['lat' => 25.3176, 'lng' => 83.0100],
    'Udaipur' => ['lat' => 24.5854, 'lng' => 73.7125],
    'Kerala' => ['lat' => 10.8505, 'lng' => 76.2711],
    'Kochi' => ['lat' => 9.9312, 'lng' => 76.2673],
    'Rajasthan' => ['lat' => 27.0238, 'lng' => 74.2179],
    'Himachal Pradesh' => ['lat' => 31.1048, 'lng' => 77.1734],
    'Shimla' => ['lat' => 31.1048, 'lng' => 77.1734],
    'Manali' => ['lat' => 32.2396, 'lng' => 77.1887],
    'Leh' => ['lat' => 34.1526, 'lng' => 77.5771],
    'Srinagar' => ['lat' => 34.0837, 'lng' => 74.7973],
    'Bengaluru' => ['lat' => 12.9716, 'lng' => 77.5946],
    'Surat' => ['lat' => 21.1702, 'lng' => 72.8311],
    'Lucknow' => ['lat' => 26.8467, 'lng' => 80.9462],
    'Kanpur' => ['lat' => 26.4499, 'lng' => 80.3319],
    'Nagpur' => ['lat' => 21.1458, 'lng' => 79.0882],
    'Indore' => ['lat' => 22.7196, 'lng' => 75.8577],
    'Thane' => ['lat' => 19.2183, 'lng' => 72.9781],
    'Bhopal' => ['lat' => 23.2599, 'lng' => 77.4126],
    'Visakhapatnam' => ['lat' => 17.6868, 'lng' => 83.2185],
    'Patna' => ['lat' => 25.5941, 'lng' => 85.1376],
    'Vadodara' => ['lat' => 22.3072, 'lng' => 73.1812],
    'Ghaziabad' => ['lat' => 28.6692, 'lng' => 77.4538],
    'Ludhiana' => ['lat' => 30.9010, 'lng' => 75.8573],
    'Coimbatore' => ['lat' => 11.0168, 'lng' => 76.9558],
    'Madurai' => ['lat' => 9.9252, 'lng' => 78.1198],
    'Jabalpur' => ['lat' => 23.1815, 'lng' => 79.9864],
    'Gwalior' => ['lat' => 26.2183, 'lng' => 78.1828],
    'Vijayawada' => ['lat' => 16.5062, 'lng' => 80.6480],
    'Rajkot' => ['lat' => 22.3039, 'lng' => 70.8022],
    'Jamshedpur' => ['lat' => 22.8046, 'lng' => 86.2029],
    'Mysore' => ['lat' => 12.2958, 'lng' => 76.6394],
    'Nashik' => ['lat' => 19.9975, 'lng' => 73.7898],
    'Faridabad' => ['lat' => 28.4089, 'lng' => 77.3178],
    'Meerut' => ['lat' => 28.9845, 'lng' => 77.7064],
    'Rajkot' => ['lat' => 22.3039, 'lng' => 70.8022],
    'Kalyan' => ['lat' => 19.2502, 'lng' => 73.1602],
    'Vasai' => ['lat' => 19.4053, 'lng' => 72.8418],
    'Dhanbad' => ['lat' => 23.7957, 'lng' => 86.4304],
    'Aurangabad' => ['lat' => 19.8762, 'lng' => 75.3433],
    'Amritsar' => ['lat' => 31.6340, 'lng' => 74.8723],
    'Allahabad' => ['lat' => 25.4358, 'lng' => 81.8463],
    'Ranchi' => ['lat' => 23.3441, 'lng' => 85.3096],
    'Howrah' => ['lat' => 22.5804, 'lng' => 88.3299],
    'Guntur' => ['lat' => 16.3067, 'lng' => 80.4365],
    'Jodhpur' => ['lat' => 26.2389, 'lng' => 73.0243],
    'Raipur' => ['lat' => 21.2514, 'lng' => 81.6296],
    'Kota' => ['lat' => 25.2138, 'lng' => 75.8648],
    'Guwahati' => ['lat' => 26.2006, 'lng' => 91.7688],
    'Chandigarh' => ['lat' => 30.7333, 'lng' => 76.7794],
    'Solapur' => ['lat' => 17.6599, 'lng' => 75.9064],
    'Hubli' => ['lat' => 15.3647, 'lng' => 75.1240],
    'Dharwad' => ['lat' => 15.4589, 'lng' => 75.0078],
    'Salem' => ['lat' => 11.6643, 'lng' => 78.1460],
    'Aligarh' => ['lat' => 27.8974, 'lng' => 78.0880],
    'Gurgaon' => ['lat' => 28.4595, 'lng' => 77.0266],
    'Moradabad' => ['lat' => 28.8386, 'lng' => 78.7733],
    'Bareilly' => ['lat' => 28.3670, 'lng' => 79.4304],
    'Jalandhar' => ['lat' => 31.3260, 'lng' => 75.5762],
    'Warangal' => ['lat' => 17.9689, 'lng' => 79.5941],
    'Mangalore' => ['lat' => 12.9141, 'lng' => 74.8560],
    'Tirupati' => ['lat' => 13.6288, 'lng' => 79.4192],
    'Kurnool' => ['lat' => 15.8281, 'lng' => 78.0373],
    'Nellore' => ['lat' => 14.4426, 'lng' => 79.9865],
    'Belgaum' => ['lat' => 15.8497, 'lng' => 74.4977],
    'Ambala' => ['lat' => 30.3782, 'lng' => 76.7767],
    'Dehradun' => ['lat' => 30.3165, 'lng' => 78.0322],
    'Ujjain' => ['lat' => 23.1793, 'lng' => 75.7849],
    'Pondicherry' => ['lat' => 11.9139, 'lng' => 79.8145],
    'Andaman' => ['lat' => 11.6670, 'lng' => 92.7359],
    'Darjeeling' => ['lat' => 27.0462, 'lng' => 88.2687],
    'Mussoorie' => ['lat' => 30.4591, 'lng' => 78.0663],
    'Ooty' => ['lat' => 11.4064, 'lng' => 76.6932],
    'Kodaikanal' => ['lat' => 10.2381, 'lng' => 77.4892],
    'Munnar' => ['lat' => 10.0889, 'lng' => 77.0595],
    'Rishikesh' => ['lat' => 30.0869, 'lng' => 78.2676],
    'Haridwar' => ['lat' => 29.9457, 'lng' => 78.1642],
    'Pushkar' => ['lat' => 26.4906, 'lng' => 74.5551],
    'Mount Abu' => ['lat' => 24.5924, 'lng' => 72.7156],
    'Khajuraho' => ['lat' => 24.8520, 'lng' => 79.9274],
    'Hampi' => ['lat' => 15.3350, 'lng' => 76.4600],
    'Badami' => ['lat' => 15.9129, 'lng' => 75.6800],
    'Pattadakal' => ['lat' => 15.9547, 'lng' => 75.8160],
    'Auroville' => ['lat' => 12.0061, 'lng' => 79.8112],
    'Mahabalipuram' => ['lat' => 12.6189, 'lng' => 80.1939],
    'Thanjavur' => ['lat' => 10.7867, 'lng' => 79.1378],
    'Trichy' => ['lat' => 10.8505, 'lng' => 78.6997],
    'Madurai' => ['lat' => 9.9252, 'lng' => 78.1198],
    'Rameswaram' => ['lat' => 9.2876, 'lng' => 79.3129],
    'Kanyakumari' => ['lat' => 8.0883, 'lng' => 77.5385],
    'Port Blair' => ['lat' => 11.6670, 'lng' => 92.7359],
];

// Auto-populate coordinates for hotels that don't have them
$res = db_query($conn, "SELECT hotel_id, name, city FROM hotels WHERE latitude IS NULL OR longitude IS NULL");
while ($hotel = db_fetch_assoc($res)) {
    $city = trim($hotel['city']);
    $found = false;
    
    if (isset($cityCoords[$city])) {
        $lat = $cityCoords[$city]['lat'];
        $lng = $cityCoords[$city]['lng'];
        $found = true;
    } else {
        foreach ($cityCoords as $cityName => $coords) {
            if (stripos($city, $cityName) !== false || stripos($cityName, $city) !== false) {
                $lat = $coords['lat'];
                $lng = $coords['lng'];
                $found = true;
                break;
            }
        }
    }
    
    if ($found) {
        db_query($conn, "UPDATE hotels SET latitude = ?, longitude = ? WHERE hotel_id = ?", [$lat, $lng, $hotel['hotel_id']]);
    }
}

// Load user's wishlist for hotels
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wl_res = db_query($conn, "SELECT item_id FROM wishlist WHERE user_id = ? AND item_type = 'hotel'", [$_SESSION['user_id']]);
    if ($wl_res) {
        while ($wl = db_fetch_assoc($wl_res)) {
            $user_wishlist[] = (int)$wl['item_id'];
        }
    }
}

// Load hotel cities from DB for dropdown
$cities = [];
$city_res = db_query($conn, "SELECT DISTINCT city FROM hotels ORDER BY city ASC");
if ($city_res) {
    while ($c = db_fetch_assoc($city_res)) {
        $cities[] = $c['city'];
    }
}

$results = [];
$search_performed = false;
$city = '';
$check_in = date('Y-m-d');
$check_out = date('Y-m-d', strtotime('+1 day'));
$guests = 1;
$sort_price = $_POST['sort_price'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $city = $_POST['hotel_city'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];

    // Server-side validation: check-out must be after check-in
    if (strtotime($check_out) <= strtotime($check_in)) {
        echo "<script>alert('Check-out date must be after Check-in date.'); history.back();</script>";
        exit();
    }

    // --- Start Google Hotels (SerpApi) Integration ---
    $serp_api_key = "c549c4393c35299ce51ce61fbf77dd1917e9614c698f6761cbfd54a5748bb2ef";
    $query_params = [
        "engine" => "google_hotels",
        "q" => $city . " Hotels",
        "check_in_date" => $check_in,
        "check_out_date" => $check_out,
        "adults" => $guests,
        "currency" => "INR",
        "api_key" => $serp_api_key
    ];

    $api_url = "https://serpapi.com/search.json?" . http_build_query($query_params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if (!$err) {
        $api_data = json_decode($response, true);
        if (isset($api_data['properties']) && is_array($api_data['properties'])) {
            foreach ($api_data['properties'] as $hotel) {
                $hotel_name = $hotel['name'] ?? 'Google Hotel';
                $hotel_price = $hotel['total_rate']['lowest'] ?? ($hotel['rate_per_night']['lowest'] ?? rand(2000, 8000));
                
                // Remove non-numeric characters from price if it's a string like "₹5,000"
                if (is_string($hotel_price)) {
                    $hotel_price = (int) preg_replace('/[^0-9]/', '', $hotel_price);
                }

                $rating = $hotel['overall_rating'] ?? 4.0;
                $description = $hotel['description'] ?? 'Stay at ' . $hotel_name;
                $thumbnail = $hotel['thumbnail'] ?? '../photos/hotel1.jpg';
                $lat = $hotel['gps_coordinates']['latitude'] ?? null;
                $lng = $hotel['gps_coordinates']['longitude'] ?? null;

                // Sync with local DB to allow booking
                $check_res = db_query($conn, "SELECT hotel_id FROM hotels WHERE name = ?", [$hotel_name]);
                $hotel_row = db_fetch_assoc($check_res);
                
                if (!$hotel_row) {
                    db_query($conn, "INSERT INTO hotels (name, city, address, description, price_per_night, rating, main_image, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$hotel_name, $city, $city, $description, $hotel_price, $rating, $thumbnail, $lat, $lng]);
                } else if ($lat && $lng) {
                    db_query($conn, "UPDATE hotels SET latitude = ?, longitude = ? WHERE hotel_id = ?", [$lat, $lng, $hotel_row['hotel_id']]);
                }
            }
        }
    }

    $search_query = "SELECT * FROM hotels 
                     WHERE city LIKE ? 
                     OR name LIKE ?
                     ORDER BY rating DESC";
    
    $res = db_query($conn, $search_query, array("%$city%", "%$city%"));

    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $results[] = $row;
        }
    }
    $search_performed = true;

    // Apply Sorting
    if (!empty($results) && !empty($sort_price)) {
        usort($results, function($a, $b) use ($sort_price) {
            if ($sort_price === 'low_to_high') {
                return $a['price_per_night'] <=> $b['price_per_night'];
            } elseif ($sort_price === 'high_to_low') {
                return $b['price_per_night'] <=> $a['price_per_night'];
            }
            return 0;
        });
    }
} else {
    // Recommendation System: Show personalized or featured hotels
    $is_personalized = false;

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        // 1. Get cities from recent bookings
        $history_res = db_query($conn, "SELECT DISTINCT city FROM bookings b 
                                       JOIN hotels h ON b.reference_id = h.hotel_id 
                                       WHERE b.user_id = ? AND b.booking_type = 'hotel' 
                                       ORDER BY b.booking_date DESC LIMIT 5", [$uid]);
        $pref_cities = [];
        while ($h = db_fetch_assoc($history_res)) {
            $pref_cities[] = $h['city'];
        }

        // 2. Get cities from wishlist
        $wish_res = db_query($conn, "SELECT DISTINCT city FROM wishlist w 
                                    JOIN hotels h ON w.item_id = h.hotel_id 
                                    WHERE w.user_id = ? AND w.item_type = 'hotel' LIMIT 5", [$uid]);
        while ($w = db_fetch_assoc($wish_res)) {
            $pref_cities[] = $w['city'];
        }

        $pref_cities = array_unique(array_filter($pref_cities));

        if (!empty($pref_cities)) {
            $placeholders = implode(',', array_fill(0, count($pref_cities), '?'));
            $rec_query = "SELECT * FROM hotels 
                         WHERE city IN ($placeholders)
                         ORDER BY rating DESC LIMIT 6";
            $rec_res = db_query($conn, $rec_query, $pref_cities);
            if ($rec_res && db_num_rows($rec_res) > 0) {
                while ($row = db_fetch_assoc($rec_res)) {
                    $results[] = $row;
                }
                $is_personalized = true;
            }
        }
    }

    if (empty($results)) {
        // Fallback: Show top-rated hotels across all cities
        $featured_res = db_query($conn, "SELECT * FROM hotels ORDER BY rating DESC LIMIT 6");
        if ($featured_res && db_num_rows($featured_res) > 0) {
            while ($row = db_fetch_assoc($featured_res)) {
                $results[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hotels | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
    <link rel="stylesheet" href="../chatbot/chatbot.css">
    <style>
        .hotel-card {
            transition: all 0.3s ease;
        }
        .hotel-card:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }
        #hotelMap {
            /* height: 400px; */
            width: 100%;
            border-radius: 15px;
            margin-bottom: 30px;
            z-index: 1;
        }
        .hotel-map-small {
            height: 200px;
            width: 100%;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="../index.php">
                <img src="../photos/logo.png" alt="TripNexus Logo" style="height: 40px; width: auto;">
                <span>Trip<span class="text-warning">Nexus</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#about-section">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact-section">Contact us</a></li>
                </ul>

                <div class="d-flex align-items-center gap-3 ms-lg-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-sm-inline">Welcome, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-fill text-dark fs-6"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li><a class="dropdown-item" href="../user/dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="../user/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../user/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="../user/login.html" class="btn btn-outline-light rounded-pill px-4 fw-bold">Login</a>
                        <a href="../user/register.html" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('../photos/Homepage-Background.avif'); background-size: cover; background-position: center; padding: 80px 0;">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Find Your Perfect Stay</h1>
            <p class="lead mb-0 opacity-75">Explore the best hotels at the most affordable prices</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="search-container mx-auto" style="margin-top: -50px;">
            <ul class="nav nav-pills custom-tabs mb-3 justify-content-center">
                <li class="nav-item"><a href="../flights/search_flight.php" class="nav-link"><i class="bi bi-airplane me-2"></i>Flight</a></li>
                <li class="nav-item"><a href="../buses/search_bus.php" class="nav-link"><i class="bi bi-bus-front me-2"></i>Bus</a></li>
                <li class="nav-item"><a href="../trains/search_train.php" class="nav-link"><i class="bi bi-train-front me-2"></i>Train</a></li>
                <li class="nav-item"><button class="nav-link active"><i class="bi bi-building me-2"></i>Hotel</button></li>
            </ul>

            <div class="modern-search-wrapper shadow-lg">
                <form method="POST" action="">
                    <div class="filter-row px-4 pt-3 d-flex gap-3 small text-muted">
                        <span><i class="bi bi-info-circle"></i> Worldwide availability</span>
                        <span><i class="bi bi-info-circle"></i> Best price guarantee</span>
                    </div>
                    <div class="modern-search-bar p-2 d-flex flex-wrap align-items-center">
                        <div class="search-input-group border-end flex-grow-1 px-3 py-2">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-building-fill text-warning me-1"></i>City / Hotel</label>
                            <input type="text" name="hotel_city" class="border-0 w-100 fw-bold" style="background: none;" placeholder="Type any city (e.g., Paris, London, Tokyo)" value="<?php echo htmlspecialchars($city); ?>" required list="citySuggestions">
                            <datalist id="citySuggestions">
                                <?php foreach ($cities as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>">
                                <?php endforeach; ?>
                                <option value="Surat">
                                <option value="Lucknow">
                                <option value="Kanpur">
                                <option value="Nagpur">
                                <option value="Indore">
                                <option value="Thane">
                                <option value="Bhopal">
                                <option value="Visakhapatnam">
                                <option value="Patna">
                                <option value="Vadodara">
                                <option value="Ghaziabad">
                                <option value="Ludhiana">
                                <option value="Coimbatore">
                                <option value="Madurai">
                                <option value="Jabalpur">
                                <option value="Gwalior">
                                <option value="Vijayawada">
                                <option value="Rajkot">
                                <option value="Jamshedpur">
                                <option value="Mysore">
                                <option value="Nashik">
                                <option value="Faridabad">
                                <option value="Meerut">
                                <option value="Kalyan">
                                <option value="Vasai">
                                <option value="Dhanbad">
                                <option value="Aurangabad">
                                <option value="Amritsar">
                                <option value="Allahabad">
                                <option value="Ranchi">
                                <option value="Howrah">
                                <option value="Guntur">
                                <option value="Jodhpur">
                                <option value="Raipur">
                                <option value="Kota">
                                <option value="Guwahati">
                                <option value="Chandigarh">
                                <option value="Solapur">
                                <option value="Hubli">
                                <option value="Dharwad">
                                <option value="Salem">
                                <option value="Aligarh">
                                <option value="Gurgaon">
                                <option value="Moradabad">
                                <option value="Bareilly">
                                <option value="Jalandhar">
                                <option value="Warangal">
                                <option value="Mangalore">
                                <option value="Tirupati">
                                <option value="Kurnool">
                                <option value="Nellore">
                                <option value="Belgaum">
                                <option value="Ambala">
                                <option value="Dehradun">
                                <option value="Ujjain">
                                <option value="Pondicherry">
                                <option value="Andaman">
                                <option value="Darjeeling">
                                <option value="Mussoorie">
                                <option value="Ooty">
                                <option value="Kodaikanal">
                                <option value="Munnar">
                                <option value="Rishikesh">
                                <option value="Haridwar">
                                <option value="Pushkar">
                                <option value="Mount Abu">
                                <option value="Khajuraho">
                                <option value="Hampi">
                                <option value="Badami">
                                <option value="Pattadakal">
                                <option value="Auroville">
                                <option value="Mahabalipuram">
                                <option value="Thanjavur">
                                <option value="Trichy">
                                <option value="Rameswaram">
                                <option value="Kanyakumari">
                                <option value="Port Blair">
                                <option value="Paris">
                                <option value="London">
                                <option value="New York">
                                <option value="Tokyo">
                                <option value="Dubai">
                                <option value="Singapore">
                                <option value="Bangkok">
                                <option value="Hong Kong">
                                <option value="Sydney">
                                <option value="Rome">
                                <option value="Barcelona">
                                <option value="Amsterdam">
                                <option value="Berlin">
                                <option value="Vienna">
                                <option value="Prague">
                                <option value="Istanbul">
                                <option value="Cairo">
                                <option value="Cape Town">
                                <option value="Rio de Janeiro">
                                <option value="Buenos Aires">
                            </datalist>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Check-in</label>
                            <input type="date" name="check_in" id="hotelCheckIn" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo htmlspecialchars($check_in); ?>" required>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 150px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Check-out</label>
                            <input type="date" name="check_out" id="hotelCheckOut" class="border-0 w-100 fw-bold" style="background: none;" value="<?php echo htmlspecialchars($check_out); ?>" required>
                        </div>
                        <div class="search-input-group border-end px-3 py-2" style="min-width: 120px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1">Guests</label>
                            <select name="guests" class="border-0 w-100 fw-bold" style="background: none;">
                                <?php for ($g = 1; $g <= 6; $g++): ?>
                                    <option value="<?php echo $g; ?>" <?php echo ($guests == $g) ? 'selected' : ''; ?>>
                                        <?php echo $g; ?> Guest<?php echo $g > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="search-input-group px-3 py-2" style="min-width: 140px;">
                            <label class="d-block small text-uppercase fw-bold text-muted mb-1"><i class="bi bi-filter text-warning me-1"></i>Price Sort</label>
                            <select name="sort_price" class="border-0 w-100 fw-bold" style="background: none;" onchange="this.form.submit()">
                                <option value="">Default</option>
                                <option value="low_to_high" <?php echo ($sort_price === 'low_to_high') ? 'selected' : ''; ?>>Low to High</option>
                                <option value="high_to_low" <?php echo ($sort_price === 'high_to_low') ? 'selected' : ''; ?>>High to Low</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-search rounded-pill px-4 py-3 ms-2 fw-bold shadow-lg">
                            Search Hotels
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($search_performed): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                <h4 class="fw-bold m-0"><i class="bi bi-building text-warning me-2"></i><?php echo count($results); ?> Hotels Found</h4>
                <div class="text-muted small">in <?php echo htmlspecialchars($city); ?></div>
            </div>
            
            <!-- Map View Section -->
            <div id="hotelMap" class="shadow-sm border"></div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                <h4 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i><?php echo $is_personalized ? 'Recommended for You' : 'Featured Hotels'; ?></h4>
                <div class="text-muted small"><?php echo $is_personalized ? 'Based on your travel history' : 'Top rated stays across India'; ?></div>
            </div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-building text-muted display-1 opacity-25"></i>
                <p class="mt-3 text-muted">No hotels found for this location. Try a different city!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
            <?php foreach ($results as $hotel): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hotel-card overflow-hidden rounded-4">
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white" style="height: 200px; background: linear-gradient(45deg, #eee, #ddd) !important;">
                            <i class="bi bi-image fs-1 opacity-25"></i>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.75rem;"><i class="bi bi-star-fill me-1"></i><?php echo htmlspecialchars($hotel['rating']); ?></span>
                            </div>
                            <p class="text-muted small mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($hotel['address']); ?>, <?php echo htmlspecialchars($hotel['city']); ?></p>
                            <p class="small text-secondary mb-4"><?php echo htmlspecialchars($hotel['amenities']); ?></p>
                            
                            <!-- Individual Hotel Map -->
                            <div id="map-<?php echo $hotel['hotel_id']; ?>" class="hotel-map-small border shadow-sm"></div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Price per night</div>
                                    <h4 class="fw-bold mb-0 text-dark">₹<?php echo number_format($hotel['price_per_night'], 0); ?></h4>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php $is_fav = in_array((int)$hotel['hotel_id'], $user_wishlist); ?>
                                    <button class="btn <?php echo $is_fav ? 'btn-danger' : 'btn-outline-secondary'; ?> btn-sm px-3 rounded-pill" onclick="toggleWishlist(this, 'hotel', <?php echo $hotel['hotel_id']; ?>, '<?php echo htmlspecialchars($hotel['name'], ENT_QUOTES); ?>')">
                                        <i class="bi <?php echo $is_fav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm px-3 rounded-pill" onclick="toggleHotelMap(this, <?php echo $hotel['hotel_id']; ?>, <?php echo $hotel['latitude'] ?? 'null'; ?>, <?php echo $hotel['longitude'] ?? 'null'; ?>, '<?php echo addslashes($hotel['name']); ?>', '<?php echo addslashes($hotel['city']); ?>')">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                    <form action="../flights/booking.php" method="POST">
                                        <input type="hidden" name="service_type" value="hotel">
                                        <input type="hidden" name="reference_id" value="<?php echo $hotel['hotel_id']; ?>">
                                        <input type="hidden" name="amount" value="<?php echo $hotel['price_per_night']; ?>">
                                        <input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($check_in); ?>">
                                        <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($hotel['name']); ?>">
                                        <button type="submit" class="btn btn-dark fw-bold px-4 rounded-pill">Book</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Google Maps JS API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=45e155bbbfe3b191f3b06f74cac0667e"></script>
    <script src="../flights/wishlist_toggle.js"></script>
    <script src="../public/script.js"></script>
    <script src="../chatbot/chatbot.js"></script>

    <script>
        let mainMap;
        const hotelMarkers = [];

        function initMainMap() {
            <?php if ($search_performed && !empty($results)): ?>
                const hotels = <?php echo json_encode($results); ?>;
                const validHotels = hotels.filter(h => h.latitude && h.longitude);
                const geocoder = new google.maps.Geocoder();

                mainMap = new google.maps.Map(document.getElementById('hotelMap'), {
                    zoom: 12,
                    center: { lat: 20.5937, lng: 78.9629 } // Default center
                });

                const bounds = new google.maps.LatLngBounds();
                let markersCount = 0;

                hotels.forEach(hotel => {
                    if (hotel.latitude && hotel.longitude) {
                        addHotelMarker(hotel, bounds);
                        markersCount++;
                    } else {
                        // Geocode missing locations for the main map
                        geocoder.geocode({ address: `${hotel.name}, ${hotel.city}` }, (results, status) => {
                            if (status === 'OK') {
                                hotel.latitude = results[0].geometry.location.lat();
                                hotel.longitude = results[0].geometry.location.lng();
                                addHotelMarker(hotel, bounds);
                                markersCount++;
                                if (markersCount === hotels.length) mainMap.fitBounds(bounds);
                            }
                        });
                    }
                });

                if (validHotels.length > 0) {
                    mainMap.fitBounds(bounds);
                }
            <?php endif; ?>
        }

        function addHotelMarker(hotel, bounds) {
            const position = { lat: parseFloat(hotel.latitude), lng: parseFloat(hotel.longitude) };
            const marker = new google.maps.Marker({
                position: position,
                map: mainMap,
                title: hotel.name
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="min-width: 150px; padding: 5px;">
                        <h6 class="fw-bold mb-1">${hotel.name}</h6>
                        <p class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${hotel.city}</p>
                        <p class="fw-bold text-dark mb-0">₹${Number(hotel.price_per_night).toLocaleString()}</p>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(mainMap, marker);
            });

            bounds.extend(position);
            hotelMarkers.push(marker);
            mainMap.fitBounds(bounds);
        }

        // City coordinates fallback
        const cityCoords = {
            'Mumbai': { lat: 19.0760, lng: 72.8777 },
            'Delhi': { lat: 28.6139, lng: 77.2090 },
            'Bangalore': { lat: 12.9716, lng: 77.5946 },
            'Hyderabad': { lat: 17.3850, lng: 78.4867 },
            'Chennai': { lat: 13.0827, lng: 80.2707 },
            'Kolkata': { lat: 22.5726, lng: 88.3639 },
            'Pune': { lat: 18.5204, lng: 73.8567 },
            'Ahmedabad': { lat: 23.0225, lng: 72.5714 },
            'Jaipur': { lat: 26.9124, lng: 75.7873 },
            'Goa': { lat: 15.2993, lng: 74.1240 },
            'Agra': { lat: 27.1767, lng: 78.0081 },
            'Varanasi': { lat: 25.3176, lng: 83.0100 },
            'Udaipur': { lat: 24.5854, lng: 73.7125 },
            'Kerala': { lat: 10.8505, lng: 76.2711 },
            'Kochi': { lat: 9.9312, lng: 76.2673 },
            'Rajasthan': { lat: 27.0238, lng: 74.2179 },
            'Himachal Pradesh': { lat: 31.1048, lng: 77.1734 },
            'Shimla': { lat: 31.1048, lng: 77.1734 },
            'Manali': { lat: 32.2396, lng: 77.1887 },
            'Leh': { lat: 34.1526, lng: 77.5771 },
            'Srinagar': { lat: 34.0837, lng: 74.7973 },
            'Bengaluru': { lat: 12.9716, lng: 77.5946 },
            'Surat': { lat: 21.1702, lng: 72.8311 },
            'Lucknow': { lat: 26.8467, lng: 80.9462 },
            'Kanpur': { lat: 26.4499, lng: 80.3319 },
            'Nagpur': { lat: 21.1458, lng: 79.0882 },
            'Indore': { lat: 22.7196, lng: 75.8577 },
            'Thane': { lat: 19.2183, lng: 72.9781 },
            'Bhopal': { lat: 23.2599, lng: 77.4126 },
            'Visakhapatnam': { lat: 17.6868, lng: 83.2185 },
            'Patna': { lat: 25.5941, lng: 85.1376 },
            'Vadodara': { lat: 22.3072, lng: 73.1812 },
            'Ghaziabad': { lat: 28.6692, lng: 77.4538 },
            'Ludhiana': { lat: 30.9010, lng: 75.8573 },
            'Coimbatore': { lat: 11.0168, lng: 76.9558 },
            'Madurai': { lat: 9.9252, lng: 78.1198 },
            'Jabalpur': { lat: 23.1815, lng: 79.9864 },
            'Gwalior': { lat: 26.2183, lng: 78.1828 },
            'Vijayawada': { lat: 16.5062, lng: 80.6480 },
            'Rajkot': { lat: 22.3039, lng: 70.8022 },
            'Jamshedpur': { lat: 22.8046, lng: 86.2029 },
            'Mysore': { lat: 12.2958, lng: 76.6394 },
            'Nashik': { lat: 19.9975, lng: 73.7898 },
            'Faridabad': { lat: 28.4089, lng: 77.3178 },
            'Meerut': { lat: 28.9845, lng: 77.7064 },
            'Kalyan': { lat: 19.2502, lng: 73.1602 },
            'Vasai': { lat: 19.4053, lng: 72.8418 },
            'Dhanbad': { lat: 23.7957, lng: 86.4304 },
            'Aurangabad': { lat: 19.8762, lng: 75.3433 },
            'Amritsar': { lat: 31.6340, lng: 74.8723 },
            'Allahabad': { lat: 25.4358, lng: 81.8463 },
            'Ranchi': { lat: 23.3441, lng: 85.3096 },
            'Howrah': { lat: 22.5804, lng: 88.3299 },
            'Guntur': { lat: 16.3067, lng: 80.4365 },
            'Jodhpur': { lat: 26.2389, lng: 73.0243 },
            'Raipur': { lat: 21.2514, lng: 81.6296 },
            'Kota': { lat: 25.2138, lng: 75.8648 },
            'Guwahati': { lat: 26.2006, lng: 91.7688 },
            'Chandigarh': { lat: 30.7333, lng: 76.7794 },
            'Solapur': { lat: 17.6599, lng: 75.9064 },
            'Hubli': { lat: 15.3647, lng: 75.1240 },
            'Dharwad': { lat: 15.4589, lng: 75.0078 },
            'Salem': { lat: 11.6643, lng: 78.1460 },
            'Aligarh': { lat: 27.8974, lng: 78.0880 },
            'Gurgaon': { lat: 28.4595, lng: 77.0266 },
            'Moradabad': { lat: 28.8386, lng: 78.7733 },
            'Bareilly': { lat: 28.3670, lng: 79.4304 },
            'Jalandhar': { lat: 31.3260, lng: 75.5762 },
            'Warangal': { lat: 17.9689, lng: 79.5941 },
            'Mangalore': { lat: 12.9141, lng: 74.8560 },
            'Tirupati': { lat: 13.6288, lng: 79.4192 },
            'Kurnool': { lat: 15.8281, lng: 78.0373 },
            'Nellore': { lat: 14.4426, lng: 79.9865 },
            'Belgaum': { lat: 15.8497, lng: 74.4977 },
            'Ambala': { lat: 30.3782, lng: 76.7767 },
            'Dehradun': { lat: 30.3165, lng: 78.0322 },
            'Ujjain': { lat: 23.1793, lng: 75.7849 },
            'Pondicherry': { lat: 11.9139, lng: 79.8145 },
            'Andaman': { lat: 11.6670, lng: 92.7359 },
            'Darjeeling': { lat: 27.0462, lng: 88.2687 },
            'Mussoorie': { lat: 30.4591, lng: 78.0663 },
            'Ooty': { lat: 11.4064, lng: 76.6932 },
            'Kodaikanal': { lat: 10.2381, lng: 77.4892 },
            'Munnar': { lat: 10.0889, lng: 77.0595 },
            'Rishikesh': { lat: 30.0869, lng: 78.2676 },
            'Haridwar': { lat: 29.9457, lng: 78.1642 },
            'Pushkar': { lat: 26.4906, lng: 74.5551 },
            'Mount Abu': { lat: 24.5924, lng: 72.7156 },
            'Khajuraho': { lat: 24.8520, lng: 79.9274 },
            'Hampi': { lat: 15.3350, lng: 76.4600 },
            'Badami': { lat: 15.9129, lng: 75.6800 },
            'Pattadakal': { lat: 15.9547, lng: 75.8160 },
            'Auroville': { lat: 12.0061, lng: 79.8112 },
            'Mahabalipuram': { lat: 12.6189, lng: 80.1939 },
            'Thanjavur': { lat: 10.7867, lng: 79.1378 },
            'Trichy': { lat: 10.8505, lng: 78.6997 },
            'Rameswaram': { lat: 9.2876, lng: 79.3129 },
            'Kanyakumari': { lat: 8.0883, lng: 77.5385 },
            'Port Blair': { lat: 11.6670, lng: 92.7359 }
        };

        function toggleHotelMap(btn, hotelId, lat, lng, name, city) {
            const mapDiv = document.getElementById(`map-${hotelId}`);
            if (mapDiv.style.display === 'block') {
                mapDiv.style.display = 'none';
                btn.classList.remove('btn-info');
                btn.classList.add('btn-outline-info');
                return;
            }

            // Hide other small maps
            document.querySelectorAll('.hotel-map-small').forEach(div => div.style.display = 'none');
            document.querySelectorAll('.btn-info').forEach(b => {
                if(b.onclick && b.onclick.toString().includes('toggleHotelMap')) {
                    b.classList.remove('btn-info');
                    b.classList.add('btn-outline-info');
                }
            });

            mapDiv.style.display = 'block';
            btn.classList.remove('btn-outline-info');
            btn.classList.add('btn-info');

            // First check if we have coordinates
            if (lat && lng) {
                renderSmallMap(mapDiv, lat, lng, name);
                return;
            }

            // Show locating message
            mapDiv.innerHTML = '<div class="p-4 text-center"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2 small text-muted">Locating hotel...</span></div>';

            // Try to find city coordinates as fallback first
            let fallbackCoords = null;
            if (cityCoords[city]) {
                fallbackCoords = cityCoords[city];
            } else {
                // Try partial match
                for (const cityName in cityCoords) {
                    if (city.toLowerCase().includes(cityName.toLowerCase()) || cityName.toLowerCase().includes(city.toLowerCase())) {
                        fallbackCoords = cityCoords[cityName];
                        break;
                    }
                }
            }

            // Try Google Maps Geocoder
            try {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: `${name}, ${city}` }, (results, status) => {
                    if (status === 'OK' && results && results[0]) {
                        const newLat = results[0].geometry.location.lat();
                        const newLng = results[0].geometry.location.lng();
                        renderSmallMap(mapDiv, newLat, newLng, name);
                        
                        // Send coordinates to server to update DB
                        fetch('update_hotel_coords.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `hotel_id=${hotelId}&lat=${newLat}&lng=${newLng}`
                        });
                    } else if (fallbackCoords) {
                        // Use fallback city coordinates
                        renderSmallMap(mapDiv, fallbackCoords.lat, fallbackCoords.lng, name);
                        
                        // Also update DB with fallback coordinates
                        fetch('update_hotel_coords.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `hotel_id=${hotelId}&lat=${fallbackCoords.lat}&lng=${fallbackCoords.lng}`
                        });
                    } else {
                        mapDiv.innerHTML = '<div class="p-4 text-center text-muted small">Could not find location for this hotel.</div>';
                    }
                });
            } catch (e) {
                // If Google Maps API fails, use fallback coordinates
                if (fallbackCoords) {
                    renderSmallMap(mapDiv, fallbackCoords.lat, fallbackCoords.lng, name);
                    
                    // Update DB with fallback coordinates
                    fetch('update_hotel_coords.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `hotel_id=${hotelId}&lat=${fallbackCoords.lat}&lng=${fallbackCoords.lng}`
                    });
                } else {
                    mapDiv.innerHTML = '<div class="p-4 text-center text-muted small">Could not find location for this hotel.</div>';
                }
            }
        }

        function renderSmallMap(mapDiv, lat, lng, name) {
            const hotelPos = { lat: parseFloat(lat), lng: parseFloat(lng) };
            const hotelMap = new google.maps.Map(mapDiv, {
                zoom: 15,
                center: hotelPos,
                mapTypeControl: false,
                streetViewControl: false
            });

            new google.maps.Marker({
                position: hotelPos,
                map: hotelMap,
                title: name
            });
        }

        // Initialize maps on load
        window.onload = initMainMap;
    </script>
</body>
</html>
