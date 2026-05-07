<?php $csrf = $_SESSION['_csrf_token'] ?? ''; ?>

<!-- Property Hero Gallery -->
<div class="property-gallery">
    <?php if (!empty($images)): ?>
        <div class="gallery__main">
            <img id="gallery_main_img"
                 src="/public/uploads/images/<?= htmlspecialchars($images[0]['file_name']) ?>"
                 alt="<?= htmlspecialchars($property['title']) ?>">
            <?php if ($property['is_featured']): ?>
                <span class="gallery__badge"><i class="fa-solid fa-star"></i> Featured</span>
            <?php endif; ?>
        </div>
        <?php if (count($images) > 1): ?>
        <div class="gallery__thumbs">
            <?php foreach ($images as $i => $img): ?>
                <img src="/public/uploads/images/<?= htmlspecialchars($img['thumbnail'] ?: $img['file_name']) ?>"
                     alt="Image <?= $i+1 ?>"
                     class="gallery__thumb <?= $i === 0 ? 'active' : '' ?>"
                     onclick="setMainImg('/public/uploads/images/<?= htmlspecialchars($img['file_name']) ?>', this)">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="gallery__placeholder"><i class="fa-solid fa-image fa-4x"></i></div>
    <?php endif; ?>
</div>

<div class="container property-detail">
    <div class="property-detail__grid">

        <!-- Left: Main Content -->
        <div class="property-detail__main">

            <!-- Header -->
            <div class="property-detail__header">
                <div class="property-detail__badges">
                    <span class="badge badge--<?= $property['status'] ?>">For <?= ucfirst($property['status']) ?></span>
                    <span class="badge"><?= ucfirst($property['type']) ?></span>
                </div>
                <h1 class="property-detail__title"><?= htmlspecialchars($property['title']) ?></h1>
                <div class="property-detail__location">
                    <i class="fa-solid fa-location-dot"></i>
                    <?= htmlspecialchars($property['address'] . ', ' . $property['area'] . ', ' . $property['city']) ?>
                </div>
                <div class="property-detail__price">৳<?= number_format($property['price']) ?>
                    <?php if ($property['status'] === 'rent'): ?>
                        <span style="font-size:1rem;font-weight:400;color:var(--color-gray)">/month</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Key Facts -->
            <div class="property-facts">
                <?php $facts = [
                    ['fa-ruler-combined', number_format($property['area_sqft'] ?? 0) . ' sqft', 'Area'],
                    ['fa-bed',  $property['bedrooms']  . ' Bedrooms',  'Bedrooms'],
                    ['fa-bath', $property['bathrooms'] . ' Bathrooms', 'Bathrooms'],
                    ['fa-layer-group', $property['floors'] . ' Floors', 'Floors'],
                    ['fa-car',   $property['parking']   ? 'Yes' : 'No', 'Parking'],
                    ['fa-couch', $property['furnished'] ? 'Yes' : 'No', 'Furnished'],
                ];
                foreach ($facts as [$icon, $value, $label]): if (!$value || $value === '0 Bedrooms'): continue; endif; ?>
                <div class="property-fact">
                    <i class="fa-solid <?= $icon ?>"></i>
                    <div>
                        <div class="property-fact__value"><?= htmlspecialchars($value) ?></div>
                        <div class="property-fact__label"><?= $label ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Description -->
            <div class="property-section">
                <h2 class="property-section__title">Description</h2>
                <div class="property-description">
                    <?= nl2br(htmlspecialchars($property['description'])) ?>
                </div>
            </div>

            <!-- Map -->
            <?php if ($property['latitude'] && $property['longitude']): ?>
            <div class="property-section">
                <h2 class="property-section__title">Location</h2>
                <div id="property_map" class="property-map"
                     data-lat="<?= $property['latitude'] ?>"
                     data-lng="<?= $property['longitude'] ?>"
                     data-title="<?= htmlspecialchars($property['title']) ?>">
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--color-gray)">
                        <i class="fa-solid fa-map-location-dot fa-2x"></i>
                        <span style="margin-left:.5rem">Map loading…</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- YouTube / Virtual Tour -->
            <?php if ($property['youtube_url']): ?>
            <div class="property-section">
                <h2 class="property-section__title">Video Tour</h2>
                <div class="property-video">
                    <?php
                    preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{11})/', $property['youtube_url'], $m);
                    $videoId = $m[1] ?? '';
                    ?>
                    <?php if ($videoId): ?>
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($videoId) ?>"
                                allowfullscreen loading="lazy"></iframe>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right: Sidebar -->
        <div class="property-detail__sidebar">

            <!-- Agent Card -->
            <div class="agent-card">
                <div class="agent-card__header">
                    <?php if ($property['agent_avatar']): ?>
                        <img src="/public/uploads/images/<?= htmlspecialchars($property['agent_avatar']) ?>"
                             class="agent-card__avatar" alt="<?= htmlspecialchars($property['agent_name']) ?>">
                    <?php else: ?>
                        <div class="agent-card__avatar-placeholder">
                            <i class="fa-solid fa-user-tie fa-2x"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="agent-card__name"><?= htmlspecialchars($property['agent_name']) ?></div>
                        <div class="agent-card__role">Real Estate Agent</div>
                    </div>
                </div>
                <?php if ($property['agent_phone']): ?>
                    <a href="tel:<?= htmlspecialchars($property['agent_phone']) ?>" class="btn btn--outline btn--block">
                        <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($property['agent_phone']) ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Inquiry Form -->
            <div class="inquiry-card">
                <h3 class="inquiry-card__title">Send Inquiry</h3>
                <form method="POST" action="/inquiries" id="inquiry_form">
                    <input type="hidden" name="_token"      value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?>">

                    <div class="form-group">
                        <input type="text" name="name" class="form-control"
                               placeholder="Your Name"
                               value="<?= htmlspecialchars($auth['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control"
                               placeholder="Your Email"
                               value="<?= htmlspecialchars($auth['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Your Phone">
                    </div>
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="4"
                                  placeholder="I am interested in this property…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block">
                        <i class="fa-solid fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Wishlist & Share -->
            <div class="sidebar-actions">
                <?php if ($auth): ?>
                    <button class="btn btn--outline btn--block" data-wishlist="<?= $property['id'] ?>">
                        <i class="fa-solid fa-heart"></i> Save to Wishlist
                    </button>
                <?php endif; ?>
                <a href="/bookings/create?property=<?= $property['id'] ?>" class="btn btn--ghost btn--block">
                    <i class="fa-solid fa-calendar-plus"></i> Schedule Tour
                </a>
            </div>

            <!-- Stats -->
            <div class="property-stats-card">
                <div><i class="fa-solid fa-eye"></i> <?= number_format($property['views']) ?> views</div>
                <div><i class="fa-solid fa-calendar"></i> Listed <?= date('d M Y', strtotime($property['created_at'])) ?></div>
                <?php if ($property['area_sqft'] && $property['price']): ?>
                    <div><i class="fa-solid fa-chart-line"></i>
                        ৳<?= number_format($property['price'] / $property['area_sqft'], 0) ?>/sqft
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Similar Properties -->
    <?php if (!empty($similar)): ?>
    <div class="property-section" style="margin-top:3rem">
        <h2 class="property-section__title">Similar Properties</h2>
        <div class="property-grid">
            <?php foreach ($similar as $p): ?>
            <a href="/property/<?= htmlspecialchars($p['slug']) ?>" class="property-card">
                <div class="property-card__media">
                    <?php if ($p['thumb']): ?>
                        <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                             class="property-card__img" loading="lazy" alt="<?= htmlspecialchars($p['title']) ?>">
                    <?php else: ?>
                        <div class="property-card__img property-card__img--placeholder"><i class="fa-solid fa-image fa-2x"></i></div>
                    <?php endif; ?>
                    <span class="property-card__badge property-card__badge--<?= $p['status'] ?>">For <?= ucfirst($p['status']) ?></span>
                </div>
                <div class="property-card__body">
                    <div class="property-card__title"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="property-card__location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p['area'] . ', ' . $p['city']) ?></div>
                    <div class="property-card__price">৳<?= number_format($p['price']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function setMainImg(src, thumb) {
    document.getElementById('gallery_main_img').src = src;
    document.querySelectorAll('.gallery__thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
</script>
