<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$filename = "users_report_" . date("Y-m-d_H-i-s") . ".csv";

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$output = fopen("php://output", "w");

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ["ID", "Full Name", "Username", "Role"]);

$query = "SELECT id, full_name, username, role FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['full_name'],
            $row['username'],
            ucfirst($row['role'])
        ]);
    }
}

fclose($output);
exit();
?>