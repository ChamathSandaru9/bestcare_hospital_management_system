<?php
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

<!-- Footer -->

<footer class="footer">

    <div class="footer-container">

        <!-- Hospital Information -->

        <div class="footer-column">

            <div class="footer-logo">

                <img src="image/logo.png" alt="BestCare Hospital Logo">

                <div>

                    <h2>BestCare Hospital</h2>

                    <p>Compassion • Excellence • Trust</p>

                </div>

            </div>

            <p class="footer-description">

                BestCare Hospital is committed to providing quality healthcare
                with experienced medical professionals, modern facilities,
                and compassionate patient care.

            </p>

        </div>

        <!-- Quick Links -->

        <div class="footer-column">

            <h3>Quick Links</h3>

            <ul>

                <li><a href="<?php echo $homeLink; ?>">Home</a></li>

                <li><a href="<?php echo $aboutLink; ?>">About Us</a></li>

                <li><a href="<?php echo $servicesLink; ?>">Services</a></li>

                <li><a href="<?php echo $departmentLink; ?>">Departments</a></li>

                <li><a href="<?php echo $emergencyLink; ?>">Emergency</a></li>

                <li><a href="<?php echo $contactLink; ?>">Contact</a></li>

            </ul>

        </div>

        <!-- Contact Information -->

        <div class="footer-column">

            <h3>Contact Us</h3>

            <ul class="contact-info">

                <li>

                    <i class="fa-solid fa-location-dot"></i>

                    Matara, Sri Lanka

                </li>

                <li>

                    <i class="fa-solid fa-phone"></i>

                    +94 41 222 1234

                </li>

                <li>

                    <i class="fa-solid fa-envelope"></i>

                    info@bestcarehospital.lk

                </li>

                <li>

                    <i class="fa-solid fa-clock"></i>

                    Open 24 Hours

                </li>

            </ul>

        </div>

        <!-- Social Media -->

        <div class="footer-column">

            <h3>Follow Us</h3>

            <div class="social-icons">

                <a href="#"><i class="fab fa-facebook-f"></i></a>

                <a href="#"><i class="fab fa-instagram"></i></a>

                <a href="#"><i class="fab fa-x-twitter"></i></a>

                <a href="#"><i class="fab fa-linkedin-in"></i></a>

            </div>

            <div class="footer-buttons">

                <a href="login.php" class="footer-login">

                    Login

                </a>

                <a href="register.php" class="footer-register">

                    Register

                </a>

            </div>

        </div>

    </div>

    <!-- Bottom Footer -->

    <div class="footer-bottom">

        <p>

            &copy; <?php echo date("Y"); ?>

            BestCare Hospital Management System.

            All Rights Reserved.

        </p>

    </div>

</footer>
<script src="js/heder.js"></script>

</body>
</html>