<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard - OneFlow</title>
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
      <li class="active"><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> My Attendance</a></li>
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
        <h1>Employee Dashboard</h1>
        <p>Track your tasks, attendance, schedule, and daily updates in one place.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search tasks, updates, schedule...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">4</span>
        </div>

        <div class="admin-avatar">E</div>
        <div>
          <h4>Employee</h4>
          <span>Team Member</span>
        </div>

        <button class="logout-btn">Logout</button>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Welcome back, Employee 👋</h2>
        <p>You have <strong>3 active tasks</strong>, <strong>1 meeting today</strong>, and <strong>4 new notifications</strong>.</p>
      </div>
      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-list-check"></i> View My Tasks</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-calendar-day"></i> Today’s Schedule</button>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
          <h3>3</h3>
          <p>Active Tasks</p>
          <span>2 due this week</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-check-circle"></i></div>
        <div class="card-info">
          <h3>12</h3>
          <p>Completed Tasks</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="card-info">
          <h3>96%</h3>
          <p>Attendance Rate</p>
          <span>This month</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-bell"></i></div>
        <div class="card-info">
          <h3>4</h3>
          <p>Notifications</p>
          <span>New updates today</span>
        </div>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="left-column">

        <div class="panel">
          <div class="panel-header">
            <h2>Quick Actions</h2>
          </div>

          <div class="quick-actions">
            <div class="quick-card">
              <i class="fas fa-list-check"></i>
              <h4>My Tasks</h4>
              <p>Check and manage your assigned work</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-calendar-check"></i>
              <h4>Attendance</h4>
              <p>Review your attendance and check-ins</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-clock"></i>
              <h4>My Schedule</h4>
              <p>See today’s meetings and time plan</p>
            </div>

            <div class="quick-card">
              <i class="fas fa-user-circle"></i>
              <h4>Profile</h4>
              <p>Update your account settings and info</p>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>My Current Tasks</h2>
            <a href="#">View All</a>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Task</th>
                  <th>Priority</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Finish monthly report</td>
                  <td>High</td>
                  <td>Apr 8, 2026</td>
                  <td><span class="status pending">In Progress</span></td>
                  <td><button class="action-btn view">View</button></td>
                </tr>
                <tr>
                  <td>Update client data</td>
                  <td>Medium</td>
                  <td>Apr 10, 2026</td>
                  <td><span class="status approved">Completed</span></td>
                  <td><button class="action-btn view">View</button></td>
                </tr>
                <tr>
                  <td>Prepare presentation slides</td>
                  <td>High</td>
                  <td>Apr 11, 2026</td>
                  <td><span class="status rejected">Not Started</span></td>
                  <td><button class="action-btn view">View</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="right-column">
        <div class="panel">
          <div class="panel-header">
            <h2>Notifications</h2>
          </div>

          <div class="notification-list">
            <div class="notification-item">
              <div class="notif-icon teal"><i class="fas fa-list-check"></i></div>
              <div>
                <h4>A new task has been assigned</h4>
                <p>Please review it today</p>
              </div>
            </div>

            <div class="notification-item">
              <div class="notif-icon green"><i class="fas fa-calendar-check"></i></div>
              <div>
                <h4>Your attendance was updated</h4>
                <p>Check-in recorded successfully</p>
              </div>
            </div>

            <div class="notification-item">
              <div class="notif-icon red"><i class="fas fa-clock"></i></div>
              <div>
                <h4>You have a meeting today</h4>
                <p>Starts at 2:00 PM</p>
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>Recent Activity</h2>
          </div>

          <div class="activity-list">
            <div class="activity-item">
              <span class="dot teal-dot"></span>
              <div>
                <h4>You completed the weekly report</h4>
                <p>20 minutes ago</p>
              </div>
            </div>
            <div class="activity-item">
              <span class="dot green-dot"></span>
              <div>
                <h4>You checked in successfully</h4>
                <p>Today at 8:03 AM</p>
              </div>
            </div>
            <div class="activity-item">
              <span class="dot orange-dot"></span>
              <div>
                <h4>You viewed your updated schedule</h4>
                <p>1 hour ago</p>
              </div>
            </div>
            <div class="activity-item">
              <span class="dot red-dot"></span>
              <div>
                <h4>A new deadline was added to your task</h4>
                <p>Yesterday</p>
              </div>
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <h2>My Overview</h2>
          </div>

          <div class="overview-box">
            <div class="overview-row">
              <span>Assigned Tasks</span>
              <strong>7</strong>
            </div>
            <div class="overview-row">
              <span>Completed Tasks</span>
              <strong>12</strong>
            </div>
            <div class="overview-row">
              <span>Meetings Today</span>
              <strong>1</strong>
            </div>
            <div class="overview-row">
              <span>Attendance Rate</span>
              <strong>96%</strong>
            </div>
            <div class="overview-row">
              <span>New Notifications</span>
              <strong>4</strong>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>
</div>

</body>
</html>