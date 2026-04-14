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
$addUserMessage = "";
$addUserType = "";

/* Total Employees Count */
$employeesCount = 0;
$query = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $employeesCount = $row['total'];
}

/* Count HR users */
$hrCount = 0;
$hrQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'hr'";
$hrResult = mysqli_query($conn, $hrQuery);

if ($hrResult && $hrRow = mysqli_fetch_assoc($hrResult)) {
    $hrCount = $hrRow['total'];
}

/* Count Admin users */
$adminCount = 0;
$adminQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$adminResult = mysqli_query($conn, $adminQuery);

if ($adminResult && $adminRow = mysqli_fetch_assoc($adminResult)) {
    $adminCount = $adminRow['total'];
}

/* Get users list */
$usersQuery = "SELECT id, full_name, username, role FROM users ORDER BY id DESC";
$usersResult = mysqli_query($conn, $usersQuery);

/* Search data for JS */
$searchUsers = [];
$searchResult = mysqli_query($conn, "SELECT id, full_name, username FROM users");
if ($searchResult) {
    while ($row = mysqli_fetch_assoc($searchResult)) {
        $searchUsers[] = $row;
    }
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

    <main class="main-content">

      <header class="topbar">
        <div class="topbar-left">
          <h1>Admin Dashboard</h1>
          <p>Monitor employees, users, and system activity in one place.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearch" list="usersList" placeholder="Search users by name or username...">
            <datalist id="usersList">
              <?php foreach ($searchUsers as $searchUser) { ?>
                <option value="<?php echo htmlspecialchars($searchUser['full_name']); ?>"></option>
                <option value="<?php echo htmlspecialchars($searchUser['username']); ?>"></option>
              <?php } ?>
            </datalist>
          </div>

          <div class="notification-wrapper" style="position: relative;">
            <button
              type="button"
              class="icon-btn notification-bell"
              id="notificationBellBtn"
              style="border:none; outline:none;"
            >
              <i class="fas fa-bell"></i>
              <span class="notif-count">3</span>
            </button>

            <div
              id="notificationDropdown"
              style="
                display:none;
                position:absolute;
                top:62px;
                right:0;
                width:340px;
                background:#ffffff;
                border:1px solid #e5eef5;
                border-radius:18px;
                box-shadow:0 20px 45px rgba(15,23,42,0.14);
                padding:16px;
                z-index:9999;
              "
            >
              <div
                style="
                  display:flex;
                  justify-content:space-between;
                  align-items:center;
                  margin-bottom:14px;
                  padding-bottom:12px;
                  border-bottom:1px solid #edf2f7;
                "
              >
                <h3 style="font-size:17px; color:#0f172a; margin:0;">Recent Notifications</h3>
                <span
                  style="
                    font-size:12px;
                    font-weight:700;
                    color:#14b8a6;
                    background:#ecfeff;
                    padding:6px 10px;
                    border-radius:999px;
                  "
                >
                  3 New
                </span>
              </div>

              <div style="display:flex; flex-direction:column; gap:12px;">

                <div
                  style="
                    display:flex;
                    gap:12px;
                    align-items:flex-start;
                    padding:12px;
                    border-radius:16px;
                    background:#f8fbff;
                    border:1px solid #e8eef5;
                  "
                >
                  <div
                    style="
                      width:42px;
                      height:42px;
                      border-radius:12px;
                      display:flex;
                      justify-content:center;
                      align-items:center;
                      color:white;
                      flex-shrink:0;
                      background:linear-gradient(135deg,#14b8a6,#06b6d4);
                    "
                  >
                    <i class="fas fa-bell"></i>
                  </div>
                  <div>
                    <h4 style="font-size:14px; color:#0f172a; margin:0 0 4px 0;">System is running smoothly</h4>
                    <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">All user records are available</p>
                  </div>
                </div>

                <div
                  style="
                    display:flex;
                    gap:12px;
                    align-items:flex-start;
                    padding:12px;
                    border-radius:16px;
                    background:#f8fbff;
                    border:1px solid #e8eef5;
                  "
                >
                  <div
                    style="
                      width:42px;
                      height:42px;
                      border-radius:12px;
                      display:flex;
                      justify-content:center;
                      align-items:center;
                      color:white;
                      flex-shrink:0;
                      background:linear-gradient(135deg,#22c55e,#10b981);
                    "
                  >
                    <i class="fas fa-user-check"></i>
                  </div>
                  <div>
                    <h4 style="font-size:14px; color:#0f172a; margin:0 0 4px 0;"><?php echo $employeesCount; ?> users in database</h4>
                    <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">Live count loaded successfully</p>
                  </div>
                </div>

                <div
                  style="
                    display:flex;
                    gap:12px;
                    align-items:flex-start;
                    padding:12px;
                    border-radius:16px;
                    background:#f8fbff;
                    border:1px solid #e8eef5;
                  "
                >
                  <div
                    style="
                      width:42px;
                      height:42px;
                      border-radius:12px;
                      display:flex;
                      justify-content:center;
                      align-items:center;
                      color:white;
                      flex-shrink:0;
                      background:linear-gradient(135deg,#ef4444,#f97316);
                    "
                  >
                    <i class="fas fa-triangle-exclamation"></i>
                  </div>
                  <div>
                    <h4 style="font-size:14px; color:#0f172a; margin:0 0 4px 0;">Remember to review roles</h4>
                    <p style="font-size:12px; color:#64748b; margin:0; line-height:1.5;">Check admin and HR access levels</p>
                  </div>
                </div>

              </div>

              <div style="margin-top:14px; padding-top:12px; border-top:1px solid #edf2f7;">
                <a
                  href="notifications.php"
                  style="
                    display:block;
                    width:100%;
                    text-align:center;
                    background:linear-gradient(90deg,#0ea5a4,#14b8a6);
                    color:white;
                    padding:12px 14px;
                    border-radius:14px;
                    font-weight:700;
                    text-decoration:none;
                  "
                >
                  View All Notifications
                </a>
              </div>
            </div>
          </div>

          <div class="admin-profile">
            <div class="admin-avatar">A</div>
            <div>
              <h4><?php echo htmlspecialchars($full_name); ?></h4>
              <span>Super Admin</span>
            </div>
          </div>

          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <section class="hero-banner">
        <div class="hero-text">
          <h2>Welcome back, <?php echo htmlspecialchars($full_name); ?> 👋</h2>
          <p>You currently have <strong><?php echo $employeesCount; ?> total users</strong> registered in the system.</p>
        </div>
        <div class="hero-actions">
          <a href="export_report.php" class="hero-btn secondary-btn">
            <i class="fas fa-file-export"></i> Export Report
          </a>
        </div>
      </section>

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

      <section class="dashboard-grid">
        <div class="left-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Quick Actions</h2>
            </div>

            <div class="quick-actions">
              <button type="button" class="quick-card quick-card-btn" onclick="openAddUserPopup()">
                <i class="fas fa-user-plus"></i>
                <h4>Add Employee</h4>
                <p>Create a new employee account</p>
              </button>

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
                            <a href="employeeinfo.php?id=<?php echo $user['id']; ?>" class="action-btn view">View</a>
                          <?php } else { ?>
                            <a href="employeeinfo.php?id=<?php echo $user['id']; ?>" class="action-btn view">View</a>
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

        <div class="right-column">

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

          <div class="panel">
            <div class="panel-header">
              <h2>Recent Activity</h2>
            </div>

            <div class="activity-list">
              <div class="activity-item">
                <span class="dot teal-dot"></span>
                <div>
                  <h4>Admin logged in successfully</h4>
                  <p>Session started for <?php echo htmlspecialchars($full_name); ?></p>
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
                  <p>Filter users by name or username</p>
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

  <div class="modal-overlay" id="addUserModal" style="display: none;">
    <div class="modal-box">
      <div class="modal-header">
        <div>
          <h2>Add New User</h2>
          <p>Create a new account directly from the dashboard.</p>
        </div>
        <button type="button" class="close-modal-btn" onclick="closeAddUserPopup()">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form method="POST" class="add-user-form">
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Enter full name" required>
          </div>

          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter email address" required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
          </div>

          <div class="form-group full-width">
            <label>Role</label>
            <select name="role" required>
              <option value="">Select role</option>
              <option value="admin">Admin</option>
              <option value="hr">HR</option>
              <option value="employee">Employee</option>
            </select>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="closeAddUserPopup()">Cancel</button>
          <button type="submit" name="add_user" class="save-user-btn">
            <i class="fas fa-user-plus"></i> Add User
          </button>
        </div>
      </form>
    </div>
  </div>

  <div id="actionPopup" class="action-popup"></div>

  <script>
    const searchUsers = <?php echo json_encode($searchUsers); ?>;
    const addUserMessage = <?php echo json_encode($addUserMessage); ?>;
    const addUserType = <?php echo json_encode($addUserType); ?>;

    function showPopup(message, type) {
      const popup = document.getElementById("actionPopup");
      if (!popup) return;

      popup.textContent = message;
      popup.className = "action-popup show " + type;

      setTimeout(function () {
        popup.className = "action-popup";
      }, 2500);
    }

    function openAddUserPopup() {
      const modal = document.getElementById("addUserModal");
      if (!modal) return;

      modal.style.display = "flex";
      modal.classList.add("show");
      document.body.classList.add("modal-open");
    }

    function closeAddUserPopup() {
      const modal = document.getElementById("addUserModal");
      if (!modal) return;

      modal.classList.remove("show");
      modal.style.display = "none";
      document.body.classList.remove("modal-open");
    }

    document.addEventListener("DOMContentLoaded", function () {
      const approveButtons = document.querySelectorAll(".action-btn.approve");
      const rejectButtons = document.querySelectorAll(".action-btn.reject");
      const userSearch = document.getElementById("userSearch");
      const addUserModal = document.getElementById("addUserModal");
      const notificationBellBtn = document.getElementById("notificationBellBtn");
      const notificationDropdown = document.getElementById("notificationDropdown");

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
          showPopup(name + " rejected.", "error");
        });
      });

      if (userSearch) {
        userSearch.addEventListener("keydown", function(e) {
          if (e.key === "Enter") {
            e.preventDefault();
            const searchValue = this.value.trim().toLowerCase();

            const matchedUser = searchUsers.find(function(user) {
              return user.full_name.toLowerCase() === searchValue || user.username.toLowerCase() === searchValue;
            });

            if (matchedUser) {
              window.location.href = "employeeinfo.php?id=" + matchedUser.id;
            } else {
              showPopup("User not found.", "error");
            }
          }
        });
      }

      if (notificationBellBtn && notificationDropdown) {
        notificationBellBtn.addEventListener("click", function(e) {
          e.preventDefault();
          e.stopPropagation();

          if (notificationDropdown.style.display === "block") {
            notificationDropdown.style.display = "none";
          } else {
            notificationDropdown.style.display = "block";
          }
        });

        notificationDropdown.addEventListener("click", function(e) {
          e.stopPropagation();
        });

        document.addEventListener("click", function() {
          notificationDropdown.style.display = "none";
        });
      }

      if (addUserModal) {
        addUserModal.addEventListener("click", function(e) {
          if (e.target === addUserModal) {
            closeAddUserPopup();
          }
        });
      }

      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
          closeAddUserPopup();
          if (notificationDropdown) {
            notificationDropdown.style.display = "none";
          }
        }
      });

      if (addUserMessage) {
        showPopup(addUserMessage, addUserType);

        if (addUserType === "error") {
          openAddUserPopup();
        }
      }
    });
  </script>

</body>
</html>