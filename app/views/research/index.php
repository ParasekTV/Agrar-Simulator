<div class="research-page">
    <div class="page-header">
        <h1>Forschungslabor</h1>
        <p class="text-muted">Erforsche neue Technologien und erweitere deine Möglichkeiten</p>
    </div>

    <?php if ($activeResearch): ?>
        <div class="card card-highlight">
            <div class="card-header">
                <h3>Aktive Forschung</h3>
            </div>
            <div class="card-body">
                <div class="active-research">
                    <div class="research-info">
                        <h4><?= htmlspecialchars($activeResearch['name']) ?></h4>
                        <p><?= htmlspecialchars($activeResearch['description']) ?></p>
                        <span class="research-category category-<?= $activeResearch['category'] ?>">
                            <?= ucfirst($activeResearch['category']) ?>
                        </span>
                    </div>
                    <div class="research-progress">
                        <?php if ($remainingTime): ?>
                            <div class="timer-display">
                                <?php if ($remainingTime['completed']): ?>
                                    <span class="text-success">Abgeschlossen! Seite neu laden.</span>
                                <?php else: ?>
                                    <span class="research-timer"
                                          data-complete-time="<?= $remainingTime['completes_at'] ?>">
                                        <?= $remainingTime['hours'] ?>h <?= $remainingTime['minutes'] ?>m verbleibend
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <form action="<?= BASE_URL ?>/research/cancel" method="POST" class="mt-2">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-outline btn-sm"
                                    onclick="return confirm('Wirklich abbrechen? Du erhältst nur 50% der Kosten zurück.')">
                                Abbrechen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="research-tree">
        <?php foreach ($researchTree as $category => $researches): ?>
            <div class="research-category-section">
                <h3 class="category-title category-<?= $category ?>">
                    <?php
                    $categoryNames = [
                        'crops' => 'Pflanzenanbau',
                        'animals' => 'Viehzucht',
                        'vehicles' => 'Fahrzeuge',
                        'buildings' => 'Gebäude',
                        'efficiency' => 'Effizienz'
                    ];
                    echo $categoryNames[$category] ?? ucfirst($category);
                    ?>
                </h3>
                <div class="research-items">
                    <?php foreach ($researches as $research): ?>
                        <div class="research-item research-<?= $research['status'] ?>">
                            <div class="research-header">
                                <h4><?= htmlspecialchars($research['name']) ?></h4>
                                <?php if ($research['status'] === 'completed'): ?>
                                    <span class="badge badge-success">Erforscht</span>
                                <?php elseif ($research['status'] === 'in_progress'): ?>
                                    <span class="badge badge-warning">In Arbeit</span>
                                <?php elseif ($research['unlockable']): ?>
                                    <span class="badge badge-info">Verfügbar</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Gesperrt</span>
                                <?php endif; ?>
                            </div>
                            <p class="research-description"><?= htmlspecialchars($research['description']) ?></p>
                            <div class="research-meta">
                                <span class="research-cost">
                                    <?php if ($research['cost'] > 0): ?>
                                        <?= number_format($research['cost'], 0, ',', '.') ?> T
                                    <?php else: ?>
                                        Kostenlos
                                    <?php endif; ?>
                                </span>
                                <span class="research-time"><?= $research['research_time_hours'] ?>h</span>
                                <span class="research-reward">+<?= $research['points_reward'] ?> Punkte</span>
                            </div>
                            <?php if ($research['level_required'] > 1): ?>
                                <div class="research-requirement">
                                    Benötigt Level <?= $research['level_required'] ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($research['unlockable'] && !$activeResearch): ?>
                                <form action="<?= BASE_URL ?>/research/start" method="POST" class="research-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="research_id" value="<?= $research['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Forschung starten</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($completedResearch)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Abgeschlossene Forschungen</h3>
            </div>
            <div class="card-body">
                <div class="completed-research-list">
                    <?php foreach ($completedResearch as $research): ?>
                        <div class="completed-research-item">
                            <span class="research-name"><?= htmlspecialchars($research['name']) ?></span>
                            <span class="research-date"><?= date('d.m.Y', strtotime($research['completed_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
