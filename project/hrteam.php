<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR Team - OneFlow</title>
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
        <li class="active"><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
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
          <h1>HR Team</h1>
          <p>Overview of HR members and their responsibilities.</p>
        </div>

        <div class="topbar-right">
          <button class="logout-btn">Logout</button>
        </div>
      </header>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-user-tie"></i></div>
          <div class="card-info">
            <h3>6</h3>
            <p>Total HR Members</p>
            <span>Across all departments</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-briefcase"></i></div>
          <div class="card-info">
            <h3>4</h3>
            <p>Active Recruiters</p>
            <span>Currently handling hiring</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-check"></i></div>
          <div class="card-info">
            <h3>12</h3>
            <p>Interviews This Week</p>
            <span>Planned by HR team</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>HR Members</h2>
        </div>

        <div class="quick-actions">
          <div class="quick-card">
            <i class="fas fa-user-tie"></i>
            <h4>Rana Ali</h4>
            <p>Recruitment Manager</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-user-tie"></i>
            <h4>Ahmad Naser</h4>
            <p>Performance Manager</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-user-tie"></i>
            <h4>Lina Omar</h4>
            <p>Training Coordinator</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-user-tie"></i>
            <h4>Mohammad Samer</h4>
            <p>Employee Relations</p>
          </div>
        </div>
      </section>

    </main>
  </div>

</body>
</html>