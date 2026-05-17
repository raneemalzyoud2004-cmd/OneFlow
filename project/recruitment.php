<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$successMessage = "";

if (isset($_POST['add_applicant'])) {
    $fullName = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $position = mysqli_real_escape_string($conn, $_POST['position_applied']);
    $cvFile = mysqli_real_escape_string($conn, $_POST['cv_file']);
    $interviewDate = !empty($_POST['interview_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['interview_date']) . "'" : "NULL";
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $insert = mysqli_query($conn, "
        INSERT INTO applicants 
        (full_name, email, phone, position_applied, cv_file, interview_date, status, notes)
        VALUES 
        ('$fullName', '$email', '$phone', '$position', '$cvFile', $interviewDate, '$status', '$notes')
    ");

    if ($insert) {
        $successMessage = "Applicant added successfully.";
    } else {
        $successMessage = "Failed to add applicant.";
    }
}

if (isset($_POST['schedule_interview'])) {
    $applicantId = intval($_POST['applicant_id']);
    $interviewDate = mysqli_real_escape_string($conn, $_POST['interview_date']);
    $interviewTime = mysqli_real_escape_string($conn, $_POST['interview_time']);
    $interviewNotes = mysqli_real_escape_string($conn, $_POST['interview_notes']);
    $interviewDateTime = $interviewDate . " " . $interviewTime . ":00";

    $update = mysqli_query($conn, "
        UPDATE applicants
        SET interview_date='$interviewDateTime', status='Shortlisted', notes=CONCAT(IFNULL(notes,''), '\nInterview Notes: $interviewNotes')
        WHERE id=$applicantId
    ");

    if ($update) {
        $successMessage = "Interview scheduled successfully.";
    } else {
        $successMessage = "Failed to schedule interview.";
    }
}

if (isset($_POST['update_status'])) {
    $applicantId = intval($_POST['applicant_id']);
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);

    if ($newStatus == "Hired") {
        $applicantQuery = mysqli_query($conn, "SELECT * FROM applicants WHERE id=$applicantId");

        if ($applicantQuery && mysqli_num_rows($applicantQuery) > 0) {
            $applicant = mysqli_fetch_assoc($applicantQuery);

            $fullName = mysqli_real_escape_string($conn, $applicant['full_name']);
            $email = mysqli_real_escape_string($conn, $applicant['email']);

            $usernameBase = strtolower(explode("@", $email)[0]);
            $username = mysqli_real_escape_string($conn, $usernameBase);

            $temporaryPassword = "Employee@123";
            $hashedPassword = password_hash($temporaryPassword, PASSWORD_DEFAULT);

            $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

            if ($checkUser && mysqli_num_rows($checkUser) > 0) {
                mysqli_query($conn, "UPDATE applicants SET status='Hired' WHERE id=$applicantId");
                $successMessage = "Applicant marked as hired, but employee account already exists.";
            } else {
                $insertUser = mysqli_query($conn, "
                    INSERT INTO users 
                    (full_name, username, email, password, role, account_status, salary, failed_attempts, is_blocked)
                    VALUES
                    ('$fullName', '$username', '$email', '$hashedPassword', 'employee', 'active', 0, 0, 0)
                ");

                if ($insertUser) {
                    mysqli_query($conn, "UPDATE applicants SET status='Hired' WHERE id=$applicantId");
                    $successMessage = "Applicant hired successfully. Employee account created. Temporary password: Employee@123";
                } else {
                    $successMessage = "Failed to create employee account.";
                }
            }
        } else {
            $successMessage = "Applicant not found.";
        }
    } else {
        $update = mysqli_query($conn, "
            UPDATE applicants 
            SET status='$newStatus' 
            WHERE id=$applicantId
        ");

        if ($update) {
            $successMessage = "Applicant status updated successfully.";
        } else {
            $successMessage = "Failed to update applicant status.";
        }
    }
}

$totalApplicants = 0;
$pendingApplicants = 0;
$shortlistedApplicants = 0;
$hiredApplicants = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM applicants");
if ($result) $totalApplicants = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM applicants WHERE status='Pending'");
if ($result) $pendingApplicants = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM applicants WHERE status='Shortlisted'");
if ($result) $shortlistedApplicants = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM applicants WHERE status='Hired'");
if ($result) $hiredApplicants = mysqli_fetch_assoc($result)['total'];

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $applicants = mysqli_query($conn, "
        SELECT * FROM applicants
        WHERE full_name LIKE '%$search%'
        OR email LIKE '%$search%'
        OR phone LIKE '%$search%'
        OR position_applied LIKE '%$search%'
        OR status LIKE '%$search%'
        ORDER BY created_at DESC
    ");
} else {
    $applicants = mysqli_query($conn, "
        SELECT * FROM applicants
        ORDER BY created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recruitment - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .search-box form {
      display: flex;
      align-items: center;
      width: 100%;
    }

    .search-box button {
      border: none;
      background: transparent;
      cursor: pointer;
      color: #0D1E4C;
      font-size: 15px;
    }

    .success-message {
      background: #e7f8ee;
      color: #166534;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    .action-form {
      display: inline-block;
      margin-right: 6px;
      margin-bottom: 6px;
    }

    .action-btn {
      padding: 8px 12px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 700;
      font-size: 13px;
      display: inline-block;
      text-decoration: none;
      margin-bottom: 5px;
    }

    .shortlist { background: #dbeafe; color: #1e3a8a; }
    .hire { background: #dcfce7; color: #166534; }
    .reject { background: #fee2e2; color: #991b1b; }
    .cv-view { background: #e0f2fe; color: #075985; }
    .cv-download { background: #fef3c7; color: #92400e; }
    .schedule { background: #ede9fe; color: #5b21b6; }

    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(13, 30, 76, 0.55);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-box {
      width: 560px;
      max-width: 100%;
      background: #fff;
      border-radius: 22px;
      padding: 26px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-box h2 {
      color: #0D1E4C;
      margin-bottom: 18px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      color: #0D1E4C;
      margin-bottom: 7px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      outline: none;
      font-family: inherit;
    }

    .form-group textarea {
      min-height: 85px;
      resize: vertical;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 18px;
    }

    .save-btn {
      background: #0D1E4C;
      color: white;
      padding: 11px 18px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      font-weight: 700;
    }

    .cancel-btn {
      background: #E5C9D7;
      color: #0D1E4C;
      padding: 11px 18px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      font-weight: 700;
    }

    .small-text {
      color: #6b7280;
      font-size: 13px;
      font-weight: 600;
      white-space: pre-line;
    }

    .actions-wrap {
      min-width: 210px;
    }

    .cv-actions {
      min-width: 145px;
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
      <p class="admin-role">HR Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li class="active"><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
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
        <h1>Recruitment</h1>
        <p>Track applicants, open positions, and hiring progress.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <form method="GET" action="recruitment.php">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              name="search" 
              placeholder="Search applicants..."
              value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit"><i class="fas fa-arrow-right"></i></button>
          </form>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>HR Manager</span>
          </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </header>

    <?php if (!empty($successMessage)): ?>
      <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>Recruitment Hub</h2>
        <p>Manage applicants, review candidate profiles, and follow hiring decisions from one place.</p>
      </div>

      <div class="hero-actions">
        <button type="button" class="hero-btn primary-btn" onclick="openAddModal()">
          <i class="fas fa-user-plus"></i> Add Applicant
        </button>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-user-plus"></i></div>
        <div class="card-info">
          <h3><?php echo $totalApplicants; ?></h3>
          <p>Total Applicants</p>
          <span>In recruitment pipeline</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $pendingApplicants; ?></h3>
          <p>Pending</p>
          <span>Waiting for review</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-clipboard-check"></i></div>
        <div class="card-info">
          <h3><?php echo $shortlistedApplicants; ?></h3>
          <p>Shortlisted</p>
          <span>Interview stage</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
        <div class="card-info">
          <h3><?php echo $hiredApplicants; ?></h3>
          <p>Hired</p>
          <span>Employee account created</span>
        </div>
      </div>
    </section>

    <section class="panel">
      <div class="panel-header">
        <h2>Applicants List</h2>
        <a href="recruitment.php">View All</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Position</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Interview</th>
              <th>CV</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($applicants && mysqli_num_rows($applicants) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($applicants)): ?>
                <tr>
                  <td>
                    <?php echo htmlspecialchars($row['full_name']); ?>
                    <br>
                    <span class="small-text"><?php echo htmlspecialchars($row['notes'] ?: 'No notes'); ?></span>
                  </td>

                  <td><?php echo htmlspecialchars($row['position_applied']); ?></td>
                  <td><?php echo htmlspecialchars($row['phone'] ?: 'No phone'); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['interview_date'] ?: 'Not scheduled'); ?></td>

                  <td class="cv-actions">
                    <?php if (!empty($row['cv_file'])): ?>
                      <a href="<?php echo htmlspecialchars($row['cv_file']); ?>" target="_blank" class="action-btn cv-view">
                        View CV
                      </a>
                      <a href="<?php echo htmlspecialchars($row['cv_file']); ?>" download class="action-btn cv-download">
                        Download
                      </a>
                    <?php else: ?>
                      No CV
                    <?php endif; ?>
                  </td>

                  <td>
                    <?php
                      $statusClass = "pending";
                      if ($row['status'] == "Shortlisted" || $row['status'] == "Hired") $statusClass = "approved";
                      if ($row['status'] == "Rejected") $statusClass = "rejected";
                    ?>
                    <span class="status <?php echo $statusClass; ?>">
                      <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                  </td>

                  <td class="actions-wrap">
                    <?php if ($row['status'] != "Hired" && $row['status'] != "Rejected"): ?>
                      <button 
                        type="button" 
                        class="action-btn schedule"
                        onclick="openScheduleModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>')"
                      >
                        Schedule
                      </button>
                    <?php endif; ?>

                    <?php if ($row['status'] != "Shortlisted" && $row['status'] != "Hired" && $row['status'] != "Rejected"): ?>
                      <form method="POST" action="recruitment.php" class="action-form">
                        <input type="hidden" name="applicant_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="status" value="Shortlisted">
                        <button type="submit" name="update_status" class="action-btn shortlist">
                          Shortlist
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($row['status'] != "Hired" && $row['status'] != "Rejected"): ?>
                      <form method="POST" action="recruitment.php" class="action-form">
                        <input type="hidden" name="applicant_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="status" value="Hired">
                        <button 
                          type="submit" 
                          name="update_status" 
                          class="action-btn hire"
                          onclick="return confirm('Hire this applicant and create an employee account?');"
                        >
                          Hire
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($row['status'] != "Rejected" && $row['status'] != "Hired"): ?>
                      <form method="POST" action="recruitment.php" class="action-form">
                        <input type="hidden" name="applicant_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="status" value="Rejected">
                        <button 
                          type="submit" 
                          name="update_status" 
                          class="action-btn reject"
                          onclick="return confirm('Reject this applicant?');"
                        >
                          Reject
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8">No applicants found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <h2>Add New Applicant</h2>

    <form method="POST" action="recruitment.php">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone">
      </div>

      <div class="form-group">
        <label>Position Applied</label>
        <input type="text" name="position_applied" required>
      </div>

      <div class="form-group">
        <label>CV File Name / Link</label>
        <input type="text" name="cv_file" placeholder="uploads/cv/example_cv.pdf">
      </div>

      <div class="form-group">
        <label>Interview Date</label>
        <input type="date" name="interview_date">
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status" required>
          <option value="Pending">Pending</option>
          <option value="Shortlisted">Shortlisted</option>
          <option value="Rejected">Rejected</option>
          <option value="Hired">Hired</option>
        </select>
      </div>

      <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" placeholder="Write applicant notes..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
        <button type="submit" name="add_applicant" class="save-btn">Add Applicant</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="scheduleModal">
  <div class="modal-box">
    <h2>Schedule Interview</h2>
    <p id="scheduleApplicantName" class="small-text" style="margin-bottom: 18px;"></p>

    <form method="POST" action="recruitment.php">
      <input type="hidden" name="applicant_id" id="scheduleApplicantId">

      <div class="form-group">
        <label>Interview Date</label>
        <input type="date" name="interview_date" required>
      </div>

      <div class="form-group">
        <label>Interview Time</label>
        <input type="time" name="interview_time" required>
      </div>

      <div class="form-group">
        <label>Interview Notes</label>
        <textarea name="interview_notes" placeholder="Example: Online interview, bring portfolio, technical questions..."></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="cancel-btn" onclick="closeScheduleModal()">Cancel</button>
        <button type="submit" name="schedule_interview" class="save-btn">Save Interview</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById("addModal").style.display = "flex";
}

function closeAddModal() {
  document.getElementById("addModal").style.display = "none";
}

function openScheduleModal(id, name) {
  document.getElementById("scheduleApplicantId").value = id;
  document.getElementById("scheduleApplicantName").innerText = "Applicant: " + name;
  document.getElementById("scheduleModal").style.display = "flex";
}

function closeScheduleModal() {
  document.getElementById("scheduleModal").style.display = "none";
}

window.onclick = function(event) {
  const addModal = document.getElementById("addModal");
  const scheduleModal = document.getElementById("scheduleModal");

  if (event.target === addModal) {
    closeAddModal();
  }

  if (event.target === scheduleModal) {
    closeScheduleModal();
  }
}
</script>

</body>
</html>