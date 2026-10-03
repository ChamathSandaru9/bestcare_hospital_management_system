<?php
session_start();

/* Enable MySQLi Exceptions */

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

/* Prevent Protected Page Caching */

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header("Pragma: no-cache");
header("Expires: 0");

/* Patient Authentication */

if (
    !isset(
        $_SESSION["user_id"],
        $_SESSION["role_id"]
    )
) {
    header("Location: login.php");
    exit();
}

/* User Roles 3 = Patient */

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: login.php");
    exit();
}

$userId =
    (int) $_SESSION["user_id"];

/* CSRF Token */

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );

}

/* Output Helper */

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}

/*  Load Active Patient */

$patientSql = "
    SELECT
        p.patient_id,
        p.user_id,
        p.first_name,
        p.last_name,
        p.gender,
        p.dob,
        p.phone,
        p.email,
        p.blood_group,
        p.emergency_contact,
        p.created_at,
        u.username,
        u.status
    FROM patients p
    INNER JOIN users u
        ON p.user_id = u.user_id
    WHERE p.user_id = ?
      AND u.status = 'Active'
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
    $userId
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

    mysqli_close($conn);

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();

}

$patientId =
    (int) $patient["patient_id"];

$fullName =
    $patient["first_name"] .
    " " .
    $patient["last_name"];

$patientInitial =
    strtoupper(
        substr(
            $patient["first_name"],
            0,
            1
        )
    );

/*  Load Active Doctors */

$availableDoctors = [];

$doctorListSql = "
    SELECT
        d.doctor_id,
        d.first_name,
        d.last_name,
        d.specialization,
        d.availability,
        dep.department_name
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    INNER JOIN departments dep
        ON d.department_id =
           dep.department_id
    WHERE u.status = 'Active'
    ORDER BY
        d.first_name ASC,
        d.last_name ASC
";

$doctorListResult =
    mysqli_query(
        $conn,
        $doctorListSql
    );

while (
    $doctor =
        mysqli_fetch_assoc(
            $doctorListResult
        )
) {
    $availableDoctors[] =
        $doctor;
}

/* Load Medical Records */

$medicalRecords = [];

$medicalRecordSql = "
    SELECT
        mr.record_id,
        mr.patient_id,
        mr.doctor_id,
        mr.diagnosis,
        mr.treatment,
        mr.notes,
        mr.created_at,

        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name,
        d.specialization,

        dep.department_name

    FROM medical_records mr

    INNER JOIN doctors d
        ON mr.doctor_id =
           d.doctor_id

    INNER JOIN departments dep
        ON d.department_id =
           dep.department_id

    WHERE mr.patient_id = ?

    ORDER BY
        mr.created_at DESC,
        mr.record_id DESC
";

$medicalRecordStmt =
    mysqli_prepare(
        $conn,
        $medicalRecordSql
    );

mysqli_stmt_bind_param(
    $medicalRecordStmt,
    "i",
    $patientId
);

mysqli_stmt_execute(
    $medicalRecordStmt
);

$medicalRecordResult =
    mysqli_stmt_get_result(
        $medicalRecordStmt
    );

while (
    $record =
        mysqli_fetch_assoc(
            $medicalRecordResult
        )
) {

    $record["prescriptions"] = [];

    $medicalRecords[
        (int) $record["record_id"]
    ] = $record;

}

mysqli_stmt_close(
    $medicalRecordStmt
);

/* Load Prescriptions */

if (!empty($medicalRecords)) {

    $prescriptionSql = "
        SELECT
            p.prescription_id,
            p.record_id,
            p.medicine,
            p.dosage,
            p.duration,
            p.instructions,
            p.created_at

        FROM prescriptions p

        INNER JOIN medical_records mr
            ON p.record_id =
               mr.record_id

        WHERE mr.patient_id = ?

        ORDER BY
            p.created_at DESC,
            p.prescription_id DESC
    ";

    $prescriptionStmt =
        mysqli_prepare(
            $conn,
            $prescriptionSql
        );

    mysqli_stmt_bind_param(
        $prescriptionStmt,
        "i",
        $patientId
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

        $recordId =
            (int) $prescription[
                "record_id"
            ];

        if (
            isset(
                $medicalRecords[
                    $recordId
                ]
            )
        ) {

            $medicalRecords[
                $recordId
            ]["prescriptions"][] =
                $prescription;

        }

    }

    mysqli_stmt_close(
        $prescriptionStmt
    );

}

/* Load Laboratory Reports */

$labReports = [];

$labReportSql = "
    SELECT
        lr.report_id,
        lr.patient_id,
        lr.doctor_id,
        lr.test_name,
        lr.report_date,
        lr.report_file,
        lr.created_at,

        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name,
        d.specialization

    FROM laboratory_reports lr

    INNER JOIN doctors d
        ON lr.doctor_id =
           d.doctor_id

    WHERE lr.patient_id = ?

    ORDER BY
        lr.report_date DESC,
        lr.created_at DESC,
        lr.report_id DESC
";

$labReportStmt =
    mysqli_prepare(
        $conn,
        $labReportSql
    );

mysqli_stmt_bind_param(
    $labReportStmt,
    "i",
    $patientId
);

mysqli_stmt_execute(
    $labReportStmt
);

$labReportResult =
    mysqli_stmt_get_result(
        $labReportStmt
    );

while (
    $report =
        mysqli_fetch_assoc(
            $labReportResult
        )
) {

    $labReports[] =
        $report;

}

mysqli_stmt_close(
    $labReportStmt
);

/*  Load Bills */

$bills = [];

$billSql = "
    SELECT
        b.bill_id,
        b.patient_id,
        b.appointment_id,
        b.consultation_fee,
        b.laboratory_fee,
        b.medicine_fee,
        b.other_fee,
        b.total_amount,
        b.payment_status,
        b.created_at,

        a.appointment_date,
        a.appointment_time,

        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name

    FROM bills b

    INNER JOIN appointments a
        ON b.appointment_id =
           a.appointment_id

    INNER JOIN doctors d
        ON a.doctor_id =
           d.doctor_id

    WHERE b.patient_id = ?

    ORDER BY
        b.created_at DESC,
        b.bill_id DESC
";

$billStmt =
    mysqli_prepare(
        $conn,
        $billSql
    );

mysqli_stmt_bind_param(
    $billStmt,
    "i",
    $patientId
);

mysqli_stmt_execute(
    $billStmt
);

$billResult =
    mysqli_stmt_get_result(
        $billStmt
    );

while (
    $bill =
        mysqli_fetch_assoc(
            $billResult
        )
) {

    $bills[] =
        $bill;

}

mysqli_stmt_close(
    $billStmt
);

/*  Load Patient Queries */

$patientQueries = [];

$patientQuerySql = "
    SELECT
        pq.query_id,
        pq.patient_id,
        pq.doctor_id,
        pq.subject,
        pq.message,
        pq.reply,
        pq.status,
        pq.created_at,
        pq.replied_at,

        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name,
        d.specialization

    FROM patient_queries pq

    INNER JOIN doctors d
        ON pq.doctor_id =
           d.doctor_id

    WHERE pq.patient_id = ?

    ORDER BY
        pq.created_at DESC,
        pq.query_id DESC
";

$patientQueryStmt =
    mysqli_prepare(
        $conn,
        $patientQuerySql
    );

mysqli_stmt_bind_param(
    $patientQueryStmt,
    "i",
    $patientId
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

    <link rel="stylesheet" href="css/patient_dashboard.css">

    <link rel="stylesheet" href="css/patient_records.css">

</head>

<body>

<div class="patient-layout">

    <!-- Sidebar -->

    <aside
        class="patient-sidebar"
        id="patientSidebar"
    >

        <div class="sidebar-logo">

            <img
                src="image/logo.png"
                alt="BestCare Hospital Logo"
            >

            <div>

                <h2>
                    BestCare
                </h2>

                <span>
                    Patient Portal
                </span>

            </div>

        </div>

        <nav class="sidebar-navigation">

            <a href="patient_dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>
                    Dashboard
                </span>

            </a>

            <a href="patient_appointments.php">

                <i class="fa-solid fa-calendar-check"></i>

                <span>
                    Appointments
                </span>

            </a>

            <a
                href="patient_records.php"
                class="active"
            >

                <i class="fa-solid fa-notes-medical"></i>

                <span>
                    Records & Queries
                </span>

            </a>

        </nav>

        <div class="sidebar-bottom">

            <a
                href="logout.php"
                class="logout-link"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>
                    Logout
                </span>

            </a>

        </div>

    </aside>

    <!-- Main Area -->

    <div class="patient-main">

        <header class="patient-topbar">

            <button
                type="button"
                class="sidebar-toggle"
                id="sidebarToggle"
                aria-label="Open navigation menu"
            >

                <i class="fa-solid fa-bars"></i>

            </button>

            <div class="topbar-title">

                <h1>
                    Records & Queries
                </h1>

                <p>
                    View your healthcare information,
                    bills, laboratory reports, and
                    doctor replies.
                </p>

            </div>

            <div class="patient-account">

                <div class="patient-avatar">

                    <?php
                    echo e(
                        $patientInitial
                    );
                    ?>

                </div>

                <div class="patient-account-text">

                    <strong>

                        <?php
                        echo e(
                            $fullName
                        );
                        ?>

                    </strong>

                    <span>
                        Patient
                    </span>

                </div>

            </div>

        </header>

        <!-- Main Content -->

        <main class="records-content">

            <!-- Success Message -->

            <?php if (
                isset(
                    $_SESSION["query_success"]
                )
            ): ?>

                <div
                    class="records-message success-message"
                >

                    <i class="fa-solid fa-circle-check"></i>

                    <span>

                        <?php
                        echo e(
                            $_SESSION[
                                "query_success"
                            ]
                        );

                        unset(
                            $_SESSION[
                                "query_success"
                            ]
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>

            <!-- Error Message -->

            <?php if (
                isset(
                    $_SESSION["query_error"]
                )
            ): ?>

                <div
                    class="records-message error-message"
                >

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>

                        <?php
                        echo e(
                            $_SESSION[
                                "query_error"
                            ]
                        );

                        unset(
                            $_SESSION[
                                "query_error"
                            ]
                        );
                        ?>

                    </span>

                </div>

            <?php endif; ?>

            <!-- Introduction -->

            <section class="records-intro">

                <div>

                    <span class="records-label">

                        Patient Information

                    </span>

                    <h2>

                        Your Medical Information
                        in One Place

                    </h2>

                    <p>

                        Review medical records,
                        prescribed medicines,
                        laboratory reports, bills,
                        and communication with
                        your selected doctor.

                    </p>

                    <div class="records-patient-summary">

                        <span>

                            <i class="fa-solid fa-id-card"></i>

                            Patient ID:
                            BC-<?php
                            echo str_pad(
                                (string)
                                $patientId,
                                5,
                                "0",
                                STR_PAD_LEFT
                            );
                            ?>

                        </span>

                        <span>

                            <i class="fa-solid fa-droplet"></i>

                            Blood Group:
                            <?php
                            echo e(
                                $patient[
                                    "blood_group"
                                ]
                            );
                            ?>

                        </span>

                    </div>

                </div>

                <div class="records-intro-icon">

                    <i class="fa-solid fa-file-waveform"></i>

                </div>

            </section>

            <!-- Summary Cards -->

            <section class="records-summary-grid">

                <article class="records-summary-card">

                    <div class="records-summary-icon">

                        <i class="fa-solid fa-notes-medical"></i>

                    </div>

                    <div>

                        <span>
                            Medical Records
                        </span>

                        <strong>

                            <?php
                            echo count(
                                $medicalRecords
                            );
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="records-summary-card">

                    <div class="records-summary-icon">

                        <i class="fa-solid fa-file-pdf"></i>

                    </div>

                    <div>

                        <span>
                            Laboratory Reports
                        </span>

                        <strong>

                            <?php
                            echo count(
                                $labReports
                            );
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="records-summary-card">

                    <div class="records-summary-icon">

                        <i class="fa-solid fa-file-invoice-dollar"></i>

                    </div>

                    <div>

                        <span>
                            Bills
                        </span>

                        <strong>

                            <?php
                            echo count(
                                $bills
                            );
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="records-summary-card">

                    <div class="records-summary-icon">

                        <i class="fa-solid fa-comments"></i>

                    </div>

                    <div>

                        <span>
                            Queries
                        </span>

                        <strong>

                            <?php
                            echo count(
                                $patientQueries
                            );
                            ?>

                        </strong>

                    </div>

                </article>

            </section>

            <!-- Page Navigation -->

            <nav class="records-tabs">

                <a href="#medicalRecords">

                    <i class="fa-solid fa-notes-medical"></i>

                    Medical Records

                </a>

                <a href="#labReports">

                    <i class="fa-solid fa-file-pdf"></i>

                    Laboratory Reports

                </a>

                <a href="#bills">

                    <i class="fa-solid fa-file-invoice-dollar"></i>

                    Bills

                </a>

                <a href="#patientQueries">

                    <i class="fa-solid fa-message"></i>

                    Patient Queries

                </a>

            </nav>
<!--  Medical Records -->
<section class="records-section" id="medicalRecords">

<div class="records-section-header">

        <div>

            <span>
                Health History
            </span>

            <h2>
                Medical Records
            </h2>

            <p>
                Review diagnoses, treatment details,
                doctor notes, and prescribed medicines.
            </p>

        </div>

        <i class="fa-solid fa-notes-medical"></i>

    </div>

    <?php if (
        empty($medicalRecords)
    ): ?>

        <div class="records-empty-state">

            <i class="fa-solid fa-notes-medical"></i>

            <h3>
                No medical records available
            </h3>

            <p>
                Your medical records will appear
                after a doctor adds consultation details.
            </p>

        </div>

    <?php else: ?>

        <div class="medical-record-list">

            <?php foreach (
                $medicalRecords
                as $record
            ): ?>

                <article class="medical-record-card">

                    <div class="medical-record-header">

                        <div>

                            <span>

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

                            </span>

                            <h3>

                                <?php
                                echo e(
                                    $record[
                                        "diagnosis"
                                    ]
                                );
                                ?>

                            </h3>

                        </div>

                        <time>

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $record[
                                        "created_at"
                                    ]
                                )
                            );
                            ?>

                        </time>

                    </div>

                    <!-- Doctor Details -->

                    <div class="record-doctor-details">

                        <span>

                            <i class="fa-solid fa-user-doctor"></i>

                            Dr.
                            <?php
                            echo e(
                                $record[
                                    "doctor_first_name"
                                ] .
                                " " .
                                $record[
                                    "doctor_last_name"
                                ]
                            );
                            ?>

                        </span>

                        <span>

                            <i class="fa-solid fa-hospital"></i>

                            <?php
                            echo e(
                                $record[
                                    "department_name"
                                ]
                            );
                            ?>

                        </span>

                        <span>

                            <i class="fa-solid fa-stethoscope"></i>

                            <?php
                            echo e(
                                $record[
                                    "specialization"
                                ]
                            );
                            ?>

                        </span>

                    </div>

                    <!-- Medical Details -->

                    <div class="record-details-grid">

                        <div class="record-detail-box">

                            <span>
                                Diagnosis
                            </span>

                            <p>

                                <?php
                                echo nl2br(
                                    e(
                                        $record[
                                            "diagnosis"
                                        ]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                        <div class="record-detail-box">

                            <span>
                                Treatment
                            </span>

                            <p>

                                <?php
                                echo nl2br(
                                    e(
                                        $record[
                                            "treatment"
                                        ]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                        <?php if (
                            !empty(
                                $record["notes"]
                            )
                        ): ?>

                            <div
                                class="record-detail-box record-notes-box"
                            >

                                <span>
                                    Doctor Notes
                                </span>

                                <p>

                                    <?php
                                    echo nl2br(
                                        e(
                                            $record[
                                                "notes"
                                            ]
                                        )
                                    );
                                    ?>

                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- Prescriptions -->

                    <div class="prescription-section">

                        <h4>

                            <i class="fa-solid fa-pills"></i>

                            Prescriptions

                        </h4>

                        <?php if (
                            empty(
                                $record[
                                    "prescriptions"
                                ]
                            )
                        ): ?>

                            <p class="no-prescription-text">

                                No medicines were prescribed
                                for this medical record.

                            </p>

                        <?php else: ?>

                            <div class="prescription-table-wrapper">

                                <table class="prescription-table">

                                    <thead>

                                        <tr>

                                            <th>
                                                Medicine
                                            </th>

                                            <th>
                                                Dosage
                                            </th>

                                            <th>
                                                Duration
                                            </th>

                                            <th>
                                                Instructions
                                            </th>

                                            <th>
                                                Prescribed On
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php foreach (
                                            $record[
                                                "prescriptions"
                                            ]
                                            as $prescription
                                        ): ?>

                                            <tr>

                                                <td>

                                                    <strong>

                                                        <?php
                                                        echo e(
                                                            $prescription[
                                                                "medicine"
                                                            ]
                                                        );
                                                        ?>

                                                    </strong>

                                                </td>

                                                <td>

                                                    <?php
                                                    echo e(
                                                        $prescription[
                                                            "dosage"
                                                        ]
                                                    );
                                                    ?>

                                                </td>

                                                <td>

                                                    <?php
                                                    echo e(
                                                        $prescription[
                                                            "duration"
                                                        ]
                                                    );
                                                    ?>

                                                </td>

                                                <td>

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

                                                </td>

                                                <td>

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

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>
<!-- Laboratory Reports -->

<section
    class="records-section"
    id="labReports"
>

    <div class="records-section-header">

        <div>

            <span>
                Test Results
            </span>

            <h2>
                Laboratory Reports
            </h2>

            <p>
                View PDF laboratory reports
                uploaded by your doctor.
            </p>

        </div>

        <i class="fa-solid fa-file-pdf"></i>

    </div>

    <?php if (
        empty($labReports)
    ): ?>

        <div class="records-empty-state">

            <i class="fa-solid fa-file-circle-xmark"></i>

            <h3>
                No laboratory reports available
            </h3>

            <p>
                Your laboratory reports will appear
                here after they are uploaded.
            </p>

        </div>

    <?php else: ?>

        <div class="lab-report-grid">

            <?php foreach (
                $labReports
                as $report
            ): ?>

                <article class="lab-report-card">

                    <div class="lab-report-icon">

                        <i class="fa-solid fa-file-pdf"></i>

                    </div>

                    <div class="lab-report-content">

                        <span>
                            Laboratory Report
                        </span>

                        <h3>

                            <?php
                            echo e(
                                $report[
                                    "test_name"
                                ]
                            );
                            ?>

                        </h3>

                        <p>

                            Dr.
                            <?php
                            echo e(
                                $report[
                                    "doctor_first_name"
                                ] .
                                " " .
                                $report[
                                    "doctor_last_name"
                                ]
                            );
                            ?>

                        </p>

                        <div class="lab-report-meta">

                            <span>

                                <i class="fa-solid fa-calendar-day"></i>

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

                            </span>

                            <span>

                                <i class="fa-solid fa-stethoscope"></i>

                                <?php
                                echo e(
                                    $report[
                                        "specialization"
                                    ]
                                );
                                ?>

                            </span>

                        </div>

                        <?php if (
                            !empty(
                                $report[
                                    "report_file"
                                ]
                            )
                        ): ?>

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
                                class="view-report-button"
                            >

                                <i class="fa-solid fa-eye"></i>

                                View PDF Report

                            </a>

                        <?php else: ?>

                            <span class="report-unavailable">

                                Report file is not available

                            </span>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<!-- Bills -->

<section
    class="records-section"
    id="bills"
>

    <div class="records-section-header">

        <div>

            <span>
                Payment Information
            </span>

            <h2>
                My Bills
            </h2>

            <p>
                Review appointment charges
                and payment status.
            </p>

        </div>

        <i class="fa-solid fa-file-invoice-dollar"></i>

    </div>

    <?php if (
        empty($bills)
    ): ?>

        <div class="records-empty-state">

            <i class="fa-solid fa-file-invoice"></i>

            <h3>
                No bills available
            </h3>

            <p>
                Bills created for your appointments
                will appear here.
            </p>

        </div>

    <?php else: ?>

        <div class="bill-table-wrapper">

            <table class="bill-table">

                <thead>

                    <tr>

                        <th>
                            Bill
                        </th>

                        <th>
                            Doctor
                        </th>

                        <th>
                            Appointment
                        </th>

                        <th>
                            Consultation
                        </th>

                        <th>
                            Laboratory
                        </th>

                        <th>
                            Medicine
                        </th>

                        <th>
                            Other
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach (
                        $bills
                        as $bill
                    ): ?>

                        <tr>

                            <td>

                                #<?php
                                echo str_pad(
                                    (string)
                                    $bill[
                                        "bill_id"
                                    ],
                                    4,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                ?>

                            </td>

                            <td>

                                Dr.
                                <?php
                                echo e(
                                    $bill[
                                        "doctor_first_name"
                                    ] .
                                    " " .
                                    $bill[
                                        "doctor_last_name"
                                    ]
                                );
                                ?>

                            </td>

                            <td>

                                <span>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $bill[
                                                "appointment_date"
                                            ]
                                        )
                                    );
                                    ?>

                                </span>

                                <small>

                                    <?php
                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $bill[
                                                "appointment_time"
                                            ]
                                        )
                                    );
                                    ?>

                                </small>

                            </td>

                            <td>

                                LKR
                                <?php
                                echo number_format(
                                    (float)
                                    $bill[
                                        "consultation_fee"
                                    ],
                                    2
                                );
                                ?>

                            </td>

                            <td>

                                LKR
                                <?php
                                echo number_format(
                                    (float)
                                    $bill[
                                        "laboratory_fee"
                                    ],
                                    2
                                );
                                ?>

                            </td>

                            <td>

                                LKR
                                <?php
                                echo number_format(
                                    (float)
                                    $bill[
                                        "medicine_fee"
                                    ],
                                    2
                                );
                                ?>

                            </td>

                            <td>

                                LKR
                                <?php
                                echo number_format(
                                    (float)
                                    $bill[
                                        "other_fee"
                                    ],
                                    2
                                );
                                ?>

                            </td>

                            <td>

                                <strong>

                                    LKR
                                    <?php
                                    echo number_format(
                                        (float)
                                        $bill[
                                            "total_amount"
                                        ],
                                        2
                                    );
                                    ?>

                                </strong>

                            </td>

                            <td>

                                <span
                                    class="bill-status status-<?php
                                    echo strtolower(
                                        e(
                                            $bill[
                                                "payment_status"
                                            ]
                                        )
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo e(
                                        $bill[
                                            "payment_status"
                                        ]
                                    );
                                    ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>
<!-- Patient Queries -->

<section
    class="records-section"
    id="patientQueries"
>

    <div class="records-section-header">

        <div>

            <span>
                Communication
            </span>

            <h2>
                Patient Queries
            </h2>

            <p>
                Send a question to a doctor and
                review previous replies.
            </p>

        </div>

        <i class="fa-solid fa-message"></i>

    </div>

    <div class="query-layout">

        <!--  New Query Form -->

        <article class="query-form-card">

            <div class="query-card-header">

                <div>

                    <span>
                        New Message
                    </span>

                    <h3>
                        Send a Query
                    </h3>

                </div>

                <i class="fa-solid fa-paper-plane"></i>

            </div>

            <?php if (
                empty($availableDoctors)
            ): ?>

                <div class="records-empty-state small-empty-state">

                    <i class="fa-solid fa-user-doctor"></i>

                    <h3>
                        No doctors available
                    </h3>

                    <p>
                        A query cannot be submitted until
                        an active doctor is available.
                    </p>

                </div>

            <?php else: ?>

                <form
                    action="db_process/patient_records_process.php"
                    method="POST"
                    id="patientQueryForm"
                    class="patient-query-form"
                    novalidate
                >

                    <input
                        type="hidden"
                        name="action"
                        value="submit_query"
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

                    <!-- Doctor -->

                    <div class="query-form-group">

                        <label for="query_doctor">

                            Doctor

                        </label>

                        <select
                            id="query_doctor"
                            name="doctor_id"
                            required
                        >

                            <option value="">

                                Select a Doctor

                            </option>

                            <?php foreach (
                                $availableDoctors
                                as $availableDoctor
                            ): ?>

                                <option
                                    value="<?php
                                    echo (int)
                                        $availableDoctor[
                                            "doctor_id"
                                        ];
                                    ?>"
                                >

                                    Dr.
                                    <?php
                                    echo e(
                                        $availableDoctor[
                                            "first_name"
                                        ] .
                                        " " .
                                        $availableDoctor[
                                            "last_name"
                                        ]
                                    );
                                    ?>

                                    —

                                    <?php
                                    echo e(
                                        $availableDoctor[
                                            "specialization"
                                        ]
                                    );
                                    ?>

                                    <?php if (
                                        !empty(
                                            $availableDoctor[
                                                "department_name"
                                            ]
                                        )
                                    ): ?>

                                        (
                                        <?php
                                        echo e(
                                            $availableDoctor[
                                                "department_name"
                                            ]
                                        );
                                        ?>
                                        )

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="field-error"></small>

                    </div>

                    <!-- Subject -->

                    <div class="query-form-group">

                        <label for="query_subject">

                            Subject

                        </label>

                        <input
                            type="text"
                            id="query_subject"
                            name="subject"
                            minlength="3"
                            maxlength="150"
                            placeholder="Enter the query subject"
                            required
                        >

                        <small class="field-error"></small>

                    </div>

                    <!-- Message -->

                    <div class="query-form-group">

                        <label for="query_message">

                            Message

                        </label>

                        <textarea
                            id="query_message"
                            name="message"
                            rows="6"
                            minlength="10"
                            maxlength="2000"
                            placeholder="Write your question for the doctor"
                            required
                        ></textarea>

                        <small class="field-error"></small>

                    </div>

                    <button
                        type="submit"
                        class="query-submit-button"
                    >

                        <i class="fa-solid fa-paper-plane"></i>

                        Submit Query

                    </button>

                </form>

            <?php endif; ?>

        </article>

        <!-- Query History -->

        <article class="query-history-card">

            <div class="query-card-header">

                <div>

                    <span>
                        Previous Messages
                    </span>

                    <h3>
                        Query History
                    </h3>

                </div>

                <i class="fa-solid fa-clock-rotate-left"></i>

            </div>

            <?php if (
                empty($patientQueries)
            ): ?>

                <div class="records-empty-state small-empty-state">

                    <i class="fa-regular fa-message"></i>

                    <h3>
                        No queries submitted
                    </h3>

                    <p>
                        Your submitted questions and
                        doctor replies will appear here.
                    </p>

                </div>

            <?php else: ?>

                <div class="query-list">

                    <?php foreach (
                        $patientQueries
                        as $query
                    ): ?>

                        <article class="query-item">

                            <div class="query-item-header">

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

                            <div class="query-doctor-info">

                                <i class="fa-solid fa-user-doctor"></i>

                                <span>

                                    Dr.
                                    <?php
                                    echo e(
                                        $query[
                                            "doctor_first_name"
                                        ] .
                                        " " .
                                        $query[
                                            "doctor_last_name"
                                        ]
                                    );
                                    ?>

                                </span>

                                <?php if (
                                    !empty(
                                        $query[
                                            "specialization"
                                        ]
                                    )
                                ): ?>

                                    <small>

                                        <?php
                                        echo e(
                                            $query[
                                                "specialization"
                                            ]
                                        );
                                        ?>

                                    </small>

                                <?php endif; ?>

                            </div>

                            <div class="query-message">

                                <span>
                                    Your Message
                                </span>

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
                                    $query[
                                        "reply"
                                    ]
                                )
                            ): ?>

                                <div class="query-reply">

                                    <span>

                                        Doctor Reply

                                    </span>

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

                                    <?php if (
                                        !empty(
                                            $query[
                                                "replied_at"
                                            ]
                                        )
                                    ): ?>

                                        <time>

                                            Replied:
                                            <?php
                                            echo date(
                                                "d M Y, h:i A",
                                                strtotime(
                                                    $query[
                                                        "replied_at"
                                                    ]
                                                )
                                            );
                                            ?>

                                        </time>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <div class="query-waiting">

                                    <i class="fa-regular fa-clock"></i>

                                    Waiting for a reply

                                </div>

                            <?php endif; ?>

                            <time class="query-submitted-time">

                                Submitted:
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

                            </time>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </article>

    </div>

</section>

        </main>

    </div>

</div>

<?php
mysqli_close($conn);
?>

<script src="js/patient_dashboard.js"></script>

<script src="js/patient_records.js"></script>

</body>

</html>

    