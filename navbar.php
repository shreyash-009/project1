<?php
// navbar.php - Common navigation bar for all pages
// Role-based navbar - shows different options based on user role

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and get role
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Determine path prefix based on current directory
$pathPrefix = (strpos($_SERVER['PHP_SELF'], '/blood/') !== false || strpos($_SERVER['PHP_SELF'], '/disease/') !== false || strpos($_SERVER['PHP_SELF'], '/waste game/') !== false) ? '../' : '';
?>
<header>
    <nav class="navbar">
        <div class="logo-container">
            <a href="<?php echo $pathPrefix; ?>homepage.php" class="logo-link">
                <img src="<?php echo $pathPrefix; ?>images.png" alt="Logo" class="logo-img">
                <span>HealthCare</span>
            </a>
        </div>
        <ul class="nav-links">
            <!-- Home - Available to all -->
            <li><a href="<?php echo $pathPrefix; ?>homepage.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'homepage.php') ? 'class="active"' : ''; ?>>Home</a></li>
            
            <!-- Show role-based options only when logged in -->
            <?php if($isLoggedIn): ?>
                <!-- Regular User Options -->
                <?php if($userRole === 'user'): ?>
                    <!-- Donor Dashboard -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/donor_dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'donor_dashboard.php') ? 'class="active"' : ''; ?>>Dashboard</a></li>
                    
                    <!-- Blood Donation - For users -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/index.php" <?php echo (strpos($_SERVER['PHP_SELF'], 'blood') !== false && strpos($_SERVER['PHP_SELF'], 'hospital') === false && basename($_SERVER['PHP_SELF']) !== 'map.php' && basename($_SERVER['PHP_SELF']) !== 'emergency_requests.php' && basename($_SERVER['PHP_SELF']) !== 'donor_dashboard.php') ? 'class="active"' : ''; ?>>Blood Donation</a></li>
                    
                    <!-- Emergency Requests - For users -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/emergency_requests.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'emergency_requests.php') ? 'class="active"' : ''; ?>>Emergency</a></li>
                    
                    <!-- Waste Management - For users -->
                    <li><a href="<?php echo $pathPrefix; ?>waste.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'waste.php') ? 'class="active"' : ''; ?>>Waste Management</a></li>
                    
                    <!-- Disease Awareness - For users -->
                    <li><a href="<?php echo $pathPrefix; ?>disease.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'disease.php') ? 'class="active"' : ''; ?>>Disease Awareness</a></li>
                    
                    <!-- Map for donor registration -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/map.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'map.php') ? 'class="active"' : ''; ?>>Map</a></li>
                <?php endif; ?>
                
                <!-- Hospital User Options -->
                <?php if($userRole === 'hospital'): ?>
                    <!-- Hospital Register -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/hospital_register.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'hospital_register.php') ? 'class="active"' : ''; ?>>Hospital Register</a></li>
                    
                    <!-- Search Donor -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/search.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'search.php') ? 'class="active"' : ''; ?>>Search Donor</a></li>
                    
                    <!-- Emergency -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/emergency_requests.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'emergency_requests.php') ? 'class="active"' : ''; ?>>Emergency</a></li>
                    
                    <!-- Map -->
                    <li><a href="<?php echo $pathPrefix; ?>blood/map.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'map.php') ? 'class="active"' : ''; ?>>Map</a></li>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- About Us - Available to all -->
            <li><a href="<?php echo $pathPrefix; ?>aboutus.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'aboutus.php') ? 'class="active"' : ''; ?>>About Us</a></li>
            
            <!-- Login/Logout -->
            <?php if($isLoggedIn): ?>
                <li><a href="<?php echo $pathPrefix; ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li class="login-btn">
                    <a href="<?php echo $pathPrefix; ?>login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
