<?php
include '../database/db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'] ?? 'User';
$msg = '';
$msg_type = 'success';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $theme = $_POST['theme'] ?? 'light';

    $sql = "UPDATE users SET fullname = ?, phone = ?, theme = ? WHERE id = ?";
    $result = db_query($conn, $sql, array($new_fullname, $phone, $theme, $user_id));

    if ($result) {
        $_SESSION['fullname'] = $new_fullname;
        $_SESSION['theme'] = $theme;
        $msg = "Experience updated successfully!";
        $msg_type = 'success';
    } else {
        $msg = "Failed to update settings.";
        $msg_type = 'danger';
    }
}

// Fetch current user data
$res = db_query($conn, "SELECT * FROM users WHERE id = ?", array($user_id));
$user = db_fetch_assoc($res);
$theme = $user['theme'] ?? 'light';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/style.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d2137 0%, #1a3a5a 100%);
            --accent-gradient: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: #0d2137 !important;
        }

        .settings-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 0 100px;
            text-align: center;
            margin-bottom: -60px;
        }

        .settings-card {
            border: none;
            border-radius: 24px;
            background: var(--glass-bg);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .form-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #f1f3f5;
            background: #f8f9fa;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #ffc107;
            background: #fff;
            box-shadow: none;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #f1f3f5;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #adb5bd;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .btn-update {
            background: var(--accent-gradient);
            border: none;
            color: #0d2137;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3);
            color: #000;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-right: 16px;
        }

        footer {
            margin-top: auto;
            background-color: #212529;
            color: white;
            padding: 20px 0;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <img src="../photos/logo.png" alt="TripNexus Logo" style="height: 35px; width: auto;" class="me-2">
                Trip<span class="text-warning">Nexus</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-light rounded-pill px-3" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="settings-hero">
        <div class="container">
            <h1 class="display-5 fw-bold mb-2">Experience Your Space</h1>
            <p class="lead opacity-75">Personalize your journey with TripNexus</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> border-0 shadow-sm mb-4 rounded-4 p-3 animate__animated animate__fadeIn">
                        <i class="bi <?php echo $msg_type == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i> 
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Profile Section -->
                    <div class="settings-card mb-4 p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-badge fs-4"></i>
                            </div>
                            <h4 class="mb-0 fw-bold">Identity Details</h4>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Display Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>

                    <!-- Atmosphere Section -->
                    <div class="settings-card mb-5 p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-palette fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Atmosphere</h4>
                                <small class="text-muted">Choose your visual environment</small>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Theme Preference</label>
                            <select name="theme" class="form-select shadow-none">
                                <option value="light" <?php echo ($theme == 'light') ? 'selected' : ''; ?>>☀️ Radiant Light</option>
                                <option value="dark" <?php echo ($theme == 'dark') ? 'selected' : ''; ?>>🌑 Deep Space (Dark Mode)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="update_profile" class="btn btn-update w-100 shadow-sm">
                        Update My Experience
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2026 TripNexus | All Rights Reserved | 
                <a href="../admin/login.php" class="text-white-50 text-decoration-none small">Admin</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>