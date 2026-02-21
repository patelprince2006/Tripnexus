<?php
include '../db.php';
include 'auth_check.php';

// Handle Actions (Block/Unblock/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action == 'block') {
        pg_query_params($conn, "UPDATE users SET status = 'blocked' WHERE id = $1", array($user_id));
        $msg = "User blocked successfully!";
    } elseif ($action == 'unblock') {
        pg_query_params($conn, "UPDATE users SET status = 'active' WHERE id = $1", array($user_id));
        $msg = "User unblocked successfully!";
    } elseif ($action == 'delete') {
        pg_query_params($conn, "DELETE FROM users WHERE id = $1", array($user_id));
        $msg = "User deleted successfully!";
    }
}

// Fetch Users
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $query = "SELECT * FROM users WHERE fullname ILIKE '%$search%' OR email ILIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM users ORDER BY id DESC";
}
$result = pg_query($conn, $query);

$active_page = 'users';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <form class="d-flex" method="GET">
            <input class="form-control me-2" type="search" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php 
                        $status = isset($row['status']) ? $row['status'] : 'active';
                        $badgeClass = $status == 'active' ? 'bg-success' : 'bg-danger';
                        echo "<span class='badge $badgeClass'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <?php if ($status == 'active'): ?>
                                <button type="submit" name="action" value="block" class="btn btn-warning btn-sm btn-action" title="Block User">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="action" value="unblock" class="btn btn-success btn-sm btn-action" title="Unblock User">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm btn-action" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
