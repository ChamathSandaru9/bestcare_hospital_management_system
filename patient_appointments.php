<?php
session_start();

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

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

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

/* CSRF Token */

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));
}

/* Load Patient */

$patientSql = "
    SELECT
        patient_id,
        first_name,
        last_name
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
    session_destroy();

    header("Location: login.php");
    exit();
}

$patientId = (int) $patient["patient_id"];

$fullName =
    $patient["first_name"] .
    " " .
    $patient["last_name"];

$patientInitial =
    strtoupper(
        substr($patient["first_name"], 0, 1)
    );

/* Load Active Departments */

$departments = [];

$departmentSql = "
    SELECT
        department_id,
        department_name
    FROM departments
    WHERE status = 'Active'
    ORDER BY department_name ASC
";

$departmentResult =
    mysqli_query($conn, $departmentSql);

while (
    $department =
        mysqli_fetch_assoc($departmentResult)
) {
    $departments[] = $department;
}

/* Load Active Doctors */

$doctors = [];

$doctorSql = "
    SELECT
        d.doctor_id,
        d.department_id,
        d.first_name,
        d.last_name,
        d.specialization,
        
        d.availability,
        dep.department_name
    FROM doctors d
    INNER JOIN users u
        ON d.user_id = u.user_id
    INNER JOIN departments dep
        ON d.department_id = dep.department_id
    WHERE u.status = 'Active'
      AND dep.status = 'Active'
    ORDER BY
        dep.department_name ASC,
        d.first_name ASC,
        d.last_name ASC
";

$doctorResult =
    mysqli_query($conn, $doctorSql);

while (
    $doctor =
        mysqli_fetch_assoc($doctorResult)
) {
    $doctors[] = $doctor;
}

/* Load Patient Appointments */

$appointments = [];

$appointmentSql = "
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,
        d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name,
        d.specialization,
        dep.department_name
    FROM appointments a
    INNER JOIN doctors d
        ON a.doctor_id = d.doctor_id
    INNER JOIN departments dep
        ON d.department_id = dep.department_id
    WHERE a.patient_id = ?
    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC
";

$appointmentStmt =
    mysqli_prepare($conn, $appointmentSql);

mysqli_stmt_bind_param(
    $appointmentStmt,
    "i",
    $patientId
);

mysqli_stmt_execute($appointmentStmt);

$appointmentResult =
    mysqli_stmt_get_result($appointmentStmt);

while (
    $appointment =
        mysqli_fetch_assoc($appointmentResult)
) {
    $appointments[] = $appointment;
}

mysqli_stmt_close($appointmentStmt);
mysqli_close($conn);

$minimumDate = date("Y-m-d");
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

    <link rel="stylesheet" href="css/patient_appointments.css">

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
                <h2>BestCare</h2>
                <span>Patient Portal</span>
            </div>

        </div>

        <nav class="sidebar-navigation">

            <a href="patient_dashboard.php">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="patient_appointments.php" class="active">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Appointments</span>
            </a>

            <a href="patient_records.php">
                <i class="fa-solid fa-notes-medical"></i>
                <span>Records & Queries</span>
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="logout.php" class="logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
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

                <h1>Appointments</h1>

                <p>
                    Book and manage your appointments.
                </p>

            </div>

            <div class="patient-account">

                <div class="patient-avatar">
                    <?php
                    echo htmlspecialchars(
                        $patientInitial
                    );
                    ?>
                </div>

                <div class="patient-account-text">

                    <strong>
                        <?php
                        echo htmlspecialchars($fullName);
                        ?>
                    </strong>

                    <span>Patient</span>

                </div>

            </div>

        </header>

        <main class="appointments-content">

            <!-- Messages -->

            <?php if (
                isset($_SESSION["appointment_success"])
                ): ?>

                <div class="appointment-message success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <span>
                        <?php
                        echo htmlspecialchars($_SESSION["appointment_success"]);

                        unset($_SESSION["appointment_success"]);
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <?php if (
                isset($_SESSION["appointment_error"])
            ): ?>

                <div class="appointment-message error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?php
                        echo htmlspecialchars($_SESSION["appointment_error"]);

                        unset($_SESSION["appointment_error"]);
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <!-- Page Introduction -->

            <section class="appointments-intro">

                <div>

                    <span class="appointments-label">
                        Patient Appointments
                    </span>

                    <h2>
                        Book and Track Medical Appointments
                    </h2>

                    <p>
                        Choose a department, select a doctor,
                        and submit your preferred date and time.
                        New requests are created with a pending status.
                    </p>

                </div>

                <div class="appointments-intro-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

            </section>

            <!-- Appointment Page Layout -->

            <section class="appointments-layout">

                <!-- Booking Form -->

                <article class="appointment-booking-card">

                    <div class="appointment-section-header">

                        <div>

                            <span>New Request</span>

                            <h2>Book Appointment</h2>

                        </div>

                        <i class="fa-solid fa-calendar-plus"></i>

                    </div>

                    <?php if (
                        empty($departments) ||
                        empty($doctors)
                    ): ?>

                        <div class="appointment-empty-state">

                            <i class="fa-solid fa-user-doctor"></i>

                            <h3>No doctors available</h3>

                            <p>
                                There are currently no active doctors
                                available for online appointment booking.
                            </p>

                        </div>

                    <?php else: ?>

                        <form
                            action="db_process/patient_appointment_process.php"
                            method="POST"
                            id="appointmentForm"
                            class="appointment-form"
                            novalidate
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="book"
                            >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php
                                echo htmlspecialchars($_SESSION["csrf_token"]);
                                ?>"
                            >

                            <!-- Department -->

                            <div class="appointment-form-group">

                                <label for="department_id">
                                    Department
                                    <span>*</span>
                                </label>

                                <div class="appointment-input-wrapper">

                                    <i class="fa-solid fa-hospital"></i>

                                    <select id="department_id" name="department_id" required>

                                        <option value="">
                                            Select Department
                                        </option>

                                        <?php
                                        foreach (
                                            $departments
                                            as $department
                                        ):
                                        ?>

                                            <option
                                                value="<?php
                                                echo (int)$department["department_id"];
                                                ?>"
                                            >
                                                <?php
                                                echo htmlspecialchars($department["department_name"]);
                                                ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <small class="field-error"></small>

                            </div>

                            <!-- Doctor -->

                            <div class="appointment-form-group">

                                <label for="doctor_id">
                                    Doctor
                                    <span>*</span>
                                </label>

                                <div class="appointment-input-wrapper">

                                    <i class="fa-solid fa-user-doctor"></i>

                                    <select
                                        id="doctor_id"
                                        name="doctor_id"
                                        required
                                        disabled
                                    >

                                        <option value="">
                                            Select a department first
                                        </option>

                                        <?php
                                        foreach ($doctors as $doctor):
                                        ?>

                                            <option
                                                value="<?php
                                                echo (int)$doctor["doctor_id"];
                                                ?>"
                                                data-department="<?php
                                                echo (int)$doctor["department_id"];
                                                ?>"
                                                data-specialization="<?php
                                                echo htmlspecialchars($doctor["specialization"]);
                                                ?>"
                                                data-availability="<?php
                                                echo htmlspecialchars($doctor["availability"]);
                                                ?>"
                                                hidden
                                            >
                                                Dr.
                                                <?php
                                                echo htmlspecialchars($doctor["first_name"]." " .$doctor["last_name"]);
                                                ?>
                                                —
                                                <?php
                                                echo htmlspecialchars($doctor["specialization"]);
                                                ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                                <small class="field-error"></small>

                            </div>

                            <!-- Selected Doctor Details -->

                            <div
                                class="selected-doctor-card"
                                id="selectedDoctorCard"
                                hidden
                            >

                                <div>

                                    <i class="fa-solid fa-stethoscope"></i>

                                    <span>Specialization</span>

                                    <strong
                                        id="doctorSpecialization"
                                    >
                                        —
                                    </strong>

                                </div>

                                <div>

                                    <i class="fa-solid fa-clock"></i>

                                    <span>Availability</span>

                                    <strong
                                        id="doctorAvailability"
                                    >
                                        —
                                    </strong>

                                </div>

                            </div>

                            <!-- Date and Time -->

                            <div class="appointment-form-row">

                                <div class="appointment-form-group">

                                    <label for="appointment_date">
                                        Appointment Date
                                        <span>*</span>
                                    </label>

                                    <div class="appointment-input-wrapper">

                                        <i class="fa-solid fa-calendar-day"></i>

                                        <input
                                            type="date"
                                            id="appointment_date"
                                            name="appointment_date"
                                            min="<?php
                                            echo htmlspecialchars($minimumDate);
                                            ?>"
                                            required
                                        >

                                    </div>

                                    <small class="field-error"></small>

                                </div>

                                <div class="appointment-form-group">

                                    <label for="appointment_time">
                                        Appointment Time
                                        <span>*</span>
                                    </label>

                                    <div class="appointment-input-wrapper">

                                        <i class="fa-solid fa-clock"></i>

                                        <input
                                            type="time"
                                            id="appointment_time"
                                            name="appointment_time"
                                            min="08:00"
                                            max="20:00"
                                            required
                                        >

                                    </div>

                                    <small class="field-error"></small>

                                </div>

                            </div>

                            <!-- Buttons -->

                            <div class="appointment-form-actions">

                                <button
                                    type="reset"
                                    class="appointment-reset-button"
                                    id="resetAppointmentButton"
                                >
                                    <i class="fa-solid fa-rotate-left"></i>
                                    Reset
                                </button>

                                <button
                                    type="submit"
                                    class="appointment-submit-button"
                                >
                                    <i class="fa-solid fa-calendar-check"></i>
                                    Book Appointment
                                </button>

                            </div>

                        </form>

                    <?php endif; ?>

                </article>

                <!-- Appointment Guidelines -->

                <aside class="appointment-guidelines-card">

                    <div class="guidelines-header">

                        <i class="fa-solid fa-circle-info"></i>

                        <div>
                            <span>Booking Information</span>
                            <h2>Appointment Guidelines</h2>
                        </div>

                    </div>

                    <ul>

                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Choose the correct department.
                        </li>

                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Select a doctor from that department.
                        </li>

                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Select a future date and available time.
                        </li>

                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            New requests start as Pending.
                        </li>

                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            Only pending appointments can be cancelled.
                        </li>

                    </ul>

                    <div class="appointment-help-box">

                        <i class="fa-solid fa-phone-volume"></i>

                        <div>
                            <span>Need help?</span>
                            <strong>+94 41 222 1234</strong>
                        </div>

                    </div>

                </aside>

            </section>

            <!-- Appointment History -->

            <section class="appointment-history-section">

                <div class="appointment-section-header">

                    <div>

                        <span>Appointment History</span>

                        <h2>My Appointments</h2>

                    </div>

                    <i class="fa-solid fa-list"></i>

                </div>

                <?php if (empty($appointments)): ?>

                    <div class="appointment-empty-state">

                        <i class="fa-regular fa-calendar-xmark"></i>

                        <h3>No appointments found</h3>

                        <p>
                            Your booked appointments will appear here.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="appointment-table-wrapper">

                        <table class="appointment-table">

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Doctor</th>
                                    <th>Department</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                foreach ($appointments as $appointment):
                                ?>

                                    <tr>

                                        <td>
                                            #<?php
                                            echo str_pad((string)$appointment["appointment_id"],4,"0",STR_PAD_LEFT);
                                            ?>
                                        </td>

                                        <td>

                                            <strong>
                                                Dr.
                                                <?php
                                                echo htmlspecialchars($appointment["doctor_first_name"] ." " .$appointment["doctor_last_name"]);
                                                ?>
                                            </strong>

                                            <small>
                                                <?php
                                                echo htmlspecialchars($appointment["specialization"]);
                                                ?>
                                            </small>

                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars($appointment["department_name"]);
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo date("d M Y", strtotime($appointment["appointment_date"]));
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo date("h:i A",strtotime($appointment["appointment_time"]));
                                            ?>
                                        </td>

                                        <td>

                                            <span
                                                class="appointment-status status-<?php
                                                echo strtolower($appointment["status"]);
                                                ?>"
                                            >
                                                <?php
                                                echo htmlspecialchars($appointment["status"]);
                                                ?>
                                            </span>

                                        </td>

                                        <td>

                                            <?php if ($appointment["status"] === "Pending"): ?>

                                                <form
                                                    action="db_process/patient_appointment_process.php"
                                                    method="POST"
                                                    class="cancel-appointment-form"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="cancel"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?php
                                                        echo htmlspecialchars($_SESSION["csrf_token"]);
                                                        ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_id"
                                                        value="<?php
                                                        echo (int)$appointment["appointment_id"];
                                                        ?>"
                                                    >

                                                    <button type="submit" class="cancel-appointment-button">
                                                        <i class="fa-solid fa-ban"></i>
                                                        Cancel
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <span class="no-action-text">
                                                    —
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>

        </main>

    </div>

</div>

<script src="js/patient_dashboard.js"></script>
<script src="js/patient_appointments.js"></script>

</body>
</html>