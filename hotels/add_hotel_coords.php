<?php
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
    'Rajasthan' => ['lat' => 27.0238, 'lng' => 74.2179],
    'Himachal Pradesh' => ['lat' => 31.1048, 'lng' => 77.1734],
    'Shimla' => ['lat' => 31.1048, 'lng' => 77.1734],
    'Manali' => ['lat' => 32.2396, 'lng' => 77.1887],
    'Leh' => ['lat' => 34.1526, 'lng' => 77.5771],
    'Srinagar' => ['lat' => 34.0837, 'lng' => 74.7973],
    'Bengaluru' => ['lat' => 12.9716, 'lng' => 77.5946],
];

// Get all hotels without lat/lng
$res = db_query($conn, "SELECT hotel_id, name, city FROM hotels WHERE latitude IS NULL OR longitude IS NULL");
$updated = 0;

while ($hotel = db_fetch_assoc($res)) {
    $city = trim($hotel['city']);
    $found = false;
    
    // Check exact city match first
    if (isset($cityCoords[$city])) {
        $lat = $cityCoords[$city]['lat'];
        $lng = $cityCoords[$city]['lng'];
        $found = true;
    } else {
        // Check partial match
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
        $updated++;
        echo "Updated: {$hotel['name']} ({$hotel['city']}) → lat: $lat, lng: $lng\n";
    } else {
        echo "Skipped: {$hotel['name']} ({$hotel['city']}) - no coordinates found\n";
    }
}

echo "\nTotal hotels updated: $updated\n";
?>