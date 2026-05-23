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
      --primary: #0D1E4C;
      --deep-navy: #0B1B32;
      --dark-blue: #26415E;
      --secondary: #C48CB3;
      --light-pink: #E5C9D7;
      --light-blue: #83A6CE;
      --white: #FFFFFF;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #fff7fb, #f4e8ef);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 35px 20px;
    }

    .apply-wrapper {
      width: 100%;
      max-width: 1180px;
      min-height: 720px;
      display: grid;
      grid-template-columns: 0.95fr 1.05fr;
      border: 4px solid var(--dark-blue);
      border-radius: 30px;
      overflow: hidden;
      background: var(--white);
      box-shadow: 0 30px 80px rgba(13, 30, 76, 0.22);
    }

    .apply-left {
      background:
        radial-gradient(circle at top right, rgba(196, 140, 179, 0.22), transparent 35%),
        linear-gradient(135deg, var(--deep-navy), var(--primary), var(--dark-blue));
      color: var(--white);
      padding: 45px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .brand {
      font-size: 34px;
      font-weight: 900;
      color: var(--secondary);
      letter-spacing: 1px;
    }

    .left-content h1 {
      font-size: 54px;
      color: var(--light-pink);
      line-height: 1.1;
      margin-bottom: 18px;
    }

    .left-content p {
      font-size: 17px;
      line-height: 1.8;
      color: #f9f9f9;
      max-width: 430px;
    }

    .line {
      width: 110px;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary), var(--light-blue));
      border-radius: 20px;
      margin-top: 28px;
    }

    .info-cards {
      display: grid;
      gap: 14px;
      margin-top: 35px;
    }

    .info-card {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px;
      border-radius: 18px;
      background: rgba(255,255,255,0.10);
      border: 1px solid rgba(255,255,255,0.16);
      backdrop-filter: blur(8px);
    }

    .info-card i {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--deep-navy);
      background: var(--light-pink);
      font-size: 18px;
    }

    .info-card h3 {
      color: var(--light-pink);
      font-size: 15px;
      margin-bottom: 3px;
    }

    .info-card span {
      font-size: 13px;
      color: #eef4ff;
    }

    .apply-right {
      background: var(--light-pink);
      padding: 38px 52px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-box {
      width: 100%;
      max-width: 560px;
      background: rgba(255,255,255,0.22);
      padding: 28px;
      border-radius: 28px;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.22);
    }

    .form-box h2 {
      text-align: center;
      color: var(--deep-navy);
      font-size: 44px;
      letter-spacing: 2px;
      margin-bottom: 8px;
    }

    .subtitle {
      text-align: center;
      color: var(--dark-blue);
      margin-bottom: 24px;
      font-size: 15px;
    }

    .message {
      padding: 12px;
      border-radius: 14px;
      margin-bottom: 15px;
      font-weight: 700;
      text-align: center;
    }

    .success {
      background: #e6f7ed;
      color: #166534;
    }

    .error {
      background: #ffe2e2;
      color: #991b1b;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px 18px;
    }

    .form-group.full {
      grid-column: span 2;
    }

    label {
      display: block;
      font-weight: 900;
      color: var(--deep-navy);
      margin-bottom: 7px;
      font-size: 15px;
    }

    input,
    select,
    textarea {
      width: 100%;
      border: none;
      outline: none;
      border-radius: 22px;
      padding: 14px 17px;
      background: rgba(255,255,255,0.82);
      color: var(--deep-navy);
      font-size: 14px;
      box-shadow: inset 0 0 0 1px rgba(13, 30, 76, 0.08);
      transition: 0.2s;
    }

    input:focus,
    select:focus,
    textarea:focus {
      background: var(--white);
      box-shadow: inset 0 0 0 2px var(--light-blue), 0 8px 18px rgba(38,65,94,0.12);
    }

    textarea {
      min-height: 95px;
      resize: vertical;
      border-radius: 18px;
    }

    .file-box {
      border: 2px dashed var(--light-blue);
      background: rgba(255,255,255,0.60);
      border-radius: 18px;
      padding: 13px;
    }

    .file-box input {
      background: transparent;
      box-shadow: none;
      padding: 5px;
      border-radius: 0;
    }

    .actions {
      margin-top: 22px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .submit-btn {
      width: 100%;
      border: none;
      border-radius: 26px;
      padding: 16px;
      font-size: 16px;
      font-weight: 900;
      color: white;
      cursor: pointer;
      background: linear-gradient(90deg, var(--primary), var(--light-blue));
      box-shadow: 0 15px 28px rgba(38, 65, 94, 0.28);
      transition: 0.2s;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 35px rgba(38, 65, 94, 0.35);
    }

    .back-home {
      text-align: center;
      color: var(--dark-blue);
      font-weight: 800;
      text-decoration: none;
    }

    .back-home:hover {
      text-decoration: underline;
    }

    .mini-note {
      margin-top: 18px;
      text-align: center;
      color: var(--dark-blue);
      font-size: 13px;
      line-height: 1.6;
    }

    @media (max-width: 950px) {
      .apply-wrapper {
        grid-template-columns: 1fr;
      }

      .apply-left {
        min-height: 420px;
      }
    }

    @media (max-width: 650px) {
      .apply-right {
        padding: 28px 22px;
      }

      .form-grid {
        grid-template-columns: 1fr;
      }

      .form-group.full {
        grid-column: span 1;
      }

      .left-content h1 {
        font-size: 40px;
      }

      .form-box h2 {
        font-size: 34px;
      }
    }
  </style>
</head>

<body>

<div class="apply-wrapper">

  <div class="apply-left">
    <div class="brand">OneFlow</div>

    <div class="left-content">
      <h1>Join Our Team</h1>
      <p>
        Submit your application and let the HR team review your profile.
        We are looking for talented people who can grow with OneFlow.
      </p>
      <div class="line"></div>

      <div class="info-cards">
        <div class="info-card">
          <i class="fa-solid fa-file-lines"></i>
          <div>
            <h3>Step 1</h3>
            <span>Fill your application details carefully.</span>
          </div>
        </div>

        <div class="info-card">
          <i class="fa-solid fa-upload"></i>
          <div>
            <h3>Step 2</h3>
            <span>Upload your CV in PDF, DOC, or DOCX format.</span>
          </div>
        </div>

        <div class="info-card">
          <i class="fa-solid fa-user-check"></i>
          <div>
            <h3>Step 3</h3>
            <span>HR will review and shortlist suitable applicants.</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="apply-right">
    <div class="form-box">

      <h2>Apply Now</h2>
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
            <input type="text" name="experience" placeholder="e.g. 1 year">
          </div>

          <div class="form-group">
            <label>Skills</label>
            <input type="text" name="skills" placeholder="e.g. PHP, SQL, UI/UX">
          </div>

          <div class="form-group full">
            <label>Notes / Cover Letter</label>
            <textarea name="notes" placeholder="Write a short note about yourself..."></textarea>
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

          <a href="index.php" class="back-home">← Return to Home</a>
        </div>

        <p class="mini-note">
          After submitting, your application will appear for HR review.
        </p>

      </form>

    </div>
  </div>

</div>

</body>
</html>