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
  <title>Assign Tasks - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .assign-layout {
      display: grid;
      grid-template-columns: 1.3fr 0.9fr;
      gap: 24px;
      margin-top: 25px;
      align-items: start;
    }

    .assign-card,
    .preview-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 28px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .assign-card h3,
    .preview-card h3 {
      margin-bottom: 18px;
      color: #0f172a;
      font-size: 24px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 16px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #334155;
      font-weight: 600;
      font-size: 15px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 14px 16px;
      font-size: 15px;
      outline: none;
      transition: 0.3s;
      background: #f8fbfd;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #19c2c9;
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(25, 194, 201, 0.10);
    }

    .form-group textarea {
      resize: none;
      min-height: 130px;
    }

    .priority-options {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 8px;
    }

    .priority-pill {
      padding: 10px 16px;
      border-radius: 999px;
      font-size: 14px;
      font-weight: 600;
      border: 1px solid #dbe4ee;
      background: #f8fbfd;
      color: #334155;
      cursor: pointer;
    }

    .priority-pill.active {
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
      color: #fff;
      border-color: transparent;
    }

    .assign-actions {
      display: flex;
      gap: 12px;
      margin-top: 18px;
      flex-wrap: wrap;
    }

    .assign-btn {
      border: none;
      border-radius: 14px;
      padding: 13px 18px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.3s;
    }

    .assign-btn.primary {
      background: linear-gradient(135deg, #12c2cc, #2dd4bf);
      color: #fff;
    }

    .assign-btn.secondary {
      background: #eff6ff;
      color: #0369a1;
    }

    .assign-btn:hover {
      transform: translateY(-2px);
      opacity: 0.95;
    }

    .preview-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-top: 18px;
    }

    .preview-item {
      border: 1px solid #e8eef5;
      border-radius: 18px;
      padding: 16px;
      background: #fbfdff;
    }

    .preview-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      gap: 10px;
    }

    .preview-top h4 {
      color: #0f172a;
      margin: 0;
      font-size: 18px;
    }

    .task-badge {
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
    }

    .high-badge {
      background: #fee2e2;
      color: #b91c1c;
    }

    .medium-badge {
      background: #fef3c7;
      color: #92400e;
    }

    .low-badge {
      background: #dcfce7;
      color: #166534;
    }

    .preview-item p {
      color: #64748b;
      margin-bottom: 12px;
      line-height: 1.6;
      font-size: 14px;
    }

    .preview-meta {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      font-size: 14px;
      color: #334155;
    }

    @media (max-width: 1100px) {
      .assign-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 700px) {
      .form-row {
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
      <li class="active"><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
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
        <h1>Assign Tasks</h1>
        <p>Create, organize, and assign tasks to your team members clearly and quickly.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search task, member, deadline...">
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">4</span>
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
        <h2>Plan and distribute work smartly ✅</h2>
        <p>Assign tasks based on team role, urgency, and deadlines to keep work organized and on track.</p>
      </div>

      <div class="hero-actions">
        <button class="hero-btn primary-btn"><i class="fas fa-plus"></i> New Task</button>
        <button class="hero-btn secondary-btn"><i class="fas fa-eye"></i> View Progress</button>
      </div>
    </section>

    <section class="assign-layout">

      <div class="assign-card">
        <h3>Task Assignment Form</h3>

        <form>
          <div class="form-row">
            <div class="form-group">
              <label>Task Title</label>
              <input type="text" placeholder="Enter task title">
            </div>

            <div class="form-group">
              <label>Assign To</label>
              <select>
                <option>Ahmad Ali</option>
                <option>Sara Khaled</option>
                <option>Lina Noor</option>
                <option>Omar Sami</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Task Type</label>
              <select>
                <option>Development</option>
                <option>Design</option>
                <option>Testing</option>
                <option>Documentation</option>
              </select>
            </div>

            <div class="form-group">
              <label>Due Date</label>
              <input type="date">
            </div>
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea placeholder="Write the task details, notes, and requirements..."></textarea>
          </div>

          <div class="form-group">
            <label>Priority</label>
            <div class="priority-options">
              <span class="priority-pill active">High</span>
              <span class="priority-pill">Medium</span>
              <span class="priority-pill">Low</span>
            </div>
          </div>

          <div class="assign-actions">
            <button type="button" class="assign-btn primary">Assign Task</button>
            <button type="reset" class="assign-btn secondary">Clear Form</button>
          </div>
        </form>
      </div>

      <div class="preview-card">
        <h3>Recent Assigned Tasks</h3>

        <div class="preview-list">

          <div class="preview-item">
            <div class="preview-top">
              <h4>Employee Dashboard UI</h4>
              <span class="task-badge high-badge">High</span>
            </div>
            <p>Design and complete the employee dashboard interface with cards, table sections, and quick action buttons.</p>
            <div class="preview-meta">
              <span><strong>Assigned to:</strong> Ahmad Ali</span>
              <span><strong>Due:</strong> 20 Apr 2026</span>
            </div>
          </div>

          <div class="preview-item">
            <div class="preview-top">
              <h4>Leave Request Testing</h4>
              <span class="task-badge medium-badge">Medium</span>
            </div>
            <p>Test the leave request flow and check form validations, button states, and page transitions.</p>
            <div class="preview-meta">
              <span><strong>Assigned to:</strong> Omar Sami</span>
              <span><strong>Due:</strong> 22 Apr 2026</span>
            </div>
          </div>

          <div class="preview-item">
            <div class="preview-top">
              <h4>Profile Page Improvement</h4>
              <span class="task-badge low-badge">Low</span>
            </div>
            <p>Improve the profile page layout and add better spacing, visual hierarchy, and responsive behavior.</p>
            <div class="preview-meta">
              <span><strong>Assigned to:</strong> Lina Noor</span>
              <span><strong>Due:</strong> 24 Apr 2026</span>
            </div>
          </div>

        </div>
      </div>

    </section>

  </main>
</div>

</body>
</html>