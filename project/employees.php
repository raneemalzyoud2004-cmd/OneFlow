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

if (isset($_POST['update_employee'])) {
    $id = intval($_POST['employee_id']);
    $fullName = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $accountStatus = mysqli_real_escape_string($conn, $_POST['account_status']);
    $salary = floatval($_POST['salary']);

    $update = mysqli_query($conn, "
        UPDATE users 
        SET full_name='$fullName',
            username='$username',
            email='$email',
            account_status='$accountStatus',
            salary='$salary'
        WHERE id=$id
    ");

    if ($update) {
        $successMessage = "User updated successfully.";
    }
}

if (isset($_POST['deactivate_employee'])) {
    $id = intval($_POST['employee_id']);

    $deactivate = mysqli_query($conn, "
        UPDATE users 
        SET account_status='inactive',
            is_blocked=1
        WHERE id=$id
    ");

    if ($deactivate) {
        $successMessage = "User deactivated successfully.";
    }
}

if (isset($_POST['activate_employee'])) {
    $id = intval($_POST['employee_id']);

    $activate = mysqli_query($conn, "
        UPDATE users 
        SET account_status='active',
            is_blocked=0,
            failed_attempts=0
        WHERE id=$id
    ");

    if ($activate) {
        $successMessage = "User activated successfully.";
    }
}

$totalEmployees = 0;
$activeEmployees = 0;
$inactiveEmployees = 0;
$pendingAccounts = 0;

$roleFilter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) $totalEmployees = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE account_status='active'");
if ($result) $activeEmployees = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE account_status='inactive'");
if ($result) $inactiveEmployees = mysqli_fetch_assoc($result)['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE account_status='pending_setup'");
if ($result) $pendingAccounts = mysqli_fetch_assoc($result)['total'];

$whereClauses = [];

if (!empty($search)) {
    $whereClauses[] = "(full_name LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%')";
}

if (!empty($roleFilter)) {
    $whereClauses[] = "role='$roleFilter'";
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$employees = mysqli_query($conn, "
    SELECT id, full_name, username, email, role, account_status, salary
    FROM users
    $whereSQL
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employees - OneFlow</title>
  <link rel="stylesheet" href="css/styleadmin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .action-btn {
      padding: 8px 12px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      margin-right: 6px;
      display: inline-block;
      border: none;
      cursor: pointer;
    }

    .view-btn { background: #E5C9D7; color: #0D1E4C; }
    .edit-btn { background: #83A6CE; color: #0D1E4C; }
    .deactivate-btn { background: #ffe0e0; color: #9b1c1c; }
    .activate-btn { background: #dcfce7; color: #166534; }

    .success-message {
      background: #e7f8ee;
      color: #166534;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

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
      width: 520px;
      max-width: 100%;
      background: #fff;
      border-radius: 22px;
      padding: 26px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.2);
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
    .form-group select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #d1d5db;
      outline: none;
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
        <h1>Employees</h1>
        <p>Manage employee records, departments, and work status.</p>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <form method="GET" action="employees.php">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search employees..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-arrow-right"></i></button>
          </form>
        </div>

        <div class="admin-profile">
          <div class="admin-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
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
        <h2>Employees Directory 👥</h2>
        <p>You can manage user information, account status, roles, and basic work data from here.</p>
      </div>

      <div class="hero-actions">
        <a href="addemployee.php" class="hero-btn primary-btn">
          <i class="fas fa-user-plus"></i> Add Employee
        </a>
      </div>
    </section>

    <section class="cards">
      <div class="card">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <div class="card-info">
          <h3><?php echo $totalEmployees; ?></h3>
          <p>Total Users</p>
          <span>Registered users</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-building"></i></div>
        <div class="card-info">
          <h3>4</h3>
          <p>Roles</p>
          <span>Admin, HR, Employee, Team Leader</span> 
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
        <div class="card-info">
          <h3><?php echo $activeEmployees; ?></h3>
          <p>Active Users</p>
          <span>Currently working</span>
        </div>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-user-clock"></i></div>
        <div class="card-info">
          <h3><?php echo $inactiveEmployees + $pendingAccounts; ?></h3>
          <p>Inactive/Pending</p>
          <span>Need HR review</span>
        </div>
      </div>
    </section>

    <div class="panel">
      <div class="panel-header">
        <h2>Search and Filter</h2>
      </div>

      <form method="GET" style="width:100%;">
        <div style="display:grid; grid-template-columns: 2fr 1fr auto; gap:18px; align-items:end; width:100%;">
          
          <div style="width:100%;">
            <label for="search" style="display:block; margin-bottom:10px; color:#0f172a; font-size:14px; font-weight:700;">Search</label>

            <input
              type="text"
              id="search"
              name="search"
              placeholder="Name, username, or email"
              value="<?php echo htmlspecialchars($search); ?>"
              style="width:100%; height:48px; padding:0 14px; border:1px solid #dbe7f0; border-radius:14px; background:#ffffff; outline:none; font-size:14px; color:#0f172a; box-shadow:0 6px 18px rgba(15, 23, 42, 0.04);"
            >
          </div>

          <div style="width:100%;">
            <label for="role" style="display:block; margin-bottom:10px; color:#0f172a; font-size:14px; font-weight:700;">Role</label>

            <select
              id="role"
              name="role"
              style="width:100%; height:48px; padding:0 14px; border:1px solid #dbe7f0; border-radius:14px; background:#ffffff; outline:none; font-size:14px; color:#0f172a; box-shadow:0 6px 18px rgba(15, 23, 42, 0.04);"
            >
              <option value="">All Roles</option>
              <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
              <option value="hr" <?php echo $roleFilter === 'hr' ? 'selected' : ''; ?>>HR</option>
              <option value="employee" <?php echo $roleFilter === 'employee' ? 'selected' : ''; ?>>Employee</option>
              <option value="teamleader" <?php echo $roleFilter === 'teamleader' ? 'selected' : ''; ?>>Team Leader</option>
            </select>
          </div>

          <div style="display:flex; gap:10px; align-items:center;">
            <button
              type="submit"
              style="min-width:110px; height:48px; border:none; border-radius:14px; font-size:14px; font-weight:700; cursor:pointer; background:linear-gradient(90deg, #0ea5a4, #14b8a6); color:white; box-shadow:0 10px 18px rgba(20, 184, 166, 0.22);"
            >
              Apply
            </button>

            <a
              href="employees.php"
              style="min-width:110px; height:48px; border:none; border-radius:14px; font-size:14px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; background:#e2e8f0; color:#0f172a;"
            >
              Reset
            </a>
          </div>

        </div>
      </form>
    </div>

    <section class="panel">
      <div class="panel-header">
        <h2>User List</h2>
        <a href="employees.php">View All</a>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Username</th>
              <th>Position</th>
              <th>Status</th>
              <th>Email</th>
              <th>Salary</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <?php if ($employees && mysqli_num_rows($employees) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($employees)): ?>

                <?php
                  $statusClass = 'pending';

                  if ($row['account_status'] == 'active') {
                      $statusClass = 'approved';
                  } elseif ($row['account_status'] == 'inactive') {
                      $statusClass = 'rejected';
                  }
                ?>

                <tr>
                  <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['username']); ?></td>
                  <td><?php echo htmlspecialchars(ucfirst($row['role'])); ?></td>

                  <td>
                    <span class="status <?php echo $statusClass; ?>">
                      <?php echo htmlspecialchars($row['account_status']); ?>
                    </span>
                  </td>

                  <td><?php echo !empty($row['email']) ? htmlspecialchars($row['email']) : 'No email'; ?></td>
                  <td><?php echo number_format((float)$row['salary'], 2); ?></td>

                  <td>
                    <a class="action-btn view-btn" href="employeeprofile.php?id=<?php echo $row['id']; ?>">View</a>

                    <button 
                      type="button"
                      class="action-btn edit-btn"
                      onclick="openEditModal(
                        '<?php echo $row['id']; ?>',
                        '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($row['account_status'], ENT_QUOTES); ?>',
                        '<?php echo htmlspecialchars($row['salary'], ENT_QUOTES); ?>'
                      )"
                    >
                      Edit
                    </button>

                    <?php if ($row['account_status'] == 'active'): ?>
                      <form method="POST" action="employees.php" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?php echo $row['id']; ?>">
                        <button 
                          type="submit"
                          name="deactivate_employee"
                          class="action-btn deactivate-btn"
                          onclick="return confirm('Are you sure you want to deactivate this user?');"
                        >
                          Deactivate
                        </button>
                      </form>
                    <?php else: ?>
                      <form method="POST" action="employees.php" style="display:inline;">
                        <input type="hidden" name="employee_id" value="<?php echo $row['id']; ?>">
                        <button 
                          type="submit"
                          name="activate_employee"
                          class="action-btn activate-btn"
                          onclick="return confirm('Are you sure you want to activate this user?');"
                        >
                          Activate
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No users found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <h2>Edit User</h2>

    <form method="POST" action="employees.php">
      <input type="hidden" name="employee_id" id="edit_employee_id">

      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" id="edit_full_name" required>
      </div>

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" id="edit_username" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" id="edit_email">
      </div>

      <div class="form-group">
        <label>Account Status</label>
        <select name="account_status" id="edit_account_status" required>
          <option value="active">active</option>
          <option value="inactive">inactive</option>
          <option value="pending_setup">pending_setup</option>
        </select>
      </div>

      <div class="form-group">
        <label>Salary</label>
        <input type="number" step="0.01" name="salary" id="edit_salary">
      </div>

      <div class="modal-actions">
        <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
        <button type="submit" name="update_employee" class="save-btn">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(id, fullName, username, email, accountStatus, salary) {
  document.getElementById("edit_employee_id").value = id;
  document.getElementById("edit_full_name").value = fullName;
  document.getElementById("edit_username").value = username;
  document.getElementById("edit_email").value = email;
  document.getElementById("edit_account_status").value = accountStatus;
  document.getElementById("edit_salary").value = salary;

  document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
}

window.onclick = function(event) {
  const modal = document.getElementById("editModal");
  if (event.target === modal) {
    closeEditModal();
  }
}
</script>

</body>
</html>   