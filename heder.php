<?php
if (!isset($pageTitle)) {
    $pageTitle = "BestCare Hospital";
}

/* Detect Current Page */

$currentPage = basename($_SERVER['PHP_SELF']);

if ($currentPage == "index.php") {
    $homeLink        = "#home";
    $aboutLink       = "#about";
    $servicesLink    = "#services";
    $departmentLink  = "#departments";
    $emergencyLink   = "#emergency";
    $contactLink     = "#contact";
} else {
    $homeLink        = "index.php#home";
    $aboutLink       = "index.php#about";
    $servicesLink    = "index.php#services";
    $departmentLink  = "index.php#departments";
    $emergencyLink   = "index.php#emergency";
    $contactLink     = "index.php#contact";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="image/logo.png">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Header CSS -->
    <link rel="stylesheet" href="css/heder.css">
    <link rel="stylesheet" href="css/footer.css">

</head>

<body>

<!-- Top Bar -->

<div class="top-bar">

    <div class="top-container">

        <div class="top-left">

            <span>
                <i class="fa-solid fa-phone"></i>
                +94 41 222 1234
            </span>

            <span>
                <i class="fa-solid fa-envelope"></i>
                info@bestcarehospital.lk
            </span>

        </div>

        <div class="top-right">

            <span>
                <i class="fa-solid fa-clock"></i>
                Open 24 Hours
            </span>

            <span>
                <i class="fa-solid fa-location-dot"></i>
                Matara, Sri Lanka
            </span>

        </div>

    </div>

</div>

<!-- Navigation -->

<header>

<nav class="navbar">

    <!-- Logo -->

    <a href="index.php" class="logo">

        <img src="image/logo.png" alt="BestCare Hospital">

        <div class="logo-text">

            <h2>BestCare Hospital</h2>

            <span>Compassion • Excellence • Trust</span>

        </div>

    </a>

    <!-- Navigation -->

    <ul class="nav-links">

        <li><a href="<?php echo $homeLink; ?>">Home</a></li>

        <li><a href="<?php echo $aboutLink; ?>">About</a></li>

        <li><a href="<?php echo $servicesLink; ?>">Services</a></li>

        <li><a href="<?php echo $departmentLink; ?>">Departments</a></li>

        <li><a href="<?php echo $emergencyLink; ?>">Emergency</a></li>

        <li><a href="<?php echo $contactLink; ?>">Contact</a></li>

    </ul>

    <!-- Buttons -->

    <div class="nav-buttons">

        <a href="login.php" class="btn-login">

            <i class="fa-solid fa-right-to-bracket"></i>

            Login

        </a>

        <a href="register.php" class="btn-register">

            <i class="fa-solid fa-user-plus"></i>

            Register

        </a>

    </div>

    <!-- Mobile Menu -->

    <div class="menu-toggle">

        <i class="fa-solid fa-bars"></i>

    </div>

</nav>

</header>
