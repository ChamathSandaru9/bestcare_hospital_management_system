<?php
session_start();

//    Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

//    Prevent Cached Protected Pages

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

//    Appointment Count Helper

function getAppointmentCount(
    mysqli $conn,
    string $sql,
    int $doctorId
): int {

    $stmt =
        mysqli_prepare(
            $conn,
            $sql
        );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $doctorId
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );

    $row =
        mysqli_fetch_assoc(
            $result
        );

    mysqli_stmt_close(
        $stmt
    );

    return (int) (
        $row["total"] ?? 0
    );
}

//    Appointment Summary Counts

/*
The administrator schedules and approves appointments.
Therefore, doctors only see Approved appointments.
*/

$todayAppointmentCount =
    getAppointmentCount(
        $conn,
        "
            SELECT
                COUNT(*) AS total
            FROM appointments
            WHERE doctor_id = ?
              AND appointment_date =
                  CURDATE()
              AND status = 'Approved'
        ",
        $doctorId
    );

$upcomingAppointmentCount =
    getAppointmentCount(
        $conn,
        "
            SELECT
                COUNT(*) AS total
            FROM appointments
            WHERE doctor_id = ?
              AND appointment_date >
                  CURDATE()
              AND status = 'Approved'
        ",
        $doctorId
    );

$completedAppointmentCount =
    getAppointmentCount(
        $conn,
        "
            SELECT
                COUNT(*) AS total
            FROM appointments
            WHERE doctor_id = ?
              AND status = 'Completed'
        ",
        $doctorId
    );

$cancelledAppointmentCount =
    getAppointmentCount(
        $conn,
        "
            SELECT
                COUNT(*) AS total
            FROM appointments
            WHERE doctor_id = ?
              AND status = 'Cancelled'
        ",
        $doctorId
    );
 
//    Load Today's Approved Appointments

$todayAppointments = [];

$todaySql = "
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,

        p.patient_id,
        p.first_name AS patient_first_name,
        p.last_name AS patient_last_name,
        p.gender,
        p.phone,
        p.email,
        p.blood_group,
        p.emergency_contact

    FROM appointments a

    INNER JOIN patients p
        ON a.patient_id = p.patient_id

    WHERE a.doctor_id = ?
      AND a.appointment_date =
          CURDATE()
      AND a.status = 'Approved'

    ORDER BY
        a.appointment_time ASC
";

$todayStmt =
    mysqli_prepare(
        $conn,
        $todaySql
    );

mysqli_stmt_bind_param(
    $todayStmt,
    "i",
    $doctorId
);

mysqli_stmt_execute(
    $todayStmt
);

$todayResult =
    mysqli_stmt_get_result(
        $todayStmt
    );

while (
    $appointment =
        mysqli_fetch_assoc(
            $todayResult
        )
) {
    $todayAppointments[] =
        $appointment;
}

mysqli_stmt_close(
    $todayStmt
);

//    Load Upcoming Approved Appointments

$upcomingAppointments = [];

$upcomingSql = "
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,

        p.patient_id,
        p.first_name AS patient_first_name,
        p.last_name AS patient_last_name,
        p.gender,
        p.phone,
        p.email,
        p.blood_group

    FROM appointments a

    INNER JOIN patients p
        ON a.patient_id = p.patient_id

    WHERE a.doctor_id = ?
      AND a.appointment_date >
          CURDATE()
      AND a.status = 'Approved'

    ORDER BY
        a.appointment_date ASC,
        a.appointment_time ASC
";

$upcomingStmt =
    mysqli_prepare(
        $conn,
        $upcomingSql
    );

mysqli_stmt_bind_param(
    $upcomingStmt,
    "i",
    $doctorId
);

mysqli_stmt_execute(
    $upcomingStmt
);

$upcomingResult =
    mysqli_stmt_get_result(
        $upcomingStmt
    );

while (
    $appointment =
        mysqli_fetch_assoc(
            $upcomingResult
        )
) {
    $upcomingAppointments[] =
        $appointment;
}

mysqli_stmt_close(
    $upcomingStmt
);

//    Load Appointment History

// History contains only appointments with a final status.

$appointmentHistory = [];

$historySql = "
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,

        p.patient_id,
        p.first_name AS patient_first_name,
        p.last_name AS patient_last_name,
        p.phone,
        p.blood_group

    FROM appointments a

    INNER JOIN patients p
        ON a.patient_id = p.patient_id

    WHERE a.doctor_id = ?
      AND a.status IN (
          'Completed',
          'Cancelled'
      )

    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC

    LIMIT 50
";

$historyStmt =
    mysqli_prepare(
        $conn,
        $historySql
    );

mysqli_stmt_bind_param(
    $historyStmt,
    "i",
    $doctorId
);

mysqli_stmt_execute(
    $historyStmt
);

$historyResult =
    mysqli_stmt_get_result(
        $historyStmt
    );

while (
    $appointment =
        mysqli_fetch_assoc(
            $historyResult
        )
) {
    $appointmentHistory[] =
        $appointment;
}

mysqli_stmt_close(
    $historyStmt
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

    <link rel="stylesheet" href="css/doctor_dashboard.css">

    <link rel="stylesheet" href="css/doctor_appointments.css">

</head>

<body>

<div class="doctor-layout">

    <!--Sidebar -->

    <aside class="doctor-sidebar" id="doctorSidebar">

        <div class="doctor-sidebar-logo">

            <img src="image/logo.png" alt="BestCare Hospital Logo">

            <div>
                <h2>BestCare</h2>
                <span>Doctor Portal</span>
            </div>

        </div>

        <nav class="doctor-navigation">

            <a href="doctor_dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

            <a href="doctor_appointments.php" class="active">

                <i class="fa-solid fa-calendar-check"></i>

                <span>Manage Appointments</span>

            </a>

            <a href="doctor_patients.php">

                <i class="fa-solid fa-user-injured"></i>

                <span>Patients</span>

            </a>

        </nav>

        <div class="doctor-sidebar-bottom">

            <a href="logout.php" class="doctor-logout-link">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </div>

    </aside>

    <!-- Main Area -->

    <div class="doctor-main">

        <!-- Topbar -->

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
                    Manage Appointments
                </h1>

                <p>
                    Review approved appointments,
                    complete consultations, or cancel
                    appointments when necessary.
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

        <!-- Main Content -->

        <main class="doctor-appointments-content">

            <!-- Messages -->

            <?php if (
                isset(
                    $_SESSION["doctor_success"]
                )
            ): ?>

                <div class="doctor-appointment-message success-message">

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

                <div class="doctor-appointment-message error-message">

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

            <!-- Introduction Banner -->

            <section class="doctor-appointments-banner">

                <div>

                    <span class="doctor-appointments-label">
                        Appointment Management
                    </span>

                    <h2>
                        Manage Your Patient Schedule
                    </h2>

                    <p>
                        View appointments scheduled by the
                        administrator, manage today's
                        consultations, and review completed
                        or cancelled appointment history.
                    </p>

                    <div class="doctor-appointment-doctor-info">

                        <span>

                            <i class="fa-solid fa-hospital"></i>

                            <?php
                            echo e(
                                $doctor[
                                    "department_name"
                                ]
                            );
                            ?>

                        </span>

                        <span>

                            <i class="fa-solid fa-clock"></i>

                            <?php
                            echo e(
                                $doctor[
                                    "availability"
                                ]
                            );
                            ?>

                        </span>

                    </div>

                </div>

                <div class="doctor-appointments-banner-icon">

                    <i class="fa-solid fa-calendar-check"></i>

                </div>

            </section>

            <!-- Summary Cards -->

            <section class="appointment-summary-grid">

                <article class="appointment-summary-card">

                    <div class="appointment-summary-icon today-summary-icon">

                        <i class="fa-solid fa-calendar-day"></i>

                    </div>

                    <div>

                        <span>
                            Today's Appointments
                        </span>

                        <strong>

                            <?php
                            echo $todayAppointmentCount;
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="appointment-summary-card">

                    <div class="appointment-summary-icon upcoming-summary-icon">

                        <i class="fa-solid fa-calendar-plus"></i>

                    </div>

                    <div>

                        <span>
                            Upcoming Appointments
                        </span>

                        <strong>

                            <?php
                            echo $upcomingAppointmentCount;
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="appointment-summary-card">

                    <div class="appointment-summary-icon completed-summary-icon">

                        <i class="fa-solid fa-circle-check"></i>

                    </div>

                    <div>

                        <span>
                            Completed Appointments
                        </span>

                        <strong>

                            <?php
                            echo $completedAppointmentCount;
                            ?>

                        </strong>

                    </div>

                </article>

                <article class="appointment-summary-card">

                    <div class="appointment-summary-icon cancelled-summary-icon">

                        <i class="fa-solid fa-ban"></i>

                    </div>

                    <div>

                        <span>
                            Cancelled Appointments
                        </span>

                        <strong>

                            <?php
                            echo $cancelledAppointmentCount;
                            ?>

                        </strong>

                    </div>

                </article>

            </section>
<!-- Today's Appointments -->
<section class="doctor-appointment-section">

    <div class="doctor-appointment-section-header">

        <div>

            <span>Today</span>

            <h2>
                Today's Appointments
            </h2>

            <p>
                Complete today's approved
                consultations or cancel an
                appointment when necessary.
            </p>

        </div>

        <i class="fa-solid fa-calendar-day"></i>

    </div>

    <?php if (empty($todayAppointments)): ?>

        <div class="doctor-appointment-empty">

            <i class="fa-regular fa-calendar-xmark"></i>

            <h3>
                No appointments today
            </h3>

            <p>
                You do not have any approved
                appointments scheduled for today.
            </p>

        </div>

    <?php else: ?>

        <div class="doctor-appointment-table-wrapper">

            <table class="doctor-appointment-table">

                <thead>

                    <tr>

                        <th>Appointment</th>

                        <th>Patient</th>

                        <th>Contact</th>

                        <th>Time</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach (
                        $todayAppointments
                        as $appointment
                    ): ?>

                        <tr>

                            <td>

                                #
                                <?php
                                echo str_pad(
                                    $appointment[
                                        "appointment_id"
                                    ],
                                    4,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                ?>

                            </td>

                            <td>

                                <strong>

                                    <?php
                                    echo e(
                                        $appointment[
                                            "patient_first_name"
                                        ] .
                                        " " .
                                        $appointment[
                                            "patient_last_name"
                                        ]
                                    );
                                    ?>

                                </strong>

                                <small>

                                    Patient ID :
                                    BC-<?php
                                    echo str_pad(
                                        $appointment[
                                            "patient_id"
                                        ],
                                        5,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>

                                </small>

                            </td>

                            <td>

                                <span>

                                    <?php
                                    echo e(
                                        $appointment[
                                            "phone"
                                        ]
                                    );
                                    ?>

                                </span>

                                <small>

                                    <?php
                                    echo e(
                                        $appointment[
                                            "email"
                                        ]
                                    );
                                    ?>

                                </small>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $appointment[
                                            "appointment_time"
                                        ]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <span
                                    class="doctor-appointment-status status-approved"
                                >

                                    Approved

                                </span>

                            </td>

                            <td>

                                <div
                                    class="doctor-appointment-actions"
                                >

                                    <!-- Complete -->

                                    <form
                                        action="db_process/doctor_process.php"
                                        method="POST"
                                        class="doctor-appointment-action-form complete-appointment-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="complete_appointment"
                                        >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo (int)
                                            $appointment[
                                                "appointment_id"
                                            ];
                                            ?>"
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

                                        <button
                                            type="submit"
                                            class="doctor-appointment-button complete-button"
                                        >

                                            <i class="fa-solid fa-circle-check"></i>

                                            Complete

                                        </button>

                                    </form>

                                    <!-- Cancel -->

                                    <form
                                        action="db_process/doctor_process.php"
                                        method="POST"
                                        class="doctor-appointment-action-form cancel-doctor-appointment-form"  
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="cancel_appointment"
                                        >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo (int)
                                            $appointment[
                                                "appointment_id"
                                            ];
                                            ?>"
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

                                        <button
                                            type="submit"
                                            class="doctor-appointment-button cancel-button"
                                        >

                                            <i class="fa-solid fa-ban"></i>

                                            Cancel

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<!-- Upcoming Appointments -->

<section class="doctor-appointment-section">

    <div class="doctor-appointment-section-header">

        <div>

            <span>Upcoming</span>

            <h2>
                Upcoming Appointments
            </h2>

            <p>
                These appointments have already
                been approved and scheduled by
                the administrator.
            </p>

        </div>

        <i class="fa-solid fa-calendar-plus"></i>

    </div>

    <?php if (empty($upcomingAppointments)): ?>

        <div class="doctor-appointment-empty">

            <i class="fa-solid fa-calendar-check"></i>

            <h3>
                No Upcoming Appointments
            </h3>

            <p>
                You currently have no approved
                future appointments.
            </p>

        </div>

    <?php else: ?>

        <div class="doctor-upcoming-grid">
            <?php foreach (
    $upcomingAppointments
    as $appointment
): ?>

    <article class="doctor-upcoming-card">

        <div class="doctor-upcoming-card-header">

            <div>

                <span>
                    Appointment
                    #<?php
                    echo str_pad(
                        (string)
                        $appointment[
                            "appointment_id"
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
                        $appointment[
                            "patient_first_name"
                        ] .
                        " " .
                        $appointment[
                            "patient_last_name"
                        ]
                    );
                    ?>
                </h3>

            </div>

            <span class="doctor-appointment-status status-approved">
                Approved
            </span>

        </div>

        <div class="doctor-upcoming-details">

            <div>

                <i class="fa-solid fa-calendar-day"></i>

                <span>Date</span>

                <strong>
                    <?php
                    echo date(
                        "d M Y",
                        strtotime(
                            $appointment[
                                "appointment_date"
                            ]
                        )
                    );
                    ?>
                </strong>

            </div>

            <div>

                <i class="fa-solid fa-clock"></i>

                <span>Time</span>

                <strong>
                    <?php
                    echo date(
                        "h:i A",
                        strtotime(
                            $appointment[
                                "appointment_time"
                            ]
                        )
                    );
                    ?>
                </strong>

            </div>

            <div>

                <i class="fa-solid fa-phone"></i>

                <span>Phone</span>

                <strong>
                    <?php
                    echo e(
                        $appointment[
                            "phone"
                        ]
                    );
                    ?>
                </strong>

            </div>

            <div>

                <i class="fa-solid fa-droplet"></i>

                <span>Blood Group</span>

                <strong>
                    <?php
                    echo e(
                        $appointment[
                            "blood_group"
                        ]
                    );
                    ?>
                </strong>

            </div>

        </div>

        <div class="doctor-upcoming-actions">

            <form
                action="db_process/doctor_process.php"
                method="POST"
                class="doctor-appointment-action-form cancel-doctor-appointment-form"
            >

                <input
                    type="hidden"
                    name="action"
                    value="cancel_appointment"
                >

                <input
                    type="hidden"
                    name="appointment_id"
                    value="<?php
                    echo (int)
                        $appointment[
                            "appointment_id"
                        ];
                    ?>"
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

                <button type="submit" class="doctor-appointment-button cancel-button">

                    <i class="fa-solid fa-ban"></i>

                    Cancel

                </button>

            </form>

        </div>

    </article>

<?php endforeach; ?>

</div>

<?php endif; ?>

</section>

<!-- Appointment History -->

<section class="doctor-appointment-section">

    <div class="doctor-appointment-section-header">

        <div>

            <span>Previous Activity</span>

            <h2>
                Appointment History
            </h2>

            <p>
                View completed and cancelled
                appointment information.
            </p>

        </div>

        <i class="fa-solid fa-clock-rotate-left"></i>

    </div>

    <?php if (
        empty($appointmentHistory)
    ): ?>

        <div class="doctor-appointment-empty">

            <i class="fa-solid fa-clock-rotate-left"></i>

            <h3>
                No Appointment History
            </h3>

            <p>
                Completed and cancelled appointments
                will appear here.
            </p>

        </div>

    <?php else: ?>

        <div class="doctor-appointment-table-wrapper">

            <table
                class="doctor-appointment-table history-table"
            >

                <thead>

                    <tr>

                        <th>Appointment</th>

                        <th>Patient</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Contact</th>

                        <th>Blood Group</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach (
                        $appointmentHistory
                        as $appointment
                    ): ?>

                        <tr>

                            <td>

                                #<?php
                                echo str_pad(
                                    (string)
                                    $appointment[
                                        "appointment_id"
                                    ],
                                    4,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                ?>

                            </td>

                            <td>

                                <strong>
                                    <?php
                                    echo e(
                                        $appointment[
                                            "patient_first_name"
                                        ] .
                                        " " .
                                        $appointment[
                                            "patient_last_name"
                                        ]
                                    );
                                    ?>
                                </strong>

                                <small>
                                    Patient ID:
                                    BC-<?php
                                    echo str_pad(
                                        (string)
                                        $appointment[
                                            "patient_id"
                                        ],
                                        5,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </small>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $appointment[
                                            "appointment_date"
                                        ]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $appointment[
                                            "appointment_time"
                                        ]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo e(
                                    $appointment[
                                        "phone"
                                    ]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo e(
                                    $appointment[
                                        "blood_group"
                                    ]
                                );
                                ?>

                            </td>

                            <td>

                                <span
                                    class="doctor-appointment-status status-<?php
                                    echo strtolower(
                                        e(
                                            $appointment[
                                                "status"
                                            ]
                                        )
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo e(
                                        $appointment[
                                            "status"
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

<!-- Status Guide -->

<section class="doctor-appointment-status-guide">

    <article>

        <i class="fa-solid fa-circle-check"></i>

        <div>

            <strong>
                Approved
            </strong>

            <span>
                Scheduled and confirmed
                by the administrator
            </span>

        </div>

    </article>

    <article>

        <i class="fa-solid fa-clipboard-check"></i>

        <div>

            <strong>
                Completed
            </strong>

            <span>
                The consultation has finished
            </span>

        </div>

    </article>

    <article>

        <i class="fa-solid fa-ban"></i>

        <div>

            <strong>
                Cancelled
            </strong>

            <span>
                The appointment will not continue
            </span>

        </div>

    </article>

</section>

</main>

</div>

</div>

<?php
mysqli_close($conn);
?>

<script src="js/doctor_dashboard.js"></script>

<script src="js/doctor_appointments.js"></script>

</body>

</html>