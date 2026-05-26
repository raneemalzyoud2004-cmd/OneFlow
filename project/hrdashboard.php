<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

include "config.php";

$full_name = $_SESSION['full_name'];
$user_id = (int) $_SESSION['user_id'];

$notificationCountResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $user_id
    AND is_read = 0
");

$notificationCount = 0;

if ($notificationCountResult) {
    $notificationCount = (int) mysqli_fetch_assoc($notificationCountResult)['total'];
}

$successMessage = "";
$errorMessage = "";

mysqli_query($conn, "ALTER TABLE users ADD COLUMN IF NOT EXISTS birth_date DATE NULL");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS hr_todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_text VARCHAR(255) NOT NULL,
        status ENUM('pending','done') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS birthday_rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reward_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        birthday_message TEXT NOT NULL,
        reward_year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

mysqli_query($conn, "ALTER TABLE birthday_rewards ADD COLUMN IF NOT EXISTS reward_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00");
mysqli_query($conn, "ALTER TABLE birthday_rewards ADD COLUMN IF NOT EXISTS birthday_message TEXT NOT NULL");
mysqli_query($conn, "ALTER TABLE birthday_rewards ADD COLUMN IF NOT EXISTS reward_year INT NOT NULL");
mysqli_query($conn, "ALTER TABLE birthday_rewards ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

if (isset($_POST['send_birthday_reward'])) {
    $birthdayUserId = intval($_POST['birthday_user_id']);
    $birthdayMessage = mysqli_real_escape_string($conn, trim($_POST['birthday_message']));
    $rewardAmount = floatval($_POST['reward_amount']);
    $currentYear = date("Y");

    $checkReward = mysqli_query($conn, "
        SELECT id
        FROM birthday_rewards
        WHERE user_id = $birthdayUserId
        AND reward_year = $currentYear
        LIMIT 1
    ");

    if ($checkReward && mysqli_num_rows($checkReward) > 0) {
        $errorMessage = "Birthday message and gift were already sent to this employee this year.";
    } else {
        $insertReward = mysqli_query($conn, "
            INSERT INTO birthday_rewards
            (user_id, reward_amount, birthday_message, reward_year)
            VALUES
            ($birthdayUserId, $rewardAmount, '$birthdayMessage', $currentYear)
        ");

        if ($insertReward) {
            $successMessage = "Birthday message and gift sent successfully.";
        } else {
            $errorMessage = "Failed to send birthday message and gift.";
        }
    }
}

if (isset($_POST['add_employee_from_popup'])) {
    $newFullName = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $newUsername = mysqli_real_escape_string($conn, trim($_POST['username']));
    $newEmail = mysqli_real_escape_string($conn, trim($_POST['email']));
    $newPassword = mysqli_real_escape_string($conn, trim($_POST['password']));
    $newSalary = floatval($_POST['salary']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birth_date']);

    $hashedPassword = hash('sha256', $newPassword);

    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$newUsername' LIMIT 1");

    if ($checkUser && mysqli_num_rows($checkUser) > 0) {
        $errorMessage = "Username already exists.";
    } else {
        $insertEmployee = mysqli_query($conn, "
            INSERT INTO users
            (full_name, username, email, password, role, account_status, salary, birth_date, failed_attempts, is_blocked)
            VALUES
            ('$newFullName', '$newUsername', '$newEmail', '$hashedPassword', 'employee', 'active', '$newSalary', '$birthDate', 0, 0)
        ");

        if ($insertEmployee) {
            $successMessage = "Employee added successfully.";
        } else {
            $errorMessage = "Failed to add employee.";
        }
    }
}

if (isset($_POST['approve_leave'])) {
    $leaveId = intval($_POST['leave_id']);
    $updateLeave = mysqli_query($conn, "UPDATE leave_requests SET status='Approved' WHERE id=$leaveId");

    if ($updateLeave) {
        $successMessage = "Leave request approved successfully.";
    } else {
        $errorMessage = "Failed to approve leave request.";
    }
}

if (isset($_POST['reject_leave'])) {
    $leaveId = intval($_POST['leave_id']);
    $updateLeave = mysqli_query($conn, "UPDATE leave_requests SET status='Rejected' WHERE id=$leaveId");

    if ($updateLeave) {
        $successMessage = "Leave request rejected successfully.";
    } else {
        $errorMessage = "Failed to reject leave request.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_hr_todo'])) {
    $taskText = trim($_POST['task_text']);

    if (!empty($taskText)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO hr_todos (task_text, status) VALUES (?, 'pending')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $taskText);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: hrdashboard.php");
    exit();
}

if (isset($_GET['toggle_hr_todo'])) {
    $todoId = (int) $_GET['toggle_hr_todo'];

    $checkResult = mysqli_query($conn, "SELECT status FROM hr_todos WHERE id = $todoId");
    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $todoRow = mysqli_fetch_assoc($checkResult);
        $newStatus = ($todoRow['status'] === 'pending') ? 'done' : 'pending';
        mysqli_query($conn, "UPDATE hr_todos SET status = '$newStatus' WHERE id = $todoId");
    }

    header("Location: hrdashboard.php");
    exit();
}

if (isset($_GET['delete_hr_todo'])) {
    $todoId = (int) $_GET['delete_hr_todo'];
    mysqli_query($conn, "DELETE FROM hr_todos WHERE id = $todoId");

    header("Location: hrdashboard.php");
    exit();
}

$totalEmployees = 0;
$leaveRequests = 0;
$attendanceIssues = 0;
$newApplicants = 0;
$activeEmployees = 0;
$todayBirthdayCount = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee'");
if ($result) {
    $totalEmployees = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee' AND account_status = 'active'");
if ($result) {
    $activeEmployees = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Pending'");
if ($result) {
    $leaveRequests = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE account_status = 'pending_setup'");
if ($result) {
    $newApplicants = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE failed_attempts > 0 OR is_blocked = 1");
if ($result) {
    $attendanceIssues = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'employee'
    AND birth_date IS NOT NULL
    AND MONTH(birth_date) = MONTH(CURDATE())
    AND DAY(birth_date) = DAY(CURDATE())
");
if ($result) {
    $todayBirthdayCount = mysqli_fetch_assoc($result)['total'];
}

$pendingLeaves = mysqli_query($conn, "
    SELECT id, employee_name, leave_type, start_date, end_date, status
    FROM leave_requests
    WHERE status = 'Pending'
    ORDER BY id DESC
    LIMIT 5
");

$pendingAccounts = mysqli_query($conn, "
    SELECT full_name, username, email, account_status
    FROM users
    WHERE account_status = 'pending_setup'
    ORDER BY id DESC
    LIMIT 5
");

$birthdayUsers = mysqli_query($conn, "
    SELECT 
        u.id,
        u.full_name,
        u.username,
        u.birth_date,
        br.reward_amount,
        br.birthday_message
    FROM users u
    LEFT JOIN birthday_rewards br
        ON u.id = br.user_id
        AND br.reward_year = YEAR(CURDATE())
    WHERE u.role = 'employee'
    AND u.birth_date IS NOT NULL
    AND MONTH(u.birth_date) = MONTH(CURDATE())
    AND DAY(u.birth_date) = DAY(CURDATE())
    ORDER BY u.full_name ASC
");

$birthdayMonthUsers = mysqli_query($conn, "
    SELECT 
        u.id,
        u.full_name,
        u.username,
        u.birth_date,
        br.reward_amount,
        br.birthday_message
    FROM users u
    LEFT JOIN birthday_rewards br
        ON u.id = br.user_id
        AND br.reward_year = YEAR(CURDATE())
    WHERE u.role = 'employee'
    AND u.birth_date IS NOT NULL
    AND MONTH(u.birth_date) = MONTH(CURDATE())
    ORDER BY DAY(u.birth_date) ASC
");

$hrTodosResult = mysqli_query($conn, "SELECT * FROM hr_todos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>HR Dashboard - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.search-box {
    position: relative;
    width: 340px;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #14b8a6;
    font-size: 15px;
    z-index: 2;
}

.search-box input {
    width: 100%;
    height: 52px;
    border-radius: 18px;
    border: 1px solid #dbe7f0;
    background: white;
    padding: 0 18px 0 48px;
    font-size: 14px;
    outline: none;
    transition: 0.3s ease;
    box-shadow: 0 8px 20px rgba(15,23,42,0.05);
}

.search-box input:focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 4px rgba(20,184,166,0.12);
}

.hr-dashboard-grid {
    display: grid;
    grid-template-columns: 1.35fr 1fr;
    gap: 24px;
    margin-top: 28px;
}

.hr-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.hr-message {
    padding: 14px 18px;
    border-radius: 16px;
    margin-bottom: 18px;
    font-weight: 800;
}

.hr-message.success {
    background: #dcfce7;
    color: #166534;
}

.hr-message.error {
    background: #fee2e2;
    color: #991b1b;
}

.no-search-results {
    display: none;
    background: #fff7ed;
    color: #9a3412;
    padding: 16px 18px;
    border-radius: 18px;
    font-weight: 800;
    margin-bottom: 20px;
    border: 1px solid #fed7aa;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.quick-card {
    background: linear-gradient(135deg, #f8fbff, #eef8f8);
    border: 1px solid #e3eef2;
    border-radius: 22px;
    padding: 22px;
    text-decoration: none;
    transition: 0.3s;
    cursor: pointer;
    text-align: left;
}

.quick-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 35px rgba(20,184,166,0.16);
}

.quick-card i {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: linear-gradient(135deg, #16c7c1, #22c55e);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    margin-bottom: 16px;
}

.quick-card.purple i {
    background: linear-gradient(135deg, #9333ea, #c084fc);
}

.quick-card.blue i {
    background: linear-gradient(135deg, #2563eb, #60a5fa);
}

.quick-card.orange i {
    background: linear-gradient(135deg, #f97316, #ec4899);
}

.quick-card h4 {
    color: #0D1E4C;
    font-size: 19px;
    margin-bottom: 8px;
}

.quick-card p {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.quick-popup-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.62);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.quick-popup-box {
    width: 720px;
    max-width: 96%;
    max-height: 90vh;
    overflow-y: auto;
    background: white;
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
}

.quick-popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.quick-popup-header h2 {
    color: #0D1E4C;
    font-size: 30px;
}

.quick-popup-header button {
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 14px;
    background: #e2e8f0;
    color: #0D1E4C;
    font-size: 26px;
    cursor: pointer;
}

.popup-subtitle {
    color: #64748b;
    margin-bottom: 22px;
    line-height: 1.6;
}

.popup-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.popup-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 18px;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.popup-item h4 {
    color: #0D1E4C;
    margin-bottom: 5px;
}

.popup-item p {
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
}

.popup-action-btn {
    padding: 10px 16px;
    border-radius: 14px;
    background: linear-gradient(90deg,#0ea5a4,#14b8a6);
    color: white;
    text-decoration: none;
    font-weight: 800;
    white-space: nowrap;
    border: none;
    cursor: pointer;
}

.popup-reject-btn {
    padding: 10px 16px;
    border-radius: 14px;
    background: #fee2e2;
    color: #991b1b;
    font-weight: 800;
    border: none;
    cursor: pointer;
}

.popup-actions-row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.popup-actions-row form {
    margin: 0;
}

.popup-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.popup-form-group label {
    display: block;
    margin-bottom: 8px;
    color: #0D1E4C;
    font-weight: 800;
}

.popup-form-group input {
    width: 100%;
    height: 48px;
    padding: 0 14px;
    border-radius: 14px;
    border: 1px solid #dbe7f0;
    outline: none;
    font-size: 14px;
}

.todo-form {
    display: flex;
    gap: 12px;
    margin-bottom: 18px;
}

.todo-form input {
    flex: 1;
    height: 48px;
    border: 1px solid #dbe7f0;
    border-radius: 14px;
    padding: 0 14px;
    outline: none;
}

.todo-form button {
    height: 48px;
    border: none;
    border-radius: 14px;
    padding: 0 20px;
    background: linear-gradient(90deg,#0ea5a4,#14b8a6);
    color: white;
    font-weight: 800;
    cursor: pointer;
}

.todo-db-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.todo-db-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
}

.todo-db-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.todo-check-btn {
    color: #14b8a6;
    font-size: 22px;
}

.todo-db-text h4 {
    color: #0D1E4C;
    margin-bottom: 5px;
}

.completed-text {
    text-decoration: line-through;
    color: #94a3b8 !important;
}

.todo-badge {
    padding: 6px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.todo-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.todo-badge.done {
    background: #dcfce7;
    color: #166534;
}

.todo-badge.passed {
    background: #e2e8f0;
    color: #475569;
}

.todo-delete-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-todo-text {
    color: #64748b;
    font-weight: 700;
}

.birthday-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.birthday-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px;
    border-radius: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.birthday-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    background: linear-gradient(135deg, #0ea5a4, #14b8a6);
}

.birthday-icon.today {
    background: linear-gradient(135deg, #f97316, #ec4899);
}

.birthday-item h4 {
    color: #0D1E4C;
    margin-bottom: 5px;
}

.birthday-item p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}

.birthday-form-grid {
    display: grid;
    grid-template-columns: 1fr 150px;
    gap: 12px;
    margin-top: 14px;
}

.birthday-form-grid input {
    height: 46px;
    border-radius: 14px;
    border: 1px solid #dbe7f0;
    padding: 0 14px;
    outline: none;
}

.birthday-gift {
    margin-top: 8px;
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    background: #dcfce7;
    color: #166534;
    font-size: 12px;
    font-weight: 800;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 14px;
    border-radius: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.activity-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    background: linear-gradient(135deg, #0ea5a4, #14b8a6);
}

.activity-item h4 {
    color: #0D1E4C;
    margin-bottom: 4px;
}

.activity-item p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}


.notification-bell {
    position: relative;
    cursor: pointer;
    text-decoration: none;
}

.notification-dropdown {
    position: absolute;
    top: 70px;
    right: 0;
    width: 360px;
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 20px 45px rgba(15,23,42,0.18);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: none;
    z-index: 9999;
}

.notification-dropdown.show {
    display: block;
    animation: fadeDropdown .25s ease;
}

@keyframes fadeDropdown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-header {
    padding: 18px;
    font-size: 18px;
    font-weight: 800;
    color: #0D1E4C;
    border-bottom: 1px solid #edf2f7;
    background: #f8fbff;
}

.notification-item-live {
    padding: 16px 18px;
    border-bottom: 1px solid #f1f5f9;
    transition: .25s;
    cursor: pointer;
}

.notification-item-live:hover {
    background: #f8fbff;
}

.notification-item-live h4 {
    color: #0D1E4C;
    font-size: 15px;
    margin-bottom: 6px;
}

.notification-item-live p {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
}

.notification-time {
    color: #94a3b8;
    font-size: 11px;
    margin-top: 7px;
    display: block;
}

.empty-live-notification {
    padding: 18px;
    color: #64748b;
    font-size: 14px;
    text-align: center;
}

@media(max-width: 1150px) {
    .hr-dashboard-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 700px) {
    .popup-form-grid,
    .birthday-form-grid {
        grid-template-columns: 1fr;
    }

    .popup-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-box {
        width: 100%;
    }
}
</style>
</head>

<body>

<div class="dashboard-container">

<aside class="sidebar">
    <div class="sidebar-top">
        <div class="logo-box">
            <i class="fa-solid fa-leaf"></i>
            <h2>OneFlow</h2>
        </div>
        <p class="admin-role">HR Panel</p>
    </div>

    <ul class="sidebar-menu">
        <li class="active"><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
        <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
        <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
        <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
        <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>HR Status</p>
            <h4>Active</h4>
            <span>Workforce operations running</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>HR Dashboard</h1>
        <p>Manage employees, birthdays, leave requests, and recruitment in one place.</p>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>

            <input 
                type="text" 
                id="hrSearch"
                list="hrSearchList"
                placeholder="Search HR dashboard..."
                oninput="searchHRDashboard()"
            >

            <datalist id="hrSearchList">
                <option value="Add Employee"></option>
                <option value="Approve Leaves"></option>
                <option value="Recruitment"></option>
                <option value="Birthdays"></option>
                <option value="HR To-Do List"></option>
                <option value="Recent Activity"></option>
                <option value="Leave Requests"></option>
                <option value="Birthday Notifications"></option>
                <option value="Attention Alerts"></option>
                <option value="Total Employees"></option>
            </datalist>
        </div>

        <div class="icon-btn notification-bell" id="notificationBell">
            <i class="fas fa-bell"></i>

            <?php if ($notificationCount > 0) { ?>
                <span class="notif-count"><?php echo $notificationCount; ?></span>
            <?php } ?>

            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">Notifications</div>
                <div id="notificationListContainer"></div>
            </div>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
            </div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>HR Manager</span>
            </div>
        </div>

<a href="logout.php" class="logout-btn">Logout</a>    </div>
</header>

<?php if (!empty($successMessage)) { ?>
    <div class="hr-message success"><?php echo htmlspecialchars($successMessage); ?></div>
<?php } ?>

<?php if (!empty($errorMessage)) { ?>
    <div class="hr-message error"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php } ?>

<div id="noSearchResults" class="no-search-results">
    No matching result found in HR dashboard.
</div>

<section class="hero-banner hr-searchable">
    <div class="hero-text">
        <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
        <p>
            You have
            <strong><?php echo $leaveRequests; ?> pending leave requests</strong>,
            <strong><?php echo $todayBirthdayCount; ?> birthday notification(s)</strong>, and
            <strong><?php echo $newApplicants; ?> pending employee accounts</strong> today.
        </p>
    </div>

    <div class="hero-actions">
        <button type="button" class="hero-btn primary-btn" onclick="openQuickPopup('addEmployeePopup')">
            <i class="fas fa-user-plus"></i> Add Employee
        </button>
        <a href="export_report.php" class="hero-btn secondary-btn">
            <i class="fas fa-file-export"></i> Export Report
        </a>
    </div>
</section>

<section class="cards">
    <div class="card hr-searchable">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <div class="card-info">
            <h3><?php echo $totalEmployees; ?></h3>
            <p>Total Employees</p>
            <span>Registered employee accounts</span>
        </div>
    </div>

    <div class="card hr-searchable">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
            <h3><?php echo $leaveRequests; ?></h3>
            <p>Leave Requests</p>
            <span>Pending HR review</span>
        </div>
    </div>

    <div class="card hr-searchable">
        <div class="card-icon"><i class="fas fa-cake-candles"></i></div>
        <div class="card-info">
            <h3><?php echo $todayBirthdayCount; ?></h3>
            <p>Birthdays Today</p>
            <span>HR can send message and gift</span>
        </div>
    </div>

    <div class="card hr-searchable">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
            <h3><?php echo $attendanceIssues; ?></h3>
            <p>Attention Alerts</p>
            <span>Security or access warnings</span>
        </div>
    </div>
</section>

<section class="hr-dashboard-grid">
    <div class="hr-column">
        <div class="panel hr-searchable">
            <div class="panel-header">
                <h2>Quick Actions</h2>
            </div>

            <div class="quick-actions">
                <button type="button" class="quick-card hr-searchable" onclick="openQuickPopup('addEmployeePopup')">
                    <i class="fas fa-user-plus"></i>
                    <h4>Add Employee</h4>
                    <p>Create employee accounts directly from the HR dashboard.</p>
                </button>

                <button type="button" class="quick-card hr-searchable purple" onclick="openQuickPopup('leavePopup')">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Approve Leaves</h4>
                    <p>Approve or reject pending leave requests instantly.</p>
                </button>

                <button type="button" class="quick-card hr-searchable blue" onclick="openQuickPopup('recruitmentPopup')">
                    <i class="fas fa-user-tie"></i>
                    <h4>Recruitment</h4>
                    <p>Review pending accounts and recruitment follow-ups.</p>
                </button>

                <button type="button" class="quick-card hr-searchable orange" onclick="openQuickPopup('birthdayPopup')">
                    <i class="fas fa-cake-candles"></i>
                    <h4>Birthdays</h4>
                    <p>Send birthday messages and decide gift amount.</p>
                </button>
            </div>
        </div>

        <div class="panel hr-searchable">
            <div class="panel-header">
                <h2>Birthday Notifications 🎂</h2>
                <a href="employees.php">View Employees</a>
            </div>

            <div class="birthday-list">
                <?php if ($birthdayUsers && mysqli_num_rows($birthdayUsers) > 0) { ?>
                    <?php while ($birthday = mysqli_fetch_assoc($birthdayUsers)) { ?>
                        <div class="birthday-item hr-searchable">
                            <div class="birthday-icon today">
                                <i class="fas fa-cake-candles"></i>
                            </div>

                            <div style="flex:1;">
                                <h4>🎉 Today is <?php echo htmlspecialchars($birthday['full_name']); ?>'s birthday!</h4>
                                <p>Username: <?php echo htmlspecialchars($birthday['username']); ?></p>

                                <?php if (!empty($birthday['birthday_message'])) { ?>
                                    <span class="birthday-gift">
                                        Message sent · Gift: $<?php echo number_format((float)$birthday['reward_amount'], 2); ?>
                                    </span>
                                <?php } else { ?>
                                    <form method="POST" style="margin-top:15px;">
                                        <input type="hidden" name="birthday_user_id" value="<?php echo $birthday['id']; ?>">

                                        <div class="birthday-form-grid">
                                            <input type="text" name="birthday_message" placeholder="Write happy birthday message..." required>
                                            <input type="number" step="0.01" name="reward_amount" placeholder="$ Gift" required>
                                        </div>

                                        <button type="submit" name="send_birthday_reward" class="popup-action-btn" style="margin-top:12px;">
                                            Send Birthday Gift 🎁
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="birthday-item hr-searchable">
                        <div class="birthday-icon">
                            <i class="fas fa-cake-candles"></i>
                        </div>

                        <div>
                            <h4>No birthdays today</h4>
                            <p>No employee birthdays detected for today.</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="hr-column">
        <div class="panel hr-searchable">
            <div class="panel-header">
                <h2>HR To-Do List</h2>
            </div>

            <form method="POST" class="todo-form">
                <input type="text" name="task_text" placeholder="Write a new HR task..." required>
                <button type="submit" name="add_hr_todo">Add</button>
            </form>

            <div class="todo-db-list">
                <?php if ($hrTodosResult && mysqli_num_rows($hrTodosResult) > 0) { ?>
                    <?php while ($todo = mysqli_fetch_assoc($hrTodosResult)) { ?>
                        <div class="todo-db-item hr-searchable <?php echo $todo['status']; ?>">
                            <div class="todo-db-left">
                                <a href="hrdashboard.php?toggle_hr_todo=<?php echo $todo['id']; ?>" class="todo-check-btn">
                                    <?php if ($todo['status'] === 'done') { ?>
                                        <i class="fas fa-circle-check"></i>
                                    <?php } else { ?>
                                        <i class="far fa-circle"></i>
                                    <?php } ?>
                                </a>

                                <div class="todo-db-text">
                                    <h4 class="<?php echo $todo['status'] === 'done' ? 'completed-text' : ''; ?>">
                                        <?php echo htmlspecialchars($todo['task_text']); ?>
                                    </h4>
                                    <span class="todo-badge <?php echo $todo['status']; ?>">
                                        <?php echo ucfirst($todo['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <a href="hrdashboard.php?delete_hr_todo=<?php echo $todo['id']; ?>" class="todo-delete-btn" onclick="return confirm('Delete this task?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="empty-todo-text hr-searchable">No HR tasks yet. Add your first task.</p>
                <?php } ?>
            </div>
        </div>

        <div class="panel hr-searchable">
            <div class="panel-header">
                <h2>Recent Activity</h2>
            </div>

            <div class="activity-list">
                <div class="activity-item hr-searchable">
                    <div class="activity-icon"><i class="fas fa-user-check"></i></div>
                    <div>
                        <h4><?php echo $activeEmployees; ?> active employees</h4>
                        <p>Current active workforce records loaded from database.</p>
                    </div>
                </div>

                <div class="activity-item hr-searchable">
                    <div class="activity-icon"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <h4><?php echo $leaveRequests; ?> pending leave requests</h4>
                        <p>Requests waiting for HR approval or rejection.</p>
                    </div>
                </div>

                <div class="activity-item hr-searchable">
                    <div class="activity-icon"><i class="fas fa-cake-candles"></i></div>
                    <div>
                        <h4><?php echo $todayBirthdayCount; ?> birthday notification(s)</h4>
                        <p>HR can send birthday messages and decide gift amount.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</main>
</div>

<div class="quick-popup-overlay" id="addEmployeePopup">
    <div class="quick-popup-box">
        <div class="quick-popup-header">
            <h2>Add Employee</h2>
            <button type="button" onclick="closeQuickPopup('addEmployeePopup')">&times;</button>
        </div>

        <p class="popup-subtitle">Create a new employee account directly from the HR dashboard.</p>

        <form method="POST">
            <div class="popup-form-grid">
                <div class="popup-form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Example: Dana Ahmad">
                </div>

                <div class="popup-form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Example: dana_emp">
                </div>

                <div class="popup-form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Example: dana@example.com">
                </div>

                <div class="popup-form-group">
                    <label>Password</label>
                    <input type="text" name="password" required placeholder="Example: d1234">
                </div>

                <div class="popup-form-group">
                    <label>Salary</label>
                    <input type="number" step="0.01" name="salary" required placeholder="Example: 900">
                </div>

                <div class="popup-form-group">
                    <label>Birth Date</label>
                    <input type="date" name="birth_date" required>
                </div>
            </div>

            <button type="submit" name="add_employee_from_popup" class="popup-action-btn" style="margin-top:18px;width:100%;height:48px;">
                Add Employee
            </button>
        </form>
    </div>
</div>

<div class="quick-popup-overlay" id="leavePopup">
    <div class="quick-popup-box">
        <div class="quick-popup-header">
            <h2>Approve Leaves</h2>
            <button type="button" onclick="closeQuickPopup('leavePopup')">&times;</button>
        </div>

        <p class="popup-subtitle">Approve or reject pending leave requests without leaving the dashboard.</p>

        <div class="popup-list">
            <?php if ($pendingLeaves && mysqli_num_rows($pendingLeaves) > 0) { ?>
                <?php while ($leave = mysqli_fetch_assoc($pendingLeaves)) { ?>
                    <div class="popup-item hr-searchable">
                        <div>
                            <h4><?php echo htmlspecialchars($leave['employee_name']); ?> - <?php echo htmlspecialchars($leave['leave_type']); ?></h4>
                            <p><?php echo htmlspecialchars($leave['start_date']); ?> to <?php echo htmlspecialchars($leave['end_date']); ?></p>
                        </div>

                        <div class="popup-actions-row">
                            <form method="POST">
                                <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                <button type="submit" name="approve_leave" class="popup-action-btn">Approve</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                <button type="submit" name="reject_leave" class="popup-reject-btn">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="popup-item hr-searchable">
                    <div>
                        <h4>No pending leave requests</h4>
                        <p>There are no leave requests waiting for HR review right now.</p>
                    </div>
                    <span class="todo-badge done">Clear</span>
                </div>
            <?php } ?>

            <div class="popup-item hr-searchable">
                <div>
                    <h4>Open leave request center</h4>
                    <p>View all leave requests and history.</p>
                </div>
                <a href="leaverequests.php" class="popup-action-btn">Open</a>
            </div>
        </div>
    </div>
</div>

<div class="quick-popup-overlay" id="recruitmentPopup">
    <div class="quick-popup-box">
        <div class="quick-popup-header">
            <h2>Recruitment</h2>
            <button type="button" onclick="closeQuickPopup('recruitmentPopup')">&times;</button>
        </div>

        <p class="popup-subtitle">Review pending accounts and recruitment follow-up items.</p>

        <div class="popup-list">
            <?php if ($pendingAccounts && mysqli_num_rows($pendingAccounts) > 0) { ?>
                <?php while ($account = mysqli_fetch_assoc($pendingAccounts)) { ?>
                    <div class="popup-item hr-searchable">
                        <div>
                            <h4><?php echo htmlspecialchars($account['full_name']); ?></h4>
                            <p><?php echo htmlspecialchars($account['email']); ?> · <?php echo htmlspecialchars($account['username']); ?></p>
                        </div>
                        <span class="todo-badge pending">Pending Setup</span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="popup-item hr-searchable">
                    <div>
                        <h4>No pending accounts</h4>
                        <p>All accounts are currently ready.</p>
                    </div>
                    <span class="todo-badge done">Clear</span>
                </div>
            <?php } ?>

            <div class="popup-item hr-searchable">
                <div>
                    <h4>Open recruitment page</h4>
                    <p>Review submitted applications, CVs, and applicant information.</p>
                </div>
                <a href="recruitment.php" class="popup-action-btn">Open</a>
            </div>
        </div>
    </div>
</div>

<div class="quick-popup-overlay" id="birthdayPopup">
    <div class="quick-popup-box">
        <div class="quick-popup-header">
            <h2>Birthday Notifications</h2>
            <button type="button" onclick="closeQuickPopup('birthdayPopup')">&times;</button>
        </div>

        <p class="popup-subtitle">
            Today’s birthdays appear first. HR writes the message and chooses the gift amount manually.
        </p>

        <div class="popup-list">
            <?php if ($birthdayMonthUsers && mysqli_num_rows($birthdayMonthUsers) > 0) { ?>
                <?php while ($birthday = mysqli_fetch_assoc($birthdayMonthUsers)) { ?>
                    <?php
                        $birthdayMonthDay = date("m-d", strtotime($birthday['birth_date']));
                        $todayMonthDay = date("m-d");

                        if ($birthdayMonthDay === $todayMonthDay) {
                            $birthdayStatus = "Today";
                            $birthdayBadgeClass = "done";
                        } elseif ($birthdayMonthDay > $todayMonthDay) {
                            $birthdayStatus = "Upcoming";
                            $birthdayBadgeClass = "pending";
                        } else {
                            $birthdayStatus = "Passed";
                            $birthdayBadgeClass = "passed";
                        }
                    ?>

                    <div class="popup-item hr-searchable">
                        <div>
                            <h4>
                                <?php echo htmlspecialchars($birthday['full_name']); ?>
                                <?php if ($birthdayStatus === "Today") { echo "🎉"; } ?>
                            </h4>

                            <p>
                                <?php echo date("M d", strtotime($birthday['birth_date'])); ?>
                                · Username:
                                <?php echo htmlspecialchars($birthday['username']); ?>
                            </p>
                        </div>

                        <?php if ($birthdayStatus === "Today" && empty($birthday['birthday_message'])) { ?>
                            <span class="todo-badge pending">Needs HR Gift</span>

                        <?php } elseif ($birthdayStatus === "Today" && !empty($birthday['birthday_message'])) { ?>
                            <span class="todo-badge done">
                                Gift $<?php echo number_format((float)$birthday['reward_amount'], 2); ?>
                            </span>

                        <?php } else { ?>
                            <span class="todo-badge <?php echo $birthdayBadgeClass; ?>">
                                <?php echo $birthdayStatus; ?>
                            </span>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="popup-item hr-searchable">
                    <div>
                        <h4>No birthdays this month</h4>
                        <p>Add employee birth dates to activate birthday notifications.</p>
                    </div>
                    <span class="todo-badge pending">Empty</span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
function openQuickPopup(id) {
    document.getElementById(id).style.display = "flex";
}

function closeQuickPopup(id) {
    document.getElementById(id).style.display = "none";
}

document.addEventListener("click", function(e) {
    const overlays = document.querySelectorAll(".quick-popup-overlay");

    overlays.forEach(function(overlay) {
        if (e.target === overlay) {
            overlay.style.display = "none";
        }
    });
});

function searchHRDashboard() {
    const input = document.getElementById("hrSearch");
    const searchValue = input.value.toLowerCase().trim();
    const items = document.querySelectorAll(".hr-searchable");
    const noResults = document.getElementById("noSearchResults");

    let found = false;

    items.forEach(function(item) {
        const text = item.innerText.toLowerCase();

        if (searchValue === "" || text.includes(searchValue)) {
            item.style.display = "";
            found = true;
        } else {
            item.style.display = "none";
        }
    });

    if (noResults) {
        noResults.style.display = found ? "none" : "block";
    }
}

const hrBell = document.getElementById("notificationBell");
const hrDropdown = document.getElementById("notificationDropdown");
const hrNotificationContainer = document.getElementById("notificationListContainer");

if (hrBell && hrDropdown && hrNotificationContainer) {
    hrBell.addEventListener("click", function(e) {
        e.stopPropagation();
        hrDropdown.classList.toggle("show");
    });

    hrDropdown.addEventListener("click", function(e) {
        e.stopPropagation();
    });

    document.addEventListener("click", function() {
        hrDropdown.classList.remove("show");
    });
}

function getHRNotificationLink(title, message) {
    const text = (title + " " + message).toLowerCase();

    if (text.includes("leave")) {
        return "leaverequests.php";
    }

    if (text.includes("attendance") || text.includes("check")) {
        return "attendance.php";
    }

    if (text.includes("employee") || text.includes("account") || text.includes("birthday")) {
        return "employees.php";
    }

    if (text.includes("recruit") || text.includes("applicant")) {
        return "recruitment.php";
    }

    if (text.includes("issue") || text.includes("support")) {
        return "report_issue.php";
    }

    return "notificationshr.php";
}

function updateHRNotifications() {
    fetch("get_notifications.php")
        .then(response => response.json())
        .then(data => {
            if (!data.success) return;

            const badge = document.querySelector(".notification-bell .notif-count");

            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    const span = document.createElement("span");
                    span.className = "notif-count";
                    span.textContent = data.count;
                    hrBell.appendChild(span);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }

            hrNotificationContainer.innerHTML = "";

            if (!data.notifications || data.notifications.length === 0) {
                hrNotificationContainer.innerHTML = '<div class="empty-live-notification">No notifications yet.</div>';
                return;
            }

            data.notifications.forEach(notification => {
                const item = document.createElement("div");
                item.className = "notification-item-live";

                item.innerHTML = `
                    <h4>${notification.title}</h4>
                    <p>${notification.message}</p>
                    <span class="notification-time">${notification.created_at}</span>
                `;

                item.addEventListener("click", function() {
                    window.location.href = getHRNotificationLink(notification.title, notification.message);
                });

                hrNotificationContainer.appendChild(item);
            });
        })
        .catch(error => console.log(error));
}

updateHRNotifications();
setInterval(updateHRNotifications, 5000);

</script>

</body>
</html>