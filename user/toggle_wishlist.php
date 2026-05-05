<?php
session_start();
require_once __DIR__ . '/../database/db.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first', 'login_required' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$item_type = $_POST['item_type'] ?? '';
$item_id = intval($_POST['item_id'] ?? 0);
$item_name = $_POST['item_name'] ?? '';

// Validate
$valid_types = ['flight', 'bus', 'train', 'hotel'];
if (!in_array($item_type, $valid_types) || $item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item']);
    exit;
}

// Check if already in wishlist
$check = db_query($conn, "SELECT id FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?", [$user_id, $item_type, $item_id]);
$exists = $check && db_fetch_assoc($check);

if ($exists) {
    // Remove from wishlist
    db_query($conn, "DELETE FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?", [$user_id, $item_type, $item_id]);
    echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist']);
} else {
    // Add to wishlist
    db_query($conn, "INSERT INTO wishlist (user_id, item_type, item_id, item_name) VALUES (?, ?, ?, ?)", [$user_id, $item_type, $item_id, $item_name]);
    echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist']);
}
?>
