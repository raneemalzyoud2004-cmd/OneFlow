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
        <p class="admin-role">HR Panel</p>
      </div>

      <ul class="sidebar-menu">
        <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
        <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
        <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
        <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li class="active"><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
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
          <p>Manage your HR profile, preferences, and account settings.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search settings...">
          </div>

          <div class="admin-profile">
            <div class="admin-avatar">H</div>
            <div>
              <h4>HR</h4>
              <span>HR Manager</span>
            </div>
          </div>

          <button class="logout-btn">Logout</button>
        </div>
      </header>

      <section class="hero-banner">
        <div class="hero-text">
          <h2>HR Settings ⚙️</h2>
          <p>Update your account details, notification preferences, and security settings.</p>
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
                <input type="text" value="HR Manager" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
              </div>

              <div>
                <label style="display:block; margin-bottom:8px; font-weight:600;">Email Address</label>
                <input type="email" value="hr@oneflow.com" style="width:100%; padding:14px; border-radius:14px; border:1px solid #d9e1ea; outline:none;">
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
                <span>Leave Request Alerts</span>
                <input type="checkbox" checked>
              </label>

              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>Attendance Alerts</span>
                <input type="checkbox" checked>
              </label>

              <label style="display:flex; align-items:center; justify-content:space-between; background:#f7fafd; padding:16px; border-radius:14px;">
                <span>Recruitment Updates</span>
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
                <strong>HR Manager</strong>
              </div>
              <div class="overview-row">
                <span>Department</span>
                <strong>Human Resources</strong>
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