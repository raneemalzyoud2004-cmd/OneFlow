<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: apply.php");
    exit();
}

$fullName = mysqli_real_escape_string($conn, $_POST["full_name"]);
$email = mysqli_real_escape_string($conn, $_POST["email"]);
$phone = mysqli_real_escape_string($conn, $_POST["phone"]);
$position = mysqli_real_escape_string($conn, $_POST["position_applied"]);
$experience = mysqli_real_escape_string($conn, $_POST["experience"]);
$skills = mysqli_real_escape_string($conn, $_POST["skills"]);
$notes = mysqli_real_escape_string($conn, $_POST["notes"]);

if (!isset($_FILES["cv_file"]) || $_FILES["cv_file"]["error"] !== 0) {
    header("Location: apply.php?error=CV upload failed");
    exit();
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "cv" . DIRECTORY_SEPARATOR;
$databasePath = "uploads/cv/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$originalName = basename($_FILES["cv_file"]["name"]);
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ["pdf", "doc", "docx"];

if (!in_array($fileExtension, $allowedExtensions)) {
    header("Location: apply.php?error=Only PDF, DOC, and DOCX files are allowed");
    exit();
}

$newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $originalName);
$serverFilePath = $uploadDir . $newFileName;

if (!move_uploaded_file($_FILES["cv_file"]["tmp_name"], $serverFilePath)) {
    header("Location: apply.php?error=Could not save CV file");
    exit();
}

$cvFile = mysqli_real_escape_string($conn, $databasePath . $newFileName);

$insert = mysqli_query($conn, "
    INSERT INTO applicants
    (full_name, email, phone, position_applied, experience, skills, cv_file, interview_date, status, notes)
    VALUES
    ('$fullName', '$email', '$phone', '$position', '$experience', '$skills', '$cvFile', NULL, 'Pending', '$notes')
");

if ($insert) {
    header("Location: apply.php?success=1");
    exit();
}

header("Location: apply.php?error=Application could not be submitted");
exit();
?>