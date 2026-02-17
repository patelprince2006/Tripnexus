<?php
session_start();
include '../db.php';

// SECURITY CONFIGURATION
define('ADMIN_SECRET_KEY', 'SKYHIGH_ADMIN_2026'); // Change this to something secure!

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $secret_key = $_POST['secret_key'];

    // 1. Verify Secret Key
    if ($secret_key !== ADMIN_SECRET_KEY) {
        $error = "Invalid Security Key! You are not authorized to create an admin account.";
    } 
    // 2. Validate Passwords
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } 
    else {
        // 3. Check for existing user/email
        $check = pg_query_params($conn, "SELECT id FROM admins WHERE username = $1 OR email = $2", array($username, $email));
        
        if (pg_num_rows($check) > 0) {
            $error = "Username or Email already exists.";
        } else {
            // 4. Create Admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin'; // Default role for new admins
            
            $sql = "INSERT INTO admins (username, email, password, role) VALUES ($1, $2, $3, $4)";
            $result = pg_query_params($conn, $sql, array($username, $email, $hashed_password, $role));

            if ($result) {
                $success = "Admin account created successfully! <a href='login.php'>Login here</a>";
            } else {
                $error = "Registration failed: " . pg_last_error($conn);
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
    <title>Admin Registration - SkyHigh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="register-header">
        <h3><i class="fas fa-user-plus text-primary"></i> Admin Registration</h3>
        <p class="text-muted">Enter the security key to create a new admin.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-bold text-danger">Security Key (Required)</label>
            <input type="password" name="secret_key" class="form-control border-danger" placeholder="Ask authorization code..." required>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold">Create Admin Account</button>
    </form>
    
    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
    </div>
</div>

</body>
</html>
