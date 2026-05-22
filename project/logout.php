<?php
session_start();
include("config.php");

if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];
    $today = date("Y-m-d");

    mysqli_query($conn, "
        UPDATE login_days
        SET logout_date = '$today'
        WHERE user_id = $user_id
        AND login_date = '$today'
        ORDER BY id DESC
        LIMIT 1
    ");
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: login.php");
exit();
?>