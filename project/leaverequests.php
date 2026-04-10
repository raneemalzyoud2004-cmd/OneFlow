<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Requests - OneFlow</title>
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
        <li class="active"><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
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
          <h1>Leave Requests</h1>
          <p>Review and manage employee leave submissions.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search leave requests...">
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
          <h2>Manage Leave Requests 📝</h2>
          <p>Approve, reject, and review employee leave requests from one place.</p>
        </div>
        <div class="hero-actions">
          <button class="hero-btn primary-btn"><i class="fas fa-file-export"></i> Export Requests</button>
        </div>
      </section>

      <section class="cards">
        <div class="card">
          <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="card-info">
            <h3>8</h3>
            <p>Pending Requests</p>
            <span>Waiting for review</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-circle-check"></i></div>
          <div class="card-info">
            <h3>24</h3>
            <p>Approved</p>
            <span>Processed successfully</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-circle-xmark"></i></div>
          <div class="card-info">
            <h3>5</h3>
            <p>Rejected</p>
            <span>Declined requests</span>
          </div>
        </div>

        <div class="card">
          <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
          <div class="card-info">
            <h3>12</h3>
            <p>On Leave Today</p>
            <span>Approved absences</span>
          </div>
        </div>
      </section>

      <section class="panel">
        <div class="panel-header">
          <h2>Recent Leave Requests</h2>
          <a href="#">View All</a>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Leave Type</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sarah Ahmad</td>
                <td>Marketing</td>
                <td>Sick Leave</td>
                <td><span class="status pending">Pending</span></td>
                <td>
                  <button class="action-btn approve">Approve</button>
                  <button class="action-btn reject">Reject</button>
                </td>
              </tr>

              <tr>
                <td>Omar Khaled</td>
                <td>IT</td>
                <td>Annual Leave</td>
                <td><span class="status approved">Approved</span></td>
                <td>
                  <button class="action-btn view">View</button>
                </td>
              </tr>

              <tr>
                <td>Lina Samer</td>
                <td>HR</td>
                <td>Emergency Leave</td>
                <td><span class="status rejected">Rejected</span></td>
                <td>
                  <button class="action-btn view">View</button>
                </td>
              </tr>

              <tr>
                <td>Ahmad Nasser</td>
                <td>Finance</td>
                <td>Annual Leave</td>
                <td><span class="status pending">Pending</span></td>
                <td>
                  <button class="action-btn approve">Approve</button>
                  <button class="action-btn reject">Reject</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>

</body>
</html>