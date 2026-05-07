<div class="dashboard">
    <div class="container">
        <div class="dashboard__header">
            <div>
                <h1 class="dashboard__title">My Properties</h1>
                <p class="dashboard__sub"><?= number_format($pagination['total']) ?> total listings</p>
            </div>
            <a href="/agent/properties/create" class="btn btn--primary">
                <i class="fa-solid fa-plus"></i> Add Property
            </a>
        </div>

        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-building fa-3x"></i>
                <h3>No properties yet</h3>
                <p><a href="/agent/properties/create" class="btn btn--primary" style="margin-top:.75rem">Add your first listing</a></p>
            </div>
        <?php else: ?>
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
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($properties as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <?php if ($p['thumb']): ?>
                                    <img src="/public/uploads/images/<?= htmlspecialchars($p['thumb']) ?>"
                                         style="width:56px;height:42px;object-fit:cover;border-radius:6px;flex-shrink:0">
                                <?php else: ?>
                                    <div style="width:56px;height:42px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <i class="fa-solid fa-image" style="color:#aaa"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:600"><?= htmlspecialchars(mb_strimwidth($p['title'], 0, 45, '…')) ?></div>
                                    <div style="font-size:.8rem;color:var(--color-gray)"><?= htmlspecialchars($p['area'] . ', ' . $p['city']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge"><?= ucfirst($p['type']) ?></span></td>
                        <td style="font-weight:700">৳<?= number_format($p['price']) ?></td>
                        <td><span class="badge badge--<?= $p['status'] ?>">For <?= ucfirst($p['status']) ?></span></td>
                        <td><span class="badge badge--<?= $p['approval_status'] ?>"><?= ucfirst($p['approval_status']) ?></span></td>
                        <td><?= number_format($p['views']) ?></td>
                        <td style="font-size:.82rem;color:var(--color-gray)"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <div style="display:flex;gap:.4rem">
                                <a href="/property/<?= htmlspecialchars($p['slug']) ?>" class="btn btn--sm btn--ghost" target="_blank" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="/agent/properties/<?= $p['id'] ?>/edit" class="btn btn--sm btn--outline" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="/agent/properties/<?= $p['id'] ?>/delete"
                                      onsubmit="return confirm('Delete this property?')">
                                    <input type="hidden" name="_token" value="<?= $_SESSION['_csrf_token'] ?? '' ?>">
                                    <button type="submit" class="btn btn--sm btn--danger" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination" style="margin-top:1.5rem">
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                <a href="?page=<?= $i ?>"
                   class="pagination__btn <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
