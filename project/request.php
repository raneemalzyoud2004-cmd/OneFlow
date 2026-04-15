<?php
include("config.php");

$success = "";
$error = "";

$name = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || !preg_match("/^07[0-9]{8}$/", $phone)) {
        $error = "Please fill all fields correctly!";
    } else {
        $name_safe = mysqli_real_escape_string($conn, $name);
        $email_safe = mysqli_real_escape_string($conn, $email);
        $phone_safe = mysqli_real_escape_string($conn, $phone);

        $checkRequestQuery = "SELECT id FROM requests 
                              WHERE email = '$email_safe' AND status = 'pending' 
                              LIMIT 1";
        $checkRequestResult = mysqli_query($conn, $checkRequestQuery);

        if ($checkRequestResult && mysqli_num_rows($checkRequestResult) > 0) {
            $error = "You already have a pending request!";
        } else {
            $insertQuery = "INSERT INTO requests (full_name, email, phone, status)
                            VALUES ('$name_safe', '$email_safe', '$phone_safe', 'pending')";

            if (mysqli_query($conn, $insertQuery)) {
                $success = "Request sent successfully!";

                // تصفير الداتا بعد النجاح
                $name = "";
                $email = "";
                $phone = "";
            } else {
                $error = "Something went wrong while sending the request!";
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
    <title>Request - OneFlow</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/request.css">
</head>

<body class="request-page">

    <div class="request-wrapper">
        <div class="request-card">

            <h2 class="request-title">Request Access</h2>

            <form method="POST" class="request-form">
                <input 
                    type="text" 
                    name="name" 
                    class="request-input" 
                    placeholder="Full Name"
                    value="<?php echo htmlspecialchars($name); ?>"
                >

                <input 
                    type="email" 
                    name="email" 
                    class="request-input" 
                    placeholder="Email"
                    value="<?php echo htmlspecialchars($email); ?>"
                >

                <input 
                    type="text" 
                    name="phone" 
                    class="request-input" 
                    placeholder="07XXXXXXXX"
                    value="<?php echo htmlspecialchars($phone); ?>"
                >

                <button type="submit" class="request-btn">Send Request</button>
            </form>

            <a href="index.php" class="back-home-btn">Return to Home</a>
        </div>
    </div>

    <?php if ($success != ""): ?>
        <div id="successPopup" class="request-popup success-popup show-popup">
            <div class="popup-icon">✓</div>
            <div class="popup-text">
                <h4>Success</h4>
                <p><?php echo $success; ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <div id="errorPopup" class="request-popup error-popup show-popup">
            <div class="popup-icon">!</div>
            <div class="popup-text">
                <h4>Error</h4>
                <p><?php echo $error; ?></p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const successPopup = document.getElementById("successPopup");
        const errorPopup = document.getElementById("errorPopup");

        if (successPopup) {
            setTimeout(function () {
                successPopup.classList.add("hide-popup");
            }, 4000);
        }

        if (errorPopup) {
            setTimeout(function () {
                errorPopup.classList.add("hide-popup");
            }, 4000);
        }
    </script>

</body>
</html>