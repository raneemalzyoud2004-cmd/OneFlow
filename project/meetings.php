<?php
session_start();
include("config.php");

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teamleader') {
    header("Location: login.php");
    exit();
}

$leader_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Team Leader';
$first_letter = strtoupper(substr(trim($full_name), 0, 1));

$success = "";
$error = "";

$teamUsernames = "'ammar_emp','dana_emp','khaled_emp','noor_emp','sara_emp'";

$selectedDate = $_GET['meeting_date'] ?? ($_POST['meeting_date'] ?? '');
$selectedStart = $_GET['start_time'] ?? ($_POST['start_time'] ?? '');
$selectedEnd = $_GET['end_time'] ?? ($_POST['end_time'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_meeting'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $meetingDate = trim($_POST['meeting_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $members = $_POST['members'] ?? [];

    if ($title === '' || $meetingDate === '' || $startTime === '' || $endTime === '' || empty($members)) {
        $error = "Please fill all required fields and select at least one available member.";
    } elseif (strtotime($meetingDate . " " . $endTime) <= strtotime($meetingDate . " " . $startTime)) {
        $error = "End time must be after start time.";
    } else {
        $safeTitle = mysqli_real_escape_string($conn, $title);
        $safeDescription = mysqli_real_escape_string($conn, $description);
        $safeDate = mysqli_real_escape_string($conn, $meetingDate);
        $safeStart = mysqli_real_escape_string($conn, $startTime);
        $safeEnd = mysqli_real_escape_string($conn, $endTime);

        $insertMeeting = mysqli_query($conn, "
            INSERT INTO meetings (title, description, meeting_date, start_time, end_time, created_by)
            VALUES ('$safeTitle', '$safeDescription', '$safeDate', '$safeStart', '$safeEnd', $leader_id)
        ");

        if ($insertMeeting) {
            $meetingId = mysqli_insert_id($conn);

            foreach ($members as $memberId) {
                $memberId = (int) $memberId;

                $checkMember = mysqli_query($conn, "
                    SELECT id
                    FROM users
                    WHERE id = $memberId
                    AND role = 'employee'
                    AND account_status = 'active'
                    AND username IN ($teamUsernames)
                    LIMIT 1
                ");

                if ($checkMember && mysqli_num_rows($checkMember) > 0) {
                    mysqli_query($conn, "
                        INSERT INTO meeting_members (meeting_id, employee_id, response_status)
                        VALUES ($meetingId, $memberId, 'Pending')
                    ");

                    mysqli_query($conn, "
                        INSERT INTO notifications (user_id, title, message, type, created_at)
                        VALUES (
                            $memberId,
                            'New Meeting Invitation',
                            'You have a new meeting invitation from your Team Leader. Please accept or reject it from My Schedule.',
                            'info',
                            NOW()
                        )
                    ");
                }
            }

            $success = "Meeting created successfully and invitations were sent.";
            $selectedDate = "";
            $selectedStart = "";
            $selectedEnd = "";
        } else {
            $error = "Failed to create meeting: " . mysqli_error($conn);
        }
    }
}

$notificationCount = 0;
$notificationCountResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $leader_id
    AND is_read = 0
");

if ($notificationCountResult) {
    $notificationCount = (int) mysqli_fetch_assoc($notificationCountResult)['total'];
}

$membersQuery = mysqli_query($conn, "
    SELECT id, full_name, username, email, role, account_status
    FROM users
    WHERE role = 'employee'
    AND account_status = 'active'
    AND username IN ($teamUsernames)
    ORDER BY full_name ASC
");

$createdMeetingsQuery = mysqli_query($conn, "
    SELECT *
    FROM meetings
    WHERE created_by = $leader_id
    ORDER BY meeting_date DESC, start_time DESC
    LIMIT 8
");

function responseStatusClass($status) {
    if ($status === 'Accepted') return 'accepted-status';
    if ($status === 'Rejected') return 'rejected-status';
    return 'pending-status';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meetings - OneFlow</title>

<link rel="stylesheet" href="css/styleadmin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.meeting-layout {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 24px;
    margin-top: 25px;
    align-items: start;
}

.meeting-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.08);
    border: 1px solid #eef2f7;
}

.meeting-card h2 {
    color: #0D1E4C;
    margin-bottom: 18px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #0D1E4C;
    font-weight: 800;
}

.form-group input,
.form-group textarea {
    width: 100%;
    border: 1px solid #dbe7f0;
    border-radius: 14px;
    padding: 13px 14px;
    outline: none;
    background: #f8fbfd;
    box-sizing: border-box;
}

.form-group textarea {
    min-height: 110px;
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}

.primary-meeting-btn,
.secondary-meeting-btn {
    border: none;
    border-radius: 14px;
    padding: 13px 18px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.primary-meeting-btn {
    background: linear-gradient(90deg, #0D1E4C, #14b8a6);
    color: white;
}

.secondary-meeting-btn {
    background: #e2e8f0;
    color: #0D1E4C;
}

.alert-box {
    border-radius: 16px;
    padding: 14px 18px;
    margin-bottom: 18px;
    font-weight: 800;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.member-grid {
    display: grid;
    gap: 18px;
}

.member-card {
    border: 1px solid #e5eef5;
    border-radius: 22px;
    padding: 18px;
    background: #fbfdff;
}

.member-top {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    align-items: center;
    margin-bottom: 14px;
}

.member-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.member-avatar {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: linear-gradient(135deg, #0D1E4C, #14b8a6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 18px;
}

.member-name h3 {
    color: #0D1E4C;
    margin: 0 0 4px;
}

.member-name span {
    color: #64748b;
    font-size: 13px;
}

.member-checkbox {
    width: 22px;
    height: 22px;
}

.availability-box {
    border-radius: 16px;
    padding: 13px 14px;
    margin-bottom: 14px;
    font-weight: 800;
    line-height: 1.5;
}

.available-box {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.busy-box {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.neutral-box {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.history-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px;
}

.history-box h4 {
    color: #0D1E4C;
    margin-bottom: 10px;
}

.history-list {
    display: grid;
    gap: 10px;
}

.history-item {
    background: white;
    border-radius: 13px;
    border: 1px solid #e5eef5;
    padding: 11px 12px;
}

.history-item strong {
    color: #0D1E4C;
    display: block;
    margin-bottom: 5px;
}

.history-item span {
    color: #64748b;
    font-size: 13px;
}

.empty-history {
    color: #64748b;
    font-weight: 700;
}

.meeting-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 18px;
}

.meetings-list {
    display: grid;
    gap: 14px;
}

.meeting-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
}

.meeting-item h4 {
    color: #0D1E4C;
    margin-bottom: 8px;
}

.meeting-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    color: #64748b;
    font-size: 13px;
    margin-bottom: 12px;
}

.response-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.response-pill {
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.pending-status {
    background: #fef3c7;
    color: #92400e;
}

.accepted-status {
    background: #dcfce7;
    color: #166534;
}

.rejected-status {
    background: #fee2e2;
    color: #991b1b;
}

@media(max-width: 1100px) {
    .meeting-layout {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 700px) {
    .form-row {
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
        <p class="admin-role">Team Leader Panel</p>
    </div>

    <ul class="sidebar-menu">
        <li><a href="dashboardteamleader.php"><i class="fas fa-house"></i> Dashboard</a></li>
        <li><a href="myteam.php"><i class="fas fa-users"></i> My Team</a></li>
        <li><a href="assigntasks.php"><i class="fas fa-list-check"></i> Assign Tasks</a></li>
        <li><a href="tasksprogress.php"><i class="fas fa-chart-line"></i> Tasks Progress</a></li>
        <li class="active"><a href="meetings.php"><i class="fas fa-calendar-days"></i> Meetings</a></li>
        <li><a href="reportsteamleader.php"><i class="fas fa-file-lines"></i> Reports</a></li>
        <li><a href="notificationsteamleader.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="report_issue.php"><i class="fas fa-headset"></i> Report Issue</a></li>
        <li><a href="settingsteamleader.php"><i class="fas fa-gear"></i> Settings</a></li>
    </ul>

    <div class="sidebar-bottom">
        <div class="system-card">
            <p>Smart Meetings</p>
            <h4>Active</h4>
            <span>Availability + task history</span>
        </div>
    </div>
</aside>

<main class="main-content">

<header class="topbar">
    <div class="topbar-left">
        <h1>Meetings</h1>
        <p>Schedule meetings based on team availability and previous task experience.</p>
    </div>

    <div class="topbar-right">
        <a href="notificationsteamleader.php" class="icon-btn notification-bell">
            <i class="fas fa-bell"></i>
            <?php if ($notificationCount > 0) { ?>
                <span class="notif-count"><?php echo $notificationCount; ?></span>
            <?php } ?>
        </a>

        <div class="admin-profile">
            <div class="admin-avatar"><?php echo htmlspecialchars($first_letter); ?></div>
            <div>
                <h4><?php echo htmlspecialchars($full_name); ?></h4>
                <span>Team Leader</span>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<?php if ($success !== "") { ?>
    <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<?php if ($error !== "") { ?>
    <div class="alert-box alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<section class="hero-banner">
    <div class="hero-text">
        <h2>Smart Meeting Planner 🗓️</h2>
        <p>
            Choose a meeting time, check member conflicts from their real schedule,
            and review previous tasks before inviting them.
        </p>
    </div>

    <div class="hero-actions">
        <a href="myteam.php" class="hero-btn secondary-btn">
            <i class="fas fa-users"></i> View Team
        </a>
    </div>
</section>

<form method="GET" class="meeting-card">
    <h2>Check Availability First</h2>

    <div class="form-row">
        <div class="form-group">
            <label>Meeting Date</label>
            <input type="date" name="meeting_date" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
        </div>

        <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="start_time" value="<?php echo htmlspecialchars($selectedStart); ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time" value="<?php echo htmlspecialchars($selectedEnd); ?>" required>
        </div>

        <div class="form-group" style="display:flex;align-items:end;">
            <button type="submit" class="primary-meeting-btn" style="width:100%;justify-content:center;">
                <i class="fas fa-magnifying-glass"></i> Check Team Availability
            </button>
        </div>
    </div>
</form>

<form method="POST">

<section class="meeting-layout">

    <div class="meeting-card">
        <h2>Create New Meeting</h2>

        <div class="form-group">
            <label>Meeting Title</label>
            <input type="text" name="title" placeholder="Example: Sprint Review Meeting" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Write meeting purpose or discussion points..."></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Meeting Date</label>
                <input type="date" name="meeting_date" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" value="<?php echo htmlspecialchars($selectedStart); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time" value="<?php echo htmlspecialchars($selectedEnd); ?>" required>
        </div>

        <div class="meeting-actions">
            <button type="submit" name="create_meeting" class="primary-meeting-btn">
                <i class="fas fa-calendar-plus"></i> Create Meeting & Send Invitations
            </button>

            <a href="meetings.php" class="secondary-meeting-btn">
                <i class="fas fa-rotate-left"></i> Reset
            </a>
        </div>
    </div>

    <div class="meeting-card">
        <h2>Select Team Members</h2>

        <div class="member-grid">

            <?php if ($membersQuery && mysqli_num_rows($membersQuery) > 0) { ?>
                <?php while ($member = mysqli_fetch_assoc($membersQuery)) { ?>
                    <?php
                    $memberId = (int) $member['id'];
                    $initial = strtoupper(substr($member['full_name'], 0, 1));

                    $availabilityText = "Select date and time above to check availability.";
                    $availabilityClass = "neutral-box";
                    $isBusy = false;

                    if (!empty($selectedDate) && !empty($selectedStart) && !empty($selectedEnd)) {
                        $meetingStartDateTime = date("Y-m-d H:i:s", strtotime($selectedDate . " " . $selectedStart));
                        $meetingEndDateTime = date("Y-m-d H:i:s", strtotime($selectedDate . " " . $selectedEnd));

                        $safeMeetingStart = mysqli_real_escape_string($conn, $meetingStartDateTime);
                        $safeMeetingEnd = mysqli_real_escape_string($conn, $meetingEndDateTime);
                        $safeSelectedDate = mysqli_real_escape_string($conn, $selectedDate);

                        $conflictQuery = mysqli_query($conn, "
                            SELECT title, type, start_time, end_time
                            FROM schedule
                            WHERE user_id = $memberId
                            AND DATE(start_time) = '$safeSelectedDate'
                            AND start_time < '$safeMeetingEnd'
                            AND end_time > '$safeMeetingStart'
                            ORDER BY start_time ASC
                            LIMIT 1
                        ");

                        if ($conflictQuery && mysqli_num_rows($conflictQuery) > 0) {
                            $conflict = mysqli_fetch_assoc($conflictQuery);
                            $isBusy = true;
                            $availabilityClass = "busy-box";
                            $availabilityText =
                                "Busy: " . htmlspecialchars($conflict['title']) .
                                " - " . htmlspecialchars($conflict['type']) .
                                " (" . date("h:i A", strtotime($conflict['start_time'])) .
                                " - " . date("h:i A", strtotime($conflict['end_time'])) . ")";
                        } else {
                            $availabilityClass = "available-box";
                            $availabilityText = "Available for the selected meeting time.";
                        }
                    }

                    $historyQuery = mysqli_query($conn, "
                        SELECT title, priority, status, due_date, updated_at
                        FROM team_tickets
                        WHERE assigned_to = $memberId
                        ORDER BY updated_at DESC, created_at DESC
                        LIMIT 4
                    ");
                    ?>

                    <div class="member-card">
                        <div class="member-top">
                            <div class="member-left">
                                <div class="member-avatar"><?php echo htmlspecialchars($initial); ?></div>

                                <div class="member-name">
                                    <h3><?php echo htmlspecialchars($member['full_name']); ?></h3>
                                    <span><?php echo htmlspecialchars($member['email'] ?: $member['username']); ?></span>
                                </div>
                            </div>

                            <input
                                class="member-checkbox"
                                type="checkbox"
                                name="members[]"
                                value="<?php echo $memberId; ?>"
                                <?php echo $isBusy ? 'disabled' : ''; ?>
                            >
                        </div>

                        <div class="availability-box <?php echo $availabilityClass; ?>">
                            <i class="fas <?php echo $isBusy ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i>
                            <?php echo $availabilityText; ?>
                        </div>

                        <div class="history-box">
                            <h4>Previous Tasks / Project Work</h4>

                            <?php if ($historyQuery && mysqli_num_rows($historyQuery) > 0) { ?>
                                <div class="history-list">
                                    <?php while ($task = mysqli_fetch_assoc($historyQuery)) { ?>
                                        <div class="history-item">
                                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                            <span>
                                                Status: <?php echo htmlspecialchars(str_replace('_', ' ', $task['status'])); ?>
                                                · Priority: <?php echo htmlspecialchars($task['priority']); ?>
                                                · Due: <?php echo !empty($task['due_date']) ? htmlspecialchars($task['due_date']) : 'No deadline'; ?>
                                            </span>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="empty-history">
                                    No previous tasks found for this member.
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                <?php } ?>
            <?php } else { ?>
                <div class="empty-history">No active team members found.</div>
            <?php } ?>

        </div>
    </div>

</section>

</form>

<section class="meeting-card" style="margin-top:25px;">
    <h2>Recent Meetings</h2>

    <div class="meetings-list">
        <?php if ($createdMeetingsQuery && mysqli_num_rows($createdMeetingsQuery) > 0) { ?>
            <?php while ($meeting = mysqli_fetch_assoc($createdMeetingsQuery)) { ?>
                <?php
                $meetingId = (int) $meeting['id'];

                $responsesQuery = mysqli_query($conn, "
                    SELECT mm.response_status, u.full_name
                    FROM meeting_members mm
                    JOIN users u ON mm.employee_id = u.id
                    WHERE mm.meeting_id = $meetingId
                    ORDER BY u.full_name ASC
                ");
                ?>

                <div class="meeting-item">
                    <h4><?php echo htmlspecialchars($meeting['title']); ?></h4>

                    <div class="meeting-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($meeting['meeting_date']); ?></span>
                        <span>
                            <i class="fas fa-clock"></i>
                            <?php echo date("h:i A", strtotime($meeting['start_time'])); ?>
                            -
                            <?php echo date("h:i A", strtotime($meeting['end_time'])); ?>
                        </span>
                    </div>

                    <div class="response-row">
                        <?php if ($responsesQuery && mysqli_num_rows($responsesQuery) > 0) { ?>
                            <?php while ($response = mysqli_fetch_assoc($responsesQuery)) { ?>
                                <span class="response-pill <?php echo responseStatusClass($response['response_status']); ?>">
                                    <?php echo htmlspecialchars($response['full_name']); ?>:
                                    <?php echo htmlspecialchars($response['response_status']); ?>
                                </span>
                            <?php } ?>
                        <?php } else { ?>
                            <span class="response-pill pending-status">No invited members</span>
                        <?php } ?>
                    </div>
                </div>

            <?php } ?>
        <?php } else { ?>
            <div class="empty-history">No meetings created yet.</div>
        <?php } ?>
    </div>
</section>

</main>
</div>

</body>
</html>