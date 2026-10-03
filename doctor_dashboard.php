<?php
session_start();

//    Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

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

// Roles 2 = Doctor

if ((int) $_SESSION["role_id"] !== 2) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

//    Load Doctor Information

$doctorSql = "
    SELECT
        d.doctor_id,
        d.department_id,
        d.first_name,
        d.last_name,
        d.phone,
        d.specialization,
        d.availability,
        d.created_at,
        dep.department_name,
        u.username,
        u.email,
        u.status
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    INNER JOIN departments dep
        ON d.department_id = dep.department_id
    WHERE d.user_id = ?
    LIMIT 1
";

$doctorStmt = mysqli_prepare(
    $conn,
    $doctorSql
);

mysqli_stmt_bind_param(
    $doctorStmt,
    "i",
    $userId
);

mysqli_stmt_execute($doctorStmt);

$doctorResult = mysqli_stmt_get_result(
    $doctorStmt
);

$doctor = mysqli_fetch_assoc(
    $doctorResult
);

mysqli_stmt_close($doctorStmt);

if (!$doctor) {
    session_destroy();

    header("Location: ../login.php");
    exit();
}

$doctorId = (int) $doctor["doctor_id"];

$fullName =
    $doctor["first_name"] .
    " " .
    $doctor["last_name"];

$doctorInitial = strtoupper(
    substr(
        $doctor["first_name"],
        0,
        1
    )
);

//    Dashboard Count Helper

function getDoctorCount(
    mysqli $conn,
    string $sql,
    int $doctorId
): int {
    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $doctorId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result(
        $stmt
    );

    $row = mysqli_fetch_assoc(
        $result
    );

    mysqli_stmt_close($stmt);

    return (int) ($row["total"] ?? 0);
}


//    Dashboard Summary Counts

$pendingAppointmentCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM appointments
        WHERE doctor_id = ?
          AND status = 'Pending'
    ",
    $doctorId
);

$todayAppointmentCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = CURDATE()
          AND status IN (
              'Pending',
              'Approved'
          )
    ",
    $doctorId
);

$completedAppointmentCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM appointments
        WHERE doctor_id = ?
          AND status = 'Completed'
    ",
    $doctorId
);

$medicalRecordCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM medical_records
        WHERE doctor_id = ?
    ",
    $doctorId
);

//    Additional Summary Counts

$labReportCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM laboratory_reports
        WHERE doctor_id = ?
    ",
    $doctorId
);

$answeredQueryCount = getDoctorCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM patient_queries
        WHERE doctor_id = ?
          AND status = 'Answered'
    ",
    $doctorId
);

mysqli_close($conn);

//    Display Values

$memberSince = !empty(
    $doctor["created_at"]
)
    ? date(
        "d M Y",
        strtotime(
            $doctor["created_at"]
        )
    )
    : "Not available";
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

</head>

<body>

<div class="doctor-layout">

    <!-- Sidebar -->

    <aside class="doctor-sidebar" id="doctorSidebar">

        <div class="doctor-sidebar-logo">

            <img src="image/logo.png" alt="BestCare Hospital Logo">

            <div>
                <h2>BestCare</h2>
                <span>Doctor Portal</span>
            </div>

        </div>

        <nav class="doctor-navigation">

            <a href="doctor_dashboard.php" class="active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="doctor_appointments.php">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Manage Appointments</span>
            </a>

            <a href="doctor_patients.php">
                <i class="fa-solid fa-user-injured"></i>
                <span>Patients</span>
            </a>

        </nav>

        <div class="doctor-sidebar-bottom">

            <a
                href="logout.php"
                class="doctor-logout-link"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>

        </div>

    </aside>

    <!-- Main Area -->

    <div class="doctor-main">

        <!-- Top Bar -->

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

                <h1>Doctor Dashboard</h1>

                <p>
                    View your profile and healthcare activity summary.
                </p>

            </div>

            <div class="doctor-account">

                <div class="doctor-avatar">
                    <?php
                    echo htmlspecialchars(
                        $doctorInitial
                    );
                    ?>
                </div>

                <div class="doctor-account-text">

                    <strong>
                        Dr.
                        <?php
                        echo htmlspecialchars(
                            $fullName
                        );
                        ?>
                    </strong>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $doctor[
                                "specialization"
                            ]
                        );
                        ?>
                    </span>

                </div>

            </div>

        </header>

        <!-- Dashboard Content -->

        <main class="doctor-content">

            <!-- Messages -->

            <?php if (
                isset($_SESSION["doctor_success"])
            ): ?>

                <div class="doctor-message success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
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
                isset($_SESSION["doctor_error"])
            ): ?>

                <div class="doctor-message error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
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

            <!-- Welcome Banner -->

            <section class="doctor-welcome-banner">

                <div>

                    <span class="doctor-welcome-label">
                        Doctor Portal
                    </span>

                    <h2>
                        Welcome , Dr.
                        <?php
                        echo htmlspecialchars(
                            $doctor["first_name"]
                        );
                        ?>!
                    </h2>

                    <p>
                        Review your appointment summary and access
                        patient care tools from the Doctor Portal.
                    </p>

                    <div class="doctor-welcome-details">

                        <span>
                            <i class="fa-solid fa-hospital"></i>

                            <?php
                            echo htmlspecialchars(
                                $doctor[
                                    "department_name"
                                ]
                            );
                            ?>
                        </span>

                        <span>
                            <i class="fa-solid fa-stethoscope"></i>

                            <?php
                            echo htmlspecialchars(
                                $doctor[
                                    "specialization"
                                ]
                            );
                            ?>
                        </span>

                        <span>
                            <i class="fa-solid fa-clock"></i>

                            <?php
                            echo htmlspecialchars(
                                $doctor[
                                    "availability"
                                ]
                            );
                            ?>
                        </span>

                    </div>

                </div>

                <div class="doctor-welcome-icon">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>

            </section>

            <!-- Summary Cards -->

            <section class="doctor-summary-grid">

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon pending-icon">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>

                    <div>

                        <span>Pending Appointments</span>

                        <strong>
                            <?php
                            echo $pendingAppointmentCount;
                            ?>
                        </strong>

                    </div>

                </article>

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon today-icon">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>

                    <div>

                        <span>Today's Appointments</span>

                        <strong>
                            <?php
                            echo $todayAppointmentCount;
                            ?>
                        </strong>

                    </div>

                </article>

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon completed-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div>

                        <span>Completed Appointments</span>

                        <strong>
                            <?php
                            echo $completedAppointmentCount;
                            ?>
                        </strong>

                    </div>

                </article>

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon records-icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>

                    <div>

                        <span>Medical Records</span>

                        <strong>
                            <?php
                            echo $medicalRecordCount;
                            ?>
                        </strong>

                    </div>

                </article>

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon reports-icon">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>

                    <div>

                        <span>Lab Reports</span>

                        <strong>
                            <?php
                            echo $labReportCount;
                            ?>
                        </strong>

                    </div>

                </article>

                <article class="doctor-summary-card">

                    <div class="doctor-summary-icon queries-icon">
                        <i class="fa-solid fa-message"></i>
                    </div>

                    <div>

                        <span>Answered Queries</span>

                        <strong>
                            <?php
                            echo $answeredQueryCount;
                            ?>
                        </strong>

                    </div>

                </article>

            </section>

            <!-- Quick Actions -->

            <section class="doctor-panel">

                <div class="doctor-panel-header">

                    <div>
                        <span>Shortcuts</span>
                        <h2>Quick Actions</h2>
                    </div>

                    <i class="fa-solid fa-bolt"></i>

                </div>

                <div class="doctor-quick-actions">

                    <a href="doctor_appointments.php">

                        <i class="fa-solid fa-calendar-check"></i>

                        <div>

                            <strong>
                                Manage Appointments
                            </strong>

                            <span>
                                View and complete your appointments
                            </span>

                        </div>

                    </a>

                    <a href="doctor_patients.php">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <div>

                            <strong>
                                Search Patients
                            </strong>

                            <span>
                                Find patient details and records
                            </span>

                        </div>

                    </a>

                    <a href="doctor_patients.php">

                        <i class="fa-solid fa-notes-medical"></i>

                        <div>

                            <strong>
                                Add Medical Record
                            </strong>

                            <span>
                                Record diagnosis and treatment
                            </span>

                        </div>

                    </a>

                    <a href="doctor_patients.php">

                        <i class="fa-solid fa-file-pdf"></i>

                        <div>

                            <strong>
                                Upload Lab Report
                            </strong>

                            <span>
                                Upload a patient PDF test report
                            </span>

                        </div>

                    </a>

                </div>

            </section>

            <!--  Doctor Profile -->

            <section class="doctor-profile-section">

                <div class="doctor-section-title">

                    <span>Doctor Information</span>

                    <h2>My Profile</h2>

                    <p>
                        Review your account and professional details.
                    </p>

                </div>

                <div class="doctor-profile-grid">

                    <article class="doctor-profile-card">

                        <div class="doctor-profile-card-header">

                            <div>

                                <span>Personal Details</span>

                                <h3>Account Information</h3>

                            </div>

                            <i class="fa-solid fa-id-card"></i>

                        </div>

                        <div class="doctor-information-grid">

                            <div class="doctor-information-item">

                                <span>Doctor ID</span>

                                <strong>
                                    DR-<?php
                                    echo str_pad(
                                        (string) $doctorId,
                                        5,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Full Name</span>

                                <strong>
                                    Dr.
                                    <?php
                                    echo htmlspecialchars(
                                        $fullName
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Username</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor[
                                            "username"
                                        ]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Email</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor["email"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Phone</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor["phone"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Status</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor["status"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Member Since</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $memberSince
                                    );
                                    ?>
                                </strong>

                            </div>

                        </div>

                    </article>

                    <article class="doctor-profile-card">

                        <div class="doctor-profile-card-header">

                            <div>

                                <span>Professional Details</span>

                                <h3>Medical Information</h3>

                            </div>

                            <i class="fa-solid fa-stethoscope"></i>

                        </div>

                        <div class="doctor-information-grid">

                            <div class="doctor-information-item">

                                <span>Department</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor[
                                            "department_name"
                                        ]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="doctor-information-item">

                                <span>Specialization</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor[
                                            "specialization"
                                        ]
                                    );
                                    ?>
                                </strong>

                            </div>
                            <div class="doctor-information-item">

                                <span>Availability</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $doctor[
                                            "availability"
                                        ]
                                    );
                                    ?>
                                </strong>

                            </div>

                        </div>

                    </article>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="js/doctor_dashboard.js"></script>

</body>
</html>