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

$totalEmployees = 0;
$leaveRequests = 0;
$attendanceIssues = 0;
$newApplicants = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee'");
if ($result) {
    $totalEmployees = mysqli_fetch_assoc($result)['total'];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR Dashboard - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
      <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Health</p>
        <h4>Excellent</h4>
        <span>99.2% uptime</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>HR Dashboard</h1>
        <p>Manage employees, attendance, leave requests, and recruitment in one place.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search employees, requests, reports...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count"><?php echo $leaveRequests; ?></span>
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

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
        <p>
          You have 
          <strong><?php echo $leaveRequests; ?> pending leave requests</strong>, 
          <strong><?php echo $attendanceIssues; ?> attention alerts</strong>, and 
          <strong><?php echo $newApplicants; ?> pending employee accounts</strong> today.
        </p>
      </div>

      <div class="hero-actions">
        <a href="employees.php" class="hero-btn primary-btn">
          <i class="fas fa-user-plus"></i> Add Employee
        </a>
        <button class="hero-btn secondary-btn">
          <i class="fas fa-file-export"></i> Export Report
        </button>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <div class="card-info">
          <h3><?php echo $totalEmployees; ?></h3>
          <p>Total Employees</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
          <h3><?php echo $leaveRequests; ?></h3>
          <p>Leave Requests</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-plus"></i></div>
        <div class="card-info">
          <h3><?php echo $newApplicants; ?></h3>
          <p>Pending Accounts</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $attendanceIssues; ?></h3>
          <p>Attention Alerts</p>
        </div>
      </div>
    </section>

  </main>
</div>

</body>
</html>