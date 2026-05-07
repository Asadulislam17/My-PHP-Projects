<div class="dashboard">
    <div class="container">

        <div class="dashboard__header">
            <div>
                <h1 class="dashboard__title">Admin Dashboard</h1>
                <p class="dashboard__sub">Platform overview – <?= date('l, d F Y') ?></p>
            </div>
        </div>

        <!-- Platform Stats -->
        <div class="stats-grid stats-grid--3">
            <?php
            $widgets = [
                ['icon'=>'fa-users',       'color'=>'#dbeafe','tc'=>'#2563eb','num'=>number_format($stats['users']),      'label'=>'Total Users'],
                ['icon'=>'fa-building',    'color'=>'#dcfce7','tc'=>'#15803d','num'=>number_format($stats['properties']), 'label'=>'Properties'],
                ['icon'=>'fa-clock',       'color'=>'#fef3c7','tc'=>'#d97706','num'=>number_format($stats['pending']),    'label'=>'Pending Approval'],
                ['icon'=>'fa-message',     'color'=>'#f3e8ff','tc'=>'#7c3aed','num'=>number_format($stats['inquiries']),  'label'=>'New Inquiries'],
                ['icon'=>'fa-calendar',    'color'=>'#fce7f3','tc'=>'#be185d','num'=>number_format($stats['bookings']),   'label'=>'Pending Tours'],
                ['icon'=>'fa-bangladeshi-taka-sign','color'=>'#dcfce7','tc'=>'#15803d','num'=>'৳'.number_format($stats['revenue']),'label'=>'Total Revenue'],
            ];
            foreach ($widgets as $w): ?>
            <div class="stat-widget">
                <div class="stat-widget__icon" style="background:<?= $w['color'] ?>;color:<?= $w['tc'] ?>">
                    <i class="fa-solid <?= $w['icon'] ?>"></i>
                </div>
                <div class="stat-widget__body">
                    <div class="stat-widget__num"><?= $w['num'] ?></div>
                    <div class="stat-widget__label"><?= $w['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="dashboard__cols">

            <!-- Pending Properties -->
            <div class="dashboard__section">
                <div class="section-header">
                    <h2 class="section-title">Pending Approval</h2>
                    <a href="/admin/properties?status=pending" class="link-more">View all →</a>
                </div>
                <?php if (empty($pendingProperties)): ?>
                    <p class="empty-msg"><i class="fa-solid fa-check-circle"></i> All caught up!</p>
                <?php else: ?>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Property</th><th>Agent</th><th>Price</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($pendingProperties as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars(mb_strimwidth($p['title'], 0, 35, '…')) ?></td>
                            <td><?= htmlspecialchars($p['agent_name']) ?></td>
                            <td>৳<?= number_format($p['price']) ?></td>
                            <td>
                                <a href="/admin/properties/<?= $p['id'] ?>/approve" class="btn btn--sm" style="background:#dcfce7;color:#15803d">Approve</a>
                                <a href="/admin/properties/<?= $p['id'] ?>/reject"  class="btn btn--sm btn--danger">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Users -->
            <div class="dashboard__section">
                <div class="section-header">
                    <h2 class="section-title">Recent Users</h2>
                    <a href="/admin/users" class="link-more">View all →</a>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Role</th><th>Verified</th><th>Joined</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><span class="badge"><?= ucfirst($u['role']) ?></span></td>
                            <td><?= $u['email_verified_at'] ? '✅' : '⏳' ?></td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Admin Quick Links -->
        <div class="dashboard__section">
            <div class="quick-links">
                <a href="/admin/users"      class="quick-link"><i class="fa-solid fa-users"></i> Users</a>
                <a href="/admin/properties" class="quick-link"><i class="fa-solid fa-building"></i> Properties</a>
                <a href="/admin/inquiries"  class="quick-link"><i class="fa-solid fa-message"></i> Inquiries</a>
                <a href="/admin/settings"   class="quick-link"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="/admin/logs"       class="quick-link"><i class="fa-solid fa-scroll"></i> Logs</a>
                <a href="/admin/materials"  class="quick-link"><i class="fa-solid fa-cubes"></i> Material Rates</a>
            </div>
        </div>

    </div>
</div>
