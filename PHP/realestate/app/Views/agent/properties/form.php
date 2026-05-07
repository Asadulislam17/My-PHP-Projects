<?php
$csrf  = $_SESSION['_csrf_token'] ?? '';
$p     = $property ?? null;
$isEdit = $p !== null;
$old   = $old ?? [];
$v     = fn($field) => htmlspecialchars($old[$field] ?? $p[$field] ?? '');
?>

<div class="container" style="padding:2rem 0 4rem">
    <div class="form-page-header">
        <h1><?= $isEdit ? 'Edit Property' : 'Add New Property' ?></h1>
        <a href="/agent/properties" class="btn btn--ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="flash flash--error" style="margin-bottom:1.5rem">
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $msgs): foreach ((array)$msgs as $m): ?>
                    <li><?= htmlspecialchars($m) ?></li>
                <?php endforeach; endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST"
          action="<?= $isEdit ? "/agent/properties/{$p['id']}/edit" : '/agent/properties/create' ?>"
          enctype="multipart/form-data"
          class="property-form">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="property-form__grid">

            <!-- ── Left Column ─────────────────────────────────────── -->
            <div class="property-form__main">

                <!-- Basic Info -->
                <div class="form-card">
                    <h2 class="form-card__title">Basic Information</h2>

                    <div class="form-group">
                        <label class="form-label">Property Title *</label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="e.g. Spacious 3-Bedroom Apartment in Gulshan"
                               value="<?= $v('title') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-control" required>
                                <?php foreach (['apartment'=>'Apartment','house'=>'House','commercial'=>'Commercial','land'=>'Land','villa'=>'Villa','office'=>'Office'] as $val=>$label): ?>
                                    <option value="<?= $val ?>" <?= $v('type') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Listing For *</label>
                            <select name="status" class="form-control" required>
                                <option value="sale" <?= $v('status') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                                <option value="rent" <?= $v('status') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                                <option value="pending" <?= $v('status') === 'pending' ? 'selected' : '' ?>>Coming Soon</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="6"
                                  placeholder="Describe the property in detail…" required><?= $v('description') ?></textarea>
                    </div>
                </div>

                <!-- Pricing & Size -->
                <div class="form-card">
                    <h2 class="form-card__title">Pricing & Size</h2>
                    <div class="form-row form-row--3">
                        <div class="form-group">
                            <label class="form-label">Price (৳) *</label>
                            <input type="number" name="price" class="form-control" required min="0"
                                   value="<?= $v('price') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Area (sqft)</label>
                            <input type="number" name="area_sqft" class="form-control" min="0" step="0.01"
                                   value="<?= $v('area_sqft') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Floors</label>
                            <input type="number" name="floors" class="form-control" min="1"
                                   value="<?= $v('floors') ?>">
                        </div>
                    </div>
                    <div class="form-row form-row--3">
                        <div class="form-group">
                            <label class="form-label">Bedrooms</label>
                            <input type="number" name="bedrooms" class="form-control" min="0"
                                   value="<?= $v('bedrooms') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bathrooms</label>
                            <input type="number" name="bathrooms" class="form-control" min="0"
                                   value="<?= $v('bathrooms') ?>">
                        </div>
                        <div class="form-group" style="display:flex;align-items:flex-end;gap:1rem;padding-bottom:.25rem">
                            <label class="checkbox-label">
                                <input type="checkbox" name="parking" value="1" <?= $v('parking') ? 'checked' : '' ?>>
                                Parking
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="furnished" value="1" <?= $v('furnished') ? 'checked' : '' ?>>
                                Furnished
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-card">
                    <h2 class="form-card__title">Location</h2>
                    <div class="form-group">
                        <label class="form-label">Full Address *</label>
                        <input type="text" name="address" class="form-control" required
                               placeholder="House/Road/Block, Area"
                               value="<?= $v('address') ?>">
                    </div>
                    <div class="form-row form-row--3">
                        <div class="form-group">
                            <label class="form-label">Area / Neighbourhood *</label>
                            <input type="text" name="area" class="form-control" required
                                   placeholder="e.g. Gulshan-1"
                                   value="<?= $v('area') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" required
                                   placeholder="e.g. Dhaka"
                                   value="<?= $v('city') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Division *</label>
                            <select name="division" class="form-control" required>
                                <?php foreach (['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barishal','Rangpur','Mymensingh'] as $div): ?>
                                    <option value="<?= $div ?>" <?= $v('division') === $div ? 'selected' : '' ?>><?= $div ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control"
                                   placeholder="23.8103" value="<?= $v('latitude') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control"
                                   placeholder="90.4125" value="<?= $v('longitude') ?>">
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="form-card">
                    <h2 class="form-card__title">Photos & Media</h2>

                    <!-- Existing images (edit mode) -->
                    <?php if (!empty($images)): ?>
                    <div class="image-manager" id="image_manager">
                        <p style="font-size:.85rem;color:var(--color-gray);margin-bottom:.75rem">
                            Click ⭐ to set as cover photo. Click 🗑 to remove.
                        </p>
                        <div class="image-grid">
                            <?php foreach ($images as $img): ?>
                            <div class="image-item" id="img_<?= $img['id'] ?>">
                                <img src="/public/uploads/images/<?= htmlspecialchars($img['thumbnail'] ?: $img['file_name']) ?>"
                                     alt="Property image">
                                <?php if ($img['is_primary']): ?>
                                    <span class="image-item__primary-badge">Cover</span>
                                <?php endif; ?>
                                <div class="image-item__actions">
                                    <button type="button" class="image-item__btn"
                                            onclick="setPrimary(<?= $img['id'] ?>, <?= $p['id'] ?>)"
                                            title="Set as cover">⭐</button>
                                    <button type="button" class="image-item__btn image-item__btn--del"
                                            onclick="deleteImg(<?= $img['id'] ?>, <?= $p['id'] ?>)"
                                            title="Delete">🗑</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Upload new images -->
                    <div class="upload-zone" id="upload_zone">
                        <i class="fa-solid fa-cloud-upload-alt fa-2x"></i>
                        <p>Drag & drop photos here, or click to select</p>
                        <small>JPG, PNG, WebP • Max 10MB each • First image becomes cover</small>
                        <input type="file" name="images[]" id="image_input"
                               accept="image/*" multiple style="display:none">
                    </div>
                    <div class="upload-preview" id="upload_preview"></div>

                    <div class="form-group" style="margin-top:1rem">
                        <label class="form-label">YouTube Video URL</label>
                        <input type="url" name="youtube_url" class="form-control"
                               placeholder="https://www.youtube.com/watch?v=..."
                               value="<?= $v('youtube_url') ?>">
                    </div>
                </div>

                <!-- SEO -->
                <div class="form-card">
                    <h2 class="form-card__title">SEO (Optional)</h2>
                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" maxlength="255"
                               value="<?= $v('meta_title') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" maxlength="500"><?= $v('meta_description') ?></textarea>
                    </div>
                </div>

            </div><!-- /.property-form__main -->

            <!-- ── Right Sidebar ─────────────────────────────── -->
            <div class="property-form__sidebar">
                <div class="form-card">
                    <h2 class="form-card__title">Submission</h2>
                    <p style="font-size:.85rem;color:var(--color-gray);margin-bottom:1rem">
                        Your listing will be reviewed by an admin before going live.
                    </p>
                    <button type="submit" class="btn btn--primary btn--block">
                        <i class="fa-solid fa-paper-plane"></i>
                        <?= $isEdit ? 'Update & Resubmit' : 'Submit for Approval' ?>
                    </button>
                    <a href="/agent/properties" class="btn btn--ghost btn--block" style="margin-top:.5rem">
                        Cancel
                    </a>
                </div>
            </div>

        </div><!-- /.property-form__grid -->
    </form>
</div>

<script>
// ── Drag-and-drop upload zone ─────────────────────────────────────── //
const zone    = document.getElementById('upload_zone');
const input   = document.getElementById('image_input');
const preview = document.getElementById('upload_preview');

zone.addEventListener('click', () => input.click());
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', ()  => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    showPreviews(e.dataTransfer.files);
    // Attach to input
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    input.files = dt.files;
});
input.addEventListener('change', () => showPreviews(input.files));

function showPreviews(files) {
    preview.innerHTML = '';
    [...files].forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'upload-thumb';
            div.innerHTML = `<img src="${e.target.result}" alt="${file.name}">
                             <span>${(file.size/1048576).toFixed(1)} MB</span>`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// ── Image management (edit mode) ─────────────────────────────────── //
const CSRF_TOKEN = document.querySelector('input[name="_token"]').value;

async function deleteImg(imageId, propertyId) {
    if (!confirm('Delete this image?')) return;
    const res = await fetch('/agent/properties/image/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ image_id: imageId, property_id: propertyId }),
    });
    const data = await res.json();
    if (data.status) {
        document.getElementById('img_' + imageId)?.remove();
    } else {
        alert(data.message);
    }
}

async function setPrimary(imageId, propertyId) {
    const res = await fetch('/agent/properties/image/primary', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ image_id: imageId, property_id: propertyId }),
    });
    const data = await res.json();
    if (data.status) {
        document.querySelectorAll('.image-item__primary-badge').forEach(b => b.remove());
        const item = document.getElementById('img_' + imageId);
        if (item) {
            const badge = document.createElement('span');
            badge.className = 'image-item__primary-badge';
            badge.textContent = 'Cover';
            item.appendChild(badge);
        }
    }
}
</script>
