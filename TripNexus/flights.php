?php
// Combined Flight Search Page
// Handles both search form display and results

require 'db.php';

$flights = null;
$search_performed = false;
$departure = '';
$arrival = '';
$departure_date = '';
$passengers = 1;
$error_message = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_performed = true;
    
    $departure = isset($_POST['departure']) ? pg_escape_string($conn, $_POST['departure']) : '';
    $arrival = isset($_POST['arrival']) ? pg_escape_string($conn, $_POST['arrival']) : '';
    $departure_date = isset($_POST['departure_date']) ? $_POST['departure_date'] : '';
    $passengers = isset($_POST['passengers']) ? intval($_POST['passengers']) : 1;
    
    // Validate inputs
    if (empty($departure) || empty($arrival) || empty($departure_date)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($departure === $arrival) {
        $error_message = "Departure and arrival airports must be different.";
    } else {
        // Query flights based on search criteria
        $query = "
            SELECT 
                f.flight_id,
                f.flight_number,
                f.departure_time,
                f.arrival_time,
                f.base_price,
                f.total_seats,
                f.available_seats,
                f.status,
                a.airline_name,
                a.airline_logo,
                dep.airport_name as departure_airport_name,
                dep.city as departure_city,
                arr.airport_name as arrival_airport_name,
                arr.city as arrival_city
            FROM flights f
            JOIN airlines a ON f.airline_id = a.airline_id
            JOIN airports dep ON f.departure_airport = dep.airport_code
            JOIN airports arr ON f.arrival_airport = arr.airport_code
            WHERE f.departure_airport = '$departure' 
            AND f.arrival_airport = '$arrival'
            AND DATE(f.departure_time) = '$departure_date'
            AND f.available_seats >= $passengers
            AND f.status = 'scheduled'
            ORDER BY f.departure_time ASC
        ";
        
        $result = pg_query($conn, $query);
        
        if (!$result) {
            $error_message = "Database error: " . pg_last_error($conn);
        } else {
            $flights = pg_fetch_all($result);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search - TripNexus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* SEARCH FORM STYLES */
        .search-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: white;
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .search-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full {
            grid-column: 1 / -1;
        }
        
        label {
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        input, select {
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            color: #333;
        }
        
        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .button-group {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
        }
        
        button {
            flex: 1;
            padding: 14px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #ff5252;
        }
        
        button.reset {
            background: rgba(255, 255, 255, 0.3);
        }
        
        button.reset:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* SEARCH CRITERIA DISPLAY */
        .search-criteria {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: none;
        }
        
        .search-criteria.active {
            display: block;
        }
        
        .search-criteria h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .criteria-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .criteria-item {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        
        .criteria-label {
            font-weight: bold;
            color: #667eea;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .criteria-value {
            color: #333;
            font-size: 16px;
            margin-top: 5px;
        }
        
        /* RESULTS STYLES */
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .error-message.active {
            display: block;
        }
        
        .no-results {
            background: white;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            color: #666;
            display: none;
        }
        
        .no-results.active {
            display: block;
        }
        
        .no-results h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .flight-results {
            display: grid;
            gap: 20px;
            display: none;
        }
        
        .flight-results.active {
            display: grid;
        }
        
        .results-count {
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .flight-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .flight-header {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .airline-info {
            text-align: center;
        }
        
        .airline-logo {
            max-width: 60px;
            height: auto;
            margin-bottom: 5px;
        }
        
        .airline-name {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .flight-number {
            font-size: 12px;
            color: #999;
        }
        
        .flight-route {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
        }
        
        .time-info {
            text-align: center;
        }
        
        .time {
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }
        
        .airport-code {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .airport-city {
            font-size: 11px;
            color: #999;
        }
        
        .duration {
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e8f5e9;
            color: #27ae60;
        }
        
        .flight-footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            padding: 20px;
            background: #f9f9f9;
            align-items: center;
        }
        
        .price-section {
            text-align: center;
        }
        
        .price {
            font-size: 22px;
            font-weight: bold;
            color: #ff6b6b;
        }
        
        .price-label {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        
        .availability {
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .availability-status {
            font-weight: bold;
            color: #27ae60;
        }
        
        .book-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            width: 100%;
        }
        
        .book-btn:hover {
            background: #5568d3;
        }
        
        .new-search-btn {
            background: #999;
            margin-top: 20px;
            padding: 12px;
            border-radius: 5px;
            border: none;
            color: white;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
        }
        
        .new-search-btn:hover {
            background: #777;
        }
        
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
            
            .flight-header {
                grid-template-columns: 1fr;
            }
            
            .flight-footer {
                grid-template-columns: 1fr;
            }
            
            .criteria-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- SEARCH FORM -->
        <div class="search-container" id="searchForm">
            <div class="search-title">✈️ Search Flights</div>
            
            <form method="POST" action="" class="search-form">
                <div class="form-group">
                    <label for="departure">Departure Airport</label>
                    <select name="departure" id="departure" required>
                        <option value="">Select Airport</option>
                        <option value="BOM" <?php echo ($departure === 'BOM') ? 'selected' : ''; ?>>BOM - Mumbai (Bombay)</option>
                        <option value="DEL" <?php echo ($departure === 'DEL') ? 'selected' : ''; ?>>DEL - Delhi</option>
                        <option value="BLR" <?php echo ($departure === 'BLR') ? 'selected' : ''; ?>>BLR - Bangalore</option>
                        <option value="HYD" <?php echo ($departure === 'HYD') ? 'selected' : ''; ?>>HYD - Hyderabad</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="arrival">Arrival Airport</label>
                    <select name="arrival" id="arrival" required>
                        <option value="">Select Airport</option>
                        <option value="BOM" <?php echo ($arrival === 'BOM') ? 'selected' : ''; ?>>BOM - Mumbai (Bombay)</option>
                        <option value="DEL" <?php echo ($arrival === 'DEL') ? 'selected' : ''; ?>>DEL - Delhi</option>
                        <option value="BLR" <?php echo ($arrival === 'BLR') ? 'selected' : ''; ?>>BLR - Bangalore</option>
                        <option value="HYD" <?php echo ($arrival === 'HYD') ? 'selected' : ''; ?>>HYD - Hyderabad</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="departure_date">Departure Date</label>
                    <input type="date" name="departure_date" id="departure_date" value="<?php echo htmlspecialchars($departure_date); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="passengers">Passengers</label>
                    <input type="number" name="passengers" id="passengers" min="1" max="9" value="<?php echo $passengers; ?>" required>
                </div>
                
                <div class="button-group">
                    <button type="submit">🔍 Search Flights</button>
                    <button type="reset" class="reset">Clear</button>
                </div>
            </form>
        </div>
        
        <!-- ERROR MESSAGE -->
        <div class="error-message <?php echo !empty($error_message) ? 'active' : ''; ?>" id="errorMessage">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        
        <!-- SEARCH CRITERIA DISPLAY -->
        <div class="search-criteria <?php echo $search_performed ? 'active' : ''; ?>" id="searchCriteria">
            <h3>Search Criteria</h3>
            <div class="criteria-info">
                <div class="criteria-item">
                    <div class="criteria-label">From</div>
                    <div class="criteria-value"><?php echo htmlspecialchars($departure); ?></div>
                </div>
                <div class="criteria-item">
                    <div class="criteria-label">To</div>
                    <div class="criteria-value"><?php echo htmlspecialchars($arrival); ?></div>
                </div>
                <div class="criteria-item">
                    <div class="criteria-label">Date</div>
                    <div class="criteria-value"><?php echo htmlspecialchars($departure_date); ?></div>
                </div>
                <div class="criteria-item">
                    <div class="criteria-label">Passengers</div>
                    <div class="criteria-value"><?php echo $passengers; ?></div>
                </div>
            </div>
        </div>
        
        <!-- NO RESULTS MESSAGE -->
        <div class="no-results <?php echo ($search_performed && (is_null($flights) || (is_array($flights) && count($flights) === 0))) ? 'active' : ''; ?>" id="noResults">
            <h2>No Flights Found</h2>
            <p>Sorry, we couldn't find any flights matching your search criteria.</p>
            <p>Try adjusting your search filters and search again.</p>
        </div>
        
        <!-- FLIGHT RESULTS -->
        <div class="flight-results <?php echo ($search_performed && is_array($flights) && count($flights) > 0) ? 'active' : ''; ?>" id="flightResults">
            <div class="results-count">
                Found <strong><?php echo is_array($flights) ? count($flights) : 0; ?></strong> flight(s) available
            </div>
            
            <?php if (is_array($flights) && count($flights) > 0): ?>
                <?php foreach ($flights as $flight): ?>
                    <?php
                    $dep_time = new DateTime($flight['departure_time']);
                    $arr_time = new DateTime($flight['arrival_time']);
                    $duration = $dep_time->diff($arr_time);
                    $total_price = floatval($flight['base_price']) * $passengers;
                    ?>
                    <div class="flight-card">
                        <div class="flight-header">
                            <div class="airline-info">
                                <?php if (!empty($flight['airline_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($flight['airline_logo']); ?>" alt="<?php echo htmlspecialchars($flight['airline_name']); ?>" class="airline-logo" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div class="airline-name"><?php echo htmlspecialchars($flight['airline_name']); ?></div>
                                <div class="flight-number"><?php echo htmlspecialchars($flight['flight_number']); ?></div>
                            </div>
                            
                            <div class="flight-route">
                                <div class="time-info">
                                    <div class="time"><?php echo $dep_time->format('H:i'); ?></div>
                                    <div class="airport-code"><?php echo htmlspecialchars($flight['departure_airport']); ?></div>
                                    <div class="airport-city"><?php echo htmlspecialchars($flight['departure_city']); ?></div>
                                </div>
                                
                                <div class="duration">
                                    <svg width="50" height="2" style="margin: 10px 0;">
                                        <line x1="0" y1="1" x2="40" y2="1" stroke="#ddd" stroke-width="2"/>
                                        <polygon points="50,1 45,0 45,2" fill="#ddd"/>
                                    </svg>
                                    <div><?php echo $duration->format('%hh %im'); ?></div>
                                </div>
                                
                                <div class="time-info">
                                    <div class="time"><?php echo $arr_time->format('H:i'); ?></div>
                                    <div class="airport-code"><?php echo htmlspecialchars($flight['arrival_airport']); ?></div>
                                    <div class="airport-city"><?php echo htmlspecialchars($flight['arrival_city']); ?></div>
                                </div>
                            </div>
                            
                            <span class="status-badge"><?php echo htmlspecialchars($flight['status']); ?></span>
                        </div>
                        
                        <div class="flight-footer">
                            <div class="price-section">
                                <div class="price">₹<?php echo number_format(floatval($flight['base_price']), 2); ?></div>
                                <div class="price-label">per person</div>
                            </div>
                            
                            <div class="availability">
                                <div class="availability-status"><?php echo $flight['available_seats']; ?> Seats</div>
                                <div style="font-size: 12px; color: #999; margin-top: 3px;">
                                    Available
                                </div>
                            </div>
                            
                            <button class="book-btn" onclick="bookFlight(<?php echo $flight['flight_id']; ?>, <?php echo $passengers; ?>, <?php echo $total_price; ?>)">
                                Book Now → ₹<?php echo number_format($total_price, 2); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <button class="new-search-btn" onclick="scrollToSearch()">← New Search</button>
        </div>
    </div>
    
    <script>
        function bookFlight(flightId, passengers, totalPrice) {
            alert('Booking Flight...\nFlight ID: ' + flightId + '\nPassengers: ' + passengers + '\nTotal: ₹' + totalPrice.toFixed(2) + '\n\n(Booking feature coming soon!)');
            // Future: Redirect to booking page
            // window.location.href = 'flight_booking.php?flight_id=' + flightId + '&passengers=' + passengers;
        }
        
        function scrollToSearch() {
            document.getElementById('searchForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
