<?php
session_start();
include("config.php");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, $password);

        $userQuery = "SELECT * FROM users WHERE username = '$username' AND role = 'employee' LIMIT 1";
        $userResult = mysqli_query($conn, $userQuery);

        if ($userResult && mysqli_num_rows($userResult) > 0) {
            $user = mysqli_fetch_assoc($userResult);

            if ($user['account_status'] === 'active') {
                $error = "This account is already active. Please log in.";
            } else {
                $checkEmailQuery = "SELECT id FROM users WHERE email = '$email' AND username != '$username' LIMIT 1";
                $checkEmailResult = mysqli_query($conn, $checkEmailQuery);

                if ($checkEmailResult && mysqli_num_rows($checkEmailResult) > 0) {
                    $error = "This email is already used by another account.";
                } else {
                    $updateQuery = "UPDATE users 
                                    SET email = '$email',
                                        password = '$password',
                                        account_status = 'active'
                                    WHERE username = '$username'";

                    if (mysqli_query($conn, $updateQuery)) {
                        $success = "Your account setup is complete. You can now log in.";
                    } else {
                        $error = "Error completing setup. Please try again.";
                    }
                }
            }
        } else {
            $error = "Username not found. Please contact admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup Account - OneFlow</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #f8fbff, #eef4f8);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .setup-card {
      width: 100%;
      max-width: 520px;
      background: white;
      border-radius: 24px;
      padding: 35px;
      box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
      border: 1px solid #e5eef5;
    }

    .setup-card h1 {
      font-size: 34px;
      color: #0f172a;
      margin-bottom: 10px;
    }

    .setup-card p {
      color: #64748b;
      font-size: 15px;
      margin-bottom: 24px;
      line-height: 1.7;
    }

    .message {
      padding: 14px 16px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-size: 14px;
      font-weight: 600;
    }

    .message.success {
      background: #dcfce7;
      color: #166534;
    }

    .message.error {
      background: #fee2e2;
      color: #991b1b;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #0f172a;
      font-weight: 700;
      font-size: 14px;
    }

    .form-group input {
      width: 100%;
      padding: 14px 16px;
      border-radius: 14px;
      border: 1px solid #dbe7f0;
      outline: none;
      font-size: 14px;
      background: #f8fbff;
    }

    .form-group input:focus {
      border-color: #14b8a6;
      background: white;
    }

    .setup-btn {
      width: 100%;
      border: none;
      padding: 14px;
      border-radius: 14px;
      background: linear-gradient(90deg, #0ea5a4, #14b8a6);
      color: white;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 6px;
    }

    .bottom-link {
      text-align: center;
      margin-top: 18px;
    }

    .bottom-link a {
      color: #0ea5a4;
      text-decoration: none;
      font-weight: 700;
    }
  </style>
</head>
<body>

  <div class="setup-card">
    <h1>Setup Account</h1>
    <p>Complete your employee account by entering your username, email, and password.</p>

    <?php if (!empty($success)) { ?>
      <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if (!empty($error)) { ?>
      <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Create your password" required>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Confirm your password" required>
      </div>

      <button type="submit" class="setup-btn">Complete Setup</button>
    </form>

    <div class="bottom-link">
      <a href="logout.php">Back to Login</a>
    </div>
  </div>

</body>
</html>