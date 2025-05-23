<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <title>GL System</title>
  <link rel="icon" href="<?=base_url('favicon.ico')?>" type="image/x-icon" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- MDB -->
  <link rel="stylesheet" href="<?=base_url('assets/login/bootstrap-login-form.min.css')?>" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card {
      border: none;
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    }

    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .illustration-img {
      max-width: 450px;
      filter: drop-shadow(0 10px 10px rgba(0, 0, 0, 0.2));
      transition: transform 0.3s ease;
    }

    .illustration-img:hover {
      transform: translateY(-10px);
    }

    .btn-login {
      background: linear-gradient(45deg, #667eea, #764ba2);
      border: none;
      border-radius: 30px;
      padding: 12px 35px;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      background: linear-gradient(45deg, #764ba2, #667eea);
    }

    .brand-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #333;
      margin-bottom: 0.5rem;
    }

    .brand-subtitle {
      color: #666;
      font-size: 1rem;
      margin-bottom: 2rem;
    }

    .alert {
      border-radius: 10px;
      border: none;
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .copyright {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
      position: fixed;
      bottom: 1rem;
      width: 100%;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="container">
      <div class="row align-items-center justify-content-center">
        <div class="col-md-6 d-none d-md-block">
          <img src="<?=base_url('assets/login/draw2.webp')?>" class="illustration-img img-fluid" alt="Login Illustration">
        </div>
        <div class="col-md-5">
          <div class="card p-4 p-md-5">
            <?php if (session()->getFlashdata('error')) : ?>
              <div class="alert" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('error') ?>
              </div>
            <?php endif; ?>

            <div class="text-center mb-4">
              <h1 class="brand-title">GL System</h1>
              <p class="brand-subtitle">Login menggunakan SSO untuk mengakses sistem</p>
            </div>

            <div class="text-center">
              <a href="<?=base_url('login-sso')?>" class="btn btn-login btn-lg text-white">
                <i class="fas fa-sign-in-alt me-2"></i>
                Login dengan SSO
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="copyright">
    Copyright © <?=date('Y')?> GL System. All rights reserved.
  </div>

  <!-- MDB -->
  <script src="<?=base_url('assets/login/mdb.min.js')?>"></script>
  <script>
    // Add smooth scroll animation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  </script>
</body>

</html>