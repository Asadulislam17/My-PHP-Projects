<?php
require_once __DIR__ . '/../classes/Property.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth     = Auth::getInstance();
$property = Property::getInstance();

// Filters from GET
$filters = [
    'keyword'    => $_GET['keyword']    ?? '',
    'price_type' => $_GET['price_type'] ?? '',
    'type'       => $_GET['type']       ?? '',
    'area_id'    => $_GET['area_id']    ?? '',
    'price_min'  => $_GET['price_min']  ?? '',
    'price_max'  => $_GET['price_max']  ?? '',
    'bedrooms'   => $_GET['bedrooms']   ?? '',
    'sort'       => $_GET['sort']       ?? 'newest',
];

$page    = max(1, (int)($_GET['p'] ?? 1));
$result  = $property->getAll($filters, $page, 9);
$props   = $result['data'];
$total   = $result['total'];
$lastPage= $result['last_page'];

// Areas for filter dropdown
$db    = Database::getInstance();
$areas = $db->query("SELECT id, name FROM areas ORDER BY name");
?>

<div class="listing-page">

  <!-- Page Header -->
  <div class="listing-header">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h1 class="listing-title">প্রপার্টি খুঁজুন</h1>
          <p class="listing-subtitle">
            <span class="text-accent fw-bold"><?= number_format($total) ?></span> টি প্রপার্টি পাওয়া গেছে
          </p>
        </div>
        <!-- View Toggle -->
        <div class="view-toggle">
          <button class="view-btn active" id="gridViewBtn" title="Grid View">
            <i class="bi bi-grid-3x3-gap"></i>
          </button>
          <button class="view-btn" id="mapViewBtn" title="Map View">
            <i class="bi bi-map"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="container py-4">
    <div class="row g-4">

      <!-- ============================================
           SIDEBAR FILTERS
      ============================================ -->
      <div class="col-lg-3">
        <div class="filter-sidebar" id="filterSidebar">

          <div class="filter-header">
            <h5><i class="bi bi-funnel me-2 text-accent"></i>ফিল্টার</h5>
            <a href="?page=listing" class="filter-reset">
              <i class="bi bi-arrow-counterclockwise me-1"></i>রিসেট
            </a>
          </div>

          <form method="GET" action="" id="filterForm">
            <input type="hidden" name="page" value="listing">

            <!-- Keyword -->
            <div class="filter-group">
              <label class="filter-label">কীওয়ার্ড</label>
              <div class="input-with-icon">
                <i class="bi bi-search"></i>
                <input type="text" name="keyword" class="filter-input"
                       placeholder="এলাকা, নাম..."
                       value="<?= htmlspecialchars($filters['keyword']) ?>">
              </div>
            </div>

            <!-- Price Type -->
            <div class="filter-group">
              <label class="filter-label">ধরন</label>
              <div class="btn-group-custom">
                <label class="btn-radio <?= $filters['price_type'] === '' ? 'active' : '' ?>">
                  <input type="radio" name="price_type" value="" hidden
                         <?= $filters['price_type'] === '' ? 'checked' : '' ?>> সব
                </label>
                <label class="btn-radio <?= $filters['price_type'] === 'sale' ? 'active' : '' ?>">
                  <input type="radio" name="price_type" value="sale" hidden
                         <?= $filters['price_type'] === 'sale' ? 'checked' : '' ?>> বিক্রয়
                </label>
                <label class="btn-radio <?= $filters['price_type'] === 'rent' ? 'active' : '' ?>">
                  <input type="radio" name="price_type" value="rent" hidden
                         <?= $filters['price_type'] === 'rent' ? 'checked' : '' ?>> ভাড়া
                </label>
              </div>
            </div>

            <!-- Property Type -->
            <div class="filter-group">
              <label class="filter-label">প্রপার্টি টাইপ</label>
              <?php
              $types = [
                '' => 'সব',
                'apartment'  => 'অ্যাপার্টমেন্ট',
                'villa'      => 'ভিলা',
                'commercial' => 'কমার্শিয়াল',
                'land'       => 'জমি',
                'office'     => 'অফিস'
              ];
              foreach ($types as $val => $label):
              ?>
              <label class="filter-checkbox">
                <input type="radio" name="type" value="<?= $val ?>"
                       <?= $filters['type'] === $val ? 'checked' : '' ?>>
                <span class="checkmark"></span>
                <?= $label ?>
              </label>
              <?php endforeach; ?>
            </div>

            <!-- Price Range -->
            <div class="filter-group">
              <label class="filter-label">
                মূল্য সীমা
                <span class="price-display" id="priceDisplay">
                  ৳<?= $filters['price_min'] ? number_format($filters['price_min']) : '০' ?>
                  - ৳<?= $filters['price_max'] ? number_format($filters['price_max']) : '৫ কোটি+' ?>
                </span>
              </label>
              <div class="price-inputs">
                <input type="number" name="price_min" class="filter-input" 
                       placeholder="সর্বনিম্ন"
                       value="<?= htmlspecialchars($filters['price_min']) ?>">
                <span>—</span>
                <input type="number" name="price_max" class="filter-input"
                       placeholder="সর্বোচ্চ"
                       value="<?= htmlspecialchars($filters['price_max']) ?>">
              </div>
            </div>

            <!-- Bedrooms -->
            <div class="filter-group">
              <label class="filter-label">বেডরুম</label>
              <div class="btn-group-custom">
                <?php foreach (['', '1', '2', '3', '4'] as $bed): ?>
                <label class="btn-radio <?= $filters['bedrooms'] === $bed ? 'active' : '' ?>">
                  <input type="radio" name="bedrooms" value="<?= $bed ?>" hidden
                         <?= $filters['bedrooms'] === $bed ? 'checked' : '' ?>>
                  <?= $bed === '' ? 'সব' : $bed . '+' ?>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Area -->
            <div class="filter-group">
              <label class="filter-label">এলাকা</label>
              <select name="area_id" class="filter-input">
                <option value="">সব এলাকা</option>
                <?php foreach ($areas as $area): ?>
                <option value="<?= $area['id'] ?>"
                        <?= $filters['area_id'] == $area['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($area['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Amenities -->
            <div class="filter-group">
              <label class="filter-label">সুযোগ-সুবিধা</label>
              <?php
              $amenities = $db->query("SELECT * FROM amenities LIMIT 8");
              foreach ($amenities as $am):
              ?>
              <label class="filter-checkbox">
                <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>">
                <span class="checkmark"></span>
                <i class="bi bi-check2 me-1 text-accent" style="font-size:0.8rem"></i>
                <?= htmlspecialchars($am['name']) ?>
              </label>
              <?php endforeach; ?>
            </div>

            <button type="submit" class="btn-filter-apply">
              <i class="bi bi-search me-2"></i>ফিল্টার করুন
            </button>
          </form>

        </div>
      </div>

      <!-- ============================================
           MAIN CONTENT
      ============================================ -->
      <div class="col-lg-9">

        <!-- Sort Bar -->
        <div class="sort-bar">
          <div class="sort-info">
            <span><?= number_format($total) ?> টি প্রপার্টি</span>
            <?php if (!empty($filters['keyword'])): ?>
            <span class="active-filter">
              "<?= htmlspecialchars($filters['keyword']) ?>"
              <a href="?page=listing"><i class="bi bi-x"></i></a>
            </span>
            <?php endif; ?>
          </div>
          <div class="d-flex align-items-center gap-2">
            <label class="sort-label">সাজান:</label>
            <select class="sort-select" onchange="applySort(this.value)">
              <?php
              $sorts = [
                'newest'     => 'সর্বশেষ',
                'price_asc'  => 'মূল্য: কম → বেশি',
                'price_desc' => 'মূল্য: বেশি → কম',
                'popular'    => 'জনপ্রিয়',
              ];
              foreach ($sorts as $val => $label):
              ?>
              <option value="<?= $val ?>" <?= $filters['sort'] === $val ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- ===== GRID VIEW ===== -->
        <div id="gridView">
          <?php if (empty($props)): ?>
          <div class="empty-result">
            <i class="bi bi-house-x"></i>
            <h4>কোনো প্রপার্টি পাওয়া যায়নি</h4>
            <p>ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন</p>
            <a href="?page=listing" class="btn-accent-sm">সব দেখুন</a>
          </div>
          <?php else: ?>
          <div class="row g-4" id="propertyGrid">
            <?php foreach ($props as $prop): ?>
            <div class="col-md-6 col-xl-4">
              <?= renderPropertyCard($prop, $auth) ?>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($lastPage > 1): ?>
          <nav class="pagination-wrap">
            <ul class="pagination-custom">
              <?php if ($page > 1): ?>
              <li>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page - 1])) ?>"
                   class="page-btn">
                  <i class="bi bi-chevron-left"></i>
                </a>
              </li>
              <?php endif; ?>

              <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
              <li>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>"
                   class="page-btn <?= $i === $page ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              </li>
              <?php endfor; ?>

              <?php if ($page < $lastPage): ?>
              <li>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page + 1])) ?>"
                   class="page-btn">
                  <i class="bi bi-chevron-right"></i>
                </a>
              </li>
              <?php endif; ?>
            </ul>
          </nav>
          <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- ===== MAP VIEW ===== -->
        <div id="mapView" style="display:none;">
          <div class="map-split">
            <!-- Left: Compact List -->
            <div class="map-list">
              <?php foreach ($props as $prop): ?>
              <?php
              $cover = $prop['cover_image']
                ? UPLOAD_URL . 'properties/' . $prop['cover_image']
                : APP_URL . '/assets/images/no-image.webp';
              ?>
              <div class="map-list-item" onclick="focusMarker(<?= $prop['id'] ?>)">
                <img src="<?= $cover ?>" alt="">
                <div class="map-list-info">
                  <h6><?= htmlspecialchars($prop['title']) ?></h6>
                  <small><i class="bi bi-geo-alt text-accent"></i>
                    <?= htmlspecialchars($prop['area_name']) ?>
                  </small>
                  <div class="fw-bold text-accent">
                    ৳<?= number_format($prop['price']) ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- Right: Map -->
            <div class="map-container" id="propertyMap"></div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Map Data for JS -->
<script>
const mapProperties = <?= json_encode(array_map(fn($p) => [
  'id'      => $p['id'],
  'title'   => $p['title'],
  'price'   => number_format($p['price']),
  'area'    => $p['area_name'],
  'lat'     => $p['latitude'],
  'lng'     => $p['longitude'],
  'url'     => '?page=property&id=' . $p['id'],
], $props)) ?>;

function applySort(val) {
  const url = new URL(window.location);
  url.searchParams.set('sort', val);
  window.location.href = url.toString();
}

function focusMarker(id) {
  if (window.propMarkers && window.propMarkers[id]) {
    window.propMarkers[id].openPopup();
  }
}

// View Toggle
document.getElementById('gridViewBtn').addEventListener('click', () => {
  document.getElementById('gridView').style.display = 'block';
  document.getElementById('mapView').style.display  = 'none';
  document.getElementById('gridViewBtn').classList.add('active');
  document.getElementById('mapViewBtn').classList.remove('active');
});

document.getElementById('mapViewBtn').addEventListener('click', () => {
  document.getElementById('gridView').style.display = 'none';
  document.getElementById('mapView').style.display  = 'block';
  document.getElementById('gridViewBtn').classList.remove('active');
  document.getElementById('mapViewBtn').classList.add('active');
  initMap();
});

// Leaflet Map Init
function initMap() {
  if (window.mapInitialized) return;
  window.mapInitialized = true;

  const map = L.map('propertyMap').setView([23.8103, 90.4125], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  window.propMarkers = {};
  mapProperties.forEach(prop => {
    if (!prop.lat || !prop.lng) return;
    const marker = L.marker([prop.lat, prop.lng])
      .addTo(map)
      .bindPopup(`
        <div style="min-width:180px">
          <strong>${prop.title}</strong><br>
          <small>${prop.area}</small><br>
          <b style="color:#C5A059">৳${prop.price}</b><br>
          <a href="${prop.url}" style="color:#C5A059">বিস্তারিত →</a>
        </div>
      `);
    window.propMarkers[prop.id] = marker;
  });
}
</script>

<!-- Leaflet CSS/JS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>