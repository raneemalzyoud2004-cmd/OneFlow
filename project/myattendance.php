<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Attendance - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .attendance-buttons {
      margin: 20px 0;
    }
    .attendance-buttons button {
      padding: 10px 20px;
      margin-right: 10px;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      background: #0D1E4C;
      color: #fff;
      transition: background 0.2s;
    }
    .attendance-buttons button:hover:not(:disabled) {
      background: #26415E;
    }
    .attendance-buttons button:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    #work-hours {
      font-weight: 700;
      margin-left: 15px;
    }
  </style>
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
      <li class="active"><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="leaverequests_employee.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> Schedule</a></li>
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
    <section class="attendance-buttons">
      <button id="start-btn">Start Work</button>
      <button id="end-btn" disabled>End Work</button>
      <span id="work-hours">Working: 00:00:00</span>
    </section>
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
<script>
let startTime, timerInterval;
document.getElementById('start-btn').addEventListener('click', () => {
    startTime = new Date();
    document.getElementById('end-btn').disabled = false;
    document.getElementById('start-btn').disabled = true;
    timerInterval = setInterval(() => {
        const now = new Date();
        let diff = now - startTime;
        let hours = Math.floor(diff / 1000 / 60 / 60);
        let minutes = Math.floor((diff / 1000 / 60) % 60);
        let seconds = Math.floor((diff / 1000) % 60);
        document.getElementById('work-hours').innerText =
            `Working: ${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
    }, 1000);
});
document.getElementById('end-btn').addEventListener('click', () => {
    clearInterval(timerInterval);
    document.getElementById('end-btn').disabled = true;
    document.getElementById('start-btn').disabled = false;
    const now = new Date();
    let diff = now - startTime;
    let hours = Math.floor(diff / 1000 / 60 / 60);
    let minutes = Math.floor((diff / 1000 / 60) % 60);
    let seconds = Math.floor((diff / 1000) % 60);
    document.getElementById('work-hours').innerText =
        `Worked: ${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
        fetch('save_attendance.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        check_in: startTime.toTimeString().slice(0,8),
        check_out: now.toTimeString().slice(0,8),
        worked_hours: `${hours}:${minutes}:${seconds}`
    })
});
});
</script>
</body>
</html>