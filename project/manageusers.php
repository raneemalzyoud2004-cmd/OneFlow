<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$popupMessage = "";
$popupType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = trim($_POST['new_role']);

    $allowedRoles = ['admin', 'hr', 'employee', 'teamleader'];

    if (in_array($newRole, $allowedRoles, true)) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $newRole, $userId);
            if (mysqli_stmt_execute($stmt)) {
                $popupMessage = "User role updated successfully.";
                $popupType = "success";
            } else {
                $popupMessage = "Failed to update user role.";
                $popupType = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $popupMessage = "Something went wrong while preparing the role update.";
            $popupType = "error";
        }
    } else {
        $popupMessage = "Invalid role selected.";
        $popupType = "error";
    }
}

if (isset($_GET['toggle_status'])) {
    $userId = (int) $_GET['toggle_status'];

    $statusQuery = "SELECT account_status FROM users WHERE id = $userId LIMIT 1";
    $statusResult = mysqli_query($conn, $statusQuery);

    if ($statusResult && mysqli_num_rows($statusResult) > 0) {
        $userRow = mysqli_fetch_assoc($statusResult);
        $currentStatus = $userRow['account_status'];

        if ($currentStatus === 'active') {
            $newStatus = 'inactive';
        } elseif ($currentStatus === 'inactive') {
            $newStatus = 'active';
        } else {
            $newStatus = $currentStatus;
        }

        if ($currentStatus !== 'pending_setup') {
            mysqli_query($conn, "UPDATE users SET account_status = '$newStatus' WHERE id = $userId");
        }
    }

    header("Location: manageusers.php");
    exit();
}

if (isset($_GET['unblock_id'])) {
    $userId = (int) $_GET['unblock_id'];

    $stmt = mysqli_prepare($conn, "UPDATE users SET is_blocked = 0, failed_attempts = 0 WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manageusers.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : "";

$sql = "SELECT id, full_name, username, email, role, account_status, failed_attempts, is_blocked
        FROM users
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if ($roleFilter !== "") {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
$users = [];

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - OneFlow</title>
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
      <p class="admin-role">Admin Panel</p>
    </div>

    <ul class="sidebar-menu">
      <li><a href="dashboardadmin.php"><i class="fas fa-house"></i> Dashboard</a></li>
      <li class="active"><a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="hrteam.php"><i class="fas fa-user-tie"></i> HR Team</a></li>
      <li><a href="requestsadmin.php"><i class="fas fa-file-circle-check"></i> Requests</a></li>
      <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
      <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
      <li><a href="securitycenter.php"><i class="fas fa-shield-halved"></i> Security Center</a></li>
      <li><a href="settingsadmin.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
      <div class="system-card">
        <p>User Control</p>
        <h4>Manage</h4>
        <span>Roles, status, and access</span>
      </div>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <div class="topbar-left">
        <h1>Manage Users</h1>
        <p>Search, review, and control user roles and account status from one place.</p>
      </div>

      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">A</div>
          <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>Super Admin</span>
          </div>
        </div>

        <a href="dashboardadmin.php" class="logout-btn" style="text-decoration:none;">Back to Dashboard</a>
      </div>
    </header>

    <section class="hero-banner">
      <div class="hero-text">
        <h2>User control center 👥</h2>
        <p>Update user roles, monitor blocked accounts, and manage active or inactive users from this page.</p>
      </div>
      <div class="hero-actions">
        <a href="dashboardadmin.php" class="hero-btn primary-btn">
          <i class="fas fa-house"></i> Dashboard
        </a>
      </div>
    </section>

    <div class="panel">
      <div class="panel-header">
        <h2>Search and Filter</h2>
      </div>

      <form method="GET" style="width:100%;">
        <div style="
          display:grid;
          grid-template-columns: 2fr 1fr auto;
          gap:18px;
          align-items:end;
          width:100%;
        ">
          
          <div style="width:100%;">
            <label for="search" style="
              display:block;
              margin-bottom:10px;
              color:#0f172a;
              font-size:14px;
              font-weight:700;
            ">Search</label>

            <input
              type="text"
              id="search"
              name="search"
              placeholder="Name, username, or email"
              value="<?php echo htmlspecialchars($search); ?>"
              style="
                width:100%;
                height:48px;
                padding:0 14px;
                border:1px solid #dbe7f0;
                border-radius:14px;
                background:#ffffff;
                outline:none;
                font-size:14px;
                color:#0f172a;
                box-shadow:0 6px 18px rgba(15, 23, 42, 0.04);
              "
            >
          </div>

          <div style="width:100%;">
            <label for="role" style="
              display:block;
              margin-bottom:10px;
              color:#0f172a;
              font-size:14px;
              font-weight:700;
            ">Role</label>

            <select
              id="role"
              name="role"
              style="
                width:100%;
                height:48px;
                padding:0 14px;
                border:1px solid #dbe7f0;
                border-radius:14px;
                background:#ffffff;
                outline:none;
                font-size:14px;
                color:#0f172a;
                box-shadow:0 6px 18px rgba(15, 23, 42, 0.04);
              "
            >
              <option value="">All Roles</option>
              <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
              <option value="hr" <?php echo $roleFilter === 'hr' ? 'selected' : ''; ?>>HR</option>
              <option value="employee" <?php echo $roleFilter === 'employee' ? 'selected' : ''; ?>>Employee</option>
              <option value="teamleader" <?php echo $roleFilter === 'teamleader' ? 'selected' : ''; ?>>Team Leader</option>
            </select>
          </div>

          <div style="
            display:flex;
            gap:10px;
            align-items:center;
          ">
            <button
              type="submit"
              style="
                min-width:110px;
                height:48px;
                border:none;
                border-radius:14px;
                font-size:14px;
                font-weight:700;
                cursor:pointer;
                background:linear-gradient(90deg, #0ea5a4, #14b8a6);
                color:white;
                box-shadow:0 10px 18px rgba(20, 184, 166, 0.22);
              "
            >
              Apply
            </button>

            <a
              href="manageusers.php"
              style="
                min-width:110px;
                height:48px;
                border:none;
                border-radius:14px;
                font-size:14px;
                font-weight:700;
                text-decoration:none;
                display:inline-flex;
                align-items:center;
                justify-content:center;
                background:#e2e8f0;
                color:#0f172a;
              "
            >
              Reset
            </a>
          </div>

        </div>
      </form>
    </div>

    <div class="panel">
      <div class="panel-header">
        <h2>Users List</h2>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Full Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Account Status</th>
              <th>Security</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($users)) { ?>
              <?php foreach ($users as $user) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($user['username']); ?></td>
                  <td><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : 'Not set yet'; ?></td>

                  <td>
                    <form method="POST" style="margin:0;">
                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                      <div style="
                        display:flex;
                        align-items:center;
                        gap:8px;
                        min-width:220px;
                      ">
                        <select
                          name="new_role"
                          style="
                            flex:1;
                            height:42px;
                            padding:0 12px;
                            border:1px solid #dbe7f0;
                            border-radius:12px;
                            background:#ffffff;
                            font-size:13px;
                            color:#0f172a;
                            outline:none;
                          "
                        >
                          <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                          <option value="hr" <?php echo $user['role'] === 'hr' ? 'selected' : ''; ?>>HR</option>
                          <option value="employee" <?php echo $user['role'] === 'employee' ? 'selected' : ''; ?>>Employee</option>
                          <option value="teamleader" <?php echo $user['role'] === 'teamleader' ? 'selected' : ''; ?>>Team Leader</option>
                        </select>

                        <button
                          type="submit"
                          name="update_role"
                          style="
                            height:42px;
                            padding:0 14px;
                            border:none;
                            border-radius:12px;
                            background:linear-gradient(90deg, #0ea5a4, #14b8a6);
                            color:white;
                            font-size:12px;
                            font-weight:700;
                            cursor:pointer;
                            white-space:nowrap;
                          "
                        >
                          Save
                        </button>
                      </div>
                    </form>
                  </td>

                  <td>
                    <span class="status <?php echo strtolower($user['account_status']); ?>">
                      <?php echo ucfirst(str_replace('_', ' ', $user['account_status'])); ?>
                    </span>
                  </td>

                  <td>
                    <?php if ((int) $user['is_blocked'] === 1) { ?>
                      <span class="status rejected">Blocked</span>
                    <?php } elseif ((int) $user['failed_attempts'] >= 2) { ?>
                      <span class="status pending">Warning</span>
                    <?php } else { ?>
                      <span class="status approved">Normal</span>
                    <?php } ?>
                  </td>

                  <td>
                    <div class="manage-user-actions">
                      <a href="employeeinfo.php?id=<?php echo $user['id']; ?>" class="action-btn view">View</a>

                      <?php if ((int) $user['is_blocked'] === 1) { ?>
                        <a href="manageusers.php?unblock_id=<?php echo $user['id']; ?>" class="action-btn approve">Unblock</a>
                      <?php } ?>

                      <?php if ($user['account_status'] === 'active') { ?>
                        <a href="manageusers.php?toggle_status=<?php echo $user['id']; ?>" class="action-btn reject">Deactivate</a>
                      <?php } elseif ($user['account_status'] === 'inactive') { ?>
                        <a href="manageusers.php?toggle_status=<?php echo $user['id']; ?>" class="action-btn approve">Activate</a>
                      <?php } elseif ($user['account_status'] === 'pending_setup') { ?>
                        <span class="manage-pending-text">Waiting setup</span>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="7">No users found.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<div id="actionPopup" class="action-popup"></div>

<script>
  function showPopup(message, type) {
    const popup = document.getElementById("actionPopup");
    if (!popup) return;

    popup.textContent = message;
    popup.className = "action-popup show " + type;

    setTimeout(function () {
      popup.className = "action-popup";
    }, 2500);
  }

  document.addEventListener("DOMContentLoaded", function () {
    const popupMessage = <?php echo json_encode($popupMessage); ?>;
    const popupType = <?php echo json_encode($popupType); ?>;

    if (popupMessage) {
      showPopup(popupMessage, popupType);
    }
  });
</script>

</body>
</html>