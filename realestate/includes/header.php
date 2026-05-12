<?php ob_start(); ?>
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth        = Auth::getInstance();
$currentUser = $auth->currentUser();
$currentPage = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= APP_NAME ?> - বাংলাদেশের সেরা রিয়েল এস্টেট প্ল্যাটফর্ম">

  <title><?= APP_NAME ?> | Premium Real Estate Bangladesh</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
  <link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#C5A059">
</head>
<body>

<!-- ============================================
     NAVBAR
============================================ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNavbar">
  <div class="container">

    <!-- LOGO -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>">
      <div class="brand-icon">
        <i class="bi bi-buildings-fill"></i>
      </div>
      <div class="brand-text">
        <span class="brand-name">Real<span class="text-accent">Estate</span></span>
        <span class="brand-tagline">BD Premium</span>
      </div>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- NAV LINKS -->
    <div class="collapse navbar-collapse" id="navMenu">

      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>"
             href="<?= APP_URL ?>?page=home">
            <i class="bi bi-house me-1"></i>হোম
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'listing' ? 'active' : '' ?>"
             href="<?= APP_URL ?>?page=listing">
            <i class="bi bi-grid me-1"></i>প্রপার্টি
          </a>
        </li>

        <!-- Dropdown: Property Type -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-tag me-1"></i>ক্যাটাগরি
          </a>
          <ul class="dropdown-menu dropdown-menu-dark-custom">
            <li>
              <a class="dropdown-item" href="?page=listing&type=apartment">
                <i class="bi bi-building me-2"></i>অ্যাপার্টমেন্ট
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="?page=listing&type=villa">
                <i class="bi bi-house-heart me-2"></i>ভিলা
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="?page=listing&type=commercial">
                <i class="bi bi-shop me-2"></i>কমার্শিয়াল
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="?page=listing&type=land">
                <i class="bi bi-map me-2"></i>জমি
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="?page=listing&price_type=rent">
                <i class="bi bi-key me-2"></i>ভাড়া
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="?page=listing&price_type=sale">
                <i class="bi bi-currency-dollar me-2"></i>বিক্রয়
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="?page=tools">
            <i class="bi bi-calculator me-1"></i>টুলস
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=blog">
            <i class="bi bi-newspaper me-1"></i>ব্লগ
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="?page=contact">
            <i class="bi bi-envelope me-1"></i>যোগাযোগ
          </a>
        </li>
      </ul>

      <!-- RIGHT SIDE -->
      <div class="navbar-right d-flex align-items-center gap-2">

        <?php if ($auth->isLoggedIn() && $currentUser): ?>

          <!-- Wishlist -->
          <a href="?page=wishlist" class="btn btn-icon" title="Wishlist">
            <i class="bi bi-heart"></i>
          </a>

          <!-- Notifications -->
          <a href="?page=notifications" class="btn btn-icon position-relative" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notif-badge"></span>
          </a>

          <!-- User Dropdown -->
          <div class="dropdown">
            <button class="btn user-menu-btn dropdown-toggle" data-bs-toggle="dropdown">
              <?php if ($currentUser['avatar']): ?>
                <img src="<?= UPLOAD_URL ?>avatars/<?= htmlspecialchars($currentUser['avatar']) ?>"
                     class="user-avatar" alt="avatar">
              <?php else: ?>
                <div class="user-avatar-placeholder">
                  <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <span class="d-none d-lg-inline ms-2">
                <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>
              </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom">
              <!-- User Info Header -->
              <li class="dropdown-header-custom">
                <strong><?= htmlspecialchars($currentUser['name']) ?></strong>
                <small><?= htmlspecialchars($currentUser['email']) ?></small>
                <span class="role-badge role-<?= $currentUser['role'] ?>">
                  <?= ucfirst($currentUser['role']) ?>
                </span>
              </li>
              <li><hr class="dropdown-divider"></li>

              <!-- Dashboard -->
              <?php
              $dashPage = match($currentUser['role']) {
                  'admin' => 'admin-dashboard',
                  'agent' => 'agent-dashboard',
                  default => 'buyer-dashboard'
              };
              ?>
              <li>
                <a class="dropdown-item" href="?page=<?= $dashPage ?>">
                  <i class="bi bi-speedometer2 me-2"></i>ড্যাশবোর্ড
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="?page=profile">
                  <i class="bi bi-person me-2"></i>প্রোফাইল
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="?page=wishlist">
                  <i class="bi bi-heart me-2"></i>Wishlist
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="?page=my-inquiries">
                  <i class="bi bi-chat me-2"></i>আমার Inquiry
                </a>
              </li>

              <?php if ($auth->isAgent() || $auth->isAdmin()): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="?page=add-property">
                    <i class="bi bi-plus-circle me-2"></i>Property যোগ করুন
                  </a>
                </li>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="?page=logout">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
              </li>
            </ul>
          </div>

        <?php else: ?>

          <!-- Guest Buttons -->
          <a href="?page=login" class="btn btn-outline-nav">
            <i class="bi bi-person me-1"></i>Login
          </a>
          <a href="?page=register" class="btn btn-accent">
            <i class="bi bi-plus-circle me-1"></i>Register
          </a>

        <?php endif; ?>

        <!-- Language Toggle -->
        <button class="btn btn-icon" id="langToggle" title="Language">
          <i class="bi bi-translate"></i>
        </button>

      </div>
    </div>
  </div>
</nav>

<!-- Navbar scroll effect -->
<script>
window.addEventListener('scroll', () => {
  const nav = document.getElementById('mainNavbar');
  nav.classList.toggle('scrolled', window.scrollY > 50);
});
</script>