<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Settings - OneFlow</title>
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
      <p class="admin-role">Employee Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> My Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> My Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li class="active"><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Performance Status</p>
        <h4>Excellent</h4>
        <span>On track this week</span>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1>Settings</h1>
        <p>Manage your account, password, and personal preferences.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search settings...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">1</span>
        </div>

        <div class="admin-avatar">E</div>
        <div>
          <h4>Employee</h4>
          <span>Team Member</span>
        </div>

        <button class="logout-btn">Logout</button>
      </div>
    </header>

    <section class="dashboard-grid">
      <div class="left-column">
        <div class="panel">
          <div class="panel-header">
            <h2>Profile Settings</h2>
          </div>

          <div class="overview-box">
            <div class="overview-row">
              <span>Full Name</span>
              <strong>Employee Name</strong>
            </div>
            <div class="overview-row">
              <span>Email</span>
              <strong>employee@oneflow.com</strong>
            </div>
            <div class="overview-row">
              <span>Role</span>
              <strong>Team Member</strong>
            </div>
            <div class="overview-row">
              <span>Department</span>
              <strong>Operations</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="right-column">
        <div class="panel">
          <div class="panel-header">
            <h2>Account Options</h2>
          </div>

          <div class="quick-actions">
            <div class="quick-card">
              <i class="fas fa-user-edit"></i>
              <h4>Edit Profile</h4>
              <p>Update your personal information</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-lock"></i>
              <h4>Change Password</h4>
              <p>Keep your account secure</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-bell"></i>
              <h4>Notification Preferences</h4>
              <p>Manage your alerts and reminders</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-shield-alt"></i>
              <h4>Privacy Settings</h4>
              <p>Control your account privacy</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

</body>
</html>