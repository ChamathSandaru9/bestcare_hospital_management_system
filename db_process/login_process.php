<?php
session_start();

require_once("includes/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    // Check empty fields
    if (empty($login) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../login.php");
        exit();
    }

    // Search by username OR email
    $sql = "SELECT * FROM users
            WHERE username = ? OR email = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ss", $login, $login);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Check account status
        if ($user['status'] != "Active") {

            $_SESSION['error'] = "Your account is inactive.";

            header("Location: ../login.php");

            exit();

        }

        // Verify password
        if (password_verify($password, $user['password'])) {

            // Create Session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['username'] = $user['username'];

            // Redirect according to role

            switch ($user['role_id']) {

                case 1:
                    header("Location: ../admin_appointments.php");
                    break;

                case 2:
                    header("Location: ../doctor_dashboard.php");
                    break;

                case 3:
                    header("Location: ../patient_dashboard.php");
                    break;

                default:

                    $_SESSION['error'] = "Invalid user role.";

                    header("Location: ../login.php");

                    break;
            }

            exit();

        } else {

            $_SESSION['error'] = "Incorrect password.";

            header("Location: ../login.php");

            exit();

        }

    } else {

        $_SESSION['error'] = "Username or email not found.";

        header("Location: ../login.php");

        exit();

    }

} else {

    header("Location: ../login.php");

    exit();

}
?>