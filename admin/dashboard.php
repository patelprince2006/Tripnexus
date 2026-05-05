<?php
require_once __DIR__ . '/../database/db.php';
include 'auth_check.php';

// Helper for safe counting
function get_count($conn, $query, $key = 'c') {
    $res = db_query($conn, $query);
    $row = db_fetch_assoc($res);
    return $row[$key] ?? 0;
}

// Fetch Statistics safely
$users_count = get_count($conn, "SELECT COUNT(*) as c FROM users");
$bookings_count = get_count($conn, "SELECT COUNT(*) as c FROM bookings");
$revenue = get_count($conn, "SELECT SUM(amount) as s FROM payments WHERE payment_status = 'success'", 's');
$hotels_count = get_count($conn, "SELECT COUNT(*) as c FROM hotels");

$active_page = 'dashboard';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <div class="row g-4 mb-4">
        <!-- Card 1: Users -->
        <div class="col-md-3">
            <div class="card p-3 card-stat bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Users</h6>
                        <h3 class="mb-0"><?php echo $users_count; ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Bookings -->
        <div class="col-md-3">
            <div class="card p-3 card-stat bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Bookings</h6>
                        <h3 class="mb-0"><?php echo $bookings_count; ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Revenue -->
        <div class="col-md-3">
            <div class="card p-3 card-stat bg-warning text-dark">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Revenue</h6>
                        <h3 class="mb-0">₹<?php echo number_format($revenue, 2); ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Hotels -->
        <div class="col-md-3">
            <div class="card p-3 card-stat bg-danger text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Hotels</h6>
                        <h3 class="mb-0"><?php echo $hotels_count; ?></h3>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
   

</div>

<script>
    // Sample Chart Data (You can fetch dynamically later)
    const ctx = document.getElementById('bookingsChart').getContext('2d');
    const bookingsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Bookings',
                data: [12, 19, 3, 5, 2, 3], // Dummy data
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctx2 = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Flights', 'Hotels', 'Tours'],
            datasets: [{
                data: [300, 50, 100], // Dummy data
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)'
                ],
                hoverOffset: 4
            }]
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
