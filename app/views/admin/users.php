<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin" class="btn btn-outline">&larr; Zurueck</a>
        <h1>Benutzer verwalten</h1>
    </div>

    <!-- Suche -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="<?= BASE_URL ?>/admin/users" method="GET" class="search-form">
                <div class="form-row">
                    <input type="text" name="search" class="form-control"
                           placeholder="Suche nach Benutzername, E-Mail oder Farmname..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Suchen</button>
                    <?php if ($search): ?>
                        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline">Zuruecksetzen</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= number_format($total) ?> Benutzer</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Benutzername</th>
                        <th>E-Mail</th>
                        <th>Farm</th>
                        <th>Level</th>
                        <th>Geld</th>
                        <th>Punkte</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['is_admin']): ?>
                                    <span class="badge badge-admin">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['farm_name'] ?? '-') ?></td>
                            <td><?= $user['level'] ?? '-' ?></td>
                            <td><?= $user['money'] ? number_format($user['money'], 0, ',', '.') . ' T' : '-' ?></td>
                            <td><?= $user['points'] ? number_format($user['points']) : '-' ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="status-badge status-active">Aktiv</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">Inaktiv</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="btn btn-sm btn-outline">Bearbeiten</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination mt-4">
            <?php if ($page > 1): ?>
                <a href="<?= BASE_URL ?>/admin/users?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-link">&laquo;</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="<?= BASE_URL ?>/admin/users?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                   class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= BASE_URL ?>/admin/users?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.search-form .form-row {
    display: flex;
    gap: 1rem;
}
.search-form .form-control {
    flex: 1;
}
.mb-4 {
    margin-bottom: 1.5rem;
}
.mt-4 {
    margin-top: 1.5rem;
}
.badge-admin {
    background: var(--color-danger);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
}
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}
.status-active {
    background: var(--color-success);
    color: white;
}
.status-inactive {
    background: var(--color-gray-400);
    color: white;
}
</style>
