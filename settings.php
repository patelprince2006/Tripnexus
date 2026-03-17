<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session upon login
$msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $theme = $_POST['theme'];

    $sql = "UPDATE users SET fullname = ?, phone = ?, theme = ? WHERE id = ?";
    $result = db_query($conn, $sql, array($fullname, $phone, $theme, $user_id));

    if ($result) {
        $_SESSION['theme'] = $theme; // Update session theme immediately
        $msg = "Settings updated successfully!";
    }
}

// Fetch current user data
$res = db_query($conn, "SELECT * FROM users WHERE id = ?", array($user_id));
$user = db_fetch_assoc($res);
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f0f2f5;
        min-height: 100vh;
    }

    .settings-header {
        background: var(--primary-gradient);
        color: white;
        padding: 60px 0;
        border-radius: 0 0 50px 50px;
        margin-bottom: -50px;
    }

    .card {
        border: none;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        background: var(--glass-bg);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 12px 15px;
        border: 2px solid #eef0f7;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25 margin-left rgba(102, 126, 234, 0.25);
    }

    .btn-save {
        background: var(--secondary-gradient);
        border: none;
        color: white;
        padding: 15px;
        border-radius: 15px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        opacity: 0.9;
        transform: scale(1.02);
        box-shadow: 0 10px 20px rgba(37, 117, 252, 0.3);
    }

    /* Dark Mode Theme */
    body.dark-mode {
        background: #1a1a2e;
        color: #e9ecef;
    }

    body.dark-mode .card {
        background: #16213e;
        color: white;
    }

    body.dark-mode .form-control, body.dark-mode .form-select {
        background: #0f3460;
        border-color: #1a1a2e;
        color: white;
    }
</style>

<div class="settings-header text-center">
    <h1 class="fw-bold">Experience Your Space</h1>
    <p class="opacity-75">Personalize your journey with TripNexus</p>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <?php if ($msg): ?>
                <div class="alert alert-success border-0 shadow-lg mb-4 rounded-4 py-3 animate__animated animate__fadeInDown">
                    <i class="fas fa-magic me-2"></i> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="card shadow-lg mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                                <i class="fas fa-fingerprint fa-2x text-primary"></i>
                            </div>
                            <h4 class="mb-0 fw-bold">Identity Details</h4>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">DISPLAY NAME</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="fullname" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($user['fullname']); ?>">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-muted small fw-bold">CONTACT NUMBER</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-phone text-muted"></i></span>
                                <input type="text" name="phone" class="form-control border-0 bg-light" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-lg mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3">
                                <i class="fas fa-moon fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Atmosphere</h4>
                                <small class="text-muted">Choose your visual environment</small>
                            </div>
                        </div>

                        <select name="theme" class="form-select form-select-lg">
                            <option value="light" <?php echo ($user['theme'] == 'light') ? 'selected' : ''; ?>>☀️ Radiant Light</option>
                            <option value="dark" <?php echo ($user['theme'] == 'dark') ? 'selected' : ''; ?>>🌑 Deep Space (Dark)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn btn-save w-100 shadow-lg">
                    Update My Experience
                </button>
            </form>
        </div>
    </div>
</div>