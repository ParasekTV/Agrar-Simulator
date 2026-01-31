<div class="admin-page">
    <div class="page-header">
        <h1>Admin-Bereich</h1>
    </div>

    <!-- Statistiken -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="stat-icon">&#128100;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['users']) ?></span>
                <span class="stat-label">Benutzer gesamt</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-icon">&#127793;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['active_users']) ?></span>
                <span class="stat-label">Aktiv (7 Tage)</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-icon">&#127968;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['farms']) ?></span>
                <span class="stat-label">Hoefe</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-icon">&#129309;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['cooperatives']) ?></span>
                <span class="stat-label">Genossenschaften</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-icon">&#128176;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['total_money'], 0, ',', '.') ?> T</span>
                <span class="stat-label">Geld im Umlauf</span>
            </div>
        </div>
        <div class="admin-stat-card">
            <div class="stat-icon">&#11088;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($stats['total_points']) ?></span>
                <span class="stat-label">Punkte gesamt</span>
            </div>
        </div>
    </div>

    <!-- Schnellzugriff -->
    <div class="admin-quick-actions">
        <h3>Verwaltung</h3>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-primary btn-lg">
                &#128100; Benutzer verwalten
            </a>
            <a href="<?= BASE_URL ?>/admin/farms" class="btn btn-primary btn-lg">
                &#127968; Hoefe verwalten
            </a>
            <a href="<?= BASE_URL ?>/admin/cooperatives" class="btn btn-primary btn-lg">
                &#129309; Genossenschaften verwalten
            </a>
        </div>
    </div>

    <!-- Neueste Benutzer -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Neueste Benutzer</h3>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-outline">Alle anzeigen</a>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Benutzername</th>
                        <th>Farm</th>
                        <th>Level</th>
                        <th>Geld</th>
                        <th>Registriert</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['is_admin']): ?>
                                    <span class="badge badge-admin">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['farm_name'] ?? '-') ?></td>
                            <td><?= $user['level'] ?? '-' ?></td>
                            <td><?= $user['money'] ? number_format($user['money'], 0, ',', '.') . ' T' : '-' ?></td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="btn btn-sm btn-outline">Bearbeiten</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.admin-stat-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
}
.admin-stat-card .stat-icon {
    font-size: 2.5rem;
}
.admin-stat-card .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-primary);
}
.admin-stat-card .stat-label {
    font-size: 0.9rem;
    color: var(--color-gray-600);
}
.admin-quick-actions {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.admin-quick-actions h3 {
    margin-bottom: 1rem;
}
.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}
.badge-admin {
    background: var(--color-danger);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}
.mt-4 {
    margin-top: 1.5rem;
}
</style>
