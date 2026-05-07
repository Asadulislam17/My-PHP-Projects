<div class="page-header">
    <div class="container">
        <h1 class="page-header__title"><?= htmlspecialchars($title ?: 'All Properties') ?></h1>

        <!-- Filter Bar -->
        <form class="filter-bar" method="GET" action="/properties">
            <div class="filter-bar__grid">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach (['apartment'=>'Apartment','house'=>'House','commercial'=>'Commercial','land'=>'Land','villa'=>'Villa','office'=>'Office'] as $val=>$label): ?>
                        <option value="<?= $val ?>" <?= ($filters['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="form-control">
                    <option value="">Sale / Rent</option>
                    <option value="sale" <?= ($filters['status'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                    <option value="rent" <?= ($filters['status'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                </select>

                <input type="text" name="city" class="form-control"
                       placeholder="City" value="<?= htmlspecialchars($filters['city'] ?? '') ?>">

                <input type="number" name="min_price" class="form-control"
                       placeholder="Min Price ৳" value="<?= htmlspecialchars($filters['min_price'] ?? '') ?>">

                <input type="number" name="max_price" class="form-control"
                       placeholder="Max Price ৳" value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>">

                <select name="bedrooms" class="form-control">
                    <option value="">Bedrooms</option>
                    <?php foreach ([1,2,3,4,5] as $n): ?>
                        <option value="<?= $n ?>" <?= ($filters['bedrooms'] ?? '') == $n ? 'selected' : '' ?>><?= $n ?>+</option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="form-control">
                    <?php foreach (['newest'=>'Newest First','price_asc'=>'Price: Low–High','price_desc'=>'Price: High–Low','popular'=>'Most Popular'] as $val=>$label): ?>
                        <option value="<?= $val ?>" <?= ($filters['sort'] ?? 'newest') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="/properties" class="btn btn--ghost">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:3rem">

    <?php if (empty($properties)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-building fa-3x"></i>
            <h3>No Properties Found</h3>
            <p>Try adjusting your filters or <a href="/properties">view all listings</a>.</p>
        </div>
    <?php else: ?>

        <div class="listing-meta">
            <span><?= number_format($pagination['total']) ?> properties found</span>
        </div>

        <div class="property-grid">
            <?php foreach ($properties as $p): ?>
            <a href="/property/<?= htmlspecialchars($p['slug']) ?>" class="property-card">
                <div class="property-card__media">
                    <?php if ($p['thumb']): ?>
                        <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="property-card__img" loading="lazy">
                    <?php else: ?>
                        <div class="property-card__img property-card__img--placeholder">
                            <i class="fa-solid fa-image fa-2x"></i>
                        </div>
                    <?php endif; ?>

                    <?php if ($p['is_featured']): ?>
                        <span class="property-card__featured"><i class="fa-solid fa-star"></i> Featured</span>
                    <?php endif; ?>
                    <span class="property-card__badge property-card__badge--<?= $p['status'] ?>">
                        For <?= ucfirst($p['status']) ?>
                    </span>
                </div>

                <div class="property-card__body">
                    <div class="property-card__title"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="property-card__location">
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($p['area'] . ', ' . $p['city']) ?>
                    </div>
                    <div class="property-card__price">৳<?= number_format($p['price']) ?></div>

                    <div class="property-card__meta">
                        <?php if ($p['bedrooms']): ?>
                            <span><i class="fa-solid fa-bed"></i> <?= $p['bedrooms'] ?> Bed</span>
                        <?php endif; ?>
                        <?php if ($p['bathrooms']): ?>
                            <span><i class="fa-solid fa-bath"></i> <?= $p['bathrooms'] ?> Bath</span>
                        <?php endif; ?>
                        <?php if ($p['area_sqft']): ?>
                            <span><i class="fa-solid fa-ruler-combined"></i> <?= number_format($p['area_sqft']) ?> sqft</span>
                        <?php endif; ?>
                    </div>

                    <div class="property-card__footer">
                        <div class="property-card__agent">
                            <i class="fa-solid fa-user-tie"></i> <?= htmlspecialchars($p['agent_name']) ?>
                        </div>
                        <div class="property-card__views">
                            <i class="fa-solid fa-eye"></i> <?= number_format($p['views']) ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['current_page'] > 1): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>" class="pagination__btn">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
                   class="pagination__btn <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>" class="pagination__btn">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
