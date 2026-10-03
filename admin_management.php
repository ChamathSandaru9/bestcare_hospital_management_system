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
    header("Location: login.php");
    exit();
}

if ((int)$_SESSION["role_id"] !== 1) {

    header("Location: login.php");
    exit();

}

//    CSRF Token

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));

}

//    Helper

function e($value)
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}

//    Load Departments

$departments = [];

$departmentSql = "

SELECT *

FROM departments

ORDER BY department_name

";

$departmentResult =
mysqli_query(
$conn,
$departmentSql
);

while(
$row=mysqli_fetch_assoc(
$departmentResult
)){
    $departments[]=$row;
}

//    Load Doctors

$doctors=[];

$doctorSql="

SELECT

d.*,

u.username,

u.email,

u.status,

dep.department_name

FROM doctors d

INNER JOIN users u

ON d.user_id=u.user_id

LEFT JOIN departments dep

ON d.department_id=dep.department_id

ORDER BY

d.first_name,
d.last_name

";

$doctorResult=
mysqli_query(
$conn,
$doctorSql
);

while(
$row=mysqli_fetch_assoc(
$doctorResult
)){
    $doctors[]=$row;
}

//    Load Patients

$patients=[];

$patientSql="

SELECT

p.*,

u.username,

u.status

FROM patients p

INNER JOIN users u

ON p.user_id=u.user_id

ORDER BY

p.first_name,
p.last_name

";

$patientResult=
mysqli_query(
$conn,
$patientSql
);

while(
$row=mysqli_fetch_assoc(
$patientResult
)){
    $patients[]=$row;
}

//    Load Bills

$bills=[];

$billSql="

SELECT *

FROM bills

ORDER BY created_at DESC

";

$billResult=
mysqli_query(
$conn,
$billSql
);

while(
$row=mysqli_fetch_assoc(
$billResult
)){
    $bills[]=$row;
}

//    Load Resources

$resources=[];

$resourceSql="

SELECT *

FROM resources

ORDER BY resource_name

";

$resourceResult=
mysqli_query(
$conn,
$resourceSql
);

while(
$row=mysqli_fetch_assoc(
$resourceResult
)){
    $resources[]=$row;
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BestCare Hospital</title>
    
    <link rel="icon" type="image/png" href="image/logo.png">

    <link rel="stylesheet" href="css/admin_management.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<div class="admin-layout">

    <!-- Admin Sidebar -->

    <aside
        class="admin-sidebar"
        id="adminSidebar"
    >

        <h2>BestCare</h2>

        <ul>

            <li>

                <a href="admin_appointments.php">

                    <i class="fa-solid fa-calendar-days"></i>

                    <span>Appointments</span>

                </a>

            </li>

            <li class="active">

                <a href="admin_management.php">

                    <i class="fa-solid fa-gears"></i>

                    <span>Management</span>

                </a>

            </li>

            <li>

                <a href="logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    <span>Logout</span>

                </a>

            </li>

        </ul>

    </aside>

    <!-- Mobile Sidebar Overlay -->

    <div
        class="admin-sidebar-overlay"
        id="adminSidebarOverlay"
        aria-hidden="true"
    ></div>

    <!-- Main Content -->

    <main class="admin-main">

        <!-- Mobile Header -->

        <header class="admin-mobile-header">

            <button
                type="button"
                class="admin-sidebar-toggle"
                id="adminSidebarToggle"
                aria-label="Open navigation menu"
                aria-controls="adminSidebar"
                aria-expanded="false"
            >

                <i class="fa-solid fa-bars"></i>

            </button>

            <div class="admin-page-heading">

                <h1>Management</h1>

                <p>
                    Manage doctors, patients, bills and hospital resources.
                </p>

            </div>

        </header>
        <?php if (!empty($_SESSION["admin_management_success"])): ?>
            <div class="success-message">
                <?php
                echo e($_SESSION["admin_management_success"]);
                unset($_SESSION["admin_management_success"]);
                ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION["admin_management_error"])): ?>
            <div class="error-message">
                <?php
                echo e($_SESSION["admin_management_error"]);
                unset($_SESSION["admin_management_error"]);
                ?>
            </div>
        <?php endif; ?>

        <!-- Management Tabs -->

        <nav
            class="management-tabs"
            aria-label="Management sections"
        >

            <button
                type="button"
                class="tab-button active"
                id="doctorsTab"
                data-tab="doctors"
                aria-controls="doctors"
                aria-selected="true"
            >
                Doctors
            </button>

            <button
                type="button"
                class="tab-button"
                id="patientsTab"
                data-tab="patients"
                aria-controls="patients"
                aria-selected="false"
            >
                Patients
            </button>

            <button
                type="button"
                class="tab-button"
                id="billsTab"
                data-tab="bills"
                aria-controls="bills"
                aria-selected="false"
            >
                Bills
            </button>

            <button
                type="button"
                class="tab-button"
                id="resourcesTab"
                data-tab="resources"
                aria-controls="resources"
                aria-selected="false"
            >
                Resources
            </button>

        </nav>
         <!-- Doctors Tab -->

        <section
            class="management-section active"
            id="doctors"
            role="tabpanel"
            aria-labelledby="doctorsTab"
        >

            <div class="section-header">

                <div>

                    <h2>Doctors</h2>

                    <p>
                        Add doctors and manage their account status.
                    </p>

                </div>

                <button
                    type="button"
                    class="primary-btn"
                    id="addDoctorBtn"
                >

                    <i class="fa-solid fa-plus"></i>

                    <span>Add Doctor</span>

                </button>

            </div>

            <div class="management-table-wrapper">

                <table class="management-table">

                    <thead>

                        <tr>

                            <th scope="col">Name</th>

                            <th scope="col">Department</th>

                            <th scope="col">Specialization</th>

                            <th scope="col">Availability</th>

                            <th scope="col">Status</th>

                            <th scope="col">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($doctors) > 0): ?>

                            <?php foreach ($doctors as $doctor): ?>

                                <?php

                                $doctorStatus =
                                    strtolower(
                                        trim(
                                            $doctor["status"] ?? ""
                                        )
                                    );

                                ?>

                                <tr>

                                    <td class="person-name">

                                        Dr.
                                        <?php
                                        echo e(
                                            $doctor["first_name"]
                                            . " "
                                            . $doctor["last_name"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $doctor["department_name"]
                                            ?: "Not assigned"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $doctor["specialization"]
                                            ?: "Not specified"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $doctor["availability"]
                                            ?: "Not specified"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php echo e($doctorStatus); ?>"
                                        >

                                            <?php
                                            echo e(
                                                $doctor["status"]
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            class="table-action-form"
                                            action="db_process/admin_management_process.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo e($_SESSION["csrf_token"]); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="toggle_doctor_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php echo (int)$doctor["user_id"]; ?>"
                                            >

                                            <button
                                                class="secondary-btn"
                                                type="submit"
                                            >

                                                <?php if ($doctor["status"] === "Active"): ?>

                                                    Deactivate

                                                <?php else: ?>

                                                    Activate

                                                <?php endif; ?>

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="empty-table-message"
                                >

                                    <i class="fa-solid fa-user-doctor"></i>

                                    <span>No doctors found.</span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Patients Tab -->

        <section
            class="management-section"
            id="patients"
            role="tabpanel"
            aria-labelledby="patientsTab"
            hidden
        >

            <div class="section-header">

                <div>

                    <h2>Patients</h2>

                    <p>
                        View registered patients and manage their account status.
                    </p>

                </div>

            </div>

            <div class="management-table-wrapper">

                <table class="management-table">

                    <thead>

                        <tr>

                            <th scope="col">Patient ID</th>

                            <th scope="col">Name</th>

                            <th scope="col">Gender</th>

                            <th scope="col">Blood Group</th>

                            <th scope="col">Status</th>

                            <th scope="col">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($patients) > 0): ?>

                            <?php foreach ($patients as $patient): ?>

                                <?php

                                $patientStatus =
                                    strtolower(
                                        trim(
                                            $patient["status"] ?? ""
                                        )
                                    );

                                ?>

                                <tr>

                                    <td>

                                        <?php
                                        echo (int)$patient["patient_id"];
                                        ?>

                                    </td>

                                    <td class="person-name">

                                        <?php
                                        echo e(
                                            $patient["first_name"]
                                            . " "
                                            . $patient["last_name"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $patient["gender"]
                                            ?: "Not specified"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $patient["blood_group"]
                                            ?: "Not specified"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php echo e($patientStatus); ?>"
                                        >

                                            <?php
                                            echo e(
                                                $patient["status"]
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            class="table-action-form"
                                            action="db_process/admin_management_process.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo e($_SESSION["csrf_token"]); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="toggle_patient_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php echo (int)$patient["user_id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="secondary-btn"
                                            >

                                                <?php if ($patient["status"] === "Active"): ?>

                                                    Deactivate

                                                <?php else: ?>

                                                    Activate

                                                <?php endif; ?>

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="empty-table-message"
                                >

                                    <i class="fa-solid fa-user-group"></i>

                                    <span>No patients found.</span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Bills Tab -->

        <section
            class="management-section"
            id="bills"
            role="tabpanel"
            aria-labelledby="billsTab"
            hidden
        >

            <div class="section-header">

                <div>

                    <h2>Bills</h2>

                    <p>
                        Create patient bills and update their payment status.
                    </p>

                </div>

                <button
                    type="button"
                    class="primary-btn"
                    id="createBillBtn"
                >

                    <i class="fa-solid fa-plus"></i>

                    <span>Create Bill</span>

                </button>

            </div>

            <div class="management-table-wrapper">

                <table class="management-table">

                    <thead>

                        <tr>

                            <th scope="col">Bill ID</th>

                            <th scope="col">Patient ID</th>

                            <th scope="col">Appointment</th>

                            <th scope="col">Total Amount</th>

                            <th scope="col">Payment Status</th>

                            <th scope="col">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($bills) > 0): ?>

                            <?php foreach ($bills as $bill): ?>

                                <?php

                                $billStatus =
                                    strtolower(
                                        trim(
                                            $bill["payment_status"] ?? ""
                                        )
                                    );

                                ?>

                                <tr>

                                    <td>

                                        <?php
                                        echo (int)$bill["bill_id"];
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo (int)$bill["patient_id"];
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo (int)$bill["appointment_id"];
                                        ?>

                                    </td>

                                    <td class="amount-cell">

                                        LKR
                                        <?php
                                        echo number_format(
                                            (float)$bill["total_amount"],
                                            2
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php echo e($billStatus); ?>"
                                        >

                                            <?php
                                            echo e(
                                                $bill["payment_status"]
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            class="table-action-form"
                                            action="db_process/admin_management_process.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo e($_SESSION["csrf_token"]); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="update_bill_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="bill_id"
                                                value="<?php echo (int)$bill["bill_id"]; ?>"
                                            >

                                            <select
                                                name="payment_status"
                                                aria-label="Payment status"
                                                required
                                            >

                                                <option
                                                    value="Pending"
                                                    <?php
                                                    echo (
                                                        $bill["payment_status"] === "Pending"
                                                    )
                                                        ? "selected"
                                                        : "";
                                                    ?>
                                                >
                                                    Pending
                                                </option>

                                                <option
                                                    value="Paid"
                                                    <?php
                                                    echo (
                                                        $bill["payment_status"] === "Paid"
                                                    )
                                                        ? "selected"
                                                        : "";
                                                    ?>
                                                >
                                                    Paid
                                                </option>

                                            </select>

                                            <button
                                                type="submit"
                                                class="secondary-btn"
                                            >
                                                Update
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="empty-table-message"
                                >

                                    <i class="fa-solid fa-file-invoice-dollar"></i>

                                    <span>No bills found.</span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Resources Tab -->

        <section
            class="management-section"
            id="resources"
            role="tabpanel"
            aria-labelledby="resourcesTab"
            hidden
        >

            <div class="section-header">

                <div>

                    <h2>Resources</h2>

                    <p>
                        Add hospital resources and update available quantities.
                    </p>

                </div>

                <button
                    type="button"
                    class="primary-btn"
                    id="addResourceBtn"
                >

                    <i class="fa-solid fa-plus"></i>

                    <span>Add Resource</span>

                </button>

            </div>

            <div class="management-table-wrapper">

                <table class="management-table">

                    <thead>

                        <tr>

                            <th scope="col">Resource</th>

                            <th scope="col">Type</th>

                            <th scope="col">Quantity</th>

                            <th scope="col">Status</th>

                            <th scope="col">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (count($resources) > 0): ?>

                            <?php foreach ($resources as $resource): ?>

                                <?php

                                $resourceStatus =
                                    strtolower(
                                        trim(
                                            $resource["status"] ?? ""
                                        )
                                    );

                                ?>

                                <tr>

                                    <td class="resource-name">

                                        <?php
                                        echo e(
                                            $resource["resource_name"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo e(
                                            $resource["resource_type"]
                                            ?: "Not specified"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo (int)$resource["quantity"];
                                        ?>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php echo e($resourceStatus); ?>"
                                        >

                                            <?php
                                            echo e(
                                                $resource["status"]
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <form
                                            class="table-action-form"
                                            action="db_process/admin_management_process.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo e($_SESSION["csrf_token"]); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="update_resource_quantity"
                                            >

                                            <input
                                                type="hidden"
                                                name="resource_id"
                                                value="<?php echo (int)$resource["resource_id"]; ?>"
                                            >

                                            <input
                                                type="number"
                                                name="quantity"
                                                min="0"
                                                value="<?php echo (int)$resource["quantity"]; ?>"
                                                aria-label="Resource quantity"
                                                required
                                            >

                                            <button
                                                type="submit"
                                                class="secondary-btn"
                                            >
                                                Update
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="empty-table-message"
                                >

                                    <i class="fa-solid fa-boxes-stacked"></i>

                                    <span>No resources found.</span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- Add Doctor Modal -->

        <div
            class="modal"
            id="addDoctorModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="addDoctorModalTitle"
            aria-hidden="true"
        >

            <div class="modal-content">

                <button
                    type="button"
                    class="close-modal"
                    aria-label="Close Add Doctor modal"
                >
                    &times;
                </button>

                <h2 id="addDoctorModalTitle">
                    Add Doctor
                </h2>

                <form
                    class="management-form"
                    action="db_process/admin_management_process.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo e($_SESSION["csrf_token"]); ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="add_doctor"
                    >

                    <div class="form-grid">

                        <div class="form-group">

                            <label for="doctorFirstName">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="doctorFirstName"
                                name="first_name"
                                autocomplete="given-name"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorLastName">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="doctorLastName"
                                name="last_name"
                                autocomplete="family-name"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorUsername">
                                Username
                            </label>

                            <input
                                type="text"
                                id="doctorUsername"
                                name="username"
                                autocomplete="username"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorEmail">
                                Email
                            </label>

                            <input
                                type="email"
                                id="doctorEmail"
                                name="email"
                                autocomplete="email"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorPassword">
                                Password
                            </label>

                            <input
                                type="password"
                                id="doctorPassword"
                                name="password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorPhone">
                                Phone
                            </label>

                            <input
                                type="tel"
                                id="doctorPhone"
                                name="phone"
                                autocomplete="tel"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="doctorDepartment">
                                Department
                            </label>

                            <select
                                id="doctorDepartment"
                                name="department_id"
                                required
                            >

                                <option value="">
                                    Select Department
                                </option>

                                <?php foreach ($departments as $department): ?>

                                    <option
                                        value="<?php echo (int)$department["department_id"]; ?>"
                                    >

                                        <?php
                                        echo e(
                                            $department["department_name"]
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <label for="doctorSpecialization">
                                Specialization
                            </label>

                            <input
                                type="text"
                                id="doctorSpecialization"
                                name="specialization"
                                required
                            >

                        </div>

                        <div class="form-group form-group-full">

                            <label for="doctorAvailability">
                                Availability
                            </label>

                            <input
                                type="text"
                                id="doctorAvailability"
                                name="availability"
                                placeholder="Example: Monday-Friday 9AM-3PM"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="primary-btn modal-submit-btn"
                    >

                        <i class="fa-solid fa-user-doctor"></i>

                        <span>Add Doctor</span>

                    </button>

                </form>

            </div>

        </div>

        <!-- Create Bill Modal -->

        <div
            class="modal"
            id="createBillModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="createBillModalTitle"
            aria-hidden="true"
        >

            <div class="modal-content">

                <button
                    type="button"
                    class="close-modal"
                    aria-label="Close Create Bill modal"
                >
                    &times;
                </button>

                <h2 id="createBillModalTitle">
                    Create Bill
                </h2>

                <form
                    class="management-form"
                    action="db_process/admin_management_process.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo e($_SESSION["csrf_token"]); ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="create_bill"
                    >

                    <div class="form-grid">

                        <div class="form-group form-group-full">

                            <label for="billAppointmentId">
                                Appointment ID
                            </label>

                            <input
                                type="number"
                                id="billAppointmentId"
                                name="appointment_id"
                                min="1"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="consultationFee">
                                Consultation Fee
                            </label>

                            <input
                                type="number"
                                id="consultationFee"
                                name="consultation_fee"
                                min="0"
                                step="0.01"
                                value="0.00"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="laboratoryFee">
                                Laboratory Fee
                            </label>

                            <input
                                type="number"
                                id="laboratoryFee"
                                name="laboratory_fee"
                                min="0"
                                step="0.01"
                                value="0.00"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="medicineFee">
                                Medicine Fee
                            </label>

                            <input
                                type="number"
                                id="medicineFee"
                                name="medicine_fee"
                                min="0"
                                step="0.01"
                                value="0.00"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="otherFee">
                                Other Fee
                            </label>

                            <input
                                type="number"
                                id="otherFee"
                                name="other_fee"
                                min="0"
                                step="0.01"
                                value="0.00"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="primary-btn modal-submit-btn"
                    >

                        <i class="fa-solid fa-file-invoice-dollar"></i>

                        <span>Create Bill</span>

                    </button>

                </form>

            </div>

        </div>

        <!-- Add Resource Modal -->

        <div
            class="modal"
            id="addResourceModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="addResourceModalTitle"
            aria-hidden="true"
        >

            <div class="modal-content">

                <button
                    type="button"
                    class="close-modal"
                    aria-label="Close Add Resource modal"
                >
                    &times;
                </button>

                <h2 id="addResourceModalTitle">
                    Add Resource
                </h2>

                <form
                    class="management-form"
                    action="db_process/admin_management_process.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo e($_SESSION["csrf_token"]); ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="add_resource"
                    >

                    <div class="form-grid">

                        <div class="form-group">

                            <label for="resourceName">
                                Resource Name
                            </label>

                            <input
                                type="text"
                                id="resourceName"
                                name="resource_name"
                                required
                            >

                        </div>

                        <div class="form-group">

                            <label for="resourceType">
                                Resource Type
                            </label>

                            <input
                                type="text"
                                id="resourceType"
                                name="resource_type"
                                required
                            >

                        </div>

                        <div class="form-group form-group-full">

                            <label for="resourceQuantity">
                                Quantity
                            </label>

                            <input
                                type="number"
                                id="resourceQuantity"
                                name="quantity"
                                min="0"
                                value="0"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="primary-btn modal-submit-btn"
                    >

                        <i class="fa-solid fa-boxes-stacked"></i>

                        <span>Add Resource</span>

                    </button>

                </form>

            </div>

        </div>
        </main>

</div>

<script src="js/admin_management.js"></script>
</body>
</html>