<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
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
  <title>Notifications - OneFlow</title>
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
        <p class="admin-role">Admin Panel</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
        <li><a href="systemlogs.php"><i class="fas fa-file-circle-check"></i> System Logs</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li class="active"><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
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
          <h1>Notifications</h1>
          <p>Stay updated with admin alerts, user activity, requests, and system events.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search notifications...">
          </div>

          <div class="admin-profile">
            <div class="admin-avatar">A</div>
            <div>
<h4><?php echo $full_name; ?></h4>              <span>Super Admin</span>
            </div>
          </div>

<a href="logout.php" class="logout-btn">Logout</a>        </div>
      </header>

      <section class="hero-banner">
        <div class="hero-text">
          <h2>Notifications Center 🔔</h2>
          <p>Review important admin updates, requests, user changes, and security alerts.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-check-double"></i> Mark All Read</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-bell"></i></div>
          <div class="card-info">
            <h3>14</h3>
            <p>Total Notifications</p>
            <span>Received today</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="card-info">
            <h3>5</h3>
            <p>Pending Alerts</p>
            <span>Need admin action</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-plus"></i></div>
          <div class="card-info">
            <h3>4</h3>
            <p>Join Requests</p>
            <span>Waiting for approval</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-shield-halved"></i></div>
          <div class="card-info">
            <h3>2</h3>
            <p>Security Alerts</p>
            <span>Require review</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Recent Notifications</h2>
          <a href="#">View All</a>
        </div>

        <div class="notification-list">
          <div class="notification-item">
            <div class="notif-icon teal"><i class="fas fa-user-plus"></i></div>
            <div>
              <h4>3 new join requests waiting for approval</h4>
              <p>10 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon green"><i class="fas fa-user-check"></i></div>
            <div>
              <h4>5 employees updated their profiles</h4>
              <p>25 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon red"><i class="fas fa-triangle-exclamation"></i></div>
            <div>
              <h4>Security alert: unusual login attempt detected</h4>
              <p>40 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon teal"><i class="fas fa-file-circle-check"></i></div>
            <div>
              <h4>2 new requests submitted by employees</h4>
              <p>1 hour ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon green"><i class="fas fa-chart-line"></i></div>
            <div>
              <h4>New analytics report is ready to view</h4>
              <p>Today</p>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>

</body>
</html>