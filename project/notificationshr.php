<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - OneFlow</title>
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
        <li class="active"><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
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
          <h1>Notifications</h1>
          <p>Stay updated with employee activities, leave requests, and HR alerts.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search notifications...">
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
          <h2>Notifications Center 🔔</h2>
          <p>Review important HR updates, employee changes, and pending actions.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-check-double"></i> Mark All Read</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-bell"></i></div>
          <div class="card-info">
            <h3>12</h3>
            <p>Total Notifications</p>
            <span>Received today</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="card-info">
            <h3>4</h3>
            <p>Pending Alerts</p>
            <span>Require your review</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="card-info">
            <h3>3</h3>
            <p>Leave Updates</p>
            <span>New leave activity</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-clock"></i></div>
          <div class="card-info">
            <h3>5</h3>
            <p>Attendance Alerts</p>
            <span>Late or missing check-ins</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Recent Notifications</h2>
          <a href="#">View All</a>
        </div>

        <div class="notification-list">
          <div class="notification-item">
            <div class="notif-icon teal"><i class="fas fa-file-circle-check"></i></div>
            <div>
              <h4>Sarah Ahmad submitted a sick leave request</h4>
              <p>10 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon green"><i class="fas fa-user-check"></i></div>
            <div>
              <h4>Omar Khaled checked in successfully</h4>
              <p>25 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon red"><i class="fas fa-clock"></i></div>
            <div>
              <h4>Lina Samer has a late check-in today</h4>
              <p>40 minutes ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon teal"><i class="fas fa-user-plus"></i></div>
            <div>
              <h4>New applicant added for Frontend Developer position</h4>
              <p>1 hour ago</p>
            </div>
          </div>

          <div class="notification-item">
            <div class="notif-icon green"><i class="fas fa-circle-check"></i></div>
            <div>
              <h4>Annual leave request approved for Ahmad Nasser</h4>
              <p>Today</p>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>

</body>
</html>