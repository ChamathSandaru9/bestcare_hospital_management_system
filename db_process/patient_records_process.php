<?php
session_start();

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "includes/connection.php";

function redirectToRecords(): void
{
    header(
        "Location: ../patient_records.php#patientQueries"
    );
    exit();
}

function setPatientQueryError(
    string $message
): void {
    $_SESSION["query_error"] = $message;
}

function setPatientQuerySuccess(
    string $message
): void {
    $_SESSION["query_success"] = $message;
}

function refreshPatientCsrfToken(): void
{
    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));
}

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {
    redirectToRecords();
}

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: ../login.php");
    exit();
}

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: ../login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

$submittedToken =
    $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    empty($submittedToken) ||
    !hash_equals(
        $_SESSION["csrf_token"],
        $submittedToken
    )
) {
    setPatientQueryError(
        "Invalid request. Please refresh the page and try again."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$action =
    trim($_POST["action"] ?? "");

if ($action !== "submit_query") {
    setPatientQueryError(
        "Invalid query action."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$doctorId =
    filter_input(
        INPUT_POST,
        "doctor_id",
        FILTER_VALIDATE_INT
    );

$subject =
    trim($_POST["subject"] ?? "");

$message =
    trim($_POST["message"] ?? "");

if (
    !$doctorId ||
    $subject === "" ||
    $message === ""
) {
    setPatientQueryError(
        "Please select a doctor and enter both the subject and message."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$subjectLength =
    mb_strlen($subject, "UTF-8");

if (
    $subjectLength < 3 ||
    $subjectLength > 150
) {
    setPatientQueryError(
        "Subject must contain between 3 and 150 characters."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$messageLength =
    mb_strlen($message, "UTF-8");

if (
    $messageLength < 10 ||
    $messageLength > 2000
) {
    setPatientQueryError(
        "Message must contain between 10 and 2000 characters."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$patientSql = "
    SELECT
        p.patient_id
    FROM patients p
    INNER JOIN users u
        ON p.user_id = u.user_id
    WHERE p.user_id = ?
      AND u.status = 'Active'
    LIMIT 1
";

$patientStmt =
    mysqli_prepare($conn, $patientSql);

mysqli_stmt_bind_param(
    $patientStmt,
    "i",
    $userId
);

mysqli_stmt_execute($patientStmt);

$patientResult =
    mysqli_stmt_get_result($patientStmt);

$patient =
    mysqli_fetch_assoc($patientResult);

mysqli_stmt_close($patientStmt);

if (!$patient) {
    setPatientQueryError(
        "Your active patient profile could not be found."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$patientId =
    (int) $patient["patient_id"];

$doctorSql = "
    SELECT
        d.doctor_id
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    WHERE d.doctor_id = ?
      AND u.status = 'Active'
    LIMIT 1
";

$doctorStmt =
    mysqli_prepare($conn, $doctorSql);

mysqli_stmt_bind_param(
    $doctorStmt,
    "i",
    $doctorId
);

mysqli_stmt_execute($doctorStmt);

$doctorResult =
    mysqli_stmt_get_result($doctorStmt);

$selectedDoctor =
    mysqli_fetch_assoc($doctorResult);

mysqli_stmt_close($doctorStmt);

if (!$selectedDoctor) {
    setPatientQueryError(
        "The selected doctor is not available."
    );

    mysqli_close($conn);
    redirectToRecords();
}

$duplicateSql = "
    SELECT
        query_id
    FROM patient_queries
    WHERE patient_id = ?
      AND doctor_id = ?
      AND subject = ?
      AND message = ?
      AND created_at >= DATE_SUB(
          NOW(),
          INTERVAL 1 MINUTE
      )
    LIMIT 1
";

$duplicateStmt =
    mysqli_prepare($conn, $duplicateSql);

mysqli_stmt_bind_param(
    $duplicateStmt,
    "iiss",
    $patientId,
    $doctorId,
    $subject,
    $message
);

mysqli_stmt_execute($duplicateStmt);

$duplicateResult =
    mysqli_stmt_get_result($duplicateStmt);

$duplicateQuery =
    mysqli_fetch_assoc($duplicateResult);

mysqli_stmt_close($duplicateStmt);

if ($duplicateQuery) {
    setPatientQueryError(
        "This query was already submitted recently."
    );

    mysqli_close($conn);
    redirectToRecords();
}

try {

    $insertSql = "
        INSERT INTO patient_queries
        (
            patient_id,
            doctor_id,
            subject,
            message,
            reply,
            status,
            created_at,
            replied_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NULL,
            'Pending',
            NOW(),
            NULL
        )
    ";

    $insertStmt =
        mysqli_prepare($conn, $insertSql);

    mysqli_stmt_bind_param(
        $insertStmt,
        "iiss",
        $patientId,
        $doctorId,
        $subject,
        $message
    );

    mysqli_stmt_execute($insertStmt);

    $affectedRows =
        mysqli_stmt_affected_rows(
            $insertStmt
        );

    mysqli_stmt_close($insertStmt);

    if ($affectedRows !== 1) {
        throw new mysqli_sql_exception(
            "Patient query insert failed."
        );
    }

    refreshPatientCsrfToken();

    setPatientQuerySuccess(
        "Your query was submitted successfully."
    );

} catch (mysqli_sql_exception $e) {

    error_log(
        "Patient query submission error: " .
        $e->getMessage()
    );

    setPatientQueryError(
        "Your query could not be submitted. Please try again."
    );
}

mysqli_close($conn);

redirectToRecords();