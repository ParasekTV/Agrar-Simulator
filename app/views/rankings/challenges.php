<div class="challenges-page">
    <div class="page-header">
        <h1>Wöchentliche Herausforderungen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/rankings" class="btn btn-outline">Rangliste</a>
            <a href="<?= BASE_URL ?>/rankings/cooperatives" class="btn btn-outline">Genossenschaften</a>
        </div>
    </div>

    <?php if (empty($challenges)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#127942;</span>
            <h3>Keine aktiven Herausforderungen</h3>
            <p>Aktuell gibt es keine aktiven Herausforderungen. Schau später wieder vorbei!</p>
        </div>
    <?php else: ?>
        <div class="challenges-container">
            <?php foreach ($challenges as $challenge): ?>
                <div class="challenge-detail-card <?= $challenge['completed'] ? 'completed' : '' ?>">
                    <div class="challenge-header">
                        <div class="challenge-info">
                            <h3><?= htmlspecialchars($challenge['challenge_name']) ?></h3>
                            <span class="challenge-type type-<?= $challenge['challenge_type'] ?>">
                                <?php
                                $types = [
                                    'sales' => 'Verkauf',
                                    'production' => 'Produktion',
                                    'research' => 'Forschung',
                                    'cooperative' => 'Genossenschaft'
                                ];
                                echo $types[$challenge['challenge_type']] ?? $challenge['challenge_type'];
                                ?>
                            </span>
                        </div>
                        <div class="challenge-reward">
                            <span class="reward-value">+<?= $challenge['reward_points'] ?></span>
                            <span class="reward-label">Punkte</span>
                        </div>
                    </div>

                    <p class="challenge-description"><?= htmlspecialchars($challenge['description'] ?? '') ?></p>

                    <div class="challenge-progress-section">
                        <div class="progress-header">
                            <span>Dein Fortschritt</span>
                            <span class="progress-percentage"><?= round($challenge['percentage']) ?>%</span>
                        </div>
                        <div class="progress-bar progress-bar-lg">
                            <div class="progress-bar-fill" style="width: <?= min(100, $challenge['percentage']) ?>%"></div>
                        </div>
                        <div class="progress-values">
                            <span><?= number_format($challenge['current_value']) ?></span>
                            <span><?= number_format($challenge['target_value']) ?></span>
                        </div>
                    </div>

                    <?php if ($challenge['completed']): ?>
                        <div class="challenge-completed-badge">
                            <span>&#127942;</span> Abgeschlossen!
                        </div>
                    <?php endif; ?>

                    <!-- Bestenliste -->
                    <?php if (!empty($challenge['leaderboard'])): ?>
                        <div class="challenge-leaderboard">
                            <h4>Top 10</h4>
                            <ol class="leaderboard-list">
                                <?php foreach (array_slice($challenge['leaderboard'], 0, 10) as $entry): ?>
                                    <li class="<?= $entry['farm_id'] === Session::getFarmId() ? 'highlight' : '' ?>">
                                        <span class="farm-name"><?= htmlspecialchars($entry['farm_name']) ?></span>
                                        <span class="farm-value"><?= number_format($entry['current_value']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    <?php endif; ?>

                    <div class="challenge-dates">
                        <span>Endet: <?= date('d.m.Y', strtotime($challenge['end_date'])) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.challenges-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}
.challenge-detail-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    position: relative;
}
.challenge-detail-card.completed {
    border: 2px solid var(--color-success);
    background: linear-gradient(135deg, #f8fff8 0%, white 100%);
}
.challenge-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}
.challenge-info h3 {
    margin: 0 0 0.5rem;
}
.challenge-type {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    background: var(--color-gray-200);
}
.type-sales { background: #e3f2fd; color: #1565c0; }
.type-production { background: #e8f5e9; color: #2e7d32; }
.type-research { background: #fff3e0; color: #ef6c00; }
.type-cooperative { background: #f3e5f5; color: #7b1fa2; }
.challenge-reward {
    text-align: center;
    background: var(--color-primary-light);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
}
.reward-value {
    font-size: 1.5rem;
    font-weight: 700;
    display: block;
}
.reward-label {
    font-size: 0.8rem;
}
.challenge-description {
    color: var(--color-gray-600);
    margin-bottom: 1.5rem;
}
.challenge-progress-section {
    margin-bottom: 1rem;
}
.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}
.progress-percentage {
    font-weight: 600;
    color: var(--color-primary);
}
.progress-values {
    display: flex;
    justify-content: space-between;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--color-gray-600);
}
.challenge-completed-badge {
    background: var(--color-success);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    text-align: center;
    font-weight: 600;
    margin-bottom: 1rem;
}
.challenge-leaderboard {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-gray-200);
}
.challenge-leaderboard h4 {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}
.leaderboard-list {
    margin: 0;
    padding-left: 1.5rem;
}
.leaderboard-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    font-size: 0.9rem;
}
.leaderboard-list li.highlight {
    font-weight: 600;
    color: var(--color-primary);
}
.farm-value {
    color: var(--color-gray-600);
}
.challenge-dates {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: var(--color-gray-500);
    text-align: right;
}
@media (max-width: 768px) {
    .challenges-container {
        grid-template-columns: 1fr;
    }
}
</style>
