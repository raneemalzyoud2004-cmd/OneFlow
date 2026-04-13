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
  <title>Settings - OneFlow</title>
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
        <li class="active"><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
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
          <h1>Settings</h1>
          <p>Manage your admin profile, preferences, and system account settings.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search settings...">
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
          <h2>Admin Settings ⚙️</h2>
          <p>Update your account details, notification preferences, security settings, and admin access controls.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-floppy-disk"></i> Save Changes</button>
        </div>
      </section>

      <section class="dashboard-grid">
        <div class="left-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Profile Settings</h2>
            </div>

            <div style="display:grid; gap:16px; padding-top:10px;">
              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Full Name</label>
                <input type="text" value="Admin" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>

              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Email Address</label>
                <input type="email" value="admin@oneflow.com" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>

              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Phone Number</label>
                <input type="text" value="+962790000000" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Notification Preferences</h2>
            </div>

            <div style="display:grid; gap:16px; padding-top:10px;">
              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>Email Notifications</span>
                <input type="checkbox" checked>
              </label>

              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>User Registration Alerts</span>
                <input type="checkbox" checked>
              </label>

              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>System Notifications</span>
                <input type="checkbox" checked>
              </label>

              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>Analytics Reports</span>
                <input type="checkbox">
              </label>
            </div>
          </div>

        </div>

        <div class="right-column">

          <div class="panel">
            <div class="panel-header">
              <h2>Security</h2>
            </div>

            <div style="display:grid; gap:16px; padding-top:10px;">
              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Current Password</label>
                <input type="password" placeholder="Enter current password" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>

              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">New Password</label>
                <input type="password" placeholder="Enter new password" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>

              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Confirm Password</label>
                <input type="password" placeholder="Confirm new password" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="panel-header">
              <h2>Account Status</h2>
            </div>

            <div class="overview-box">
              <div class="overview-row">
                <span>Role</span>
                <strong>Super Admin</strong>
              </div>
              <div class="overview-row">
                <span>Department</span>
                <strong>Administration</strong>
              </div>
              <div class="overview-row">
                <span>Account Status</span>
                <strong>Active</strong>
              </div>
              <div class="overview-row">
                <span>Last Login</span>
                <strong>Today</strong>
              </div>
            </div>
          </div>

        </div>
      </section>

    </main>
  </div>

</body>
</html>