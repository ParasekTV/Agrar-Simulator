<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin/cooperatives" class="btn btn-outline">&larr; Zurück</a>
        <h1><?= htmlspecialchars($coop['name']) ?></h1>
    </div>

    <div class="admin-edit-grid">
        <!-- Genossenschaft-Formular -->
        <div class="card">
            <div class="card-header">
                <h3>Genossenschafts-Daten</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/admin/cooperatives/<?= $coop['id'] ?>/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= htmlspecialchars($coop['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Beschreibung</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($coop['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="treasury">Kasse (T)</label>
                            <input type="number" id="treasury" name="treasury" class="form-control"
                                   value="<?= $coop['treasury'] ?>" step="1">
                        </div>
                        <div class="form-group">
                            <label for="level">Level</label>
                            <input type="number" id="level" name="level" class="form-control"
                                   value="<?= $coop['level'] ?>" min="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="member_limit">Mitglieder-Limit</label>
                        <input type="number" id="member_limit" name="member_limit" class="form-control"
                               value="<?= $coop['member_limit'] ?>" min="1" max="50">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aktionen -->
        <div class="card">
            <div class="card-header">
                <h3>Aktionen</h3>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Gegründet:</span>
                        <span class="info-value"><?= date('d.m.Y H:i', strtotime($coop['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Mitglieder:</span>
                        <span class="info-value"><?= count($members) ?>/<?= $coop['member_limit'] ?></span>
                    </div>
                </div>

                <hr>

                <div class="danger-zone">
                    <h4>Gefahrenzone</h4>
                    <form action="<?= BASE_URL ?>/admin/cooperatives/<?= $coop['id'] ?>/delete" method="POST"
                          onsubmit="return confirm('Genossenschaft wirklich löschen? Alle Mitglieder werden entfernt!')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-danger btn-block">
                            Genossenschaft löschen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mitglieder -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Mitglieder (<?= count($members) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($members)): ?>
                <p class="text-muted">Keine Mitglieder vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Besitzer</th>
                            <th>Level</th>
                            <th>Punkte</th>
                            <th>Rolle</th>
                            <th>Beigetreten</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($member['farm_name']) ?></strong></td>
                                <td><?= htmlspecialchars($member['username']) ?></td>
                                <td><?= $member['level'] ?></td>
                                <td><?= number_format($member['points']) ?></td>
                                <td>
                                    <?php if ($member['role'] === 'founder'): ?>
                                        <span class="badge badge-founder">Gründer</span>
                                    <?php else: ?>
                                        Mitglied
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y', strtotime($member['joined_at'])) ?></td>
                                <td>
                                    <?php if ($member['role'] !== 'founder'): ?>
                                        <form action="<?= BASE_URL ?>/admin/cooperatives/remove-member" method="POST"
                                              style="display: inline;"
                                              onsubmit="return confirm('Mitglied wirklich entfernen?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="cooperative_id" value="<?= $coop['id'] ?>">
                                            <input type="hidden" name="farm_id" value="<?= $member['farm_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Entfernen</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
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
.admin-edit-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 968px) {
    .admin-edit-grid {
        grid-template-columns: 1fr;
    }
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-actions {
    margin-top: 1.5rem;
}
hr {
    margin: 1.5rem 0;
    border: none;
    border-top: 1px solid var(--color-gray-200);
}
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.info-item {
    display: flex;
    justify-content: space-between;
}
.info-label {
    color: var(--color-gray-600);
}
.info-value {
    font-weight: 500;
}
.danger-zone {
    background: #fff5f5;
    border: 1px solid var(--color-danger);
    border-radius: var(--radius);
    padding: 1rem;
}
.danger-zone h4 {
    color: var(--color-danger);
    margin-bottom: 1rem;
}
.mt-4 {
    margin-top: 1.5rem;
}
.badge-founder {
    background: var(--color-warning);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}
</style>
