<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Notifications - OneFlow</title>
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
      <li class="active"><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
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
        <h1>Notifications</h1>
        <p>See your latest updates, reminders, and employee alerts.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search notifications...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">4</span>
        </div>

        <div class="admin-avatar">E</div>
        <div>
          <h4>Employee</h4>
          <span>Team Member</span>
        </div>

        <button class="logout-btn">Logout</button>
      </div>
    </header>

    <section class="panel">
      <div class="panel-header">
        <h2>Recent Notifications</h2>
      </div>

      <div class="notification-list">
        <div class="notification-item">
          <div class="notif-icon teal"><i class="fas fa-list-check"></i></div>
          <div>
            <h4>New task assigned to you</h4>
            <p>Today at 9:30 AM</p>
          </div>
        </div>

        <div class="notification-item">
          <div class="notif-icon green"><i class="fas fa-calendar-check"></i></div>
          <div>
            <h4>Your attendance was updated</h4>
            <p>Check-in recorded successfully</p>
          </div>
        </div>

        <div class="notification-item">
          <div class="notif-icon red"><i class="fas fa-clock"></i></div>
          <div>
            <h4>You have a meeting at 2:00 PM</h4>
            <p>Reminder for today</p>
          </div>
        </div>

        <div class="notification-item">
          <div class="notif-icon green"><i class="fas fa-check-circle"></i></div>
          <div>
            <h4>Task completed successfully</h4>
            <p>Your recent update was saved</p>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

</body>
</html>