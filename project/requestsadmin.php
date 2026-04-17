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
  <title>Requests - OneFlow</title>
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
        <p class="admin-role">Admin Panel</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
        <li class="active"><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Access Requests Overview</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
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

    <!-- Main Content -->
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Requests</h1>
          <p>Review and manage employee requests.</p>
        </div>

        <div class="topbar-right">
<a href="logout.php" class="logout-btn">Logout</a>        </div>
      </header>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="card-info">
            <h3>15</h3>
            <p>Pending Requests</p>
            <span>Need approval</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-circle-check"></i></div>
          <div class="card-info">
            <h3>87</h3>
            <p>Approved Requests</p>
            <span>Processed successfully</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-circle-xmark"></i></div>
          <div class="card-info">
            <h3>18</h3>
            <p>Rejected Requests</p>
            <span>Declined submissions</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>All Requests</h2>
          <a href="#">View All</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Request Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sarah Ahmad</td>
                <td>Leave Request</td>
                <td>2026-04-06</td>
                <td><span class="status pending">Pending</span></td>
                <td>
                  <button class="action-btn approve">Approve</button>
                  <button class="action-btn reject">Reject</button>
                </td>
              </tr>

              <tr>
                <td>Omar Khaled</td>
                <td>Document Update</td>
                <td>2026-04-05</td>
                <td><span class="status approved">Approved</span></td>
                <td>
                  <button class="action-btn view">View</button>
                </td>
              </tr>

              <tr>
                <td>Lina Samer</td>
                <td>Account Access</td>
                <td>2026-04-04</td>
                <td><span class="status rejected">Rejected</span></td>
                <td>
                  <button class="action-btn view">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>