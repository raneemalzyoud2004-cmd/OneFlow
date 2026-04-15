<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="OneFlow" />
    <link rel="shortcut icon" href="favicon.png" />

    <meta
      name="description"
      content="OneFlow - Smart HR and Employee Management Platform"
    />
    <meta
      name="keywords"
      content="HR platform, employee management, admin dashboard, attendance, leave requests"
    />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="fonts/icomoon/style.css" />
    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css" />
    <link rel="stylesheet" href="css/tiny-slider.css" />
    <link rel="stylesheet" href="css/aos.css" />
    <link rel="stylesheet" href="css/style.css" />

    <title>OneFlow - Smart HR Platform</title>

    <style>
      :root {
        --oneflow-primary: #0d1e4c;
        --oneflow-secondary: #c48cb3;
        --oneflow-light-pink: #e5c9d7;
        --oneflow-light-blue: #83a6ce;
        --oneflow-dark-blue: #26415e;
        --oneflow-deep-navy: #0b1b32;
        --oneflow-white: #ffffff;
      }

      body {
        font-family: "Work Sans", sans-serif;
        background: var(--oneflow-deep-navy);
        margin: 0;
        overflow-x: hidden;
      }

      .site-nav {
        background: rgba(11, 27, 50, 0.92);
        padding: 14px 0;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
        animation: navDrop 0.9s ease;
      }

      .menu-bg-wrap {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(38, 65, 94, 0.1);
        border-radius: 20px;
        padding: 12px 28px;
        box-shadow: 0 10px 30px rgba(11, 27, 50, 0.1);
      }

      .site-navigation {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .site-nav .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        margin: 0;
      }

      .site-nav .logo img {
        height: 42px;
        width: auto;
        object-fit: contain;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
      }

      .site-nav .logo span {
        color: var(--oneflow-primary);
        font-size: 22px;
        font-weight: 800;
        letter-spacing: 0.2px;
        line-height: 1;
      }

      .site-nav .site-menu {
        margin: 0;
        padding: 0;
      }

      .site-nav .site-menu > li {
        margin: 0 0 0 22px;
      }

      .site-nav .site-menu > li > a {
        color: var(--oneflow-primary) !important;
        font-weight: 700;
        font-size: 15px;
        transition: 0.3s ease;
      }

      .site-nav .site-menu > li > a:hover {
        color: var(--oneflow-secondary) !important;
      }

      .site-nav .site-menu > li.active > a {
        color: var(--oneflow-secondary) !important;
      }

      .hero {
        position: relative;
        overflow: hidden;
      }

      .hero .img.overlay:before {
        background: linear-gradient(
          135deg,
          rgba(11, 27, 50, 0.86),
          rgba(13, 30, 76, 0.78)
        );
      }

      .hero-slide {
        animation: heroZoom 1.4s ease;
      }

      .hero .heading {
        font-size: 58px;
        font-weight: 800;
        color: var(--oneflow-white);
        line-height: 1.2;
        margin-bottom: 18px;
      }

      .hero-heading-line {
        display: block;
        opacity: 0;
        transform: translateY(70px);
        filter: blur(8px);
        animation: lineReveal 1s forwards;
      }

      .hero-heading-line.line-1 {
        animation-delay: 0.35s;
      }

      .hero-heading-line.line-2 {
        animation-delay: 0.7s;
      }

      .hero-subtitle {
        color: #f3eaf0;
        font-size: 18px;
        max-width: 850px;
        margin: 20px auto 30px auto;
        line-height: 1.8;
        opacity: 0;
        transform: translateY(50px);
        filter: blur(5px);
        animation: subtitleReveal 1s ease forwards;
        animation-delay: 1.05s;
      }

      .hero-buttons {
        display: flex;
        justify-content: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 10px;
        opacity: 0;
        transform: translateY(45px);
        animation: buttonsReveal 0.9s ease forwards;
        animation-delay: 1.35s;
      }

      .btn-oneflow-primary {
        background: var(--oneflow-secondary);
        color: var(--oneflow-white) !important;
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
      }

      .btn-oneflow-primary:hover {
        background: #b97ba5;
      }

      .btn-oneflow-outline {
        background: transparent;
        color: var(--oneflow-white) !important;
        border: 2px solid var(--oneflow-light-pink);
        border-radius: 12px;
        padding: 12px 30px;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
      }

      .btn-oneflow-outline:hover {
        background: var(--oneflow-light-pink);
        color: var(--oneflow-deep-navy) !important;
      }

      .btn-oneflow-light {
        background: var(--oneflow-light-blue);
        color: var(--oneflow-deep-navy) !important;
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-weight: 700;
        text-decoration: none;
        display: inline-block;
      }

      .btn-oneflow-light:hover {
        background: #7398c2;
      }

      .section-title {
        color: var(--oneflow-primary);
        font-weight: 800;
        margin-bottom: 16px;
      }

      .section-subtitle {
        color: #5f6672;
        max-width: 720px;
        margin: 0 auto 40px auto;
        line-height: 1.8;
      }

      .feature-card,
      .role-card,
      .workflow-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(13, 30, 76, 0.08);
        height: 100%;
        transition: 0.35s ease;
      }

      .feature-card:hover,
      .role-card:hover,
      .workflow-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 18px 40px rgba(13, 30, 76, 0.14);
      }

      .feature-icon,
      .role-icon,
      .workflow-number {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        font-size: 24px;
        font-weight: 800;
      }

      .feature-icon {
        background: var(--oneflow-light-pink);
        color: var(--oneflow-primary);
      }

      .role-icon {
        background: var(--oneflow-light-blue);
        color: var(--oneflow-deep-navy);
      }

      .workflow-number {
        background: var(--oneflow-primary);
        color: var(--oneflow-white);
      }

      .feature-card h3,
      .role-card h3,
      .workflow-card h3 {
        font-size: 22px;
        font-weight: 700;
        color: var(--oneflow-deep-navy);
        margin-bottom: 12px;
      }

      .feature-card p,
      .role-card p,
      .workflow-card p {
        color: #667085;
        margin-bottom: 0;
        line-height: 1.8;
      }

      .roles-section {
        background: #f8f6fa;
      }

      .workflow-section {
        background: linear-gradient(
          180deg,
          #ffffff 0%,
          rgba(229, 201, 215, 0.22) 100%
        );
      }

      .cta-box {
        background: linear-gradient(
          135deg,
          var(--oneflow-primary),
          var(--oneflow-dark-blue)
        );
        border-radius: 24px;
        padding: 50px 30px;
        text-align: center;
        color: white;
      }

      .cta-box h2 {
        color: white;
        font-weight: 800;
        margin-bottom: 16px;
      }

      .cta-box p {
        color: #ebeff8;
        max-width: 700px;
        margin: 0 auto 24px auto;
        line-height: 1.8;
      }

      .site-footer {
        background: var(--oneflow-deep-navy);
        color: #d8deea;
      }

      .site-footer h3,
      .site-footer a {
        color: #ffffff !important;
      }

      .site-footer .links a {
        color: #d8deea !important;
      }

      .site-footer .links a:hover {
        color: var(--oneflow-light-pink) !important;
      }

      .text-primary,
      .heading.text-primary {
        color: var(--oneflow-primary) !important;
      }

      .btn.btn-primary {
        background: var(--oneflow-secondary) !important;
        border-color: var(--oneflow-secondary) !important;
      }

      @keyframes navDrop {
        from {
          opacity: 0;
          transform: translateY(-45px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes heroZoom {
        from {
          opacity: 0;
          transform: scale(1.08);
          filter: blur(10px);
        }
        to {
          opacity: 1;
          transform: scale(1);
          filter: blur(0);
        }
      }

      @keyframes lineReveal {
        0% {
          opacity: 0;
          transform: translateY(70px);
          filter: blur(8px);
          letter-spacing: 1px;
        }
        60% {
          opacity: 1;
          transform: translateY(-8px);
          filter: blur(0);
        }
        100% {
          opacity: 1;
          transform: translateY(0);
          filter: blur(0);
          letter-spacing: 0;
        }
      }

      @keyframes subtitleReveal {
        0% {
          opacity: 0;
          transform: translateY(50px);
          filter: blur(5px);
        }
        100% {
          opacity: 1;
          transform: translateY(0);
          filter: blur(0);
        }
      }

      @keyframes buttonsReveal {
        0% {
          opacity: 0;
          transform: translateY(45px);
        }
        100% {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @media (max-width: 991px) {
        .site-nav .logo img {
          height: 34px;
        }

        .site-nav .logo span {
          font-size: 22px;
        }

        .hero .heading {
          font-size: 38px;
        }

        .hero-subtitle {
          font-size: 16px;
        }

        .site-nav .site-menu > li.active > a {
          color: var(--oneflow-secondary) !important;
        }
      }
    </style>
  </head>

  <body>
    <div class="site-mobile-menu site-navbar-target">
      <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close">
          <span class="icofont-close js-menu-toggle"></span>
        </div>
      </div>
      <div class="site-mobile-menu-body"></div>
    </div>

    <nav class="site-nav">
      <div class="container">
        <div class="menu-bg-wrap">
          <div class="site-navigation">
            <a href="index.php" class="logo m-0 float-start">
              <img src="images/oneflow.png" alt="OneFlow Logo" />
              <span>OneFlow</span>
            </a>

            <ul
              class="js-clone-nav d-none d-lg-inline-block text-start site-menu float-end"
            >
              <li><a href="index.php">Home</a></li>
              <li class="active"><a href="#features">Features</a></li>
              <li><a href="#roles">Roles</a></li>
              <li><a href="#workflow">Workflow</a></li>
              <li><a href="request.php">Request</a></li>
              <li><a href="login.php">Login</a></li>
            </ul>

            <a
              href="#"
              class="burger light me-auto float-end mt-1 site-menu-toggle js-menu-toggle d-inline-block d-lg-none"
              data-toggle="collapse"
              data-target="#main-navbar"
            >
              <span></span>
            </a>
          </div>
        </div>
      </div>
    </nav>

    <div class="hero">
      <div class="hero-slide">
        <div
          class="img overlay"
          style="background-image: url('images/hero_bg_3.jpg')"
        ></div>
        <div
          class="img overlay"
          style="background-image: url('images/hero_bg_2.jpg')"
        ></div>
        <div
          class="img overlay"
          style="background-image: url('images/hero_bg_1.jpg')"
        ></div>
      </div>

      <div class="container">
        <div class="row justify-content-center align-items-center">
          <div class="col-lg-10 text-center">
            <h1 class="heading">
              <span class="hero-heading-line line-1">Simplify HR, employee</span>
              <span class="hero-heading-line line-2">management, and daily workflows with OneFlow</span>
            </h1>

            <p class="hero-subtitle">
              OneFlow helps organizations manage employees, attendance, leave
              requests, HR tasks, and admin control through one smart platform
              with role-based dashboards for Employees, HR, and Admins.
            </p>

            <div class="hero-buttons">
              <a href="login.php" class="btn-oneflow-primary">Login</a>
              <a href="request.php" class="btn-oneflow-light">Request</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="features" class="section">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-lg-8">
            <h2 class="section-title">Core Features of OneFlow</h2>
            <p class="section-subtitle">
              Designed to support modern teams with a smooth, organized, and
              professional employee management experience.
            </p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <div class="feature-card">
              <div class="feature-icon">01</div>
              <h3>Attendance Tracking</h3>
              <p>
                Monitor employee attendance, check-ins, work hours, and daily
                activity from one centralized dashboard.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
              <div class="feature-icon">02</div>
              <h3>Leave Requests</h3>
              <p>
                Let employees submit leave requests easily while HR reviews,
                approves, or rejects them efficiently.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
              <div class="feature-icon">03</div>
              <h3>Employee Profiles</h3>
              <p>
                Store employee information, department details, records, and key
                updates in one secure place.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <div class="feature-card">
              <div class="feature-icon">04</div>
              <h3>Task Monitoring</h3>
              <p>
                Track internal workflows and task progress to improve
                productivity and visibility across teams.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card">
              <div class="feature-icon">05</div>
              <h3>Role-Based Access</h3>
              <p>
                Each user gets the right dashboard and permissions based on
                their role: Employee, HR, or Admin.
              </p>
            </div>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card">
              <div class="feature-icon">06</div>
              <h3>Reports & Insights</h3>
              <p>
                Give management and HR clear insights into operations,
                performance, and workforce activity.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="roles" class="section roles-section">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-lg-8">
            <h2 class="section-title">Built for Every Role</h2>
            <p class="section-subtitle">
              OneFlow delivers a tailored experience for each type of user in
              the organization.
            </p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4" data-aos="fade-up">
            <div class="role-card">
              <div class="role-icon">E</div>
              <h3>Employee Dashboard</h3>
              <p>
                Employees can view attendance, submit leave requests, update
                profiles, and follow assigned tasks easily.
              </p>
            </div>
          </div>

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="role-card">
              <div class="role-icon">HR</div>
              <h3>HR Dashboard</h3>
              <p>
                HR teams can manage requests, employee records, attendance
                monitoring, and workforce-related processes.
              </p>
            </div>
          </div>

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="role-card">
              <div class="role-icon">A</div>
              <h3>Admin Dashboard</h3>
              <p>
                Admins get full control over users, settings, system access, and
                platform-wide visibility.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="workflow" class="section workflow-section">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-lg-8">
            <h2 class="section-title">How OneFlow Works</h2>
            <p class="section-subtitle">
              A simple workflow that makes employee and HR processes faster,
              clearer, and more organized.
            </p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4" data-aos="fade-up">
            <div class="workflow-card">
              <div class="workflow-number">1</div>
              <h3>Login by Role</h3>
              <p>
                Users log in based on their role and access the dashboard that
                matches their responsibilities.
              </p>
            </div>
          </div>

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="workflow-card">
              <div class="workflow-number">2</div>
              <h3>Manage Daily Tasks</h3>
              <p>
                Employees and HR teams handle attendance, leave, records, and
                tasks through one connected system.
              </p>
            </div>
          </div>

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="workflow-card">
              <div class="workflow-number">3</div>
              <h3>Track and Improve</h3>
              <p>
                Admins and HR review reports, monitor workflows, and make better
                decisions with clear data.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="section">
      <div class="container">
        <div class="cta-box" data-aos="fade-up">
          <h2>Ready to organize your team with OneFlow?</h2>
          <p>
            Start with a clean landing page experience, then connect it to
            login, signup, and role-based dashboards for a complete HR
            management platform.
          </p>
          <div class="hero-buttons" style="opacity: 1; transform: none; animation: none;">
            <a href="login.php" class="btn-oneflow-primary">Go to Login</a>
            <a href="request.php" class="btn-oneflow-light">Request</a>
          </div>
        </div>
      </div>
    </div>

    <div class="site-footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-4">
            <div class="widget">
              <h3>OneFlow</h3>
              <p>
                A smart HR and employee management platform designed to simplify
                internal processes and improve organization workflow.
              </p>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="widget">
              <h3>Pages</h3>
              <ul class="list-unstyled links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#roles">Roles</a></li>
                <li><a href="#workflow">Workflow</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="widget">
              <h3>Access</h3>
              <ul class="list-unstyled links">
                <li><a href="login.php">Login</a></li>
                <li><a href="request.php">Request</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="row mt-5">
          <div class="col-12 text-center">
            <p>
              Copyright &copy;
              <script>
                document.write(new Date().getFullYear());
              </script>
              OneFlow. All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div id="overlayer"></div>
    <div class="loader">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/tiny-slider.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/navbar.js"></script>
    <script src="js/counter.js"></script>
    <script src="js/custom.js"></script>

    <script>
      window.addEventListener("scroll", function () {
        let navbar = document.querySelector(".site-nav");

        if (window.scrollY > 50) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
      });
    </script>
  </body>
</html>