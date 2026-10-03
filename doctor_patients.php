<?php
session_start();
 
//    Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

//    Prevent Protected Page Caching

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header("Pragma: no-cache");
header("Expires: 0");

//    Doctor Authentication

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: login.php");
    exit();
}

// Doctor Role = 2

if ((int) $_SESSION["role_id"] !== 2) {
    header("Location: login.php");
    exit();
}

$userId =
    (int) $_SESSION["user_id"];
 
//    CSRF Token

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );

}

//    Output Helper

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}

//    Load Logged-in Doctor

$doctorSql = "
    SELECT
        d.doctor_id,
        d.user_id,
        d.department_id,
        d.first_name,
        d.last_name,
        d.phone,
        d.specialization,
        d.availability,
        dep.department_name,
        u.username,
        u.email,
        u.status
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    INNER JOIN departments dep
        ON d.department_id =
           dep.department_id
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

    header("Location: login.php");
    exit();

}

$doctorId =
    (int) $doctor["doctor_id"];

$doctorName =
    $doctor["first_name"] .
    " " .
    $doctor["last_name"];

$doctorInitial =
    strtoupper(
        substr(
            $doctor["first_name"],
            0,
            1
        )
    );

//    Search Value

$search =
    trim(
        $_GET["search"] ?? ""
    );

//    Selected Patient

$selectedPatient = null;

$selectedPatientId =
    filter_input(
        INPUT_GET,
        "patient_id",
        FILTER_VALIDATE_INT
    );

if ($selectedPatientId) {

    $selectedPatientSql = "
        SELECT
            patient_id,
            user_id,
            first_name,
            last_name,
            gender,
            dob,
            phone,
            email,
            blood_group,
            emergency_contact,
            created_at
        FROM patients
        WHERE patient_id = ?
        LIMIT 1
    ";

    $selectedPatientStmt =
        mysqli_prepare(
            $conn,
            $selectedPatientSql
        );

    mysqli_stmt_bind_param(
        $selectedPatientStmt,
        "i",
        $selectedPatientId
    );

    mysqli_stmt_execute(
        $selectedPatientStmt
    );

    $selectedPatientResult =
        mysqli_stmt_get_result(
            $selectedPatientStmt
        );

    $selectedPatient =
        mysqli_fetch_assoc(
            $selectedPatientResult
        );

    mysqli_stmt_close(
        $selectedPatientStmt
    );

    if (!$selectedPatient) {

        $_SESSION["doctor_error"] =
            "The selected patient could not be found.";

        header(
            "Location: doctor_patients.php"
        );

        exit();

    }

}
//    Completed Appointments Without Records

$completedAppointments = [];

if ($selectedPatientId) {

    $completedAppointmentSql = "
        SELECT
            a.appointment_id,
            a.appointment_date,
            a.appointment_time
        FROM appointments a
        LEFT JOIN medical_records mr
            ON a.appointment_id =
               mr.appointment_id
        WHERE a.patient_id = ?
          AND a.doctor_id = ?
          AND a.status = 'Completed'
          AND mr.record_id IS NULL
        ORDER BY
            a.appointment_date DESC,
            a.appointment_time DESC
    ";

    $completedAppointmentStmt =
        mysqli_prepare(
            $conn,
            $completedAppointmentSql
        );

    mysqli_stmt_bind_param(
        $completedAppointmentStmt,
        "ii",
        $selectedPatientId,
        $doctorId
    );

    mysqli_stmt_execute(
        $completedAppointmentStmt
    );

    $completedAppointmentResult =
        mysqli_stmt_get_result(
            $completedAppointmentStmt
        );

    while (
        $appointment =
            mysqli_fetch_assoc(
                $completedAppointmentResult
            )
    ) {
        $completedAppointments[] =
            $appointment;
    }

    mysqli_stmt_close(
        $completedAppointmentStmt
    );
}

//    Search Patients

$patients = [];

if ($search !== "") {

    $keyword =
        "%" . $search . "%";

    $patientSearchSql = "
        SELECT
            patient_id,
            first_name,
            last_name,
            gender,
            dob,
            blood_group
        FROM patients
        WHERE
            first_name LIKE ?
            OR last_name LIKE ?
            OR CONCAT(
                first_name,
                ' ',
                last_name
            ) LIKE ?
            OR CAST(
                patient_id AS CHAR
            ) LIKE ?
        ORDER BY
            first_name ASC,
            last_name ASC
    ";

    $patientSearchStmt =
        mysqli_prepare(
            $conn,
            $patientSearchSql
        );

    mysqli_stmt_bind_param(
        $patientSearchStmt,
        "ssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute(
        $patientSearchStmt
    );

    $patientSearchResult =
        mysqli_stmt_get_result(
            $patientSearchStmt
        );

    while (
        $patient =
            mysqli_fetch_assoc(
                $patientSearchResult
            )
    ) {
        $patients[] = $patient;
    }

    mysqli_stmt_close(
        $patientSearchStmt
    );

}

//    Load Medical Records

$medicalRecords = [];

if ($selectedPatient) {

    $medicalRecordSql = "
        SELECT
            record_id,
            patient_id,
            doctor_id,
            diagnosis,
            treatment,
            notes,
            created_at
        FROM medical_records
        WHERE patient_id = ?
        ORDER BY created_at DESC
    ";

    $medicalRecordStmt =
        mysqli_prepare(
            $conn,
            $medicalRecordSql
        );

    mysqli_stmt_bind_param(
        $medicalRecordStmt,
        "i",
        $selectedPatient["patient_id"]
    );

    mysqli_stmt_execute(
        $medicalRecordStmt
    );

    $medicalRecordResult =
        mysqli_stmt_get_result(
            $medicalRecordStmt
        );

    while (
        $medicalRecord =
            mysqli_fetch_assoc(
                $medicalRecordResult
            )
    ) {
        $medicalRecords[] =
            $medicalRecord;
    }

    mysqli_stmt_close(
        $medicalRecordStmt
    );

}

//    Load Records Available for Prescriptions

$prescriptionRecords = [];

if ($selectedPatient) {

    $prescriptionRecordSql = "
        SELECT
            record_id,
            diagnosis,
            created_at
        FROM medical_records
        WHERE patient_id = ?
          AND doctor_id = ?
        ORDER BY created_at DESC
    ";

    $prescriptionRecordStmt =
        mysqli_prepare(
            $conn,
            $prescriptionRecordSql
        );

    mysqli_stmt_bind_param(
        $prescriptionRecordStmt,
        "ii",
        $selectedPatient["patient_id"],
        $doctorId
    );

    mysqli_stmt_execute(
        $prescriptionRecordStmt
    );

    $prescriptionRecordResult =
        mysqli_stmt_get_result(
            $prescriptionRecordStmt
        );

    while (
        $record =
            mysqli_fetch_assoc(
                $prescriptionRecordResult
            )
    ) {
        $prescriptionRecords[] =
            $record;
    }

    mysqli_stmt_close(
        $prescriptionRecordStmt
    );

}

//    Load Prescriptions

$prescriptions = [];

if ($selectedPatient) {

    $prescriptionSql = "
        SELECT
            p.prescription_id,
            p.record_id,
            p.medicine,
            p.dosage,
            p.duration,
            p.instructions,
            p.created_at,
            mr.diagnosis
        FROM prescriptions p
        INNER JOIN medical_records mr
            ON p.record_id =
               mr.record_id
        WHERE mr.patient_id = ?
          AND mr.doctor_id = ?
        ORDER BY p.created_at DESC
    ";

    $prescriptionStmt =
        mysqli_prepare(
            $conn,
            $prescriptionSql
        );

    mysqli_stmt_bind_param(
        $prescriptionStmt,
        "ii",
        $selectedPatient["patient_id"],
        $doctorId
    );

    mysqli_stmt_execute(
        $prescriptionStmt
    );

    $prescriptionResult =
        mysqli_stmt_get_result(
            $prescriptionStmt
        );

    while (
        $prescription =
            mysqli_fetch_assoc(
                $prescriptionResult
            )
    ) {
        $prescriptions[] =
            $prescription;
    }

    mysqli_stmt_close(
        $prescriptionStmt
    );

}

//    Load Laboratory Reports

$laboratoryReports = [];

if ($selectedPatient) {

    $laboratoryReportSql = "
        SELECT
            report_id,
            patient_id,
            doctor_id,
            test_name,
            report_date,
            report_file,
            created_at
        FROM laboratory_reports
        WHERE patient_id = ?
          AND doctor_id = ?
        ORDER BY
            report_date DESC,
            created_at DESC
    ";

    $laboratoryReportStmt =
        mysqli_prepare(
            $conn,
            $laboratoryReportSql
        );

    mysqli_stmt_bind_param(
        $laboratoryReportStmt,
        "ii",
        $selectedPatient["patient_id"],
        $doctorId
    );

    mysqli_stmt_execute(
        $laboratoryReportStmt
    );

    $laboratoryReportResult =
        mysqli_stmt_get_result(
            $laboratoryReportStmt
        );

    while (
        $report =
            mysqli_fetch_assoc(
                $laboratoryReportResult
            )
    ) {
        $laboratoryReports[] =
            $report;
    }

    mysqli_stmt_close(
        $laboratoryReportStmt
    );

}

//    Load Patient Queries

$patientQueries = [];

if ($selectedPatient) {

/*This query assumes the database column is named "message".
If your table uses "massage", rename that column to "message".*/

    $patientQuerySql = "
        SELECT
            query_id,
            patient_id,
            doctor_id,
            subject,
            message,
            reply,
            status,
            created_at
        FROM patient_queries
        WHERE patient_id = ?
          AND doctor_id = ?
        ORDER BY created_at DESC
    ";

    $patientQueryStmt =
        mysqli_prepare(
            $conn,
            $patientQuerySql
        );

    mysqli_stmt_bind_param(
        $patientQueryStmt,
        "ii",
        $selectedPatient["patient_id"],
        $doctorId
    );

    mysqli_stmt_execute(
        $patientQueryStmt
    );

    $patientQueryResult =
        mysqli_stmt_get_result(
            $patientQueryStmt
        );

    while (
        $query =
            mysqli_fetch_assoc(
                $patientQueryResult
            )
    ) {
        $patientQueries[] =
            $query;
    }

    mysqli_stmt_close(
        $patientQueryStmt
    );

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BestCare Hospital</title>

    <link rel="icon" type="image/png" href="image/logo.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="css/doctor_dashboard.css">

    <link rel="stylesheet" href="css/doctor_patients.css">

</head>

<body>

<div class="doctor-layout">

    <!-- Sidebar -->

    <aside class="doctor-sidebar" id="doctorSidebar">

        <div class="doctor-sidebar-logo">

            <img src="image/logo.png" alt="BestCare Hospital Logo">

            <div>

                <h2>BestCare</h2>

                <span>
                    Doctor Portal
                </span>

            </div>

        </div>

        <nav class="doctor-navigation">

            <a href="doctor_dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>
                    Dashboard
                </span>

            </a>

            <a href="doctor_appointments.php">

                <i class="fa-solid fa-calendar-check"></i>

                <span>
                    Manage Appointments
                </span>

            </a>

            <a href="doctor_patients.php" class="active">

                <i class="fa-solid fa-user-injured"></i>

                <span>
                    Patients
                </span>

            </a>

        </nav>

        <div class="doctor-sidebar-bottom">

            <a href="logout.php" class="doctor-logout-link">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>
                    Logout
                </span>

            </a>

        </div>

    </aside>

    <!-- Main Content -->

    <div class="doctor-main">

        <header class="doctor-topbar">

            <button
                type="button"
                class="doctor-sidebar-toggle"
                id="doctorSidebarToggle"
                aria-label="Open navigation menu"
            >

                <i class="fa-solid fa-bars"></i>

            </button>

            <div class="doctor-topbar-title">

                <h1>
                    Patients
                </h1>

                <p>
                    Search for a patient and manage
                    clinical information.
                </p>

            </div>

            <div class="doctor-account">

                <div class="doctor-avatar">

                    <?php
                    echo e(
                        $doctorInitial
                    );
                    ?>

                </div>

                <div class="doctor-account-text">

                    <strong>

                        Dr.
                        <?php
                        echo e(
                            $doctorName
                        );
                        ?>

                    </strong>

                    <span>

                        <?php
                        echo e(
                            $doctor[
                                "specialization"
                            ]
                        );
                        ?>

                    </span>

                </div>

            </div>

        </header>

        <main class="doctor-patients-content">

            <!-- Messages -->

            <?php if (
                isset(
                    $_SESSION["doctor_success"]
                )
            ): ?>

                <div class="doctor-patient-message success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <span>

                        <?php
                        echo e(
                            $_SESSION[
                                "doctor_success"
                            ]
                        );

                        unset(
                            $_SESSION[
                                "doctor_success"
                            ]
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>

            <?php if (
                isset(
                    $_SESSION["doctor_error"]
                )
            ): ?>

                <div
                    class="doctor-patient-message error-message"
                >

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>

                        <?php
                        echo e(
                            $_SESSION[
                                "doctor_error"
                            ]
                        );

                        unset(
                            $_SESSION[
                                "doctor_error"
                            ]
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>

            <!-- Search Card -->

            <section class="search-card">

                <div>

                    <span class="search-card-label">
                        Patient Search
                    </span>

                    <h2>

                        <i class="fa-solid fa-user-group"></i>

                        Search Patient

                    </h2>

                    <p>
                        Search by patient ID, first name,
                        last name, or full name.
                    </p>

                </div>

                <form
                    method="GET"
                    action="doctor_patients.php"
                    class="patient-search-form"
                >

                    <div class="patient-search-input">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="search"
                            name="search"
                            placeholder="Patient ID or name"
                            value="<?php
                            echo e($search);
                            ?>"
                            required
                        >

                    </div>

                    <button type="submit" class="search-button">

                        <i class="fa-solid fa-search"></i>

                        Search

                    </button>

                </form>

            </section>

            <!-- Search Results -->

            <?php if ($search !== ""): ?>

                <section class="patient-results">

                    <div class="patient-results-header">

                        <div>

                            <span>
                                Search Results
                            </span>

                            <h2>
                                Matching Patients
                            </h2>

                        </div>

                        <strong>
                            <?php
                            echo count($patients);
                            ?>
                            result<?php
                            echo count($patients) === 1
                                ? ""
                                : "s";
                            ?>
                        </strong>

                    </div>

                    <div class="patient-results-table-wrapper">

                        <table class="patient-results-table">

                            <thead>

                                <tr>

                                    <th>
                                        Patient ID
                                    </th>

                                    <th>
                                        Name
                                    </th>

                                    <th>
                                        Gender
                                    </th>

                                    <th>
                                        Date of Birth
                                    </th>

                                    <th>
                                        Blood Group
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if (
                                    empty($patients)
                                ): ?>

                                    <tr>

                                        <td
                                            colspan="6"
                                            class="empty-result-cell"
                                        >

                                            No patients matched your search.

                                        </td>

                                    </tr>

                                <?php else: ?>

                                    <?php foreach (
                                        $patients
                                        as $patient
                                    ): ?>

                                        <tr>

                                            <td>

                                                BC-<?php
                                                echo str_pad(
                                                    (string)
                                                    $patient[
                                                        "patient_id"
                                                    ],
                                                    5,
                                                    "0",
                                                    STR_PAD_LEFT
                                                );
                                                ?>

                                            </td>

                                            <td>

                                                <strong>

                                                    <?php
                                                    echo e(
                                                        $patient[
                                                            "first_name"
                                                        ] .
                                                        " " .
                                                        $patient[
                                                            "last_name"
                                                        ]
                                                    );
                                                    ?>

                                                </strong>

                                            </td>

                                            <td>

                                                <?php
                                                echo e(
                                                    $patient[
                                                        "gender"
                                                    ]
                                                );
                                                ?>

                                            </td>

                                            <td>

                                                <?php
                                                echo date(
                                                    "d M Y",
                                                    strtotime(
                                                        $patient[
                                                            "dob"
                                                        ]
                                                    )
                                                );
                                                ?>

                                            </td>

                                            <td>

                                                <span class="blood-group-badge">

                                                    <?php
                                                    echo e(
                                                        $patient[
                                                            "blood_group"
                                                        ]
                                                    );
                                                    ?>

                                                </span>

                                            </td>

                                            <td>

                                                <a
                                                    href="doctor_patients.php?search=<?php
                                                    echo urlencode(
                                                        $search
                                                    );
                                                    ?>&patient_id=<?php
                                                    echo (int)
                                                        $patient[
                                                            "patient_id"
                                                        ];
                                                    ?>"
                                                    class="view-profile-btn"
                                                >

                                                    <i class="fa-solid fa-address-card"></i>

                                                    View Profile

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </section>

            <?php endif; ?>

            <!-- Patient Profile Modal -->

            <div
                id="patientModal"
                class="patient-modal"
                aria-hidden="<?php
                echo $selectedPatient
                    ? "false"
                    : "true";
                ?>"
            >

                <div class="patient-modal-content">

                    <button
                        type="button"
                        id="closeModal"
                        class="patient-modal-close"
                        aria-label="Close patient profile"
                    >

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                    <div class="patient-modal-header">

                        <div>

                            <span>
                                Patient Profile
                            </span>

                            <h2>

                                <?php if (
                                    $selectedPatient
                                ): ?>

                                    <?php
                                    echo e(
                                        $selectedPatient[
                                            "first_name"
                                        ] .
                                        " " .
                                        $selectedPatient[
                                            "last_name"
                                        ]
                                    );
                                    ?>

                                <?php else: ?>

                                    Select a Patient

                                <?php endif; ?>

                            </h2>

                        </div>

                        <div class="patient-profile-icon">

                            <i class="fa-solid fa-user-injured"></i>

                        </div>

                    </div>

                    <!-- Basic Patient Information -->

                    <section class="patient-basic-info">

                        <article>

                            <span>
                                Name
                            </span>

                            <strong id="modalName">

                                <?php
                                echo $selectedPatient
                                    ? e(
                                        $selectedPatient[
                                            "first_name"
                                        ] .
                                        " " .
                                        $selectedPatient[
                                            "last_name"
                                        ]
                                    )
                                    : "";
                                ?>

                            </strong>

                        </article>

                        <article>

                            <span>
                                Patient ID
                            </span>

                            <strong id="modalId">

                                <?php if (
                                    $selectedPatient
                                ): ?>

                                    BC-<?php
                                    echo str_pad(
                                        (string)
                                        $selectedPatient[
                                            "patient_id"
                                        ],
                                        5,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>

                                <?php endif; ?>

                            </strong>

                        </article>

                        <article>

                            <span>
                                Gender
                            </span>

                            <strong id="modalGender">

                                <?php
                                echo $selectedPatient
                                    ? e(
                                        $selectedPatient[
                                            "gender"
                                        ]
                                    )
                                    : "";
                                ?>

                            </strong>

                        </article>

                        <article>

                            <span>
                                Date of Birth
                            </span>

                            <strong id="modalDob">

                                <?php if (
                                    $selectedPatient
                                ): ?>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $selectedPatient[
                                                "dob"
                                            ]
                                        )
                                    );
                                    ?>

                                <?php endif; ?>

                            </strong>

                        </article>

                        <article>

                            <span>
                                Blood Group
                            </span>

                            <strong id="modalBlood">

                                <?php
                                echo $selectedPatient
                                    ? e(
                                        $selectedPatient[
                                            "blood_group"
                                        ]
                                    )
                                    : "";
                                ?>

                            </strong>

                        </article>

                    </section>

                    <!-- Profile Tabs -->

                    <div
                        class="patient-tabs"
                        role="tablist"
                    >

                        <button
                            type="button"
                            class="tab-btn active"
                            data-tab="medical"
                        >

                            <i class="fa-solid fa-notes-medical"></i>

                            Medical Records

                        </button>

                        <button
                            type="button"
                            class="tab-btn"
                            data-tab="prescription"
                        >

                            <i class="fa-solid fa-pills"></i>

                            Prescriptions

                        </button>

                        <button
                            type="button"
                            class="tab-btn"
                            data-tab="lab"
                        >

                            <i class="fa-solid fa-file-pdf"></i>

                            Laboratory Reports

                        </button>

                        <button
                            type="button"
                            class="tab-btn"
                            data-tab="queries"
                        >

                            <i class="fa-solid fa-comments"></i>

                            Patient Queries

                        </button>

                    </div>
<!-- Medical Records Tab -->
<div id="medical" class="tab-content active">

    <div class="tab-section-header">

        <div>

            <span>
                Clinical Information
            </span>

            <h3>

                <i class="fa-solid fa-notes-medical"></i>

                Medical Records

            </h3>

        </div>

    </div>

    <?php if ($selectedPatient): ?>

        <!-- Add Medical Record -->

        <section class="medical-record-form-card">

            <h4>

                <i class="fa-solid fa-file-circle-plus"></i>

                Add Medical Record

            </h4>

            <form
                action="db_process/doctor_process.php"
                method="POST"
                class="doctor-form"
                id="medicalRecordForm"
            >

                <input
                    type="hidden"
                    name="action"
                    value="add_medical_record"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php
                    echo e(
                        $_SESSION[
                            "csrf_token"
                        ]
                    );
                    ?>"
                >

                <input
                    type="hidden"
                    name="patient_id"
                    value="<?php
                    echo (int)
                        $selectedPatient[
                            "patient_id"
                        ];
                    ?>"
                >

                <div class="form-group">
                    <label for="appointment_id">
                        Completed Appointment
                    </label>
                    <select
                    id="appointment_id" name="appointment_id" required>
                    <option value="">
                        Select a completed appointment
                    </option>
                    <?php foreach ($completedAppointments as $appointment): ?>
                        <option
                        value="<?php
                        echo (int)
                        $appointment["appointment_id"];
                        ?>">
                        <?php
                        echo e($appointment["appointment_date"] ." at " .$appointment["appointment_time"]);?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-error"></small>

                    <label for="diagnosis">

                        Diagnosis

                    </label>

                    <textarea
                        id="diagnosis"
                        name="diagnosis"
                        rows="4"
                        maxlength="1000"
                        placeholder="Enter the patient's diagnosis"
                        required
                    ></textarea>

                    <small class="field-error"></small>

                </div>

                <div class="form-group">

                    <label for="treatment">

                        Treatment

                    </label>

                    <textarea
                        id="treatment"
                        name="treatment"
                        rows="4"
                        maxlength="1000"
                        placeholder="Enter the recommended treatment"
                        required
                    ></textarea>

                    <small class="field-error"></small>

                </div>

                <div class="form-group">

                    <label for="notes">

                        Doctor Notes

                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="4"
                        maxlength="2000"
                        placeholder="Add any additional medical notes"
                    ></textarea>

                    <small class="field-error"></small>

                </div>

                <button type="submit" class="save-btn">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Medical Record

                </button>

            </form>

        </section>

        <!-- Medical Record History -->

        <section class="medical-record-history">

            <div class="history-section-header">

                <div>

                    <span>
                        Previous Records
                    </span>

                    <h4>

                        <i class="fa-solid fa-clock-rotate-left"></i>

                        Medical Record History

                    </h4>

                </div>

            </div>

            <?php if (
                empty($medicalRecords)
            ): ?>

                <div class="empty-box">

                    <i class="fa-solid fa-file-circle-xmark"></i>

                    <p>
                        No medical records are available
                        for this patient.
                    </p>

                </div>

            <?php else: ?>

                <div class="record-list">

                    <?php foreach (
                        $medicalRecords
                        as $medicalRecord
                    ): ?>

                        <article class="record-card">

                            <div class="record-header">

                                <div>

                                    <span>
                                        Medical Record
                                        #<?php
                                        echo str_pad(
                                            (string)
                                            $medicalRecord[
                                                "record_id"
                                            ],
                                            4,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        ?>
                                    </span>

                                    <h4>

                                        <?php
                                        echo e(
                                            $medicalRecord[
                                                "diagnosis"
                                            ]
                                        );
                                        ?>

                                    </h4>

                                </div>

                                <time>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $medicalRecord[
                                                "created_at"
                                            ]
                                        )
                                    );
                                    ?>

                                </time>

                            </div>

                            <div class="record-body">

                                <div class="record-detail">

                                    <span>

                                        <i class="fa-solid fa-stethoscope"></i>

                                        Diagnosis

                                    </span>

                                    <p>

                                        <?php
                                        echo nl2br(
                                            e(
                                                $medicalRecord[
                                                    "diagnosis"
                                                ]
                                            )
                                        );
                                        ?>

                                    </p>

                                </div>

                                <div class="record-detail">

                                    <span>

                                        <i class="fa-solid fa-hand-holding-medical"></i>

                                        Treatment

                                    </span>

                                    <p>

                                        <?php
                                        echo nl2br(
                                            e(
                                                $medicalRecord[
                                                    "treatment"
                                                ]
                                            )
                                        );
                                        ?>

                                    </p>

                                </div>

                                <?php if (
                                    !empty(
                                        $medicalRecord[
                                            "notes"
                                        ]
                                    )
                                ): ?>

                                    <div class="record-detail">

                                        <span>

                                            <i class="fa-solid fa-note-sticky"></i>

                                            Doctor Notes

                                        </span>

                                        <p>

                                            <?php
                                            echo nl2br(
                                                e(
                                                    $medicalRecord[
                                                        "notes"
                                                    ]
                                                )
                                            );
                                            ?>

                                        </p>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php else: ?>

        <div class="empty-box">

            <i class="fa-solid fa-user-slash"></i>

            <p>
                Select a patient before adding
                or viewing medical records.
            </p>

        </div>

    <?php endif; ?>

</div>
<!-- Prescriptions Tab -->

<div id="prescription" class="tab-content">

    <div class="tab-section-header">

        <div>

            <span>
                Medication Management
            </span>

            <h3>

                <i class="fa-solid fa-pills"></i>

                Prescriptions

            </h3>

        </div>

    </div>

    <?php if ($selectedPatient): ?>

        <!-- Add Prescription Form -->

        <section class="prescription-form-card">

            <h4>

                <i class="fa-solid fa-file-prescription"></i>

                Add Prescription

            </h4>

            <?php if (
                empty($prescriptionRecords)
            ): ?>

                <div class="empty-box">

                    <i class="fa-solid fa-notes-medical"></i>

                    <p>
                        Create a medical record before
                        adding a prescription.
                    </p>

                </div>

            <?php else: ?>

                <form
                    action="db_process/doctor_process.php"
                    method="POST"
                    class="doctor-form"
                    id="prescriptionForm"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="add_prescription"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php
                        echo e(
                            $_SESSION[
                                "csrf_token"
                            ]
                        );
                        ?>"
                    >

                    <input
                        type="hidden"
                        name="patient_id"
                        value="<?php
                        echo (int)
                            $selectedPatient[
                                "patient_id"
                            ];
                        ?>"
                    >

                    <div class="form-group">

                        <label for="record_id">

                            Medical Record

                        </label>

                        <select
                            id="record_id"
                            name="record_id"
                            required
                        >

                            <option value="">

                                Select Medical Record

                            </option>

                            <?php foreach (
                                $prescriptionRecords
                                as $record
                            ): ?>

                                <option
                                    value="<?php
                                    echo (int)
                                        $record[
                                            "record_id"
                                        ];
                                    ?>"
                                >

                                    Record
                                    #<?php
                                    echo str_pad(
                                        (string)
                                        $record[
                                            "record_id"
                                        ],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>

                                    —

                                    <?php
                                    echo e(
                                        $record[
                                            "diagnosis"
                                        ]
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="field-error"></small>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label for="medicine">

                                Medicine

                            </label>

                            <input
                                type="text"
                                id="medicine"
                                name="medicine"
                                maxlength="150"
                                placeholder="Example: Amoxicillin"
                                required
                            >

                            <small class="field-error"></small>

                        </div>

                        <div class="form-group">

                            <label for="dosage">

                                Dosage

                            </label>

                            <input
                                type="text"
                                id="dosage"
                                name="dosage"
                                maxlength="100"
                                placeholder="Example: 500 mg twice daily"
                                required
                            >

                            <small class="field-error"></small>

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label for="duration">

                                Duration

                            </label>

                            <input
                                type="text"
                                id="duration"
                                name="duration"
                                maxlength="100"
                                placeholder="Example: 7 days"
                                required
                            >

                            <small class="field-error"></small>

                        </div>

                        <div class="form-group">

                            <label for="instructions">

                                Instructions

                            </label>

                            <input
                                type="text"
                                id="instructions"
                                name="instructions"
                                maxlength="500"
                                placeholder="Example: Take after meals"
                            >

                            <small class="field-error"></small>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="save-btn"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Prescription

                    </button>

                </form>

            <?php endif; ?>

        </section>

        <!-- Prescription History -->

        <section class="prescription-history-section">

            <div class="history-section-header">

                <div>

                    <span>
                        Previous Prescriptions
                    </span>

                    <h4>

                        <i class="fa-solid fa-clock-rotate-left"></i>

                        Prescription History

                    </h4>

                </div>

            </div>

            <?php if (
                empty($prescriptions)
            ): ?>

                <div class="empty-box">

                    <i class="fa-solid fa-prescription-bottle-medical"></i>

                    <p>
                        No prescriptions are available
                        for this patient.
                    </p>

                </div>

            <?php else: ?>

                <div class="prescription-list">

                    <?php foreach (
                        $prescriptions
                        as $prescription
                    ): ?>

                        <article class="prescription-card">

                            <div class="prescription-card-header">

                                <div>

                                    <span>
                                        Prescription
                                        #<?php
                                        echo str_pad(
                                            (string)
                                            $prescription[
                                                "prescription_id"
                                            ],
                                            4,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        ?>
                                    </span>

                                    <h4>

                                        <?php
                                        echo e(
                                            $prescription[
                                                "medicine"
                                            ]
                                        );
                                        ?>

                                    </h4>

                                </div>

                                <time>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $prescription[
                                                "created_at"
                                            ]
                                        )
                                    );
                                    ?>

                                </time>

                            </div>

                            <div class="prescription-record-reference">

                                <i class="fa-solid fa-notes-medical"></i>

                                <span>

                                    Medical Record:
                                    #<?php
                                    echo str_pad(
                                        (string)
                                        $prescription[
                                            "record_id"
                                        ],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>

                                    —

                                    <?php
                                    echo e(
                                        $prescription[
                                            "diagnosis"
                                        ]
                                    );
                                    ?>

                                </span>

                            </div>

                            <div class="prescription-details-grid">

                                <div>

                                    <span>
                                        Dosage
                                    </span>

                                    <strong>

                                        <?php
                                        echo e(
                                            $prescription[
                                                "dosage"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>

                                <div>

                                    <span>
                                        Duration
                                    </span>

                                    <strong>

                                        <?php
                                        echo e(
                                            $prescription[
                                                "duration"
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </div>

                                <div class="prescription-instructions">

                                    <span>
                                        Instructions
                                    </span>

                                    <strong>

                                        <?php
                                        echo !empty(
                                            $prescription[
                                                "instructions"
                                            ]
                                        )
                                            ? e(
                                                $prescription[
                                                    "instructions"
                                                ]
                                            )
                                            : "No special instructions";
                                        ?>

                                    </strong>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php else: ?>

        <div class="empty-box">

            <i class="fa-solid fa-user-slash"></i>

            <p>
                Select a patient before adding
                or viewing prescriptions.
            </p>

        </div>

    <?php endif; ?>

</div>
<!-- Laboratory Reports Tab -->

<div id="lab" class="tab-content">

    <div class="tab-section-header">

        <div>

            <span>
                Laboratory Reports
            </span>

            <h3>

                <i class="fa-solid fa-file-pdf"></i>

                Upload Laboratory Report

            </h3>

        </div>

    </div>

    <?php if ($selectedPatient): ?>

        <!-- Upload Laboratory Report -->

        <section class="lab-upload-card">

            <h4>

                <i class="fa-solid fa-cloud-arrow-up"></i>

                Upload PDF Report

            </h4>

            <form
                action="db_process/doctor_process.php"
                method="POST"
                enctype="multipart/form-data"
                class="doctor-form"
                id="labReportForm"
            >

                <input
                    type="hidden"
                    name="action"
                    value="upload_lab_report"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php
                    echo e(
                        $_SESSION[
                            "csrf_token"
                        ]
                    );
                    ?>"
                >

                <input
                    type="hidden"
                    name="patient_id"
                    value="<?php
                    echo (int)
                        $selectedPatient[
                            "patient_id"
                        ];
                    ?>"
                >

                <div class="form-row">

                    <div class="form-group">

                        <label for="test_name">

                            Test Name

                        </label>

                        <input
                            type="text"
                            id="test_name"
                            name="test_name"
                            maxlength="150"
                            placeholder="Example: Full Blood Count"
                            required
                        >

                        <small class="field-error"></small>

                    </div>

                    <div class="form-group">

                        <label for="report_date">

                            Report Date

                        </label>

                        <input
                            type="date"
                            id="report_date"
                            name="report_date"
                            max="<?php
                            echo date("Y-m-d");
                            ?>"
                            required
                        >

                        <small class="field-error"></small>

                    </div>

                </div>

                <div class="form-group">

                    <label for="report_file">

                        PDF Report

                    </label>

                    <input
                        type="file"
                        id="report_file"
                        name="report_file"
                        accept=".pdf,application/pdf"
                        required
                    >

                    <small>
                        Only PDF files are allowed.
                        Maximum file size: 5 MB.
                    </small>

                    <small class="field-error"></small>

                </div>

                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    Upload Report

                </button>

            </form>

        </section>

        <!-- Laboratory Report History -->

        <section class="lab-history-section">

            <div class="history-section-header">

                <div>

                    <span>
                        Previous Reports
                    </span>

                    <h4>

                        <i class="fa-solid fa-clock-rotate-left"></i>

                        Uploaded Laboratory Reports

                    </h4>

                </div>

            </div>

            <?php if (
                empty($laboratoryReports)
            ): ?>

                <div class="empty-box">

                    <i class="fa-solid fa-file-circle-xmark"></i>

                    <p>
                        No laboratory reports are available
                        for this patient.
                    </p>

                </div>

            <?php else: ?>

                <div class="lab-report-list">

                    <?php foreach (
                        $laboratoryReports
                        as $report
                    ): ?>

                        <article class="lab-report-card">

                            <div class="lab-report-header">

                                <div>

                                    <span>
                                        Report
                                        #<?php
                                        echo str_pad(
                                            (string)
                                            $report[
                                                "report_id"
                                            ],
                                            4,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                        ?>
                                    </span>

                                    <h4>

                                        <?php
                                        echo e(
                                            $report[
                                                "test_name"
                                            ]
                                        );
                                        ?>

                                    </h4>

                                </div>

                                <time>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $report[
                                                "report_date"
                                            ]
                                        )
                                    );
                                    ?>

                                </time>

                            </div>

                            <div class="lab-report-details">

                                <div>

                                    <span>
                                        Uploaded
                                    </span>

                                    <strong>

                                        <?php
                                        echo date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $report[
                                                    "created_at"
                                                ]
                                            )
                                        );
                                        ?>

                                    </strong>

                                </div>

                            </div>

                            <div class="lab-report-actions">

                                <a
                                    href="uploads/lab_reports/<?php
                                    echo rawurlencode(
                                        basename(
                                            $report[
                                                "report_file"
                                            ]
                                        )
                                    );
                                    ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="view-report-btn"
                                >

                                    <i class="fa-solid fa-file-pdf"></i>

                                    View PDF

                                </a>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    <?php else: ?>

        <div class="empty-box">

            <i class="fa-solid fa-user-slash"></i>

            <p>
                Select a patient before uploading
                or viewing laboratory reports.
            </p>

        </div>

    <?php endif; ?>

</div>
<!-- Patient Queries Tab -->

<div id="queries" class="tab-content">

    <div class="tab-section-header">

        <div>

            <span>
                Patient Communication
            </span>

            <h3>

                <i class="fa-solid fa-comments"></i>

                Patient Queries

            </h3>

        </div>

    </div>

    <?php if ($selectedPatient): ?>

        <?php if (
            empty($patientQueries)
        ): ?>

            <div class="empty-box">

                <i class="fa-solid fa-envelope-open"></i>

                <p>
                    This patient has not submitted
                    any queries to you.
                </p>

            </div>

        <?php else: ?>

            <div class="query-list">

                <?php foreach (
                    $patientQueries
                    as $query
                ): ?>

                    <article class="query-card">

                        <div class="query-header">

                            <div>

                                <span>
                                    Query
                                    #<?php
                                    echo str_pad(
                                        (string)
                                        $query[
                                            "query_id"
                                        ],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </span>

                                <h4>

                                    <?php
                                    echo e(
                                        $query[
                                            "subject"
                                        ]
                                    );
                                    ?>

                                </h4>

                            </div>

                            <span
                                class="query-status status-<?php
                                echo strtolower(
                                    e(
                                        $query[
                                            "status"
                                        ]
                                    )
                                );
                                ?>"
                            >

                                <?php
                                echo e(
                                    $query[
                                        "status"
                                    ]
                                );
                                ?>

                            </span>

                        </div>

                        <div class="query-meta">

                            <span>

                                <i class="fa-solid fa-calendar-day"></i>

                                <?php
                                echo date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $query[
                                            "created_at"
                                        ]
                                    )
                                );
                                ?>

                            </span>

                        </div>

                        <div class="query-body">

                            <strong>
                                Patient Message
                            </strong>

                            <p>

                                <?php
                                echo nl2br(
                                    e(
                                        $query[
                                            "message"
                                        ]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                        <?php if (
                            !empty(
                                $query["reply"]
                            )
                        ): ?>

                            <div class="query-reply">

                                <strong>
                                    Doctor Reply
                                </strong>

                                <p>

                                    <?php
                                    echo nl2br(
                                        e(
                                            $query[
                                                "reply"
                                            ]
                                        )
                                    );
                                    ?>

                                </p>

                            </div>

                        <?php else: ?>

                            <form
                                action="db_process/doctor_process.php"
                                method="POST"
                                class="doctor-form query-reply-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="reply_patient_query"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php
                                    echo e(
                                        $_SESSION[
                                            "csrf_token"
                                        ]
                                    );
                                    ?>"
                                >

                                <input
                                    type="hidden"
                                    name="patient_id"
                                    value="<?php
                                    echo (int)
                                        $selectedPatient[
                                            "patient_id"
                                        ];
                                    ?>"
                                >

                                <input
                                    type="hidden"
                                    name="query_id"
                                    value="<?php
                                    echo (int)
                                        $query[
                                            "query_id"
                                        ];
                                    ?>"
                                >

                                <div class="form-group">

                                    <label
                                        for="reply_<?php
                                        echo (int)
                                            $query[
                                                "query_id"
                                            ];
                                        ?>"
                                    >

                                        Reply

                                    </label>

                                    <textarea
                                        id="reply_<?php
                                        echo (int)
                                            $query[
                                                "query_id"
                                            ];
                                        ?>"
                                        name="reply"
                                        rows="4"
                                        maxlength="2000"
                                        placeholder="Write your reply to the patient"
                                        required
                                    ></textarea>

                                    <small class="field-error"></small>

                                </div>

                                <button
                                    type="submit"
                                    class="save-btn"
                                >

                                    <i class="fa-solid fa-paper-plane"></i>

                                    Send Reply

                                </button>

                            </form>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    <?php else: ?>

        <div class="empty-box">

            <i class="fa-solid fa-user-slash"></i>

            <p>
                Select a patient before viewing
                or replying to patient queries.
            </p>

        </div>

    <?php endif; ?>

</div>

</div>

</div>

</main>

</div>

</div>

<?php
mysqli_close($conn);
?>

<?php if ($selectedPatient): ?>

<script>
document.addEventListener(
    "DOMContentLoaded",
    function () {

        const modal =
            document.getElementById(
                "patientModal"
            );

        if (modal) {

            modal.classList.add("active");

            modal.setAttribute(
                "aria-hidden",
                "false"
            );

            document.body.style.overflow =
                "hidden";

        }

    }
);
</script>

<?php endif; ?>

<script src="js/doctor_dashboard.js"></script>

<script src="js/doctor_patients.js"></script>

</body>

</html>
