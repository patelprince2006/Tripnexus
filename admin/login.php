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
                    $error = "Invalid password. If you forgot your password, you can <a href='setup_admin_db.php' class='text-primary'>reset it to admin123 here</a>.";
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
            min-height: 130vh;
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
            padding: 30px 20px 30px;
        }
        .login-layout {
            width: 100%;
            max-width: 1500px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 30px;
            align-items: center;
        }
        @media (max-width: 768px) {
            .login-hero-text {
                text-align: center;
            }
            .login-hero-text h1 {
                font-size: 2rem;
            }
        }
        .cta-steps {
            padding: 28px 24px;
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
            margin-bottom: 18px;
            color: #0d2137;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
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
            padding: 16px 20px 12px;
            background: rgba(0, 0, 0, 0.46);
            border-bottom: 1px solid rgba(109, 106, 106, 0.12);
        }
        .admin-login-inner {
            max-width: 1100px;
            color: #ffffff;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-login-inner img {
            height: 34px;
            width: auto;
        }
        .admin-brand {
            font-size: 1.3rem;
            font-weight: 700;
        }
        .admin-brand span {
            color: #f5c542;
        }
        .admin-login-inner h1 {
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .admin-login-inner p {
            margin: 0;
            font-size: 0.98rem;
            opacity: 0.95;
        }
        .admin-footer {
            margin-top: 0 !important;
        }
        @media (max-width: 992px) {
            .login-layout {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .steps-grid {
                grid-template-columns: 1fr;
            }
            body {
                padding: 40px 0;
            }
        }
</style>
</head>
<body>

<header class="admin-login-header">
    <div class="admin-login-inner">
        <img src="../photos/logo.png" alt="TripNexus Logo">
        <div>
            <div class="admin-brand">Trip<span>Nexus</span></div>
            <p>Secure access to manage users, bookings, and content.</p>
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
                <h3>Admin Panel</h3>
                <p class="text-muted mb-0 small">Manage bookings, users, and site content securely</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">access name</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">access Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="../index.php" class="text-secondary small">Back to Website</a>
            </div>
        </div>
    </div>
</div>

<section class="cta-steps">
    <div class="cta-inner">
        <h2>Don't Wait Anymore. Get Started in Just 3 Steps</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-badge">1</div>
                <h3>Create Your Account Now</h3>
                <p>Get started by providing minimal details like employee size, organization name, and preferences.</p>
            </div>
            <div class="step-card">
                <div class="step-badge">2</div>
                <h3>Set Up Employee-Friendly Policy Guidelines</h3>
                <p>Define employee-friendly policies to unlock better control and smoother approvals.</p>
            </div>
            <div class="step-card">
                <div class="step-badge">3</div>
                <h3>Invite Your Employees and Start Booking</h3>
                <p>Invite your team so they can book travel quickly while staying within policy.</p>
            </div>
        </div>
    </div>
</section>
<footer class="bg-dark text-white text-center py-4 admin-footer">
    <p class="mb-0">© 2026 TripNexus | All Rights Reserved |</p>
</footer>
</body>
</html>
