<?php
session_start();

$pageTitle = "Patient Registration | BestCare Hospital";
include "heder.php";
?>

<link rel="stylesheet" href="css/register.css">

<main class="register-page">

    <div class="register-container">

        <div class="register-box">

            <div class="register-header">
                <img src="image/logo.png"
                     class="register-logo"
                     alt="BestCare Hospital Logo">

                <h1>BestCare Hospital</h1>
                <h3>Patient Registration</h3>
            </div>

            <?php if (isset($_SESSION["success"])): ?>
                <div class="success-message">
                    <?php
                    echo htmlspecialchars($_SESSION["success"]);
                    unset($_SESSION["success"]);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION["error"])): ?>
                <div class="error-message">
                    <?php
                    echo htmlspecialchars($_SESSION["error"]);
                    unset($_SESSION["error"]);
                    ?>
                </div>
            <?php endif; ?>

            <form id="registerForm"
                  class="register-form"
                  action="db_process/register_process.php"
                  method="POST"
                  novalidate>

                <h2>Personal Information</h2>

                <div class="form-row">

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text"
                               id="first_name"
                               name="first_name"
                               maxlength="50"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text"
                               id="last_name"
                               name="last_name"
                               maxlength="50"
                               required>
                    </div>

                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="gender">Gender</label>

                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date"
                               id="dob"
                               name="dob"
                               required>
                    </div>

                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>

                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               maxlength="15"
                               inputmode="numeric"
                               required>
                    </div>

                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               maxlength="100"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact</label>
                        <input type="tel"
                               id="emergency_contact"
                               name="emergency_contact"
                               maxlength="15"
                               inputmode="numeric"
                               required>
                    </div>

                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address"
                              name="address"
                              rows="4"
                              required></textarea>
                </div>

                <h2>Login Information</h2>

                <div class="form-row">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               maxlength="100"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               required>
                    </div>

                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           required>
                </div>

                <div class="button-group">
                    <button type="submit">Register</button>
                    <button type="reset">Reset</button>
                </div>

                <p class="login-link">
                    Already have an account?
                    <a href="login.php">Login Here</a>
                </p>

            </form>

        </div>

    </div>

</main>

<script src="js/register.js"></script>

<?php include "footer.php"; ?>

