<?php
session_start();

require_once "db_process/includes/connection.php";

/* Patient Authentication */

if (!isset($_SESSION["user_id"], $_SESSION["role_id"])) {
    header("Location: login.php");
    exit();
}

/* 3 = Patient */

if ((int) $_SESSION["role_id"] !== 3) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

/* CSRF Token */

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

/* Load Patient Information */

$patientSql = "
    SELECT
        p.patient_id,
        p.first_name,
        p.last_name,
        p.gender,
        p.dob,
        p.phone,
        p.email,
        p.address,
        p.blood_group,
        p.emergency_contact,
        p.created_at,
        u.username,
        u.status
    FROM patients p
    INNER JOIN users u
        ON p.user_id = u.user_id
    WHERE p.user_id = ?
    LIMIT 1
";

$patientStmt = mysqli_prepare($conn, $patientSql);

if (!$patientStmt) {
    die("Unable to load patient information.");
}

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

/* Count Helper */

function getPatientCount(
    mysqli $conn,
    string $sql,
    int $patientId
): int {
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $patientId
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    $row =
        mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return (int) ($row["total"] ?? 0);
}

/* Summary Counts */

$appointmentCount = getPatientCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM appointments
        WHERE patient_id = ?
    ",
    $patientId
);

$medicalRecordCount = getPatientCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM medical_records
        WHERE patient_id = ?
    ",
    $patientId
);

$labReportCount = getPatientCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM laboratory_reports
        WHERE patient_id = ?
          AND status = 'Completed'
    ",
    $patientId
);

$pendingBillCount = getPatientCount(
    $conn,
    "
        SELECT COUNT(*) AS total
        FROM bills
        WHERE patient_id = ?
          AND payment_status = 'Pending'
    ",
    $patientId
);

mysqli_close($conn);

/* Display Values */

$patientInitial = strtoupper(
    substr($patient["first_name"], 0, 1)
);

$formattedDob = !empty($patient["dob"])
    ? date("d M Y", strtotime($patient["dob"]))
    : "Not available";

$memberSince = !empty($patient["created_at"])
    ? date("d M Y", strtotime($patient["created_at"]))
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

    <link rel="stylesheet" href="css/patient_dashboard.css">

</head>

<body>

<div class="patient-layout">

    <!-- Sidebar -->

    <aside class="patient-sidebar" id="patientSidebar">

        <div class="sidebar-logo">

            <img src="image/logo.png" alt="BestCare Hospital Logo">

            <div>
                <h2>BestCare</h2>
                <span>Patient Portal</span>
            </div>

        </div>

        <nav class="sidebar-navigation">

            <a href="patient_dashboard.php" class="active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="patient_appointments.php">
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

    <!-- Main Content -->

    <div class="patient-main">

        <!-- Top Bar -->

        <header class="patient-topbar">

            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open navigation menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-title">

                <h1>Patient Dashboard</h1>

                <p>
                    Manage your profile and healthcare services.
                </p>

            </div>

            <div class="patient-account">

                <div class="patient-avatar">
                    <?php
                    echo htmlspecialchars($patientInitial);
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

        <main class="dashboard-content">

            <!-- Messages -->

            <?php if (
                isset($_SESSION["profile_success"])
            ): ?>

                <div class="dashboard-message success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["profile_success"]
                        );

                        unset(
                            $_SESSION["profile_success"]
                        );
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <?php if (
                isset($_SESSION["profile_error"])
            ): ?>

                <div class="dashboard-message error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["profile_error"]
                        );

                        unset(
                            $_SESSION["profile_error"]
                        );
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <?php if (
                isset($_SESSION["password_success"])
            ): ?>

                <div class="dashboard-message success-message">

                    <i class="fa-solid fa-circle-check"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["password_success"]
                        );

                        unset(
                            $_SESSION["password_success"]
                        );
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <?php if (
                isset($_SESSION["password_error"])
            ): ?>

                <div class="dashboard-message error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["password_error"]
                        );

                        unset(
                            $_SESSION["password_error"]
                        );
                        ?>
                    </span>

                </div>

            <?php endif; ?>

            <!-- Welcome Banner -->

            <section class="welcome-banner">

                <div>

                    <span class="welcome-label">
                        Patient Portal
                    </span>

                    <h2>
                        Welcome,
                        <?php
                        echo htmlspecialchars(
                            $patient["first_name"]
                        );
                        ?>!
                    </h2>

                    <p>
                        Manage your profile, appointments,
                        healthcare records, bills, and patient
                        queries from one secure portal.
                    </p>

                    <a
                        href="patient_appointments.php" class="welcome-button">
                        <i class="fa-solid fa-calendar-plus"></i>
                        Manage Appointments
                    </a>

                </div>

                <div class="welcome-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>

            </section>

            <!-- Summary Cards -->

            <section class="summary-grid">

                <article class="summary-card">

                    <div class="summary-icon appointment-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                    <div>
                        <span>Total Appointments</span>

                        <strong>
                            <?php echo $appointmentCount; ?>
                        </strong>
                    </div>

                </article>

                <article class="summary-card">

                    <div class="summary-icon record-icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>

                    <div>
                        <span>Medical Records</span>

                        <strong>
                            <?php echo $medicalRecordCount; ?>
                        </strong>
                    </div>

                </article>

                <article class="summary-card">

                    <div class="summary-icon laboratory-icon">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>

                    <div>
                        <span>Lab Reports</span>

                        <strong>
                            <?php echo $labReportCount; ?>
                        </strong>
                    </div>

                </article>

                <article class="summary-card">

                    <div class="summary-icon billing-icon">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>

                    <div>
                        <span>Pending Bills</span>

                        <strong>
                            <?php echo $pendingBillCount; ?>
                        </strong>
                    </div>

                </article>

            </section>

            <!-- Quick Actions -->

            <section class="dashboard-panel quick-actions-panel">

                <div class="panel-header">

                    <div>
                        <span>Shortcuts</span>
                        <h2>Quick Actions</h2>
                    </div>

                </div>

                <div class="quick-actions">

                    <a href="patient_appointments.php">

                        <i class="fa-solid fa-calendar-plus"></i>

                        <div>
                            <strong>Appointments</strong>

                            <span>
                                Book, view, or cancel an appointment
                            </span>
                        </div>

                    </a>

                    <a href="patient_records.php#medicalRecords">

                        <i class="fa-solid fa-notes-medical"></i>

                        <div>
                            <strong>Medical Records</strong>

                            <span>
                                View diagnoses and prescriptions
                            </span>
                        </div>

                    </a>

                    <a href="patient_records.php#labReports">

                        <i class="fa-solid fa-file-pdf"></i>

                        <div>
                            <strong>Lab Reports</strong>

                            <span>
                                View your PDF test reports
                            </span>
                        </div>

                    </a>

                    <a href="patient_records.php#patientQueries">

                        <i class="fa-solid fa-message"></i>

                        <div>
                            <strong>Patient Queries</strong>

                            <span>
                                Send queries and view replies
                            </span>
                        </div>

                    </a>

                </div>

            </section>

            <!-- Profile Settings -->

            <section class="dashboard-settings-section" id="profileSettings">

                <div class="section-title">

                    <span>Account Settings</span>

                    <h2>My Profile</h2>

                    <p>
                        Review your personal details and update
                        your contact information.
                    </p>

                </div>

                <div class="settings-grid">

                    <!-- Personal Information -->

                    <article class="settings-card">

                        <div class="settings-card-header">

                            <div>
                                <span>Patient Details</span>
                                <h3>Personal Information</h3>
                            </div>

                            <i class="fa-solid fa-id-card"></i>

                        </div>

                        <div class="profile-information-grid">

                            <div class="profile-information-item">

                                <span>Patient ID</span>

                                <strong>
                                    BC-<?php
                                    echo str_pad(
                                        (string) $patientId,
                                        5,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Full Name</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars($fullName);
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Username</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $patient["username"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Gender</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $patient["gender"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Date of Birth</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $formattedDob
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Blood Group</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $patient["blood_group"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

                                <span>Account Status</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $patient["status"]
                                    );
                                    ?>
                                </strong>

                            </div>

                            <div class="profile-information-item">

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

                    <!-- Update Contact Information -->

                    <article class="settings-card">

                        <div class="settings-card-header">

                            <div>
                                <span>Editable Details</span>
                                <h3>Update Contact Information</h3>
                            </div>

                            <i class="fa-solid fa-pen-to-square"></i>

                        </div>

                        <form
                            action="db_process/patient_profile_process.php"
                            method="POST"
                            id="profileForm"
                            class="dashboard-form"
                            novalidate
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php
                                echo htmlspecialchars(
                                    $_SESSION["csrf_token"]
                                );
                                ?>"
                            >

                            <div class="dashboard-form-group">

                                <label for="phone">
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    maxlength="15"
                                    inputmode="numeric"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $patient["phone"]
                                    );
                                    ?>"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="dashboard-form-group">

                                <label for="email">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    maxlength="100"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $patient["email"]
                                    );
                                    ?>"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="dashboard-form-group">

                                <label for="emergency_contact">
                                    Emergency Contact
                                </label>

                                <input
                                    type="tel"
                                    id="emergency_contact"
                                    name="emergency_contact"
                                    maxlength="15"
                                    inputmode="numeric"
                                    value="<?php
                                    echo htmlspecialchars($patient["emergency_contact"]);
                                    ?>"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="dashboard-form-group">

                                <label for="address">
                                    Residential Address
                                </label>

                                <textarea id="address" name="address" rows="4" maxlength="500" required>
                                    <?php
                                    echo htmlspecialchars($patient["address"]);
                                    ?>
                                </textarea>

                                <small class="field-error"></small>

                            </div>

                            <button type="submit" class="dashboard-submit-button">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Profile Changes
                            </button>

                        </form>

                    </article>

                    <!-- Change Password -->

                    <article class="settings-card password-settings-card">

                        <div class="settings-card-header">

                            <div>
                                <span>Account Security</span>
                                <h3>Change Password</h3>
                            </div>

                            <i class="fa-solid fa-key"></i>

                        </div>

                        <form
                            action="db_process/change_password_process.php"
                            method="POST"
                            id="changePasswordForm"
                            class="dashboard-form"
                            novalidate
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php
                                echo htmlspecialchars(
                                    $_SESSION["csrf_token"]
                                );
                                ?>"
                            >

                            <div class="dashboard-form-group">

                                <label for="current_password">
                                    Current Password
                                </label>

                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    autocomplete="current-password"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="dashboard-form-group">

                                <label for="new_password">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="dashboard-form-group">

                                <label for="confirm_password">
                                    Confirm New Password
                                </label>

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                                <small class="field-error"></small>

                            </div>

                            <div class="password-requirements">

                                <span>
                                    <i class="fa-solid fa-circle-check"></i>
                                    At least 8 characters
                                </span>

                                <span>
                                    <i class="fa-solid fa-circle-check"></i>
                                    At least one uppercase letter
                                </span>

                                <span>
                                    <i class="fa-solid fa-circle-check"></i>
                                    At least one number
                                </span>

                            </div>

                            <button
                                type="submit"
                                class="dashboard-submit-button"
                            >
                                <i class="fa-solid fa-key"></i>
                                Change Password
                            </button>

                        </form>

                    </article>

                </div>

            </section>

        </main>

    </div>

</div>

<script src="js/patient_dashboard.js"></script>

</body>
</html>