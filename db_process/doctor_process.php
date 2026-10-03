<?php
session_start();

   //MySQLi Error Handling

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "includes/connection.php";

   //Redirect Helpers

function redirectToAppointments(): void
{
    header(
        "Location: ../doctor_appointments.php"
    );
    exit();
}

function redirectToPatient(
    int $patientId,
    string $tab = "medical"
): void {
    $allowedTabs = [
        "medical",
        "prescription",
        "lab",
        "queries"
    ];

    if (!in_array($tab, $allowedTabs, true)) {
        $tab = "medical";
    }

    header(
        "Location: ../doctor_patients.php" .
        "?patient_id=" . $patientId .
        "&tab=" . urlencode($tab)
    );

    exit();
}

function setDoctorError(
    string $message
): void {
    $_SESSION["doctor_error"] = $message;
}

function setDoctorSuccess(
    string $message
): void {
    $_SESSION["doctor_success"] = $message;
}

function refreshCsrfToken(): void
{
    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));
}

// Allow POST Requests Only

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectToAppointments();
}

// Doctor Authentication

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: ../login.php");
    exit();
}

// Doctor Roles = 2

if ((int) $_SESSION["role_id"] !== 2) {
    header("Location: ../login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

// CSRF Validation

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
    setDoctorError(
        "Invalid request. Please refresh the page and try again."
    );

    $patientId = filter_input(
        INPUT_POST,
        "patient_id",
        FILTER_VALIDATE_INT
    );

    if ($patientId) {
        redirectToPatient(
            (int) $patientId
        );
    }

    redirectToAppointments();
}

//  Load Logged-in Doctor

$doctorSql = "
    SELECT
        d.doctor_id,
        d.department_id,
        d.first_name,
        d.last_name,
        d.specialization,
        d.availability
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    WHERE d.user_id = ?
      AND u.status = 'Active'
    LIMIT 1
";

$doctorStmt =
    mysqli_prepare(
        $conn,
        $doctorSql
    );

mysqli_stmt_bind_param(
    $doctorStmt,
    "i",
    $userId
);

mysqli_stmt_execute(
    $doctorStmt
);

$doctorResult =
    mysqli_stmt_get_result(
        $doctorStmt
    );

$doctor =
    mysqli_fetch_assoc(
        $doctorResult
    );

mysqli_stmt_close(
    $doctorStmt
);

if (!$doctor) {
    mysqli_close($conn);

    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit();
}

$doctorId =
    (int) $doctor["doctor_id"];

//  Requested Action

$action =
    trim($_POST["action"] ?? "");

$allowedActions = [
    "approve_appointment",
    "complete_appointment",
    "cancel_appointment",
    "add_medical_record",
    "add_prescription",
    "upload_lab_report",
    "reply_patient_query"
];

if (
    !in_array(
        $action,
        $allowedActions,
        true
    )
) {
    setDoctorError(
        "Invalid doctor action."
    );

    mysqli_close($conn);
    redirectToAppointments();
}

// Appointment Actions

if (
    in_array(
        $action,
        [
            "approve_appointment",
            "complete_appointment",
            "cancel_appointment"
        ],
        true
    )
) {
    $appointmentId =
        filter_input(
            INPUT_POST,
            "appointment_id",
            FILTER_VALIDATE_INT
        );

    if (!$appointmentId) {
        setDoctorError(
            "Invalid appointment selected."
        );

        mysqli_close($conn);
        redirectToAppointments();
    }

    $appointmentSql = "
        SELECT
            appointment_id,
            patient_id,
            appointment_date,
            appointment_time,
            status
        FROM appointments
        WHERE appointment_id = ?
          AND doctor_id = ?
        LIMIT 1
    ";

    $appointmentStmt =
        mysqli_prepare(
            $conn,
            $appointmentSql
        );

    mysqli_stmt_bind_param(
        $appointmentStmt,
        "ii",
        $appointmentId,
        $doctorId
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
        setDoctorError(
            "This appointment was not found or does not belong to you."
        );

        mysqli_close($conn);
        redirectToAppointments();
    }

     // Approve Appointment

    if ($action === "approve_appointment") {

        if (
            $appointment["status"] !== "Pending"
        ) {
            setDoctorError(
                "Only pending appointments can be approved."
            );

            mysqli_close($conn);
            redirectToAppointments();
        }

        try {
            $approveSql = "
                UPDATE appointments
                SET status = 'Approved'
                WHERE appointment_id = ?
                  AND doctor_id = ?
                  AND status = 'Pending'
            ";

            $approveStmt =
                mysqli_prepare(
                    $conn,
                    $approveSql
                );

            mysqli_stmt_bind_param(
                $approveStmt,
                "ii",
                $appointmentId,
                $doctorId
            );

            mysqli_stmt_execute(
                $approveStmt
            );

            $affectedRows =
                mysqli_stmt_affected_rows(
                    $approveStmt
                );

            mysqli_stmt_close(
                $approveStmt
            );

            if ($affectedRows !== 1) {
                throw new mysqli_sql_exception(
                    "Appointment approval failed."
                );
            }

            refreshCsrfToken();

            setDoctorSuccess(
                "Appointment approved successfully."
            );

        } catch (mysqli_sql_exception $e) {
            error_log(
                "Doctor approval error: " .
                $e->getMessage()
            );

            setDoctorError(
                "Unable to approve the appointment."
            );
        }

        mysqli_close($conn);
        redirectToAppointments();
    }

    //  Complete Appointment

    if ($action === "complete_appointment") {

        if (
            $appointment["status"] !== "Approved"
        ) {
            setDoctorError(
                "Only approved appointments can be completed."
            );

            mysqli_close($conn);
            redirectToAppointments();
        }

        $appointmentDateTime =
            DateTime::createFromFormat(
                "Y-m-d H:i:s",
                $appointment[
                    "appointment_date"
                ] .
                " " .
                $appointment[
                    "appointment_time"
                ]
            );

        if (
            !$appointmentDateTime ||
            $appointmentDateTime <
            new DateTime()
        ) {
            setDoctorError(
                "This appointment cannot be completed before its scheduled time."
            );

            mysqli_close($conn);
            redirectToAppointments();
        }

        try {
            $completeSql = "
                UPDATE appointments
                SET status = 'Completed'
                WHERE appointment_id = ?
                  AND doctor_id = ?
                  AND status = 'Approved'
            ";

            $completeStmt =
                mysqli_prepare(
                    $conn,
                    $completeSql
                );

            mysqli_stmt_bind_param(
                $completeStmt,
                "ii",
                $appointmentId,
                $doctorId
            );

            mysqli_stmt_execute(
                $completeStmt
            );

            $affectedRows =
                mysqli_stmt_affected_rows(
                    $completeStmt
                );

            mysqli_stmt_close(
                $completeStmt
            );

            if ($affectedRows !== 1) {
                throw new mysqli_sql_exception(
                    "Appointment completion failed."
                );
            }

            refreshCsrfToken();

            setDoctorSuccess(
                "Appointment marked as completed successfully."
            );

        } catch (mysqli_sql_exception $e) {
            error_log(
                "Doctor completion error: " .
                $e->getMessage()
            );

            setDoctorError(
                "Unable to complete the appointment."
            );
        }

        mysqli_close($conn);
        redirectToAppointments();
    }

    // Cancel Appointment

    if ($action === "cancel_appointment") {

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
            setDoctorError(
                "Only pending or approved appointments can be cancelled."
            );

            mysqli_close($conn);
            redirectToAppointments();
        }

        try {
            $cancelSql = "
                UPDATE appointments
                SET status = 'Cancelled'
                WHERE appointment_id = ?
                  AND doctor_id = ?
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
                "ii",
                $appointmentId,
                $doctorId
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

            refreshCsrfToken();

            setDoctorSuccess(
                "Appointment cancelled successfully."
            );

        } catch (mysqli_sql_exception $e) {
            error_log(
                "Doctor cancellation error: " .
                $e->getMessage()
            );

            setDoctorError(
                "Unable to cancel the appointment."
            );
        }

        mysqli_close($conn);
        redirectToAppointments();
    }
}

// Patient ID for Clinical Actions

$patientId =
    filter_input(
        INPUT_POST,
        "patient_id",
        FILTER_VALIDATE_INT
    );

if (!$patientId) {
    setDoctorError(
        "Invalid patient selected."
    );

    mysqli_close($conn);
    redirectToAppointments();
}

// Verify Patient Exists

$patientSql = "
    SELECT patient_id
    FROM patients
    WHERE patient_id = ?
    LIMIT 1
";

$patientStmt =
    mysqli_prepare(
        $conn,
        $patientSql
    );

mysqli_stmt_bind_param(
    $patientStmt,
    "i",
    $patientId
);

mysqli_stmt_execute(
    $patientStmt
);

$patientResult =
    mysqli_stmt_get_result(
        $patientStmt
    );

$patient =
    mysqli_fetch_assoc(
        $patientResult
    );

mysqli_stmt_close(
    $patientStmt
);

if (!$patient) {
    setDoctorError(
        "The selected patient could not be found."
    );

    mysqli_close($conn);
    redirectToAppointments();
}

//  Add Medical Record

if ($action === "add_medical_record") {

    $appointmentId =
        filter_input(
            INPUT_POST,
            "appointment_id",
            FILTER_VALIDATE_INT
        );

    $diagnosis =
        trim($_POST["diagnosis"] ?? "");

    $treatment =
        trim($_POST["treatment"] ?? "");

    $notes =
        trim($_POST["notes"] ?? "");

    if (
        !$appointmentId ||
        $diagnosis === "" ||
        $treatment === ""
    ) {
        setDoctorError(
            "Please complete all required medical-record fields."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "medical"
        );
    }

    if (
        mb_strlen($diagnosis) < 3 ||
        mb_strlen($diagnosis) > 1000
    ) {
        setDoctorError(
            "Diagnosis must contain between 3 and 1000 characters."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "medical"
        );
    }

    if (
        mb_strlen($treatment) < 3 ||
        mb_strlen($treatment) > 1000
    ) {
        setDoctorError(
            "Treatment must contain between 3 and 1000 characters."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "medical"
        );
    }

    if (mb_strlen($notes) > 2000) {
        setDoctorError(
            "Notes cannot exceed 2000 characters."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "medical"
        );
    }

    $appointmentCheckSql = "
        SELECT a.appointment_id
        FROM appointments a
        LEFT JOIN medical_records mr
            ON a.appointment_id =
               mr.appointment_id
        WHERE a.appointment_id = ?
          AND a.patient_id = ?
          AND a.doctor_id = ?
          AND a.status = 'Completed'
          AND mr.record_id IS NULL
        LIMIT 1
    ";

    $appointmentCheckStmt =
        mysqli_prepare(
            $conn,
            $appointmentCheckSql
        );

    mysqli_stmt_bind_param(
        $appointmentCheckStmt,
        "iii",
        $appointmentId,
        $patientId,
        $doctorId
    );

    mysqli_stmt_execute(
        $appointmentCheckStmt
    );

    $appointmentCheckResult =
        mysqli_stmt_get_result(
            $appointmentCheckStmt
        );

    $validAppointment =
        mysqli_fetch_assoc(
            $appointmentCheckResult
        );

    mysqli_stmt_close(
        $appointmentCheckStmt
    );

    if (!$validAppointment) {
        setDoctorError(
            "Select a completed appointment that does not already have a medical record."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "medical"
        );
    }

    try {
        $medicalRecordSql = "
            INSERT INTO medical_records
            (
                appointment_id,
                patient_id,
                doctor_id,
                diagnosis,
                treatment,
                notes,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW()
            )
        ";

        $medicalRecordStmt =
            mysqli_prepare(
                $conn,
                $medicalRecordSql
            );

        mysqli_stmt_bind_param(
            $medicalRecordStmt,
            "iiisss",
            $appointmentId,
            $patientId,
            $doctorId,
            $diagnosis,
            $treatment,
            $notes
        );

        mysqli_stmt_execute(
            $medicalRecordStmt
        );

        mysqli_stmt_close(
            $medicalRecordStmt
        );

        refreshCsrfToken();

        setDoctorSuccess(
            "Medical record added successfully."
        );

    } catch (mysqli_sql_exception $e) {
        error_log(
            "Medical record error: " .
            $e->getMessage()
        );

        setDoctorError(
            "Unable to add the medical record."
        );
    }

    mysqli_close($conn);

    redirectToPatient(
        $patientId,
        "medical"
    );
}

//  Add Prescription

if ($action === "add_prescription") {

    $recordId =
        filter_input(
            INPUT_POST,
            "record_id",
            FILTER_VALIDATE_INT
        );

    $medicine =
        trim($_POST["medicine"] ?? "");

    $dosage =
        trim($_POST["dosage"] ?? "");

    $duration =
        trim($_POST["duration"] ?? "");

    $instructions =
        trim($_POST["instructions"] ?? "");

    if (
        !$recordId ||
        $medicine === "" ||
        $dosage === "" ||
        $duration === ""
    ) {
        setDoctorError(
            "Please complete all required prescription fields."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "prescription"
        );
    }

    if (
        mb_strlen($medicine) > 150 ||
        mb_strlen($dosage) > 100 ||
        mb_strlen($duration) > 100 ||
        mb_strlen($instructions) > 500
    ) {
        setDoctorError(
            "One or more prescription fields exceed the allowed length."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "prescription"
        );
    }

    $recordCheckSql = "
        SELECT record_id
        FROM medical_records
        WHERE record_id = ?
          AND patient_id = ?
          AND doctor_id = ?
        LIMIT 1
    ";

    $recordCheckStmt =
        mysqli_prepare(
            $conn,
            $recordCheckSql
        );

    mysqli_stmt_bind_param(
        $recordCheckStmt,
        "iii",
        $recordId,
        $patientId,
        $doctorId
    );

    mysqli_stmt_execute(
        $recordCheckStmt
    );

    $recordCheckResult =
        mysqli_stmt_get_result(
            $recordCheckStmt
        );

    $validRecord =
        mysqli_fetch_assoc(
            $recordCheckResult
        );

    mysqli_stmt_close(
        $recordCheckStmt
    );

    if (!$validRecord) {
        setDoctorError(
            "The selected medical record is invalid."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "prescription"
        );
    }

    try {
        $prescriptionSql = "
            INSERT INTO prescriptions
            (
                record_id,
                medicine,
                dosage,
                duration,
                instructions,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW()
            )
        ";

        $prescriptionStmt =
            mysqli_prepare(
                $conn,
                $prescriptionSql
            );

        mysqli_stmt_bind_param(
            $prescriptionStmt,
            "issss",
            $recordId,
            $medicine,
            $dosage,
            $duration,
            $instructions
        );

        mysqli_stmt_execute(
            $prescriptionStmt
        );

        mysqli_stmt_close(
            $prescriptionStmt
        );

        refreshCsrfToken();

        setDoctorSuccess(
            "Prescription added successfully."
        );

    } catch (mysqli_sql_exception $e) {
        error_log(
            "Prescription error: " .
            $e->getMessage()
        );

        setDoctorError(
            "Unable to add the prescription."
        );
    }

    mysqli_close($conn);

    redirectToPatient(
        $patientId,
        "prescription"
    );
}

// Upload Laboratory Report

if ($action === "upload_lab_report") {

    $testName =
        trim($_POST["test_name"] ?? "");

    $reportDate =
        trim($_POST["report_date"] ?? "");

    if (
        $testName === "" ||
        $reportDate === ""
    ) {
        setDoctorError(
            "Please enter the test name and report date."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    if (
        mb_strlen($testName) < 2 ||
        mb_strlen($testName) > 150
    ) {
        setDoctorError(
            "Test name must contain between 2 and 150 characters."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    $dateObject =
        DateTime::createFromFormat(
            "Y-m-d",
            $reportDate
        );

    if (
        !$dateObject ||
        $dateObject->format("Y-m-d") !==
            $reportDate ||
        $reportDate > date("Y-m-d")
    ) {
        setDoctorError(
            "Please select a valid report date."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    if (
        !isset($_FILES["report_file"]) ||
        $_FILES["report_file"]["error"] !==
            UPLOAD_ERR_OK
    ) {
        setDoctorError(
            "Please select a valid PDF report."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    $uploadedFile =
        $_FILES["report_file"];

    $maximumFileSize =
        5 * 1024 * 1024;

    if (
        (int) $uploadedFile["size"] >
        $maximumFileSize
    ) {
        setDoctorError(
            "The PDF report cannot exceed 5 MB."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    $temporaryPath =
        $uploadedFile["tmp_name"];

    $finfo =
        new finfo(FILEINFO_MIME_TYPE);

    $mimeType =
        $finfo->file($temporaryPath);

    if ($mimeType !== "application/pdf") {
        setDoctorError(
            "Only genuine PDF files are allowed."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    $uploadDirectory =
        dirname(__DIR__) .
        "/../uploads/lab_reports/";

    if (
        !is_dir($uploadDirectory) &&
        !mkdir(
            $uploadDirectory,
            0755,
            true
        )
    ) {
        setDoctorError(
            "The report upload folder could not be created."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    $storedFileName =
        "lab_" .
        $patientId .
        "_" .
        bin2hex(random_bytes(12)) .
        ".pdf";

    $destination =
        $uploadDirectory .
        $storedFileName;

    if (
        !move_uploaded_file(
            $temporaryPath,
            $destination
        )
    ) {
        setDoctorError(
            "The PDF report could not be uploaded."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "lab"
        );
    }

    try {
        $reportSql = "
            INSERT INTO laboratory_reports
            (
                patient_id,
                doctor_id,
                test_name,
                report_file,
                status,
                report_date,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'Completed',
                ?,
                NOW()
            )
        ";

        $reportStmt =
            mysqli_prepare(
                $conn,
                $reportSql
            );

        mysqli_stmt_bind_param(
            $reportStmt,
            "iisss",
            $patientId,
            $doctorId,
            $testName,
            $storedFileName,
            $reportDate
        );

        mysqli_stmt_execute(
            $reportStmt
        );

        mysqli_stmt_close(
            $reportStmt
        );

        refreshCsrfToken();

        setDoctorSuccess(
            "Laboratory report uploaded successfully."
        );

    } catch (mysqli_sql_exception $e) {
        if (is_file($destination)) {
            unlink($destination);
        }

        error_log(
            "Laboratory report error: " .
            $e->getMessage()
        );

        setDoctorError(
            "Unable to save the laboratory report."
        );
    }

    mysqli_close($conn);

    redirectToPatient(
        $patientId,
        "lab"
    );
}

//  Reply to Patient Query

if ($action === "reply_patient_query") {

    $queryId =
        filter_input(
            INPUT_POST,
            "query_id",
            FILTER_VALIDATE_INT
        );

    $reply =
        trim($_POST["reply"] ?? "");

    if (
        !$queryId ||
        $reply === ""
    ) {
        setDoctorError(
            "Please enter a valid reply."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "queries"
        );
    }

    if (
        mb_strlen($reply) < 5 ||
        mb_strlen($reply) > 2000
    ) {
        setDoctorError(
            "Reply must contain between 5 and 2000 characters."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "queries"
        );
    }

    $queryCheckSql = "
        SELECT query_id
        FROM patient_queries
        WHERE query_id = ?
          AND patient_id = ?
          AND (
              doctor_id IS NULL
              OR doctor_id = ?
          )
        LIMIT 1
    ";

    $queryCheckStmt =
        mysqli_prepare(
            $conn,
            $queryCheckSql
        );

    mysqli_stmt_bind_param(
        $queryCheckStmt,
        "iii",
        $queryId,
        $patientId,
        $doctorId
    );

    mysqli_stmt_execute(
        $queryCheckStmt
    );

    $queryCheckResult =
        mysqli_stmt_get_result(
            $queryCheckStmt
        );

    $validQuery =
        mysqli_fetch_assoc(
            $queryCheckResult
        );

    mysqli_stmt_close(
        $queryCheckStmt
    );

    if (!$validQuery) {
        setDoctorError(
            "The selected patient query is invalid."
        );

        mysqli_close($conn);
        redirectToPatient(
            $patientId,
            "queries"
        );
    }

    try {
        $replySql = "
            UPDATE patient_queries
            SET
                doctor_id = ?,
                reply = ?,
                status = 'Answered'
            WHERE query_id = ?
              AND patient_id = ?
        ";

        $replyStmt =
            mysqli_prepare(
                $conn,
                $replySql
            );

        mysqli_stmt_bind_param(
            $replyStmt,
            "isii",
            $doctorId,
            $reply,
            $queryId,
            $patientId
        );

        mysqli_stmt_execute(
            $replyStmt
        );

        $affectedRows =
            mysqli_stmt_affected_rows(
                $replyStmt
            );

        mysqli_stmt_close(
            $replyStmt
        );

        if ($affectedRows !== 1) {
            throw new mysqli_sql_exception(
                "Query reply update failed."
            );
        }

        refreshCsrfToken();

        setDoctorSuccess(
            "Your reply was saved successfully."
        );

    } catch (mysqli_sql_exception $e) {
        error_log(
            "Patient query reply error: " .
            $e->getMessage()
        );

        setDoctorError(
            "Unable to save the query reply."
        );
    }

    mysqli_close($conn);

    redirectToPatient(
        $patientId,
        "queries"
    );
}

//  Fallback

setDoctorError(
    "The requested action could not be completed."
);

mysqli_close($conn);
redirectToAppointments();