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

if (isset($_GET['approve_request'])) {
    $requestId = (int) $_GET['approve_request'];

    $requestQuery = "SELECT * FROM requests WHERE id = $requestId AND status = 'pending' LIMIT 1";
    $requestResult = mysqli_query($conn, $requestQuery);

    if ($requestResult && mysqli_num_rows($requestResult) > 0) {
        $requestData = mysqli_fetch_assoc($requestResult);

        $fullName = mysqli_real_escape_string($conn, $requestData['full_name']);
        $email = mysqli_real_escape_string($conn, $requestData['email']);

        $baseUsername = strtolower(trim(explode('@', $requestData['email'])[0]));
        $baseUsername = preg_replace('/[^a-z0-9_]/', '', $baseUsername);
        if (empty($baseUsername)) {
            $baseUsername = "employee";
        }

        $username = $baseUsername;
        $counter = 1;

        while (true) {
            $checkUsernameQuery = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
            $checkUsernameResult = mysqli_query($conn, $checkUsernameQuery);

            if ($checkUsernameResult && mysqli_num_rows($checkUsernameResult) == 0) {
                break;
            }

            $username = $baseUsername . $counter;
            $counter++;
        }

        $checkEmailQuery = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

        if ($checkEmailResult && mysqli_num_rows($checkEmailResult) > 0) {
            header("Location: dashboardadmin.php");
            exit();
        }

        $insertUserQuery = "INSERT INTO users (full_name, username, email, password, role, account_status)
                            VALUES ('$fullName', '$username', '$email', NULL, 'employee', 'pending_setup')";

        if (mysqli_query($conn, $insertUserQuery)) {
            mysqli_query($conn, "UPDATE requests SET status = 'approved' WHERE id = $requestId");
        }
    }

    header("Location: dashboardadmin.php");
    exit();
}

if (isset($_GET['reject_request'])) {
    $requestId = (int) $_GET['reject_request'];
    mysqli_query($conn, "UPDATE requests SET status = 'rejected' WHERE id = $requestId");
    header("Location: dashboardadmin.php");
    exit();
}

if (isset($_GET['unblock_id'])) {
    $unblock_id = (int) $_GET['unblock_id'];

    $unblock_sql = "UPDATE users SET is_blocked = 0, failed_attempts = 0 WHERE id = ? AND role IN ('hr', 'employee')";
    $unblock_stmt = mysqli_prepare($conn, $unblock_sql);

    if ($unblock_stmt) {
        mysqli_stmt_bind_param($unblock_stmt, "i", $unblock_id);
        mysqli_stmt_execute($unblock_stmt);
        mysqli_stmt_close($unblock_stmt);
    }

    header("Location: dashboardadmin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_todo'])) {
    $taskText = trim($_POST['task_text']);

    if (!empty($taskText)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO admin_todos (task_text, status) VALUES (?, 'pending')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $taskText);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: dashboardadmin.php");
    exit();
}

if (isset($_GET['toggle_todo'])) {
    $todoId = (int) $_GET['toggle_todo'];

    $checkQuery = "SELECT status FROM admin_todos WHERE id = $todoId";
    $checkResult = mysqli_query($conn, $checkQuery);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        $todoRow = mysqli_fetch_assoc($checkResult);
        $newStatus = ($todoRow['status'] === 'pending') ? 'done' : 'pending';

        mysqli_query($conn, "UPDATE admin_todos SET status = '$newStatus' WHERE id = $todoId");
    }

    header("Location: dashboardadmin.php");
    exit();
}

if (isset($_GET['delete_todo'])) {
    $todoId = (int) $_GET['delete_todo'];
    mysqli_query($conn, "DELETE FROM admin_todos WHERE id = $todoId");

    header("Location: dashboardadmin.php");
    exit();
}

$employeesCount = 0;
$query = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $employeesCount = $row['total'];
}

$hrCount = 0;
$hrQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'hr'";
$hrResult = mysqli_query($conn, $hrQuery);

if ($hrResult && $hrRow = mysqli_fetch_assoc($hrResult)) {
    $hrCount = $hrRow['total'];
}

$adminCount = 0;
$adminQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
$adminResult = mysqli_query($conn, $adminQuery);

if ($adminResult && $adminRow = mysqli_fetch_assoc($adminResult)) {
    $adminCount = $adminRow['total'];
}

$pendingRequestsCount = 0;
$pendingQuery = "SELECT COUNT(*) AS total FROM requests WHERE status = 'pending'";
$pendingResult = mysqli_query($conn, $pendingQuery);

if ($pendingResult && $pendingRow = mysqli_fetch_assoc($pendingResult)) {
    $pendingRequestsCount = $pendingRow['total'];
}

$usersQuery = "SELECT id, full_name, username, role FROM users ORDER BY id DESC";
$usersResult = mysqli_query($conn, $usersQuery);

$searchUsers = [];
$searchResult = mysqli_query($conn, "SELECT id, full_name, username FROM users");
if ($searchResult) {
    while ($row = mysqli_fetch_assoc($searchResult)) {
        $searchUsers[] = $row;
    }
}

$requestsQuery = "SELECT * FROM requests ORDER BY id DESC";
$requestsResult = mysqli_query($conn, $requestsQuery);

$todosQuery = "SELECT * FROM admin_todos ORDER BY id DESC";
$todosResult = mysqli_query($conn, $todosQuery);

$blocked_sql = "SELECT id, full_name, username, role, failed_attempts FROM users WHERE is_blocked = 1 AND role IN ('hr', 'employee')";
$blocked_result = mysqli_query($conn, $blocked_sql);
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
        <li><a href="systemlogs.php"><i class="fas fa-file-circle-check"></i> System Logs</a></li>
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
            <h3><?php echo $pendingRequestsCount; ?></h3>
            <p>Pending Requests</p>
            <span>Live count from requests table</span>
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
              <a href="manageusers.php" class="quick-card">
                <i class="fas fa-user-shield"></i>
                <h4>Manage Permissions</h4>
                <p>Control roles and access permissions</p>
              </a>

              <a href="securitycenter.php" class="quick-card">
                <i class="fas fa-shield-halved"></i>
                <h4>Security Center</h4>
                <p>Monitor blocked accounts and security status</p>
              </a>

              <a href="manageusers.php" class="quick-card">
                <i class="fas fa-users"></i>
                <h4>View Users</h4>
                <p>Browse all users in the system</p>
              </a>

              
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Recent Requests</h2>
              <a href="systemlogs.php">View All</a>
            </div>

            <div class="table-wrapper">
              <table id="requestsTable">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($requestsResult && mysqli_num_rows($requestsResult) > 0) { ?>
                    <?php while ($request = mysqli_fetch_assoc($requestsResult)) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['email']); ?></td>
                        <td><?php echo htmlspecialchars($request['phone']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($request['status']); ?>">
                            <?php echo ucfirst($request['status']); ?>
                          </span>
                        </td>
                        <td>
                          <?php if ($request['status'] === 'pending') { ?>
                            <a href="dashboardadmin.php?approve_request=<?php echo $request['id']; ?>" class="action-btn approve">Approve</a>
                            <a href="dashboardadmin.php?reject_request=<?php echo $request['id']; ?>" class="action-btn reject">Reject</a>
                          <?php } else { ?>
                            <span style="color:#64748b; font-weight:600;">Reviewed</span>
                          <?php } ?>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5">No requests found.</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <div class="right-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Admin To-Do List</h2>
            </div>

            <form method="POST" class="todo-form">
              <input type="text" name="task_text" placeholder="Write a new admin task..." required>
              <button type="submit" name="add_todo">Add</button>
            </form>

            <div class="todo-db-list">
              <?php if ($todosResult && mysqli_num_rows($todosResult) > 0) { ?>
                <?php while ($todo = mysqli_fetch_assoc($todosResult)) { ?>
                  <div class="todo-db-item <?php echo $todo['status']; ?>">
                    <div class="todo-db-left">
                      <a href="dashboardadmin.php?toggle_todo=<?php echo $todo['id']; ?>" class="todo-check-btn">
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

                    <a href="dashboardadmin.php?delete_todo=<?php echo $todo['id']; ?>" class="todo-delete-btn" onclick="return confirm('Delete this task?');">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                <?php } ?>
              <?php } else { ?>
                <p class="empty-todo-text">No tasks yet. Add your first task.</p>
              <?php } ?>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Blocked Accounts</h2>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Failed Attempts</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($blocked_result && mysqli_num_rows($blocked_result) > 0) { ?>
                    <?php while ($blocked_user = mysqli_fetch_assoc($blocked_result)) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($blocked_user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($blocked_user['username']); ?></td>
                        <td><?php echo htmlspecialchars($blocked_user['role']); ?></td>
                        <td><?php echo htmlspecialchars($blocked_user['failed_attempts']); ?></td>
                        <td>
                          <a href="dashboardadmin.php?unblock_id=<?php echo $blocked_user['id']; ?>" class="action-btn approve">
                            Unblock
                          </a>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5">No blocked accounts found.</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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

          
      </section>

    </main>
  </div>

  <div id="actionPopup" class="action-popup"></div>

  <script>
    const searchUsers = <?php echo json_encode($searchUsers); ?>;

    function showPopup(message, type) {
      const popup = document.getElementById("actionPopup");
      if (!popup) return;

      popup.textContent = message;
      popup.className = "action-popup show " + type;

      setTimeout(function () {
        popup.className = "action-popup";
      }, 2500);
    }

    document.addEventListener("DOMContentLoaded", function () {
      const userSearch = document.getElementById("userSearch");
      const notificationBellBtn = document.getElementById("notificationBellBtn");
      const notificationDropdown = document.getElementById("notificationDropdown");

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

      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape" && notificationDropdown) {
          notificationDropdown.style.display = "none";
        }
      });
    });
  </script>

</body>
</html>