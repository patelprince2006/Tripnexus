

<?php
session_start();
include '../database/db.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$wishlist_id = intval($_POST['wishlist_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($wishlist_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid wishlist ID']);
    exit;
}

// Verify ownership and delete
$result = db_query($conn, "DELETE FROM wishlist WHERE id = ? AND user_id = ?", [$wishlist_id, $user_id]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove']);
}
?>