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

// Summary cards
$totalLoggedInUsers = 0;
$loggedUsersQuery = "SELECT COUNT(*) AS total FROM users WHERE last_login IS NOT NULL";
$loggedUsersResult = mysqli_query($conn, $loggedUsersQuery);
if ($loggedUsersResult && $row = mysqli_fetch_assoc($loggedUsersResult)) {
    $totalLoggedInUsers = $row['total'];
}

$totalPendingSetup = 0;
$pendingSetupQuery = "SELECT COUNT(*) AS total FROM users WHERE account_status = 'pending_setup'";
$pendingSetupResult = mysqli_query($conn, $pendingSetupQuery);
if ($pendingSetupResult && $row = mysqli_fetch_assoc($pendingSetupResult)) {
    $totalPendingSetup = $row['total'];
}

$totalPendingRequests = 0;
$pendingRequestsQuery = "SELECT COUNT(*) AS total FROM requests WHERE status = 'pending'";
$pendingRequestsResult = mysqli_query($conn, $pendingRequestsQuery);
if ($pendingRequestsResult && $row = mysqli_fetch_assoc($pendingRequestsResult)) {
    $totalPendingRequests = $row['total'];
}

$totalActiveUsers = 0;
$activeUsersQuery = "SELECT COUNT(*) AS total FROM users WHERE account_status = 'active'";
$activeUsersResult = mysqli_query($conn, $activeUsersQuery);
if ($activeUsersResult && $row = mysqli_fetch_assoc($activeUsersResult)) {
    $totalActiveUsers = $row['total'];
}

// Recent requests
$recentRequestsQuery = "SELECT id, full_name, email, phone, status, created_at
                        FROM requests
                        ORDER BY id DESC
                        LIMIT 8";
$recentRequestsResult = mysqli_query($conn, $recentRequestsQuery);

// Recent user state log
$recentUsersQuery = "SELECT id, full_name, username, email, role, account_status, is_blocked, failed_attempts
                     FROM users
                     ORDER BY id DESC
                     LIMIT 10";
$recentUsersResult = mysqli_query($conn, $recentUsersQuery);

// Latest logins
$latestLoginsQuery = "SELECT id, full_name, username, email, role, last_login
                      FROM users
                      WHERE last_login IS NOT NULL
                      ORDER BY last_login DESC
                      LIMIT 10";
$latestLoginsResult = mysqli_query($conn, $latestLoginsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Logs - OneFlow</title>
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
            <li class="active"><a href="systemlogs.php"><i class="fas fa-clipboard-list"></i> System Logs</a></li>

      <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
      <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Monitoring Status</p>
        <h4>Active</h4>
        <span>Logs overview ready</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>System Logs</h1>
        <p>Track operational activity, recent logins, request states, and key user events.</p>
      </div>

      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">A</div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Super Admin</span>
          </div>
        </div>

        <a href="dashboardadmin.php" class="logout-btn" style="text-decoration:none;">Back to Dashboard</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>System activity overview 📋</h2>
        <p>Use this page to monitor recent login activity, account state changes, and request flow across the system.</p>
      </div>
      <div class="hero-actions">
        <a href="securitycenter.php" class="hero-btn primary-btn">
          <i class="fas fa-shield-halved"></i> Security Center
        </a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-right-to-bracket"></i></div>
        <div class="card-info">
          <h3><?php echo $totalLoggedInUsers; ?></h3>
          <p>Users With Login History</p>
          <span>Accounts that logged in at least once</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
        <div class="card-info">
          <h3><?php echo $totalActiveUsers; ?></h3>
          <p>Active Users</p>
          <span>Users with active system accounts</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $totalPendingSetup; ?></h3>
          <p>Pending Setup</p>
          <span>Accounts waiting for completion</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-file-circle-question"></i></div>
        <div class="card-info">
          <h3><?php echo $totalPendingRequests; ?></h3>
          <p>Pending Requests</p>
          <span>Requests still under review</span>
        </div>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="left-column">

        <div class="panel">
          <div class="panel-header">
            <h2>Latest Logins</h2>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Full Name</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Date</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($latestLoginsResult && mysqli_num_rows($latestLoginsResult) > 0) { ?>
                  <?php while ($login = mysqli_fetch_assoc($latestLoginsResult)) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($login['full_name']); ?></td>
                      <td><?php echo htmlspecialchars($login['username']); ?></td>
                      <td>
                        <span class="status <?php echo strtolower($login['role']); ?>">
                          <?php echo ucfirst($login['role']); ?>
                        </span>
                      </td>
                      <td>
                        <?php echo date("Y-m-d", strtotime($login['last_login'])); ?>
                      </td>
                      <td>
                        <?php echo date("h:i A", strtotime($login['last_login'])); ?>
                      </td>
                    </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="5">No login activity found yet.</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Recent User State Log</h2>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>User</th>
                  <th>Role</th>
                  <th>Account</th>
                  <th>Security</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentUsersResult && mysqli_num_rows($recentUsersResult) > 0) { ?>
                  <?php while ($u = mysqli_fetch_assoc($recentUsersResult)) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                      <td>
                        <span class="status <?php echo strtolower($u['role']); ?>">
                          <?php echo ucfirst($u['role']); ?>
                        </span>
                      </td>
                      <td>
                        <span class="status <?php echo strtolower($u['account_status']); ?>">
                          <?php echo ucfirst(str_replace('_', ' ', $u['account_status'])); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ((int) $u['is_blocked'] === 1) { ?>
                          <span class="status rejected">Blocked</span>
                        <?php } elseif ((int) $u['failed_attempts'] >= 2) { ?>
                          <span class="status pending">Warning</span>
                        <?php } else { ?>
                          <span class="status approved">Normal</span>
                        <?php } ?>
                      </td>
                    </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="4">No user log data found.</td>
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
            <h2>Recent Request Log</h2>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentRequestsResult && mysqli_num_rows($recentRequestsResult) > 0) { ?>
                  <?php while ($req = mysqli_fetch_assoc($recentRequestsResult)) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($req['full_name']); ?></td>
                      <td>
                        <span class="status <?php echo strtolower($req['status']); ?>">
                          <?php echo ucfirst($req['status']); ?>
                        </span>
                      </td>
                      <td><?php echo htmlspecialchars($req['created_at']); ?></td>
                    </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="3">No request activity found.</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>System Notes</h2>
          </div>

          <div class="activity-list">
            <div class="activity-item">
              <span class="dot teal-dot"></span>
              <div>
                <h4>Latest Logins are now real</h4>
                <p>This section reads directly from the <strong>last_login</strong> column in the database.</p>
              </div>
            </div>

            <div class="activity-item">
              <span class="dot green-dot"></span>
              <div>
                <h4>Request activity is still tracked</h4>
                <p>The page continues to summarize request-side flow without duplicating Security Center.</p>
              </div>
            </div>

            <div class="activity-item">
              <span class="dot orange-dot"></span>
              <div>
                <h4>Can be improved later</h4>
                <p>You can later add a real logs table for role changes, approvals, and login failures.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

  </main>
</div>

</body>
</html>