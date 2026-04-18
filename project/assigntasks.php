<?php
session_start();
require_once 'config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teamleader') {
    header("Location: login.php");
    exit();
}

$leader_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Team Leader';
$first_letter = strtoupper(substr(trim($full_name), 0, 1));

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ticket'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = trim($_POST['priority'] ?? 'medium');
    $assigned_to = (int) ($_POST['assigned_to'] ?? 0);
    $due_date = trim($_POST['due_date'] ?? '');

    $allowed_priorities = ['low', 'medium', 'high'];

    if ($title === '' || $description === '' || $assigned_to <= 0) {
        $error_message = "Please fill in all required fields.";
    } elseif (!in_array($priority, $allowed_priorities, true)) {
        $error_message = "Invalid priority selected.";
    } else {
        $checkEmployee = mysqli_query($conn, "SELECT id, full_name FROM users WHERE id = $assigned_to AND role = 'employee' LIMIT 1");

        if (!$checkEmployee || mysqli_num_rows($checkEmployee) === 0) {
            $error_message = "Selected employee was not found.";
        } else {
            $safe_title = mysqli_real_escape_string($conn, $title);
            $safe_description = mysqli_real_escape_string($conn, $description);
            $safe_priority = mysqli_real_escape_string($conn, $priority);

            $safe_due_date_sql = "NULL";
            if (!empty($due_date)) {
                $safe_due_date = mysqli_real_escape_string($conn, $due_date);
                $safe_due_date_sql = "'$safe_due_date'";
            }

            $insertQuery = "
                INSERT INTO team_tickets (title, description, priority, status, assigned_by, assigned_to, due_date)
                VALUES ('$safe_title', '$safe_description', '$safe_priority', 'pending', $leader_id, $assigned_to, $safe_due_date_sql)
            ";

            if (mysqli_query($conn, $insertQuery)) {
                $success_message = "Ticket assigned successfully.";
            } else {
                $error_message = "Failed to assign ticket: " . mysqli_error($conn);
            }
        }
    }
}

$employees = [];
$employeesQuery = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role = 'employee' ORDER BY full_name ASC");
if ($employeesQuery) {
    while ($row = mysqli_fetch_assoc($employeesQuery)) {
        $employees[] = $row;
    }
}

$recentTickets = [];
$recentTicketsQuery = mysqli_query($conn, "
    SELECT tt.*, u.full_name AS employee_name
    FROM team_tickets tt
    JOIN users u ON tt.assigned_to = u.id
    WHERE tt.assigned_by = $leader_id
    ORDER BY tt.created_at DESC
    LIMIT 6
");
if ($recentTicketsQuery) {
    while ($row = mysqli_fetch_assoc($recentTicketsQuery)) {
        $recentTickets[] = $row;
    }
}

function priorityBadgeClass($priority)
{
    if ($priority === 'high') {
        return 'high-badge';
    }
    if ($priority === 'medium') {
        return 'medium-badge';
    }
    return 'low-badge';
}
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
      box-sizing: border-box;
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
      text-transform: capitalize;
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

    .alert-box {
      border-radius: 16px;
      padding: 14px 18px;
      margin-bottom: 18px;
      font-weight: 600;
      font-size: 14px;
    }

    .alert-success {
      background: #ecfdf5;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert-error {
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
    }

    .empty-box {
      padding: 18px;
      border-radius: 16px;
      background: #f8fbfd;
      color: #64748b;
      border: 1px dashed #dbe4ee;
      text-align: center;
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
        <span>Smart task assignment enabled</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Assign Tasks</h1>
        <p>Create and send tickets directly to employees.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search task, member, deadline..." disabled>
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">4</span>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo htmlspecialchars($first_letter); ?>
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
        <h2>Create a team ticket quickly ✅</h2>
        <p>Assign work with title, details, priority, and deadline so employees can start immediately.</p>
      </div>

      <div class="hero-actions">
        <a href="assigntasks.php" class="hero-btn primary-btn"><i class="fas fa-plus"></i> New Ticket</a>
        <a href="tasksprogress.php" class="hero-btn secondary-btn"><i class="fas fa-eye"></i> View Progress</a>
      </div>
    </section>

    <section class="assign-layout">

      <div class="assign-card">
        <h3>Ticket Assignment Form</h3>

        <?php if ($success_message !== ''): ?>
          <div class="alert-box alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message !== ''): ?>
          <div class="alert-box alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label for="title">Ticket Title</label>
              <input type="text" id="title" name="title" placeholder="Enter ticket title" required>
            </div>

            <div class="form-group">
              <label for="assigned_to">Assign To</label>
              <select id="assigned_to" name="assigned_to" required>
                <option value="">Select employee</option>
                <?php foreach ($employees as $employee): ?>
                  <option value="<?php echo (int) $employee['id']; ?>">
                    <?php echo htmlspecialchars($employee['full_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="priority">Priority</label>
              <select id="priority" name="priority" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>

            <div class="form-group">
              <label for="due_date">Due Date</label>
              <input type="date" id="due_date" name="due_date">
            </div>
          </div>

          <div class="form-group">
            <label for="description">Task Description</label>
            <textarea id="description" name="description" placeholder="Write the full task details here..." required></textarea>
          </div>

          <div class="assign-actions">
            <button type="submit" name="assign_ticket" class="assign-btn primary">
              <i class="fas fa-paper-plane"></i> Assign Ticket
            </button>
            <button type="reset" class="assign-btn secondary">
              <i class="fas fa-rotate-left"></i> Reset
            </button>
          </div>
        </form>
      </div>

      <div class="preview-card">
        <h3>Recently Assigned Tickets</h3>

        <?php if (empty($recentTickets)): ?>
          <div class="empty-box">
            No tickets assigned yet. Start by creating your first team ticket.
          </div>
        <?php else: ?>
          <div class="preview-list">
            <?php foreach ($recentTickets as $ticket): ?>
              <div class="preview-item">
                <div class="preview-top">
                  <h4><?php echo htmlspecialchars($ticket['title']); ?></h4>
                  <span class="task-badge <?php echo priorityBadgeClass($ticket['priority']); ?>">
                    <?php echo htmlspecialchars($ticket['priority']); ?>
                  </span>
                </div>

                <p><?php echo htmlspecialchars($ticket['description']); ?></p>

                <div class="preview-meta">
                  <span><strong>Employee:</strong> <?php echo htmlspecialchars($ticket['employee_name']); ?></span>
                  <span><strong>Status:</strong> <?php echo htmlspecialchars(str_replace('_', ' ', $ticket['status'])); ?></span>
                  <span><strong>Due:</strong> <?php echo !empty($ticket['due_date']) ? htmlspecialchars($ticket['due_date']) : 'No deadline'; ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </section>

  </main>
</div>

</body>
</html>