<?php
/**
 * BLOG PAGE — pages/blog.php
 */
require_once __DIR__ . '/../config/config.php';

// Static blog posts (DB integration পরে যোগ করা যাবে)
$posts = [
  ['id'=>1,'title'=>'ঢাকায় অ্যাপার্টমেন্ট কেনার সম্পূর্ণ গাইড ২০২৪','category'=>'গাইড','author'=>'Team RealEstate','date'=>'২০ মে ২০২৪','read_time'=>'৮ মিনিট','image'=>'','excerpt'=>'ঢাকায় অ্যাপার্টমেন্ট কেনার আগে যা জানা দরকার — বাজেট, এলাকা, legal প্রক্রিয়া এবং developer বাছাই নিয়ে সম্পূর্ণ তথ্য।','tags'=>['ঢাকা','কেনা','গাইড'],'views'=>1240],
  ['id'=>2,'title'=>'EMI কি? Housing Loan নেওয়ার আগে যা জানবেন','category'=>'Finance','author'=>'Rahim Ahmed','date'=>'১৫ মে ২০২৪','read_time'=>'৬ মিনিট','image'=>'','excerpt'=>'Bank থেকে housing loan নেওয়ার প্রক্রিয়া, সুদের হার, এবং EMI calculation নিয়ে বিস্তারিত আলোচনা।','tags'=>['EMI','Loan','Finance'],'views'=>980],
  ['id'=>3,'title'=>'গুলশান বনাম ধানমন্ডি — কোথায় বিনিয়োগ লাভজনক?','category'=>'বিনিয়োগ','author'=>'Karim Properties','date'=>'১০ মে ২০২৪','read_time'=>'৫ মিনিট','image'=>'','excerpt'=>'ঢাকার দুটি প্রিমিয়াম এলাকার property মূল্য, ROI, সুবিধা-অসুবিধা নিয়ে তুলনামূলক বিশ্লেষণ।','tags'=>['গুলশান','ধানমন্ডি','বিনিয়োগ'],'views'=>2100],
  ['id'=>4,'title'=>'নতুন নির্মাণ প্রযুক্তি — ২০২৪ সালের সেরা উপকরণ','category'=>'নির্মাণ','author'=>'Build Expert','date'=>'৫ মে ২০২৪','read_time'=>'৭ মিনিট','image'=>'','excerpt'=>'আধুনিক নির্মাণে cement, rod এবং eco-friendly উপকরণের ব্যবহার নিয়ে বিশেষজ্ঞদের পরামর্শ।','tags'=>['নির্মাণ','উপকরণ','২০২৪'],'views'=>756],
  ['id'=>5,'title'=>'ভাড়া বাড়িতে থাকার আগে যা চেক করবেন','category'=>'ভাড়া','author'=>'Team RealEstate','date'=>'১ মে ২০২৪','read_time'=>'৪ মিনিট','image'=>'','excerpt'=>'নতুন ভাড়া বাড়িতে ওঠার আগে বৈদ্যুতিক সংযোগ, পানি সরবরাহ, নিরাপত্তাসহ ১৫ টি গুরুত্বপূর্ণ বিষয় চেক করুন।','tags'=>['ভাড়া','টিপস'],'views'=>1580],
  ['id'=>6,'title'=>'চট্টগ্রামে real estate বিনিয়োগ — সুযোগ ও চ্যালেঞ্জ','category'=>'বিনিয়োগ','author'=>'CRE Expert','date'=>'২৫ এপ্রিল ২০২৪','read_time'=>'৯ মিনিট','image'=>'','excerpt'=>'বাংলাদেশের দ্বিতীয় বৃহত্তম শহরে property বিনিয়োগের সম্ভাবনা ও বাজার বিশ্লেষণ।','tags'=>['চট্টগ্রাম','বিনিয়োগ'],'views'=>890],
];

$categories = ['সব','গাইড','Finance','বিনিয়োগ','নির্মাণ','ভাড়া'];
$activeCategory = $_GET['cat'] ?? 'সব';
$search = $_GET['q'] ?? '';

$filtered = array_filter($posts, function($p) use ($activeCategory, $search) {
  $catMatch    = $activeCategory === 'সব' || $p['category'] === $activeCategory;
  $searchMatch = !$search || stripos($p['title'],$search)!==false || stripos($p['excerpt'],$search)!==false;
  return $catMatch && $searchMatch;
});

$featured = $posts[0];
?>

<div class="blog-page">

  <!-- Hero -->
  <div class="blog-hero">
    <div class="blog-hero-bg">
      <div class="bh-orb bh-orb-1"></div>
      <div class="bh-orb bh-orb-2"></div>
      <div class="bh-grid"></div>
    </div>
    <div class="container">
      <div class="blog-hero-content">
        <span class="blog-hero-badge">
          <i class="bi bi-newspaper me-1"></i>রিয়েল এস্টেট ব্লগ
        </span>
        <h1>জানুন, শিখুন, সিদ্ধান্ত নিন</h1>
        <p>Property কেনা-বেচা, বিনিয়োগ ও নির্মাণ নিয়ে বিশেষজ্ঞ পরামর্শ</p>
        <!-- Search -->
        <form method="GET" class="blog-search-form">
          <input type="hidden" name="page" value="blog">
          <div class="blog-search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" placeholder="আর্টিকেল খুঁজুন..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit">খুঁজুন</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="container py-5">

    <!-- Featured Post -->
    <div class="blog-featured">
      <div class="bf-image">
        <div class="bf-image-placeholder">
          <i class="bi bi-newspaper"></i>
        </div>
        <span class="bf-category-badge"><?= $featured['category'] ?></span>
      </div>
      <div class="bf-content">
        <div class="bf-meta">
          <span><i class="bi bi-person me-1"></i><?= $featured['author'] ?></span>
          <span><i class="bi bi-calendar me-1"></i><?= $featured['date'] ?></span>
          <span><i class="bi bi-clock me-1"></i><?= $featured['read_time'] ?></span>
        </div>
        <h2 class="bf-title">
          <a href="?page=blog-post&id=<?= $featured['id'] ?>"><?= htmlspecialchars($featured['title']) ?></a>
        </h2>
        <p class="bf-excerpt"><?= htmlspecialchars($featured['excerpt']) ?></p>
        <div class="bf-tags">
          <?php foreach ($featured['tags'] as $tag): ?>
          <a href="?page=blog&q=<?= urlencode($tag) ?>" class="blog-tag"><?= $tag ?></a>
          <?php endforeach; ?>
        </div>
        <a href="?page=blog-post&id=<?= $featured['id'] ?>" class="bf-read-btn">
          পড়ুন <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>

    <!-- Category Filters -->
    <div class="blog-cats mb-4">
      <?php foreach ($categories as $cat): ?>
      <a href="?page=blog&cat=<?= urlencode($cat) ?>"
         class="blog-cat-btn <?= $activeCategory===$cat?'active':'' ?>">
        <?= $cat ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Posts Grid -->
    <?php if (empty($filtered)): ?>
    <div class="empty-page-state">
      <div class="eps-icon"><i class="bi bi-search"></i></div>
      <h3>কোনো আর্টিকেল পাওয়া যায়নি</h3>
      <a href="?page=blog" class="btn-accent-lg">সব দেখুন</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($filtered as $post): ?>
      <div class="col-md-6 col-lg-4">
        <article class="blog-card">
          <div class="blog-card-img">
            <div class="blog-img-placeholder">
              <i class="bi bi-file-text"></i>
            </div>
            <span class="blog-card-cat"><?= $post['category'] ?></span>
          </div>
          <div class="blog-card-body">
            <div class="blog-card-meta">
              <span><?= $post['date'] ?></span>
              <span><i class="bi bi-eye me-1"></i><?= number_format($post['views']) ?></span>
              <span><i class="bi bi-clock me-1"></i><?= $post['read_time'] ?></span>
            </div>
            <h4 class="blog-card-title">
              <a href="?page=blog-post&id=<?= $post['id'] ?>">
                <?= htmlspecialchars($post['title']) ?>
              </a>
            </h4>
            <p class="blog-card-excerpt">
              <?= htmlspecialchars(substr($post['excerpt'], 0, 100)) ?>...
            </p>
            <div class="blog-card-footer">
              <div class="blog-author">
                <div class="blog-author-avatar">
                  <?= strtoupper(substr($post['author'],0,1)) ?>
                </div>
                <small><?= htmlspecialchars($post['author']) ?></small>
              </div>
              <a href="?page=blog-post&id=<?= $post['id'] ?>" class="blog-read-link">
                পড়ুন <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>