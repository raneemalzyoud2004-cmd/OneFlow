<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$successMessage = "";

if (isset($_POST['submit_ticket'])) {

    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn, "
        INSERT INTO support_tickets
        (
            employee_id,
            employee_name,
            subject,
            description,
            priority,
            category,
            status,
            created_at
        )
        VALUES
        (
            '$user_id',
            '$full_name',
            '$subject',
            '$description',
            '$priority',
            '$category',
            'Pending',
            NOW()
        )
    ");

    $successMessage = "Technical issue submitted successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Report Issue - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

.issue-container{
    max-width:900px;
    margin:auto;
}

.issue-card{
    background:white;
    border-radius:28px;
    padding:32px;
    box-shadow:0 15px 40px rgba(15,23,42,0.08);
}

.issue-header{
    margin-bottom:28px;
}

.issue-header h2{
    color:#0D1E4C;
    font-size:34px;
    margin-bottom:10px;
}

.issue-header p{
    color:#64748b;
    line-height:1.7;
}

.success-message{
    background:#dcfce7;
    color:#166534;
    padding:15px 18px;
    border-radius:16px;
    font-weight:800;
    margin-bottom:20px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.form-group{
    margin-bottom:18px;
}

.form-group.full{
    grid-column:1 / -1;
}

.form-group label{
    display:block;
    margin-bottom:10px;
    font-weight:800;
    color:#0D1E4C;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    border:1px solid #dbe7f0;
    border-radius:16px;
    padding:14px 16px;
    outline:none;
    font-size:14px;
    color:#0f172a;
    background:#ffffff;
}

.form-group textarea{
    min-height:170px;
    resize:vertical;
}

.submit-btn{
    border:none;
    background:linear-gradient(90deg,#0ea5a4,#14b8a6);
    color:white;
    padding:15px 22px;
    border-radius:16px;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 12px 24px rgba(20,184,166,0.22);
}

.issue-info{
    margin-top:25px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:18px;
}

.issue-info h4{
    color:#0D1E4C;
    margin-bottom:10px;
}

.issue-info p{
    color:#64748b;
    line-height:1.6;
}

@media(max-width:800px){

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

<p class="admin-role">
<?php echo ucfirst($role); ?> Panel
</p>

</div>

<ul class="sidebar-menu">

<li>
<a href="javascript:history.back()">
<i class="fas fa-arrow-left"></i>
Back
</a>
</li>

<li class="active">
<a href="report_issue.php">
<i class="fas fa-headset"></i>
Report Issue
</a>
</li>

</ul>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
<h1>Report Technical Issue</h1>
<p>Send technical issues directly to the IT Support team.</p>
</div>

<div class="topbar-right">

<div class="admin-profile">

<div class="admin-avatar">
<?php echo strtoupper(substr($full_name,0,1)); ?>
</div>

<div>
<h4><?php echo htmlspecialchars($full_name); ?></h4>
<span><?php echo ucfirst($role); ?></span>
</div>

</div>

<a href="logout.php" class="logout-btn">Logout</a>

</div>

</header>

<div class="issue-container">

<div class="issue-card">

<div class="issue-header">

<h2>Technical Support Ticket 🎫</h2>

<p>
Describe your technical issue clearly so the IT Support team can
review and resolve it quickly.
</p>

</div>

<?php if(!empty($successMessage)){ ?>

<div class="success-message">
<?php echo $successMessage; ?>
</div>

<?php } ?>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>Issue Subject</label>

<input
type="text"
name="subject"
placeholder="Example: Login problem"
required
>

</div>

<div class="form-group">

<label>Category</label>

<select name="category" required>

<option value="">Select category</option>

<option value="Login">Login</option>

<option value="Attendance">Attendance</option>

<option value="Inventory">Inventory</option>

<option value="System">System</option>

<option value="Other">Other</option>

</select>

</div>

<div class="form-group">

<label>Priority</label>

<select name="priority" required>

<option value="">Select priority</option>

<option value="Low">Low</option>

<option value="Medium">Medium</option>

<option value="High">High</option>

</select>

</div>

<div class="form-group">

<label>User Role</label>

<input
type="text"
value="<?php echo ucfirst($role); ?>"
readonly
>

</div>

<div class="form-group full">

<label>Issue Description</label>

<textarea
name="description"
placeholder="Describe the issue in detail..."
required
></textarea>

</div>

</div>

<button
type="submit"
name="submit_ticket"
class="submit-btn"
>
Submit Ticket
</button>

</form>

<div class="issue-info">

<h4>How IT Support handles tickets</h4>

<p>
Every submitted ticket appears directly inside the IT Support dashboard.
The support team can assign the ticket, track progress, and resolve the issue.
</p>

</div>

</div>

</div>

</main>
</div>

</body>
</html>