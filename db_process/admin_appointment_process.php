<?php
session_start();

//  Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "includes/connection.php";

 // Redirect Helper

function redirectToAdminAppointments(): void
{
    header(
        "Location: ../admin_appointments.php"
    );

    exit();
}

// Message Helpers

function setAdminAppointmentError(
    string $message
): void {
    $_SESSION["admin_appointment_error"] =
        $message;
}

function setAdminAppointmentSuccess(
    string $message
): void {
    $_SESSION["admin_appointment_success"] =
        $message;
}

function refreshAdminCsrfToken(): void
{
    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );
}

// Allow POST Requests Only

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {
    redirectToAdminAppointments();
}

//  Admin Authentication

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: ../login.php");
    exit();
}

// Administrator Role = 1

if (
    (int) $_SESSION["role_id"] !== 1
) {
    header("Location: ../login.php");
    exit();
}

$adminUserId =
    (int) $_SESSION["user_id"];

// Validate CSRF Token

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
    setAdminAppointmentError(
        "Invalid request. Please refresh the page and try again."
    );

    mysqli_close($conn);

    redirectToAdminAppointments();
}

//  Read Requested Action

$action =
    trim($_POST["action"] ?? "");

$allowedActions = [
    "schedule",
    "reschedule",
    "cancel"
];

if (
    !in_array(
        $action,
        $allowedActions,
        true
    )
) {
    setAdminAppointmentError(
        "Invalid appointment action."
    );

    mysqli_close($conn);

    redirectToAdminAppointments();
}

//  Validate Appointment ID

$appointmentId =
    filter_input(
        INPUT_POST,
        "appointment_id",
        FILTER_VALIDATE_INT
    );

if (!$appointmentId) {
    setAdminAppointmentError(
        "Invalid appointment selected."
    );

    mysqli_close($conn);

    redirectToAdminAppointments();
}

//  Load Selected Appointment

$appointmentSql = "
    SELECT
        a.appointment_id,
        a.patient_id,
        a.doctor_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,
        p.first_name AS patient_first_name,
        p.last_name AS patient_last_name,
        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name
    FROM appointments a
    INNER JOIN patients p
        ON a.patient_id = p.patient_id
    INNER JOIN doctors d
        ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = ?
    LIMIT 1
";

$appointmentStmt =
    mysqli_prepare(
        $conn,
        $appointmentSql
    );

mysqli_stmt_bind_param(
    $appointmentStmt,
    "i",
    $appointmentId
);

mysqli_stmt_execute(
    $appointmentStmt
);

$appointmentResult =
    mysqli_stmt_get_result(
        $appointmentStmt
    );

$appointment =
    mysqli_fetch_assoc(
        $appointmentResult
    );

mysqli_stmt_close(
    $appointmentStmt
);

if (!$appointment) {
    setAdminAppointmentError(
        "The selected appointment could not be found."
    );

    mysqli_close($conn);

    redirectToAdminAppointments();
}

// Action Routing

if ($action === "schedule") {

// Get Schedule Data

    $appointmentDate =
        trim($_POST["appointment_date"] ?? "");

    $appointmentTime =
        trim($_POST["appointment_time"] ?? "");

    // Validate Date

    if ($appointmentDate === "") {

        setAdminAppointmentError(
            "Please select an appointment date."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    if (
        !preg_match(
            "/^\d{4}-\d{2}-\d{2}$/",
            $appointmentDate
        )
    ) {

        setAdminAppointmentError(
            "Invalid appointment date."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    if (
        strtotime($appointmentDate) <
        strtotime(date("Y-m-d"))
    ) {

        setAdminAppointmentError(
            "Appointment date cannot be in the past."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    // Validate Time

    if ($appointmentTime === "") {

        setAdminAppointmentError(
            "Please select an appointment time."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    // Check Appointment Status

    if (
        $appointment["status"] !== "Pending"
    ) {

        setAdminAppointmentError(
            "Only pending appointments can be scheduled."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    // Check Doctor Availability

    $conflictSql = "

        SELECT appointment_id

        FROM appointments

        WHERE doctor_id = ?

        AND appointment_date = ?

        AND appointment_time = ?

        AND status = 'Approved'

        LIMIT 1

    ";

    $conflictStmt =
        mysqli_prepare(
            $conn,
            $conflictSql
        );

    mysqli_stmt_bind_param(

        $conflictStmt,

        "iss",

        $appointment["doctor_id"],

        $appointmentDate,

        $appointmentTime

    );

    mysqli_stmt_execute(
        $conflictStmt
    );

    $conflictResult =
        mysqli_stmt_get_result(
            $conflictStmt
        );

    $conflict =
        mysqli_fetch_assoc(
            $conflictResult
        );

    mysqli_stmt_close(
        $conflictStmt
    );

    if ($conflict) {

        setAdminAppointmentError(
            "The selected doctor already has an appointment at that date and time."
        );

        mysqli_close($conn);

        redirectToAdminAppointments();

    }

    //  Schedule Appointment

    try {

        $updateSql = "
        UPDATE appointments
        SET
        appointment_date = ?,
        appointment_time = ?,
        status = 'Approved'
    WHERE appointment_id = ?
      AND status = 'Pending'
";


        $updateStmt =
            mysqli_prepare(
                $conn,
                $updateSql
            );

        mysqli_stmt_bind_param(

            $updateStmt,

            "ssi",

            $appointmentDate,

            $appointmentTime,

            $appointmentId

        );

        mysqli_stmt_execute(
            $updateStmt
        );

        mysqli_stmt_close(
            $updateStmt
        );

        refreshAdminCsrfToken();

        setAdminAppointmentSuccess(

            "Appointment scheduled successfully."

        );

    } catch (
        mysqli_sql_exception $e
    ) {

        error_log(
            "Schedule Appointment Error: " .
            $e->getMessage()
        );

        setAdminAppointmentError(

            "Unable to schedule the appointment."

        );

    }

    mysqli_close($conn);

    redirectToAdminAppointments();

    }elseif ($action === "reschedule") {

    //  Get Reschedule Data

    $appointmentDate =
        trim($_POST["appointment_date"] ?? "");

    $appointmentTime =
        trim($_POST["appointment_time"] ?? "");

    //  Validate Date and Time

    if (
        $appointmentDate === "" ||
        $appointmentTime === ""
    ) {
        setAdminAppointmentError(
            "Please select both the appointment date and time."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

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
        setAdminAppointmentError(
            "Please select a valid appointment date."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    $timeObject =
        DateTime::createFromFormat(
            "H:i",
            $appointmentTime
        );

    if (!$timeObject) {
        $timeObject =
            DateTime::createFromFormat(
                "H:i:s",
                $appointmentTime
            );
    }

    if (!$timeObject) {
        setAdminAppointmentError(
            "Please select a valid appointment time."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    $normalizedTime =
        $timeObject->format("H:i:s");

    $newAppointmentDateTime =
        DateTime::createFromFormat(
            "Y-m-d H:i:s",
            $appointmentDate .
            " " .
            $normalizedTime
        );

    if (
        !$newAppointmentDateTime ||
        $newAppointmentDateTime <=
            new DateTime()
    ) {
        setAdminAppointmentError(
            "Please choose a future appointment date and time."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    // Check Current Status

    if (
        $appointment["status"] !== "Approved"

    ) {
        setAdminAppointmentError(
            "Only approved appointments can be rescheduled."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    // Check Doctor Time Conflict

    $doctorConflictSql = "
        SELECT appointment_id
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND appointment_time = ?
          AND appointment_id != ?
          AND status = 'Approved'
        LIMIT 1
    ";

    $doctorConflictStmt =
        mysqli_prepare(
            $conn,
            $doctorConflictSql
        );

    mysqli_stmt_bind_param(
        $doctorConflictStmt,
        "issi",
        $appointment["doctor_id"],
        $appointmentDate,
        $normalizedTime,
        $appointmentId
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
        setAdminAppointmentError(
            "The doctor already has another appointment at the selected date and time."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    // Check Patient Time Conflict

    $patientConflictSql = "
        SELECT appointment_id
        FROM appointments
        WHERE patient_id = ?
          AND appointment_date = ?
          AND appointment_time = ?
          AND appointment_id != ?
          AND status = 'Approved'
        LIMIT 1
    ";

    $patientConflictStmt =
        mysqli_prepare(
            $conn,
            $patientConflictSql
        );

    mysqli_stmt_bind_param(
        $patientConflictStmt,
        "issi",
        $appointment["patient_id"],
        $appointmentDate,
        $normalizedTime,
        $appointmentId
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
        setAdminAppointmentError(
            "The patient already has another appointment at the selected date and time."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    //  Update Appointment

    try {

        $rescheduleSql = "
            UPDATE appointments
            SET
                appointment_date = ?,
                appointment_time = ?
            WHERE appointment_id = ?
              AND status = 'Approved'
        ";

        $rescheduleStmt =
            mysqli_prepare(
                $conn,
                $rescheduleSql
            );

        mysqli_stmt_bind_param(
            $rescheduleStmt,
            "ssi",
            $appointmentDate,
            $normalizedTime,
            $appointmentId
        );

        mysqli_stmt_execute(
            $rescheduleStmt
        );

        $affectedRows =
            mysqli_stmt_affected_rows(
                $rescheduleStmt
            );

        mysqli_stmt_close(
            $rescheduleStmt
        );

        if ($affectedRows !== 1) {
            throw new mysqli_sql_exception(
                "Appointment reschedule failed."
            );
        }

        refreshAdminCsrfToken();

        setAdminAppointmentSuccess(
            "Appointment rescheduled successfully."
        );

    } catch (mysqli_sql_exception $e) {

        error_log(
            "Admin appointment reschedule error: " .
            $e->getMessage()
        );

        setAdminAppointmentError(
            "Unable to reschedule the appointment. Please try again."
        );
    }

    mysqli_close($conn);

    redirectToAdminAppointments();
}

 elseif ($action === "cancel") {

    if (
        !in_array(
            $appointment["status"],
            [
                "Pending",
                "Approved"
            ],
            true
        )
    ) {
        setAdminAppointmentError(
            "Only pending or approved appointments can be cancelled."
        );

        mysqli_close($conn);
        redirectToAdminAppointments();
    }

    //  Cancel Appointment

    try {

        $cancelSql = "
            UPDATE appointments
            SET
                status = 'Cancelled'
            WHERE appointment_id = ?
              AND status IN (
                  'Pending',
                  'Approved'
              )
        ";

        $cancelStmt =
            mysqli_prepare(
                $conn,
                $cancelSql
            );

        mysqli_stmt_bind_param(
            $cancelStmt,
            "i",
            $appointmentId
        );

        mysqli_stmt_execute(
            $cancelStmt
        );

        $affectedRows =
            mysqli_stmt_affected_rows(
                $cancelStmt
            );

        mysqli_stmt_close(
            $cancelStmt
        );

        if ($affectedRows !== 1) {

            throw new mysqli_sql_exception(
                "Appointment cancellation failed."
            );

        }

        refreshAdminCsrfToken();

        setAdminAppointmentSuccess(
            "Appointment cancelled successfully."
        );

    } catch (mysqli_sql_exception $e) {

        error_log(
            "Admin appointment cancellation error: " .
            $e->getMessage()
        );

        setAdminAppointmentError(
            "Unable to cancel the appointment."
        );

    }

    mysqli_close($conn);

    redirectToAdminAppointments();
}

// Fallback

setAdminAppointmentError(
    "The requested action could not be completed."
);

mysqli_close($conn);

redirectToAdminAppointments();
