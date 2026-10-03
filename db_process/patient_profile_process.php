<?php
session_start();

require_once "includes/connection.php";

//    Enable MySQLi Exceptions

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//    Allow POST Requests Only

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Check Patient Authentication

if (!isset($_SESSION["user_id"], $_SESSION["role_id"])) {
    header("Location: ../login.php");
    exit();
}

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: ../login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

//    Validate CSRF Token

$submittedToken = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $submittedToken)
) {
    $_SESSION["profile_error"] =
        "Invalid request. Please refresh the page and try again.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Get Form Data

$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");
$emergencyContact = trim($_POST["emergency_contact"] ?? "");
$address = trim($_POST["address"] ?? "");

//    Required Field Validation

if (
    $phone === "" ||
    $email === "" ||
    $emergencyContact === "" ||
    $address === ""
) {
    $_SESSION["profile_error"] =
        "Please fill in all required fields.";

    header("Location: ../patient_dashboard.php#profileSettings");
    exit();
}

//    Phone Validation

if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
    $_SESSION["profile_error"] =
        "Phone number must contain between 10 and 15 digits.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Email Validation

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["profile_error"] =
        "Please enter a valid email address.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Emergency Contact Validation

if (!preg_match("/^[0-9]{10,15}$/", $emergencyContact)) {
    $_SESSION["profile_error"] =
        "Emergency contact must contain between 10 and 15 digits.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Address Validation

$addressLength = mb_strlen($address);

if ($addressLength < 5 || $addressLength > 500) {
    $_SESSION["profile_error"] =
        "Address must contain between 5 and 500 characters.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Check Duplicate Email

$duplicateSql = "
    SELECT user_id
    FROM users
    WHERE email = ?
      AND user_id != ?
    LIMIT 1
";

$duplicateStmt = mysqli_prepare($conn, $duplicateSql);

mysqli_stmt_bind_param(
    $duplicateStmt,
    "si",
    $email,
    $userId
);

mysqli_stmt_execute($duplicateStmt);

$duplicateResult =
    mysqli_stmt_get_result($duplicateStmt);

if (mysqli_fetch_assoc($duplicateResult)) {
    mysqli_stmt_close($duplicateStmt);

    $_SESSION["profile_error"] =
        "This email address is already used by another account.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

mysqli_stmt_close($duplicateStmt);

//    Update Profile Using Transaction

mysqli_begin_transaction($conn);

try {

    //  Update users table 

    $userSql = "
        UPDATE users
        SET email = ?
        WHERE user_id = ?
    ";

    $userStmt = mysqli_prepare($conn, $userSql);

    mysqli_stmt_bind_param(
        $userStmt,
        "si",
        $email,
        $userId
    );

    mysqli_stmt_execute($userStmt);
    mysqli_stmt_close($userStmt);

    //  Update patients table 

    $patientSql = "
        UPDATE patients
        SET
            phone = ?,
            email = ?,
            emergency_contact = ?,
            address = ?
        WHERE user_id = ?
    ";

    $patientStmt = mysqli_prepare($conn, $patientSql);

    mysqli_stmt_bind_param(
        $patientStmt,
        "ssssi",
        $phone,
        $email,
        $emergencyContact,
        $address,
        $userId
    );

    mysqli_stmt_execute($patientStmt);

    if (mysqli_stmt_affected_rows($patientStmt) < 0) {
        throw new mysqli_sql_exception(
            "Patient profile update failed."
        );
    }

    mysqli_stmt_close($patientStmt);

    mysqli_commit($conn);

    $_SESSION["profile_success"] =
        "Your profile was updated successfully.";

} catch (mysqli_sql_exception $e) {

    mysqli_rollback($conn);

    error_log(
        "Patient profile update error: " .
        $e->getMessage()
    );

    $_SESSION["profile_error"] =
        "Your profile could not be updated. Please try again.";
}

mysqli_close($conn);

header("Location: ../patient_dashboard.php#profileSetting");
exit();
?>