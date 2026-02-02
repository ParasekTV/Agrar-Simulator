<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin" class="btn btn-outline">&larr; Zurück</a>
        <h1>Genossenschaften verwalten</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= count($cooperatives) ?> Genossenschaften</h3>
        </div>
        <div class="card-body">
            <?php if (empty($cooperatives)): ?>
                <p class="text-muted">Keine Genossenschaften vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Gründer</th>
                            <th>Mitglieder</th>
                            <th>Level</th>
                            <th>Kasse</th>
                            <th>Gegründet</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cooperatives as $coop): ?>
                            <tr>
                                <td><?= $coop['id'] ?></td>
                                <td><strong><?= htmlspecialchars($coop['name']) ?></strong></td>
                                <td><?= htmlspecialchars($coop['founder_name'] ?? 'Unbekannt') ?></td>
                                <td><?= $coop['member_count'] ?>/<?= $coop['member_limit'] ?></td>
                                <td><?= $coop['level'] ?></td>
                                <td><?= number_format($coop['treasury'], 0, ',', '.') ?> T</td>
                                <td><?= date('d.m.Y', strtotime($coop['created_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/cooperatives/<?= $coop['id'] ?>" class="btn btn-sm btn-outline">Bearbeiten</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
