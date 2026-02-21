<?php
include '../db.php';
include 'auth_check.php';

// Fetch Payments
$query = "
    SELECT p.*, u.fullname, b.booking_type 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    JOIN bookings b ON p.booking_id = b.id 
    ORDER BY p.payment_date DESC
";
$result = pg_query($conn, $query);

$active_page = 'payments';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Payment History</h2>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Transaction ID</th>
                    <th>User</th>
                    <th>Booking Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo $row['transaction_id'] ? $row['transaction_id'] : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><span class="badge bg-light text-dark"><?php echo strtoupper($row['booking_type']); ?></span></td>
                    <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($row['payment_date'])); ?></td>
                    <td>
                        <?php 
                        $status = $row['payment_status'];
                        $badgeClass = match($status) {
                            'success' => 'bg-success',
                            'pending' => 'bg-warning text-dark',
                            'failed' => 'bg-danger',
                            'refunded' => 'bg-info',
                            default => 'bg-secondary'
                        };
                        echo "<span class='badge $badgeClass'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
