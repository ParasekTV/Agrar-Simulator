<div class="admin-page">
    <div class="page-header">
        <h1>News & Changelog</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/admin/news/create" class="btn btn-primary">Neuen Beitrag erstellen</a>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h3>Alle Admin-Beiträge (<?= $total ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($posts)): ?>
                <p class="text-muted">Noch keine News oder Changelogs vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kategorie</th>
                            <th>Titel</th>
                            <th>Erstellt</th>
                            <th>Views</th>
                            <th>Likes</th>
                            <th>Kommentare</th>
                            <th>Angepinnt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>#<?= $post['id'] ?></td>
                                <td>
                                    <?php if ($post['category'] === 'changelog'): ?>
                                        <span class="badge badge-info">Changelog</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">News</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/news/<?= $post['id'] ?>" target="_blank">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                                <td><?= number_format($post['views']) ?></td>
                                <td><?= number_format($post['likes']) ?></td>
                                <td><?= $post['comment_count'] ?></td>
                                <td>
                                    <?php if ($post['is_pinned']): ?>
                                        <span class="text-success">Ja</span>
                                    <?php else: ?>
                                        <span class="text-muted">Nein</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="<?= BASE_URL ?>/admin/news/<?= $post['id'] ?>" class="btn btn-sm btn-outline">
                                        Bearbeiten
                                    </a>
                                    <form action="<?= BASE_URL ?>/admin/news/<?= $post['id'] ?>/delete" method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Beitrag wirklich löschen?');">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.admin-table th {
    background: var(--color-bg-secondary);
    font-weight: 600;
}

.admin-table .actions {
    display: flex;
    gap: 0.5rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-primary {
    background: var(--color-primary);
    color: white;
}

.btn-danger {
    background: var(--color-danger);
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #c82333;
}
</style>
