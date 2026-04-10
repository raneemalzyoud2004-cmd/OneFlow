<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Schedule - OneFlow</title>
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
      <li class="active"><a href="myschedule.php"><i class="fas fa-clock"></i> My Schedule</a></li>
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
        <h1>My Schedule</h1>
        <p>Stay updated with your meetings, work plan, and important times.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search schedule...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">3</span>
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
        <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="card-info">
          <h3>1</h3>
          <p>Meetings Today</p>
          <span>Planned for today</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-business-time"></i></div>
        <div class="card-info">
          <h3>5</h3>
          <p>Work Sessions</p>
          <span>This week</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clock"></i></div>
        <div class="card-info">
          <h3>2:00 PM</h3>
          <p>Next Meeting</p>
          <span>Today</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Today Schedule</h2>
        <a href="#">View Calendar</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Time</th>
              <th>Activity</th>
              <th>Type</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>08:00 AM</td>
              <td>Check In</td>
              <td>Work Start</td>
              <td><span class="status approved">Done</span></td>
            </tr>
            <tr>
              <td>10:00 AM</td>
              <td>Team Review</td>
              <td>Meeting</td>
              <td><span class="status approved">Done</span></td>
            </tr>
            <tr>
              <td>02:00 PM</td>
              <td>Project Sync</td>
              <td>Meeting</td>
              <td><span class="status pending">Upcoming</span></td>
            </tr>
            <tr>
              <td>04:00 PM</td>
              <td>Check Out</td>
              <td>Work End</td>
              <td><span class="status rejected">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

</body>
</html>