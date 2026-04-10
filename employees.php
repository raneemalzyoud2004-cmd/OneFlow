<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employees - OneFlow</title>
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
        <li class="active"><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
        <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
        <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
        <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
        <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
          <h1>Employees</h1>
          <p>Manage employee records, departments, and work status.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search employees...">
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
          <h2>Employees Directory 👥</h2>
          <p>You can manage employee information, departments, and current work status from here.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add Employee</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-users"></i></div>
          <div class="card-info">
            <h3>120</h3>
            <p>Total Employees</p>
            <span>Active in the company</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-building"></i></div>
          <div class="card-info">
            <h3>6</h3>
            <p>Departments</p>
            <span>Across the organization</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-check"></i></div>
          <div class="card-info">
            <h3>108</h3>
            <p>Active Employees</p>
            <span>Currently working</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-clock"></i></div>
          <div class="card-info">
            <h3>12</h3>
            <p>On Leave</p>
            <span>Temporary absence</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Employee List</h2>
          <a href="#">View All</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Status</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sarah Ahmad</td>
                <td>Marketing</td>
                <td>Content Specialist</td>
                <td><span class="status approved">Active</span></td>
                <td>sarah@example.com</td>
              </tr>

              <tr>
                <td>Omar Khaled</td>
                <td>IT</td>
                <td>System Support</td>
                <td><span class="status pending">On Leave</span></td>
                <td>omar@example.com</td>
              </tr>

              <tr>
                <td>Lina Samer</td>
                <td>HR</td>
                <td>HR Coordinator</td>
                <td><span class="status approved">Active</span></td>
                <td>lina@example.com</td>
              </tr>

              <tr>
                <td>Ahmad Nasser</td>
                <td>Finance</td>
                <td>Accountant</td>
                <td><span class="status approved">Active</span></td>
                <td>ahmad@example.com</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>