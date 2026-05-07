<div class="dashboard">
    <div class="container">
        <div class="dashboard__header">
            <h1 class="dashboard__title">Manage Properties</h1>
        </div>

        <!-- Filter Bar -->
        <form class="filter-bar filter-bar--compact" method="GET" action="/admin/properties" style="margin-bottom:1.5rem">
            <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                <select name="status" class="form-control" style="width:auto">
                    <option value="">All Status</option>
                    <option value="pending"  <?= ($filters['approval_status']??'') === 'pending'  ? 'selected':'' ?>>Pending</option>
                    <option value="approved" <?= ($filters['approval_status']??'') === 'approved' ? 'selected':'' ?>>Approved</option>
                    <option value="rejected" <?= ($filters['approval_status']??'') === 'rejected' ? 'selected':'' ?>>Rejected</option>
                </select>
                <select name="type" class="form-control" style="width:auto">
                    <option value="">All Types</option>
                    <?php foreach (['apartment','house','commercial','land','villa','office'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($filters['type']??'') === $t ? 'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="q" class="form-control" placeholder="Search title or agent…"
                       value="<?= htmlspecialchars($filters['q'] ?? '') ?>" style="width:220px">
                <button type="submit" class="btn btn--primary">Filter</button>
                <a href="/admin/properties" class="btn btn--ghost">Reset</a>
            </div>
        </form>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Agent</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th>Views</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($properties)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--color-gray)">No properties found.</td></tr>
                <?php endif; ?>
                <?php foreach ($properties as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <?php if ($p['thumb']): ?>
                                    <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                                         style="width:56px;height:42px;object-fit:cover;border-radius:6px;flex-shrink:0">
                                <?php else: ?>
                                    <div style="width:56px;height:42px;background:#f0f0f0;border-radius:6px;flex-shrink:0"></div>
                                <?php endif; ?>
                                <div>
                                    <a href="/admin/properties/<?= $p['id'] ?>" style="font-weight:600">
                                        <?= htmlspecialchars(mb_strimwidth($p['title'], 0, 40, '…')) ?>
                                    </a>
                                    <div style="font-size:.8rem;color:var(--color-gray)"><?= htmlspecialchars($p['city']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['agent_name']) ?></td>
                        <td><span class="badge"><?= ucfirst($p['type']) ?></span></td>
                        <td>৳<?= number_format($p['price']) ?></td>
                        <td><span class="badge badge--<?= $p['status'] ?>">For <?= ucfirst($p['status']) ?></span></td>
                        <td><span class="badge badge--<?= $p['approval_status'] ?>"><?= ucfirst($p['approval_status']) ?></span></td>
                        <td><?= number_format($p['views']) ?></td>
                        <td>
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                <a href="/admin/properties/<?= $p['id'] ?>" class="btn btn--sm btn--ghost">Review</a>

                                <?php if ($p['approval_status'] !== 'approved'): ?>
                                <form method="POST" action="/admin/properties/<?= $p['id'] ?>/approve">
                                    <input type="hidden" name="_token" value="<?= $_SESSION['_csrf_token'] ?? '' ?>">
                                    <button class="btn btn--sm" style="background:#dcfce7;color:#15803d">✓ Approve</button>
                                </form>
                                <?php endif; ?>

                                <?php if ($p['approval_status'] !== 'rejected'): ?>
                                <form method="POST" action="/admin/properties/<?= $p['id'] ?>/reject">
                                    <input type="hidden" name="_token" value="<?= $_SESSION['_csrf_token'] ?? '' ?>">
                                    <button class="btn btn--sm btn--danger">✗ Reject</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination" style="margin-top:1.5rem">
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"
                   class="pagination__btn <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
