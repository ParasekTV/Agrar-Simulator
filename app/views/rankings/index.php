<div class="rankings-page">
    <div class="page-header">
        <h1>Rangliste</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/rankings/cooperatives" class="btn btn-outline">Genossenschaften</a>
            <a href="<?= BASE_URL ?>/rankings/challenges" class="btn btn-outline">Herausforderungen</a>
        </div>
    </div>

    <div class="rankings-layout">
        <!-- Mein Rang -->
        <div class="my-rank-card">
            <h3>Dein Rang</h3>
            <div class="rank-display">
                <span class="rank-position">#<?= $myRank['position'] ?? '-' ?></span>
                <div class="rank-stats">
                    <div class="rank-stat">
                        <span class="stat-value"><?= number_format($myRank['total_points'] ?? 0) ?></span>
                        <span class="stat-label">Punkte</span>
                    </div>
                    <div class="rank-stat">
                        <span class="stat-value"><?= number_format($myRank['total_money'] ?? 0, 0, ',', '.') ?></span>
                        <span class="stat-label">Vermögen</span>
                    </div>
                    <div class="rank-stat">
                        <span class="stat-value"><?= number_format($myRank['total_sales_value'] ?? 0, 0, ',', '.') ?></span>
                        <span class="stat-label">Umsatz</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Globale Statistiken -->
        <div class="global-stats">
            <div class="stat-mini">
                <span class="stat-value"><?= number_format($stats['total_players']) ?></span>
                <span class="stat-label">Spieler</span>
            </div>
            <div class="stat-mini">
                <span class="stat-value"><?= number_format($stats['total_cooperatives']) ?></span>
                <span class="stat-label">Genossenschaften</span>
            </div>
            <div class="stat-mini">
                <span class="stat-value"><?= number_format($stats['active_today']) ?></span>
                <span class="stat-label">Heute aktiv</span>
            </div>
        </div>
    </div>

    <!-- Wöchentliche Herausforderungen -->
    <?php if (!empty($challenges)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Wöchentliche Herausforderungen</h3>
            </div>
            <div class="card-body">
                <div class="challenges-grid">
                    <?php foreach ($challenges as $challenge): ?>
                        <div class="challenge-card <?= $challenge['completed'] ? 'completed' : '' ?>">
                            <div class="challenge-header">
                                <h4><?= htmlspecialchars($challenge['challenge_name']) ?></h4>
                                <span class="challenge-reward">+<?= $challenge['reward_points'] ?> Punkte</span>
                            </div>
                            <p class="challenge-description"><?= htmlspecialchars($challenge['description'] ?? '') ?></p>
                            <div class="challenge-progress">
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?= $challenge['percentage'] ?>%"></div>
                                </div>
                                <span class="progress-text">
                                    <?= number_format($challenge['current_value']) ?> / <?= number_format($challenge['target_value']) ?>
                                </span>
                            </div>
                            <?php if ($challenge['completed']): ?>
                                <span class="badge badge-success">Abgeschlossen!</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rangliste -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Top-Farmer</h3>
        </div>
        <div class="card-body">
            <table class="table table-rankings">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Farm</th>
                        <th>Spieler</th>
                        <th>Level</th>
                        <th>Punkte</th>
                        <th>Vermögen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $rank): ?>
                        <tr class="<?= $rank['farm_id'] === Session::getFarmId() ? 'highlight' : '' ?>">
                            <td>
                                <span class="rank-number rank-<?= $rank['position'] <= 3 ? $rank['position'] : 'other' ?>">
                                    <?php if ($rank['position'] === 1): ?>
                                        &#127942;
                                    <?php elseif ($rank['position'] === 2): ?>
                                        &#129352;
                                    <?php elseif ($rank['position'] === 3): ?>
                                        &#129353;
                                    <?php else: ?>
                                        #<?= $rank['position'] ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($rank['farm_name']) ?></strong></td>
                            <td><?= htmlspecialchars($rank['username']) ?></td>
                            <td>
                                <span class="level-badge">Lvl <?= $rank['level'] ?></span>
                            </td>
                            <td><?= number_format($rank['total_points']) ?></td>
                            <td><?= number_format($rank['total_money'], 0, ',', '.') ?> T</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= min(10, $pagination['totalPages']); $i++): ?>
                        <a href="<?= BASE_URL ?>/rankings?page=<?= $i ?>"
                           class="pagination-link <?= $i === $pagination['page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($pagination['totalPages'] > 10): ?>
                        <span class="pagination-ellipsis">...</span>
                        <a href="<?= BASE_URL ?>/rankings?page=<?= $pagination['totalPages'] ?>"
                           class="pagination-link">
                            <?= $pagination['totalPages'] ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
