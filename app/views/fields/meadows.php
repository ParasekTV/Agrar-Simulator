<div class="page-header">
    <h1>Wiesen</h1>
</div>

<div class="field-nav mb-4">
    <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">Felder</a>
    <a href="<?= BASE_URL ?>/fields/meadows" class="btn btn-primary">Wiesen</a>
    <a href="<?= BASE_URL ?>/fields/greenhouses" class="btn btn-outline">Gewächshäuser</a>
</div>

<!-- Wiese kaufen -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Wiese kaufen</h3>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/fields/buy-meadow" method="POST" class="form-inline">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="form-group mr-3">
                <label for="size">Größe:</label>
                <select name="size" id="size" class="form-select ml-2">
                    <?php foreach ($fieldLimits as $limit): ?>
                        <option value="<?= $limit['size_hectares'] ?>">
                            <?= $limit['size_hectares'] ?> ha - <?= number_format($limit['price_per_hectare'] * $limit['size_hectares'], 0, ',', '.') ?> T
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Kaufen</button>
        </form>
    </div>
</div>

<?php if (empty($meadows)): ?>
    <div class="alert alert-info">
        Du hast noch keine Wiesen. Kaufe eine Wiese um Gras zu produzieren!
    </div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($meadows as $meadow): ?>
            <div class="card field-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Wiese #<?= $meadow['id'] ?></h4>
                        <span class="badge badge-secondary"><?= $meadow['size_hectares'] ?> ha</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="field-status mb-3">
                        <?php if ($meadow['status'] === 'ready'): ?>
                            <span class="badge badge-success">Bereit zum Mähen</span>
                        <?php elseif ($meadow['status'] === 'growing'): ?>
                            <span class="badge badge-warning">Gras wächst</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?= ucfirst($meadow['status']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Bodenqualität -->
                    <div class="soil-quality mb-3">
                        <label>Bodenqualität:</label>
                        <div class="progress">
                            <div class="progress-bar <?= $meadow['soil_quality'] < 50 ? 'bg-danger' : ($meadow['soil_quality'] < 75 ? 'bg-warning' : 'bg-success') ?>"
                                 style="width: <?= $meadow['soil_quality'] ?>%">
                                <?= $meadow['soil_quality'] ?>%
                            </div>
                        </div>
                    </div>

                    <!-- Wachstums-Fortschritt -->
                    <?php if ($meadow['status'] === 'growing' && $meadow['planted_at']): ?>
                        <?php
                        $plantedAt = strtotime($meadow['planted_at']);
                        $readyAt = strtotime($meadow['harvest_ready_at']);
                        $now = time();
                        $progress = min(100, (($now - $plantedAt) / ($readyAt - $plantedAt)) * 100);
                        ?>
                        <div class="growth-progress mb-3">
                            <label>Wachstum:</label>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?= $progress ?>%">
                                    <?= round($progress) ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                Bereit: <?= date('d.m.Y H:i', $readyAt) ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <!-- Aktionen -->
                    <?php if ($meadow['status'] === 'ready'): ?>
                        <form action="<?= BASE_URL ?>/fields/mow" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $meadow['id'] ?>">
                            <button type="submit" class="btn btn-success btn-block">
                                Mähen
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.field-nav { display: flex; gap: 0.5rem; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
@media (max-width: 992px) { .grid-3 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .grid-3 { grid-template-columns: 1fr; } }
.field-card { height: 100%; }
.form-inline { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.mr-3 { margin-right: 1rem; }
.ml-2 { margin-left: 0.5rem; }
.btn-block { width: 100%; }
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
</style>
