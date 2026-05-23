<?php

session_start();

include("config.php");

$error = "";

mysqli_query($conn, "

    CREATE TABLE IF NOT EXISTS login_days (

        id INT AUTO_INCREMENT PRIMARY KEY,

        user_id INT NOT NULL,

        login_date DATE NOT NULL,

        logout_date DATE NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )

");

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {

        header("Location: dashboardadmin.php");

        exit();

    } elseif ($_SESSION['role'] === 'hr') {

        header("Location: hrdashboard.php");

        exit();

    } elseif ($_SESSION['role'] === 'employee') {

        header("Location: dashboardemployee.php");

        exit();

    } elseif ($_SESSION['role'] === 'teamleader') {

        header("Location: dashboardteamleader.php");

        exit();

    } elseif ($_SESSION['role'] === 'itsupport') {

        header("Location: itsupport_dashboard.php");

        exit();

    }

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);

    $password_input = trim($_POST['password']);

    if (!empty($username) && !empty($password_input)) {

        $sql = "SELECT * FROM users WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $username);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {

                $protectedRoles = ['hr', 'employee', 'teamleader', 'itsupport'];

                if (in_array($row['role'], $protectedRoles) && isset($row['is_blocked']) && $row['is_blocked'] == 1) {

                    $error = "Your account is blocked. Please contact admin.";

                } elseif (in_array($row['role'], $protectedRoles) && isset($row['account_status']) && $row['account_status'] === 'pending_setup') {

                    $error = "Your account is not ready yet. Please complete account setup first.";

                } else {

                    $stored_password = $row['password'];

                    $sha_input = hash('sha256', $password_input);

                 $storedPassword = $row['password'];

$loginOk = false;

/* bcrypt hash */

if (password_verify($password_input, $storedPassword)) {

    $loginOk = true;

}

/* sha256 hash */

elseif (hash('sha256', $password_input) === $storedPassword) {

    $loginOk = true;

}

/* plain text password */

elseif ($storedPassword === $password_input) {

    $loginOk = true;

}

if ($loginOk) {

                        $reset_sql = "UPDATE users SET failed_attempts = 0 WHERE id = ?";

                        $reset_stmt = mysqli_prepare($conn, $reset_sql);

                        if ($reset_stmt) {

                            mysqli_stmt_bind_param($reset_stmt, "i", $row['id']);

                            mysqli_stmt_execute($reset_stmt);

                            mysqli_stmt_close($reset_stmt);

                        }

                        if (isset($row['last_login'])) {

                            $last_login_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";

                            $last_login_stmt = mysqli_prepare($conn, $last_login_sql);

                            if ($last_login_stmt) {

                                mysqli_stmt_bind_param($last_login_stmt, "i", $row['id']);

                                mysqli_stmt_execute($last_login_stmt);

                                mysqli_stmt_close($last_login_stmt);

                            }

                        }

                        $userId = (int)$row['id'];

                        $today = date("Y-m-d");

                        $checkLoginDay = mysqli_query($conn, "

                            SELECT id 

                            FROM login_days 

                            WHERE user_id = $userId 

                            AND login_date = '$today'

                            ORDER BY id DESC

                            LIMIT 1

                        ");

                        if ($checkLoginDay && mysqli_num_rows($checkLoginDay) > 0) {

                            $loginDayRow = mysqli_fetch_assoc($checkLoginDay);

                            $_SESSION['login_day_id'] = $loginDayRow['id'];

                        } else {

                            mysqli_query($conn, "

                                INSERT INTO login_days (user_id, login_date)

                                VALUES ($userId, '$today')

                            ");

                            $_SESSION['login_day_id'] = mysqli_insert_id($conn);

                        }

                        $_SESSION['user_id'] = $row['id'];

                        $_SESSION['full_name'] = $row['full_name'];

                        $_SESSION['username'] = $row['username'];

                        $_SESSION['role'] = $row['role'];

                        if ($row['role'] === 'admin') {

                            header("Location: dashboardadmin.php");

                            exit();

                        } elseif ($row['role'] === 'hr') {

                            header("Location: hrdashboard.php");

                            exit();

                        } elseif ($row['role'] === 'employee') {

                            header("Location: dashboardemployee.php");

                            exit();

                        } elseif ($row['role'] === 'teamleader') {

                            header("Location: dashboardteamleader.php");

                            exit();

                        } elseif ($row['role'] === 'itsupport') {

                            header("Location: itsupport_dashboard.php");

                            exit();

                        } else {

                            $error = "Invalid user role.";

                        }

                    } else {

                        if (in_array($row['role'], $protectedRoles)) {

                            $current_attempts = isset($row['failed_attempts']) ? (int)$row['failed_attempts'] : 0;

                            $new_attempts = $current_attempts + 1;

                            if ($new_attempts >= 3) {

                                $update_sql = "UPDATE users SET failed_attempts = ?, is_blocked = 1 WHERE id = ?";

                                $update_stmt = mysqli_prepare($conn, $update_sql);

                                if ($update_stmt) {

                                    mysqli_stmt_bind_param($update_stmt, "ii", $new_attempts, $row['id']);

                                    mysqli_stmt_execute($update_stmt);

                                    mysqli_stmt_close($update_stmt);

                                }

                                $error = "Your account has been blocked after 3 failed attempts.";

                            } else {

                                $update_sql = "UPDATE users SET failed_attempts = ? WHERE id = ?";

                                $update_stmt = mysqli_prepare($conn, $update_sql);

                                if ($update_stmt) {

                                    mysqli_stmt_bind_param($update_stmt, "ii", $new_attempts, $row['id']);

                                    mysqli_stmt_execute($update_stmt);

                                    mysqli_stmt_close($update_stmt);

                                }

                                $remaining = 3 - $new_attempts;

                                $error = "Invalid password. You have $remaining attempt(s) left before block.";

                            }

                        } else {

                            $error = "Invalid username or password.";

                        }

                    }

                }

            } else {

                $error = "Invalid username or password.";

            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Database query error.";

        }

    } else {

        $error = "Please enter your username and password.";

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OneFlow | Login</title>
<link rel="stylesheet" href="css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-wrapper">
<div class="login-left">
<div class="brand">OneFlow</div>
<div class="welcome-text">
<h1>Welcome Back</h1>
<p>Log in to continue your workflow with clarity and confidence.</p>
</div>
</div>
<div class="login-right">
<div class="form-box">
<h2>Log in</h2>
<?php if (!empty($error)) { ?>
<p style="color:red; margin-bottom:15px; text-align:center;">
<?php echo htmlspecialchars($error); ?>
</p>
<?php } ?>
<form method="POST" action="">
<div class="input-box">
<i class="fa-solid fa-user"></i>
<input type="text" name="username" placeholder="Username" required>
</div>
<div class="input-box">
<i class="fa-solid fa-lock"></i>
<input type="password" name="password" placeholder="Password" required>
</div>
<button type="submit" class="login-btn">Log in</button>
<a href="index.php" class="back-home">← Return to Home</a>
</form>
<div class="logo">
<img src="images/oneflow.png" alt="OneFlow Logo">
</div>
</div>
</div>
</div>
</body>
</html>
 