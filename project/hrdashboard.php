<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Dashboard - OneFlow</title>
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
       <p class="admin-role">HR Panel</p>
      </div>

      <ul class="sidebar-menu">
  <li class="active"><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
  <ul class="sidebar-menu">
  <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>

  <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>

  <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>

  <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>

  <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>

  <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
</ul>
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

      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
         <h1>HR Dashboard</h1>
<p>Manage employees, attendance, leave requests, and recruitment in one place.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search employees, requests, reports...">
          </div>

          <div class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notif-count">3</span>
          </div>

          <div class="admin-avatar">H</div>
<div>
  <h4>HR</h4>
  <span>HR Manager</span>
</div>

          <button class="logout-btn">Logout</button>
        </div>
      </header>

      <!-- Hero Banner -->
      <section class="hero-banner">
  <div class="hero-text">
    <h2>Welcome back, HR 👋</h2>
    <p>You have <strong>8 pending leave requests</strong>, <strong>4 Late / Missing Attendance</strong>, and <strong>6 new applicants</strong> today.</p>
  </div>
  <div class="hero-actions">
    <button class="hero-btn primary-btn"><i class="fas fa-user-plus"></i> Add Employee</button>
    <button class="hero-btn secondary-btn"><i class="fas fa-file-export"></i> Export HR Report</button>
  </div>
</section>

      <!-- Stats -->
      <section class="cards">
  <div class="card">
    <div class="card-icon"><i class="fas fa-users"></i></div>
    <div class="card-info">
      <h3>120</h3>
      <p>Total Employees</p>
      <span>+5 this month</span>
    </div>
  </div>

  <div class="card">
    <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
    <div class="card-info">
      <h3>8</h3>
      <p>Leave Requests</p>
      <span>Waiting for review</span>
    </div>
  </div>

  <div class="card">
    <div class="card-icon"><i class="fas fa-user-plus"></i></div>
    <div class="card-info">
      <h3>6</h3>
      <p>New Applicants</p>
      <span>Received today</span>
    </div>
  </div>

  <div class="card">
    <div class="card-icon"><i class="fas fa-clock"></i></div>
    <div class="card-info">
      <h3>4</h3>
      <p>Late / Missing Attendance</p>
      <span>Need follow-up</span>
    </div>
  </div>
</section>
      <!-- Dashboard Grid -->
      <section class="dashboard-grid">

        <!-- Left Column -->
        <div class="left-column">

          <!-- Quick Actions -->
        <div class="panel">
  <div class="panel-header">
    <h2>Quick Actions</h2>
  </div>

  <div class="quick-actions">
    <div class="quick-card">
      <i class="fas fa-user-plus"></i>
      <h4>Add Employee</h4>
      <p>Create a new employee profile</p>
    </div>

    <div class="quick-card">
      <i class="fas fa-calendar-check"></i>
      <h4>Track Attendance</h4>
      <p>Monitor employee attendance records</p>
    </div>

    <div class="quick-card">
      <i class="fas fa-file-signature"></i>
      <h4>Review Leaves</h4>
      <p>Approve or reject leave requests</p>
    </div>

    <div class="quick-card">
      <i class="fas fa-user-tie"></i>
      <h4>Recruitment</h4>
      <p>Manage new applicants and hiring</p>
    </div>
  </div>
</div>
          <!-- Recent Requests -->
          <div class="panel">
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
      </tbody>
    </table>
  </div>
</div>
        <!-- Right Column -->
        <div class="right-column">

          <!-- Notifications -->
          <div class="panel">
  <div class="panel-header">
    <h2>Notifications</h2>
  </div>

  <div class="notification-list">
    <div class="notification-item">
      <div class="notif-icon teal"><i class="fas fa-calendar-check"></i></div>
      <div>
        <h4>4 new leave requests</h4>
        <p>Waiting for HR review</p>
      </div>
    </div>

    <div class="notification-item">
      <div class="notif-icon green"><i class="fas fa-user-plus"></i></div>
      <div>
        <h4>2 new applicants applied</h4>
        <p>Today at 11:15 AM</p>
      </div>
    </div>

    <div class="notification-item">
      <div class="notif-icon red"><i class="fas fa-clock"></i></div>
      <div>
        <h4>Attendance issue detected</h4>
        <p>Follow-up required</p>
      </div>
    </div>
  </div>
</div>

          <!-- Recent Activity -->
          <div class="panel">
  <div class="panel-header">
    <h2>Recent Activity</h2>
  </div>

  <div class="activity-list">
    <div class="activity-item">
      <span class="dot teal-dot"></span>
      <div>
        <h4>HR approved Sarah Ahmad's leave request</h4>
        <p>5 minutes ago</p>
      </div>
    </div>

    <div class="activity-item">
      <span class="dot green-dot"></span>
      <div>
        <h4>New applicant added to recruitment list</h4>
        <p>25 minutes ago</p>
      </div>
    </div>

    <div class="activity-item">
      <span class="dot orange-dot"></span>
      <div>
        <h4>Attendance report generated</h4>
        <p>1 hour ago</p>
      </div>
    </div>

    <div class="activity-item">
      <span class="dot red-dot"></span>
      <div>
        <h4>Late check-in flagged for review</h4>
        <p>Today</p>
      </div>
    </div>
  </div>
</div>

          <!-- Overview -->
          <div class="panel">
  <div class="panel-header">
    <h2>HR Overview</h2>
  </div>

  <div class="overview-box">
    <div class="overview-row">
      <span>Total Employees</span>
      <strong>120</strong>
    </div>
    <div class="overview-row">
      <span>Present Today</span>
      <strong>108</strong>
    </div>
    <div class="overview-row">
      <span>On Leave</span>
      <strong>7</strong>
    </div>
    <div class="overview-row">
      <span>New Applicants</span>
      <strong>6</strong>
    </div>
    <div class="overview-row">
      <span>Pending Leave Requests</span>
      <strong>8</strong>
    </div>
  </div>
</div>  

        </div>
      </section>

    </main>
  </div>

</body>
</html> 