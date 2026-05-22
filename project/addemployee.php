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

$success = "";
$error = "";

if (isset($_POST['add_employee'])) {

    $employee_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $salary = floatval($_POST['salary']);
    $password = trim($_POST['password']);

    if (
        empty($employee_name) ||
        empty($username) ||
        empty($role) ||
        empty($password)
    ) {

        $error = "Please fill all required fields.";

    } else {

        $checkUser = mysqli_query($conn, "
            SELECT id FROM users 
            WHERE username='$username'
        ");

        if (mysqli_num_rows($checkUser) > 0) {

            $error = "Username already exists.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn, "
                INSERT INTO users 
                (
                    full_name,
                    username,
                    email,
                    password,
                    role,
                    salary,
                    account_status,
                    is_blocked,
                    failed_attempts
                )
                VALUES
                (
                    '$employee_name',
                    '$username',
                    '$email',
                    '$hashed_password',
                    '$role',
                    '$salary',
                    'active',
                    0,
                    0
                )
            ");

            if ($insert) {

                header("Location: employees.php");
                exit();

            } else {

                $error = "Failed to add employee.";

            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Employee - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.add-container{
    background:#ffffff;
    padding:30px;
    border-radius:20px;
    margin-top:25px;
    box-shadow:0 10px 25px rgba(13,30,76,0.08);
    border:1px solid rgba(13,30,76,0.08);
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group label{
    margin-bottom:8px;
    color:#0D1E4C;
    font-weight:700;
}

.form-group input,
.form-group select{
    padding:13px;
    border-radius:12px;
    border:1px solid #d1d5db;
    outline:none;
    font-size:15px;
}

.form-group input:focus,
.form-group select:focus{
    border-color:#83A6CE;
    box-shadow:0 0 0 3px rgba(131,166,206,0.25);
}

.form-actions{
    margin-top:25px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.save-btn{
    background:#0D1E4C;
    color:white;
    border:none;
    padding:13px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:800;
}

.cancel-btn{
    background:#E5C9D7;
    color:#0D1E4C;
    padding:13px 20px;
    border-radius:12px;
    text-decoration:none;
    font-weight:800;
}

.error-message{
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
    font-weight:700;
}

@media(max-width:900px){

    .form-grid{
        grid-template-columns:1fr;
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

<li>
<a href="hrdashboard.php">
<i class="fas fa-house"></i> Dashboard
</a>
</li>

<li class="active">
<a href="employees.php">
<i class="fas fa-users"></i> Employees
</a>
</li>

<li>
<a href="attendance.php">
<i class="fas fa-calendar-check"></i> Attendance
</a>
</li>

<li>
<a href="leaverequests.php">
<i class="fas fa-file-circle-check"></i> Leave Requests
</a>
</li>

<li>
<a href="recruitment.php">
<i class="fas fa-user-plus"></i> Recruitment
</a>
</li>

<li>
<a href="notificationshr.php">
<i class="fas fa-bell"></i> Notifications
</a>
</li>

<li>
<a href="settingshr.php">
<i class="fas fa-gear"></i> Settings
</a>
</li>

</ul>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>Add Employee</h1>
<p>Create a new user account inside OneFlow.</p>
</div>

<div class="topbar-right">

<div class="admin-profile">

<div class="admin-avatar">
<?php echo strtoupper(substr($full_name,0,1)); ?>
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

<?php if(!empty($error)): ?>
<div class="error-message">
<?php echo $error; ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="form-grid">

<div class="form-group">
<label>Full Name *</label>
<input type="text" name="full_name" required>
</div>

<div class="form-group">
<label>Username *</label>
<input type="text" name="username" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email">
</div>

<div class="form-group">
<label>Password *</label>
<input type="password" name="password" required>
</div>

<div class="form-group">
<label>Role *</label>

<select name="role" required>
<option value="">Select Role</option>
<option value="admin">Admin</option>
<option value="hr">HR</option>
<option value="employee">Employee</option>
<option value="teamleader">Team Leader</option>
</select>

</div>

<div class="form-group">
<label>Salary</label>
<input type="number" step="0.01" name="salary">
</div>

</div>

<div class="form-actions">

<button type="submit" name="add_employee" class="save-btn">
<i class="fas fa-user-plus"></i> Add Employee
</button>

<a href="employees.php" class="cancel-btn">
<i class="fas fa-arrow-left"></i> Cancel
</a>

</div>

</form>

</section>

</main>

</div>

</body>
</html>