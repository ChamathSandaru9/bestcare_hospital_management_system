<?php
session_start();
require_once 'includes/connection.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../register.php");
    exit();
}

// Get form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$blood_group = $_POST['blood_group'];
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$emergency_contact = trim($_POST['emergency_contact']);
$address = trim($_POST['address']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

$role_id = 3;
$status = "Active";


// Server-side Validation


if (empty($first_name) || empty($last_name) || empty($gender) || empty($dob) ||
    empty($blood_group) || empty($phone) || empty($email) ||
    empty($emergency_contact) || empty($address) ||
    empty($username) || empty($password) || empty($confirm_password)) {

    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: ../register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email address.";
    header("Location: ../register.php");
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $_SESSION['error'] = "Phone number must contain exactly 10 digits.";
    header("Location: ../register.php");
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $emergency_contact)) {
    $_SESSION['error'] = "Emergency contact must contain exactly 10 digits.";
    header("Location: ../register.php");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: ../register.php");
    exit();
}

// Password Hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);


// Check Duplicate Username


$stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error'] = "Username already exists.";
    header("Location: ../register.php");
    exit();
}
mysqli_stmt_close($stmt);


// Check Duplicate Email


$stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email=?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error'] = "Email already exists.";
    header("Location: ../register.php");
    exit();
}
mysqli_stmt_close($stmt);


// Begin Transaction

mysqli_begin_transaction($conn);

try {

    // Insert into users table

    $stmt = mysqli_prepare($conn,
        "INSERT INTO users
        (role_id, username, email, password, status, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())");

    mysqli_stmt_bind_param(
        $stmt,
        "issss",
        $role_id,
        $username,
        $email,
        $hashed_password,
        $status
    );

    mysqli_stmt_execute($stmt);

    $user_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

    // Insert into patients table

    $stmt = mysqli_prepare($conn,
        "INSERT INTO patients
        (user_id, first_name, last_name, gender, dob, phone,
        email, address, blood_group, emergency_contact, created_at)

        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    mysqli_stmt_bind_param(
        $stmt,
        "isssssssss",
        $user_id,
        $first_name,
        $last_name,
        $gender,
        $dob,
        $phone,
        $email,
        $address,
        $blood_group,
        $emergency_contact
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    $_SESSION['success'] =
        "Registration completed successfully. Please login.";

    header("Location: ../login.php");
    exit();

    } catch (mysqli_sql_exception $e) {

    mysqli_rollback($conn);

    die("Registration database error: " . $e->getMessage());
}
?>