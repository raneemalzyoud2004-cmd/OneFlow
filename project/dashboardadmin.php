<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

// Total Employees Count
$employeesCount = 0;
$query = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $employeesCount = $row['total'];
}

// Count HR users
$hrCount = 0;
$hrQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'hr'";
$hrResult = mysqli_query($conn, $hrQuery);

if ($hrResult && $hrRow = mysqli_fetch_assoc($hrResult)) {
    $hrCount = $hrRow['total'];
}

// Count Admin users
$adminCount = 0;
$adminQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$adminResult = mysqli_query($conn, $adminQuery);

if ($adminResult && $adminRow = mysqli_fetch_assoc($adminResult)) {
    $adminCount = $adminRow['total'];
}

// Get users list
$usersQuery = "SELECT id, full_name, username, role FROM users ORDER BY id DESC";
$usersResult = mysqli_query($conn, $usersQuery);
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
        <li class="active"><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
        <li><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Requests</a></li>
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

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1>Admin Dashboard</h1>
          <p>Monitor employees, users, and system activity in one place.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" placeholder="Search users by name, username, or role...">
          </div>

          <a href="notifications.php" class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notif-count">3</span>
          </a>

          <div class="admin-profile">
            <div class="admin-avatar">A</div>
            <div>
              <h4><?php echo $full_name; ?></h4>
              <span>Super Admin</span>
            </div>
          </div>

          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <!-- Hero Banner -->
      <section class="hero-banner">
        <div class="hero-text">
          <h2>Welcome back, <?php echo $full_name; ?> 👋</h2>
          <p>You currently have <strong><?php echo $employeesCount; ?> total users</strong> registered in the system.</p>
        </div>
        <div class="hero-actions">
          <a href="manageusers.php" class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add New User</a>
          <a href="analytics.php" class="hero-btn secondary-btn"><i class="fas fa-file-export"></i> Export Report</a>
        </div>
      </section>

      <!-- Stats -->
      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-users"></i></div>
          <div class="card-info">
            <h3><?php echo $employeesCount; ?></h3>
            <p>Total Users</p>
            <span>Live count from database</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-shield"></i></div>
          <div class="card-info">
            <h3><?php echo $adminCount; ?></h3>
            <p>Admins</p>
            <span>System administrators</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-tie"></i></div>
          <div class="card-info">
            <h3><?php echo $hrCount; ?></h3>
            <p>HR Members</p>
            <span>HR team in system</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
          <div class="card-info">
            <h3><?php echo $employeesCount; ?></h3>
            <p>Pending Requests</p>
            <span>Users stored in database</span>
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
              <a href="manageusers.php" class="quick-card">
                <i class="fas fa-user-plus"></i>
                <h4>Add Employee</h4>
                <p>Create a new employee account</p>
              </a>

              <a href="settingsadmin.php" class="quick-card">
                <i class="fas fa-user-shield"></i>
                <h4>Manage Roles</h4>
                <p>Control admin and HR access</p>
              </a>

              <a href="manageusers.php" class="quick-card">
                <i class="fas fa-users"></i>
                <h4>View Users</h4>
                <p>Browse all users in the system</p>
              </a>

              <a href="analytics.php" class="quick-card">
                <i class="fas fa-chart-column"></i>
                <h4>Generate Report</h4>
                <p>View and export system insights</p>
              </a>
            </div>
          </div>

          <!-- Recent Users -->
          <div class="panel">
            <div class="panel-header">
              <h2>Recent Users</h2>
              <a href="manageusers.php">View All</a>
            </div>

            <div class="table-wrapper">
              <table id="usersTable">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($usersResult && mysqli_num_rows($usersResult) > 0) { ?>
                    <?php while ($user = mysqli_fetch_assoc($usersResult)) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($user['role']); ?>">
                            <?php echo ucfirst($user['role']); ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($user['role'] == 'employee') { ?>
                            <button type="button" class="action-btn approve">Approve</button>
                            <button type="button" class="action-btn reject">Reject</button>
                          <?php } else { ?>
                            <button type="button" class="action-btn view">View</button>
                          <?php } ?>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } ?>
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
                  <h4>System is running smoothly</h4>
                  <p>All user records are available</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon green"><i class="fas fa-user-check"></i></div>
                <div>
                  <h4><?php echo $employeesCount; ?> users in database</h4>
                  <p>Live count loaded successfully</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon red"><i class="fas fa-triangle-exclamation"></i></div>
                <div>
                  <h4>Remember to review roles</h4>
                  <p>Check admin and HR access levels</p>
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
                  <h4>Admin logged in successfully</h4>
                  <p>Session started for <?php echo $full_name; ?></p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot green-dot"></span>
                <div>
                  <h4>User list loaded</h4>
                  <p>Live data fetched from database</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot orange-dot"></span>
                <div>
                  <h4>Search feature active</h4>
                  <p>Filter users by name, username, or role</p>
                </div>
              </div>

              <div class="activity-item">
                <span class="dot red-dot"></span>
                <div>
                  <h4>Logout protection enabled</h4>
                  <p>Dashboard is secured by session</p>
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
                <span>Total Users</span>
                <strong><?php echo $employeesCount; ?></strong>
              </div>
              <div class="overview-row">
                <span>HR Members</span>
                <strong><?php echo $hrCount; ?></strong>
              </div>
              <div class="overview-row">
                <span>Admins</span>
                <strong><?php echo $adminCount; ?></strong>
              </div>
              <div class="overview-row">
                <span>Logged In Admin</span>
                <strong><?php echo htmlspecialchars($full_name); ?></strong>
              </div>
              <div class="overview-row">
                <span>Status</span>
                <strong>Online</strong>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

  <!-- Popup Message -->
  <div id="actionPopup" class="action-popup"></div>

  <script>
    function showPopup(message, type) {
      const popup = document.getElementById("actionPopup");
      popup.textContent = message;
      popup.className = "action-popup show " + type;

      setTimeout(function () {
        popup.className = "action-popup";
      }, 2500);
    }

    document.addEventListener("DOMContentLoaded", function () {
      const approveButtons = document.querySelectorAll(".action-btn.approve");
      const rejectButtons = document.querySelectorAll(".action-btn.reject");
      const viewButtons = document.querySelectorAll(".action-btn.view");
      const searchInput = document.getElementById("userSearch");
      const tableRows = document.querySelectorAll("#usersTable tbody tr");

      approveButtons.forEach(function(button) {
        button.addEventListener("click", function () {
          const row = this.closest("tr");
          const name = row.querySelector("td").textContent;
          showPopup(name + " approved successfully.", "success");
        });
      });

      rejectButtons.forEach(function(button) {
        button.addEventListener("click", function () {
          const row = this.closest("tr");
          const name = row.querySelector("td").textContent;
          showPopup(name + " is rejected.", "error");
        });
      });

      viewButtons.forEach(function(button) {
        button.addEventListener("click", function () {
          const row = this.closest("tr");
          const name = row.querySelector("td").textContent;
          showPopup("Viewing profile for " + name + ".", "info");
        });
      });

      if (searchInput) {
        searchInput.addEventListener("keyup", function () {
          const searchValue = this.value.toLowerCase();

          tableRows.forEach(function(row) {
            const rowText = row.textContent.toLowerCase();

            if (rowText.includes(searchValue)) {
              row.style.display = "";
            } else {
              row.style.display = "none";
            }
          });
        });
      }
    });
  </script>

</body>
</html>