<?php
session_start();
include "config.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "count" => 0,
        "notifications" => []
    ]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$countResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $user_id
    AND is_read = 0
");

$count = 0;

if ($countResult) {
    $count = (int) mysqli_fetch_assoc($countResult)['total'];
}

$notifications = [];

$result = mysqli_query($conn, "
    SELECT id, title, message, type, is_read, created_at
    FROM notifications
    WHERE user_id = $user_id
    ORDER BY id DESC
    LIMIT 5
");

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "count" => $count,
    "notifications" => $notifications
]);
?>