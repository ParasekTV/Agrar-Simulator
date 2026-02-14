<div class="cooperative-page">
    <div class="page-header">
        <h1>Herausforderungen</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/board" class="coop-nav-item">Pinnwand</a>
        <a href="<?= BASE_URL ?>/cooperative/vehicles" class="coop-nav-item">Fahrzeugverleih</a>
        <a href="<?= BASE_URL ?>/cooperative/productions" class="coop-nav-item">Produktionen</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item active">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <?php
    $weeklyChallenges = array_filter($challenges, fn($c) => $c['challenge_period'] === 'weekly');
    $monthlyChallenges = array_filter($challenges, fn($c) => $c['challenge_period'] === 'monthly');
    ?>

    <!-- Wöchentliche Herausforderungen -->
    <h2 class="section-title">Wöchentliche Herausforderungen</h2>
    <?php if (empty($weeklyChallenges)): ?>
        <p class="text-muted">Keine aktiven wöchentlichen Herausforderungen.</p>
    <?php else: ?>
        <div class="challenges-grid">
            <?php foreach ($weeklyChallenges as $challenge): ?>
                <?php
                $targetValue = $challenge['target_value'] ?: 1;
                $progress = ($challenge['current_value'] / $targetValue) * 100;
                $remaining = strtotime($challenge['end_date']) - time();
                $daysLeft = max(0, floor($remaining / 86400));
                $hoursLeft = max(0, floor(($remaining % 86400) / 3600));
                ?>
                <div class="challenge-card">
                    <div class="challenge-header">
                        <h4><?= htmlspecialchars($challenge['name']) ?></h4>
                        <span class="challenge-timer"><?= $daysLeft ?>T <?= $hoursLeft ?>h</span>
                    </div>
                    <p class="challenge-desc"><?= htmlspecialchars($challenge['description']) ?></p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= min(100, $progress) ?>%"></div>
                    </div>
                    <div class="challenge-progress-text">
                        <?= number_format($challenge['current_value']) ?> / <?= number_format($targetValue) ?>
                        <span>(<?= number_format($progress, 1) ?>%)</span>
                    </div>
                    <div class="challenge-rewards">
                        <?php if ($challenge['reward_money'] > 0): ?>
                            <span class="reward-item"><?= number_format($challenge['reward_money'], 0, ',', '.') ?> T</span>
                        <?php endif; ?>
                        <?php if ($challenge['reward_points'] > 0): ?>
                            <span class="reward-item"><?= number_format($challenge['reward_points']) ?> Punkte</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($challenge['contributions'])): ?>
                        <div class="challenge-contributions">
                            <h5>Beiträge</h5>
                            <?php foreach (array_slice($challenge['contributions'], 0, 5) as $contrib): ?>
                                <div class="contribution-row">
                                    <span><?= htmlspecialchars($contrib['farm_name']) ?></span>
                                    <span><?= number_format($contrib['contribution_value']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Monatliche Herausforderungen -->
    <h2 class="section-title">Monatliche Herausforderungen</h2>
    <?php if (empty($monthlyChallenges)): ?>
        <p class="text-muted">Keine aktiven monatlichen Herausforderungen.</p>
    <?php else: ?>
        <div class="challenges-grid">
            <?php foreach ($monthlyChallenges as $challenge): ?>
                <?php
                $targetValue = $challenge['target_value'] ?: 1;
                $progress = ($challenge['current_value'] / $targetValue) * 100;
                $remaining = strtotime($challenge['end_date']) - time();
                $daysLeft = max(0, floor($remaining / 86400));
                ?>
                <div class="challenge-card challenge-monthly">
                    <div class="challenge-header">
                        <h4><?= htmlspecialchars($challenge['name']) ?></h4>
                        <span class="challenge-timer"><?= $daysLeft ?> Tage</span>
                    </div>
                    <p class="challenge-desc"><?= htmlspecialchars($challenge['description']) ?></p>
                    <div class="progress-bar">
                        <div class="progress-fill monthly" style="width: <?= min(100, $progress) ?>%"></div>
                    </div>
                    <div class="challenge-progress-text">
                        <?= number_format($challenge['current_value']) ?> / <?= number_format($targetValue) ?>
                        <span>(<?= number_format($progress, 1) ?>%)</span>
                    </div>
                    <div class="challenge-rewards">
                        <?php if ($challenge['reward_money'] > 0): ?>
                            <span class="reward-item"><?= number_format($challenge['reward_money'], 0, ',', '.') ?> T</span>
                        <?php endif; ?>
                        <?php if ($challenge['reward_points'] > 0): ?>
                            <span class="reward-item"><?= number_format($challenge['reward_points']) ?> Punkte</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($challenge['contributions'])): ?>
                        <div class="challenge-contributions">
                            <h5>Beiträge</h5>
                            <?php foreach (array_slice($challenge['contributions'], 0, 5) as $contrib): ?>
                                <div class="contribution-row">
                                    <span><?= htmlspecialchars($contrib['farm_name']) ?></span>
                                    <span><?= number_format($contrib['contribution_value']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.section-title { margin: 1.5rem 0 1rem; font-size: 1.25rem; }
.challenges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.challenge-card { background: white; border-radius: var(--radius-lg); padding: 1.25rem; box-shadow: var(--shadow-sm); border-left: 4px solid var(--color-primary); }
.challenge-monthly { border-left-color: #8b5cf6; }
.challenge-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
.challenge-header h4 { margin: 0; font-size: 1rem; }
.challenge-timer { font-size: 0.8rem; color: var(--color-warning); font-weight: 600; white-space: nowrap; }
.challenge-desc { font-size: 0.85rem; color: var(--color-gray-600); margin: 0.5rem 0; }
.progress-bar { height: 8px; background: var(--color-gray-200); border-radius: 4px; overflow: hidden; margin: 0.75rem 0 0.25rem; }
.progress-fill { height: 100%; background: var(--color-primary); border-radius: 4px; transition: width 0.3s; }
.progress-fill.monthly { background: #8b5cf6; }
.challenge-progress-text { font-size: 0.8rem; color: var(--color-gray-500); margin-bottom: 0.5rem; }
.challenge-rewards { display: flex; gap: 0.5rem; }
.reward-item { font-size: 0.85rem; font-weight: 600; color: var(--color-primary); background: var(--color-gray-100); padding: 0.15rem 0.5rem; border-radius: var(--radius); }
.challenge-contributions { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--color-gray-200); }
.challenge-contributions h5 { margin: 0 0 0.5rem; font-size: 0.85rem; color: var(--color-gray-500); }
.contribution-row { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 0.15rem 0; }
</style>
