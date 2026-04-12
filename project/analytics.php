<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - OneFlow</title>
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
        <li class="active"><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
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
          <h1>Analytics</h1>
          <p>Track system performance and employee metrics.</p>
        </div>

        <div class="topbar-right">
          <button class="logout-btn">Logout</button>
        </div>
      </header>

      <!-- Cards -->
      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-users"></i></div>
          <div class="card-info">
            <h3>120</h3>
            <p>Total Employees</p>
            <span>+8 this month</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
          <div class="card-info">
            <h3>92%</h3>
            <p>Performance Rate</p>
            <span>Across all departments</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-file-lines"></i></div>
          <div class="card-info">
            <h3>34</h3>
            <p>Reports Generated</p>
            <span>This month</span>
          </div>
        </div>
      </section>

      <!-- Fake Charts -->
      <section class="panel">
        <div class="panel-header">
          <h2>Performance Overview</h2>
        </div>

        <div class="quick-actions">
          <div class="quick-card">
            <i class="fas fa-arrow-up"></i>
            <h4>Employee Growth</h4>
            <p>Increasing steadily this quarter 📈</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-clock"></i>
            <h4>Response Time</h4>
            <p>Average approval time improved</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-check"></i>
            <h4>Approval Rate</h4>
            <p>Higher approval rate this month</p>
          </div>

          <div class="quick-card">
            <i class="fas fa-triangle-exclamation"></i>
            <h4>Issues</h4>
            <p>Minor system warnings detected</p>
          </div>
        </div>
      </section>

    </main>
  </div>

</body>
</html>
