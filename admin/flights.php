<?php
include '../db.php';
include 'auth_check.php';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'delete') {
            $flight_id = intval($_POST['flight_id']);
            pg_query_params($conn, "DELETE FROM flights WHERE flight_id = $1", array($flight_id));
            $msg = "Flight deleted!";
        } elseif ($action == 'save') {
            $f_num = $_POST['flight_number'];
            $dep = $_POST['departure_airport'];
            $arr = $_POST['arrival_airport'];
            $d_time = $_POST['departure_time'];
            $a_time = $_POST['arrival_time'];
            $price = $_POST['base_price'];
            $seats = $_POST['total_seats'];
            $avail = $_POST['available_seats'];
            $status = $_POST['status'];
            $id = isset($_POST['flight_id']) ? $_POST['flight_id'] : '';

            if ($id) {
                // Update
                $sql = "UPDATE flights SET flight_number=$1, departure_airport=$2, arrival_airport=$3, departure_time=$4, arrival_time=$5, base_price=$6, total_seats=$7, available_seats=$8, status=$9 WHERE flight_id=$10";
                pg_query_params($conn, $sql, array($f_num, $dep, $arr, $d_time, $a_time, $price, $seats, $avail, $status, $id));
                $msg = "Flight updated!";
            } else {
                // Insert
                // Assuming Airline ID 1 for now or add a select box
                $airline_id = 1; 
                $sql = "INSERT INTO flights (flight_number, airline_id, departure_airport, arrival_airport, departure_time, arrival_time, base_price, total_seats, available_seats, status) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
                pg_query_params($conn, $sql, array($f_num, $airline_id, $dep, $arr, $d_time, $a_time, $price, $seats, $avail, $status));
                $msg = "Flight added!";
            }
        }
    }
}

// Fetch Flights
$query = "SELECT f.*, a.airline_name FROM flights f JOIN airlines a ON f.airline_id = a.airline_id ORDER BY f.departure_time DESC";
$result = pg_query($conn, $query);

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
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['flight_number']; ?></td>
                    <td><?php echo $row['airline_name']; ?></td>
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
                        <div class="col-md-6 mb-3">
                            <label>Flight Number</label>
                            <input type="text" name="flight_number" id="flight_number" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
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
                                <option value="delayed">Delayed</option>
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
