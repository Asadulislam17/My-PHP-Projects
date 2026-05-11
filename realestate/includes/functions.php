<?php
function renderPropertyCard(array $prop, Auth $auth): string {

    // =========================
    // SAFE VALUES (NO ERROR)
    // =========================
    $id = $prop['id'] ?? 0;
    $title = htmlspecialchars($prop['title'] ?? 'No Title');

    $coverImage = !empty($prop['cover_image'])
        ? UPLOAD_URL . 'properties/' . htmlspecialchars($prop['cover_image'])
        : APP_URL . '/assets/images/no-image.webp';

    $price = number_format($prop['price'] ?? 0);
    $priceType = $prop['price_type'] ?? 'sale';
    $priceLabel = $priceType === 'rent' ? '/মাস' : '';

    $area = htmlspecialchars($prop['area_name'] ?? 'Unknown Area');
    $district = htmlspecialchars($prop['district_name'] ?? 'Unknown District');

    $agentName = $prop['agent_name'] ?? 'Unknown Agent';
    $agentSafe = htmlspecialchars($agentName);
    $agentInit = strtoupper(substr($agentName, 0, 1));

    // =========================
    // WISHLIST STATUS
    // =========================
    $isWishlisted = false;

    if ($auth->isLoggedIn()) {
        $isWishlisted = false; // DB check বসাতে পারো পরে
    }

    // =========================
    // BADGES
    // =========================
    $verified = !empty($prop['is_verified'])
        ? "<span class='badge-verified'><i class='bi bi-patch-check-fill me-1'></i>যাচাইকৃত</span>"
        : "";

    $featured = !empty($prop['is_featured'])
        ? "<span class='badge-featured'><i class='bi bi-star-fill me-1'></i>Featured</span>"
        : "";

    $typeLabel = $priceType === 'rent' ? 'ভাড়া' : 'বিক্রয়';
    $typeClass = $priceType === 'rent' ? 'badge-rent' : 'badge-sale';

    // =========================
    // SPECIFICATIONS
    // =========================
    $bedrooms = !empty($prop['bedrooms'])
        ? "<span><i class='bi bi-door-open'></i> {$prop['bedrooms']} বেড</span>"
        : "";

    $bathrooms = !empty($prop['bathrooms'])
        ? "<span><i class='bi bi-droplet'></i> {$prop['bathrooms']} বাথ</span>"
        : "";

    $size = !empty($prop['size_sqft'])
        ? "<span><i class='bi bi-rulers'></i> " . number_format($prop['size_sqft']) . " sqft</span>"
        : "";

    // =========================
    // RETURN HTML
    // =========================
    return "
    <div class='property-card'>

        <!-- IMAGE -->
        <div class='prop-image'>
            <a href='?page=property&id={$id}'>
                <img src='{$coverImage}' alt='{$title}' loading='lazy'>
            </a>

            <div class='prop-badges'>
                <span class='badge-type {$typeClass}'>{$typeLabel}</span>
                {$verified}
                {$featured}
            </div>

            <button class='wishlist-btn " . ($isWishlisted ? 'active' : '') . "'
                    onclick='toggleWishlist({$id}, this)'
                    title='Wishlist'>
                <i class='bi bi-heart" . ($isWishlisted ? '-fill' : '') . "'></i>
            </button>
        </div>

        <!-- INFO -->
        <div class='prop-info'>

            <div class='prop-location'>
                <i class='bi bi-geo-alt text-accent'></i>
                {$area}, {$district}
            </div>

            <h5 class='prop-title'>
                <a href='?page=property&id={$id}'>{$title}</a>
            </h5>

            <div class='prop-price'>
                ৳ {$price} <small>{$priceLabel}</small>
            </div>

            <div class='prop-specs'>
                {$bedrooms}
                {$bathrooms}
                {$size}
            </div>

            <div class='prop-footer'>
                <div class='agent-mini'>
                    <div class='agent-avatar-sm'>{$agentInit}</div>
                    <small>{$agentSafe}</small>
                </div>

                <a href='?page=property&id={$id}' class='btn-view-prop'>
                    বিস্তারিত <i class='bi bi-arrow-right ms-1'></i>
                </a>
            </div>

        </div>

    </div>";
}
?>