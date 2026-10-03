<?php
session_start();
include "heder.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BestCare Hospital</title>
    <link rel="icon" type="image/png" href="image/logo.png">
    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="container">
    <div class="login-card">

        <!-- Header -->

        <div class="card-header">

            <img src="image/logo.png" alt="BestCare Hospital Logo" class="login-logo">

            <h2>BestCare Hospital</h2>

            <p>User Login</p>

        </div>

        <!-- Body -->

        <div class="card-body">

            <?php

            if(isset($_SESSION['success']))
            {
                echo "<div class='success-message'>".$_SESSION['success']."</div>";
                unset($_SESSION['success']);
            }

            if(isset($_SESSION['error']))
            {
                echo "<div class='error-message'>".$_SESSION['error']."</div>";
                unset($_SESSION['error']);
            }

            ?>

            <h3>User Login</h3>

            <form action="db_process/login_process.php" method="POST" id="loginForm">

                <!-- Username -->

                <label>

                    Username or Email

                </label>

                <input
                    type="text"
                    name="login"
                    placeholder="Enter Username or Email"
                    required>

                <!-- Password -->

                <label>

                    Password

                </label>

                <div class="password-box">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter Password"
                        required>

                    <span id="togglePassword">

                        <i class="fa-solid fa-eye"></i>

                    </span>

                </div>

                <div class="options">

                    <label class="remember">

                        <input type="checkbox">

                        Remember Me

                    </label>

                    <a href="#">Forgot Password?</a>

                </div>

                <button type="submit" class="login-btn">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </button>

            </form>

            <div class="register-link">

                Don't have a patient account?

                <a href="register.php">

                    Register Here

                </a>

            </div>

        </div>

    </div>

</div>

<script src="js\login.js"></script>
<?php
include "footer.php";
?>
