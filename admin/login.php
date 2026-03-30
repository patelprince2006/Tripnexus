<?php
session_start();
include '../database/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Check if admins table exists
        $table_check = db_query($conn, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admins'");
        $table_exists = $table_check ? (int) db_fetch_value($table_check, 0, 0) : 0;
        if ($table_exists === 0) {
            // Auto-setup the admin database
            include 'setup_admin_db.php';
            echo "<div class='alert alert-success'>Admin database has been set up automatically. Please try logging in again.</div>";
            exit();
        } else {
            // Query database
            $query = "SELECT * FROM admins WHERE username = ?";
            $result = db_query($conn, $query, array($username));

            if (!$result) {
                $error = "Database error occurred. Please try again later.";
            } else if (db_num_rows($result) > 0) {
                $admin = db_fetch_assoc($result);
                if (password_verify($password, $admin['password'])) {
                    // Successful login
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role']; // 'superadmin' or 'admin'
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
        }
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('../photos/Homepage-Background.avif');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .login-hero-text {
            color: #ffffff;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.45);
            text-align: left;
        }
        .login-hero-text h1 {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1rem;
        }
        .login-hero-text p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 0.75rem;
            opacity: 0.95;
        }
        .login-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }
        .login-layout {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 30px;
            align-items: center;
        }
        @media (max-width: 992px) {
            .login-layout {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .login-hero-text {
                text-align: center;
                margin-bottom: 2rem;
            }
        }
        .cta-steps {
            padding: 40px 24px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
        }
        .cta-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        .cta-steps h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 24px;
            color: #0d2137;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .step-card h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #1a2b49;
        }
        .step-card p {
            margin: 0;
            font-size: 0.92rem;
            color: #4b5563;
        }
        .step-badge {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff6a6a, #c21d6f);
        }
        .admin-login-header {
            padding: 16px 20px;
            background: rgba(0, 0, 0, 0.46);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .admin-login-inner {
            max-width: 1200px;
            margin: 0 auto;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-login-inner img {
            height: 40px;
            width: auto;
        }
        .admin-brand {
            font-size: 1.4rem;
            font-weight: 700;
        }
        .admin-brand span {
            color: #f5c542;
        }
        @media (max-width: 768px) {
            .steps-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header class="admin-login-header">
    <div class="admin-login-inner">
        <img src="../photos/logo.png" alt="TripNexus Logo">
        <div>
            <div class="admin-brand">Trip<span>Nexus</span> Admin</div>
        </div>
    </div>
</header>

<div class="login-content">
    <div class="login-layout">
        <div class="login-hero-text">
            <h1>Business Travel Super Simplified</h1>
            <p>Multi-platform booking flow, wide range of hotel options and easy modifications to meet your last-minute needs.</p>
            <p>Single platform for filing expenses and ensuring easy reconciliation.</p>
        </div>

        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h3>Admin Panel Login</h3>
                <p class="text-muted mb-0 small">Secure access for administrators</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">GSTIN NO</label>
                    <input type="text" name="gstin" class="form-control" placeholder="Enter GSTIN Number" 
                           minlength="15" maxlength="15" 
                           pattern="^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$" 
                           title="Please enter a valid 15-character GSTIN (e.g., 22AAAAA0000A1Z5)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Enter username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Login to Dashboard</button>
            </form>
            
            <div class="text-center mt-4">
                <a href="../index.php" class="text-secondary text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Website
                </a>
            </div>
        </div>
    </div>
</div>

<section class="cta-steps">
    <div class="cta-inner">
        <h2 class="text-center">Getting Started is Easy</h2>
        <div class="steps-grid">
            <div class="step-card text-center">
                <div class="step-badge">1</div>
                <h3>Configure System</h3>
                <p>Set up your travel policies and preferences in minutes.</p>
            </div>
            <div class="step-card text-center">
                <div class="step-badge">2</div>
                <h3>Manage Content</h3>
                <p>Update flights, hotels, and tours with our intuitive tools.</p>
            </div>
            <div class="step-card text-center">
                <div class="step-badge">3</div>
                <h3>Analyze Growth</h3>
                <p>Monitor bookings and revenue through real-time analytics.</p>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white text-center py-4">
    <p class="mb-0">&copy; 2026 TripNexus | Secure Admin Portal | All Rights Reserved</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
