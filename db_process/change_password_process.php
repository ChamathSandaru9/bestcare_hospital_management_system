<?php
session_start();

require_once "../db_process/includes/connection.php";

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
    $_SESSION["password_error"] =
        "Invalid request. Please refresh the page and try again.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Get Form Data

$currentPassword = $_POST["current_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

//    Required Field Validation

if (
    $currentPassword === "" ||
    $newPassword === "" ||
    $confirmPassword === ""
) {
    $_SESSION["password_error"] =
        "Please complete all password fields.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Password Confirmation

if ($newPassword !== $confirmPassword) {
    $_SESSION["password_error"] =
        "New password and confirmation do not match.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Password Strength Validation

if (strlen($newPassword) < 8) {
    $_SESSION["password_error"] =
        "New password must contain at least 8 characters.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

if (!preg_match("/[A-Z]/", $newPassword)) {
    $_SESSION["password_error"] =
        "New password must contain at least one uppercase letter.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

if (!preg_match("/[0-9]/", $newPassword)) {
    $_SESSION["password_error"] =
        "New password must contain at least one number.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    New Password Must Be Different

if ($currentPassword === $newPassword) {
    $_SESSION["password_error"] =
        "New password must be different from your current password.";

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Load Current Password Hash

$sql = "
    SELECT password
    FROM users
    WHERE user_id = ?
      AND role_id = 3
      AND status = 'Active'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    $_SESSION["password_error"] =
        "Your account could not be found or is inactive.";

    mysqli_close($conn);

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Verify Current Password

if (!password_verify($currentPassword, $user["password"])) {
    $_SESSION["password_error"] =
        "Your current password is incorrect.";

    mysqli_close($conn);

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}


//    Prevent Reusing Current Password 

if (password_verify($newPassword, $user["password"])) {
    $_SESSION["password_error"] =
        "New password must be different from your current password.";

    mysqli_close($conn);

    header("Location: ../patient_dashboard.php#profileSetting");
    exit();
}

//    Hash and Update Password
 
$newPasswordHash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

try {

    $updateSql = "
        UPDATE users
        SET password = ?
        WHERE user_id = ?
          AND role_id = 3
          AND status = 'Active'
    ";

    $updateStmt = mysqli_prepare($conn, $updateSql);

    mysqli_stmt_bind_param(
        $updateStmt,
        "si",
        $newPasswordHash,
        $userId
    );

    mysqli_stmt_execute($updateStmt);

    if (mysqli_stmt_affected_rows($updateStmt) !== 1) {
        throw new mysqli_sql_exception(
            "Password update did not affect the expected account."
        );
    }

    mysqli_stmt_close($updateStmt);

    
    //  Regenerate Session ID
    
    session_regenerate_id(true);

    //  Generate a New CSRF Token
    
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    $_SESSION["password_success"] =
        "Your password was changed successfully.";

} catch (mysqli_sql_exception $e) {

    error_log(
        "Patient password update error: " .
        $e->getMessage()
    );

    $_SESSION["password_error"] =
        "Your password could not be changed. Please try again.";
}

mysqli_close($conn);

header("Location: ../patient_dashboard.php#profileSetting");
exit();
?>