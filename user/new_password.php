<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | TripNexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%;">
            <h3 class="text-center fw-bold mb-4">Create New Password</h3>
            
            <form method="POST" action="update_password.php" id="resetPasswordForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control border-start-0 rounded-end-3" id="newPassword" name="password"
                            placeholder="Enter a strong password" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-check-circle-fill"></i></span>
                        <input type="password" class="form-control border-start-0 rounded-end-3" id="confirmNewPassword" name="confirm_password"
                            placeholder="Re-enter your password" required>
                    </div>
                    <div id="resetPasswordError" class="text-danger small mt-1"></div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">Update Password</button>
            </form>
        </div>
    </div>

    <script src="../public/script.js"></script>
</body>
</html>