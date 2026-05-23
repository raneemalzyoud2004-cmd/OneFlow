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
$errorMessage = "";

if (isset($_POST['update_employee'])) {
    $id = intval($_POST['employee_id']);

    $fullName = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $accountStatus = mysqli_real_escape_string($conn, trim($_POST['account_status']));
    $salary = floatval($_POST['salary']);
    $birthDate = mysqli_real_escape_string($conn, trim($_POST['birth_date']));
    $newPassword = trim($_POST['new_password']);

    $allowedRoles = ['admin', 'hr', 'employee', 'teamleader', 'itsupport'];
    $allowedStatuses = ['active', 'inactive', 'pending_setup'];

    if (empty($fullName) || empty($username) || empty($role) || empty($accountStatus)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (!in_array($role, $allowedRoles)) {
        $errorMessage = "Invalid role selected.";
    } elseif (!in_array($accountStatus, $allowedStatuses)) {
        $errorMessage = "Invalid account status selected.";
    } else {
        $checkUsername = mysqli_query($conn, "
            SELECT id 
            FROM users 
            WHERE username='$username' 
            AND id != $id
            LIMIT 1
        ");

        if ($checkUsername && mysqli_num_rows($checkUsername) > 0) {
            $errorMessage = "This username is already used by another user.";
        } else {
            $checkEmail = mysqli_query($conn, "
                SELECT id 
                FROM users 
                WHERE email='$email' 
                AND id != $id
                LIMIT 1
            ");

            if (!empty($email) && $checkEmail && mysqli_num_rows($checkEmail) > 0) {
                $errorMessage = "This email is already used by another user.";
            } else {
                $birthDateSql = "NULL";
                if (!empty($birthDate)) {
                    $birthDateSql = "'$birthDate'";
                }

                $emailSql = "NULL";
                if (!empty($email)) {
                    $emailSql = "'$email'";
                }

                $passwordUpdate = "";
                if (!empty($newPassword)) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $hashedPassword = mysqli_real_escape_string($conn, $hashedPassword);
                    $passwordUpdate = ", password='$hashedPassword'";
                }

                $update = mysqli_query($conn, "
                    UPDATE users 
                    SET full_name='$fullName',
                        username='$username',
                        email=$emailSql,
                        role='$role',
                        account_status='$accountStatus',
                        salary='$salary',
                        birth_date=$birthDateSql
                        $passwordUpdate
                    WHERE id=$id
                ");

                if ($update) {
                    $successMessage = "User updated successfully.";
                } else {
                    $errorMessage = "Update failed: " . mysqli_error($conn);
                }
            }
        }
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
    SELECT id, full_name, username, email, role, account_status, salary, birth_date
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

.error-message {
    background: #fee2e2;
    color: #991b1b;
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
    width: 760px;
    max-width: 100%;
    max-height: 92vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 22px;
    padding: 26px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.modal-box h2 {
    color: #0D1E4C;
    margin-bottom: 18px;
}

.edit-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group.full {
    grid-column: 1 / -1;
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

.helper-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 6px;
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

@media(max-width: 800px) {
    .edit-grid {
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
    <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
<?php endif; ?>

<?php if (!empty($errorMessage)): ?>
    <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>

<section class="hero-banner">
    <div class="hero-text">
        <h2>Employees Directory 👥</h2>
        <p>You can complete missing employee data after account setup, including salary and birth date.</p>
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
            <h3>5</h3>
            <p>Roles</p>
            <span>Admin, HR, Employee, Team Leader, IT</span>
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
                    <option value="itsupport" <?php echo $roleFilter === 'itsupport' ? 'selected' : ''; ?>>IT Support</option>
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
                    <th>Role</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Salary</th>
                    <th>Birth Date</th>
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

                        $birthDateValue = !empty($row['birth_date']) ? $row['birth_date'] : '';
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
                            <td><?php echo !empty($birthDateValue) ? htmlspecialchars($birthDateValue) : 'Not set'; ?></td>

                            <td>
                                <a class="action-btn view-btn" href="employeeprofile.php?id=<?php echo $row['id']; ?>">View</a>

                                <button 
                                    type="button"
                                    class="action-btn edit-btn"
                                    onclick="openEditModal(
                                        '<?php echo $row['id']; ?>',
                                        '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($row['role'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($row['account_status'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($row['salary'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($birthDateValue, ENT_QUOTES); ?>'
                                    )"
                                >
                                    Edit
                                </button>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No users found.</td>
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
        <h2>Edit User Details</h2>

        <form method="POST" action="employees.php">
            <input type="hidden" name="employee_id" id="edit_employee_id">

            <div class="edit-grid">
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
                    <label>Role</label>
                    <select name="role" id="edit_role" required>
                        <option value="admin">Admin</option>
                        <option value="hr">HR</option>
                        <option value="employee">Employee</option>
                        <option value="teamleader">Team Leader</option>
                        <option value="itsupport">IT Support</option>
                    </select>
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

                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" name="birth_date" id="edit_birth_date">
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="edit_new_password" placeholder="Leave empty if unchanged">
                    <div class="helper-text">Only fill this field if HR wants to reset the user's password.</div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_employee" class="save-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, fullName, username, email, role, accountStatus, salary, birthDate) {
    document.getElementById("edit_employee_id").value = id;
    document.getElementById("edit_full_name").value = fullName;
    document.getElementById("edit_username").value = username;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_role").value = role;
    document.getElementById("edit_account_status").value = accountStatus;
    document.getElementById("edit_salary").value = salary;
    document.getElementById("edit_birth_date").value = birthDate;
    document.getElementById("edit_new_password").value = "";

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