<?php
include 'database/db.php';

echo "Updating hotels in the database...\n\n";

$additional_hotels = [
    ['Lake Palace Udaipur', 'Udaipur', 'Pichola Lake, Udaipur 313001', 'Iconic floating palace on Lake Pichola offering regal accommodations with stunning lake views.', 18000.00, 5.0, 'Pool, Spa, Wifi, Restaurant, Bar, Lake View, Boat Service', ''],
    ['Gateway Hotel', 'Agra', 'Fatehabad Road, Agra 282001', 'Premium hotel with breathtaking views of the Taj Mahal and modern amenities.', 9000.00, 4.6, 'Pool, Gym, Wifi, Restaurant, Taj View, Bar', ''],
    ['Manali Heights', 'Manali', 'Mall Road, Manali 175131', 'Cozy mountain resort with panoramic Himalayan views and comfortable stay.', 6000.00, 4.3, 'Mountain View, Wifi, Restaurant, Bonfire, Travel Desk', ''],
    ['Pondicherry Beach Resort', 'Pondicherry', 'Beach Road, Pondicherry 605001', 'French colonial style beach resort with serene ambiance and coastal charm.', 7500.00, 4.4, 'Beach Access, Pool, Wifi, Restaurant, Bar, Spa', ''],
    ['Ranthambore Tiger Resort', 'Ranthambore', 'Near Ranthambore National Park, Sawai Madhopur 322001', 'Wildlife resort offering jungle safari experiences and comfortable stay amidst nature.', 8500.00, 4.5, 'Safari Tours, Pool, Wifi, Restaurant, Wildlife Viewing', ''],
    ['Shimla Haven', 'Shimla', 'Mall Road, Shimla 171001', 'Charming heritage hotel in the Queen of Hills with colonial architecture.', 7000.00, 4.2, 'Mountain View, Wifi, Restaurant, Heater, Fireplace', ''],
    ['Varanasi Ghat View', 'Varanasi', 'Assi Ghat, Varanasi 221005', 'Riverside hotel overlooking the Ganges with authentic spiritual experience.', 5500.00, 4.1, 'Ghat View, Wifi, Restaurant, Temple Tours, Yoga', ''],
    ['Amritsar Grand', 'Amritsar', 'Golden Temple Road, Amritsar 143001', 'Comfortable hotel near Golden Temple offering easy access to religious sites.', 4500.00, 4.0, 'Wifi, Restaurant, Temple View, Parking, 24hr Service', ''],
    ['Gangtok Himalayan', 'Gangtok', 'Mahatma Gandhi Marg, Gangtok 737101', 'Scenic mountain hotel in Sikkim with stunning Kanchenjunga views.', 8000.00, 4.4, 'Mountain View, Wifi, Restaurant, Spa, Cable Car Access', ''],
    ['Ooty Lake View', 'Ooty', 'Lake Road, Ooty 643001', 'Beautiful hill station hotel overlooking Ooty Lake with colonial charm.', 6500.00, 4.3, 'Lake View, Wifi, Restaurant, Garden, Tea Tours', ''],
    ['Darjeeling Tea Estate', 'Darjeeling', 'Mall Road, Darjeeling 734101', 'Heritage hotel with tea plantation views and cozy mountain ambiance.', 7200.00, 4.4, 'Tea Garden View, Wifi, Restaurant, Tea Tours, Fireplace', ''],
    ['Nainital Lake Resort', 'Nainital', 'Thandi Sadak, Nainital 263001', 'Serene lakeside resort with panoramic views of Naini Lake.', 6800.00, 4.2, 'Lake View, Wifi, Restaurant, Boating, Mountain View', ''],
    ['Kanyakumari Beach', 'Kanyakumari', 'Beach Road, Kanyakumari 629702', 'Coastal resort at India\'s southern tip with sunrise and sunset views.', 5800.00, 4.1, 'Beach Access, Wifi, Restaurant, Sunrise View, Temple Tours', ''],
    ['Mussoorie Gateway', 'Mussoorie', 'Mall Road, Mussoorie 248001', 'Hill station hotel with breathtaking Doon Valley views.', 6200.00, 4.0, 'Valley View, Wifi, Restaurant, Cable Car, Nature Walks', ''],
    ['Srinagar Houseboat', 'Srinagar', 'Dal Lake, Srinagar 190001', 'Authentic Kashmir houseboat experience on Dal Lake with shikara rides.', 9500.00, 4.7, 'Lake View, Wifi, Restaurant, Shikara Ride, Garden View', ''],
    ['Ahmedabad Heritage', 'Ahmedabad', 'Law Garden, Ahmedabad 380006', 'Heritage hotel in Gujarat showcasing traditional architecture and culture.', 5200.00, 4.1, 'Wifi, Restaurant, Heritage Tours, Garden, Parking', ''],
    ['Surat Central', 'Surat', 'Ring Road, Surat 395003', 'Modern business hotel in Surat with excellent connectivity.', 4800.00, 4.0, 'Wifi, Restaurant, Gym, Business Center, Parking', ''],
    ['Vadodara Palace', 'Vadodara', 'Alkapuri, Vadodara 390005', 'Elegant hotel in Gujarat with palace-style architecture.', 6000.00, 4.2, 'Pool, Wifi, Restaurant, Bar, Gym, Garden', ''],
    ['Nagpur Orange', 'Nagpur', 'Sitabuldi, Nagpur 440012', 'Comfortable hotel in Nagpur with modern amenities and easy access.', 4200.00, 4.0, 'Wifi, Restaurant, Parking, Gym, 24hr Service', ''],
    ['Indore Rajwada', 'Indore', 'Rajwada, Indore 452001', 'Central hotel in Indore near Rajwada Palace with local flavors.', 4600.00, 4.1, 'Wifi, Restaurant, Local Food Tours, Parking, Gym', '']
];

$insert_count = 0;
foreach ($additional_hotels as $hotel) {
    $check_query = "SELECT hotel_id FROM hotels WHERE name = ? AND city = ?";
    $check_result = db_query($conn, $check_query, [$hotel[0], $hotel[1]]);
    
    if ($check_result && db_num_rows($check_result) == 0) {
        $insert_query = "INSERT INTO hotels (name, city, address, description, price_per_night, rating, amenities, main_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = db_query($conn, $insert_query, $hotel);
        
        if ($result) {
            echo "✓ Added: {$hotel[0]} ({$hotel[1]})\n";
            $insert_count++;
        } else {
            echo "✗ Failed to add: {$hotel[0]}\n";
        }
    } else {
        echo "- Already exists: {$hotel[0]} ({$hotel[1]})\n";
    }
}

echo "\n✅ Done! Added $insert_count new hotels.\n";

echo "\n--- Current Hotel Cities in Database ---\n";
$city_result = db_query($conn, "SELECT DISTINCT city FROM hotels ORDER BY city ASC");
if ($city_result) {
    $cities = [];
    while ($row = db_fetch_assoc($city_result)) {
        $cities[] = $row['city'];
    }
    echo count($cities) . " cities:\n";
    foreach ($cities as $city) {
        echo "  • $city\n";
    }
}
?>