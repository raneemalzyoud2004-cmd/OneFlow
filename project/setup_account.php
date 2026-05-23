<?php
session_start();
include("config.php");

$success = "";
$error = "";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid setup link.");
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

$requestQuery = mysqli_query($conn, "
    SELECT *
    FROM requests
    WHERE setup_token = '$token'
    AND status = 'approved'
    AND token_used = 0
    LIMIT 1
");

if (!$requestQuery || mysqli_num_rows($requestQuery) == 0) {
    die("This setup link is invalid or already used.");
}

$requestData = mysqli_fetch_assoc($requestQuery);

$full_name = $requestData['full_name'];
$email = $requestData['email'];
$request_id = (int)$requestData['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $username_safe = mysqli_real_escape_string($conn, $username);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $checkUsername = mysqli_query($conn, "
            SELECT id 
            FROM users 
            WHERE username = '$username_safe'
            LIMIT 1
        ");

        if ($checkUsername && mysqli_num_rows($checkUsername) > 0) {
            $error = "This username is already taken.";
        } else {
            $full_name_safe = mysqli_real_escape_string($conn, $full_name);
            $email_safe = mysqli_real_escape_string($conn, $email);
            $password_safe = mysqli_real_escape_string($conn, $password_hash);

            $insertUser = mysqli_query($conn, "
                INSERT INTO users 
                (full_name, username, email, password, role, account_status)
                VALUES 
                ('$full_name_safe', '$username_safe', '$email_safe', '$password_safe', 'employee', 'active')
            ");

            if ($insertUser) {
                mysqli_query($conn, "
                    UPDATE requests
                    SET token_used = 1
                    WHERE id = $request_id
                ");

                $success = "Your account setup is complete. You can now log in.";
            } else {
                $error = "Error creating your account: " . mysqli_error($conn);
            }
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

.user-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 16px;
    border-radius: 16px;
    margin-bottom: 22px;
}

.user-box strong {
    color: #0D1E4C;
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

.setup-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
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
    <p>Complete your OneFlow employee account using your approved request link.</p>

    <div class="user-box">
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>

    <?php if (!empty($success)) { ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if (empty($success)) { ?>
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Choose your username" required>
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
    <?php } ?>

    <div class="bottom-link">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>