<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="logo-box">
        <i class="fa-solid fa-leaf"></i>
        <h2>OneFlow</h2>
      </div>
      <p class="admin-role">Employee Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li class="active"><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>System Status</p>
        <h4>Online</h4>
        <span>All services running</span>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <h1>Employee Dashboard</h1>
        <p>Track your tasks, attendance, and daily activities.</p>
      </div>

      <div class="topbar-right">

        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">2</span>
        </div>

        <!-- Profile -->
        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Employee</span>
          </div>
        </div>

        <!-- Logout -->
        <a href="logout.php" class="logout-btn">Logout</a>

      </div>
    </header>

    <!-- Hero -->
    <section class="hero-banner">
      <div class="hero-text">
        <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
        <p>You have <strong>3 tasks</strong> to complete and <strong>1 meeting</strong> today.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-list-check"></i> View Tasks</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-calendar"></i> View Schedule</button>
      </div>
    </section>

    <!-- Cards -->
    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
          <h3>3</h3>
          <p>My Tasks</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
          <h3>95%</h3>
          <p>Attendance</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3>1</h3>
          <p>Upcoming Meetings</p>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-bell"></i></div>
        <div class="card-info">
          <h3>2</h3>
          <p>Notifications</p>
        </div>
      </div>
    </section>

  </main>
</div>

</body>
</html>