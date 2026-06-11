<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireLogin();

$propClass = Property::getInstance();
$db        = Database::getInstance();
$userId    = $_SESSION['user_id'];

// CSRF token (basic security)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// REMOVE FROM WISHLIST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid request");
    }

    $propClass->toggleWishlist((int)$_POST['remove_id'], $userId);

    $_SESSION['flash'] = "Wishlist থেকে সরানো হয়েছে!";
    header('Location: ?page=wishlist');
    exit;
}

$wishlist = $propClass->getWishlist($userId);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!-- HEADER -->
<div class="inner-page-header">
  <div class="container">
    <div class="iph-content">
      <div>
        <h1><i class="bi bi-heart-fill text-accent me-2"></i>আমার Wishlist</h1>
        <p><?= count($wishlist) ?> টি property সংরক্ষিত</p>
      </div>

      <a href="?page=listing" class="btn-accent-outline">
        <i class="bi bi-search me-2"></i>Property দেখুন
      </a>
    </div>
  </div>
</div>

<div class="container py-5">

  <!-- FLASH MESSAGE -->
  <?php if ($flash): ?>
    <div class="alert alert-success">
      <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <!-- EMPTY STATE -->
  <?php if (empty($wishlist)): ?>
    <div class="text-center py-5">
      <div style="font-size:60px; opacity:0.3;">💔</div>
      <h3>Wishlist খালি</h3>
      <p>আপনার পছন্দের property ♥ আইকনে যোগ করুন</p>
      <a href="?page=listing" class="btn btn-warning mt-3">
        Property খুঁজুন
      </a>
    </div>

  <?php else: ?>

  <!-- GRID -->
  <div class="row g-4">
    <?php foreach ($wishlist as $prop): ?>

      <?php
      $cover = $prop['cover_image']
        ? UPLOAD_URL . 'properties/' . $prop['cover_image']
        : APP_URL . '/assets/images/no-image.webp';
      ?>

      <div class="col-md-6 col-lg-4">

        <div class="card wishlist-card h-100 shadow-sm">

          <!-- IMAGE -->
          <div class="position-relative">
            <img src="<?= $cover ?>" class="card-img-top" style="height:220px; object-fit:cover;">

            <span class="badge bg-dark position-absolute top-0 start-0 m-2">
              <?= $prop['price_type'] === 'rent' ? 'ভাড়া' : 'বিক্রয়' ?>
            </span>

            <!-- REMOVE BUTTON -->
            <form method="POST" class="position-absolute top-0 end-0 m-2">
              <input type="hidden" name="remove_id" value="<?= $prop['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

              <button class="btn btn-danger btn-sm"
                      onclick="return confirm('Remove from wishlist?')">
                <i class="bi bi-heart-fill"></i>
              </button>
            </form>
          </div>

          <!-- BODY -->
          <div class="card-body">

            <small class="text-muted">
              <i class="bi bi-geo-alt"></i>
              <?= htmlspecialchars($prop['area_name']) ?>
            </small>

            <h5 class="mt-2">
              <a href="?page=property&id=<?= $prop['id'] ?>" class="text-dark text-decoration-none">
                <?= htmlspecialchars($prop['title']) ?>
              </a>
            </h5>

            <h4 class="text-warning">
              ৳<?= number_format($prop['price']) ?>
            </h4>

            <div class="d-flex gap-3 text-muted small mt-2">
              <?php if ($prop['bedrooms']): ?>
                <span><i class="bi bi-door-open"></i> <?= $prop['bedrooms'] ?> bed</span>
              <?php endif; ?>

              <?php if ($prop['size_sqft']): ?>
                <span><i class="bi bi-rulers"></i> <?= number_format($prop['size_sqft']) ?> sqft</span>
              <?php endif; ?>
            </div>

            <a href="?page=property&id=<?= $prop['id'] ?>"
               class="btn btn-outline-warning w-100 mt-3">
              Details দেখুন
            </a>

          </div>
        </div>

      </div>

    <?php endforeach; ?>
  </div>

  <?php endif; ?>

</div>