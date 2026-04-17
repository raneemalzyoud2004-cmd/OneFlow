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
$popupMessage = "";
$popupType = "";

if (isset($_GET['unblock_id'])) {
    $unblock_id = (int) $_GET['unblock_id'];

    $unblock_sql = "UPDATE users 
                    SET is_blocked = 0, failed_attempts = 0, blocked_until = NULL 
                    WHERE id = ? AND role IN ('hr', 'employee')";
    $unblock_stmt = mysqli_prepare($conn, $unblock_sql);

    if ($unblock_stmt) {
        mysqli_stmt_bind_param($unblock_stmt, "i", $unblock_id);

        if (mysqli_stmt_execute($unblock_stmt)) {
            $popupMessage = "User unblocked successfully.";
            $popupType = "success";
        } else {
            $popupMessage = "Failed to unblock user.";
            $popupType = "error";
        }

        mysqli_stmt_close($unblock_stmt);
    } else {
        $popupMessage = "Something went wrong.";
        $popupType = "error";
    }
}

$blockedCount = 0;
$blockedCountQuery = "SELECT COUNT(*) AS total FROM users WHERE is_blocked = 1";
$blockedCountResult = mysqli_query($conn, $blockedCountQuery);
if ($blockedCountResult && $row = mysqli_fetch_assoc($blockedCountResult)) {
    $blockedCount = $row['total'];
}

$failedAttemptsTotal = 0;
$failedAttemptsQuery = "SELECT COALESCE(SUM(failed_attempts), 0) AS total FROM users";
$failedAttemptsResult = mysqli_query($conn, $failedAttemptsQuery);
if ($failedAttemptsResult && $row = mysqli_fetch_assoc($failedAttemptsResult)) {
    $failedAttemptsTotal = $row['total'];
}

$activeProtectedCount = 0;
$protectedQuery = "SELECT COUNT(*) AS total FROM users WHERE failed_attempts > 0 OR is_blocked = 1";$protectedResult = mysqli_query($conn, $protectedQuery);
if ($protectedResult && $row = mysqli_fetch_assoc($protectedResult)) {
    $activeProtectedCount = $row['total'];
}

$highRiskCount = 0;
$highRiskQuery = "SELECT COUNT(*) AS total FROM users WHERE failed_attempts >= 2 AND is_blocked = 0";
$highRiskResult = mysqli_query($conn, $highRiskQuery);
if ($highRiskResult && $row = mysqli_fetch_assoc($highRiskResult)) {
    $highRiskCount = $row['total'];
}

$blockedUsersQuery = "SELECT id, full_name, username, role, failed_attempts, is_blocked, blocked_until
                      FROM users
                      WHERE is_blocked = 1
                      ORDER BY id DESC";
$blockedUsersResult = mysqli_query($conn, $blockedUsersQuery);

$riskyUsersQuery = "SELECT id, full_name, username, role, failed_attempts
                    FROM users
                    WHERE failed_attempts >= 2 AND is_blocked = 0
                    ORDER BY failed_attempts DESC, id DESC";
$riskyUsersResult = mysqli_query($conn, $riskyUsersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Security Center - OneFlow</title>
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
        <li><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Requests</a></li>
        <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
      </ul>

      <div class="sidebar-bottom">
        <div class="system-card">
          <p>Security Status</p>
          <h4><?php echo $blockedCount > 0 ? "Attention" : "Protected"; ?></h4>
          <span><?php echo $blockedCount; ?> blocked account(s)</span>
        </div>
      </div>
    </aside>

    <main class="main-content">

      <header class="topbar">
        <div class="topbar-left">
          <h1>Security Center</h1>
          <p>Track blocked accounts, failed attempts, and access protection across the system.</p>
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
          <h2>Security overview for OneFlow 🔐</h2>
          <p>Monitor risky login activity, unblock accounts when necessary, and keep access under control from one place.</p>
        </div>
        <div class="hero-actions">
          <a href="dashboardadmin.php" class="hero-btn primary-btn">
            <i class="fas fa-house"></i> Admin Dashboard
          </a>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-user-lock"></i></div>
          <div class="card-info">
            <h3><?php echo $blockedCount; ?></h3>
            <p>Blocked Accounts</p>
            <span>Users currently blocked from login</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-triangle-exclamation"></i></div>
          <div class="card-info">
            <h3><?php echo $failedAttemptsTotal; ?></h3>
            <p>Failed Attempts</p>
            <span>Total failed login attempts in records</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-shield"></i></div>
          <div class="card-info">
            <h3><?php echo $activeProtectedCount; ?></h3>
            <p> Users Under Monitoring</p>
            <span>Users with suspicious login activity</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-eye"></i></div>
          <div class="card-info">
            <h3><?php echo $highRiskCount; ?></h3>
            <p>High Risk Accounts</p>
            <span>Users close to being blocked</span>
          </div>
        </div>
      </section>

      <section class="dashboard-grid">
        <div class="left-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Blocked Accounts</h2>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Failed Attempts</th>
                    <th>Blocked Until</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($blockedUsersResult && mysqli_num_rows($blockedUsersResult) > 0) { ?>
                    <?php while ($blockedUser = mysqli_fetch_assoc($blockedUsersResult)) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($blockedUser['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($blockedUser['username']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($blockedUser['role']); ?>">
                            <?php echo ucfirst($blockedUser['role']); ?>
                          </span>
                        </td>
                        <td><?php echo (int) $blockedUser['failed_attempts']; ?></td>
                        <td>
                          <?php echo !empty($blockedUser['blocked_until']) ? htmlspecialchars($blockedUser['blocked_until']) : 'Manual'; ?>
                        </td>
                        <td>
                          <a href="securitycenter.php?unblock_id=<?php echo $blockedUser['id']; ?>" class="action-btn approve">
                            Unblock
                          </a>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="6">No blocked accounts found.</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Accounts Near Blocking</h2>
            </div>

            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Failed Attempts</th>
                    <th>Risk Level</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($riskyUsersResult && mysqli_num_rows($riskyUsersResult) > 0) { ?>
                    <?php while ($riskyUser = mysqli_fetch_assoc($riskyUsersResult)) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($riskyUser['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($riskyUser['username']); ?></td>
                        <td>
                          <span class="status <?php echo strtolower($riskyUser['role']); ?>">
                            <?php echo ucfirst($riskyUser['role']); ?>
                          </span>
                        </td>
                        <td><?php echo (int) $riskyUser['failed_attempts']; ?></td>
                        <td>
                          <span class="status pending">Warning</span>
                        </td>
                      </tr>
                    <?php } ?>
                  <?php } else { ?>
                    <tr>
                      <td colspan="5">No risky accounts found.</td>
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
              <h2>Security Summary</h2>
            </div>

            <div class="overview-box">
              <div class="overview-row">
                <span>Blocked Accounts</span>
                <strong><?php echo $blockedCount; ?></strong>
              </div>
              <div class="overview-row">
                <span>Total Failed Attempts</span>
                <strong><?php echo $failedAttemptsTotal; ?></strong>
              </div>
              <div class="overview-row">
                <span>Accounts Near Block</span>
                <strong><?php echo $highRiskCount; ?></strong>
              </div>
              <div class="overview-row">
                <span>Admin Monitoring</span>
                <strong>Active</strong>
              </div>
              <div class="overview-row">
                <span>System Protection</span>
                <strong>Enabled</strong>
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Security Notes</h2>
            </div>

            <div class="notification-list">
              <div class="notification-item">
                <div class="notif-icon teal"><i class="fas fa-shield-halved"></i></div>
                <div>
                  <h4>Login protection is active</h4>
                  <p>Accounts can be blocked after repeated failed login attempts.</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon green"><i class="fas fa-user-check"></i></div>
                <div>
                  <h4>Admin can manually unblock users</h4>
                  <p>Blocked HR and employee accounts can be restored from this page.</p>
                </div>
              </div>

              <div class="notification-item">
                <div class="notif-icon red"><i class="fas fa-eye"></i></div>
                <div>
                  <h4>Watch high-risk accounts</h4>
                  <p>Users with multiple failed attempts should be monitored before they get blocked.</p>
                </div>
              </div>
            </div>
          </div>

          
       
      </section>

    </main>
  </div>

  <div id="actionPopup" class="action-popup"></div>

  <script>
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
      const popupMessage = <?php echo json_encode($popupMessage); ?>;
      const popupType = <?php echo json_encode($popupType); ?>;

      if (popupMessage) {
        showPopup(popupMessage, popupType);
      }
    });
  </script>

</body>
</html>