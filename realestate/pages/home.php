<?php
require_once __DIR__ . '/../classes/Property.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth     = Auth::getInstance();
$property = Property::getInstance();

// Featured Properties
$featured = $property->getAll(['is_featured' => true], 1, 6);

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
<section class="hero-3d-section">
  <!-- ব্যাকগ্রাউন্ডে ৩ডি ডাইনামিক লাইটিং গ্লো -->
  <div class="glow-3d-orb orb-1"></div>
  <div class="glow-3d-orb orb-2"></div>
  <div class="hero-overlay-mesh"></div>
  
  <div class="hero-content container position-relative">
    <div class="row align-items-center g-5">
      
      <!-- বাম পাশের টেক্সট কন্টেন্ট -->
      <div class="col-lg-5 text-center text-lg-start" data-aos="fade-right">
        <span class="hero-badge-3d">
          <i class="bi bi-patch-check-fill text-accent me-1"></i> <?= __('বাংলাদেশের #১ নেক্সট-জেন প্ল্যাটফর্ম', 'Bangladesh\'s #1 Next-Gen Platform') ?>
        </span>
        <h1 class="hero-title-3d">
          <?= __('আপনার স্বপ্নের', 'Find Your Dream') ?> <br>
          <span class="text-gradient-3d"><?= __('বাড়ি খুঁজুন', 'Home Today') ?></span>
        </h1>
        <p class="hero-subtitle-3d">
          <?= __('১২,০০০+ লাইভ ও মেটা-ভেরিফাইড প্রপার্টি থেকে স্মার্টলি বেছে নিন আপনার নিজের ঠিকানা।', 'Choose your own address smartly from 12,000+ live and meta-verified properties.') ?>
        </p>
        
        <!-- মিনি স্ট্যাটাস কাউন্টার (৩ডি কার্ড স্টাইল) -->
        <div class="hero-stats-3d-mini">
          <?php foreach ($stats as $label => $val): ?>
          <div class="stat-box-3d">
            <span class="stat-val"><?= $val ?></span>
            <span class="stat-lbl">
              <?= match($label) {
                'properties' => __('প্রপার্টি', 'Properties'),
                'agents'     => __('এজেন্ট', 'Agents'),
                'cities'     => __('জেলা', 'Districts'),
                'happy'      => __('গ্রাহক', 'Customers')
              } ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ডান পাশের ফ্লোটিং ৩ডি সার্চ মেটা-কার্ড -->
      <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
        <div class="hero-search-card-3d">
          
          <!-- গ্লাস-ট্যাবস: কিনুন / ভাড়া নিন -->
          <div class="search-tabs-3d">
            <button type="button" class="search-tab-3d active" data-type="sale">
              <div class="tab-icon-wrapper"><i class="bi bi-house-door-fill"></i></div>
              <span><?= __('কিনুন', 'Buy') ?></span>
            </button>
            <button type="button" class="search-tab-3d" data-type="rent">
              <div class="tab-icon-wrapper"><i class="bi bi-key-fill"></i></div>
              <span><?= __('ভাড়া নিন', 'Rent') ?></span>
            </button>
          </div>

          <!-- সার্চ ফর্ম -->
          <form class="search-form-3d" action="index.php" method="GET">
            <input type="hidden" name="page" value="listing">
            <input type="hidden" name="price_type" id="searchPriceType" value="sale">

            <div class="search-grid-3d">
              
              <!-- এলাকা ইনপুট -->
              <div class="input-block-3d">
                <label><i class="bi bi-geo-alt-fill text-accent"></i> <?= __('এলাকা বেছে নিন', 'Select Location') ?></label>
                <div class="input-wrapper-3d">
                  <input type="text" name="keyword" class="field-input-3d" placeholder="<?= __('গুলশান, ধানমন্ডি, বনানী...', 'Gulshan, Dhanmondi, Banani...') ?>" id="locationSearch" autocomplete="off">
                  <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
              </div>

              <!-- দুই কলামের গ্রিড টাইপ ও বাজেটের জন্য -->
              <div class="row g-3 mt-1">
                <div class="col-sm-6">
                  <div class="input-block-3d">
                    <label><i class="bi bi-building-fill text-accent"></i> <?= __('প্রপার্টি ধরন', 'Property Type') ?></label>
                    <select name="type" class="field-input-3d">
                      <option value=""><?= __('সব ধরন', 'All Types') ?></option>
                      <option value="apartment"><?= __('অ্যাপার্টমেন্ট', 'Apartment') ?></option>
                      <option value="villa"><?= __('ভিলা', 'Villa') ?></option>
                      <option value="commercial"><?= __('কমার্শিয়াল', 'Commercial') ?></option>
                      <option value="land"><?= __('জমি', 'Land') ?></option>
                      <option value="office"><?= __('অফিস', 'Office') ?></option>
                    </select>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="input-block-3d">
                    <label><i class="bi bi-cash-stack text-accent"></i> <?= __('সর্বোচ্চ বাজেট', 'Max Budget') ?></label>
                    <select name="price_max" class="field-input-3d">
                      <option value=""><?= __('যেকোনো মূল্য', 'Any Price') ?></option>
                      <option value="2000000"><?= __('২০ লাখের নিচে', 'Below 20 Lakh') ?></option>
                      <option value="5000000"><?= __('৫০ লাখের নিচে', 'Below 50 Lakh') ?></option>
                      <option value="10000000"><?= __('১ কোটির নিচে', 'Below 1 Crore') ?></option>
                      <option value="20000000"><?= __('২ কোটির নিচে', 'Below 2 Crore') ?></option>
                      <option value="50000000"><?= __('৫ কোটির নিচে', 'Below 5 Crore') ?></option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- আল্ট্রা-৩ডি সাবমিট বাটন -->
              <button type="submit" class="search-submit-btn-3d">
                <span><?= __('স্মার্ট সার্চ শুরু করুন', 'Start Smart Search') ?></span>
                <i class="bi bi-lightning-charge-fill ms-2"></i>
              </button>

            </div>
          </form>

          <!-- জনপ্রিয় লিংক সমূহ -->
          <div class="popular-tags-3d">
            <span class="title"><?= __('জনপ্রিয় সার্চ:', 'Popular:') ?></span>
            <div class="tags-group">
              <a href="?page=listing&keyword=gulshan"><i class="bi bi-arrow-up-right small"></i> <?= __('গুলশান', 'Gulshan') ?></a>
              <a href="?page=listing&keyword=dhanmondi"><i class="bi bi-arrow-up-right small"></i> <?= __('ধানমন্ডি', 'Dhanmondi') ?></a>
              <a href="?page=listing&type=apartment"><i class="bi bi-arrow-up-right small"></i> <?= __('ফ্ল্যাট', 'Flat') ?></a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>




<!-- ============================================
     CATEGORIES
============================================ -->
<!-- ============================================
     3D CATEGORIES SECTION
============================================ -->
<section class="section-pad bg-3d-dark">
  <div class="container">

    <div class="section-header text-center mb-5" data-aos="fade-up">
      <span class="section-badge-3d-accent"><?= __('ক্যাটাগরি', 'Categories') ?></span>
      <h2 class="text-white mt-2 fw-bold"><?= __('প্রপার্টির ধরন বেছে নিন', 'Choose Property Type') ?></h2>
    </div>

    <div class="row g-4 mt-2">
      <?php
      $categories = [
        ['icon' => 'bi-building',     'label_bn' => 'অ্যাপার্টমেন্ট', 'label_en' => 'Apartment', 'type' => 'apartment', 'count' => '৪,২০০+', 'color' => 'blue'],
        ['icon' => 'bi-house-heart',  'label_bn' => 'ভিলা',          'label_en' => 'Villa',     'type' => 'villa',      'count' => '৮৫০+',   'color' => 'gold'],
        ['icon' => 'bi-shop',         'label_bn' => 'কমার্শিয়াল',    'label_en' => 'Commercial','type' => 'commercial', 'count' => '২,১০০+', 'color' => 'green'],
        ['icon' => 'bi-map',          'label_bn' => 'জমি',          'label_en' => 'Land',      'type' => 'land',       'count' => '৩,৫০০+', 'color' => 'earth'],
        ['icon' => 'bi-monitor',      'label_bn' => 'অফিস স্পেস',    'label_en' => 'Office Space','type' => 'office',     'count' => '৯৮০+',   'color' => 'purple'],
        ['icon' => 'bi-key',          'label_bn' => 'ভাড়া',         'label_en' => 'Rent',      'type' => '',           'count' => '৬,৪০০+', 'color' => 'red', 'price_type' => 'rent'],
      ];

      foreach ($categories as $cat):
        $url = '?page=listing&type=' . $cat['type'];
        if (!empty($cat['price_type'])) $url = '?page=listing&price_type=' . $cat['price_type'];
        $label = (getLang() === 'en') ? $cat['label_en'] : $cat['label_bn'];
      ?>
      <div class="col-6 col-md-4 col-lg-2" data-aos="zoom-in">
        <a href="<?= $url ?>" class="category-card-3d color-<?= $cat['color'] ?>">
          <div class="cat-icon-3d">
            <i class="bi <?= $cat['icon'] ?>"></i>
          </div>
          <h6><?= $label ?></h6>
          <small><?= $cat['count'] ?> <?= __('প্রপার্টি', 'Properties') ?></small>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ============================================
     3D FEATURED PROPERTIES SECTION
============================================ -->
<section class="section-pad bg-3d-deep">
  <div class="container">

    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4" data-aos="fade-up">
      <div>
        <span class="section-badge-3d-accent"><?= __('বাছাইকৃত', 'Featured') ?></span>
        <h2 class="text-white mt-2 fw-bold"><?= __('বাছাই করা প্রপার্টি', 'Featured Properties') ?></h2>
      </div>
      <a href="?page=listing&featured=1" class="btn-link-accent-3d">
        <?= __('সব দেখুন', 'View All') ?> <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <?php 
    // আগের সমস্যা এড়াতে সেফটি ডাটা ম্যাচিং চেক
    $featured_list = isset($featured['data']) ? $featured['data'] : $featured;

    if (empty($featured_list)): 
    ?>
      <div class="empty-state-3d text-center py-5" data-aos="fade-up">
        <i class="bi bi-house-x display-4 text-muted"></i>
        <p class="text-muted mt-3"><?= __('এখনো কোনো বাছাই করা প্রপার্টি নেই।', 'No featured properties available right now.') ?></p>
      </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($featured_list as $prop): ?>
      <div class="col-md-6 col-lg-4" data-aos="fade-up">
        <div class="card-wrapper-3d">
          <?php echo renderPropertyCard($prop, $auth); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>



<!-- ============================================
     WHY CHOOSE US
============================================ -->
<!-- ============================================
     3D WHY CHOOSE US SECTION
============================================ -->
<section class="section-pad bg-3d-deep-features text-white">
  <div class="container">

    <div class="section-header text-center mb-5" data-aos="fade-up">
      <span class="section-badge-3d-accent"><?= __('কেন আমরা?', 'Why Choose Us?') ?></span>
      <h2 class="text-white mt-2 fw-bold"><?= __('আমাদের বেছে নেওয়ার কারণ', 'Reasons to Choose Us') ?></h2>
    </div>

    <div class="row g-4 mt-2">
      <?php
      $features = [
        [
          'icon' => 'bi-shield-check',   
          'title_bn' => 'যাচাইকৃত প্রপার্টি',  'title_en' => 'Verified Properties',
          'desc_bn' => 'প্রতিটি প্রপার্টি আমাদের টিম দ্বারা যাচাই করা হয়', 'desc_en' => 'Every property is rigorously verified by our team'
        ],
        [
          'icon' => 'bi-headset',        
          'title_bn' => 'সার্বক্ষণিক সহায়তা',  'title_en' => '24/7 Support',
          'desc_bn' => '২৪/৭ কাস্টমার সাপোর্ট সবসময় আপনার পাশে', 'desc_en' => 'Dedicated customer support is always by your side'
        ],
        [
          'icon' => 'bi-graph-up-arrow', 
          'title_bn' => 'সেরা মূল্য',           'title_en' => 'Best Pricing',
          'desc_bn' => 'বাজারের সেরা দামে প্রপার্টি পাওয়ার নিশ্চয়তা', 'desc_en' => 'Guaranteed best market rates for your dream home'
        ],
        [
          'icon' => 'bi-people',         
          'title_bn' => 'অভিজ্ঞ এজেন্ট',        'title_en' => 'Expert Agents',
          'desc_bn' => '৮০০+ সার্টিফাইড এজেন্ট আপনাকে সহায়তা করতে প্রস্তুত', 'desc_en' => '800+ certified agents ready to assist you'
        ],
      ];
      foreach ($features as $f):
        $title = (getLang() === 'en') ? $f['title_en'] : $f['title_bn'];
        $desc  = (getLang() === 'en') ? $f['desc_en'] : $f['desc_bn'];
      ?>
      <div class="col-md-6 col-lg-3" data-aos="fade-up">
        <div class="feature-card-3d">
          <div class="feature-icon-3d">
            <i class="bi <?= $f['icon'] ?>"></i>
          </div>
          <h5><?= $title ?></h5>
          <p><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ============================================
     3D RECENTLY VIEWED (Logged In Users)
============================================ -->
<?php if ($auth->isLoggedIn() && !empty($recentlyViewed)): ?>
<section class="section-pad bg-3d-dark">
  <div class="container">

    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4" data-aos="fade-up">
      <div>
        <span class="section-badge-3d-accent"><?= __('সাম্প্রতিক', 'Recent') ?></span>
        <h2 class="text-white mt-2 fw-bold"><?= __('আপনি সম্প্রতি দেখেছেন', 'Recently Viewed') ?></h2>
      </div>
      <a href="?page=buyer-dashboard" class="btn-link-accent-3d">
        <?= __('সব দেখুন', 'View All') ?> <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php foreach ($recentlyViewed as $prop): ?>
      <div class="col-md-6 col-lg-3" data-aos="fade-up">
        <div class="card-wrapper-3d">
          <?php echo renderPropertyCard($prop, $auth); ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>


<!-- ============================================
     3D CTA SECTION
============================================ -->
<section class="cta-section-3d" data-aos="zoom-in">
  <div class="container">
    <div class="cta-card-3d">
      <div class="row align-items-center g-4">
        <div class="col-lg-8 text-center text-lg-start">
          <h2><?= __('আপনার প্রপার্টি বিক্রি বা ভাড়া দিতে চান?', 'Want to Sell or Rent Your Property?') ?></h2>
          <p class="mb-0"><?= __('আজই আমাদের সাথে যোগ দিন এবং হাজার হাজার ক্রেতার কাছে পৌঁছান।', 'Join us today and reach thousands of potential buyers instantly.') ?></p>
        </div>
        <div class="col-lg-4 d-flex gap-3 justify-content-center justify-content-lg-end flex-wrap">
          <a href="?page=add-property" class="btn-cta-luxury">
            <i class="bi bi-plus-circle-fill me-2"></i><?= __('Property দিন', 'Add Property') ?>
          </a>
          <a href="?page=contact" class="btn-cta-outline-luxury">
            <i class="bi bi-telephone-fill me-2"></i><?= __('যোগাযোগ করুন', 'Contact Us') ?>
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

?>