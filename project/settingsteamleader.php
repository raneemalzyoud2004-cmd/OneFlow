<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Team Leader';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .settings-grid {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 24px;
      margin-top: 28px;
      align-items: start;
    }

    .settings-card,
    .preferences-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 26px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .settings-card h3,
    .preferences-card h3 {
      font-size: 24px;
      color: #0f172a;
      margin-bottom: 20px;
    }

    .settings-form .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .settings-form .form-group {
      margin-bottom: 16px;
    }

    .settings-form label {
      display: block;
      margin-bottom: 8px;
      color: #334155;
      font-weight: 600;
      font-size: 15px;
    }

    .settings-form input,
    .settings-form select,
    .settings-form textarea {
      width: 100%;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 14px 16px;
      font-size: 15px;
      outline: none;
      transition: 0.3s;
      background: #f8fbfd;
    }

    .settings-form input:focus,
    .settings-form select:focus,
    .settings-form textarea:focus {
      border-color: #19c2c9;
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(25, 194, 201, 0.10);
    }

    .settings-form textarea {
      resize: none;
      min-height: 110px;
    }

    .settings-actions {
      display: flex;
      gap: 12px;
      margin-top: 10px;
      flex-wrap: wrap;
    }

    .settings-btn {
      border: none;
      border-radius: 14px;
      padding: 13px 18px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
    }

    .settings-btn.primary {
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
      color: #fff;
    }

    .settings-btn.secondary {
      background: #eff6ff;
      color: #0369a1;
    }

    .settings-btn:hover {
      transform: translateY(-2px);
      opacity: 0.95;
    }

    .preferences-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .preference-item {
      border: 1px solid #edf2f7;
      border-radius: 18px;
      padding: 16px;
      background: #fbfdff;
    }

    .preference-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 8px;
    }

    .preference-top h4 {
      margin: 0;
      font-size: 17px;
      color: #0f172a;
    }

    .preference-item p {
      margin: 0;
      color: #64748b;
      font-size: 14px;
      line-height: 1.5;
    }

    .toggle-switch {
      width: 48px;
      height: 28px;
      background: #cbd5e1;
      border-radius: 999px;
      position: relative;
      flex-shrink: 0;
    }

    .toggle-switch::after {
      content: "";
      width: 22px;
      height: 22px;
      background: #ffffff;
      border-radius: 50%;
      position: absolute;
      top: 3px;
      left: 3px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .toggle-switch.active {
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
    }

    .toggle-switch.active::after {
      left: 23px;
    }

    .account-box {
      margin-top: 18px;
      border-top: 1px solid #edf2f7;
      padding-top: 18px;
    }

    .account-box h4 {
      margin-bottom: 12px;
      color: #0f172a;
      font-size: 18px;
    }

    .account-box ul {
      padding-left: 18px;
      color: #64748b;
      line-height: 1.8;
      font-size: 14px;
    }

    @media (max-width: 1100px) {
      .settings-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 700px) {
      .settings-form .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
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
      <p class="admin-role">Team Leader Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardteamleader.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li class="active"><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Performance</p>
        <h4>Excellent</h4>
        <span>92% tasks completed</span>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Settings</h1>
        <p>Manage your profile, team preferences, notifications, and personal dashboard options.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search setting, preference...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">6</span>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Team Leader</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Customize Your Workspace ⚙️</h2>
        <p>Update your information, control alert preferences, and adjust how your dashboard works.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-floppy-disk"></i> Save Changes</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-rotate-left"></i> Reset</button>
      </div>
    </section>

    <section class="settings-grid">

      <div class="settings-card">
        <h3>Profile Settings</h3>

        <form class="settings-form">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" value="<?php echo htmlspecialchars($full_name); ?>">
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input type="email" value="teamleader@oneflow.com">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" value="+962 7X XXX XXXX">
            </div>

            <div class="form-group">
              <label>Department</label>
              <select>
                <option selected>Development Team</option>
                <option>Design Team</option>
                <option>QA Team</option>
                <option>Operations Team</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>About</label>
            <textarea placeholder="Write a short description about your role and responsibilities...">Responsible for assigning tasks, following up with the team, and improving work progress.</textarea>
          </div>

          <div class="settings-actions">
            <button type="button" class="settings-btn primary">Update Profile</button>
            <button type="reset" class="settings-btn secondary">Cancel</button>
          </div>
        </form>
      </div>

      <div class="preferences-card">
        <h3>Preferences</h3>

        <div class="preferences-list">

          <div class="preference-item">
            <div class="preference-top">
              <h4>Email Notifications</h4>
              <div class="toggle-switch active"></div>
            </div>
            <p>Receive email updates for task changes, reminders, and team activities.</p>
          </div>

          <div class="preference-item">
            <div class="preference-top">
              <h4>Task Deadline Alerts</h4>
              <div class="toggle-switch active"></div>
            </div>
            <p>Get alerted when deadlines are near or when tasks become overdue.</p>
          </div>

          <div class="preference-item">
            <div class="preference-top">
              <h4>Weekly Summary</h4>
              <div class="toggle-switch"></div>
            </div>
            <p>Receive a weekly performance summary of your team progress and status.</p>
          </div>

          <div class="preference-item">
            <div class="preference-top">
              <h4>Dark Mode</h4>
              <div class="toggle-switch"></div>
            </div>
            <p>Switch your dashboard style to a darker appearance for night use.</p>
          </div>

        </div>

        <div class="account-box">
          <h4>Account Notes</h4>
          <ul>
            <li>Your role is currently set as <strong>Team Leader</strong>.</li>
            <li>You can assign and monitor tasks for your team members.</li>
            <li>Backend saving will be connected later after frontend completion.</li>
          </ul>
        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>