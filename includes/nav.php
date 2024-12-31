<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-birthday-cake me-2"></i>
            Birthday Cards
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'templates.php' ? 'active' : ''; ?>" href="templates.php">
                        <i class="fas fa-images me-1"></i> Templates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'email_tracking.php' ? 'active' : ''; ?>" href="email_tracking.php">
                        <i class="fas fa-chart-line me-1"></i> Email Tracking
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
