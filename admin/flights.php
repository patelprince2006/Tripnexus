<?php
require_once __DIR__ . '/../database/db.php';
include 'auth_check.php';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'delete') {
            $flight_id = intval($_POST['flight_id'] ?? 0);
            db_query($conn, "DELETE FROM flights WHERE flight_id = ?", array($flight_id));
            $msg = "Flight deleted!";
        } elseif ($action == 'save') {
            $f_num = $_POST['flight_number'] ?? '';
            $dep = $_POST['departure_airport'] ?? '';
            $arr = $_POST['arrival_airport'] ?? '';
            $d_time = $_POST['departure_time'] ?? '';
            $a_time = $_POST['arrival_time'] ?? '';
            $price = $_POST['base_price'] ?? 0;
            $seats = $_POST['total_seats'] ?? 0;
            $avail = $_POST['available_seats'] ?? 0;
            $status = $_POST['status'] ?? '';
            $airline_id = intval($_POST['airline_id'] ?? 0);
            if ($airline_id === 0) {
                $error = "Please select a valid airline.";
            } else {
                $id = $_POST['flight_id'] ?? '';

            // Ensure airports exist
            $check_dep = db_query($conn, "SELECT airport_code FROM airports WHERE airport_code = ?", array($dep));
            if (!db_fetch_assoc($check_dep)) {
                db_query($conn, "INSERT INTO airports (airport_code, airport_name, city, country) VALUES (?, ?, ?, ?)", array($dep, $dep . ' Airport', 'Unknown', 'Unknown'));
            }
            $check_arr = db_query($conn, "SELECT airport_code FROM airports WHERE airport_code = ?", array($arr));
            if (!db_fetch_assoc($check_arr)) {
                db_query($conn, "INSERT INTO airports (airport_code, airport_name, city, country) VALUES (?, ?, ?, ?)", array($arr, $arr . ' Airport', 'Unknown', 'Unknown'));
            }

            if ($id) {
                // Update
                $sql = "UPDATE flights SET flight_number=?, airline_id=?, departure_airport=?, arrival_airport=?, departure_time=?, arrival_time=?, base_price=?, total_seats=?, available_seats=?, status=? WHERE flight_id=?";
                $res = db_query($conn, $sql, array($f_num, $airline_id, $dep, $arr, $d_time, $a_time, $price, $seats, $avail, $status, $id));
                if ($res) {
                    $msg = "Flight updated successfully!";
                } else {
                    $error = "Error updating flight: " . db_last_error($conn);
                }
            } else {
                // Insert
                $sql = "INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $res = db_query($conn, $sql, array($f_num, $airline_id, $dep, $arr, $d_time, $a_time, $price, $seats, $avail, $status));
                if ($res) {
                    $msg = "Flight added successfully!";
                } else {
                    $error = "Error adding flight: " . db_last_error($conn);
                }
            }
            }
        }
    }
}

// Fetch Airlines for dropdown
$airlines_res = db_query($conn, "SELECT airline_id, airline_name FROM airlines ORDER BY airline_name ASC");
$airlines = [];
if ($airlines_res) {
    while ($row = db_fetch_assoc($airlines_res)) {
        $airlines[] = $row;
    }
}

// Fetch Flights
$query = "SELECT f.*, a.airline_name FROM flights f LEFT JOIN airlines a ON f.airline_id = a.airline_id ORDER BY f.departure_time DESC";
$result = db_query($conn, $query);

$active_page = 'flights';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Flight Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#flightModal" onclick="clearForm()">
            <i class="fas fa-plus"></i> Add Flight
        </button>
    </div>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Flight No</th>
                    <th>Airline</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Price</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = db_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['flight_number']; ?></td>
                    <td><?php echo $row['airline_name'] ?? '<span class="text-danger">No Airline</span>'; ?></td>
                    <td><?php echo $row['departure_airport'] . ' <i class="fas fa-arrow-right"></i> ' . $row['arrival_airport']; ?></td>
                    <td><?php echo date('d M H:i', strtotime($row['departure_time'])); ?></td>
                    <td>₹<?php echo $row['base_price']; ?></td>
                    <td><?php echo $row['available_seats'] . '/' . $row['total_seats']; ?></td>
                    <td><span class="badge bg-info"><?php echo $row['status']; ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-info btn-action" onclick='editFlight(<?php echo json_encode($row); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this flight?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="flight_id" value="<?php echo $row['flight_id']; ?>">
                            <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="flightModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Flight</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="flight_id" id="flight_id">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Flight Number</label>
                            <input type="text" name="flight_number" id="flight_number" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Airline</label>
                            <select name="airline_id" id="airline_id" class="form-control" required>
                                <option value="">Select Airline</option>
                                <?php if (empty($airlines)): ?>
                                    <option value="" disabled>No airlines found! Please add airlines first.</option>
                                <?php else: ?>
                                    <?php foreach ($airlines as $airline): ?>
                                        <option value="<?php echo $airline['airline_id']; ?>">
                                            <?php echo htmlspecialchars($airline['airline_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Base Price</label>
                            <input type="number" name="base_price" id="base_price" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>From</label>
                            <input type="text" name="departure_airport" id="departure_airport" class="form-control" placeholder="e.g. BOM" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>To</label>
                            <input type="text" name="arrival_airport" id="arrival_airport" class="form-control" placeholder="e.g. DEL" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Departure Time</label>
                            <input type="datetime-local" name="departure_time" id="departure_time" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" id="arrival_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Total Seats</label>
                            <input type="number" name="total_seats" id="total_seats" class="form-control" value="60" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Available Seats</label>
                            <input type="number" name="available_seats" id="available_seats" class="form-control" value="60" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="scheduled">Scheduled</option>
                                <option value="boarding">Boarding</option>
                                <option value="departed">Departed</option>
                                <option value="landed">Landed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Flight</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editFlight(data) {
    document.getElementById('modalTitle').innerText = 'Edit Flight';
    document.getElementById('flight_id').value = data.flight_id;
    document.getElementById('flight_number').value = data.flight_number;
    document.getElementById('airline_id').value = data.airline_id;
    document.getElementById('base_price').value = data.base_price;
    document.getElementById('departure_airport').value = data.departure_airport;
    document.getElementById('arrival_airport').value = data.arrival_airport;
    
    // Format timestamp for datetime-local input
    document.getElementById('departure_time').value = data.departure_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('arrival_time').value = data.arrival_time.replace(' ', 'T').slice(0, 16);
    
    document.getElementById('total_seats').value = data.total_seats;
    document.getElementById('available_seats').value = data.available_seats;
    document.getElementById('status').value = data.status;
    
    var myModal = new bootstrap.Modal(document.getElementById('flightModal'));
    myModal.show();
}

function clearForm() {
    document.getElementById('modalTitle').innerText = 'Add Flight';
    document.querySelector('form').reset();
    document.getElementById('flight_id').value = '';
}
</script>

<?php include 'includes/footer.php'; ?>
