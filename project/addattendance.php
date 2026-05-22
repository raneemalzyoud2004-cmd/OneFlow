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
$error = "";

$employees = mysqli_query($conn, "
    SELECT id, full_name, email
    FROM users
    WHERE role='employee' AND account_status='active'
    ORDER BY full_name ASC
");

if (isset($_POST['add_attendance'])) {
    $employee_id = intval($_POST['employee_id']);
    $attendance_date = mysqli_real_escape_string($conn, $_POST['attendance_date']);
    $check_in = mysqli_real_escape_string($conn, $_POST['check_in']);
    $check_out = mysqli_real_escape_string($conn, $_POST['check_out']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    if ($employee_id == 0 || empty($attendance_date) || empty($status)) {
        $error = "Please fill all required fields.";
    } else {
        if (!empty($check_in) && $check_in > "09:00" && $status == "Present") {
            $status = "Late";
            $notes = trim($notes . " Auto marked as late because check-in is after 09:00 AM.");
        }

        $checkDuplicate = mysqli_query($conn, "
            SELECT id FROM attendance
            WHERE employee_id='$employee_id'
            AND attendance_date='$attendance_date'
        ");

        if (mysqli_num_rows($checkDuplicate) > 0) {
            $error = "Attendance for this employee already exists on this date.";
        } else {
            $insert = mysqli_query($conn, "
                INSERT INTO attendance
                (employee_id, attendance_date, check_in, check_out, status, notes)
                VALUES
                (
                    '$employee_id',
                    '$attendance_date',
                    " . (!empty($check_in) ? "'$check_in'" : "NULL") . ",
                    " . (!empty($check_out) ? "'$check_out'" : "NULL") . ",
                    '$status',
                    '$notes'
                )
            ");

            if ($insert) {
                header("Location: attendance.php");
                exit();
            } else {
                $error = "Failed to add attendance record.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Attendance - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .add-container {
      background: #ffffff;
      padding: 30px;
      border-radius: 20px;
      margin-top: 25px;
      box-shadow: 0 10px 25px rgba(13,30,76,0.08);
      border: 1px solid rgba(13,30,76,0.08);
      max-width: 900px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 18px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      margin-bottom: 8px;
      color: #0D1E4C;
      font-weight: 700;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      padding: 13px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      outline: none;
      font-size: 15px;
    }

    .form-group textarea {
      min-height: 110px;
      resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #83A6CE;
      box-shadow: 0 0 0 3px rgba(131,166,206,0.25);
    }

    .full-width {
      grid-column: 1 / -1;
    }

    .form-actions {
      margin-top: 25px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .save-btn {
      background: #0D1E4C;
      color: white;
      border: none;
      padding: 13px 20px;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 800;
    }

    .cancel-btn {
      background: #E5C9D7;
      color: #0D1E4C;
      padding: 13px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
    }

    .error-message {
      background: #fee2e2;
      color: #991b1b;
      padding: 14px;
      border-radius: 12px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    @media(max-width:900px) {
      .form-grid {
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
      <p class="admin-role">HR Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="hrdashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li class="active"><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
      <li><a href="leaverequests.php"><i class="fas fa-file-circle-check"></i> Leave Requests</a></li>
      <li><a href="recruitment.php"><i class="fas fa-user-plus"></i> Recruitment</a></li>
      <li><a href="notificationshr.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
      <li><a href="settingshr.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Add Attendance</h1>
        <p>Create a new attendance record for an employee.</p>
      </div>

      <div class="topbar-right">
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

    <section class="add-container">

      <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">

        <div class="form-grid">

          <div class="form-group full-width">
            <label>Employee *</label>
            <select name="employee_id" required>
              <option value="">Select Employee</option>
              <?php if ($employees && mysqli_num_rows($employees) > 0): ?>
                <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                  <option value="<?php echo $emp['id']; ?>">
                    <?php echo htmlspecialchars($emp['full_name']); ?>
                    <?php echo !empty($emp['email']) ? " - " . htmlspecialchars($emp['email']) : ""; ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
          </div>

          <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
              <option value="">Select Status</option>
              <option value="Present">Present</option>
              <option value="Absent">Absent</option>
              <option value="Late">Late</option>
              <option value="On Leave">On Leave</option>
            </select>
          </div>

          <div class="form-group">
            <label>Check-In</label>
            <input type="time" name="check_in">
          </div>

          <div class="form-group">
            <label>Check-Out</label>
            <input type="time" name="check_out">
          </div>

          <div class="form-group full-width">
            <label>Notes</label>
            <textarea name="notes" placeholder="Write any notes about this attendance record..."></textarea>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" name="add_attendance" class="save-btn">
            <i class="fas fa-save"></i> Save Attendance
          </button>

          <a href="attendance.php" class="cancel-btn">
            <i class="fas fa-arrow-left"></i> Cancel
          </a>
        </div>

      </form>

    </section>

  </main>

</div>

</body>
</html>