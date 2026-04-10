<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Attendance - OneFlow</title>
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
      <li class="active"><a href="myattendance.php"><i class="fas fa-calendar-check"></i> My Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> My Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
        <h1>My Attendance</h1>
        <p>Monitor your attendance records, check-ins, and work days.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search attendance...">
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

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
          <h3>96%</h3>
          <p>Attendance Rate</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3>2</h3>
          <p>Late Check-ins</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-check"></i></div>
        <div class="card-info">
          <h3>21</h3>
          <p>Present Days</p>
          <span>Current month</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Attendance History</h2>
        <a href="#">View All</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Check In</th>
              <th>Check Out</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Apr 7, 2026</td>
              <td>08:02 AM</td>
              <td>04:00 PM</td>
              <td><span class="status approved">Present</span></td>
            </tr>
            <tr>
              <td>Apr 6, 2026</td>
              <td>08:15 AM</td>
              <td>04:00 PM</td>
              <td><span class="status pending">Late</span></td>
            </tr>
            <tr>
              <td>Apr 5, 2026</td>
              <td>08:00 AM</td>
              <td>04:03 PM</td>
              <td><span class="status approved">Present</span></td>
            </tr>
            <tr>
              <td>Apr 4, 2026</td>
              <td>-</td>
              <td>-</td>
              <td><span class="status rejected">Absent</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

</body>
</html>