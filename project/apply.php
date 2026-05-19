<?php
$success = isset($_GET['success']);
$error = isset($_GET['error']) ? $_GET['error'] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply Now - OneFlow</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --oneflow-primary: #0D1E4C;
      --oneflow-secondary: #C48CB3;
      --oneflow-light-pink: #E5C9D7;
      --oneflow-light-blue: #83A6CE;
      --oneflow-dark-blue: #26415E;
      --oneflow-deep-navy: #0B1B32;
      --oneflow-white: #FFFFFF;
      --oneflow-teal: #19b7b5;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, var(--oneflow-deep-navy), var(--oneflow-primary));
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 35px 18px;
    }

    .apply-wrapper {
      width: 100%;
      max-width: 950px;
      background: #ffffff;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 25px 70px rgba(0,0,0,0.25);
      display: grid;
      grid-template-columns: 0.9fr 1.2fr;
    }

    .apply-info {
      background: linear-gradient(160deg, #0D1E4C, #19b7b5);
      color: white;
      padding: 42px 34px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 30px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 24px;
      font-weight: 800;
    }

    .brand i {
      width: 45px;
      height: 45px;
      border-radius: 14px;
      background: rgba(255,255,255,0.14);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #34d399;
    }

    .apply-info h1 {
      font-size: 38px;
      line-height: 1.2;
      margin-bottom: 14px;
    }

    .apply-info p {
      line-height: 1.8;
      color: #edf7ff;
      font-size: 15px;
    }

    .steps {
      display: grid;
      gap: 14px;
    }

    .step {
      background: rgba(255,255,255,0.13);
      border: 1px solid rgba(255,255,255,0.16);
      border-radius: 18px;
      padding: 14px;
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .step span {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      background: white;
      color: var(--oneflow-primary);
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .apply-form {
      padding: 42px 38px;
    }

    .apply-form h2 {
      color: var(--oneflow-primary);
      font-size: 30px;
      margin-bottom: 8px;
    }

    .subtitle {
      color: #64748b;
      margin-bottom: 28px;
      line-height: 1.7;
    }

    .message {
      padding: 14px 16px;
      border-radius: 14px;
      margin-bottom: 18px;
      font-weight: 700;
    }

    .success {
      background: #dcfce7;
      color: #166534;
    }

    .error {
      background: #fee2e2;
      color: #991b1b;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group.full {
      grid-column: span 2;
    }

    label {
      display: block;
      font-weight: 800;
      color: var(--oneflow-primary);
      margin-bottom: 7px;
    }

    input,
    select,
    textarea {
      width: 100%;
      border: 1px solid #d9e2ec;
      border-radius: 14px;
      padding: 13px 14px;
      font-size: 14px;
      outline: none;
      background: #f8fafc;
    }

    textarea {
      min-height: 110px;
      resize: vertical;
    }

    .file-box {
      border: 2px dashed #83A6CE;
      background: #f8fbff;
      border-radius: 18px;
      padding: 18px;
    }

    .actions {
      display: flex;
      gap: 12px;
      margin-top: 8px;
      flex-wrap: wrap;
    }

    .submit-btn,
    .back-btn {
      border: none;
      border-radius: 14px;
      padding: 14px 22px;
      font-weight: 800;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .submit-btn {
      background: var(--oneflow-secondary);
      color: white;
    }

    .back-btn {
      background: #eaf1f8;
      color: var(--oneflow-primary);
    }

    @media (max-width: 850px) {
      .apply-wrapper {
        grid-template-columns: 1fr;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .form-group.full {
        grid-column: span 1;
      }
    }
  </style>
</head>
<body>

<div class="apply-wrapper">

  <div class="apply-info">
    <div>
      <div class="brand">
        <i class="fa-solid fa-leaf"></i>
        OneFlow
      </div>

      <div style="margin-top:45px;">
        <h1>Join our team</h1>
        <p>Submit your information and upload your CV. The HR team will review your application and contact you if you are shortlisted.</p>
      </div>
    </div>

    <div class="steps">
      <div class="step"><span>1</span><p>Fill your application details</p></div>
      <div class="step"><span>2</span><p>Upload your CV file</p></div>
      <div class="step"><span>3</span><p>HR reviews your application</p></div>
    </div>
  </div>

  <div class="apply-form">

    <h2>Application Form</h2>
    <p class="subtitle">Please complete the form below and attach your CV.</p>

    <?php if ($success): ?>
      <div class="message success">Your application has been submitted successfully.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="submit_application.php" method="POST" enctype="multipart/form-data">

      <div class="form-grid">

        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" required>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" required>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="text" name="phone" required>
        </div>

        <div class="form-group">
          <label>Position Applied For</label>
          <select name="position_applied" required>
            <option value="">Select position</option>
            <option value="HR Assistant">HR Assistant</option>
            <option value="Frontend Developer">Frontend Developer</option>
            <option value="Backend Developer">Backend Developer</option>
            <option value="IT Support">IT Support</option>
            <option value="UI/UX Designer">UI/UX Designer</option>
          </select>
        </div>

        <div class="form-group">
          <label>Experience</label>
          <input type="text" name="experience">
        </div>

        <div class="form-group">
          <label>Skills</label>
          <input type="text" name="skills">
        </div>

        <div class="form-group full">
          <label>Notes / Cover Letter</label>
          <textarea name="notes"></textarea>
        </div>

        <div class="form-group full">
          <label>Upload CV</label>

          <div class="file-box">
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" required>
          </div>
        </div>

      </div>

      <div class="actions">

        <button type="submit" class="submit-btn">
          <i class="fa-solid fa-paper-plane"></i>
          Submit Application
        </button>

        <a href="index.php" class="back-btn">
          <i class="fa-solid fa-arrow-left"></i>
          Back Home
        </a>

      </div>

    </form>

  </div>

</div>

</body>
</html>