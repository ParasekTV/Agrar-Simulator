<div class="coop-rankings-page">
    <div class="page-header">
        <h1>Genossenschafts-Rangliste</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/rankings" class="btn btn-outline">Spieler-Rangliste</a>
            <a href="<?= BASE_URL ?>/rankings/challenges" class="btn btn-outline">Herausforderungen</a>
        </div>
    </div>

    <?php if (empty($rankings)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#127968;</span>
            <h3>Keine Genossenschaften</h3>
            <p>Es gibt noch keine Genossenschaften. Gruende die Erste!</p>
            <a href="<?= BASE_URL ?>/cooperative" class="btn btn-primary">Zur Genossenschaft</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-rankings">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Name</th>
                            <th>Mitglieder</th>
                            <th>Punkte</th>
                            <th>Kasse</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankings as $index => $coop): ?>
                            <tr>
                                <td>
                                    <span class="rank-number rank-<?= $index < 3 ? $index + 1 : 'other' ?>">
                                        <?php if ($index === 0): ?>
                                            &#127942;
                                        <?php elseif ($index === 1): ?>
                                            &#129352;
                                        <?php elseif ($index === 2): ?>
                                            &#129353;
                                        <?php else: ?>
                                            #<?= $index + 1 ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($coop['name']) ?></strong>
                                    <?php if ($coop['description']): ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars(mb_substr($coop['description'], 0, 50)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="member-badge"><?= $coop['member_count'] ?>/<?= $coop['member_limit'] ?></span>
                                </td>
                                <td>
                                    <strong><?= number_format($coop['total_points']) ?></strong>
                                </td>
                                <td><?= number_format($coop['treasury'], 0, ',', '.') ?> T</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.member-badge {
    background: var(--color-gray-200);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
}
</style>
