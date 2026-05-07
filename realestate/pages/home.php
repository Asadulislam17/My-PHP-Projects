<?php
require_once __DIR__ . '/../classes/Property.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth     = Auth::getInstance();
$property = Property::getInstance();

// Featured Properties
$featured = $property->getAll(['featured' => true], 1, 6);

// Recently Viewed (logged in user)
$recentlyViewed = [];
if ($auth->isLoggedIn()) {
    $recentlyViewed = $property->getRecentlyViewed($_SESSION['user_id'], 4);
}

// Stats (dummy — পরে DB থেকে আনবে)
$stats = [
    'properties' => '12,000+',
    'agents'     => '800+',
    'cities'     => '64',
    'happy'      => '25,000+'
];
?>

<!-- ============================================
     HERO SECTION
============================================ -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="hero-content container">

    <div class="hero-text" data-aos="fade-up">
      <span class="hero-badge">
        <i class="bi bi-star-fill me-1"></i> বাংলাদেশের #১ রিয়েল এস্টেট প্ল্যাটফর্ম
      </span>
      <h1 class="hero-title">
        আপনার স্বপ্নের <br>
        <span class="text-accent">বাড়ি খুঁজুন</span>
      </h1>
      <p class="hero-subtitle">
        ১২,০০০+ যাচাইকৃত প্রপার্টি থেকে বেছে নিন
      </p>
    </div>

    <!-- SEARCH BAR -->
    <div class="hero-search-card" data-aos="fade-up" data-aos-delay="100">

      <!-- Tabs: Sale / Rent -->
      <div class="search-tabs">
        <button class="search-tab active" data-type="sale">
          <i class="bi bi-house-door me-1"></i>কিনুন
        </button>
        <button class="search-tab" data-type="rent">
          <i class="bi bi-key me-1"></i>ভাড়া নিন
        </button>
      </div>

      <form class="search-form" action="index.php" method="GET">
        <input type="hidden" name="page" value="listing">
        <input type="hidden" name="price_type" id="searchPriceType" value="sale">

        <div class="search-fields">

          <!-- Location -->
          <div class="search-field">
            <label><i class="bi bi-geo-alt text-accent"></i> এলাকা</label>
            <input type="text" name="keyword" class="search-input"
                   placeholder="গুলশান, ধানমন্ডি, বনানী..."
                   id="locationSearch" autocomplete="off">
            <!-- Suggestions -->
            <div class="search-suggestions" id="searchSuggestions"></div>
          </div>

          <div class="search-divider"></div>

          <!-- Property Type -->
          <div class="search-field">
            <label><i class="bi bi-building text-accent"></i> ধরন</label>
            <select name="type" class="search-input">
              <option value="">সব ধরন</option>
              <option value="apartment">অ্যাপার্টমেন্ট</option>
              <option value="villa">ভিলা</option>
              <option value="commercial">কমার্শিয়াল</option>
              <option value="land">জমি</option>
              <option value="office">অফিস</option>
            </select>
          </div>

          <div class="search-divider"></div>

          <!-- Budget -->
          <div class="search-field">
            <label><i class="bi bi-cash-stack text-accent"></i> বাজেট</label>
            <select name="price_max" class="search-input">
              <option value="">যেকোনো মূল্য</option>
              <option value="2000000">২০ লাখের নিচে</option>
              <option value="5000000">৫০ লাখের নিচে</option>
              <option value="10000000">১ কোটির নিচে</option>
              <option value="20000000">২ কোটির নিচে</option>
              <option value="50000000">৫ কোটির নিচে</option>
            </select>
          </div>

          <button type="submit" class="search-btn">
            <i class="bi bi-search me-2"></i>খুঁজুন
          </button>

        </div>
      </form>

      <!-- Popular Searches -->
      <div class="popular-searches">
        <span>জনপ্রিয়:</span>
        <a href="?page=listing&keyword=gulshan">গুলশান</a>
        <a href="?page=listing&keyword=dhanmondi">ধানমন্ডি</a>
        <a href="?page=listing&type=apartment">অ্যাপার্টমেন্ট</a>
        <a href="?page=listing&price_type=rent">ভাড়া</a>
      </div>
    </div>

    <!-- Stats -->
    <div class="hero-stats" data-aos="fade-up" data-aos-delay="200">
      <?php foreach ($stats as $label => $val): ?>
      <div class="stat-item">
        <strong><?= $val ?></strong>
        <span>
          <?= match($label) {
            'properties' => 'প্রপার্টি',
            'agents'     => 'এজেন্ট',
            'cities'     => 'জেলা',
            'happy'      => 'সন্তুষ্ট গ্রাহক'
          } ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ============================================
     CATEGORIES
============================================ -->
<section class="section-pad bg-light-custom">
  <div class="container">

    <div class="section-header text-center">
      <span class="section-badge">ক্যাটাগরি</span>
      <h2>প্রপার্টির ধরন বেছে নিন</h2>
    </div>

    <div class="row g-3 mt-3">
      <?php
      $categories = [
        ['icon' => 'bi-building',     'label' => 'অ্যাপার্টমেন্ট', 'type' => 'apartment', 'count' => '৪,২০০+', 'color' => 'blue'],
        ['icon' => 'bi-house-heart',  'label' => 'ভিলা',            'type' => 'villa',      'count' => '৮৫০+',   'color' => 'gold'],
        ['icon' => 'bi-shop',         'label' => 'কমার্শিয়াল',     'type' => 'commercial', 'count' => '২,১০০+', 'color' => 'green'],
        ['icon' => 'bi-map',          'label' => 'জমি',             'type' => 'land',       'count' => '৩,৫০০+', 'color' => 'earth'],
        ['icon' => 'bi-monitor',      'label' => 'অফিস স্পেস',      'type' => 'office',     'count' => '৯৮০+',   'color' => 'purple'],
        ['icon' => 'bi-key',          'label' => 'ভাড়া',           'type' => '',           'count' => '৬,৪০০+', 'color' => 'red', 'price_type' => 'rent'],
      ];
      foreach ($categories as $cat):
        $url = '?page=listing&type=' . $cat['type'];
        if (!empty($cat['price_type'])) $url = '?page=listing&price_type=' . $cat['price_type'];
      ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= $url ?>" class="category-card color-<?= $cat['color'] ?>">
          <div class="cat-icon">
            <i class="bi <?= $cat['icon'] ?>"></i>
          </div>
          <h6><?= $cat['label'] ?></h6>
          <small><?= $cat['count'] ?></small>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ============================================
     FEATURED PROPERTIES
============================================ -->
<section class="section-pad">
  <div class="container">

    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2">
      <div>
        <span class="section-badge">Featured</span>
        <h2>বাছাই করা প্রপার্টি</h2>
      </div>
      <a href="?page=listing&featured=1" class="btn-link-accent">
        সব দেখুন <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <?php if (empty($featured['data'])): ?>
      <div class="empty-state">
        <i class="bi bi-house-x"></i>
        <p>এখনো কোনো featured property নেই।</p>
      </div>
    <?php else: ?>
    <div class="row g-4 mt-1">
      <?php foreach ($featured['data'] as $prop): ?>
      <div class="col-md-6 col-lg-4">
        <?php echo renderPropertyCard($prop, $auth); ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>


<!-- ============================================
     WHY CHOOSE US
============================================ -->
<section class="section-pad bg-primary-custom text-white">
  <div class="container">

    <div class="section-header text-center text-white">
      <span class="section-badge-light">কেন আমরা?</span>
      <h2 class="text-white">আমাদের বেছে নেওয়ার কারণ</h2>
    </div>

    <div class="row g-4 mt-2">
      <?php
      $features = [
        ['icon' => 'bi-shield-check',   'title' => 'যাচাইকৃত প্রপার্টি',  'desc' => 'প্রতিটি প্রপার্টি আমাদের টিম দ্বারা যাচাই করা হয়'],
        ['icon' => 'bi-headset',        'title' => 'সার্বক্ষণিক সহায়তা',  'desc' => '২৪/৭ customer support সবসময় আপনার পাশে'],
        ['icon' => 'bi-graph-up-arrow', 'title' => 'সেরা মূল্য',           'desc' => 'বাজারের সেরা দামে প্রপার্টি পাওয়ার নিশ্চয়তা'],
        ['icon' => 'bi-people',         'title' => 'অভিজ্ঞ এজেন্ট',        'desc' => '৮০০+ সার্টিফাইড এজেন্ট আপনাকে সহায়তা করতে প্রস্তুত'],
      ];
      foreach ($features as $f):
      ?>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="bi <?= $f['icon'] ?>"></i>
          </div>
          <h5><?= $f['title'] ?></h5>
          <p><?= $f['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ============================================
     RECENTLY VIEWED (Logged In Users)
============================================ -->
<?php if ($auth->isLoggedIn() && !empty($recentlyViewed)): ?>
<section class="section-pad bg-light-custom">
  <div class="container">

    <div class="section-header d-flex justify-content-between align-items-end">
      <div>
        <span class="section-badge">সাম্প্রতিক</span>
        <h2>আপনি সম্প্রতি দেখেছেন</h2>
      </div>
      <a href="?page=buyer-dashboard" class="btn-link-accent">
        সব দেখুন <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4 mt-1">
      <?php foreach ($recentlyViewed as $prop): ?>
      <div class="col-md-6 col-lg-3">
        <?php echo renderPropertyCard($prop, $auth); ?>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>


<!-- ============================================
     CTA SECTION
============================================ -->
<section class="cta-section">
  <div class="container">
    <div class="cta-card">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          <h2>আপনার প্রপার্টি বিক্রি বা ভাড়া দিতে চান?</h2>
          <p>আজই আমাদের সাথে যোগ দিন এবং হাজার হাজার ক্রেতার কাছে পৌঁছান।</p>
        </div>
        <div class="col-lg-4 text-lg-end d-flex gap-3 justify-content-lg-end flex-wrap">
          <a href="?page=add-property" class="btn btn-accent btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Property দিন
          </a>
          <a href="?page=contact" class="btn btn-outline-light btn-lg">
            <i class="bi bi-telephone me-2"></i>যোগাযোগ করুন
          </a>
        </div>
      </div>
    </div>
  </div>
</section>


<?php
// ============================================
// PROPERTY CARD COMPONENT
// ============================================
function renderPropertyCard(array $prop, Auth $auth): string {

    $coverImage = $prop['cover_image']
        ? UPLOAD_URL . 'properties/' . htmlspecialchars($prop['cover_image'])
        : APP_URL . '/assets/images/no-image.webp';

    $price = number_format($prop['price']);
    $priceLabel = $prop['price_type'] === 'rent' ? '/মাস' : '';

    $isWishlisted = false;
    if ($auth->isLoggedIn()) {
        // wishlist check (simplified)
        $isWishlisted = false; // DB থেকে check করা যাবে
    }

    $verified = $prop['is_verified'] ? '
        <span class="badge-verified">
            <i class="bi bi-patch-check-fill me-1"></i>যাচাইকৃত
        </span>' : '';

    $featured = $prop['is_featured'] ? '
        <span class="badge-featured">
            <i class="bi bi-star-fill me-1"></i>Featured
        </span>' : '';

    $typeLabel = $prop['price_type'] === 'rent' ? 'ভাড়া' : 'বিক্রয়';
    $typeClass = $prop['price_type'] === 'rent' ? 'badge-rent' : 'badge-sale';

    return "
    <div class='property-card'>

      <!-- Image -->
      <div class='prop-image'>
        <a href='?page=property&id={$prop['id']}'>
          <img src='{$coverImage}' alt='" . htmlspecialchars($prop['title']) . "' loading='lazy'>
        </a>

        <!-- Badges -->
        <div class='prop-badges'>
          <span class='badge-type {$typeClass}'>{$typeLabel}</span>
          {$verified}
          {$featured}
        </div>

        <!-- Wishlist Button -->
        <button class='wishlist-btn " . ($isWishlisted ? 'active' : '') . "'
                onclick='toggleWishlist({$prop['id']}, this)'
                title='Wishlist এ যোগ করুন'>
          <i class='bi bi-heart" . ($isWishlisted ? '-fill' : '') . "'></i>
        </button>
      </div>

      <!-- Info -->
      <div class='prop-info'>
        <div class='prop-location'>
          <i class='bi bi-geo-alt text-accent'></i>
          " . htmlspecialchars($prop['area_name']) . ", " . htmlspecialchars($prop['district_name']) . "
        </div>

        <h5 class='prop-title'>
          <a href='?page=property&id={$prop['id']}'>
            " . htmlspecialchars($prop['title']) . "
          </a>
        </h5>

        <div class='prop-price'>
          ৳ {$price} <small>{$priceLabel}</small>
        </div>

        <!-- Specs -->
        <div class='prop-specs'>
          " . ($prop['bedrooms'] ? "<span><i class='bi bi-door-open'></i> {$prop['bedrooms']} বেড</span>" : '') . "
          " . ($prop['bathrooms'] ? "<span><i class='bi bi-droplet'></i> {$prop['bathrooms']} বাথ</span>" : '') . "
          " . ($prop['size_sqft'] ? "<span><i class='bi bi-rulers'></i> " . number_format($prop['size_sqft']) . " sqft</span>" : '') . "
        </div>

        <div class='prop-footer'>
          <div class='agent-mini'>
            <div class='agent-avatar-sm'>
              " . strtoupper(substr($prop['agent_name'], 0, 1)) . "
            </div>
            <small>" . htmlspecialchars($prop['agent_name']) . "</small>
          </div>
          <a href='?page=property&id={$prop['id']}' class='btn-view-prop'>
            বিস্তারিত <i class='bi bi-arrow-right ms-1'></i>
          </a>
        </div>
      </div>
    </div>";
}
?>