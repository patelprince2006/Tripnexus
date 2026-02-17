    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fa-solid fa-plane-departure me-2"></i>Admin Panel</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="<?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            </li>
            <li class="<?php echo ($active_page == 'users') ? 'active' : ''; ?>">
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li class="<?php echo ($active_page == 'flights') ? 'active' : ''; ?>">
                <a href="flights.php"><i class="fas fa-plane"></i> Flights</a>
            </li>
            <li class="<?php echo ($active_page == 'hotels') ? 'active' : ''; ?>">
                <a href="hotels.php"><i class="fas fa-hotel"></i> Hotels</a>
            </li>
            <li class="<?php echo ($active_page == 'tours') ? 'active' : ''; ?>">
                <a href="tours.php"><i class="fas fa-map-marked-alt"></i> Tours</a>
            </li>
            <li class="<?php echo ($active_page == 'bookings') ? 'active' : ''; ?>">
                <a href="bookings.php"><i class="fas fa-bookmark"></i> Bookings</a>
            </li>
            <li class="<?php echo ($active_page == 'payments') ? 'active' : ''; ?>">
                <a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
            </li>
            <li class="<?php echo ($active_page == 'reviews') ? 'active' : ''; ?>">
                <a href="reviews.php"><i class="fas fa-star"></i> Reviews</a>
            </li>
            <li class="<?php echo ($active_page == 'notifications') ? 'active' : ''; ?>">
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
            </li>
            <li>
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light rounded shadow-sm mb-4">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-dark">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-brand ms-3">Welcome, Admin</span>
                
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
