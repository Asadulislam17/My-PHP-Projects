<?php
require_once __DIR__ . '/../classes/Property.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth     = Auth::getInstance();
$propClass= Property::getInstance();
$db       = Database::getInstance();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ?page=listing'); exit; }

$prop = $propClass->getById($id);
if (!$prop || $prop['status'] !== 'approved') {
    header('Location: ?page=listing'); exit;
}

// Track recently viewed
if ($auth->isLoggedIn()) {
    $propClass->trackView($id, $_SESSION['user_id']);
}

// Similar Properties
$similar = $propClass->getAll([
    'type'   => $prop['type_slug'],
    'area_id'=> $prop['area_id'],
], 1, 3);

// Handle Inquiry Form
$inquiryMsg = '';
$inquiryErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (!$auth->isLoggedIn()) {
        $inquiryErr = 'Inquiry করতে <a href="?page=login">login</a> করুন।';
    } else {

        if ($_POST['action'] === 'inquiry') {
            $inserted = $db->execute(
                "INSERT INTO inquiries (property_id, sender_id, agent_id, message)
                 VALUES (?, ?, ?, ?)",
                [$id, $_SESSION['user_id'], $prop['user_id'], trim($_POST['message'])]
            );
            $inquiryMsg = $inserted ? 'Inquiry পাঠানো হয়েছে!' : 'কিছু সমস্যা হয়েছে।';
        }

        if ($_POST['action'] === 'booking') {
            $inserted = $db->execute(
                "INSERT INTO bookings (property_id, user_id, agent_id, tour_date, tour_time, message)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $id, $_SESSION['user_id'], $prop['user_id'],
                    $_POST['tour_date'], $_POST['tour_time'],
                    trim($_POST['booking_message'] ?? '')
                ]
            );
            $inquiryMsg = $inserted ? 'Tour schedule হয়েছে!' : 'কিছু সমস্যা হয়েছে।';
        }
    }
}

$coverImage = '';
foreach ($prop['images'] as $img) {
    if ($img['is_cover']) { $coverImage = $img['image_path']; break; }
}
if (!$coverImage && !empty($prop['images'])) {
    $coverImage = $prop['images'][0]['image_path'];
}
?>

<div class="property-detail-page">

  <!-- Breadcrumb -->
  <div class="breadcrumb-bar">
    <div class="container">
      <nav>
        <a href="?page=home">হোম</a>
        <i class="bi bi-chevron-right"></i>
        <a href="?page=listing">প্রপার্টি</a>
        <i class="bi bi-chevron-right"></i>
        <span><?= htmlspecialchars($prop['title']) ?></span>
      </nav>
    </div>
  </div>

  <div class="container py-4">
    <div class="row g-4">

      <!-- ============================================
           LEFT: MAIN CONTENT
      ============================================ -->
      <div class="col-lg-8">

        <!-- ===== IMAGE GALLERY ===== -->
        <div class="gallery-section">

          <!-- Main Image -->
          <div class="gallery-main">
            <?php if ($coverImage): ?>
            <img src="<?= UPLOAD_URL ?>properties/<?= htmlspecialchars($coverImage) ?>"
                 alt="<?= htmlspecialchars($prop['title']) ?>"
                 id="mainImage" class="gallery-main-img">
            <?php else: ?>
            <div class="gallery-placeholder">
              <i class="bi bi-image"></i>
              <p>কোনো ছবি নেই</p>
            </div>
            <?php endif; ?>

            <!-- Badges Overlay -->
            <div class="gallery-badges">
              <?php if ($prop['is_featured']): ?>
              <span class="badge-featured"><i class="bi bi-star-fill me-1"></i>Featured</span>
              <?php endif; ?>
              <?php if ($prop['is_verified']): ?>
              <span class="badge-verified"><i class="bi bi-patch-check-fill me-1"></i>যাচাইকৃত</span>
              <?php endif; ?>
            </div>

            <!-- 360 Tour Placeholder -->
            <button class="tour-360-btn" onclick="show360Tour()">
              <i class="bi bi-camera-video me-2"></i>৩৬০° ভার্চুয়াল ট্যুর
            </button>
          </div>

          <!-- Thumbnails -->
          <?php if (count($prop['images']) > 1): ?>
          <div class="gallery-thumbs">
            <?php foreach ($prop['images'] as $img): ?>
            <div class="gallery-thumb" onclick="changeImage('<?= UPLOAD_URL ?>properties/<?= $img['image_path'] ?>')">
              <img src="<?= UPLOAD_URL ?>properties/thumbs/<?= $img['thumbnail'] ?: $img['image_path'] ?>"
                   alt="thumb" loading="lazy">
            </div>
            <?php endforeach; ?>
            <!-- More indicator -->
            <?php if (count($prop['images']) > 4): ?>
            <div class="gallery-more">+<?= count($prop['images']) - 4 ?></div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- ===== TITLE & PRICE ===== -->
        <div class="detail-title-bar">
          <div>
            <div class="detail-location">
              <i class="bi bi-geo-alt text-accent"></i>
              <?= htmlspecialchars($prop['area_name']) ?>,
              <?= htmlspecialchars($prop['district_name']) ?>,
              <?= htmlspecialchars($prop['division_name']) ?>
            </div>
            <h1 class="detail-title"><?= htmlspecialchars($prop['title']) ?></h1>
            <div class="detail-meta">
              <span><i class="bi bi-eye me-1"></i><?= number_format($prop['views_count']) ?> ভিউ</span>
              <span><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($prop['created_at'])) ?></span>
              <span class="badge-type-sm <?= $prop['price_type'] === 'rent' ? 'rent' : 'sale' ?>">
                <?= $prop['price_type'] === 'rent' ? 'ভাড়া' : 'বিক্রয়' ?>
              </span>
            </div>
          </div>
          <div class="detail-price-box">
            <div class="detail-price">
              ৳<?= number_format($prop['price']) ?>
              <?= $prop['price_type'] === 'rent' ? '<small>/মাস</small>' : '' ?>
            </div>
            <?php if ($prop['size_sqft']): ?>
            <div class="price-per-sqft">
              ৳<?= number_format($prop['price'] / $prop['size_sqft'], 0) ?>/sqft
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===== SPECS ===== -->
        <div class="detail-specs-grid">
          <?php
          $specs = [
            ['icon' => 'bi-door-open',   'label' => 'বেডরুম',     'val' => $prop['bedrooms'] . ' টি'],
            ['icon' => 'bi-droplet',     'label' => 'বাথরুম',     'val' => $prop['bathrooms'] . ' টি'],
            ['icon' => 'bi-rulers',      'label' => 'আয়তন',      'val' => number_format($prop['size_sqft']) . ' sqft'],
            ['icon' => 'bi-buildings',   'label' => 'ফ্লোর',      'val' => $prop['floor_no'] ? $prop['floor_no'] . '/' . $prop['total_floors'] : 'N/A'],
            ['icon' => 'bi-compass',     'label' => 'দিকমুখী',    'val' => $prop['facing'] ?? 'N/A'],
            ['icon' => 'bi-calendar2',   'label' => 'নির্মাণ',    'val' => $prop['year_built'] ?? 'N/A'],
            ['icon' => 'bi-car-front',   'label' => 'পার্কিং',    'val' => $prop['parking'] ? 'আছে' : 'নেই'],
            ['icon' => 'bi-tag',         'label' => 'টাইপ',       'val' => $prop['type_name']],
          ];
          foreach ($specs as $s):
          ?>
          <div class="spec-box">
            <div class="spec-icon"><i class="bi <?= $s['icon'] ?>"></i></div>
            <div class="spec-label"><?= $s['label'] ?></div>
            <div class="spec-val"><?= $s['val'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- ===== DESCRIPTION ===== -->
        <div class="detail-card">
          <h4 class="detail-card-title">
            <i class="bi bi-file-text me-2 text-accent"></i>বিবরণ
          </h4>
          <div class="detail-description" id="descContent">
            <?= nl2br(htmlspecialchars($prop['description'])) ?>
          </div>
          <?php if (strlen($prop['description']) > 400): ?>
          <button class="btn-read-more" onclick="toggleDesc(this)">
            আরো পড়ুন <i class="bi bi-chevron-down ms-1"></i>
          </button>
          <?php endif; ?>
        </div>

        <!-- ===== AMENITIES ===== -->
        <?php if (!empty($prop['amenities'])): ?>
        <div class="detail-card">
          <h4 class="detail-card-title">
            <i class="bi bi-check-circle me-2 text-accent"></i>সুযোগ-সুবিধা
          </h4>
          <div class="amenities-grid">
            <?php foreach ($prop['amenities'] as $am): ?>
            <div class="amenity-item">
              <i class="bi bi-check-circle-fill text-accent"></i>
              <?= htmlspecialchars($am['name']) ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===== MAP ===== -->
        <?php if ($prop['latitude'] && $prop['longitude']): ?>
        <div class="detail-card">
          <h4 class="detail-card-title">
            <i class="bi bi-map me-2 text-accent"></i>লোকেশন
          </h4>
          <div id="detailMap" style="height:280px;border-radius:10px;"></div>
        </div>
        <?php endif; ?>

        <!-- ===== BOOKING FORM ===== -->
        <div class="detail-card" id="bookingSection">
          <h4 class="detail-card-title">
            <i class="bi bi-calendar-check me-2 text-accent"></i>ট্যুর বুক করুন
          </h4>
          <?php if ($inquiryMsg): ?>
          <div class="alert alert-success"><?= $inquiryMsg ?></div>
          <?php endif; ?>
          <?php if ($inquiryErr): ?>
          <div class="alert alert-danger"><?= $inquiryErr ?></div>
          <?php endif; ?>

          <form method="POST" class="booking-form">
            <input type="hidden" name="action" value="booking">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">তারিখ বেছে নিন</label>
                <input type="date" name="tour_date" class="form-control"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">সময় বেছে নিন</label>
                <select name="tour_time" class="form-control" required>
                  <option value="">সময় বেছে নিন</option>
                  <?php
                  foreach (['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'] as $t):
                  ?>
                  <option value="<?= $t ?>"><?= $t ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">বার্তা (ঐচ্ছিক)</label>
                <textarea name="booking_message" class="form-control" rows="2"
                          placeholder="কোনো বিশেষ অনুরোধ থাকলে লিখুন..."></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn-accent w-100">
                  <i class="bi bi-calendar-check me-2"></i>ট্যুর বুক করুন
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- ===== SIMILAR PROPERTIES ===== -->
        <?php if (!empty($similar['data'])): ?>
        <div class="mt-4">
          <h4 class="detail-card-title mb-3">
            <i class="bi bi-houses me-2 text-accent"></i>একই রকম প্রপার্টি
          </h4>
          <div class="row g-3">
            <?php foreach ($similar['data'] as $s): ?>
            <?php if ($s['id'] !== $prop['id']): ?>
            <div class="col-md-6">
              <?= renderPropertyCard($s, $auth) ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- ============================================
           RIGHT: STICKY SIDEBAR
      ============================================ -->
      <div class="col-lg-4">
        <div class="detail-sticky-sidebar">

          <!-- Agent Card -->
          <div class="agent-card">
            <div class="agent-card-header">
              <div class="agent-avatar-lg">
                <?= strtoupper(substr($prop['agent_name'], 0, 1)) ?>
              </div>
              <div>
                <h5><?= htmlspecialchars($prop['agent_name']) ?></h5>
                <span class="agent-badge">যাচাইকৃত এজেন্ট</span>
              </div>
            </div>
            <div class="agent-contacts">
              <a href="tel:<?= htmlspecialchars($prop['agent_phone']) ?>" class="agent-contact-btn">
                <i class="bi bi-telephone-fill"></i>
                <?= htmlspecialchars($prop['agent_phone']) ?>
              </a>
              <a href="mailto:<?= htmlspecialchars($prop['agent_email']) ?>" class="agent-contact-btn outline">
                <i class="bi bi-envelope-fill"></i>
                Email পাঠান
              </a>
            </div>
          </div>

          <!-- Quick Inquiry Form -->
          <div class="inquiry-card">
            <h5 class="inquiry-title">
              <i class="bi bi-chat-dots me-2 text-accent"></i>Inquiry পাঠান
            </h5>
            <form method="POST">
              <input type="hidden" name="action" value="inquiry">
              <?php if (!$auth->isLoggedIn()): ?>
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="আপনার নাম" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" required>
              </div>
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="ফোন নম্বর">
              </div>
              <?php endif; ?>
              <div class="mb-3">
                <textarea name="message" class="form-control" rows="4"
                          placeholder="আমি এই প্রপার্টি সম্পর্কে আরো জানতে চাই..."
                          required><?= htmlspecialchars("আমি এই প্রপার্টি '{$prop['title']}' সম্পর্কে আরো জানতে চাই।") ?></textarea>
              </div>
              <button type="submit" class="btn-accent w-100 mb-2">
                <i class="bi bi-send me-2"></i>Inquiry পাঠান
              </button>
              <a href="#bookingSection" class="btn-outline-primary-custom w-100">
                <i class="bi bi-calendar me-2"></i>ট্যুর বুক করুন
              </a>
            </form>
          </div>

          <!-- Compare Button -->
          <button class="btn-compare" onclick="addToCompare(<?= $prop['id'] ?>, '<?= htmlspecialchars($prop['title']) ?>')">
            <i class="bi bi-layers me-2"></i>তুলনায় যোগ করুন
          </button>

          <!-- Share -->
          <div class="share-card">
            <h6>শেয়ার করুন</h6>
            <div class="share-btns">
              <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode(APP_URL . '?page=property&id=' . $id) ?>"
                 target="_blank" class="share-btn fb">
                <i class="bi bi-facebook"></i>
              </a>
              <a href="https://wa.me/?text=<?= urlencode($prop['title'] . ' - ' . APP_URL . '?page=property&id=' . $id) ?>"
                 target="_blank" class="share-btn wa">
                <i class="bi bi-whatsapp"></i>
              </a>
              <button onclick="copyLink()" class="share-btn copy">
                <i class="bi bi-link-45deg"></i>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Compare Drawer -->
<div class="compare-drawer" id="compareDrawer">
  <div class="compare-drawer-header">
    <h6><i class="bi bi-layers me-2"></i>তুলনা করুন</h6>
    <button onclick="clearCompare()"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="compare-items" id="compareItems"></div>
  <a href="#" class="btn-accent w-100" id="compareBtn" style="display:none">
    <i class="bi bi-bar-chart me-2"></i>এখন তুলনা করুন
  </a>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Gallery
function changeImage(src) {
  document.getElementById('mainImage').src = src;
}

// Description toggle
function toggleDesc(btn) {
  const desc = document.getElementById('descContent');
  const isExpanded = desc.classList.toggle('expanded');
  btn.innerHTML = isExpanded
    ? 'কম দেখুন <i class="bi bi-chevron-up ms-1"></i>'
    : 'আরো পড়ুন <i class="bi bi-chevron-down ms-1"></i>';
}

// 360 Tour Placeholder
function show360Tour() {
  alert('৩৬০° ভার্চুয়াল ট্যুর শীঘ্রই আসছে!');
}

// Detail Map
<?php if ($prop['latitude'] && $prop['longitude']): ?>
const detailMap = L.map('detailMap').setView([<?= $prop['latitude'] ?>, <?= $prop['longitude'] ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(detailMap);
L.marker([<?= $prop['latitude'] ?>, <?= $prop['longitude'] ?>])
  .addTo(detailMap)
  .bindPopup('<b><?= htmlspecialchars($prop['title']) ?></b>').openPopup();
<?php endif; ?>

// Compare
let compareList = JSON.parse(localStorage.getItem('compareList') || '[]');

function addToCompare(id, title) {
  if (compareList.length >= 3) {
    showToast('সর্বোচ্চ ৩ টি প্রপার্টি তুলনা করা যাবে', 'error'); return;
  }
  if (compareList.find(p => p.id === id)) {
    showToast('এই প্রপার্টি আগেই যোগ হয়েছে', 'info'); return;
  }
  compareList.push({ id, title });
  localStorage.setItem('compareList', JSON.stringify(compareList));
  updateCompareDrawer();
  showToast('তুলনায় যোগ হয়েছে!', 'success');
}

function updateCompareDrawer() {
  const drawer = document.getElementById('compareDrawer');
  const items  = document.getElementById('compareItems');
  const btn    = document.getElementById('compareBtn');
  items.innerHTML = compareList.map(p =>
    `<div class="compare-item">
       <span>${p.title.substring(0,30)}...</span>
       <button onclick="removeCompare(${p.id})"><i class="bi bi-x"></i></button>
     </div>`
  ).join('');
  drawer.classList.toggle('show', compareList.length > 0);
  btn.style.display = compareList.length >= 2 ? 'block' : 'none';
  btn.href = '?page=compare&ids=' + compareList.map(p => p.id).join(',');
}

function removeCompare(id) {
  compareList = compareList.filter(p => p.id !== id);
  localStorage.setItem('compareList', JSON.stringify(compareList));
  updateCompareDrawer();
}

function clearCompare() {
  compareList = [];
  localStorage.removeItem('compareList');
  updateCompareDrawer();
}

// Copy Link
function copyLink() {
  navigator.clipboard.writeText(window.location.href);
  showToast('লিঙ্ক কপি হয়েছে!', 'success');
}

updateCompareDrawer();
</script>