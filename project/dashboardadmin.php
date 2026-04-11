<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - OneFlow</title>
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
        <li class="active"><a href="#"><i class="fas fa-house"></i> Dashboard</a></li>
       <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="#"><i class="fas fa-user-tie"></i> HR Team</a></li>
<li><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Requests</a></li>
       <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
       <li><a href="notifications.php">Notifications</a></li>
        <li><a href="#"><i class="fas fa-gear"></i> Settings</a></li>
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

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Admin Dashboard</h1>
          <p>Monitor employees, requests, and system activity in one place.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search employees, requests, reports...">
          </div>

          <div class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notif-count">3</span>
          </div>

          <div class="admin-profile">
            <div class="admin-avatar">A</div>
            <div>
              <h4>Admin</h4>
              <span>Super Admin</span>
            </div>
          </div>

<a href="logout.php" class="logout-btn">Logout</a>   </div>
      </header>

      <!-- Hero Banner -->
      <section class="hero-banner">
        <div class="hero-text">
          <h2>Welcome back, Admin 👋</h2>
          <p>You have <strong>15 pending requests</strong>, <strong>3 new alerts</strong>, and <strong>12 new user activities</strong> today.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add New User</button>
          <button class="hero-btn secondary-btn"><i class="fas fa-file-export"></i> Export Report</button>
        </div>
      </section>

      <!-- Stats -->
      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-users"></i></div>
          <div class="card-info">
            <h3>120</h3>
            <p>Total Employees</p>
            <span>+8 this month</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="card-info">
            <h3>15</h3>
            <p>Pending Requests</p>
            <span>Needs review today</span>
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
          <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
          <div class="card-info">
            <h3>92%</h3>
            <p>Performance Rate</p>
            <span>Across all departments</span>
          </div>
        </div>
      </section>

      <!-- Dashboard Grid -->
      <section class="dashboard-grid">

        <!-- Left Column -->
        <div class="left-column">

          <!-- Quick Actions -->
          <div class="panel">
            <div class="panel-header">
              <h2>Quick Actions</h2>
            </div>

            <div class="quick-actions">
              <div class="quick-card">
                <i class="fas fa-user-plus"></i>
                <h4>Add Employee</h4>
                <p>Create a new employee account</p>
              </div>

              <div class="quick-card">
                <i class="fas fa-user-shield"></i>
                <h4>Manage Roles</h4>
                <p>Control admin and HR access</p>
              </div>

              <div class="quick-card">
                <i class="fas fa-file-signature"></i>
                <h4>Review Requests</h4>
                <p>Approve or reject submissions</p>
              </div>

              <div class="quick-card">
                <i class="fas fa-chart-column"></i>
                <h4>Generate Report</h4>
                <p>View and export system insights</p>
              </div>
            </div>
          </div>

          <!-- Recent Requests -->
          <div class="panel">
            <div class="panel-header">
              <h2>Recent Requests</h2>
              <a href="#">View All</a>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Sarah Ahmad</td>
                    <td>sarah@example.com</td>
                    <td>+962791234567</td>
                    <td><span class="status pending">Pending</span></td>
                    <td>
                      <button class="action-btn approve">Approve</button>
                      <button class="action-btn reject">Reject</button>
                    </td>
                  </tr>

                  <tr>
                    <td>Omar Khaled</td>
                    <td>omar@example.com</td>
                    <td>+962781112233</td>
                    <td><span class="status approved">Approved</span></td>
                    <td>
                      <button class="action-btn view">View</button>
                    </td>
                  </tr>

                  <tr>
                    <td>Lina Samer</td>
                    <td>lina@example.com</td>
                    <td>+962799998877</td>
                    <td><span class="status rejected">Rejected</span></td>
                    <td>
                      <button class="action-btn view">View</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <!-- Right Column -->
        <div class="right-column">

          <!-- Notifications -->
          <div class="panel">
            <div class="panel-header">
              <h2>Notifications</h2>
            </div>

            <div class="notification-list">
              <div class="notification-item">
                <div class="notif-icon teal"><i class="fas fa-bell"></i></div>
                <div>
                  <h4>3 new join requests</h4>
                  <p>Waiting for admin approval</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon green"><i class="fas fa-user-check"></i></div>
                <div>
                  <h4>5 employees updated profiles</h4>
                  <p>Today at 10:30 AM</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon red"><i class="fas fa-triangle-exclamation"></i></div>
                <div>
                  <h4>Password reset request</h4>
                  <p>Security action required</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="panel">
            <div class="panel-header">
              <h2>Recent Activity</h2>
            </div>

            <div class="activity-list">
              <div class="activity-item">
                <span class="dot teal-dot"></span>
                <div>
                  <h4>Admin approved Sarah Ahmad's request</h4>
                  <p>2 minutes ago</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot green-dot"></span>
                <div>
                  <h4>New HR report was generated</h4>
                  <p>20 minutes ago</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot orange-dot"></span>
                <div>
                  <h4>Omar Khaled submitted a document</h4>
                  <p>1 hour ago</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot red-dot"></span>
                <div>
                  <h4>System flagged an unusual login attempt</h4>
                  <p>Today</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Overview -->
          <div class="panel">
            <div class="panel-header">
              <h2>System Overview</h2>
            </div>

            <div class="overview-box">
              <div class="overview-row">
                <span>Employees Active</span>
                <strong>108</strong>
              </div>
              <div class="overview-row">
                <span>HR Managers</span>
                <strong>6</strong>
              </div>
              <div class="overview-row">
                <span>Admins</span>
                <strong>2</strong>
              </div>
              <div class="overview-row">
                <span>Reports Generated</span>
                <strong>34</strong>
              </div>
              <div class="overview-row">
                <span>Open Requests</span>
                <strong>15</strong>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

</body>
</html>