<?php
include '../db.php';
include 'auth_check.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id = intval($_POST['review_id']);
    
    if ($_POST['action'] == 'approve') {
        pg_query_params($conn, "UPDATE reviews SET status = 'approved' WHERE id = $1", array($id));
        $msg = "Review approved!";
    } elseif ($_POST['action'] == 'delete') {
        pg_query_params($conn, "DELETE FROM reviews WHERE id = $1", array($id));
        $msg = "Review deleted!";
    }
}

// Fetch Reviews
$query = "
    SELECT r.*, u.fullname 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
";
$result = pg_query($conn, $query);

$active_page = 'reviews';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Review Management</h2>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo strtoupper($row['review_type']); ?></span></td>
                    <td>
                        <?php 
                        for($i=0; $i<$row['rating']; $i++) echo '<i class="fas fa-star text-warning"></i>';
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
                    <td>
                        <?php 
                        $status = $row['status'];
                        $badgeClass = $status == 'approved' ? 'bg-success' : 'bg-warning text-dark';
                        echo "<span class='badge $badgeClass'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="review_id" value="<?php echo $row['id']; ?>">
                            <?php if ($status == 'pending'): ?>
                                <button name="action" value="approve" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                            <?php endif; ?>
                            <button name="action" value="delete" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this review?');"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
