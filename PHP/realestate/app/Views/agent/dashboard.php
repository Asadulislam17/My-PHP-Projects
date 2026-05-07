<div class="dashboard">
    <div class="container">

        <div class="dashboard__header">
            <div>
                <h1 class="dashboard__title">Agent Dashboard</h1>
                <p class="dashboard__sub">Welcome, <strong><?= htmlspecialchars($auth['name'] ?? '') ?></strong></p>
            </div>
            <a href="/agent/properties/create" class="btn btn--primary">
                <i class="fa-solid fa-plus"></i> Add Property
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid stats-grid--4">
            <?php
            $widgets = [
                ['icon' => 'fa-building',        'color' => '#dbeafe', 'text_color' => '#2563eb', 'num' => $stats['properties'], 'label' => 'My Listings'],
                ['icon' => 'fa-message',          'color' => '#dcfce7', 'text_color' => '#15803d', 'num' => $stats['inquiries'],  'label' => 'New Inquiries'],
                ['icon' => 'fa-calendar-check',   'color' => '#fef3c7', 'text_color' => '#d97706', 'num' => $stats['bookings'],   'label' => 'Pending Tours'],
                ['icon' => 'fa-eye',              'color' => '#f3e8ff', 'text_color' => '#7c3aed', 'num' => number_format($stats['views']), 'label' => 'Total Views'],
            ];
            foreach ($widgets as $w): ?>
            <div class="stat-widget">
                <div class="stat-widget__icon" style="background:<?= $w['color'] ?>;color:<?= $w['text_color'] ?>">
                    <i class="fa-solid <?= $w['icon'] ?>"></i>
                </div>
                <div class="stat-widget__body">
                    <div class="stat-widget__num"><?= $w['num'] ?></div>
                    <div class="stat-widget__label"><?= $w['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recent Properties -->
        <?php if (!empty($recentProperties)): ?>
        <div class="dashboard__section">
            <div class="section-header">
                <h2 class="section-title">Recent Listings</h2>
                <a href="/agent/properties" class="link-more">View all →</a>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentProperties as $p): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.75rem">
                                    <?php if ($p['thumb']): ?>
                                        <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                                             style="width:48px;height:36px;object-fit:cover;border-radius:4px">
                                    <?php else: ?>
                                        <div style="width:48px;height:36px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#aaa">
                                            <i class="fa-solid fa-image fa-xs"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars(mb_strimwidth($p['title'], 0, 40, '…')) ?></span>
                                </div>
                            </td>
                            <td><span class="badge"><?= ucfirst($p['type']) ?></span></td>
                            <td>৳<?= number_format($p['price']) ?></td>
                            <td>
                                <span class="badge badge--<?= $p['status'] === 'sale' ? 'sale' : 'rent' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge--<?= $p['approval_status'] ?>">
                                    <?= ucfirst($p['approval_status']) ?>
                                </span>
                            </td>
                            <td><?= number_format($p['views']) ?></td>
                            <td>
                                <a href="/agent/properties/<?= $p['id'] ?>/edit" class="btn btn--sm btn--ghost">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Links -->
        <div class="dashboard__section">
            <div class="quick-links">
                <a href="/agent/properties"  class="quick-link"><i class="fa-solid fa-building"></i> My Properties</a>
                <a href="/agent/inquiries"   class="quick-link"><i class="fa-solid fa-message"></i> Inquiries</a>
                <a href="/agent/bookings"    class="quick-link"><i class="fa-solid fa-calendar"></i> Tour Requests</a>
                <a href="/agent/subscription" class="quick-link"><i class="fa-solid fa-crown"></i> Subscription</a>
            </div>
        </div>

    </div>
</div>
