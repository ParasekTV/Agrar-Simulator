<div class="cooperative-page">
    <div class="page-header">
        <h1>Genossenschafts-Forschung</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item active">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <p class="text-muted">Kasse: <?= number_format($coopDetails['treasury'] ?? 0, 0, ',', '.') ?> T</p>

    <div class="research-grid">
        <?php foreach ($researchTree as $research): ?>
            <?php
            $status = $research['status'] ?? 'locked';
            $isCompleted = $status === 'completed';
            $isInProgress = $status === 'in_progress';
            $isAvailable = !$isCompleted && !$isInProgress;

            // Prüfe ob Voraussetzung erfüllt
            $prereqMet = true;
            if ($research['prerequisite_id'] && $isAvailable) {
                foreach ($researchTree as $r) {
                    if ($r['id'] == $research['prerequisite_id'] && ($r['status'] ?? null) !== 'completed') {
                        $prereqMet = false;
                        break;
                    }
                }
            }

            $cardClass = $isCompleted ? 'research-completed' : ($isInProgress ? 'research-active' : ($prereqMet ? 'research-available' : 'research-locked'));
            ?>
            <?php
            // Icon aus unlocks-Feld ableiten (z.B. "production:baeckerei" -> "baeckerei.png")
            $coopResearchIcon = null;
            if (!empty($research['unlocks']) && strpos($research['unlocks'], 'production:') === 0) {
                $coopResearchIcon = str_replace('production:', '', $research['unlocks']) . '.png';
            }
            ?>
            <div class="research-card <?= $cardClass ?>">
                <div class="research-header">
                    <h4>
                        <?php if ($coopResearchIcon): ?>
                            <img src="<?= BASE_URL ?>/img/productions/<?= htmlspecialchars($coopResearchIcon) ?>"
                                 class="research-icon" alt="" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <?= htmlspecialchars($research['name']) ?>
                    </h4>
                    <?php if ($isCompleted): ?>
                        <span class="badge badge-success">Erforscht</span>
                    <?php elseif ($isInProgress): ?>
                        <?php
                        $startTime = strtotime($research['started_at']);
                        $endTime = $startTime + ($research['research_time_hours'] * 3600);
                        $remaining = max(0, $endTime - time());
                        $remainingHours = floor($remaining / 3600);
                        $remainingMinutes = floor(($remaining % 3600) / 60);
                        ?>
                        <span class="badge badge-warning">Läuft... <?= $remainingHours ?>h <?= $remainingMinutes ?>m</span>
                    <?php elseif (!$prereqMet): ?>
                        <span class="badge badge-secondary">Gesperrt</span>
                    <?php endif; ?>
                </div>
                <p class="research-desc"><?= htmlspecialchars($research['description']) ?></p>
                <div class="research-info">
                    <span>Kosten: <?= number_format($research['cost'], 0, ',', '.') ?> T</span>
                    <span>Dauer: <?= $research['research_time_hours'] ?>h</span>
                </div>
                <?php if (!empty($research['unlocks'])): ?>
                    <p class="research-bonus">Schaltet frei: <?= htmlspecialchars($research['unlocks']) ?></p>
                <?php endif; ?>
                <?php if ($canManage && $isAvailable && $prereqMet && !$isInProgress): ?>
                    <form action="<?= BASE_URL ?>/cooperative/start-research" method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="research_id" value="<?= $research['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary"
                                onclick="return confirm('Forschung für <?= number_format($research['cost'], 0, ',', '.') ?> T starten?')">
                            Erforschen
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.research-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.research-card { background: white; border-radius: var(--radius-lg); padding: 1.25rem; box-shadow: var(--shadow-sm); border-left: 4px solid var(--color-gray-300); }
.research-completed { border-left-color: var(--color-success); background: #f0fff4; }
.research-active { border-left-color: var(--color-warning); background: #fffbeb; }
.research-available { border-left-color: var(--color-primary); }
.research-locked { border-left-color: var(--color-gray-300); opacity: 0.6; }
.research-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
.research-header h4 { margin: 0; font-size: 1rem; display: flex; align-items: center; }
.research-icon { width: 28px; height: 28px; object-fit: contain; margin-right: 0.4rem; flex-shrink: 0; }
.research-desc { font-size: 0.85rem; color: var(--color-gray-600); margin: 0.5rem 0; }
.research-info { display: flex; gap: 1rem; font-size: 0.85rem; color: var(--color-gray-500); margin: 0.5rem 0; }
.research-bonus { font-size: 0.85rem; color: var(--color-primary); font-weight: 500; margin: 0.25rem 0; }
.badge-success { background: var(--color-success); color: white; }
.badge-warning { background: var(--color-warning); color: #333; }
.mt-2 { margin-top: 0.5rem; }
</style>
