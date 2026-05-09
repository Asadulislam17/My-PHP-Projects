<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth = Auth::getInstance();
$auth->requireRole(['agent','admin']);

$propClass = Property::getInstance();
$db        = Database::getInstance();

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF');
    }

    $result = $propClass->create($_POST, $_SESSION['user_id']);

    if ($result['success']) {
        // Handle image upload
        if (!empty($_FILES['images']['name'][0])) {
            $propClass->uploadImages($result['property_id'], $_FILES['images']);
        }
        $success = $result['message'];
        header('Location: ?page=agent-dashboard&tab=properties&added=1');
        exit;
    } else {
        $errors = $result['errors'];
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$types     = $db->query("SELECT * FROM property_types ORDER BY name");
$amenities = $db->query("SELECT * FROM amenities ORDER BY name");
$areas     = $db->query(
    "SELECT a.id, a.name, d.name AS district
     FROM areas a JOIN districts d ON d.id=a.district_id ORDER BY d.name, a.name"
);
?>

<div class="add-property-page">

  <!-- 3D Header -->
  <div class="add-prop-header">
    <div class="container">
      <div class="add-prop-header-inner">
        <div>
          <h1>নতুন Property যোগ করুন</h1>
          <p>সব তথ্য সঠিকভাবে পূরণ করুন। Admin অনুমোদনের পরে প্রকাশিত হবে।</p>
        </div>
        <div class="add-prop-steps">
          <div class="step active" id="step1Ind">
            <span>১</span> তথ্য
          </div>
          <div class="step-line"></div>
          <div class="step" id="step2Ind">
            <span>২</span> ছবি
          </div>
          <div class="step-line"></div>
          <div class="step" id="step3Ind">
            <span>৩</span> সুবিধা
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container py-5">
    <form method="POST" enctype="multipart/form-data" id="addPropForm">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

      <div class="row g-4">
        <div class="col-lg-8">

          <!-- ── Step 1: Basic Info ── -->
          <div class="form-section" id="formStep1">
            <div class="form-section-header">
              <div class="form-section-num">০১</div>
              <div>
                <h4>মূল তথ্য</h4>
                <p>Property এর সাধারণ তথ্য দিন</p>
              </div>
            </div>

            <!-- Title -->
            <div class="field-wrap <?= isset($errors['title']) ? 'has-error' : '' ?>">
              <label class="field-label">
                Property শিরোনাম <span class="required">*</span>
              </label>
              <input type="text" name="title" class="field-input"
                     placeholder="যেমন: গুলশান-১ এ ৩ BHK অ্যাপার্টমেন্ট"
                     value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
              <?php if (isset($errors['title'])): ?>
              <span class="field-error"><?= $errors['title'] ?></span>
              <?php endif; ?>
              <span class="char-count" id="titleCount">0/255</span>
            </div>

            <!-- Type + Price Type -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="field-wrap <?= isset($errors['type_id']) ? 'has-error' : '' ?>">
                  <label class="field-label">Property টাইপ <span class="required">*</span></label>
                  <div class="type-cards">
                    <?php foreach ($types as $t): ?>
                    <label class="type-card">
                      <input type="radio" name="type_id" value="<?= $t['id'] ?>"
                             <?= ($_POST['type_id'] ?? '') == $t['id'] ? 'checked' : '' ?>>
                      <div class="type-card-inner">
                        <i class="bi bi-building"></i>
                        <span><?= htmlspecialchars($t['name']) ?></span>
                      </div>
                    </label>
                    <?php endforeach; ?>
                  </div>
                  <?php if (isset($errors['type_id'])): ?>
                  <span class="field-error"><?= $errors['type_id'] ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-md-6">
                <div class="field-wrap <?= isset($errors['price_type']) ? 'has-error' : '' ?>">
                  <label class="field-label">বিক্রয় / ভাড়া <span class="required">*</span></label>
                  <div class="price-type-toggle">
                    <label class="price-toggle-btn">
                      <input type="radio" name="price_type" value="sale"
                             <?= ($_POST['price_type'] ?? 'sale') === 'sale' ? 'checked' : '' ?>>
                      <span><i class="bi bi-house-door me-1"></i>বিক্রয়</span>
                    </label>
                    <label class="price-toggle-btn">
                      <input type="radio" name="price_type" value="rent"
                             <?= ($_POST['price_type'] ?? '') === 'rent' ? 'checked' : '' ?>>
                      <span><i class="bi bi-key me-1"></i>ভাড়া</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Price + Area -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="field-wrap <?= isset($errors['price']) ? 'has-error' : '' ?>">
                  <label class="field-label">মূল্য (BDT) <span class="required">*</span></label>
                  <div class="input-prefix">
                    <span>৳</span>
                    <input type="number" name="price" class="field-input prefix"
                           placeholder="৫০০০০০০"
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                  </div>
                  <?php if (isset($errors['price'])): ?>
                  <span class="field-error"><?= $errors['price'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="field-wrap <?= isset($errors['area_id']) ? 'has-error' : '' ?>">
                  <label class="field-label">এলাকা <span class="required">*</span></label>
                  <select name="area_id" class="field-input">
                    <option value="">এলাকা বেছে নিন</option>
                    <?php foreach ($areas as $a): ?>
                    <option value="<?= $a['id'] ?>"
                            <?= ($_POST['area_id'] ?? '') == $a['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($a['name']) ?> — <?= htmlspecialchars($a['district']) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($errors['area_id'])): ?>
                  <span class="field-error"><?= $errors['area_id'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Specs Row -->
            <div class="row g-3">
              <div class="col-6 col-md-3">
                <div class="field-wrap">
                  <label class="field-label">বেডরুম</label>
                  <div class="number-stepper">
                    <button type="button" onclick="stepVal('bedrooms',-1)">−</button>
                    <input type="number" name="bedrooms" id="bedrooms"
                           class="field-input center" value="<?= $_POST['bedrooms'] ?? 2 ?>" min="0">
                    <button type="button" onclick="stepVal('bedrooms',1)">+</button>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="field-wrap">
                  <label class="field-label">বাথরুম</label>
                  <div class="number-stepper">
                    <button type="button" onclick="stepVal('bathrooms',-1)">−</button>
                    <input type="number" name="bathrooms" id="bathrooms"
                           class="field-input center" value="<?= $_POST['bathrooms'] ?? 2 ?>" min="0">
                    <button type="button" onclick="stepVal('bathrooms',1)">+</button>
                  </div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="field-wrap">
                  <label class="field-label">আয়তন (sqft)</label>
                  <input type="number" name="size_sqft" class="field-input"
                         placeholder="১২০০" value="<?= $_POST['size_sqft'] ?? '' ?>">
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="field-wrap">
                  <label class="field-label">ফ্লোর নং</label>
                  <input type="number" name="floor_no" class="field-input"
                         placeholder="৫" value="<?= $_POST['floor_no'] ?? '' ?>">
                </div>
              </div>
            </div>

            <!-- Address -->
            <div class="field-wrap">
              <label class="field-label">সম্পূর্ণ ঠিকানা</label>
              <input type="text" name="address" class="field-input"
                     placeholder="বাড়ি নং, রোড, এলাকা..."
                     value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>

            <!-- Map Location Picker -->
            <div class="field-wrap">
              <label class="field-label">
                <i class="bi bi-geo-alt text-accent me-1"></i>
                মানচিত্রে অবস্থান নির্বাচন করুন
              </label>
              <div id="locationPicker" style="height:250px;border-radius:12px;border:1.5px solid var(--border);overflow:hidden;"></div>
              <div class="row g-2 mt-2">
                <div class="col-6">
                  <input type="number" name="latitude"  id="lat" class="field-input"
                         placeholder="Latitude"  step="0.0000001"
                         value="<?= $_POST['latitude'] ?? '' ?>">
                </div>
                <div class="col-6">
                  <input type="number" name="longitude" id="lng" class="field-input"
                         placeholder="Longitude" step="0.0000001"
                         value="<?= $_POST['longitude'] ?? '' ?>">
                </div>
              </div>
              <small class="text-muted">মানচিত্রে click করলে coordinate auto-set হবে</small>
            </div>

            <!-- Description -->
            <div class="field-wrap">
              <label class="field-label">বিবরণ</label>
              <textarea name="description" class="field-input textarea" rows="5"
                        placeholder="Property সম্পর্কে বিস্তারিত লিখুন..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Extras -->
            <div class="row g-3">
              <div class="col-md-4">
                <div class="field-wrap">
                  <label class="field-label">দিকমুখী</label>
                  <select name="facing" class="field-input">
                    <option value="">বেছে নিন</option>
                    <?php foreach (['north'=>'উত্তর','south'=>'দক্ষিণ','east'=>'পূর্ব','west'=>'পশ্চিম'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($_POST['facing']??'')===$v?'selected':'' ?>>
                      <?= $l ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="field-wrap">
                  <label class="field-label">নির্মাণ বছর</label>
                  <input type="number" name="year_built" class="field-input"
                         placeholder="২০২০" min="1900" max="<?= date('Y') ?>"
                         value="<?= $_POST['year_built'] ?? '' ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="field-wrap">
                  <label class="field-label">পার্কিং</label>
                  <label class="toggle-switch">
                    <input type="checkbox" name="parking" value="1"
                           <?= !empty($_POST['parking']) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="toggle-label">পার্কিং সুবিধা আছে</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Step 2: Images ── -->
          <div class="form-section" id="formStep2">
            <div class="form-section-header">
              <div class="form-section-num">০২</div>
              <div>
                <h4>ছবি আপলোড</h4>
                <p>সর্বোচ্চ ১০ টি ছবি। প্রথম ছবিটি Cover হবে।</p>
              </div>
            </div>

            <div class="image-upload-zone" id="imageDropZone">
              <input type="file" name="images[]" id="imageInput"
                     multiple accept="image/*" style="display:none">
              <div class="upload-zone-content">
                <div class="upload-icon">
                  <i class="bi bi-cloud-arrow-up"></i>
                </div>
                <h5>ছবি drag করুন অথবা</h5>
                <button type="button" class="btn-upload-browse"
                        onclick="document.getElementById('imageInput').click()">
                  ফাইল বেছে নিন
                </button>
                <p class="upload-hint">JPG, PNG, WebP — সর্বোচ্চ 5MB প্রতিটি</p>
              </div>
            </div>

            <div class="image-preview-grid" id="imagePreviewGrid"></div>
          </div>

          <!-- ── Step 3: Amenities ── -->
          <div class="form-section" id="formStep3">
            <div class="form-section-header">
              <div class="form-section-num">০৩</div>
              <div>
                <h4>সুযোগ-সুবিধা</h4>
                <p>যা আছে তা বেছে নিন</p>
              </div>
            </div>

            <div class="amenities-picker">
              <?php foreach ($amenities as $am): ?>
              <label class="amenity-pick-card">
                <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>">
                <div class="amenity-pick-inner">
                  <i class="bi bi-check2-circle amenity-check-icon"></i>
                  <i class="bi bi-wifi amenity-icon"></i>
                  <span><?= htmlspecialchars($am['name']) ?></span>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

        <!-- ── Sticky Submit Sidebar ── -->
        <div class="col-lg-4">
          <div class="submit-sidebar">
            <div class="submit-preview">
              <div class="submit-preview-icon">
                <i class="bi bi-house-check"></i>
              </div>
              <h5>Submit করার আগে</h5>
              <ul class="submit-checklist" id="submitChecklist">
                <li id="chk-title">
                  <i class="bi bi-circle"></i> শিরোনাম দিন
                </li>
                <li id="chk-type">
                  <i class="bi bi-circle"></i> টাইপ বেছে নিন
                </li>
                <li id="chk-price">
                  <i class="bi bi-circle"></i> মূল্য দিন
                </li>
                <li id="chk-area">
                  <i class="bi bi-circle"></i> এলাকা বেছে নিন
                </li>
                <li id="chk-images">
                  <i class="bi bi-circle"></i> ছবি যোগ করুন
                </li>
              </ul>
            </div>

            <div class="submit-info">
              <i class="bi bi-info-circle text-accent me-2"></i>
              Submit এর পর Admin review করবেন।
              সাধারণত ২৪ ঘণ্টার মধ্যে অনুমোদন হয়।
            </div>

            <button type="submit" class="btn-submit-property" id="submitBtn">
              <span class="submit-btn-inner">
                <i class="bi bi-send me-2"></i>
                Property Submit করুন
              </span>
              <div class="submit-btn-glow"></div>
            </button>

            <a href="?page=agent-dashboard" class="btn-cancel-property">
              <i class="bi bi-x me-1"></i>বাতিল করুন
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Location Picker ───────────────────────────────
const pickMap = L.map('locationPicker').setView([23.8103, 90.4125], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickMap);
let pickMarker = null;

pickMap.on('click', e => {
  const {lat, lng} = e.latlng;
  document.getElementById('lat').value = lat.toFixed(7);
  document.getElementById('lng').value = lng.toFixed(7);
  if (pickMarker) pickMarker.setLatLng(e.latlng);
  else pickMarker = L.marker(e.latlng).addTo(pickMap);
});

// ── Number Stepper ────────────────────────────────
function stepVal(id, delta) {
  const el = document.getElementById(id);
  el.value = Math.max(0, parseInt(el.value||0) + delta);
  updateChecklist();
}

// ── Image Upload & Preview ────────────────────────
const imageInput    = document.getElementById('imageInput');
const previewGrid   = document.getElementById('imagePreviewGrid');
const dropZone      = document.getElementById('imageDropZone');
let uploadedImages  = [];

imageInput.addEventListener('change', handleFiles);

dropZone.addEventListener('dragover', e => {
  e.preventDefault(); dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault(); dropZone.classList.remove('drag-over');
  handleFileList(e.dataTransfer.files);
});

function handleFiles(e) { handleFileList(e.target.files); }

function handleFileList(files) {
  Array.from(files).slice(0, 10 - uploadedImages.length).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
      uploadedImages.push({name: file.name, url: e.target.result});
      renderPreviews();
    };
    reader.readAsDataURL(file);
  });
  updateChecklist();
}

function renderPreviews() {
  previewGrid.innerHTML = uploadedImages.map((img, i) => `
    <div class="preview-item ${i===0?'cover':''}">
      <img src="${img.url}" alt="">
      ${i===0 ? '<span class="cover-badge">Cover</span>' : ''}
      <button type="button" class="remove-preview"
              onclick="removePreview(${i})">
        <i class="bi bi-x"></i>
      </button>
    </div>
  `).join('');
}

function removePreview(i) {
  uploadedImages.splice(i, 1);
  renderPreviews();
  updateChecklist();
}

// ── Checklist ─────────────────────────────────────
function updateChecklist() {
  const title  = document.querySelector('[name=title]')?.value?.trim();
  const typeEl = document.querySelector('[name=type_id]:checked');
  const price  = document.querySelector('[name=price]')?.value;
  const area   = document.querySelector('[name=area_id]')?.value;

  setChk('chk-title',  !!title);
  setChk('chk-type',   !!typeEl);
  setChk('chk-price',  !!price && price > 0);
  setChk('chk-area',   !!area);
  setChk('chk-images', uploadedImages.length > 0);
}

function setChk(id, ok) {
  const li = document.getElementById(id);
  if (!li) return;
  li.classList.toggle('done', ok);
  li.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
}

// Live checklist update
document.getElementById('addPropForm').addEventListener('input', updateChecklist);
document.addEventListener('change', updateChecklist);

// Title char count
document.querySelector('[name=title]')?.addEventListener('input', function() {
  document.getElementById('titleCount').textContent = this.value.length + '/255';
  updateChecklist();
});
</script>