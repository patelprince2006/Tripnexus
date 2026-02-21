<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Fetch stats using PostgreSQL syntax
$sql = "SELECT 
            (SELECT COUNT(*) FROM bookings WHERE user_id = $1 AND status = 'confirmed') as active_trips,
            (SELECT COUNT(*) FROM bookings WHERE user_id = $1) as total_bookings";

$result = pg_query_params($conn, $sql, array($user_id));

if ($result) {
    $stats = pg_fetch_assoc($result);
} else {
    // Default values if the query fails or table is empty
    $stats = ['active_trips' => 0, 'total_bookings' => 0];
}

// Fetch recent notifications
$notifQuery = pg_query_params(
    $conn,
    'SELECT id, type, subject, message, created_at, is_read FROM notifications WHERE user_id = $1 ORDER BY created_at DESC LIMIT 5',
    array($user_id)
);

$notifications = [];
if ($notifQuery) {
    while ($row = pg_fetch_assoc($notifQuery)) {
        $notifications[] = $row;
    }
}

// Count unread notifications
$unreadQuery = pg_query_params(
    $conn,
    'SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = $1 AND is_read = false',
    array($user_id)
);

$unreadCount = 0;
if ($unreadQuery) {
    $unreadRow = pg_fetch_assoc($unreadQuery);
    $unreadCount = $unreadRow['unread_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 px-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                Trip<span class="text-warning">Nexus</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small d-none d-sm-inline">
                        Welcome, <?php echo htmlspecialchars($fullname); ?>!
                    </span>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-circle d-inline-block mb-3">
                            <i class="bi bi-person-circle fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($fullname); ?></h5>
                        <p class="text-muted small">Traveler Member</p>
                    </div>
                </div>

                <div class="list-group shadow-sm border-0">
                    <a href="#" class="list-group-item list-group-item-action active"><i class="bi bi-grid-1x2-fill me-2"></i> Overview</a>
                    <a href="my_booking_standlone.php" class="list-group-item list-group-item-action"><i class="bi bi-ticket-perforated me-2"></i> My Bookings</a>
                    <a href="#" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> Wishlist</a>
                    <a href="#" class="list-group-item list-group-item-action"><i class="bi bi-gear me-2"></i> Settings</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm bg-primary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Active Trips</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $stats['active_trips'] ?? 0; ?></h2>
                                </div>
                                <i class="bi bi-airplane fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm bg-dark text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Bookings</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $stats['total_bookings'] ?? 0; ?></h2>
                                </div>
                                <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-bell me-2"></i>Notifications</h5>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?> New</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>No notifications yet. All quiet!
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="list-group-item <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold">
                                                    <?php 
                                                        $icon = '';
                                                        switch($notif['type']) {
                                                            case 'verification': $icon = 'bi-check-circle'; break;
                                                            case 'password_reset': $icon = 'bi-key'; break;
                                                            case 'booking': $icon = 'bi-ticket'; break;
                                                            case 'order': $icon = 'bi-box'; break;
                                                            default: $icon = 'bi-bell';
                                                        }
                                                    ?>
                                                    <i class="bi <?php echo $icon; ?> me-2"></i><?php echo htmlspecialchars($notif['subject']); ?>
                                                </h6>
                                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></small>
                                            </div>
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="badge bg-primary">NEW</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Recent Bookings</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service</th>
                                        <th>Destination</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No recent bookings found. Start your adventure!</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</body>

</html>