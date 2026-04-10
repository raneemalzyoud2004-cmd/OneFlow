<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="dashboard-container">

    <!-- Sidebar -->
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
        <li class="active"><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
        <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
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

    <!-- Main Content -->
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Manage Users</h1>
          <p>Manage employee accounts, departments, and status.</p>
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
            <p>Total Users</p>
            <span>All registered users</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-check"></i></div>
          <div class="card-info">
            <h3>108</h3>
            <p>Active Users</p>
            <span>Currently active</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-clock"></i></div>
          <div class="card-info">
            <h3>7</h3>
            <p>Pending Accounts</p>
            <span>Waiting for approval</span>
          </div>
        </div>
      </section>

      <!-- Users Table -->
      <section class="panel">
        <div class="panel-header">
          <h2>Employees List</h2>
          <a href="#">Add New</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Role</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sarah Ahmad</td>
                <td>sarah@example.com</td>
                <td>HR</td>
                <td>Employee</td>
                <td><span class="status approved">Active</span></td>
              </tr>
              <tr>
                <td>Omar Khaled</td>
                <td>omar@example.com</td>
                <td>IT</td>
                <td>Manager</td>
                <td><span class="status pending">Pending</span></td>
              </tr>
              <tr>
                <td>Lina Samer</td>
                <td>lina@example.com</td>
                <td>Finance</td>
                <td>Employee</td>
                <td><span class="status rejected">Inactive</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>