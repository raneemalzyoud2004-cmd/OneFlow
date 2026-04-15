<?php
session_start();
include("config.php");

$error = "";

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
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {

                if (($row['role'] === 'hr' || $row['role'] === 'employee') && $row['is_blocked'] == 1) {
                    $error = "Your account is blocked. Please contact admin.";
                } else {

                    if ($row['password'] === $password) {

                        $reset_sql = "UPDATE users SET failed_attempts = 0 WHERE id = ?";
                        $reset_stmt = mysqli_prepare($conn, $reset_sql);
                        mysqli_stmt_bind_param($reset_stmt, "i", $row['id']);
                        mysqli_stmt_execute($reset_stmt);
                        mysqli_stmt_close($reset_stmt);

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
                        } else {
                            $error = "Invalid user role.";
                        }

                    } else {

                        if ($row['role'] === 'hr' || $row['role'] === 'employee') {
                            $new_attempts = $row['failed_attempts'] + 1;

                            if ($new_attempts >= 3) {
                                $update_sql = "UPDATE users SET failed_attempts = ?, is_blocked = 1 WHERE id = ?";
                                $update_stmt = mysqli_prepare($conn, $update_sql);
                                mysqli_stmt_bind_param($update_stmt, "ii", $new_attempts, $row['id']);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);

                                $error = "Your account has been blocked after 3 failed attempts.";
                            } else {
                                $update_sql = "UPDATE users SET failed_attempts = ? WHERE id = ?";
                                $update_stmt = mysqli_prepare($conn, $update_sql);
                                mysqli_stmt_bind_param($update_stmt, "ii", $new_attempts, $row['id']);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);

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
        <p style="color:red; margin-bottom:15px; text-align:center;"><?php echo $error; ?></p>
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