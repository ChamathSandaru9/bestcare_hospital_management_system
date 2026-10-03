<?php
session_start();

require_once "includes/connection.php";


//  Allow POST requests only


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php#contact");
    exit();
}

//  Get and clean form data


$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

//  Required field validation

if (
    $name === "" ||
    $email === "" ||
    $subject === "" ||
    $message === ""
) {
    $_SESSION["contact_error"] = "Please complete all required fields.";
    header("Location: ../index.php#contact");
    exit();
}

//  Name validation


if (!preg_match("/^[A-Za-z\s.'-]{2,100}$/", $name)) {
    $_SESSION["contact_error"] = "Please enter a valid full name.";
    header("Location: ../index.php#contact");
    exit();
}

// Email validation


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["contact_error"] = "Please enter a valid email address.";
    header("Location: ../index.php#contact");
    exit();
}


// Length validation


if (strlen($subject) < 3 || strlen($subject) > 150) {
    $_SESSION["contact_error"] =
        "The subject must contain between 3 and 150 characters.";

    header("Location: ../index.php#contact");
    exit();
}

if (strlen($message) < 10 || strlen($message) > 1000) {
    $_SESSION["contact_error"] =
        "The message must contain between 10 and 1000 characters.";

    header("Location: ../index.php#contact");
    exit();
}

//  Insert query using a prepared statement


$sql = "
    INSERT INTO contact_queries
    (name, email, subject, message, status, created_at)
    VALUES (?, ?, ?, ?, 'Pending', NOW())
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION["contact_error"] =
        "Unable to process your query at the moment.";

    header("Location: ../index.php#contact");
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $name,
    $email,
    $subject,
    $message
);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION["contact_success"] =
        "Your query was submitted successfully. BestCare Hospital will respond as soon as possible.";

} else {

    $_SESSION["contact_error"] =
        "Your query could not be submitted. Please try again.";

}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: ../index.php#contact");
exit();
?>