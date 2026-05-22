<?php

function addNotification($conn, $user_id, $title, $message, $type = 'info')
{
    $stmt = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, type, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ");

    $stmt->bind_param("isss", $user_id, $title, $message, $type);

    $stmt->execute();
}
?>