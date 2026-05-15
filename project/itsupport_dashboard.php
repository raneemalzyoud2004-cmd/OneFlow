<?php
session_start();
include "config.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'itsupport') {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];
$successMessage = "";

if (isset($_GET['take_ticket'])) {
    $ticketId = intval($_GET['take_ticket']);

    $stmt = mysqli_prepare($conn, "UPDATE support_tickets SET assigned_to = ?, status = 'In Progress' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $ticketId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $successMessage = "Ticket assigned to you successfully.";
    }
}

if (isset($_GET['resolve_ticket'])) {
    $ticketId = intval($_GET['resolve_ticket']);

    $stmt = mysqli_prepare($conn, "UPDATE support_tickets SET status = 'Resolved' WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $ticketId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $successMessage = "Ticket marked as resolved.";
    }
}

$totalTickets = 0;
$pendingTickets = 0;
$progressTickets = 0;
$resolvedTickets = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets");
if ($result) {
    $totalTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status = 'Pending'");
if ($result) {
    $pendingTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status = 'In Progress'");
if ($result) {
    $progressTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status = 'Resolved'");
if ($result) {
    $resolvedTickets = mysqli_fetch_assoc($result)['total'];
}

$ticketsQuery = mysqli_query($conn, "
    SELECT 
        st.id,
        st.employee_id,
        st.employee_name,
        st.subject,
        st.description,
        st.status,
        st.assigned_to,
        st.created_at,
        st.updated_at,
        u.full_name AS assigned_name
    FROM support_tickets st
    LEFT JOIN users u ON st.assigned_to = u.id
    ORDER BY st.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IT Support Dashboard - OneFlow</title>
<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.support-status {
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
}

.support-status.Pending {
    background: #fef3c7;
    color: #92400e;
}

.support-status.InProgress {
    background: #dbeafe;
    color: #1e40af;
}

.support-status.Resolved {
    background: #dcfce7;
    color: #166534;
}

.support-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.support-btn {
    border: none;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    display: inline-block;
    cursor: pointer;
}

.take-btn {
    background: #83A6CE;
    color: #0D1E4C;
}

.resolve-btn {
    background: #22c55e;
    color: white;
}

.disabled-btn {
    background: #e2e8f0;
    color: #64748b;
}

.ticket-desc {
    color: #64748b;
    font-size: 13px;
    line-height: 1.5;
    max-width: 360px;
}

.success-message {
    background: #e7f8ee;
    color: #166534;
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 18px;
    font-weight: 700;
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
        <p class="admin-role">IT Support Panel</p>
    </div>

    <ul class="sidebar-menu">
        <li class="active"><a href="itsupport_dashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="itsupport_dashboard.php"><i class="fas fa-ticket"></i> Tickets</a></li>
        <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>System Status</p>
            <h4>Running</h4>
            <span>Tickets connected to database</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>IT Support Dashboard</h1>
        <p>Monitor, assign, and resolve technical support tickets.</p>
    </div>

    <div class="topbar-right">
        <div class="admin-profile">
            <div class="admin-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>IT Support</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if (!empty($successMessage)) { ?>
    <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
<?php } ?>

<section class="hero-banner">
    <div class="hero-text">
        <h2>Technical Support Center 🛠️</h2>
        <p>Track issues reported by employees, take ownership of tickets, and mark resolved issues.</p>
    </div>
</section>

<section class="cards">
    <div class="card">
        <div class="card-icon"><i class="fas fa-ticket"></i></div>
        <div class="card-info">
            <h3><?php echo $totalTickets; ?></h3>
            <p>Total Tickets</p>
            <span>All submitted issues</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-circle-exclamation"></i></div>
        <div class="card-info">
            <h3><?php echo $pendingTickets; ?></h3>
            <p>Pending</p>
            <span>Waiting for action</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-spinner"></i></div>
        <div class="card-info">
            <h3><?php echo $progressTickets; ?></h3>
            <p>In Progress</p>
            <span>Currently being handled</span>
        </div>
    </div>

    <div class="card">
        <div class="card-icon"><i class="fas fa-circle-check"></i></div>
        <div class="card-info">
            <h3><?php echo $resolvedTickets; ?></h3>
            <p>Resolved</p>
            <span>Completed tickets</span>
        </div>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Support Tickets</h2>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($ticketsQuery && mysqli_num_rows($ticketsQuery) > 0) { ?>
                <?php while ($ticket = mysqli_fetch_assoc($ticketsQuery)) { ?>
                    <?php
                        $statusClass = str_replace(' ', '', $ticket['status']);
                    ?>
                    <tr>
                        <td><?php echo $ticket['id']; ?></td>

                        <td>
                            <strong><?php echo htmlspecialchars($ticket['employee_name']); ?></strong>
                        </td>

                        <td><?php echo htmlspecialchars($ticket['subject']); ?></td>

                        <td>
                            <div class="ticket-desc">
                                <?php echo htmlspecialchars($ticket['description']); ?>
                            </div>
                        </td>

                        <td>
                            <span class="support-status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($ticket['status']); ?>
                            </span>
                        </td>

                        <td>
                            <?php
                                if (!empty($ticket['assigned_name'])) {
                                    echo htmlspecialchars($ticket['assigned_name']);
                                } else {
                                    echo "Not assigned";
                                }
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($ticket['created_at']); ?></td>

                        <td>
                            <div class="support-actions">
                                <?php if ($ticket['status'] === 'Pending') { ?>
                                    <a class="support-btn take-btn" href="itsupport_dashboard.php?take_ticket=<?php echo $ticket['id']; ?>">
                                        Take
                                    </a>
                                <?php } else { ?>
                                    <span class="support-btn disabled-btn">Taken</span>
                                <?php } ?>

                                <?php if ($ticket['status'] !== 'Resolved') { ?>
                                    <a class="support-btn resolve-btn" href="itsupport_dashboard.php?resolve_ticket=<?php echo $ticket['id']; ?>">
                                        Resolve
                                    </a>
                                <?php } else { ?>
                                    <span class="support-btn disabled-btn">Done</span>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="8">No support tickets found.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</section>

</main>
</div>

</body>
</html>