<?php
require_once __DIR__ . '/../classes/Property.php';
$propClass = Property::getInstance();

// Get IDs from URL
$idsRaw = $_GET['ids'] ?? '';
$ids    = array_unique(array_filter(array_map('intval', explode(',', $idsRaw))));
$ids    = array_slice($ids, 0, 3); // max 3

$properties = [];
foreach ($ids as $id) {
    $p = $propClass->getById($id);
    if ($p && $p['status'] === 'approved') $properties[] = $p;
}

$count = count($properties);
?>

<div class="compare-page">

  <!-- Header -->
  <div class="inner-page-header">
    <div class="container">
      <div class="iph-content">
        <div>
          <h1><i class="bi bi-layers-fill text-accent me-3"></i>Property তুলনা</h1>
          <p><?= $count ?> টি property তুলনা করা হচ্ছে</p>
        </div>
        <a href="?page=listing" class="btn-accent-outline">
          <i class="bi bi-plus-circle me-2"></i>Property যোগ করুন
        </a>
      </div>
    </div>
  </div>

  <div class="container py-5">

    <?php if ($count < 2): ?>
    <div class="empty-page-state">
      <div class="eps-icon"><i class="bi bi-layers"></i></div>
      <h3>কমপক্ষে ২ টি Property বেছে নিন</h3>
      <p>Property detail page এ "তুলনায় যোগ করুন" বাটন ক্লিক করুন</p>
      <a href="?page=listing" class="btn-accent-lg">
        <i class="bi bi-search me-2"></i>Property দেখুন
      </a>
    </div>
    <?php else: ?>

    <!-- Comparison Table -->
    <div class="compare-table-wrap">
      <table class="compare-table">

        <!-- Images Row -->
        <tr class="compare-images-row">
          <td class="compare-label-col">
            <div class="compare-section-title">Property</div>
          </td>
          <?php foreach ($properties as $prop): ?>
          <?php
          $cover = '';
          foreach ($prop['images'] as $img) {
            if ($img['is_cover']) { $cover = $img['image_path']; break; }
          }
          if (!$cover && !empty($prop['images'])) $cover = $prop['images'][0]['image_path'];
          ?>
          <td class="compare-prop-col">
            <div class="compare-prop-card">
              <div class="compare-prop-img">
                <?php if ($cover): ?>
                <img src="<?= UPLOAD_URL ?>properties/<?= htmlspecialchars($cover) ?>" alt="">
                <?php else: ?>
                <div class="compare-no-img"><i class="bi bi-image"></i></div>
                <?php endif; ?>
              </div>
              <div class="compare-prop-info">
                <h5>
                  <a href="?page=property&id=<?= $prop['id'] ?>">
                    <?= htmlspecialchars(substr($prop['title'],0,45)) ?>...
                  </a>
                </h5>
                <div class="compare-prop-location">
                  <i class="bi bi-geo-alt text-accent"></i>
                  <?= htmlspecialchars($prop['area_name']) ?>
                </div>
                <div class="compare-prop-price">
                  ৳<?= number_format($prop['price']) ?>
                  <?= $prop['price_type']==='rent' ? '<small>/মাস</small>' : '' ?>
                </div>
              </div>
              <a href="?page=property&id=<?= $prop['id'] ?>" class="compare-view-btn">
                বিস্তারিত <i class="bi bi-arrow-right ms-1"></i>
              </a>
              <button class="compare-remove-btn"
                      onclick="removeFromCompare(<?= $prop['id'] ?>)">
                <i class="bi bi-x"></i>
              </button>
            </div>
          </td>
          <?php endforeach; ?>
          <!-- Empty slot -->
          <?php for ($i = $count; $i < 3; $i++): ?>
          <td class="compare-prop-col">
            <div class="compare-empty-slot">
              <div class="ces-icon"><i class="bi bi-plus-circle"></i></div>
              <p>Property যোগ করুন</p>
              <a href="?page=listing" class="compare-add-btn">Browse করুন</a>
            </div>
          </td>
          <?php endfor; ?>
        </tr>

        <?php
        // Define comparison rows
        $rows = [
          ['section' => 'মূল তথ্য', 'rows' => [
            ['key'=>'price',      'label'=>'মূল্য',        'format'=>fn($v)=>'৳'.number_format($v)],
            ['key'=>'price_type', 'label'=>'ধরন',          'format'=>fn($v)=>$v==='rent'?'ভাড়া':'বিক্রয়'],
            ['key'=>'type_name',  'label'=>'টাইপ',         'format'=>fn($v)=>$v],
            ['key'=>'status',     'label'=>'অবস্থা',       'format'=>fn($v)=>ucfirst($v)],
          ]],
          ['section' => 'আয়তন ও কক্ষ', 'rows' => [
            ['key'=>'size_sqft',    'label'=>'আয়তন',        'format'=>fn($v)=>$v?number_format($v).' sqft':'—'],
            ['key'=>'bedrooms',     'label'=>'বেডরুম',       'format'=>fn($v)=>$v?$v.' টি':'—'],
            ['key'=>'bathrooms',    'label'=>'বাথরুম',       'format'=>fn($v)=>$v?$v.' টি':'—'],
            ['key'=>'floor_no',     'label'=>'ফ্লোর নং',    'format'=>fn($v)=>$v?:'—'],
            ['key'=>'total_floors', 'label'=>'মোট ফ্লোর',   'format'=>fn($v)=>$v?:'—'],
          ]],
          ['section' => 'অবস্থান', 'rows' => [
            ['key'=>'area_name',     'label'=>'এলাকা',      'format'=>fn($v)=>$v],
            ['key'=>'district_name', 'label'=>'জেলা',       'format'=>fn($v)=>$v],
            ['key'=>'address',       'label'=>'ঠিকানা',     'format'=>fn($v)=>$v?:'—'],
            ['key'=>'facing',        'label'=>'দিকমুখী',    'format'=>fn($v)=>$v?ucfirst($v):'—'],
          ]],
          ['section' => 'বিশেষত্ব', 'rows' => [
            ['key'=>'year_built',  'label'=>'নির্মাণ বছর',  'format'=>fn($v)=>$v?:'—'],
            ['key'=>'parking',     'label'=>'পার্কিং',      'format'=>fn($v)=>$v?'✓ আছে':'✗ নেই'],
            ['key'=>'is_verified', 'label'=>'যাচাইকৃত',    'format'=>fn($v)=>$v?'✓ হ্যাঁ':'✗ না'],
            ['key'=>'is_featured', 'label'=>'Featured',     'format'=>fn($v)=>$v?'✓ হ্যাঁ':'✗ না'],
            ['key'=>'views_count', 'label'=>'মোট ভিউ',     'format'=>fn($v)=>number_format($v)],
          ]],
        ];

        foreach ($rows as $section): ?>

        <!-- Section Header -->
        <tr class="compare-section-header">
          <td colspan="<?= $count + 1 ?>"><?= $section['section'] ?></td>
        </tr>

        <?php foreach ($section['rows'] as $row): ?>
        <tr class="compare-row">
          <td class="compare-label"><?= $row['label'] ?></td>
          <?php
          // Find best value for numeric comparison
          $values = array_map(fn($p) => $p[$row['key']] ?? null, $properties);
          $numericValues = array_filter($values, 'is_numeric');
          $maxVal = $numericValues ? max($numericValues) : null;

          foreach ($properties as $prop):
            $val     = $prop[$row['key']] ?? null;
            $display = ($row['format'])($val);
            $isBest  = $maxVal !== null && is_numeric($val) && $val == $maxVal && $count > 1;

            // Special case: price — lower is better
            if ($row['key'] === 'price' && $numericValues) {
              $isBest = $val == min($numericValues);
            }
          ?>
          <td class="compare-value <?= $isBest ? 'best-value' : '' ?>">
            <?= htmlspecialchars($display) ?>
            <?php if ($isBest): ?>
            <span class="best-badge">সেরা</span>
            <?php endif; ?>
          </td>
          <?php endforeach; ?>
          <!-- Empty cols -->
          <?php for ($i=$count;$i<3;$i++): ?><td class="compare-value empty">—</td><?php endfor; ?>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- Amenities Row -->
        <tr class="compare-section-header">
          <td colspan="<?= $count + 1 ?>">সুযোগ-সুবিধা</td>
        </tr>
        <tr class="compare-row amenities-row">
          <td class="compare-label">Amenities</td>
          <?php foreach ($properties as $prop): ?>
          <td class="compare-value">
            <div class="compare-amenities">
              <?php if (empty($prop['amenities'])): ?>
              <span class="no-amenity">—</span>
              <?php else: ?>
              <?php foreach ($prop['amenities'] as $am): ?>
              <span class="amenity-chip">
                <i class="bi bi-check-circle-fill text-accent me-1"></i>
                <?= htmlspecialchars($am['name']) ?>
              </span>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </td>
          <?php endforeach; ?>
          <?php for ($i=$count;$i<3;$i++): ?><td class="compare-value empty">—</td><?php endfor; ?>
        </tr>

        <!-- CTA Row -->
        <tr class="compare-cta-row">
          <td></td>
          <?php foreach ($properties as $prop): ?>
          <td>
            <div class="compare-cta">
              <a href="?page=property&id=<?= $prop['id'] ?>" class="cta-inquiry-btn">
                <i class="bi bi-send me-1"></i>Inquiry করুন
              </a>
              <a href="?page=property&id=<?= $prop['id'] ?>#bookingSection" class="cta-book-btn">
                <i class="bi bi-calendar me-1"></i>Tour Book
              </a>
            </div>
          </td>
          <?php endforeach; ?>
          <?php for ($i=$count;$i<3;$i++): ?><td></td><?php endfor; ?>
        </tr>

      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function removeFromCompare(id) {
  const ids = new URLSearchParams(window.location.search).get('ids');
  const newIds = ids.split(',').filter(i=>parseInt(i)!==id).join(',');
  window.location.href = '?page=compare&ids=' + newIds;
}
</script>