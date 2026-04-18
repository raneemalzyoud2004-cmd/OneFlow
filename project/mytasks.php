<?php
session_start();
require_once 'config.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$employee_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Employee';
$first_letter = strtoupper(substr(trim($full_name), 0, 1));

$success_message = '';
$error_message = '';

$upload_dir = __DIR__ . '/uploads/';
$upload_url = 'uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int) ($_POST['ticket_id'] ?? 0);

    if ($ticket_id > 0) {
        $ticketCheck = mysqli_query($conn, "
            SELECT id, status, attachment_name, attachment_path
            FROM team_tickets
            WHERE id = $ticket_id AND assigned_to = $employee_id
            LIMIT 1
        ");

        if ($ticketCheck && mysqli_num_rows($ticketCheck) === 1) {
            $ticketData = mysqli_fetch_assoc($ticketCheck);

            if (isset($_POST['start_task'])) {
                if ($ticketData['status'] === 'pending' || $ticketData['status'] === 'revision_required') {
                    $updateQuery = mysqli_query($conn, "
                        UPDATE team_tickets
                        SET status = 'in_progress'
                        WHERE id = $ticket_id AND assigned_to = $employee_id
                    ");

                    if ($updateQuery) {
                        $success_message = "Task started successfully.";
                    } else {
                        $error_message = "Failed to start the task.";
                    }
                }
            }

            if (isset($_POST['submit_task'])) {
                $employee_response = trim($_POST['employee_response'] ?? '');

                if ($employee_response === '') {
                    $error_message = "Please write your task solution before submitting.";
                } else {
                    $safeResponse = mysqli_real_escape_string($conn, $employee_response);

                    $attachment_name_sql = "attachment_name";
                    $attachment_path_sql = "attachment_path";
                    $attachment_name_value = $ticketData['attachment_name'] ? "'" . mysqli_real_escape_string($conn, $ticketData['attachment_name']) . "'" : "NULL";
                    $attachment_path_value = $ticketData['attachment_path'] ? "'" . mysqli_real_escape_string($conn, $ticketData['attachment_path']) . "'" : "NULL";

                    if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $file = $_FILES['task_file'];

                        if ($file['error'] !== UPLOAD_ERR_OK) {
                            $error_message = "File upload failed. Please try again.";
                        } else {
                            $original_name = basename($file['name']);
$file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
$file_size = (int) $file['size'];

if ($file_size > 20 * 1024 * 1024) {
    $error_message = "File size must be 20MB or less.";
} else {
                                $new_file_name = 'task_' . $ticket_id . '_' . time() . '.' . $file_extension;
                                $destination = $upload_dir . $new_file_name;

                                if (move_uploaded_file($file['tmp_name'], $destination)) {
                                    $attachment_name_value = "'" . mysqli_real_escape_string($conn, $original_name) . "'";
                                    $attachment_path_value = "'" . mysqli_real_escape_string($conn, $upload_url . $new_file_name) . "'";
                                } else {
                                    $error_message = "Failed to save uploaded file.";
                                }
                            }
                        }
                    }

                    if ($error_message === '') {
                        $updateQuery = mysqli_query($conn, "
                            UPDATE team_tickets
                            SET status = 'submitted',
                                employee_response = '$safeResponse',
                                $attachment_name_sql = $attachment_name_value,
                                $attachment_path_sql = $attachment_path_value
                            WHERE id = $ticket_id AND assigned_to = $employee_id
                        ");

                        if ($updateQuery) {
                            $success_message = "Task submitted to the team leader successfully.";
                        } else {
                            $error_message = "Failed to submit the task.";
                        }
                    }
                }
            }
        } else {
            $error_message = "Task not found or access denied.";
        }
    }
}

$tasks = [];
$tasksQuery = mysqli_query($conn, "
    SELECT tt.*, u.full_name AS leader_name
    FROM team_tickets tt
    JOIN users u ON tt.assigned_by = u.id
    WHERE tt.assigned_to = $employee_id
    ORDER BY
      CASE
        WHEN tt.status = 'submitted' THEN 1
        WHEN tt.status = 'in_progress' THEN 2
        WHEN tt.status = 'revision_required' THEN 3
        WHEN tt.status = 'pending' THEN 4
        WHEN tt.status = 'completed' THEN 5
        ELSE 6
      END,
      tt.updated_at DESC,
      tt.created_at DESC
");

if ($tasksQuery) {
    while ($row = mysqli_fetch_assoc($tasksQuery)) {
        $messageQuery = mysqli_query($conn, "
            SELECT message
            FROM ticket_appreciation_messages
            WHERE ticket_id = " . (int) $row['id'] . "
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $row['appreciation_message'] = '';
        if ($messageQuery && mysqli_num_rows($messageQuery) > 0) {
            $messageData = mysqli_fetch_assoc($messageQuery);
            $row['appreciation_message'] = $messageData['message'];
        }

        $tasks[] = $row;
    }
}

$totalTasks = count($tasks);
$inProgressCount = 0;
$completedCount = 0;

foreach ($tasks as $task) {
    if ($task['status'] === 'in_progress' || $task['status'] === 'submitted' || $task['status'] === 'revision_required') {
        $inProgressCount++;
    }
    if ($task['status'] === 'completed') {
        $completedCount++;
    }
}

function formatStatusLabel($status)
{
    return ucwords(str_replace('_', ' ', $status));
}

function statusClass($status)
{
    if ($status === 'completed') {
        return 'approved';
    }
    if ($status === 'pending' || $status === 'revision_required') {
        return 'rejected';
    }
    return 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tasks - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .ticket-wrapper {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .ticket-card {
      background: #ffffff;
      border-radius: 22px;
      padding: 22px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      border: 1px solid #eef2f7;
    }

    .ticket-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 14px;
      flex-wrap: wrap;
      margin-bottom: 12px;
    }

    .ticket-top h3 {
      margin: 0;
      color: #0f172a;
      font-size: 22px;
    }

    .ticket-description {
      color: #64748b;
      line-height: 1.7;
      margin-bottom: 14px;
      font-size: 15px;
    }

    .ticket-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 14px;
    }

    .meta-chip {
      background: #f1f5f9;
      color: #334155;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .priority-chip {
      text-transform: capitalize;
    }

    .priority-high {
      background: #fee2e2;
      color: #b91c1c;
    }

    .priority-medium {
      background: #fef3c7;
      color: #92400e;
    }

    .priority-low {
      background: #dcfce7;
      color: #166534;
    }

    .submission-box,
    .feedback-box,
    .appreciation-box,
    .attachment-box {
      margin-top: 14px;
      padding: 14px 16px;
      border-radius: 16px;
      font-size: 14px;
      line-height: 1.7;
    }

    .submission-box {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: #1e3a8a;
    }

    .feedback-box {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      color: #9a3412;
    }

    .appreciation-box {
      background: #ecfdf5;
      border: 1px solid #bbf7d0;
      color: #166534;
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

    .task-form-box {
      margin-top: 14px;
      border-top: 1px solid #e5edf5;
      padding-top: 14px;
    }

    .task-form-box textarea {
      width: 100%;
      min-height: 100px;
      resize: vertical;
      border: 1px solid #dbe4ee;
      border-radius: 14px;
      padding: 12px 14px;
      box-sizing: border-box;
      margin-bottom: 12px;
      outline: none;
      background: #f8fbfd;
    }

    .task-form-box textarea:focus {
      border-color: #19c2c9;
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(25, 194, 201, 0.10);
    }

    .file-upload-area {
      margin-bottom: 12px;
    }

    .custom-file-label {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #f0fdfa;
      color: #0f766e;
      border: 1px dashed #14b8a6;
      border-radius: 14px;
      padding: 12px 16px;
      cursor: pointer;
      font-weight: 700;
      transition: 0.3s;
    }

    .custom-file-label:hover {
      background: #ccfbf1;
    }

    .custom-file-label input[type="file"] {
      display: none;
    }

    .task-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .small-btn {
      border: none;
      border-radius: 12px;
      padding: 11px 15px;
      font-weight: 700;
      cursor: pointer;
      color: #fff;
    }

    .start-btn {
      background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    }

    .submit-btn {
      background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .alert-box {
      border-radius: 16px;
      padding: 14px 18px;
      margin-bottom: 20px;
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
      background: #ffffff;
      border-radius: 22px;
      padding: 24px;
      text-align: center;
      color: #64748b;
      border: 1px dashed #dbe4ee;
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
      <li><a href="dashboardemployee.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li class="active"><a href="mytasks.php"><i class="fas fa-list-check"></i> My Tasks</a></li>
      <li><a href="myattendance.php"><i class="fas fa-calendar-check"></i> My Attendance</a></li>
      <li><a href="myschedule.php"><i class="fas fa-clock"></i> My Schedule</a></li>
      <li><a href="notificationsemployee.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="settingsemployee.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>Performance Status</p>
        <h4>Excellent</h4>
        <span>Track and submit your team tickets</span>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1>My Tasks</h1>
        <p>View your assigned tickets, work on them, and submit them to your team leader.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search tasks..." disabled>
        </div>

        <div class="icon-btn notification-bell">
          <i class="fas fa-bell"></i>
          <span class="notif-count">2</span>
        </div>

        <div class="admin-avatar"><?php echo htmlspecialchars($first_letter); ?></div>
        <div>
          <h4><?php echo htmlspecialchars($full_name); ?></h4>
          <span>Employee</span>
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

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-list-check"></i></div>
        <div class="card-info">
          <h3><?php echo $totalTasks; ?></h3>
          <p>Total Tasks</p>
          <span>Assigned to you</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-spinner"></i></div>
        <div class="card-info">
          <h3><?php echo $inProgressCount; ?></h3>
          <p>Active Tasks</p>
          <span>In progress or waiting review</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-check-circle"></i></div>
        <div class="card-info">
          <h3><?php echo $completedCount; ?></h3>
          <p>Completed</p>
          <span>Approved by team leader</span>
        </div>
      </div>
    </section>

    <section class="ticket-wrapper">
      <?php if (empty($tasks)): ?>
        <div class="empty-box">
          You do not have any assigned tasks yet.
        </div>
      <?php else: ?>
        <?php foreach ($tasks as $task): ?>
          <div class="ticket-card">
            <div class="ticket-top">
              <div>
                <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                <p class="ticket-description"><?php echo htmlspecialchars($task['description']); ?></p>
              </div>

              <span class="status <?php echo statusClass($task['status']); ?>">
                <?php echo htmlspecialchars(formatStatusLabel($task['status'])); ?>
              </span>
            </div>

            <div class="ticket-meta">
              <span class="meta-chip">Leader: <?php echo htmlspecialchars($task['leader_name']); ?></span>
              <span class="meta-chip priority-chip priority-<?php echo htmlspecialchars($task['priority']); ?>">
                Priority: <?php echo htmlspecialchars($task['priority']); ?>
              </span>
              <span class="meta-chip">
                Due: <?php echo !empty($task['due_date']) ? htmlspecialchars($task['due_date']) : 'No deadline'; ?>
              </span>
            </div>

            <?php if (!empty($task['employee_response'])): ?>
              <div class="submission-box">
                <strong>Your Submission:</strong><br>
                <?php echo nl2br(htmlspecialchars($task['employee_response'])); ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($task['attachment_path'])): ?>
              <div class="attachment-box">
                <strong>Uploaded File:</strong><br>
                <a class="attachment-link" href="<?php echo htmlspecialchars($task['attachment_path']); ?>" target="_blank">
                  <i class="fas fa-file-arrow-down"></i>
                  <?php echo htmlspecialchars($task['attachment_name'] ?: 'Open Attachment'); ?>
                </a>
              </div>
            <?php endif; ?>

            <?php if (!empty($task['leader_feedback']) && $task['status'] === 'revision_required'): ?>
              <div class="feedback-box">
                <strong>Leader Feedback:</strong><br>
                <?php echo nl2br(htmlspecialchars($task['leader_feedback'])); ?>
              </div>
            <?php endif; ?>

            <?php if ($task['status'] === 'completed' && !empty($task['appreciation_message'])): ?>
              <div class="appreciation-box">
                <strong>Leader Appreciation:</strong><br>
                <?php echo nl2br(htmlspecialchars($task['appreciation_message'])); ?>
              </div>
            <?php endif; ?>

            <?php if ($task['status'] === 'pending' || $task['status'] === 'revision_required' || $task['status'] === 'in_progress'): ?>
              <div class="task-form-box">

                <?php if ($task['status'] === 'pending' || $task['status'] === 'revision_required'): ?>
                  <form method="POST" style="margin-bottom: 12px;">
                    <input type="hidden" name="ticket_id" value="<?php echo (int) $task['id']; ?>">
                    <button type="submit" name="start_task" class="small-btn start-btn">
                      <i class="fas fa-play"></i> Start Task
                    </button>
                  </form>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="ticket_id" value="<?php echo (int) $task['id']; ?>">

                  <textarea name="employee_response" placeholder="Write your completed work, explanation, or final solution here..."><?php echo htmlspecialchars($task['employee_response'] ?? ''); ?></textarea>

               <div class="file-upload-area">

  <div class="drag-drop-box" id="dropArea">
    <i class="fas fa-cloud-upload-alt"></i>
    <p>Drag & Drop file here or click to upload</p>
    <input type="file" name="task_file" id="fileInput" hidden>
  </div>

  <div class="selected-file-name">No file selected</div>

  <div class="file-preview" id="filePreview"></div>

</div>
                  <div class="task-actions">
                    <button type="submit" name="submit_task" class="small-btn submit-btn">
                      <i class="fas fa-paper-plane"></i> Submit to Team Leader
                    </button>
                  </div>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</div>
<script>
function showSelectedFileName(input) {
  const fileNameBox = input.closest('.file-upload-area').querySelector('.selected-file-name');

  if (input.files.length > 0) {
    fileNameBox.textContent = input.files[0].name;
  } else {
    fileNameBox.textContent = "No file selected";
  }
}
</script>
<script>
const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const fileNameBox = document.querySelector(".selected-file-name");
const previewBox = document.getElementById("filePreview");

dropArea.addEventListener("click", () => fileInput.click());

dropArea.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropArea.style.background = "#ccfbf1";
});

dropArea.addEventListener("dragleave", () => {
  dropArea.style.background = "#f0fdfa";
});

dropArea.addEventListener("drop", (e) => {
  e.preventDefault();
  fileInput.files = e.dataTransfer.files;
  handleFile(fileInput.files[0]);
});

fileInput.addEventListener("change", () => {
  handleFile(fileInput.files[0]);
});

function handleFile(file) {
  if (!file) return;

  fileNameBox.textContent = file.name;
  previewBox.innerHTML = "";

  if (file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewBox.innerHTML = '<img src="' + e.target.result + '" />';
    };
    reader.readAsDataURL(file);
  }
}
</script>


</body>
</html>