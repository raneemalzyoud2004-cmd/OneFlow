<?php
session_start();
include("config.php");

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

if (isset($_POST['resolve_ticket'])) {
    $ticket_id = intval($_POST['ticket_id']);

    mysqli_query($conn, "
        UPDATE support_tickets 
        SET status='Resolved'
        WHERE id='$ticket_id'
    ");

    $successMessage = "Ticket marked as resolved.";
}

if (isset($_POST['assign_ticket'])) {
    $ticket_id = intval($_POST['ticket_id']);

    mysqli_query($conn, "
        UPDATE support_tickets 
        SET status='In Progress',
            assigned_to='$user_id'
        WHERE id='$ticket_id'
    ");

    $successMessage = "Ticket assigned to you.";
}

$totalTickets = 0;
$openTickets = 0;
$progressTickets = 0;
$resolvedTickets = 0;
$highPriorityTickets = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets");
if ($result) {
    $totalTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='Pending'");
if ($result) {
    $openTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='In Progress'");
if ($result) {
    $progressTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE status='Resolved'");
if ($result) {
    $resolvedTickets = mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM support_tickets WHERE priority='High' AND status != 'Resolved'");
if ($result) {
    $highPriorityTickets = mysqli_fetch_assoc($result)['total'];
}

$tickets = mysqli_query($conn, "
    SELECT support_tickets.*, users.full_name AS assigned_name
    FROM support_tickets
    LEFT JOIN users ON support_tickets.assigned_to = users.id
    ORDER BY 
        CASE 
            WHEN support_tickets.priority = 'High' THEN 1
            WHEN support_tickets.priority = 'Medium' THEN 2
            WHEN support_tickets.priority = 'Low' THEN 3
            ELSE 4
        END,
        support_tickets.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IT Support Dashboard - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.support-message {
    background: #dcfce7;
    color: #166534;
    padding: 14px 18px;
    border-radius: 16px;
    font-weight: 800;
    margin-bottom: 18px;
}

.hero-support {
    background: linear-gradient(135deg, #0D1E4C, #14b8a6);
    border-radius: 26px;
    padding: 30px;
    color: white;
    margin-bottom: 25px;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.16);
}

.hero-support h2 {
    font-size: 32px;
    margin-bottom: 10px;
}

.hero-support p {
    opacity: 0.92;
    line-height: 1.7;
    max-width: 850px;
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.quick-card {
    background: white;
    border-radius: 24px;
    padding: 22px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.07);
    border: 1px solid #eef2f7;
}

.quick-card i {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 14px;
    background: #dff7f5;
    color: #14b8a6;
}

.quick-card h3 {
    font-size: 34px;
    color: #0D1E4C;
    margin-bottom: 4px;
}

.quick-card p {
    color: #64748b;
    font-weight: 700;
}

.ticket-box {
    background: white;
    border-radius: 26px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    overflow-x: auto;
}

.ticket-box table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1150px;
}

.ticket-box th {
    background: #f1f5f9;
    padding: 16px;
    text-align: left;
    color: #0D1E4C;
    font-size: 14px;
}

.ticket-box td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.ticket-title {
    font-weight: 800;
    color: #0D1E4C;
}

.ticket-desc {
    font-size: 13px;
    color: #64748b;
    margin-top: 5px;
    max-width: 320px;
    line-height: 1.5;
}

.ticket-status,
.ticket-priority,
.ticket-category {
    padding: 7px 13px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
    white-space: nowrap;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-resolved {
    background: #dcfce7;
    color: #166534;
}

.priority-high {
    background: #fee2e2;
    color: #991b1b;
}

.priority-medium {
    background: #fef3c7;
    color: #92400e;
}

.priority-low {
    background: #dcfce7;
    color: #166534;
}

.category-login {
    background: #ede9fe;
    color: #5b21b6;
}

.category-attendance {
    background: #e0f2fe;
    color: #075985;
}

.category-inventory {
    background: #ecfccb;
    color: #3f6212;
}

.category-system {
    background: #ffe4e6;
    color: #9f1239;
}

.category-other {
    background: #e2e8f0;
    color: #334155;
}

.ticket-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.ticket-actions form {
    margin: 0;
}

.action-btn {
    border: none;
    padding: 9px 13px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    color: white;
}

.view-btn {
    background: #0D1E4C;
}

.assign-btn {
    background: #3b82f6;
}

.resolve-btn {
    background: #22c55e;
}

.done-btn {
    background: #e2e8f0;
    color: #64748b;
    cursor: default;
}

.recommend-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 22px;
}

.recommend-box h3 {
    color: #0D1E4C;
    margin-bottom: 8px;
}

.recommend-box p {
    color: #64748b;
    line-height: 1.6;
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.62);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-box {
    background: white;
    width: 560px;
    max-width: 95%;
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
}

.modal-box h2 {
    color: #0D1E4C;
    margin-bottom: 14px;
}

.detail-row {
    margin-bottom: 13px;
}

.detail-row strong {
    display: block;
    color: #0D1E4C;
    margin-bottom: 4px;
}

.detail-row span,
.detail-row p {
    color: #64748b;
    line-height: 1.6;
}

.close-modal {
    width: 100%;
    margin-top: 16px;
    background: #0D1E4C;
    color: white;
    border: none;
    padding: 12px 16px;
    border-radius: 14px;
    font-weight: 800;
    cursor: pointer;
}

@media(max-width: 1250px) {
    .quick-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width: 700px) {
    .quick-grid {
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

    <p class="admin-role">IT Support Panel</p>
</div>

<ul class="sidebar-menu">
    <li class="active"><a href="itsupport_dashboard.php"><i class="fas fa-house"></i> Dashboard</a></li>
    <li><a href="itsupport_dashboard.php"><i class="fas fa-ticket"></i> Tickets</a></li>
    <li><a href="it_inventory.php"><i class="fas fa-boxes"></i> Device Inventory</a></li>
    <li><a href="it_whoholdswhat.php"><i class="fas fa-laptop-user"></i> Who Holds What</a></li>
    <li><a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
</ul>

<div class="sidebar-bottom">
    <div class="system-card">
        <p>System Status</p>
        <h4>Running</h4>
        <span>Tickets and assets connected</span>
    </div>
</div>

</aside>

<main class="main-content">

<header class="topbar">

<div class="topbar-left">
    <h1>IT Support Dashboard</h1>
    <p>Monitor technical issues, tickets, devices, and employee assignments.</p>
</div>

<div class="topbar-right">

    <div class="admin-profile">
        <div class="admin-avatar">
            <?php echo strtoupper(substr($full_name,0,1)); ?>
        </div>

        <div>
            <h4><?php echo htmlspecialchars($full_name); ?></h4>
            <span>IT Support</span>
        </div>
    </div>

    <a href="logout.php" class="logout-btn">Logout</a>

</div>

</header>

<?php if (!empty($successMessage)) { ?>
    <div class="support-message">
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
<?php } ?>

<div class="hero-support">
    <h2>Technical Operations Center 🛠️</h2>
    <p>
        This dashboard helps IT Support understand what needs attention first, assign tickets,
        resolve issues, and monitor the technical health of OneFlow.
    </p>
</div>

<div class="quick-grid">

    <div class="quick-card">
        <i class="fas fa-ticket"></i>
        <h3><?php echo $totalTickets; ?></h3>
        <p>Total Tickets</p>
    </div>

    <div class="quick-card">
        <i class="fas fa-circle-exclamation"></i>
        <h3><?php echo $openTickets; ?></h3>
        <p>Pending</p>
    </div>

    <div class="quick-card">
        <i class="fas fa-spinner"></i>
        <h3><?php echo $progressTickets; ?></h3>
        <p>In Progress</p>
    </div>

    <div class="quick-card">
        <i class="fas fa-circle-check"></i>
        <h3><?php echo $resolvedTickets; ?></h3>
        <p>Resolved</p>
    </div>

    <div class="quick-card">
        <i class="fas fa-triangle-exclamation"></i>
        <h3><?php echo $highPriorityTickets; ?></h3>
        <p>High Priority</p>
    </div>

</div>

<div class="recommend-box">
    <h3>What should IT handle first?</h3>
    <p>
        Start with high priority tickets, then pending tickets that are not assigned yet.
        Inventory and system issues should be reviewed quickly because they may affect daily employee work.
    </p>
</div>

<div class="ticket-box">

<div class="panel-header">
    <h2>Latest Support Tickets</h2>
</div>

<table>

<thead>
<tr>
    <th>ID</th>
    <th>Issue</th>
    <th>Employee</th>
    <th>Priority</th>
    <th>Category</th>
    <th>Status</th>
    <th>Assigned To</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php if ($tickets && mysqli_num_rows($tickets) > 0): ?>

    <?php while($row = mysqli_fetch_assoc($tickets)): ?>

        <?php
            $statusClass = "status-pending";

            if ($row['status'] === "In Progress") {
                $statusClass = "status-progress";
            }

            if ($row['status'] === "Resolved") {
                $statusClass = "status-resolved";
            }

            $priority = !empty($row['priority']) ? $row['priority'] : "Medium";
            $category = !empty($row['category']) ? $row['category'] : "Other";

            $priorityClass = "priority-medium";
            if ($priority === "High") {
                $priorityClass = "priority-high";
            }
            if ($priority === "Low") {
                $priorityClass = "priority-low";
            }

            $categoryClass = "category-other";
            if ($category === "Login") {
                $categoryClass = "category-login";
            }
            if ($category === "Attendance") {
                $categoryClass = "category-attendance";
            }
            if ($category === "Inventory") {
                $categoryClass = "category-inventory";
            }
            if ($category === "System") {
                $categoryClass = "category-system";
            }

            $assignedName = !empty($row['assigned_name']) ? $row['assigned_name'] : "Unassigned";

            $recommendation = "Review the issue details and take the correct action.";
            if ($priority === "High") {
                $recommendation = "Handle this ticket first because it has high priority.";
            }
            if ($category === "Login") {
                $recommendation = "Check the account status, password hash, failed attempts, and blocking status.";
            }
            if ($category === "Attendance") {
                $recommendation = "Check attendance records, check-in/check-out logic, and related database entries.";
            }
            if ($category === "Inventory") {
                $recommendation = "Check the assigned device, inventory status, and who currently holds the asset.";
            }
            if ($category === "System") {
                $recommendation = "Check system logs, recent code changes, and database connection settings.";
            }
        ?>

        <tr>

            <td>#<?php echo $row['id']; ?></td>

            <td>
                <div class="ticket-title">
                    <?php echo htmlspecialchars($row['subject']); ?>
                </div>

                <div class="ticket-desc">
                    <?php echo htmlspecialchars($row['description']); ?>
                </div>
            </td>

            <td>
                <?php echo htmlspecialchars($row['employee_name']); ?>
            </td>

            <td>
                <span class="ticket-priority <?php echo $priorityClass; ?>">
                    <?php echo htmlspecialchars($priority); ?>
                </span>
            </td>

            <td>
                <span class="ticket-category <?php echo $categoryClass; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </span>
            </td>

            <td>
                <span class="ticket-status <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                </span>
            </td>

            <td>
                <?php echo htmlspecialchars($assignedName); ?>
            </td>

            <td>
                <div class="ticket-actions">

                    <button
                        type="button"
                        class="action-btn view-btn"
                        onclick="openTicketModal(
                            '<?php echo htmlspecialchars($row['subject'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($row['employee_name'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($priority, ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($category, ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($assignedName, ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($recommendation, ENT_QUOTES); ?>'
                        )"
                    >
                        View
                    </button>

                    <?php if($row['status'] == "Pending"): ?>
                        <form method="POST">
                            <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="assign_ticket" class="action-btn assign-btn">
                                Assign
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($row['status'] != "Resolved"): ?>
                        <form method="POST">
                            <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="resolve_ticket" class="action-btn resolve-btn">
                                Resolve
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($row['status'] == "Resolved"): ?>
                        <button type="button" class="action-btn done-btn">
                            Done
                        </button>
                    <?php endif; ?>

                </div>
            </td>

        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="8">No support tickets found.</td>
    </tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>
</div>

<div class="modal-overlay" id="ticketModal">
    <div class="modal-box">
        <h2 id="modalTitle"></h2>

        <div class="detail-row">
            <strong>Description</strong>
            <p id="modalDescription"></p>
        </div>

        <div class="detail-row">
            <strong>Employee</strong>
            <span id="modalEmployee"></span>
        </div>

        <div class="detail-row">
            <strong>Priority</strong>
            <span id="modalPriority"></span>
        </div>

        <div class="detail-row">
            <strong>Category</strong>
            <span id="modalCategory"></span>
        </div>

        <div class="detail-row">
            <strong>Status</strong>
            <span id="modalStatus"></span>
        </div>

        <div class="detail-row">
            <strong>Assigned To</strong>
            <span id="modalAssigned"></span>
        </div>

        <div class="detail-row">
            <strong>Recommended Action</strong>
            <p id="modalRecommendation"></p>
        </div>

        <button type="button" class="close-modal" onclick="closeTicketModal()">Close</button>
    </div>
</div>

<script>
function openTicketModal(title, description, employee, priority, category, status, assigned, recommendation) {
    document.getElementById("modalTitle").textContent = title;
    document.getElementById("modalDescription").textContent = description;
    document.getElementById("modalEmployee").textContent = employee;
    document.getElementById("modalPriority").textContent = priority;
    document.getElementById("modalCategory").textContent = category;
    document.getElementById("modalStatus").textContent = status;
    document.getElementById("modalAssigned").textContent = assigned;
    document.getElementById("modalRecommendation").textContent = recommendation;
    document.getElementById("ticketModal").style.display = "flex";
}

function closeTicketModal() {
    document.getElementById("ticketModal").style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById("ticketModal");
    if (event.target === modal) {
        closeTicketModal();
    }
}
</script>

</body>
</html>