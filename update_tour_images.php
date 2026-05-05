<?php
include 'database/db.php';

echo "Updating tour package images...\n";

$tour_images = [
    1 => 'photos/Agra.jpg',
    2 => 'photos/Manali.jpg',
    3 => 'photos/Goa.jpg',
    4 => 'photos/Manali2.jpg',
    5 => 'photos/Agra.jpg',
    6 => 'photos/Mumbai.jpg'
];

foreach ($tour_images as $tour_id => $image_path) {
    $update_query = "UPDATE tour_packages SET main_image = ? WHERE id = ?";
    $result = db_query($conn, $update_query, [$image_path, $tour_id]);
    
    if ($result) {
        echo "Updated tour package ID $tour_id with image: $image_path\n";
    } else {
        echo "Failed to update tour package ID $tour_id\n";
    }
}

echo "\nDone! Tour package images updated successfully.\n";
?>