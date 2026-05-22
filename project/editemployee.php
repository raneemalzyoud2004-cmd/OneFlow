<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hr') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$user_id = intval($_GET['id']);
$success = "";
$error = "";

/* Update user */
if (isset($_POST['update_user'])) {
    $new_full_name = trim($_POST['full_name']);
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_role = trim($_POST['role']);
    $new_salary = trim($_POST['salary']);
    $new_status = trim($_POST['account_status']);
    $new_blocked = intval($_POST['is_blocked']);

    if ($new_full_name == "" || $new_username == "" || $new_role == "" || $new_status == "") {
        $error = "Please fill all required fields.";
    } else {
        $update = "UPDATE users 
                   SET full_name = ?, username = ?, email = ?, role = ?, salary = ?, account_status = ?, is_blocked = ?
                   WHERE id = ?";

        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssdssi",
            $new_full_name,
            $new_username,
            $new_email,
            $new_role,
            $new_salary,
            $new_status,
            $new_blocked,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: employeeprofile.php?id=" . $user_id . "&updated=1");
            exit();
        } else {
            $error = "Update failed. Please try again.";
        }
    }
}

/* Get user data */
$query = "SELECT id, full_name, username, email, role, account_status, salary, is_blocked 
          FROM users 
          WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "User not found.";
    exit();
}

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .edit-container {
      background: #ffffff;
      padding: 28px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(13, 30, 76, 0.08);
      border: 1px solid rgba(13, 30, 76, 0.08);
      margin-top: 25px;
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
      color: #0D1E4C;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .form-group input,
    .form-group select {
      padding: 13px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      font-size: 15px;
      outline: none;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #83A6CE;
      box-shadow: 0 0 0 3px rgba(131, 166, 206, 0.25);
    }

    .form-actions {
      margin-top: 25px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .save-btn {
      background: #0D1E4C;
      color: #ffffff;
      border: none;
      padding: 13px 20px;
      border-radius: 12px;
      font-weight: 800;
      cursor: pointer;
      text-decoration: none;
    }

    .cancel-btn {
      background: #E5C9D7;
      color: #0D1E4C;
      padding: 13px 20px;
      border-radius: 12px;
      font-weight: 800;
      text-decoration: none;
    }

    .message-error {
      background: #fee2e2;
      color: #991b1b;
      padding: 13px;
      border-radius: 12px;
      margin-bottom: 15px;
      font-weight: 700;
    }

    @media (max-width: 900px) {
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
      <li class="active"><a href="employees.php"><i class="fas fa-users"></i> Employees</a></li>
      <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
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
        <h1>Edit User</h1>
        <p>Update user information and account settings.</p>
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

    <section class="edit-container">

      <?php if (!empty($error)): ?>
        <div class="message-error">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <div class="form-grid">

          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
          </div>

          <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
          </div>

          <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
              <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
              <option value="hr" <?php if ($user['role'] == 'hr') echo 'selected'; ?>>HR</option>
              <option value="employee" <?php if ($user['role'] == 'employee') echo 'selected'; ?>>Employee</option>
              <option value="teamleader" <?php if ($user['role'] == 'teamleader') echo 'selected'; ?>>Team Leader</option>
            </select>
          </div>

          <div class="form-group">
            <label>Salary</label>
            <input type="number" step="0.01" name="salary" value="<?php echo htmlspecialchars($user['salary']); ?>">
          </div>

          <div class="form-group">
            <label>Account Status *</label>
            <select name="account_status" required>
              <option value="active" <?php if ($user['account_status'] == 'active') echo 'selected'; ?>>Active</option>
              <option value="inactive" <?php if ($user['account_status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
              <option value="pending" <?php if ($user['account_status'] == 'pending') echo 'selected'; ?>>Pending</option>
            </select>
          </div>

          <div class="form-group">
            <label>Blocked Status</label>
            <select name="is_blocked">
              <option value="0" <?php if ($user['is_blocked'] == 0) echo 'selected'; ?>>Not Blocked</option>
              <option value="1" <?php if ($user['is_blocked'] == 1) echo 'selected'; ?>>Blocked</option>
            </select>
          </div>

        </div>

        <div class="form-actions">
          <button type="submit" name="update_user" class="save-btn">
            <i class="fas fa-save"></i> Save Changes
          </button>

          <a href="employeeprofile.php?id=<?php echo $user['id']; ?>" class="cancel-btn">
            <i class="fas fa-arrow-left"></i> Cancel
          </a>
        </div>

      </form>

    </section>

  </main>

</div>

</body>
</html>