<?php
include '../db.php';
include 'auth_check.php';

$msg = '';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'delete') {
        $id = intval($_POST['tour_id']);
        pg_query_params($conn, "DELETE FROM tour_packages WHERE id = $1", array($id));
        $msg = "Tour package deleted!";
    } elseif ($action == 'save') {
        $name = $_POST['name'];
        $loc = $_POST['location'];
        $dur = $_POST['duration'];
        $price = $_POST['price'];
        $desc = $_POST['description'];
        $image = $_POST['main_image'];
        $id = isset($_POST['tour_id']) ? $_POST['tour_id'] : '';

        if ($id) {
            $sql = "UPDATE tour_packages SET name=$1, location=$2, duration=$3, price=$4, description=$5, main_image=$6 WHERE id=$7";
            pg_query_params($conn, $sql, array($name, $loc, $dur, $price, $desc, $image, $id));
            $msg = "Tour package updated!";
        } else {
            $sql = "INSERT INTO tour_packages (name, location, duration, price, description, main_image) VALUES ($1, $2, $3, $4, $5, $6)";
            pg_query_params($conn, $sql, array($name, $loc, $dur, $price, $desc, $image));
            $msg = "Tour package added!";
        }
    }
}

// Fetch Tours
$result = pg_query($conn, "SELECT * FROM tour_packages ORDER BY id DESC");

$active_page = 'tours';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tour Packages</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tourModal" onclick="clearForm()">
            <i class="fas fa-plus"></i> Add Package
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <?php while ($row = pg_fetch_assoc($result)): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img src="<?php echo $row['main_image'] ? $row['main_image'] : 'https://placehold.co/400x200'; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="text-muted mb-1"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p class="mb-2"><span class="badge bg-primary"><?php echo $row['duration']; ?> Days</span></p>
                    <h5 class="text-success mb-3">₹<?php echo number_format($row['price']); ?></h5>
                    
                    <button class="btn btn-sm btn-info w-100 mb-2" onclick='editTour(<?php echo json_encode($row); ?>)'>Edit Details</button>
                    <form method="POST" onsubmit="return confirm('Delete this package?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="tour_id" value="<?php echo $row['id']; ?>">
                        <button class="btn btn-sm btn-outline-danger w-100">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="tourModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Tour Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="tour_id" id="tour_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Package Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Location</label>
                            <input type="text" name="location" id="location" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Duration (Days)</label>
                            <input type="number" name="duration" id="duration" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Price</label>
                            <input type="number" name="price" id="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Image URL</label>
                        <input type="text" name="main_image" id="main_image" class="form-control" placeholder="http://...">
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTour(data) {
    document.getElementById('modalTitle').innerText = 'Edit Tour Package';
    document.getElementById('tour_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('location').value = data.location;
    document.getElementById('duration').value = data.duration;
    document.getElementById('price').value = data.price;
    document.getElementById('main_image').value = data.main_image;
    document.getElementById('description').value = data.description;
    
    var myModal = new bootstrap.Modal(document.getElementById('tourModal'));
    myModal.show();
}

function clearForm() {
    document.getElementById('modalTitle').innerText = 'Add Tour Package';
    document.querySelector('form').reset();
    document.getElementById('tour_id').value = '';
}
</script>

<?php include 'includes/footer.php'; ?>
