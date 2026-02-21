<?php
include '../db.php';
include 'auth_check.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $type = $_POST['type'];

    if ($title && $message) {
        // Save to DB
        $sql = "INSERT INTO notifications (title, message, type) VALUES ($1, $2, $3)";
        pg_query_params($conn, $sql, array($title, $message, $type));
        
        // TODO: In a real app, you might loop through users and send emails here.
        
        $msg = "Notification sent successfully!";
    }
}

// Fetch Past Notifications
$result = pg_query($conn, "SELECT * FROM notifications ORDER BY sent_at DESC LIMIT 10");

$active_page = 'notifications';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">Notifications & Offers</h2>

    <div class="row">
        <!-- Send Form -->
        <div class="col-md-5">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Send Notification</h5>
                </div>
                <div class="card-body">
                    <?php if ($msg): ?>
                        <div class="alert alert-success"><?php echo $msg; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Summer Sale!">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="general">General Announcement</option>
                                <option value="offer">Special Offer</option>
                                <option value="discount">Discount</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Enter details..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Send Notification</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Recent Notifications</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = pg_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo $row['type']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($row['sent_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
