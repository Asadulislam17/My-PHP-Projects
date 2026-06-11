<?php
/**
 * ══════════════════════════════════════════════
 * EDIT PROPERTY PAGE
 * pages/edit-property.php
 * ══════════════════════════════════════════════
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Property.php';

$auth      = Auth::getInstance();
$auth->requireRole(['agent', 'admin']);

$propClass = Property::getInstance();
$db        = Database::getInstance();
$userId    = (int)$_SESSION['user_id'];
$role      = $_SESSION['user_role'];

/* ── Get Property ID ── */
$propId = (int)($_GET['id'] ?? 0);
if (!$propId) {
    header('Location: ?page=agent-dashboard&tab=properties');
    exit;
}

/* ── Load Property ── */
$prop = $propClass->getById($propId);
if (!$prop) {
    header('Location: ?page=agent-dashboard&tab=properties&error=not_found');
    exit;
}

/* ── Permission Check ── */
if ($role !== 'admin' && $prop['user_id'] !== $userId) {
    header('Location: ?page=agent-dashboard&tab=properties&error=forbidden');
    exit;
}

/* ── Load Dropdown Data ── */
$types     = $db->query("SELECT * FROM property_types ORDER BY name");
$amenities = $db->query("SELECT * FROM amenities ORDER BY name");
$areas     = $db->query(
    "SELECT a.id, a.name, d.name AS district
     FROM areas a
     JOIN districts d ON d.id = a.district_id
     ORDER BY d.name, a.name"
);

/* ── Currently selected amenities ── */
$selectedAmenities = array_column($prop['amenities'], 'id');

/* ── Existing images ── */
$existingImages = $prop['images'];

/* ── Handle Form Submit ── */
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* CSRF */
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? 'update';

    /* ── Delete single image ── */
    if ($action === 'delete_image' && !empty($_POST['image_id'])) {
        $result = $propClass->deleteImage((int)$_POST['image_id'], $userId);
        echo json_encode($result);
        exit;
    }

    /* ── Set cover image ── */
    if ($action === 'set_cover' && !empty($_POST['image_id'])) {
        $db->execute("UPDATE property_images SET is_cover=0 WHERE property_id=?", [$propId]);
        $db->execute("UPDATE property_images SET is_cover=1 WHERE id=? AND property_id=?",
            [(int)$_POST['image_id'], $propId]);
        echo json_encode(['success' => true]);
        exit;
    }

    /* ── Main update ── */
    if ($action === 'update') {
        $result = $propClass->update($propId, $_POST, $userId);

        if ($result['success']) {
            /* Upload new images if any */
            if (!empty($_FILES['new_images']['name'][0])) {
                $propClass->uploadImages($propId, $_FILES['new_images']);
            }

            /* Activity log */
            $db->execute(
                "INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)",
                [$userId, 'property.updated', 'Property #' . $propId . ' updated', $_SERVER['REMOTE_ADDR'] ?? '']
            );

            header('Location: ?page=edit-property&id=' . $propId . '&saved=1');
            exit;
        } else {
            $errors = $result['errors'] ?? ['general' => $result['message'] ?? 'Update failed'];
        }
    }
}

/* ── CSRF Token ── */
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

/* ── Status color map ── */
$statusConfig = [
    'pending'  => ['color' => 'amber',  'label' => 'অনুমোদন বাকি', 'icon' => 'clock-history'],
    'approved' => ['color' => 'green',  'label' => 'অনুমোদিত',     'icon' => 'check-circle-fill'],
    'rejected' => ['color' => 'red',    'label' => 'প্রত্যাখ্যাত', 'icon' => 'x-circle-fill'],
    'sold'     => ['color' => 'blue',   'label' => 'বিক্রিত',       'icon' => 'bag-check-fill'],
    'rented'   => ['color' => 'purple', 'label' => 'ভাড়া হয়েছে',  'icon' => 'key-fill'],
];
$statusCfg = $statusConfig[$prop['status']] ?? $statusConfig['pending'];
?>

<div class="edit-prop-page">

  <!-- ══ 3D HEADER ══ -->
  <div class="ep-header">
    <div class="ep-header-bg">
      <div class="ep-orb ep-orb-1"></div>
      <div class="ep-orb ep-orb-2"></div>
      <div class="ep-grid"></div>
    </div>
    <div class="container">
      <div class="ep-header-inner">

        <!-- Breadcrumb -->
        <nav class="ep-breadcrumb">
          <a href="?page=<?= $role === 'admin' ? 'admin-dashboard&tab=properties' : 'agent-dashboard&tab=properties' ?>">
            <i class="bi bi-arrow-left me-1"></i>
            <?= $role === 'admin' ? 'Admin Panel' : 'আমার Properties' ?>
          </a>
          <i class="bi bi-chevron-right"></i>
          <span>Property Edit</span>
        </nav>

        <div class="ep-header-content">
          <!-- Property Preview Info -->
          <div class="ep-prop-preview">
            <?php
            $coverImg = '';
            foreach ($existingImages as $img) {
                if ($img['is_cover']) { $coverImg = $img['image_path']; break; }
            }
            if (!$coverImg && !empty($existingImages)) $coverImg = $existingImages[0]['image_path'];
            ?>
            <div class="ep-preview-thumb">
              <?php if ($coverImg): ?>
              <img src="<?= UPLOAD_URL ?>properties/<?= htmlspecialchars($coverImg) ?>"
                   alt="cover" id="headerThumb">
              <?php else: ?>
              <div class="ep-no-thumb"><i class="bi bi-image"></i></div>
              <?php endif; ?>
            </div>
            <div class="ep-preview-info">
              <div class="ep-status-badge color-<?= $statusCfg['color'] ?>">
                <i class="bi bi-<?= $statusCfg['icon'] ?> me-1"></i>
                <?= $statusCfg['label'] ?>
              </div>
              <h1 class="ep-title"><?= htmlspecialchars($prop['title']) ?></h1>
              <div class="ep-meta">
                <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($prop['area_name']) ?></span>
                <span><i class="bi bi-eye me-1"></i><?= number_format($prop['views_count']) ?> ভিউ</span>
                <span><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($prop['created_at'])) ?></span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="ep-header-actions">
            <a href="?page=property&id=<?= $propId ?>" target="_blank" class="ep-action-btn outline">
              <i class="bi bi-eye me-1"></i>Live দেখুন
            </a>
            <?php if ($role === 'admin'): ?>
            <?php if ($prop['status'] === 'pending'): ?>
            <form method="POST" action="?page=admin-dashboard" style="display:inline">
              <input type="hidden" name="action" value="approve_property">
              <input type="hidden" name="id"     value="<?= $propId ?>">
              <input type="hidden" name="tab"    value="properties">
              <button type="submit" class="ep-action-btn green">
                <i class="bi bi-check-circle me-1"></i>Approve
              </button>
            </form>
            <?php endif; ?>
            <form method="POST" action="?page=admin-dashboard" style="display:inline">
              <input type="hidden" name="action" value="toggle_featured">
              <input type="hidden" name="id"     value="<?= $propId ?>">
              <input type="hidden" name="tab"    value="properties">
              <button type="submit"
                      class="ep-action-btn <?= $prop['is_featured'] ? 'gold-active' : 'gold' ?>">
                <i class="bi bi-star<?= $prop['is_featured'] ? '-fill' : '' ?> me-1"></i>
                <?= $prop['is_featured'] ? 'Featured সরান' : 'Featured করুন' ?>
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ MAIN CONTENT ══ -->
  <div class="container py-5">

    <!-- Alerts -->
    <?php if (isset($_GET['saved'])): ?>
    <div class="ep-alert success mb-4">
      <i class="bi bi-check-circle-fill me-2"></i>
      Property সফলভাবে আপডেট হয়েছে!
      <?php if ($prop['status'] !== 'approved'): ?>
      Admin review করলে publish হবে।
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
    <div class="ep-alert error mb-4">
      <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errors['general']) ?>
    </div>
    <?php endif; ?>

    <?php if ($prop['status'] === 'rejected'): ?>
    <div class="ep-alert warning mb-4">
      <i class="bi bi-exclamation-circle me-2"></i>
      এই property reject হয়েছে। তথ্য সংশোধন করে আবার submit করুন। Admin পুনরায় review করবেন।
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="editPropForm">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <input type="hidden" name="action"     value="update">

      <div class="row g-4">

        <!-- ══ LEFT: Form Sections ══ -->
        <div class="col-lg-8">

          <!-- ── Section 1: Basic Info ── -->
          <div class="ep-section" id="sec-basic">
            <div class="ep-section-header">
              <div class="ep-sec-num">০১</div>
              <div>
                <h4>মূল তথ্য</h4>
                <p>Property এর সাধারণ তথ্য সম্পাদনা করুন</p>
              </div>
              <div class="ep-sec-badge">
                <i class="bi bi-pencil-fill me-1"></i>সম্পাদনাযোগ্য
              </div>
            </div>

            <!-- Title -->
            <div class="ep-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
              <label class="ep-label">
                Property শিরোনাম <span class="req">*</span>
                <span class="char-count" id="titleCount">
                  <?= strlen($prop['title']) ?>/255
                </span>
              </label>
              <input type="text" name="title" class="ep-input"
                     placeholder="Property শিরোনাম..."
                     value="<?= htmlspecialchars($_POST['title'] ?? $prop['title']) ?>"
                     maxlength="255"
                     oninput="document.getElementById('titleCount').textContent=this.value.length+'/255'">
              <?php if (isset($errors['title'])): ?>
              <span class="ep-error"><?= $errors['title'] ?></span>
              <?php endif; ?>
            </div>

            <!-- Type + Price Type -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="ep-field <?= isset($errors['type_id']) ? 'has-error' : '' ?>">
                  <label class="ep-label">Property টাইপ <span class="req">*</span></label>
                  <div class="ep-type-grid">
                    <?php foreach ($types as $t): ?>
                    <?php $checked = ($_POST['type_id'] ?? $prop['type_id']) == $t['id']; ?>
                    <label class="ep-type-card <?= $checked ? 'checked' : '' ?>">
                      <input type="radio" name="type_id" value="<?= $t['id'] ?>"
                             <?= $checked ? 'checked' : '' ?>
                             onchange="this.closest('.ep-type-grid').querySelectorAll('.ep-type-card').forEach(c=>c.classList.remove('checked'));this.closest('.ep-type-card').classList.add('checked')">
                      <i class="bi bi-building"></i>
                      <span><?= htmlspecialchars($t['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                  </div>
                  <?php if (isset($errors['type_id'])): ?>
                  <span class="ep-error"><?= $errors['type_id'] ?></span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-md-6">
                <div class="ep-field <?= isset($errors['price_type']) ? 'has-error' : '' ?>">
                  <label class="ep-label">বিক্রয় / ভাড়া <span class="req">*</span></label>
                  <div class="ep-price-type-toggle">
                    <?php foreach (['sale' => ['bi-house-door','বিক্রয়'], 'rent' => ['bi-key','ভাড়া']] as $val => [$icon, $label]): ?>
                    <?php $checked = ($_POST['price_type'] ?? $prop['price_type']) === $val; ?>
                    <label class="ep-pt-btn <?= $checked ? 'checked' : '' ?>">
                      <input type="radio" name="price_type" value="<?= $val ?>"
                             <?= $checked ? 'checked' : '' ?>
                             onchange="document.querySelectorAll('.ep-pt-btn').forEach(b=>b.classList.remove('checked'));this.closest('.ep-pt-btn').classList.add('checked')">
                      <i class="bi <?= $icon ?> me-1"></i><?= $label ?>
                    </label>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Price + Area -->
            <div class="row g-3">
              <div class="col-md-6">
                <div class="ep-field <?= isset($errors['price']) ? 'has-error' : '' ?>">
                  <label class="ep-label">মূল্য (BDT) <span class="req">*</span></label>
                  <div class="ep-input-prefix">
                    <span class="ep-prefix">৳</span>
                    <input type="number" name="price" class="ep-input"
                           placeholder="৫০০০০০০" min="0"
                           value="<?= htmlspecialchars($_POST['price'] ?? $prop['price']) ?>">
                  </div>
                  <?php if (isset($errors['price'])): ?>
                  <span class="ep-error"><?= $errors['price'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6">
                <div class="ep-field <?= isset($errors['area_id']) ? 'has-error' : '' ?>">
                  <label class="ep-label">এলাকা <span class="req">*</span></label>
                  <select name="area_id" class="ep-input">
                    <option value="">এলাকা বেছে নিন</option>
                    <?php foreach ($areas as $a): ?>
                    <option value="<?= $a['id'] ?>"
                            <?= ($_POST['area_id'] ?? $prop['area_id']) == $a['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($a['name']) ?> — <?= htmlspecialchars($a['district']) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (isset($errors['area_id'])): ?>
                  <span class="ep-error"><?= $errors['area_id'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- Specs Row -->
            <div class="row g-3">
              <?php
              $specFields = [
                ['bedrooms',    'বেডরুম',   '0'],
                ['bathrooms',   'বাথরুম',   '0'],
                ['size_sqft',   'sqft',     ''],
                ['floor_no',    'ফ্লোর নং', ''],
                ['total_floors','মোট ফ্লোর',''],
              ];
              foreach ($specFields as [$name, $label, $min]):
                $val = $_POST[$name] ?? $prop[$name] ?? '';
              ?>
              <div class="col-6 col-md-<?= in_array($name,['bedrooms','bathrooms'])?'3':'4' ?>">
                <div class="ep-field">
                  <label class="ep-label"><?= $label ?></label>
                  <?php if (in_array($name, ['bedrooms','bathrooms'])): ?>
                  <div class="ep-stepper">
                    <button type="button" onclick="stepEP('<?= $name ?>', -1)">−</button>
                    <input type="number" name="<?= $name ?>" id="ep_<?= $name ?>"
                           class="ep-input center" min="0"
                           value="<?= htmlspecialchars($val) ?>">
                    <button type="button" onclick="stepEP('<?= $name ?>', 1)">+</button>
                  </div>
                  <?php else: ?>
                  <input type="number" name="<?= $name ?>" class="ep-input"
                         placeholder="—" min="0"
                         value="<?= htmlspecialchars($val) ?>">
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Address -->
            <div class="ep-field">
              <label class="ep-label">সম্পূর্ণ ঠিকানা</label>
              <input type="text" name="address" class="ep-input"
                     placeholder="বাড়ি নং, রোড, এলাকা..."
                     value="<?= htmlspecialchars($_POST['address'] ?? $prop['address'] ?? '') ?>">
            </div>

            <!-- Map Picker -->
            <div class="ep-field">
              <label class="ep-label">
                <i class="bi bi-geo-alt text-accent me-1"></i>মানচিত্রে অবস্থান
              </label>
              <div id="epMap" style="height:240px;border-radius:12px;border:1.5px solid var(--border);overflow:hidden;"></div>
              <div class="row g-2 mt-2">
                <div class="col-6">
                  <input type="number" name="latitude" id="epLat" class="ep-input"
                         placeholder="Latitude" step="0.0000001"
                         value="<?= htmlspecialchars($_POST['latitude'] ?? $prop['latitude'] ?? '') ?>">
                </div>
                <div class="col-6">
                  <input type="number" name="longitude" id="epLng" class="ep-input"
                         placeholder="Longitude" step="0.0000001"
                         value="<?= htmlspecialchars($_POST['longitude'] ?? $prop['longitude'] ?? '') ?>">
                </div>
              </div>
              <small class="ep-hint">মানচিত্রে click করলে coordinate auto-set হবে</small>
            </div>

            <!-- Description -->
            <div class="ep-field">
              <label class="ep-label">বিবরণ</label>
              <textarea name="description" class="ep-input ep-textarea" rows="6"
                        placeholder="Property সম্পর্কে বিস্তারিত লিখুন..."><?= htmlspecialchars($_POST['description'] ?? $prop['description'] ?? '') ?></textarea>
            </div>

            <!-- Extras Row -->
            <div class="row g-3">
              <div class="col-md-4">
                <div class="ep-field">
                  <label class="ep-label">দিকমুখী</label>
                  <select name="facing" class="ep-input">
                    <option value="">বেছে নিন</option>
                    <?php foreach (['north'=>'উত্তর','south'=>'দক্ষিণ','east'=>'পূর্ব','west'=>'পশ্চিম'] as $v=>$l): ?>
                    <option value="<?= $v ?>"
                            <?= ($_POST['facing'] ?? $prop['facing'] ?? '') === $v ? 'selected' : '' ?>>
                      <?= $l ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="ep-field">
                  <label class="ep-label">নির্মাণ বছর</label>
                  <input type="number" name="year_built" class="ep-input"
                         placeholder="২০২০" min="1900" max="<?= date('Y') ?>"
                         value="<?= htmlspecialchars($_POST['year_built'] ?? $prop['year_built'] ?? '') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="ep-field">
                  <label class="ep-label">পার্কিং সুবিধা</label>
                  <label class="ep-toggle">
                    <input type="checkbox" name="parking" value="1"
                           <?= !empty($_POST['parking'] ?? $prop['parking']) ? 'checked' : '' ?>>
                    <span class="ep-toggle-slider"></span>
                    <span class="ep-toggle-label">পার্কিং আছে</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Section 2: Image Management ── -->
          <div class="ep-section" id="sec-images">
            <div class="ep-section-header">
              <div class="ep-sec-num">০২</div>
              <div>
                <h4>ছবি পরিচালনা</h4>
                <p>বিদ্যমান ছবি সম্পাদনা বা নতুন ছবি যোগ করুন</p>
              </div>
              <span class="ep-img-count">
                <?= count($existingImages) ?> টি ছবি
              </span>
            </div>

            <!-- Existing Images Grid -->
            <?php if (!empty($existingImages)): ?>
            <div class="ep-existing-imgs" id="existingImgGrid">
              <?php foreach ($existingImages as $img): ?>
              <div class="ep-img-item <?= $img['is_cover'] ? 'is-cover' : '' ?>"
                   id="epimg-<?= $img['id'] ?>">
                <img src="<?= UPLOAD_URL ?>properties/<?= htmlspecialchars($img['image_path']) ?>"
                     alt="property image" loading="lazy">

                <!-- Cover Badge -->
                <?php if ($img['is_cover']): ?>
                <div class="ep-img-cover-badge">
                  <i class="bi bi-star-fill me-1"></i>Cover
                </div>
                <?php endif; ?>

                <!-- Image Actions -->
                <div class="ep-img-actions">
                  <?php if (!$img['is_cover']): ?>
                  <button type="button"
                          class="ep-img-btn star"
                          title="Cover হিসেবে সেট করুন"
                          onclick="setCover(<?= $img['id'] ?>, <?= $propId ?>)">
                    <i class="bi bi-star-fill"></i>
                  </button>
                  <?php endif; ?>
                  <button type="button"
                          class="ep-img-btn trash"
                          title="মুছুন"
                          onclick="deleteImg(<?= $img['id'] ?>, <?= $propId ?>)">
                    <i class="bi bi-trash3-fill"></i>
                  </button>
                </div>
              </div>
              <?php endforeach; ?>

              <!-- Add More Slot -->
              <label class="ep-img-add-slot" for="newImageInput">
                <i class="bi bi-plus-circle"></i>
                <span>ছবি যোগ করুন</span>
              </label>
            </div>
            <?php endif; ?>

            <!-- New Image Upload -->
            <div class="ep-upload-zone <?= empty($existingImages) ? 'full' : 'compact' ?>"
                 id="epDropZone"
                 ondragover="this.classList.add('drag-over');event.preventDefault()"
                 ondragleave="this.classList.remove('drag-over')"
                 ondrop="handleDrop(event)">
              <input type="file" name="new_images[]" id="newImageInput"
                     multiple accept="image/*"
                     onchange="previewNewImages(this.files)"
                     style="display:none">
              <?php if (empty($existingImages)): ?>
              <div class="ep-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
              <h5>নতুন ছবি আপলোড করুন</h5>
              <button type="button" class="ep-browse-btn"
                      onclick="document.getElementById('newImageInput').click()">
                ফাইল বেছে নিন
              </button>
              <p class="ep-upload-hint">JPG, PNG, WebP — সর্বোচ্চ 5MB প্রতিটি</p>
              <?php else: ?>
              <p class="ep-upload-hint-sm">
                <i class="bi bi-info-circle me-1 text-accent"></i>
                নতুন ছবি drag করুন অথবা
                <button type="button" class="ep-browse-link"
                        onclick="document.getElementById('newImageInput').click()">
                  বেছে নিন
                </button>
              </p>
              <?php endif; ?>
            </div>

            <!-- New Image Preview -->
            <div class="ep-new-preview-grid" id="newPreviewGrid"></div>
          </div>

          <!-- ── Section 3: Amenities ── -->
          <div class="ep-section" id="sec-amenities">
            <div class="ep-section-header">
              <div class="ep-sec-num">০৩</div>
              <div>
                <h4>সুযোগ-সুবিধা</h4>
                <p>যা আছে তা selected রাখুন</p>
              </div>
              <span class="ep-amenity-count" id="amenityCount">
                <?= count($selectedAmenities) ?> টি selected
              </span>
            </div>

            <div class="ep-amenities-grid">
              <?php foreach ($amenities as $am): ?>
              <?php $isSelected = in_array($am['id'], $selectedAmenities); ?>
              <label class="ep-amenity-card <?= $isSelected ? 'checked' : '' ?>">
                <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>"
                       <?= $isSelected ? 'checked' : '' ?>
                       onchange="updateAmenityCount()">
                <div class="ep-amenity-inner">
                  <i class="bi bi-check2-circle ep-amenity-check"></i>
                  <i class="bi bi-circle ep-amenity-empty"></i>
                  <span><?= htmlspecialchars($am['name']) ?></span>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

        </div>

        <!-- ══ RIGHT: Sticky Sidebar ══ -->
        <div class="col-lg-4">
          <div class="ep-sidebar">

            <!-- Save Button -->
            <div class="ep-save-card">
              <div class="ep-save-icon">
                <i class="bi bi-floppy2-fill"></i>
              </div>
              <h5>পরিবর্তন সংরক্ষণ</h5>
              <p>সংরক্ষণ করলে Admin পুনরায় review করবেন।</p>

              <button type="submit" class="ep-save-btn" id="epSaveBtn">
                <span class="ep-save-btn-inner">
                  <i class="bi bi-floppy2-fill me-2"></i>
                  পরিবর্তন সংরক্ষণ করুন
                </span>
                <div class="ep-save-btn-glow"></div>
              </button>

              <a href="?page=<?= $role==='admin'?'admin-dashboard&tab=properties':'agent-dashboard&tab=properties' ?>"
                 class="ep-cancel-btn">
                <i class="bi bi-x me-1"></i>বাতিল করুন
              </a>
            </div>

            <!-- Current Status Card -->
            <div class="ep-status-card color-<?= $statusCfg['color'] ?>">
              <div class="ep-status-icon">
                <i class="bi bi-<?= $statusCfg['icon'] ?>"></i>
              </div>
              <div>
                <strong>বর্তমান অবস্থা</strong>
                <p><?= $statusCfg['label'] ?></p>
              </div>
            </div>

            <!-- Property Stats -->
            <div class="ep-stats-card">
              <h6><i class="bi bi-bar-chart-fill me-2 text-accent"></i>পরিসংখ্যান</h6>
              <div class="ep-stats-list">
                <?php
                $statsData = [
                  ['bi-eye',           number_format($prop['views_count']),    'মোট ভিউ'],
                  ['bi-heart-fill',    $db->queryOne("SELECT COUNT(*) c FROM wishlist WHERE property_id=?",[$propId])['c'], 'Wishlist'],
                  ['bi-chat-dots-fill',$db->queryOne("SELECT COUNT(*) c FROM inquiries WHERE property_id=?",[$propId])['c'], 'Inquiries'],
                  ['bi-calendar-check',$db->queryOne("SELECT COUNT(*) c FROM bookings WHERE property_id=?",[$propId])['c'],  'Bookings'],
                ];
                foreach ($statsData as [$icon, $val, $label]):
                ?>
                <div class="ep-stat-row">
                  <div class="ep-stat-icon"><i class="bi <?= $icon ?>"></i></div>
                  <div class="ep-stat-info">
                    <strong><?= $val ?></strong>
                    <span><?= $label ?></span>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Quick Links -->
            <div class="ep-quick-links">
              <h6>দ্রুত লিঙ্ক</h6>
              <a href="?page=property&id=<?= $propId ?>" target="_blank" class="ep-quick-link">
                <i class="bi bi-eye me-2"></i>Live Page দেখুন
              </a>
              <a href="?page=my-inquiries" class="ep-quick-link">
                <i class="bi bi-chat-dots me-2"></i>Inquiries দেখুন
              </a>
              <?php if ($role === 'admin'): ?>
              <a href="?page=admin-dashboard&tab=properties" class="ep-quick-link">
                <i class="bi bi-arrow-left me-2"></i>Admin Panel
              </a>
              <?php else: ?>
              <a href="?page=agent-dashboard&tab=properties" class="ep-quick-link">
                <i class="bi bi-arrow-left me-2"></i>আমার Properties
              </a>
              <?php endif; ?>
            </div>

            <!-- Danger Zone (Admin only) -->
            <?php if ($role === 'admin'): ?>
            <div class="ep-danger-zone">
              <h6><i class="bi bi-exclamation-triangle me-1"></i>বিপদ অঞ্চল</h6>
              <form method="POST" action="?page=admin-dashboard"
                    onsubmit="return confirm('এই property চিরতরে মুছে ফেলবেন?')">
                <input type="hidden" name="action" value="delete_property">
                <input type="hidden" name="id"     value="<?= $propId ?>">
                <input type="hidden" name="tab"    value="properties">
                <button type="submit" class="ep-delete-btn">
                  <i class="bi bi-trash3 me-2"></i>Property মুছুন
                </button>
              </form>
            </div>
            <?php endif; ?>

          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<!-- ══ Leaflet Map ══ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<!-- ══════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════ -->
<script>
/* ── Map Init ── */
const initLat = <?= $prop['latitude']  ?: '23.8103' ?>;
const initLng = <?= $prop['longitude'] ?: '90.4125' ?>;

const epMap = L.map('epMap').setView([initLat, initLng], <?= $prop['latitude'] ? '15' : '12' ?>);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(epMap);

let epMarker = null;
<?php if ($prop['latitude'] && $prop['longitude']): ?>
epMarker = L.marker([initLat, initLng]).addTo(epMap);
<?php endif; ?>

epMap.on('click', e => {
  const {lat, lng} = e.latlng;
  document.getElementById('epLat').value = lat.toFixed(7);
  document.getElementById('epLng').value = lng.toFixed(7);
  if (epMarker) epMarker.setLatLng(e.latlng);
  else epMarker = L.marker(e.latlng).addTo(epMap);
});

/* ── Number Stepper ── */
function stepEP(id, delta) {
  const el  = document.getElementById('ep_' + id);
  const val = Math.max(0, parseInt(el.value || 0) + delta);
  el.value  = val;
}

/* ── Amenity Count ── */
function updateAmenityCount() {
  const count = document.querySelectorAll('[name="amenities[]"]:checked').length;
  document.getElementById('amenityCount').textContent = count + ' টি selected';
}

/* ── Handle checkbox styling ── */
document.querySelectorAll('.ep-amenity-card input[type="checkbox"]').forEach(cb => {
  cb.addEventListener('change', function() {
    this.closest('.ep-amenity-card').classList.toggle('checked', this.checked);
    updateAmenityCount();
  });
});

/* ── New Image Preview ── */
let newImages = [];

function previewNewImages(files) {
  Array.from(files).forEach((file, i) => {
    if (!file.type.startsWith('image/')) return;
    if (file.size > 5 * 1024 * 1024) {
      showToast(file.name + ' — 5MB এর বেশি!', 'error');
      return;
    }
    const reader = new FileReader();
    const idx    = newImages.length;
    newImages.push(file);
    reader.onload = e => renderNewPreview(idx, e.target.result, i === 0 && newImages.length === 1);
    reader.readAsDataURL(file);
  });
}

function renderNewPreview(idx, src, isFirst) {
  const grid = document.getElementById('newPreviewGrid');
  const div  = document.createElement('div');
  div.className    = 'ep-new-preview-item';
  div.id           = 'np-' + idx;
  div.innerHTML    = `
    <img src="${src}" alt="">
    ${isFirst ? '<div class="ep-new-first-badge">নতুন ১ম</div>' : ''}
    <button type="button" class="ep-new-preview-remove" onclick="removeNewPreview(${idx})">
      <i class="bi bi-x"></i>
    </button>
  `;
  grid.appendChild(div);
}

function removeNewPreview(idx) {
  document.getElementById('np-' + idx)?.remove();
  newImages[idx] = null;
}

/* ── Drop Zone ── */
function handleDrop(e) {
  e.preventDefault();
  e.currentTarget.classList.remove('drag-over');
  previewNewImages(e.dataTransfer.files);
}

/* ── Set Cover Image (AJAX) ── */
function setCover(imageId, propId) {
  fetch('?page=edit-property&id=' + propId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=set_cover&image_id=${imageId}&csrf_token=<?= $_SESSION['csrf_token'] ?>`,
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      /* Remove all cover badges */
      document.querySelectorAll('.ep-img-cover-badge').forEach(b => b.remove());
      document.querySelectorAll('.ep-img-item').forEach(i => i.classList.remove('is-cover'));
      /* Add cover badge to this image */
      const item = document.getElementById('epimg-' + imageId);
      if (item) {
        item.classList.add('is-cover');
        item.insertAdjacentHTML('beforeend', '<div class="ep-img-cover-badge"><i class="bi bi-star-fill me-1"></i>Cover</div>');
        /* Remove star button from this item */
        item.querySelector('.ep-img-btn.star')?.remove();
        /* Update header thumb */
        const thumb = document.getElementById('headerThumb');
        if (thumb) thumb.src = item.querySelector('img')?.src || '';
      }
      showToast('Cover image সেট হয়েছে! ⭐', 'success');
    }
  })
  .catch(() => showToast('কিছু সমস্যা হয়েছে', 'error'));
}

/* ── Delete Image (AJAX) ── */
function deleteImg(imageId, propId) {
  if (!confirm('এই ছবি মুছে ফেলবেন?')) return;

  fetch('?page=edit-property&id=' + propId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=delete_image&image_id=${imageId}&csrf_token=<?= $_SESSION['csrf_token'] ?>`,
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const item = document.getElementById('epimg-' + imageId);
      if (item) {
        item.style.opacity  = '0';
        item.style.transform= 'scale(0.8)';
        item.style.transition= 'all 0.3s';
        setTimeout(() => {
          item.remove();
          /* Update count */
          const remaining = document.querySelectorAll('.ep-img-item').length;
          document.querySelector('.ep-img-count').textContent = remaining + ' টি ছবি';
        }, 300);
      }
      showToast('ছবি মুছে গেছে।', 'success');
    } else {
      showToast(data.message || 'মুছতে পারেনি।', 'error');
    }
  })
  .catch(() => showToast('কিছু সমস্যা হয়েছে', 'error'));
}

/* ── Save Button Loading ── */
document.getElementById('editPropForm')?.addEventListener('submit', function(e) {
  const btn = document.getElementById('epSaveBtn');
  if (btn) {
    btn.disabled = true;
    btn.querySelector('.ep-save-btn-inner').innerHTML =
      '<span class="spinner-border spinner-border-sm me-2"></span>সংরক্ষণ হচ্ছে...';
  }
});

/* ── Sticky Scroll Indicator ── */
const sections = [
  { el: document.getElementById('sec-basic'),     label: 'মূল তথ্য' },
  { el: document.getElementById('sec-images'),    label: 'ছবি' },
  { el: document.getElementById('sec-amenities'), label: 'সুবিধা' },
];

const scrollObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      /* Could add scroll-based section indicator here */
    }
  });
}, { threshold: 0.3 });

sections.forEach(s => { if (s.el) scrollObserver.observe(s.el); });
</script>