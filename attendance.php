<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance - OneFlow</title>
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
        <li class="active"><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
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
          <h1>Attendance</h1>
          <p>Track employee attendance, absences, and late check-ins.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search attendance records...">
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
          <h2>Attendance Overview 📅</h2>
          <p>Monitor attendance records, late arrivals, and absent employees for today.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-file-export"></i> Export Attendance</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-user-check"></i></div>
          <div class="card-info">
            <h3>108</h3>
            <p>Present Today</p>
            <span>Checked in successfully</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-user-xmark"></i></div>
          <div class="card-info">
            <h3>7</h3>
            <p>Absent</p>
            <span>Not checked in</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-clock"></i></div>
          <div class="card-info">
            <h3>4</h3>
            <p>Late Check-ins</p>
            <span>Require review</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-calendar-minus"></i></div>
          <div class="card-info">
            <h3>12</h3>
            <p>On Leave</p>
            <span>Approved leave today</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Attendance Records</h2>
          <a href="#">View All</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Check-In Time</th>
                <th>Status</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sarah Ahmad</td>
                <td>Marketing</td>
                <td>08:00 AM</td>
                <td><span class="status approved">Present</span></td>
                <td>On time</td>
              </tr>

              <tr>
                <td>Omar Khaled</td>
                <td>IT</td>
                <td>08:35 AM</td>
                <td><span class="status pending">Late</span></td>
                <td>Traffic delay</td>
              </tr>

              <tr>
                <td>Lina Samer</td>
                <td>HR</td>
                <td>—</td>
                <td><span class="status rejected">Absent</span></td>
                <td>No check-in recorded</td>
              </tr>

              <tr>
                <td>Ahmad Nasser</td>
                <td>Finance</td>
                <td>07:55 AM</td>
                <td><span class="status approved">Present</span></td>
                <td>Early arrival</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>