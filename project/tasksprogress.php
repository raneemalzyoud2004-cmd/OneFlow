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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int) ($_POST['ticket_id'] ?? 0);

    if ($ticket_id > 0) {
        $ticketQuery = mysqli_query($conn, "
            SELECT id, assigned_to
            FROM team_tickets
            WHERE id = $ticket_id AND assigned_by = $leader_id
            LIMIT 1
        ");

        if ($ticketQuery && mysqli_num_rows($ticketQuery) === 1) {
            $ticketData = mysqli_fetch_assoc($ticketQuery);
            $employee_id = (int) $ticketData['assigned_to'];

            if (isset($_POST['approve_ticket'])) {
                require_once 'includes/appreciation_messages.php';
                $randomMessage = $appreciationMessages[array_rand($appreciationMessages)];
                $safeMessage = mysqli_real_escape_string($conn, $randomMessage);

                $updateTicket = mysqli_query($conn, "
                    UPDATE team_tickets
                    SET status = 'completed'
                    WHERE id = $ticket_id AND assigned_by = $leader_id
                ");

                if ($updateTicket) {
                    mysqli_query($conn, "
                        INSERT INTO ticket_appreciation_messages (ticket_id, employee_id, leader_id, message)
                        VALUES ($ticket_id, $employee_id, $leader_id, '$safeMessage')
                    ");
                    $success_message = "Ticket approved and appreciation message sent successfully.";
                } else {
                    $error_message = "Failed to approve the ticket.";
                }
            }

            if (isset($_POST['return_ticket'])) {
                $feedback = trim($_POST['leader_feedback'] ?? '');

                if ($feedback === '') {
                    $error_message = "Please write feedback before returning the ticket.";
                } else {
                    $safeFeedback = mysqli_real_escape_string($conn, $feedback);

                    $updateTicket = mysqli_query($conn, "
                        UPDATE team_tickets
                        SET status = 'revision_required', leader_feedback = '$safeFeedback'
                        WHERE id = $ticket_id AND assigned_by = $leader_id
                    ");

                    if ($updateTicket) {
                        $success_message = "Ticket returned to the employee for revision.";
                    } else {
                        $error_message = "Failed to return the ticket.";
                    }
                }
            }
        } else {
            $error_message = "Ticket not found or you do not have permission.";
        }
    }
}

function getTicketsByStatus($conn, $leader_id, $statuses)
{
    $safeStatuses = array_map(function ($status) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $status) . "'";
    }, $statuses);

    $statusList = implode(',', $safeStatuses);

    $query = mysqli_query($conn, "
        SELECT tt.*, u.full_name AS employee_name
        FROM team_tickets tt
        JOIN users u ON tt.assigned_to = u.id
        WHERE tt.assigned_by = $leader_id
          AND tt.status IN ($statusList)
        ORDER BY tt.updated_at DESC, tt.created_at DESC
    ");

    $tickets = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $tickets[] = $row;
        }
    }

    return $tickets;
}

$pendingTickets = getTicketsByStatus($conn, $leader_id, ['pending']);
$progressTickets = getTicketsByStatus($conn, $leader_id, ['in_progress', 'submitted', 'revision_required']);
$doneTickets = getTicketsByStatus($conn, $leader_id, ['completed']);

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

function formatStatusLabel($status)
{
    return ucwords(str_replace('_', ' ', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasks Progress - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .progress-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-top: 25px;
    }

    .mini-stat {
      background: #ffffff;
      border-radius: 22px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .mini-stat-icon {
      width: 62px;
      height: 62px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #fff;
      flex-shrink: 0;
    }

    .pending-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .progress-icon { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
    .done-icon { background: linear-gradient(135deg, #22c55e, #4ade80); }

    .mini-stat-text h3 {
      margin: 0;
      font-size: 34px;
      color: #0f172a;
    }

    .mini-stat-text p {
      margin: 5px 0 0;
      color: #64748b;
      font-size: 15px;
    }

    .board-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
      margin-top: 28px;
      align-items: start;
    }

    .board-column {
      background: #ffffff;
      border-radius: 24px;
      padding: 22px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .column-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .column-header h3 {
      margin: 0;
      font-size: 22px;
      color: #0f172a;
    }

    .column-count {
      min-width: 34px;
      height: 34px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 700;
      color: #fff;
    }

    .pending-count { background: #f59e0b; }
    .progress-count { background: #0ea5e9; }
    .done-count { background: #22c55e; }

    .task-card-progress {
      background: #f8fbfd;
      border: 1px solid #e8eef5;
      border-radius: 18px;
      padding: 16px;
      margin-bottom: 16px;
      transition: 0.3s ease;
    }

    .task-card-progress:hover {
      transform: translateY(-3px);
    }

    .task-card-progress h4 {
      margin: 0 0 8px;
      color: #0f172a;
      font-size: 18px;
    }

    .task-card-progress p {
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 14px;
    }

    .task-meta {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 14px;
      font-size: 13px;
      color: #334155;
    }

    .task-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 14px;
    }

    .member-chip {
      background: #e0f2fe;
      color: #0369a1;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .deadline-chip {
      background: #f1f5f9;
      color: #475569;
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .priority-chip {
      padding: 7px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      text-transform: capitalize;
    }

    .high-badge { background: #fee2e2; color: #b91c1c; }
    .medium-badge { background: #fef3c7; color: #92400e; }
    .low-badge { background: #dcfce7; color: #166534; }

    .action-box {
      border-top: 1px solid #e5edf5;
      margin-top: 14px;
      padding-top: 14px;
    }

    .action-box textarea {
      width: 100%;
      min-height: 90px;
      resize: vertical;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 12px 14px;
      box-sizing: border-box;
      margin-bottom: 10px;
      background: #fff;
      outline: none;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .small-btn {
      border: none;
      border-radius: 12px;
      padding: 10px 14px;
      font-weight: 700;
      cursor: pointer;
      color: #fff;
    }

    .approve-btn { background: linear-gradient(135deg, #16a34a, #22c55e); }
    .return-btn { background: linear-gradient(135deg, #ef4444, #f97316); }

    .employee-response,
    .feedback-box,
    .attachment-box {
      margin-top: 12px;
      padding: 12px 14px;
      border-radius: 14px;
      font-size: 14px;
      line-height: 1.6;
    }

    .employee-response {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: #1e3a8a;
    }

    .feedback-box {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #9a3412;
    }

    .attachment-box {
      background: #f8fafc;
      border: 1px solid #dbe4ee;
      color: #334155;
    }

    .attachment-link {
      color: #0f766e;
      font-weight: 700;
      text-decoration: none;
    }

    .attachment-link:hover {
      text-decoration: underline;
    }

    .alert-box {
      border-radius: 16px;
      padding: 14px 18px;
      margin-top: 20px;
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

    @media (max-width: 1200px) {
      .board-grid,
      .progress-stats {
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
      <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
      <li class="active"><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
      <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
      <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Team Performance</p>
        <h4>Excellent</h4>
        <span>Review and approve task submissions</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Tasks Progress</h1>
        <p>Track pending tasks, employee progress, and completed work.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search task status, member, deadline..." disabled>
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

    <?php if ($success_message !== ''): ?>
      <div class="alert-box alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($error_message !== ''): ?>
      <div class="alert-box alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <section class="progress-stats">
      <div class="mini-stat">
        <div class="mini-stat-icon pending-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="mini-stat-text">
          <h3><?php echo count($pendingTickets); ?></h3>
          <p>Pending Tasks</p>
        </div>
      </div>

      <div class="mini-stat">
        <div class="mini-stat-icon progress-icon"><i class="fas fa-spinner"></i></div>
        <div class="mini-stat-text">
          <h3><?php echo count($progressTickets); ?></h3>
          <p>In Progress / Submitted</p>
        </div>
      </div>

      <div class="mini-stat">
        <div class="mini-stat-icon done-icon"><i class="fas fa-circle-check"></i></div>
        <div class="mini-stat-text">
          <h3><?php echo count($doneTickets); ?></h3>
          <p>Completed Tasks</p>
        </div>
      </div>
    </section>

    <section class="board-grid">

      <div class="board-column">
        <div class="column-header">
          <h3>Pending</h3>
          <span class="column-count pending-count"><?php echo count($pendingTickets); ?></span>
        </div>

        <?php if (empty($pendingTickets)): ?>
          <div class="empty-box">No pending tickets.</div>
        <?php else: ?>
          <?php foreach ($pendingTickets as $ticket): ?>
            <div class="task-card-progress">
              <h4><?php echo htmlspecialchars($ticket['title']); ?></h4>
              <p><?php echo htmlspecialchars($ticket['description']); ?></p>

              <div class="task-meta">
                <span>Status: <?php echo htmlspecialchars(formatStatusLabel($ticket['status'])); ?></span>
                <span>Created: <?php echo htmlspecialchars(date('M d, Y', strtotime($ticket['created_at']))); ?></span>
              </div>

              <div class="task-footer">
                <span class="member-chip"><?php echo htmlspecialchars($ticket['employee_name']); ?></span>
                <span class="priority-chip <?php echo priorityBadgeClass($ticket['priority']); ?>">
                  <?php echo htmlspecialchars($ticket['priority']); ?>
                </span>
                <span class="deadline-chip">
                  Due: <?php echo !empty($ticket['due_date']) ? htmlspecialchars($ticket['due_date']) : 'No deadline'; ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="board-column">
        <div class="column-header">
          <h3>In Progress / Submitted</h3>
          <span class="column-count progress-count"><?php echo count($progressTickets); ?></span>
        </div>

        <?php if (empty($progressTickets)): ?>
          <div class="empty-box">No active or submitted tickets.</div>
        <?php else: ?>
          <?php foreach ($progressTickets as $ticket): ?>
            <div class="task-card-progress">
              <h4><?php echo htmlspecialchars($ticket['title']); ?></h4>
              <p><?php echo htmlspecialchars($ticket['description']); ?></p>

              <div class="task-meta">
                <span>Status: <?php echo htmlspecialchars(formatStatusLabel($ticket['status'])); ?></span>
                <span>Updated: <?php echo htmlspecialchars(date('M d, Y', strtotime($ticket['updated_at']))); ?></span>
              </div>

              <div class="task-footer">
                <span class="member-chip"><?php echo htmlspecialchars($ticket['employee_name']); ?></span>
                <span class="priority-chip <?php echo priorityBadgeClass($ticket['priority']); ?>">
                  <?php echo htmlspecialchars($ticket['priority']); ?>
                </span>
                <span class="deadline-chip">
                  Due: <?php echo !empty($ticket['due_date']) ? htmlspecialchars($ticket['due_date']) : 'No deadline'; ?>
                </span>
              </div>

              <?php if (!empty($ticket['employee_response'])): ?>
                <div class="employee-response">
                  <strong>Employee Submission:</strong><br>
                  <?php echo nl2br(htmlspecialchars($ticket['employee_response'])); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($ticket['attachment_path'])): ?>
                <div class="attachment-box">
                  <strong>Uploaded File:</strong><br>
                  <a class="attachment-link" href="<?php echo htmlspecialchars($ticket['attachment_path']); ?>" target="_blank">
                    <i class="fas fa-file-arrow-down"></i>
                    <?php echo htmlspecialchars($ticket['attachment_name'] ?: 'Open Attachment'); ?>
                  </a>
                </div>
              <?php endif; ?>

              <?php if (!empty($ticket['leader_feedback']) && $ticket['status'] === 'revision_required'): ?>
                <div class="feedback-box">
                  <strong>Leader Feedback:</strong><br>
                  <?php echo nl2br(htmlspecialchars($ticket['leader_feedback'])); ?>
                </div>
              <?php endif; ?>

              <?php if ($ticket['status'] === 'submitted'): ?>
                <div class="action-box">
                  <form method="POST">
                    <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                    <textarea name="leader_feedback" placeholder="Write feedback only if you want to return the ticket for revision..."></textarea>

                    <div class="action-buttons">
                      <button type="submit" name="approve_ticket" class="small-btn approve-btn">
                        <i class="fas fa-check"></i> Approve + Send Appreciation
                      </button>

                      <button type="submit" name="return_ticket" class="small-btn return-btn">
                        <i class="fas fa-rotate-left"></i> Return for Revision
                      </button>
                    </div>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="board-column">
        <div class="column-header">
          <h3>Completed</h3>
          <span class="column-count done-count"><?php echo count($doneTickets); ?></span>
        </div>

        <?php if (empty($doneTickets)): ?>
          <div class="empty-box">No completed tickets yet.</div>
        <?php else: ?>
          <?php foreach ($doneTickets as $ticket): ?>
            <div class="task-card-progress">
              <h4><?php echo htmlspecialchars($ticket['title']); ?></h4>
              <p><?php echo htmlspecialchars($ticket['description']); ?></p>

              <div class="task-meta">
                <span>Status: <?php echo htmlspecialchars(formatStatusLabel($ticket['status'])); ?></span>
                <span>Completed: <?php echo htmlspecialchars(date('M d, Y', strtotime($ticket['updated_at']))); ?></span>
              </div>

              <div class="task-footer">
                <span class="member-chip"><?php echo htmlspecialchars($ticket['employee_name']); ?></span>
                <span class="priority-chip <?php echo priorityBadgeClass($ticket['priority']); ?>">
                  <?php echo htmlspecialchars($ticket['priority']); ?>
                </span>
                <span class="deadline-chip">
                  Due: <?php echo !empty($ticket['due_date']) ? htmlspecialchars($ticket['due_date']) : 'No deadline'; ?>
                </span>
              </div>

              <?php if (!empty($ticket['employee_response'])): ?>
                <div class="employee-response">
                  <strong>Employee Submission:</strong><br>
                  <?php echo nl2br(htmlspecialchars($ticket['employee_response'])); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($ticket['attachment_path'])): ?>
                <div class="attachment-box">
                  <strong>Uploaded File:</strong><br>
                  <a class="attachment-link" href="<?php echo htmlspecialchars($ticket['attachment_path']); ?>" target="_blank">
                    <i class="fas fa-file-arrow-down"></i>
                    <?php echo htmlspecialchars($ticket['attachment_name'] ?: 'Open Attachment'); ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </section>

  </main>
</div>

</body>
</html>