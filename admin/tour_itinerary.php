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
$tour_res = db_query($conn, "SELECT name, duration FROM tour_packages WHERE id = ?", [$tour_id]);
$tour = db_fetch_assoc($tour_res);

// Handle Save/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'delete') {
        $id = intval($_POST['itinerary_id']);
        db_query($conn, "DELETE FROM tour_itinerary WHERE id = ?", [$id]);
        $msg = "Itinerary day deleted!";
    } elseif ($action == 'save') {
        $day_number = intval($_POST['day_number']);
        $route_from = $_POST['route_from'];
        $route_to = $_POST['route_to'];
        $transport_type = $_POST['transport_type'];
        $transport_time = $_POST['transport_time'];
        $hotel_id = !empty($_POST['hotel_id']) ? intval($_POST['hotel_id']) : null;
        $activities = $_POST['activities'];
        $id = isset($_POST['itinerary_id']) ? intval($_POST['itinerary_id']) : '';

        if ($id) {
            $sql = "UPDATE tour_itinerary SET day_number=?, route_from=?, route_to=?, transport_type=?, transport_time=?, hotel_id=?, activities=? WHERE id=?";
            db_query($conn, $sql, [$day_number, $route_from, $route_to, $transport_type, $transport_time, $hotel_id, $activities, $id]);
            $msg = "Itinerary day updated!";
        } else {
            $sql = "INSERT INTO tour_itinerary (tour_id, day_number, route_from, route_to, transport_type, transport_time, hotel_id, activities) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            db_query($conn, $sql, [$tour_id, $day_number, $route_from, $route_to, $transport_type, $transport_time, $hotel_id, $activities]);
            $msg = "New itinerary day added!";
        }
    }
}

// Fetch Itinerary
$itinerary_res = db_query($conn, "SELECT i.*, h.name as hotel_name FROM tour_itinerary i LEFT JOIN hotels h ON i.hotel_id = h.hotel_id WHERE i.tour_id = ? ORDER BY i.day_number ASC", [$tour_id]);
$itinerary = [];
while ($row = db_fetch_assoc($itinerary_res)) {
    $itinerary[] = $row;
}

// Fetch Hotels for dropdown
$hotels_res = db_query($conn, "SELECT hotel_id, name, city FROM hotels ORDER BY city, name ASC");
$hotels = [];
while ($row = db_fetch_assoc($hotels_res)) {
    $hotels[] = $row;
}

$active_page = 'tours';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="tours.php">Tours</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($tour['name']); ?> Itinerary</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h2>Manage Itinerary: <?php echo htmlspecialchars($tour['name']); ?></h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itineraryModal" onclick="clearForm()">
                <i class="fas fa-plus"></i> Add Day Details
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
                            <th>Day</th>
                            <th>Route</th>
                            <th>Transport</th>
                            <th>Hotel (Night)</th>
                            <th>Activities</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($itinerary) > 0): ?>
                            <?php foreach ($itinerary as $row): ?>
                            <tr>
                                <td class="fw-bold">Day <?php echo $row['day_number']; ?></td>
                                <td><?php echo htmlspecialchars($row['route_from']); ?> &rarr; <?php echo htmlspecialchars($row['route_to']); ?></td>
                                <td>
                                    <?php if ($row['transport_type'] != 'None'): ?>
                                        <span class="badge bg-info text-dark">
                                            <i class="fas fa-<?php echo strtolower($row['transport_type']); ?>"></i> 
                                            <?php echo $row['transport_type']; ?> (<?php echo date('H:i', strtotime($row['transport_time'])); ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['hotel_id']): ?>
                                        <i class="fas fa-hotel text-primary"></i> <?php echo htmlspecialchars($row['hotel_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No hotel set</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars(substr($row['activities'], 0, 50)); ?>...</small></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick='editItinerary(<?php echo json_encode($row); ?>)'>Edit</button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this day details?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="itinerary_id" value="<?php echo $row['id']; ?>">
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">No daily itinerary details added yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="itineraryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Itinerary Day Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="itinerary_id" id="itinerary_id">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Day Number</label>
                            <input type="number" name="day_number" id="day_number" class="form-control" required min="1" max="<?php echo $tour['duration']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Route From</label>
                            <input type="text" name="route_from" id="route_from" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Route To</label>
                            <input type="text" name="route_to" id="route_to" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Transport Type</label>
                            <select name="transport_type" id="transport_type" class="form-control">
                                <option value="None">None / Walking</option>
                                <option value="Bus">Bus</option>
                                <option value="Train">Train</option>
                                <option value="Flight">Flight</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Transport Time</label>
                            <input type="time" name="transport_time" id="transport_time" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stay Hotel (Night)</label>
                            <select name="hotel_id" id="hotel_id" class="form-control">
                                <option value="">No Hotel / Overnight Travel</option>
                                <?php foreach ($hotels as $h): ?>
                                    <option value="<?php echo $h['hotel_id']; ?>"><?php echo htmlspecialchars($h['city'] . ' - ' . $h['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select hotel for this day's night stay.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Day Activities / Description</label>
                        <textarea name="activities" id="activities" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Day Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editItinerary(data) {
    document.getElementById('modalTitle').innerText = 'Edit Itinerary Day Details';
    document.getElementById('itinerary_id').value = data.id;
    document.getElementById('day_number').value = data.day_number;
    document.getElementById('route_from').value = data.route_from;
    document.getElementById('route_to').value = data.route_to;
    document.getElementById('transport_type').value = data.transport_type;
    document.getElementById('transport_time').value = data.transport_time;
    document.getElementById('hotel_id').value = data.hotel_id || '';
    document.getElementById('activities').value = data.activities;
    
    var myModal = new bootstrap.Modal(document.getElementById('itineraryModal'));
    myModal.show();
}

function clearForm() {
    document.getElementById('modalTitle').innerText = 'Add Itinerary Day Details';
    document.getElementById('itinerary_id').value = '';
    document.getElementById('day_number').value = '';
    document.getElementById('route_from').value = '';
    document.getElementById('route_to').value = '';
    document.getElementById('transport_type').value = 'None';
    document.getElementById('transport_time').value = '';
    document.getElementById('hotel_id').value = '';
    document.getElementById('activities').value = '';
}
</script>

<?php include 'includes/footer.php'; ?>