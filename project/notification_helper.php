<?php
function addNotification($conn, $user_id, $role, $title, $message, $type = 'info') {

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, role, title, message, type)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issss",
        $user_id,
        $role,
        $title,
        $message,
        $type
    );

    return $stmt->execute();
}
?>