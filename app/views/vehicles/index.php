<div class="vehicles-page">
    <div class="page-header">
        <h1>Meine Fahrzeuge</h1>
        <div class="page-actions">
            <span class="efficiency-bonus">Gesamt-Effizienzbonus: +<?= number_format($efficiencyBonus, 1) ?>%</span>
            <button class="btn btn-primary" onclick="showBuyVehicleModal()">Fahrzeug kaufen</button>
        </div>
    </div>

    <?php if (empty($farmVehicles)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#128666;</span>
            <h3>Keine Fahrzeuge vorhanden</h3>
            <p>Kaufe Fahrzeuge, um effizienter zu arbeiten!</p>
            <button class="btn btn-primary" onclick="showBuyVehicleModal()">Fahrzeug kaufen</button>
        </div>
    <?php else: ?>
        <div class="vehicles-grid">
            <?php foreach ($farmVehicles as $vehicle): ?>
                <div class="vehicle-card">
                    <div class="vehicle-header">
                        <span class="vehicle-icon">
                            <?php
                            $icons = [
                                'tractor' => '&#128666;',
                                'harvester' => '&#127806;',
                                'seeder' => '&#127793;',
                                'plow' => '&#129683;',
                                'trailer' => '&#128666;'
                            ];
                            echo $icons[$vehicle['type']] ?? '&#128666;';
                            ?>
                        </span>
                        <div class="vehicle-info">
                            <h4><?= htmlspecialchars($vehicle['name']) ?></h4>
                            <span class="vehicle-type"><?= ucfirst($vehicle['type']) ?></span>
                        </div>
                    </div>

                    <div class="vehicle-stats">
                        <div class="vehicle-stat">
                            <span class="stat-label">Zustand</span>
                            <div class="progress-bar progress-bar-sm">
                                <div class="progress-bar-fill <?= $vehicle['condition_percent'] < 50 ? 'bg-danger' : ($vehicle['condition_percent'] < 75 ? 'bg-warning' : '') ?>"
                                     style="width: <?= $vehicle['condition_percent'] ?>%"></div>
                            </div>
                            <span class="stat-value"><?= $vehicle['condition_percent'] ?>%</span>
                        </div>
                        <div class="vehicle-stat">
                            <span class="stat-label">Effizienz</span>
                            <span class="stat-value">+<?= $vehicle['efficiency_bonus'] ?>%</span>
                        </div>
                        <div class="vehicle-stat">
                            <span class="stat-label">Betriebsstunden</span>
                            <span class="stat-value"><?= $vehicle['hours_used'] ?>h</span>
                        </div>
                    </div>

                    <div class="vehicle-actions">
                        <?php if ($vehicle['condition_percent'] < 100): ?>
                            <form action="<?= BASE_URL ?>/vehicles/repair" method="POST" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="farm_vehicle_id" value="<?= $vehicle['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Reparieren</button>
                            </form>
                        <?php endif; ?>
                        <form action="<?= BASE_URL ?>/vehicles/sell" method="POST" class="inline-form"
                              onsubmit="return confirm('Wirklich verkaufen?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="farm_vehicle_id" value="<?= $vehicle['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm">Verkaufen</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Fahrzeug kaufen -->
<div class="modal" id="buy-vehicle-modal">
    <div class="modal-backdrop" onclick="closeBuyVehicleModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Fahrzeug kaufen</h3>
            <button class="modal-close" onclick="closeBuyVehicleModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/vehicles/buy" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="vehicle-type">Fahrzeug waehlen</label>
                    <select name="vehicle_id" id="vehicle-type" class="form-select" required>
                        <option value="">Waehle ein Fahrzeug...</option>
                        <?php foreach ($availableVehicles as $vehicle): ?>
                            <option value="<?= $vehicle['id'] ?>">
                                <?= htmlspecialchars($vehicle['name']) ?> -
                                <?= number_format($vehicle['cost'], 0, ',', '.') ?> EUR
                                (+<?= $vehicle['efficiency_bonus'] ?>% Effizienz)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 2, ',', '.') ?> EUR</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBuyVehicleModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Kaufen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBuyVehicleModal() {
    document.getElementById('buy-vehicle-modal').classList.add('active');
}
function closeBuyVehicleModal() {
    document.getElementById('buy-vehicle-modal').classList.remove('active');
}
</script>

<style>
.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.vehicle-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
}
.vehicle-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}
.vehicle-icon { font-size: 2.5rem; }
.vehicle-info h4 { margin: 0; }
.vehicle-type { font-size: 0.9rem; color: var(--color-gray-600); }
.vehicle-stats { margin-bottom: 1rem; }
.vehicle-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}
.vehicle-stat .stat-label { width: 100px; color: var(--color-gray-600); }
.vehicle-stat .progress-bar { flex: 1; }
.vehicle-actions { display: flex; gap: 0.5rem; }
.efficiency-bonus {
    background: var(--color-primary-light);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    font-weight: 500;
}
</style>
