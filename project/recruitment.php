<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recruitment - OneFlow</title>
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
        <li class="active"><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
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
          <h1>Recruitment</h1>
          <p>Track applicants, open positions, and hiring progress.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search applicants...">
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
          <h2>Recruitment Hub 👤</h2>
          <p>Manage open positions, review applicants, and follow hiring status.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add Applicant</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-user-plus"></i></div>
          <div class="card-info">
            <h3>18</h3>
            <p>Total Applicants</p>
            <span>In recruitment pipeline</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-briefcase"></i></div>
          <div class="card-info">
            <h3>4</h3>
            <p>Open Positions</p>
            <span>Currently hiring</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-clock"></i></div>
          <div class="card-info">
            <h3>7</h3>
            <p>Interview Stage</p>
            <span>Awaiting decisions</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-check"></i></div>
          <div class="card-info">
            <h3>3</h3>
            <p>Hired</p>
            <span>Successfully onboarded</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Applicants List</h2>
          <a href="#">View All</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Experience</th>
                <th>Status</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Rana Ali</td>
                <td>UI/UX Designer</td>
                <td>3 Years</td>
                <td><span class="status pending">Interview</span></td>
                <td>rana@example.com</td>
              </tr>

              <tr>
                <td>Yousef Sami</td>
                <td>Frontend Developer</td>
                <td>2 Years</td>
                <td><span class="status approved">Shortlisted</span></td>
                <td>yousef@example.com</td>
              </tr>

              <tr>
                <td>Huda Ahmad</td>
                <td>HR Assistant</td>
                <td>1 Year</td>
                <td><span class="status rejected">Rejected</span></td>
                <td>huda@example.com</td>
              </tr>

              <tr>
                <td>Ahmad Saleh</td>
                <td>Accountant</td>
                <td>4 Years</td>
                <td><span class="status approved">Hired</span></td>
                <td>ahmads@example.com</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>