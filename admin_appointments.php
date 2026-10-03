<?php
session_start();

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "db_process/includes/connection.php";

//    Admin Authentication

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role_id"])
) {
    header("Location: ../login.php");
    exit();
}

if ((int)$_SESSION["role_id"] !== 1) {

    header("Location: ../login.php");
    exit();

}

//    CSRF Token

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));

}

//    Pending Appointments

$pendingSql = "

SELECT

a.appointment_id,

a.appointment_date,

a.appointment_time,

a.status,

p.patient_id,

p.first_name,

p.last_name,

d.doctor_id,

d.first_name AS doctor_first,

d.last_name AS doctor_last

FROM appointments a

INNER JOIN patients p

ON a.patient_id=p.patient_id

INNER JOIN doctors d

ON a.doctor_id=d.doctor_id

WHERE a.status='Pending'

ORDER BY a.created_at ASC

";

$pendingResult =
mysqli_query(
$conn,
$pendingSql
);

//    Scheduled Appointments

$scheduledSql="

SELECT

a.appointment_id,

a.appointment_date,

a.appointment_time,

a.status,

p.first_name,

p.last_name,

d.first_name AS doctor_first,

d.last_name AS doctor_last

FROM appointments a

INNER JOIN patients p

ON a.patient_id=p.patient_id

INNER JOIN doctors d

ON a.doctor_id=d.doctor_id

WHERE a.status='Approved'
ORDER BY
a.appointment_date,
a.appointment_time
";

$scheduledResult=
mysqli_query(
$conn,
$scheduledSql
);

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>BestCare Hospital</title>

<link rel="icon" type="image/png" href="image/logo.png">

<link rel="stylesheet" href="css/admin_appointments.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <h2>BestCare</h2>
            <ul>
                <li class="active">
                    <a href="admin_appointments.php">
                        <i class="fa-solid fa-calendar-days"></i>
                        Appointments
                    </a>
                </li>
                <li>
                    <a href="admin_management.php">
                        <i class="fa-solid fa-gears"></i>
                        Management
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </aside>
        <div class="admin-sidebar-overlay"
        id="adminSidebarOverlay"
        aria-hidden="true">
    </div>
    <main class="admin-main">
        <header class="admin-mobile-header">
            <button 
            type="button"
            class="admin-sidebar-toggle"
            id="adminSidebarToggle"
            aria-controls="adminSidebar"
            aria-expanded="false"
            aria-label="Open navigation menu">
            <i class="fa-solid fa-bars"></i>
            </button>
            <div class="admin-page-heading">
                <h1>Appointment Management</h1>
                <p>Schedule and manage patient appointments.</p>
            </div>
        </header>

<section class="appointment-section">
    <h2>Pending Appointments</h2>
    <div class="appointment-table-wrapper">
        <table class="appointment-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row=mysqli_fetch_assoc($pendingResult)){
                ?>
                <tr>
                    <td>
                        <?php
                        echo htmlspecialchars($row["first_name"]." ".$row["last_name"]);
                        ?>
                    </td>
                    <td>
                        Dr.
                        <?php
                        echo htmlspecialchars($row["doctor_first"]." ".$row["doctor_last"]);
                        ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button 
                            class="schedule-btn" 
                            data-appointment-id="<?php
                            echo $row["appointment_id"];
                            ?>"
                            data-date="<?php
                            echo
                            $row["appointment_date"];
                            ?>"
                            data-time="<?php
                            echo
                            $row["appointment_time"];
                            ?>"
                            >
                            Schedule
                            </button>
                        </div>
                    </td>
                </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        </div> 

</section>
<section class="appointment-section">
    <h2>Scheduled Appointments</h2>
    <div class="appointment-table-wrapper">
        <table class="appointment-table">
    <thead>
            <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while(
            $row=mysqli_fetch_assoc(
                $scheduledResult
                )){
        ?>
        <tr>
            <td>
                <?php
                echo htmlspecialchars($row["first_name"]." ".$row["last_name"]);
                ?>
            </td>
            <td>
                Dr.
                <?php
                echo htmlspecialchars($row["doctor_first"]." ".$row["doctor_last"]);
                ?>
            </td>
            <td>
                <?php
                echo $row["appointment_date"];
                ?>
            </td>
            <td>
                <?php
                echo $row["appointment_time"];
                ?>
            </td>
            <td>
                <div class="table-actions">
                    <button 
                    class="edit-btn" 
                    data-appointment-id="<?php echo $row["appointment_id"];?>"
                    data-date="<?php echo $row['appointment_date']; ?>"
                    data-time="<?php echo $row['appointment_time']; ?>">
                    
                    Reschedule
                
                    </button>
                    <button
                    class="cancel-btn"
                    data-appointment-id="<?php echo $row['appointment_id']; ?>">
                    Cancel
                    </button>
                </div>
            </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
</section>

<div class="appointment-modal" id="appointmentModal">
    <div class="appointment-modal-content">
        <button id="closeModal">
            ×
        </button>
        <h2>
        Schedule Appointment
        </h2>
        <form
        action="db_process/admin_appointment_process.php"
        method="POST"
        id="scheduleForm">
        
        <input
        type="hidden"
        name="csrf_token"
        value="<?php
        echo $_SESSION["csrf_token"];
        ?>">

        <input
        type="hidden"
        name="appointment_id"
        id="appointmentId">

        <input
        type="hidden"
        name="action"
        id="appointmentAction"
        value="schedule">
        
        <label>Appointment Date</label>

        <input
        type="date"
        name="appointment_date"
        id="appointmentDate"
        required>

        <label>Appointment Time</label>

        <input
        type="time"
        name="appointment_time"
        id="appointmentTime"
        required>
        
        <button
        type="submit">

        Save Schedule

        </button>
    
    </form>

</div>

</div>
</main>

</div>

<script src="js/admin_appointments.js"></script>

</body>

</html>