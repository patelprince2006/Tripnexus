<?php
include '../db.php';
include 'auth_check.php';

$msg = '';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'delete') {
        $id = intval($_POST['hotel_id']);
        pg_query_params($conn, "DELETE FROM hotels WHERE id = $1", array($id));
        $msg = "Hotel deleted!";
    } elseif ($action == 'save') {
        $name = $_POST['name'];
        $city = $_POST['city'];
        $address = $_POST['address'];
        $desc = $_POST['description'];
        $price = $_POST['price_per_night'];
        $rating = $_POST['rating'];
        $image = $_POST['main_image'];
        $id = isset($_POST['hotel_id']) ? $_POST['hotel_id'] : '';

        if ($id) {
            $sql = "UPDATE hotels SET name=$1, city=$2, address=$3, description=$4, price_per_night=$5, rating=$6, main_image=$7 WHERE id=$8";
            pg_query_params($conn, $sql, array($name, $city, $address, $desc, $price, $rating, $image, $id));
            $msg = "Hotel updated!";
        } else {
            $sql = "INSERT INTO hotels (name, city, address, description, price_per_night, rating, main_image) VALUES ($1, $2, $3, $4, $5, $6, $7)";
            pg_query_params($conn, $sql, array($name, $city, $address, $desc, $price, $rating, $image));
            $msg = "Hotel added!";
        }
    }
}

// Fetch Hotels
$result = pg_query($conn, "SELECT * FROM hotels ORDER BY id DESC");

$active_page = 'hotels';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Hotel Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hotelModal" onclick="clearForm()">
            <i class="fas fa-plus"></i> Add Hotel
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>Price/Night</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = pg_fetch_assoc($result)): ?>
                <tr>
                    <td><img src="<?php echo $row['main_image'] ? $row['main_image'] : 'https://placehold.co/50'; ?>" width="50" class="rounded"></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td>₹<?php echo $row['price_per_night']; ?></td>
                    <td><?php echo $row['rating']; ?> <i class="fas fa-star text-warning"></i></td>
                    <td>
                        <button class="btn btn-sm btn-info btn-action" onclick='editHotel(<?php echo json_encode($row); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this hotel?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="hotel_id" value="<?php echo $row['id']; ?>">
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
<div class="modal fade" id="hotelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="hotel_id" id="hotel_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>City</label>
                            <input type="text" name="city" id="city" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" id="address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Price per Night</label>
                            <input type="number" step="0.01" name="price_per_night" id="price_per_night" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Rating</label>
                            <input type="number" step="0.1" max="5" name="rating" id="rating" class="form-control" value="4.0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Image URL</label>
                            <input type="text" name="main_image" id="main_image" class="form-control" placeholder="http://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editHotel(data) {
    document.getElementById('modalTitle').innerText = 'Edit Hotel';
    document.getElementById('hotel_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('city').value = data.city;
    document.getElementById('address').value = data.address;
    document.getElementById('description').value = data.description;
    document.getElementById('price_per_night').value = data.price_per_night;
    document.getElementById('rating').value = data.rating;
    document.getElementById('main_image').value = data.main_image;
    
    var myModal = new bootstrap.Modal(document.getElementById('hotelModal'));
    myModal.show();
}

function clearForm() {
    document.getElementById('modalTitle').innerText = 'Add Hotel';
    document.querySelector('form').reset();
    document.getElementById('hotel_id').value = '';
}
</script>

<?php include 'includes/footer.php'; ?>
