<?php
require_once __DIR__ . '/../database/db.php';
include 'auth_check.php';

if (!isset($_GET['tour_id'])) {
    header('Location: tours.php');
    exit;
}

$tour_id = intval($_GET['tour_id']);
$msg = '';

// Fetch tour details
$tour_res = db_query($conn, "SELECT name FROM tour_packages WHERE id = ?", [$tour_id]);
$tour = db_fetch_assoc($tour_res);

// Handle Add/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'delete') {
        $id = intval($_POST['schedule_id']);
        db_query($conn, "DELETE FROM tour_schedules WHERE id = ?", [$id]);
        $msg = "Schedule deleted!";
    } elseif ($action == 'save') {
        $start_date = $_POST['start_date'];
        $seats = intval($_POST['available_seats']);
        
        $sql = "INSERT INTO tour_schedules (tour_id, start_date, available_seats) VALUES (?, ?, ?)";
        db_query($conn, $sql, [$tour_id, $start_date, $seats]);
        $msg = "New schedule added!";
    }
}

// Fetch Schedules
$schedules = db_query($conn, "SELECT * FROM tour_schedules WHERE tour_id = ? ORDER BY start_date ASC", [$tour_id]);

$active_page = 'tours';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="tours.php">Tours</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($tour['name']); ?> Schedules</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h2>Manage Schedules: <?php echo htmlspecialchars($tour['name']); ?></h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                <i class="fas fa-plus"></i> Add Fixed Date
            </button>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Start Date</th>
                            <th>Available Seats</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (db_num_rows($schedules) > 0): ?>
                            <?php while ($row = db_fetch_assoc($schedules)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td class="fw-bold"><?php echo date('d M Y', strtotime($row['start_date'])); ?></td>
                                <td><?php echo $row['available_seats']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this schedule?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No fixed dates found. Add one to enable booking.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Fixed Date Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="available_seats" class="form-control" value="30" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>