<?php
// includes/functions.php

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
