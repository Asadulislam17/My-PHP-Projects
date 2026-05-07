<div class="dashboard">
    <div class="container">

        <div class="dashboard__header">
            <div>
                <h1 class="dashboard__title">My Dashboard</h1>
                <p class="dashboard__sub">Welcome back, <strong><?= htmlspecialchars($auth['name'] ?? '') ?></strong></p>
            </div>
            <a href="/properties" class="btn btn--primary">
                <i class="fa-solid fa-search"></i> Browse Properties
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-widget">
                <div class="stat-widget__icon" style="background:#dbeafe;color:#2563eb">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div class="stat-widget__body">
                    <div class="stat-widget__num"><?= $stats['wishlist'] ?></div>
                    <div class="stat-widget__label">Saved Properties</div>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-widget__icon" style="background:#dcfce7;color:#15803d">
                    <i class="fa-solid fa-message"></i>
                </div>
                <div class="stat-widget__body">
                    <div class="stat-widget__num"><?= $stats['inquiries'] ?></div>
                    <div class="stat-widget__label">My Inquiries</div>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-widget__icon" style="background:#fef3c7;color:#d97706">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="stat-widget__body">
                    <div class="stat-widget__num"><?= $stats['bookings'] ?></div>
                    <div class="stat-widget__label">Tour Bookings</div>
                </div>
            </div>
        </div>

        <!-- Recently Viewed -->
        <?php if (!empty($recentlyViewed)): ?>
        <div class="dashboard__section">
            <h2 class="section-title">Recently Viewed</h2>
            <div class="property-grid">
                <?php foreach ($recentlyViewed as $p): ?>
                <a href="/property/<?= htmlspecialchars($p['slug']) ?>" class="property-card">
                    <?php if ($p['thumb']): ?>
                        <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>" class="property-card__img">
                    <?php else: ?>
                        <div class="property-card__img property-card__img--placeholder">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    <?php endif; ?>
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

        <!-- Quick Links -->
        <div class="dashboard__section">
            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-links">
                <a href="/buyer/wishlist"   class="quick-link"><i class="fa-solid fa-heart"></i> My Wishlist</a>
                <a href="/buyer/inquiries"  class="quick-link"><i class="fa-solid fa-message"></i> My Inquiries</a>
                <a href="/buyer/bookings"   class="quick-link"><i class="fa-solid fa-calendar"></i> My Bookings</a>
                <a href="/buyer/profile"    class="quick-link"><i class="fa-solid fa-user"></i> Edit Profile</a>
            </div>
        </div>

    </div>
</div>
