<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email == "admin@test.com" && $password == "1234") {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
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

      <?php if (isset($error)) { ?>
        <p style="color:red; margin-bottom:15px; text-align:center;"><?php echo $error; ?></p>
      <?php } ?>

      <form method="POST">
        <div class="input-box">
          <i class="fa-solid fa-user"></i>
          <input type="email" name="email" placeholder="Email" required>
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