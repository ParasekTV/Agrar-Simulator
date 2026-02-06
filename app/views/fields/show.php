<div class="field-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">&larr; Zurück</a>
        <h1>Feld #<?= $field['id'] ?></h1>
    </div>

    <div class="field-detail-card">
        <div class="field-visual field-<?= $field['status'] ?>">
            <div class="field-icon-large">
                <?php if ($field['status'] === 'empty'): ?>
                    &#127806;
                <?php elseif ($field['status'] === 'growing'): ?>
                    &#127793;
                <?php elseif ($field['status'] === 'ready'): ?>
                    &#127807;
                <?php endif; ?>
            </div>
            <h2>
                <?php if ($field['status'] === 'empty'): ?>
                    Leeres Feld
                <?php elseif ($field['crop_name']): ?>
                    <?= htmlspecialchars($field['crop_name']) ?>
                <?php endif; ?>
            </h2>
        </div>

        <div class="field-stats">
            <div class="stat-row">
                <span class="stat-label">Größe</span>
                <span class="stat-value"><?= $field['size_hectares'] ?> Hektar</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Bodenqualität</span>
                <div class="stat-bar">
                    <div class="progress-bar">
                        <div class="progress-bar-fill <?= $field['soil_quality'] < 60 ? 'bg-warning' : '' ?>"
                             style="width: <?= $field['soil_quality'] ?>%"></div>
                    </div>
                    <span><?= $field['soil_quality'] ?>%</span>
                </div>
            </div>
            <div class="stat-row">
                <span class="stat-label">Status</span>
                <span class="stat-value status-<?= $field['status'] ?>">
                    <?php
                    $statusNames = [
                        'empty' => 'Leer',
                        'growing' => 'Wachsend',
                        'ready' => 'Erntereif',
                        'harvesting' => 'Wird geerntet'
                    ];
                    echo $statusNames[$field['status']] ?? $field['status'];
                    ?>
                </span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Position</span>
                <span class="stat-value">X: <?= $field['position_x'] ?>, Y: <?= $field['position_y'] ?></span>
            </div>

            <?php if ($field['status'] === 'growing'): ?>
                <div class="stat-row">
                    <span class="stat-label">Gepflanzt</span>
                    <span class="stat-value"><?= date('d.m.Y H:i', strtotime($field['planted_at'])) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Erntereif</span>
                    <span class="stat-value field-timer" data-harvest-time="<?= $field['harvest_ready_at'] ?>">
                        <?= date('d.m.Y H:i', strtotime($field['harvest_ready_at'])) ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="field-actions-large">
            <?php if ($field['status'] === 'empty'): ?>
                <form action="<?= BASE_URL ?>/fields/plant" method="POST" class="plant-form-large">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="field_id" value="<?= $field['id'] ?>">

                    <div class="form-group">
                        <label for="crop">Pflanze auswählen</label>
                        <select name="crop_id" id="crop" class="form-select" required>
                            <option value="">Wähle eine Frucht...</option>
                            <?php foreach ($availableCrops as $crop): ?>
                                <option value="<?= $crop['id'] ?>">
                                    <?= htmlspecialchars($crop['name']) ?>
                                    - <?= number_format($crop['buy_price'] * $field['size_hectares'], 0, ',', '.') ?> T
                                    (<?= $crop['growth_time_hours'] ?>h Wachstumszeit)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-block">Pflanzen</button>
                </form>

                <?php if ($field['soil_quality'] < 100): ?>
                    <form action="<?= BASE_URL ?>/fields/fertilize" method="POST" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-block">
                            Boden düngen (+20% Qualität)
                        </button>
                    </form>
                <?php endif; ?>

            <?php elseif ($field['status'] === 'ready'): ?>
                <form action="<?= BASE_URL ?>/fields/harvest" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                    <button type="submit" class="btn btn-success btn-block btn-lg">
                        &#127807; Jetzt ernten!
                    </button>
                </form>
                <p class="harvest-info">
                    Erwarteter Ertrag: ca. <?= number_format($field['size_hectares'] * 100 * ($field['soil_quality'] / 100)) ?> Einheiten
                </p>

            <?php elseif ($field['status'] === 'growing'): ?>
                <div class="growing-info">
                    <p class="text-muted">Dieses Feld wird gerade bewirtschaftet.</p>
                    <div class="timer-large">
                        <span class="field-timer" data-harvest-time="<?= $field['harvest_ready_at'] ?>">
                            Berechne Zeit...
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.field-detail-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
    max-width: 600px;
}
.field-visual {
    padding: 3rem;
    text-align: center;
    background: var(--color-gray-100);
}
.field-visual.field-empty { background: #f5f5f5; }
.field-visual.field-growing { background: linear-gradient(135deg, #fff9c4 0%, #f0f4c3 100%); }
.field-visual.field-ready { background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%); }
.field-icon-large {
    font-size: 5rem;
    margin-bottom: 1rem;
}
.field-visual h2 {
    margin: 0;
    color: var(--color-gray-800);
}
.field-stats {
    padding: 1.5rem;
}
.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--color-gray-200);
}
.stat-row:last-child {
    border-bottom: none;
}
.field-detail-page .stat-label {
    color: var(--color-gray-600);
}
.field-detail-page .stat-value {
    font-weight: 500;
}
.stat-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    max-width: 200px;
}
.stat-bar .progress-bar {
    flex: 1;
}
.status-empty { color: var(--color-gray-500); }
.status-growing { color: var(--color-warning); }
.status-ready { color: var(--color-success); font-weight: 600; }
.field-actions-large {
    padding: 1.5rem;
    border-top: 1px solid var(--color-gray-200);
    background: var(--color-gray-50);
}
.plant-form-large .form-group {
    margin-bottom: 1rem;
}
.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}
.harvest-info {
    text-align: center;
    margin-top: 1rem;
    color: var(--color-gray-600);
}
.growing-info {
    text-align: center;
}
.timer-large {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--color-primary);
    margin-top: 0.5rem;
}
</style>
