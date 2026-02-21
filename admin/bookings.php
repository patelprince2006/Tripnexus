<?php
include '../db.php';
include 'auth_check.php';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $id = intval($_POST['booking_id']);
        $status = $_POST['status'];
        pg_query_params($conn, "UPDATE bookings SET status = $1 WHERE id = $2", array($status, $id));
        $msg = "Booking status updated to " . ucfirst($status);
    }
}

// Fetch Bookings with User Details
// This query joins users to show who booked. For reference details (flight/hotel name), we'd need more complex joins or separate queries.
// For simplicity in this list, we'll show ID and Type.
$query = "
    SELECT b.*, u.fullname, u.email 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.booking_date DESC
";
$result = pg_query($conn, $query);

$active_page = 'bookings';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Booking Management</h2>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($row['fullname']); ?><br>
                        <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?php echo strtoupper($row['booking_type']); ?></span>
                        <small class="d-block text-muted">Ref ID: <?php echo $row['reference_id']; ?></small>
                    </td>
                    <td><?php echo date('d M Y, h:i A', strtotime($row['booking_date'])); ?></td>
                    <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                    <td>
                        <?php 
                        $status = $row['status'];
                        $badgeClass = match($status) {
                            'confirmed' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'cancelled' => 'bg-danger',
                            'completed' => 'bg-info',
                            default => 'bg-secondary'
                        };
                        echo "<span class='badge $badgeClass'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            
                            <?php if ($status == 'pending'): ?>
                                <button name="status" value="confirmed" class="btn btn-sm btn-success" title="Confirm"><i class="fas fa-check"></i></button>
                                <button name="status" value="cancelled" class="btn btn-sm btn-danger" title="Cancel"><i class="fas fa-times"></i></button>
                            <?php elseif ($status == 'confirmed'): ?>
                                <button name="status" value="completed" class="btn btn-sm btn-info text-white" title="Complete"><i class="fas fa-check-double"></i></button>
                                <button name="status" value="cancelled" class="btn btn-sm btn-danger" title="Cancel"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
