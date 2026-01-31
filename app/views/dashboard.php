<div class="dashboard">
    <div class="page-header">
        <h1>Willkommen auf <?= htmlspecialchars($farm['farm_name']) ?>!</h1>
        <p class="text-muted">Uebersicht deiner Farm</p>
    </div>

    <!-- Statistiken -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#127806;</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['fields_total'] ?></span>
                <span class="stat-label">Felder</span>
            </div>
            <div class="stat-detail">
                <?= $stats['fields_growing'] ?> wachsend, <?= $stats['fields_ready'] ?> bereit
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128046;</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['animals'] ?></span>
                <span class="stat-label">Tiere</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128666;</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['vehicles'] ?></span>
                <span class="stat-label">Fahrzeuge</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127942;</div>
            <div class="stat-info">
                <span class="stat-value">#<?= $stats['rank'] ?: '-' ?></span>
                <span class="stat-label">Rang</span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Felder-Uebersicht -->
        <div class="card">
            <div class="card-header">
                <h3>Meine Felder</h3>
                <a href="<?= BASE_URL ?>/fields" class="btn btn-sm btn-outline">Alle anzeigen</a>
            </div>
            <div class="card-body">
                <?php if (empty($fields)): ?>
                    <p class="text-muted">Keine Felder vorhanden.</p>
                <?php else: ?>
                    <div class="fields-mini-grid">
                        <?php foreach (array_slice($fields, 0, 6) as $field): ?>
                            <div class="field-mini field-<?= $field['status'] ?>">
                                <div class="field-mini-status">
                                    <?php if ($field['status'] === 'empty'): ?>
                                        <span class="status-empty">Leer</span>
                                    <?php elseif ($field['status'] === 'growing'): ?>
                                        <span class="status-growing"><?= htmlspecialchars($field['crop_name'] ?? '') ?></span>
                                        <span class="field-timer" data-harvest-time="<?= $field['harvest_ready_at'] ?>">
                                            Wachsend...
                                        </span>
                                    <?php elseif ($field['status'] === 'ready'): ?>
                                        <span class="status-ready">Bereit!</span>
                                    <?php endif; ?>
                                </div>
                                <div class="field-mini-size"><?= $field['size_hectares'] ?> ha</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aktive Forschung -->
        <div class="card">
            <div class="card-header">
                <h3>Forschung</h3>
                <a href="<?= BASE_URL ?>/research" class="btn btn-sm btn-outline">Zum Labor</a>
            </div>
            <div class="card-body">
                <?php if ($activeResearch): ?>
                    <div class="research-active">
                        <h4><?= htmlspecialchars($activeResearch['name']) ?></h4>
                        <p><?= htmlspecialchars($activeResearch['description']) ?></p>
                        <div class="progress-bar">
                            <div class="progress-bar-fill research-timer"
                                 data-complete-time="<?= date('Y-m-d H:i:s', strtotime($activeResearch['started_at']) + ($activeResearch['research_time_hours'] * 3600)) ?>">
                            </div>
                        </div>
                        <span class="research-timer"
                              data-complete-time="<?= date('Y-m-d H:i:s', strtotime($activeResearch['started_at']) + ($activeResearch['research_time_hours'] * 3600)) ?>">
                            Berechne...
                        </span>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Keine aktive Forschung.</p>
                    <a href="<?= BASE_URL ?>/research" class="btn btn-primary">Forschung starten</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Herausforderungen -->
        <div class="card">
            <div class="card-header">
                <h3>Woechentliche Herausforderungen</h3>
                <a href="<?= BASE_URL ?>/rankings/challenges" class="btn btn-sm btn-outline">Alle</a>
            </div>
            <div class="card-body">
                <?php if (empty($challenges)): ?>
                    <p class="text-muted">Keine aktiven Herausforderungen.</p>
                <?php else: ?>
                    <?php foreach (array_slice($challenges, 0, 3) as $challenge): ?>
                        <div class="challenge-item <?= $challenge['completed'] ? 'completed' : '' ?>">
                            <div class="challenge-info">
                                <strong><?= htmlspecialchars($challenge['challenge_name']) ?></strong>
                                <span class="challenge-reward">+<?= $challenge['reward_points'] ?> Punkte</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?= $challenge['percentage'] ?>%"></div>
                            </div>
                            <small><?= $challenge['current_value'] ?> / <?= $challenge['target_value'] ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Letzte Ereignisse -->
        <div class="card">
            <div class="card-header">
                <h3>Letzte Ereignisse</h3>
                <a href="<?= BASE_URL ?>/events" class="btn btn-sm btn-outline">Alle</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentEvents)): ?>
                    <p class="text-muted">Keine Ereignisse.</p>
                <?php else: ?>
                    <ul class="event-list">
                        <?php foreach ($recentEvents as $event): ?>
                            <li class="event-item event-<?= $event['event_type'] ?>">
                                <span class="event-icon">
                                    <?php
                                    $icons = [
                                        'harvest' => '&#127806;',
                                        'sale' => '&#128176;',
                                        'purchase' => '&#128722;',
                                        'research' => '&#128300;',
                                        'building' => '&#127968;',
                                        'level_up' => '&#11088;',
                                        'points' => '&#127942;'
                                    ];
                                    echo $icons[$event['event_type']] ?? '&#128196;';
                                    ?>
                                </span>
                                <div class="event-content">
                                    <span class="event-description"><?= htmlspecialchars($event['description']) ?></span>
                                    <span class="event-time"><?= date('d.m. H:i', strtotime($event['created_at'])) ?></span>
                                </div>
                                <?php if ($event['money_change'] != 0): ?>
                                    <span class="event-money <?= $event['money_change'] > 0 ? 'positive' : 'negative' ?>">
                                        <?= $event['money_change'] > 0 ? '+' : '' ?><?= number_format($event['money_change'], 0, ',', '.') ?> EUR
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Level-Fortschritt -->
    <div class="card level-progress-card">
        <div class="card-body">
            <div class="level-info">
                <span class="current-level">Level <?= $farm['level'] ?></span>
                <span class="points-progress"><?= $farm['points'] ?> / <?= $farm['level'] * 100 ?> Punkte</span>
                <span class="next-level">Level <?= $farm['level'] + 1 ?></span>
            </div>
            <div class="progress-bar progress-bar-lg">
                <div class="progress-bar-fill" style="width: <?= min(100, ($farm['points'] / ($farm['level'] * 100)) * 100) ?>%"></div>
            </div>
        </div>
    </div>
</div>
