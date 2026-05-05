<?php
require_once __DIR__ . '/../database/db.php';
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
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $fullname = trim($first_name . ' ' . $last_name);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $nationality = trim($_POST['nationality']);
    $marital_status = $_POST['marital_status'];
    $city_of_residence = trim($_POST['city_of_residence']);
    $booker_type = $_POST['booker_type'];
    $phone = trim($_POST['phone']);
    $passport_no = trim($_POST['passport_no']);
    $expiry_date = $_POST['expiry_date'];
    $issuing_country = trim($_POST['issuing_country']);
    $theme = $_POST['theme'] ?? 'light';

    $sql = "UPDATE users SET 
            fullname = ?, 
            first_name = ?, 
            last_name = ?, 
            gender = ?, 
            dob = ?, 
            nationality = ?, 
            marital_status = ?, 
            city_of_residence = ?, 
            booker_type = ?, 
            phone = ?, 
            passport_no = ?, 
            expiry_date = ?, 
            issuing_country = ?, 
            theme = ? 
            WHERE id = ?";
            
    $params = [
        $fullname, 
        $first_name, 
        $last_name, 
        $gender, 
        $dob, 
        $nationality, 
        $marital_status, 
        $city_of_residence, 
        $booker_type, 
        $phone, 
        $passport_no, 
        $expiry_date, 
        $issuing_country, 
        $theme, 
        $user_id
    ];

    $result = db_query($conn, $sql, $params);

    if ($result) {
        $_SESSION['fullname'] = $fullname;
        $_SESSION['theme'] = $theme;
        $msg = "Profile updated successfully!";
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-6 fw-bold mb-0 text-start">My Profile</h1>
                <button type="submit" form="profileForm" name="update_profile" class="btn btn-sm px-4 fw-bold" style="background: #ccc; border-radius: 8px;">SAVE</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> border-0 shadow-sm mb-4 rounded-4 p-3 animate__animated animate__fadeIn">
                        <i class="bi <?php echo $msg_type == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i> 
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="profileForm">
                    <!-- General Information Section -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3">General Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">First & Middle Name</label>
                                    <input type="text" name="first_name" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" placeholder="Enter name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Last Name</label>
                                    <input type="text" name="last_name" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" placeholder="Enter last name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Gender</label>
                                    <select name="gender" class="form-select border-0 p-0 fw-bold shadow-none">
                                        <option value="">GENDER</option>
                                        <option value="Male" <?php echo ($user['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($user['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($user['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Nationality</label>
                                    <select name="nationality" class="form-select border-0 p-0 fw-bold shadow-none">
                                        <option value="">NATIONALITY</option>
                                        <option value="Indian" <?php echo ($user['nationality'] ?? '') == 'Indian' ? 'selected' : ''; ?>>Indian</option>
                                        <option value="American" <?php echo ($user['nationality'] ?? '') == 'American' ? 'selected' : ''; ?>>American</option>
                                        <option value="British" <?php echo ($user['nationality'] ?? '') == 'British' ? 'selected' : ''; ?>>British</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Marital Status</label>
                                    <select name="marital_status" class="form-select border-0 p-0 fw-bold shadow-none">
                                        <option value="">MARITAL STATUS</option>
                                        <option value="Single" <?php echo ($user['marital_status'] ?? '') == 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo ($user['marital_status'] ?? '') == 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Divorced" <?php echo ($user['marital_status'] ?? '') == 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">City of Residence</label>
                                    <input type="text" name="city_of_residence" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['city_of_residence'] ?? ''); ?>" placeholder="CITY OF RESIDENCE">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Booker Type</label>
                                    <select name="booker_type" class="form-select border-0 p-0 fw-bold shadow-none">
                                        <option value="">Booker Type</option>
                                        <option value="Leisure" <?php echo ($user['booker_type'] ?? '') == 'Leisure' ? 'selected' : ''; ?>>Leisure</option>
                                        <option value="Business" <?php echo ($user['booker_type'] ?? '') == 'Business' ? 'selected' : ''; ?>>Business</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Section -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-1">Contact Details</h5>
                        <p class="text-muted small mb-3">Add contact information to receive booking details & other alerts</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Mobile Number</label>
                                    <input type="text" name="phone" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter mobile number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <label class="form-label small text-muted text-uppercase fw-bold mb-1">Email ID</label>
                                        <input type="email" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                    </div>
                                    <div class="text-success ms-2">
                                        <i class="bi bi-check-lg fs-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Details Section -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3">Documents Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Passport No.</label>
                                    <input type="text" name="passport_no" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['passport_no'] ?? ''); ?>" placeholder="PASSPORT NO.">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control border-0 p-0 fw-bold" value="<?php echo htmlspecialchars($user['expiry_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-2 rounded-3 border">
                                    <label class="form-label small text-muted text-uppercase fw-bold mb-1">Issuing Country</label>
                                    <select name="issuing_country" class="form-select border-0 p-0 fw-bold shadow-none">
                                        <option value="">ISSUING COUNTRY</option>
                                        <option value="India" <?php echo ($user['issuing_country'] ?? '') == 'India' ? 'selected' : ''; ?>>India</option>
                                        <option value="USA" <?php echo ($user['issuing_country'] ?? '') == 'USA' ? 'selected' : ''; ?>>USA</option>
                                        <!-- Add more options as needed -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Atmosphere Section (Moved to bottom or kept) -->
                    <div class="settings-card mb-5 p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning" style="width: 40px; height: 40px;">
                                <i class="bi bi-palette fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">Atmosphere</h6>
                                <small class="text-muted">Visual environment</small>
                            </div>
                        </div>

                        <div class="mb-0">
                            <select name="theme" class="form-select shadow-none">
                                <option value="light" <?php echo ($theme == 'light') ? 'selected' : ''; ?>>☀️ Radiant Light</option>
                                <option value="dark" <?php echo ($theme == 'dark') ? 'selected' : ''; ?>>🌑 Deep Space (Dark Mode)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button (Sticky or at bottom) -->
                    <button type="submit" name="update_profile" class="btn btn-update w-100 shadow-sm mb-4">
                        Update Profile
                    </button>
                    
                    <div class="text-center">
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