<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%;">
            <h3 class="text-center fw-bold mb-4">Create New Password</h3>
            
            <form method="POST" action="update_password.php" id="resetPasswordForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="password"
                        placeholder="Enter a strong password" minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':&quot;\\|,.<>\/?~`]).{8,}"
                        title="Min 8 chars, 1 uppercase, 1 lowercase, 1 special character"
                        required>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        Min 8 chars • Uppercase • Lowercase • Special character (!@#$%^&*)
                    </small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmNewPassword" name="confirm_password"
                        placeholder="Re-enter your password" minlength="8" required>
                    <div id="resetPasswordError" class="text-danger small mt-1"></div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-bold">Update Password</button>
            </form>
        </div>
    </div>

    <script src="../public/script.js"></script>
</body>
</html>