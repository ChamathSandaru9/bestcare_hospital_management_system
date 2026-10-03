<?php
session_start();

//    Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "includes/connection.php";

//    Redirect Helper

function redirectToAppointments(): void
{
    header("Location: ../patient_appointments.php");
    exit();
}

//    Allow POST Requests Only

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectToAppointments();
}

//    Patient Authentication

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: ../login.php");
    exit();
}


//  Final roles 3 = Patient

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: ../login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

//    Validate CSRF Token

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
    $_SESSION["appointment_error"] =
        "Invalid request. Please refresh the page and try again.";

    redirectToAppointments();
}

//    Load Patient ID

$patientSql = "
    SELECT patient_id
    FROM patients
    WHERE user_id = ?
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
    $_SESSION["appointment_error"] =
        "Your patient profile could not be found.";

    mysqli_close($conn);
    redirectToAppointments();
}

$patientId = (int) $patient["patient_id"];


//    Determine Requested Action


$action =
    trim($_POST["action"] ?? "");

if ($action === "book") {

    //    Book Appointment
    
    $departmentId =
        filter_input(
            INPUT_POST,
            "department_id",
            FILTER_VALIDATE_INT
        );

    $doctorId =
        filter_input(
            INPUT_POST,
            "doctor_id",
            FILTER_VALIDATE_INT
        );

    $appointmentDate =
        trim($_POST["appointment_date"] ?? "");

    $appointmentTime =
        trim($_POST["appointment_time"] ?? "");

    //    Required Field Validation

    if (
        !$departmentId ||
        !$doctorId ||
        $appointmentDate === "" ||
        $appointmentTime === ""
    ) {
        $_SESSION["appointment_error"] =
            "Please complete all appointment fields.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Date Format Validation

    $dateObject =
        DateTime::createFromFormat(
            "Y-m-d",
            $appointmentDate
        );

    $dateIsValid =
        $dateObject &&
        $dateObject->format("Y-m-d") ===
        $appointmentDate;

    if (!$dateIsValid) {
        $_SESSION["appointment_error"] =
            "Please select a valid appointment date.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Time Format Validation

    $timeObject =
        DateTime::createFromFormat(
            "H:i",
            $appointmentTime
        );

    $timeIsValid =
        $timeObject &&
        $timeObject->format("H:i") ===
        $appointmentTime;

    if (!$timeIsValid) {
        $_SESSION["appointment_error"] =
            "Please select a valid appointment time.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Prevent Past Date or Time

    $appointmentDateTime =
        DateTime::createFromFormat(
            "Y-m-d H:i",
            $appointmentDate .
            " " .
            $appointmentTime
        );

    $currentDateTime =
        new DateTime();

    if (
        !$appointmentDateTime ||
        $appointmentDateTime <= $currentDateTime
    ) {
        $_SESSION["appointment_error"] =
            "Please select a future appointment date and time.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Hospital Time Validation

    if (
        $appointmentTime < "08:00" ||
        $appointmentTime > "20:00"
    ) {
        $_SESSION["appointment_error"] =
            "Appointments are available only between 8:00 AM and 8:00 PM.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Validate Doctor and Department

    $doctorSql = "
        SELECT d.doctor_id
        FROM doctors d
        INNER JOIN users u
            ON d.user_id = u.user_id
        INNER JOIN departments dep
            ON d.department_id = dep.department_id
        WHERE d.doctor_id = ?
          AND d.department_id = ?
          AND u.status = 'Active'
          AND dep.status = 'Active'
        LIMIT 1
    ";

    $doctorStmt =
        mysqli_prepare($conn, $doctorSql);

    mysqli_stmt_bind_param(
        $doctorStmt,
        "ii",
        $doctorId,
        $departmentId
    );

    mysqli_stmt_execute($doctorStmt);

    $doctorResult =
        mysqli_stmt_get_result($doctorStmt);

    $doctor =
        mysqli_fetch_assoc($doctorResult);

    mysqli_stmt_close($doctorStmt);

    if (!$doctor) {
        $_SESSION["appointment_error"] =
            "The selected doctor is not available in that department.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Prevent Doctor Time-Slot Conflict

    $doctorConflictSql = "
        SELECT appointment_id
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND appointment_time = ?
          AND status IN ('Pending', 'Approved')
        LIMIT 1
    ";

    $doctorConflictStmt =
        mysqli_prepare(
            $conn,
            $doctorConflictSql
        );

    mysqli_stmt_bind_param(
        $doctorConflictStmt,
        "iss",
        $doctorId,
        $appointmentDate,
        $appointmentTime
    );

    mysqli_stmt_execute(
        $doctorConflictStmt
    );

    $doctorConflictResult =
        mysqli_stmt_get_result(
            $doctorConflictStmt
        );

    $doctorConflict =
        mysqli_fetch_assoc(
            $doctorConflictResult
        );

    mysqli_stmt_close(
        $doctorConflictStmt
    );

    if ($doctorConflict) {
        $_SESSION["appointment_error"] =
            "This doctor already has an appointment at the selected date and time.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Prevent Patient Time-Slot Conflict

    $patientConflictSql = "
        SELECT appointment_id
        FROM appointments
        WHERE patient_id = ?
          AND appointment_date = ?
          AND appointment_time = ?
          AND status IN ('Pending', 'Approved')
        LIMIT 1
    ";

    $patientConflictStmt =
        mysqli_prepare(
            $conn,
            $patientConflictSql
        );

    mysqli_stmt_bind_param(
        $patientConflictStmt,
        "iss",
        $patientId,
        $appointmentDate,
        $appointmentTime
    );

    mysqli_stmt_execute(
        $patientConflictStmt
    );

    $patientConflictResult =
        mysqli_stmt_get_result(
            $patientConflictStmt
        );

    $patientConflict =
        mysqli_fetch_assoc(
            $patientConflictResult
        );

    mysqli_stmt_close(
        $patientConflictStmt
    );

    if ($patientConflict) {
        $_SESSION["appointment_error"] =
            "You already have another appointment at the selected date and time.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    //    Insert Appointment

    try {

        $insertSql = "
            INSERT INTO appointments
            (
                patient_id,
                doctor_id,
                appointment_date,
                appointment_time,
                status,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'Pending',
                NOW()
            )
        ";

        $insertStmt =
            mysqli_prepare(
                $conn,
                $insertSql
            );

        mysqli_stmt_bind_param(
            $insertStmt,
            "iiss",
            $patientId,
            $doctorId,
            $appointmentDate,
            $appointmentTime
        );

        mysqli_stmt_execute($insertStmt);

        mysqli_stmt_close($insertStmt);

        $_SESSION["appointment_success"] =
            "Your appointment request was submitted successfully.";

        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));

    } catch (mysqli_sql_exception $e) {

        error_log(
            "Appointment booking error: " .
            $e->getMessage()
        );

        $_SESSION["appointment_error"] =
            "Your appointment could not be booked. Please try again.";
    }

    mysqli_close($conn);
    redirectToAppointments();

} elseif ($action === "cancel") {

    //    Cancel Appointment

    $appointmentId =
        filter_input(
            INPUT_POST,
            "appointment_id",
            FILTER_VALIDATE_INT
        );

    if (!$appointmentId) {
        $_SESSION["appointment_error"] =
            "Invalid appointment selected.";

        mysqli_close($conn);
        redirectToAppointments();
    }

    try {

        // Only the logged-in patient may cancel their own pending appointment.

        $cancelSql = "
            UPDATE appointments
            SET status = 'Cancelled'
            WHERE appointment_id = ?
              AND patient_id = ?
              AND status = 'Pending'
        ";

        $cancelStmt =
            mysqli_prepare(
                $conn,
                $cancelSql
            );

        mysqli_stmt_bind_param(
            $cancelStmt,
            "ii",
            $appointmentId,
            $patientId
        );

        mysqli_stmt_execute($cancelStmt);

        $affectedRows =
            mysqli_stmt_affected_rows(
                $cancelStmt
            );

        mysqli_stmt_close($cancelStmt);

        if ($affectedRows !== 1) {
            $_SESSION["appointment_error"] =
                "This appointment cannot be cancelled. It may already be approved, completed, cancelled, or belong to another patient.";
        } else {
            $_SESSION["appointment_success"] =
                "Your appointment was cancelled successfully.";

            $_SESSION["csrf_token"] =
                bin2hex(random_bytes(32));
        }

    } catch (mysqli_sql_exception $e) {

        error_log(
            "Appointment cancellation error: " .
            $e->getMessage()
        );

        $_SESSION["appointment_error"] =
            "The appointment could not be cancelled. Please try again.";
    }

    mysqli_close($conn);
    redirectToAppointments();

} else {

    //    Unknown Action

    $_SESSION["appointment_error"] =
        "Invalid appointment action.";

    mysqli_close($conn);
    redirectToAppointments();
}
?>