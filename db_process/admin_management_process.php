<?php
session_start();

//  Enable MySQLi Exceptions

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once "includes/connection.php";

// Redirect Helper

function redirectToAdminManagement(
    string $tab = "doctors"
): void {
    $allowedTabs = [
        "doctors",
        "patients",
        "bills",
        "resources"
    ];

    if (
        !in_array(
            $tab,
            $allowedTabs,
            true
        )
    ) {
        $tab = "doctors";
    }

    header(
        "Location: ../admin_management.php" .
        "?tab=" . urlencode($tab)
    );

    exit();
}

// Message Helpers

function setAdminManagementError(
    string $message
): void {
    $_SESSION["admin_management_error"] =
        $message;
}

function setAdminManagementSuccess(
    string $message
): void {
    $_SESSION["admin_management_success"] =
        $message;
}

function refreshAdminManagementCsrfToken(): void
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
    redirectToAdminManagement();
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
    setAdminManagementError(
        "Invalid request. Please refresh the page and try again."
    );

    mysqli_close($conn);

    redirectToAdminManagement();
}

// Read Requested Action

$action =
    trim($_POST["action"] ?? "");

$allowedActions = [
    "add_doctor",
    "toggle_doctor_status",
    "toggle_patient_status",
    "create_bill",
    "update_bill_status",
    "add_resource",
    "update_resource_quantity"
];

if (
    !in_array(
        $action,
        $allowedActions,
        true
    )
) {
    setAdminManagementError(
        "Invalid management action."
    );

    mysqli_close($conn);

    redirectToAdminManagement();
}

//  Action Routing

if ($action === "add_doctor") {

    // Get Doctor Form Data

    $firstName =
        trim($_POST["first_name"] ?? "");

    $lastName =
        trim($_POST["last_name"] ?? "");

    $username =
        trim($_POST["username"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";

    $phone =
        trim($_POST["phone"] ?? "");

    $departmentId =
        filter_input(
            INPUT_POST,
            "department_id",
            FILTER_VALIDATE_INT
        );

    $specialization =
        trim($_POST["specialization"] ?? "");

    $availability =
        trim($_POST["availability"] ?? "");

    //  Required Fields

    if (
        $firstName === "" ||
        $lastName === "" ||
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $phone === "" ||
        !$departmentId ||
        $specialization === "" ||
        $availability === ""
    ) {
        setAdminManagementError(
            "Please complete all required doctor fields."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Name Validation

    if (
        !preg_match(
            "/^[A-Za-z][A-Za-z\s'-]{1,99}$/",
            $firstName
        )
    ) {
        setAdminManagementError(
            "Please enter a valid doctor first name."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    if (
        !preg_match(
            "/^[A-Za-z][A-Za-z\s'-]{1,99}$/",
            $lastName
        )
    ) {
        setAdminManagementError(
            "Please enter a valid doctor last name."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Username Validation

    if (
        !preg_match(
            "/^[A-Za-z0-9_.]{4,100}$/",
            $username
        )
    ) {
        setAdminManagementError(
            "Username must contain 4 to 100 letters, numbers, underscores, or dots."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    // Email Validation

    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        setAdminManagementError(
            "Please enter a valid email address."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Phone Validation

    if (
        !preg_match(
            "/^[0-9]{10,15}$/",
            $phone
        )
    ) {
        setAdminManagementError(
            "Phone number must contain between 10 and 15 digits."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Password Validation

    if (strlen($password) < 8) {
        setAdminManagementError(
            "Password must contain at least 8 characters."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    if (
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[0-9]/", $password)
    ) {
        setAdminManagementError(
            "Password must contain uppercase, lowercase, and numeric characters."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Text Length Validation

    if (
        mb_strlen($specialization) < 2 ||
        mb_strlen($specialization) > 150
    ) {
        setAdminManagementError(
            "Specialization must contain between 2 and 150 characters."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    if (
        mb_strlen($availability) < 3 ||
        mb_strlen($availability) > 255
    ) {
        setAdminManagementError(
            "Availability must contain between 3 and 255 characters."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Validate Department

    $departmentSql = "
        SELECT department_id
        FROM departments
        WHERE department_id = ?
        LIMIT 1
    ";

    $departmentStmt =
        mysqli_prepare(
            $conn,
            $departmentSql
        );

    mysqli_stmt_bind_param(
        $departmentStmt,
        "i",
        $departmentId
    );

    mysqli_stmt_execute(
        $departmentStmt
    );

    $departmentResult =
        mysqli_stmt_get_result(
            $departmentStmt
        );

    $department =
        mysqli_fetch_assoc(
            $departmentResult
        );

    mysqli_stmt_close(
        $departmentStmt
    );

    if (!$department) {
        setAdminManagementError(
            "The selected department does not exist."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    // Check Username or Email Duplicate

    $duplicateSql = "
        SELECT user_id
        FROM users
        WHERE username = ?
           OR email = ?
        LIMIT 1
    ";

    $duplicateStmt =
        mysqli_prepare(
            $conn,
            $duplicateSql
        );

    mysqli_stmt_bind_param(
        $duplicateStmt,
        "ss",
        $username,
        $email
    );

    mysqli_stmt_execute(
        $duplicateStmt
    );

    $duplicateResult =
        mysqli_stmt_get_result(
            $duplicateStmt
        );

    $duplicateUser =
        mysqli_fetch_assoc(
            $duplicateResult
        );

    mysqli_stmt_close(
        $duplicateStmt
    );

    if ($duplicateUser) {
        setAdminManagementError(
            "The username or email address is already in use."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    // Create Doctor Account

    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );

    if ($passwordHash === false) {
        setAdminManagementError(
            "The doctor password could not be secured."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    mysqli_begin_transaction($conn);

    try {

        // Create users record
        // role_id 2 = Doctor

        $userSql = "
            INSERT INTO users
            (
                role_id,
                username,
                email,
                password,
                status,
                created_at
            )
            VALUES
            (
                2,
                ?,
                ?,
                ?,
                'Active',
                NOW()
            )
        ";

        $userStmt =
            mysqli_prepare(
                $conn,
                $userSql
            );

        mysqli_stmt_bind_param(
            $userStmt,
            "sss",
            $username,
            $email,
            $passwordHash
        );

        mysqli_stmt_execute(
            $userStmt
        );

        $newUserId =
            mysqli_insert_id($conn);

        mysqli_stmt_close(
            $userStmt
        );

        if ($newUserId <= 0) {
            throw new mysqli_sql_exception(
                "Doctor user account creation failed."
            );
        }

        // Create doctors record

        $doctorSql = "
            INSERT INTO doctors
            (
                user_id,
                department_id,
                first_name,
                last_name,
                phone,
                specialization,
                availability,
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
                ?,
                NOW()
            )
        ";

        $doctorStmt =
            mysqli_prepare(
                $conn,
                $doctorSql
            );

        mysqli_stmt_bind_param(
            $doctorStmt,
            "iisssss",
            $newUserId,
            $departmentId,
            $firstName,
            $lastName,
            $phone,
            $specialization,
            $availability
        );

        mysqli_stmt_execute(
            $doctorStmt
        );

        if (
            mysqli_stmt_affected_rows(
                $doctorStmt
            ) !== 1
        ) {
            throw new mysqli_sql_exception(
                "Doctor profile creation failed."
            );
        }

        mysqli_stmt_close(
            $doctorStmt
        );

        mysqli_commit($conn);

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Doctor account created successfully."
        );

    } catch (mysqli_sql_exception $e) {

        mysqli_rollback($conn);

        error_log(
            "Add doctor error: " .
            $e->getMessage()
        );

        setAdminManagementError(
            "The doctor account could not be created. Please try again."
        );
    }

    mysqli_close($conn);

    redirectToAdminManagement(
        "doctors"
    );

} elseif (
    $action === "toggle_doctor_status"
) {

    //  Validate User ID

    $doctorUserId =
        filter_input(
            INPUT_POST,
            "user_id",
            FILTER_VALIDATE_INT
        );

    if (!$doctorUserId) {
        setAdminManagementError(
            "Invalid doctor account selected."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    // Load Doctor Account

    $doctorAccountSql = "
        SELECT
            u.user_id,
            u.status,
            d.doctor_id,
            d.first_name,
            d.last_name
        FROM users u
        INNER JOIN doctors d
            ON u.user_id = d.user_id
        WHERE u.user_id = ?
          AND u.role_id = 2
        LIMIT 1
    ";

    $doctorAccountStmt =
        mysqli_prepare(
            $conn,
            $doctorAccountSql
        );

    mysqli_stmt_bind_param(
        $doctorAccountStmt,
        "i",
        $doctorUserId
    );

    mysqli_stmt_execute(
        $doctorAccountStmt
    );

    $doctorAccountResult =
        mysqli_stmt_get_result(
            $doctorAccountStmt
        );

    $doctorAccount =
        mysqli_fetch_assoc(
            $doctorAccountResult
        );

    mysqli_stmt_close(
        $doctorAccountStmt
    );

    if (!$doctorAccount) {
        setAdminManagementError(
            "The selected doctor account could not be found."
        );

        mysqli_close($conn);

        redirectToAdminManagement(
            "doctors"
        );
    }

    //  Determine New Status

    $newStatus =
        $doctorAccount["status"] === "Active"
            ? "Inactive"
            : "Active";

    // Update Doctor Status

    try {

        $statusSql = "
            UPDATE users
            SET status = ?
            WHERE user_id = ?
              AND role_id = 2
        ";

        $statusStmt =
            mysqli_prepare(
                $conn,
                $statusSql
            );

        mysqli_stmt_bind_param(
            $statusStmt,
            "si",
            $newStatus,
            $doctorUserId
        );

        mysqli_stmt_execute(
            $statusStmt
        );

        if (
            mysqli_stmt_affected_rows(
                $statusStmt
            ) !== 1
        ) {
            throw new mysqli_sql_exception(
                "Doctor status update failed."
            );
        }

        mysqli_stmt_close(
            $statusStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Doctor account status changed to " .
            $newStatus .
            "."
        );

    } catch (mysqli_sql_exception $e) {

        error_log(
            "Doctor status error: " .
            $e->getMessage()
        );

        setAdminManagementError(
            "The doctor account status could not be changed."
        );
    }

    mysqli_close($conn);

    redirectToAdminManagement(
        "doctors"
    );

} elseif ($action === "toggle_patient_status") {

    // Validate Patient User ID

    $patientUserId = filter_input(
        INPUT_POST,
        "user_id",
        FILTER_VALIDATE_INT
    );

    if (!$patientUserId) {

        setAdminManagementError(
            "Invalid patient selected."
        );

        mysqli_close($conn);

        redirectToAdminManagement("patients");
    }

    //  Load Patient

    $patientSql = "
        SELECT
            user_id,
            status
        FROM users
        WHERE user_id = ?
          AND role_id = 3
        LIMIT 1
    ";

    $patientStmt = mysqli_prepare(
        $conn,
        $patientSql
    );

    mysqli_stmt_bind_param(
        $patientStmt,
        "i",
        $patientUserId
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

        setAdminManagementError(
            "Patient not found."
        );

        mysqli_close($conn);

        redirectToAdminManagement("patients");
    }

    // Determine New Status

    $newStatus =
        $patient["status"] === "Active"
            ? "Inactive"
            : "Active";

    //  Update Status

    try {

        $updateSql = "
            UPDATE users
            SET status = ?
            WHERE user_id = ?
              AND role_id = 3
        ";

        $updateStmt = mysqli_prepare(
            $conn,
            $updateSql
        );

        mysqli_stmt_bind_param(
            $updateStmt,
            "si",
            $newStatus,
            $patientUserId
        );

        mysqli_stmt_execute(
            $updateStmt
        );

        mysqli_stmt_close(
            $updateStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Patient account updated successfully."
        );

    } catch (mysqli_sql_exception $e) {

        error_log(
            $e->getMessage()
        );

        setAdminManagementError(
            "Unable to update patient account."
        );

    }

    mysqli_close($conn);

    redirectToAdminManagement("patients");

} elseif ($action === "create_bill") {

    //  Validate Appointment

    $appointmentId = filter_input(
        INPUT_POST,
        "appointment_id",
        FILTER_VALIDATE_INT
    );

    if (!$appointmentId) {

        setAdminManagementError(
            "Invalid appointment selected."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");
    }

    $consultationFee =
        (float)($_POST["consultation_fee"] ?? 0);

    $laboratoryFee =
        (float)($_POST["laboratory_fee"] ?? 0);

    $medicineFee =
        (float)($_POST["medicine_fee"] ?? 0);

    $otherFee =
        (float)($_POST["other_fee"] ?? 0);

    if (
        $consultationFee < 0 ||
        $laboratoryFee < 0 ||
        $medicineFee < 0 ||
        $otherFee < 0
    ) {

        setAdminManagementError(
            "Invalid bill amount."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");
    }

    // Load Appointment

    $appointmentSql = "
        SELECT
            appointment_id,
            patient_id
        FROM appointments
        WHERE appointment_id = ?
          AND status = 'Completed'
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

        setAdminManagementError(
            "Completed appointment not found."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");
    }

    // Check Existing Bill

    $existsSql = "
        SELECT bill_id
        FROM bills
        WHERE appointment_id = ?
        LIMIT 1
    ";

    $existsStmt =
        mysqli_prepare(
            $conn,
            $existsSql
        );

    mysqli_stmt_bind_param(
        $existsStmt,
        "i",
        $appointmentId
    );

    mysqli_stmt_execute(
        $existsStmt
    );

    $existsResult =
        mysqli_stmt_get_result(
            $existsStmt
        );

    if (
        mysqli_num_rows($existsResult) > 0
    ) {

        mysqli_stmt_close(
            $existsStmt
        );

        setAdminManagementError(
            "A bill already exists for this appointment."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");
    }

    mysqli_stmt_close(
        $existsStmt
    );

    // Calculate Total

    $totalAmount =
        $consultationFee +
        $laboratoryFee +
        $medicineFee +
        $otherFee;

    // Insert Bill

    try {

        $insertSql = "
            INSERT INTO bills
            (
                patient_id,
                appointment_id,
                consultation_fee,
                laboratory_fee,
                medicine_fee,
                other_fee,
                total_amount,
                payment_status,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?,
                ?, 'Pending', NOW()
            )
        ";

        $insertStmt =
            mysqli_prepare(
                $conn,
                $insertSql
            );

        mysqli_stmt_bind_param(
            $insertStmt,
            "iiddddd",
            $appointment["patient_id"],
            $appointmentId,
            $consultationFee,
            $laboratoryFee,
            $medicineFee,
            $otherFee,
            $totalAmount
        );

        mysqli_stmt_execute(
            $insertStmt
        );

        mysqli_stmt_close(
            $insertStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Bill created successfully."
        );

    } catch (mysqli_sql_exception $e) {

        error_log(
            $e->getMessage()
        );

        setAdminManagementError(
            "Unable to create bill."
        );

    }

    mysqli_close($conn);

    redirectToAdminManagement("bills");

} elseif ($action === "update_bill_status") {

    // Validate Bill ID

    $billId = filter_input(
        INPUT_POST,
        "bill_id",
        FILTER_VALIDATE_INT
    );

    if (!$billId) {

        setAdminManagementError(
            "Invalid bill selected."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");

    }

    // Validate Payment Status

    $paymentStatus = trim(
        $_POST["payment_status"] ?? ""
    );

    $allowedStatuses = [
        "Pending",
        "Paid"
    ];

    if (
        !in_array(
            $paymentStatus,
            $allowedStatuses,
            true
        )
    ) {

        setAdminManagementError(
            "Invalid payment status."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");

    }

    //  Check Bill Exists

    $billSql = "
        SELECT
            bill_id,
            payment_status
        FROM bills
        WHERE bill_id = ?
        LIMIT 1
    ";

    $billStmt = mysqli_prepare(
        $conn,
        $billSql
    );

    mysqli_stmt_bind_param(
        $billStmt,
        "i",
        $billId
    );

    mysqli_stmt_execute(
        $billStmt
    );

    $billResult =
        mysqli_stmt_get_result(
            $billStmt
        );

    $bill =
        mysqli_fetch_assoc(
            $billResult
        );

    mysqli_stmt_close(
        $billStmt
    );

    if (!$bill) {

        setAdminManagementError(
            "Bill not found."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");

    }

    // Already Same Status

    if (
        $bill["payment_status"] ===
        $paymentStatus
    ) {

        setAdminManagementSuccess(
            "No changes were made."
        );

        mysqli_close($conn);

        redirectToAdminManagement("bills");

    }

    //  Update Bill Status

    try {

        $updateSql = "
            UPDATE bills
            SET payment_status = ?
            WHERE bill_id = ?
        ";

        $updateStmt =
            mysqli_prepare(
                $conn,
                $updateSql
            );

        mysqli_stmt_bind_param(
            $updateStmt,
            "si",
            $paymentStatus,
            $billId
        );

        mysqli_stmt_execute(
            $updateStmt
        );

        if (
            mysqli_stmt_affected_rows(
                $updateStmt
            ) !== 1
        ) {

            throw new mysqli_sql_exception(
                "Unable to update bill."
            );

        }

        mysqli_stmt_close(
            $updateStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Bill payment status updated successfully."
        );

    } catch (
        mysqli_sql_exception $e
    ) {

        error_log(
            "Update Bill Status Error: " .
            $e->getMessage()
        );

        setAdminManagementError(
            "Unable to update bill payment status."
        );

    }

    mysqli_close($conn);

    redirectToAdminManagement("bills");

} elseif (
    $action === "add_resource"
) {

     //  Get Resource Data

    $resourceName = trim(
        $_POST["resource_name"] ?? ""
    );

    $resourceType = trim(
        $_POST["resource_type"] ?? ""
    );

    $quantity = filter_input(
        INPUT_POST,
        "quantity",
        FILTER_VALIDATE_INT
    );

    if (
        $resourceName === "" ||
        $resourceType === "" ||
        $quantity === false ||
        $quantity < 0
    ) {

        setAdminManagementError(
            "Please complete all resource information correctly."
        );

        mysqli_close($conn);

        redirectToAdminManagement("resources");

    }

     // Check Duplicate Resource

    $duplicateSql = "
        SELECT resource_id
        FROM resources
        WHERE resource_name = ?
        LIMIT 1
    ";

    $duplicateStmt = mysqli_prepare(
        $conn,
        $duplicateSql
    );

    mysqli_stmt_bind_param(
        $duplicateStmt,
        "s",
        $resourceName
    );

    mysqli_stmt_execute(
        $duplicateStmt
    );

    $duplicateResult =
        mysqli_stmt_get_result(
            $duplicateStmt
        );

    if (
        mysqli_num_rows(
            $duplicateResult
        ) > 0
    ) {

        mysqli_stmt_close(
            $duplicateStmt
        );

        setAdminManagementError(
            "This resource already exists."
        );

        mysqli_close($conn);

        redirectToAdminManagement("resources");

    }

    mysqli_stmt_close(
        $duplicateStmt
    );

     //  Determine Status

    $status =
        ($quantity > 0)
            ? "Available"
            : "Unavailable";

    // Insert Resource

    try {

        $insertSql = "
            INSERT INTO resources
            (
                resource_name,
                resource_type,
                quantity,
                status,
                created_at
            )
            VALUES
            (
                ?, ?, ?, ?, NOW()
            )
        ";

        $insertStmt =
            mysqli_prepare(
                $conn,
                $insertSql
            );

        mysqli_stmt_bind_param(
            $insertStmt,
            "ssis",
            $resourceName,
            $resourceType,
            $quantity,
            $status
        );

        mysqli_stmt_execute(
            $insertStmt
        );

        mysqli_stmt_close(
            $insertStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Resource added successfully."
        );

    } catch (
        mysqli_sql_exception $e
    ) {

        error_log(
            "Add Resource Error: " .
            $e->getMessage()
        );

        setAdminManagementError(
            "Unable to add resource."
        );

    }

    mysqli_close($conn);

    redirectToAdminManagement("resources");

} elseif (
    $action === "update_resource_quantity"
) {

    // Validate Input

    $resourceId = filter_input(
        INPUT_POST,
        "resource_id",
        FILTER_VALIDATE_INT
    );

    $quantity = filter_input(
        INPUT_POST,
        "quantity",
        FILTER_VALIDATE_INT
    );

    if (
        !$resourceId ||
        $quantity === false ||
        $quantity < 0
    ) {

        setAdminManagementError(
            "Invalid resource information."
        );

        mysqli_close($conn);

        redirectToAdminManagement("resources");

    }

    // Check Resource Exists

    $resourceSql = "
        SELECT resource_id
        FROM resources
        WHERE resource_id = ?
        LIMIT 1
    ";

    $resourceStmt = mysqli_prepare(
        $conn,
        $resourceSql
    );

    mysqli_stmt_bind_param(
        $resourceStmt,
        "i",
        $resourceId
    );

    mysqli_stmt_execute(
        $resourceStmt
    );

    $resourceResult =
        mysqli_stmt_get_result(
            $resourceStmt
        );

    $resource =
        mysqli_fetch_assoc(
            $resourceResult
        );

    mysqli_stmt_close(
        $resourceStmt
    );

    if (!$resource) {

        setAdminManagementError(
            "Resource not found."
        );

        mysqli_close($conn);

        redirectToAdminManagement("resources");

    }

    //  Update Quantity

    $status =
        ($quantity > 0)
            ? "Available"
            : "Unavailable";

    try {

        $updateSql = "
            UPDATE resources
            SET
                quantity = ?,
                status = ?
            WHERE resource_id = ?
        ";

        $updateStmt =
            mysqli_prepare(
                $conn,
                $updateSql
            );

        mysqli_stmt_bind_param(
            $updateStmt,
            "isi",
            $quantity,
            $status,
            $resourceId
        );

        mysqli_stmt_execute(
            $updateStmt
        );

        if (
            mysqli_stmt_affected_rows(
                $updateStmt
            ) < 0
        ) {

            throw new mysqli_sql_exception(
                "Resource update failed."
            );

        }

        mysqli_stmt_close(
            $updateStmt
        );

        refreshAdminManagementCsrfToken();

        setAdminManagementSuccess(
            "Resource updated successfully."
        );

    } catch (
        mysqli_sql_exception $e
    ) {

        error_log(
            "Update Resource Error: " .
            $e->getMessage()
        );

        setAdminManagementError(
            "Unable to update resource."
        );

    }

    mysqli_close($conn);

    redirectToAdminManagement("resources");

}

// Fallback

setAdminManagementError(
    "The requested action could not be completed."
);

mysqli_close($conn);

redirectToAdminManagement();
