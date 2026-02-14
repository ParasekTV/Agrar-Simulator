<div class="page-header">
    <h1>Gewächshäuser</h1>
</div>

<div class="field-nav mb-4">
    <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">Felder</a>
    <a href="<?= BASE_URL ?>/fields/meadows" class="btn btn-outline">Wiesen</a>
    <a href="<?= BASE_URL ?>/fields/greenhouses" class="btn btn-primary">Gewächshäuser</a>
</div>

<!-- Gewächshaus kaufen -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Gewächshaus kaufen</h3>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>/fields/buy-greenhouse" method="POST" class="form-inline">
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

<?php if (empty($greenhouses)): ?>
    <div class="alert alert-info">
        Du hast noch keine Gewächshäuser. Kaufe ein Gewächshaus um Gemüse wie Tomaten, Gurken und Paprika anzubauen!
    </div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($greenhouses as $gh): ?>
            <div class="card greenhouse-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Gewächshaus #<?= $gh['id'] ?></h4>
                        <span class="badge badge-secondary"><?= $gh['size_hectares'] ?> ha</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status -->
                    <div class="field-status mb-3">
                        <?php if ($gh['status'] === 'empty'): ?>
                            <span class="badge badge-secondary">Leer</span>
                        <?php elseif ($gh['status'] === 'growing'): ?>
                            <span class="badge badge-warning">
                                <?= htmlspecialchars($gh['crop_name'] ?? 'Wächst') ?>
                            </span>
                        <?php elseif ($gh['status'] === 'ready'): ?>
                            <span class="badge badge-success">Erntereif</span>
                        <?php endif; ?>
                    </div>

                    <!-- Wachstums-Fortschritt -->
                    <?php if ($gh['status'] === 'growing' && $gh['planted_at']): ?>
                        <?php
                        $plantedAt = strtotime($gh['planted_at']);
                        $readyAt = strtotime($gh['harvest_ready_at']);
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
                                Stufe <?= $gh['growth_stage'] ?? 0 ?>/<?= $gh['max_growth_stages'] ?? 4 ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <!-- Aktionen -->
                    <?php if ($gh['status'] === 'empty'): ?>
                        <form action="<?= BASE_URL ?>/fields/plant" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $gh['id'] ?>">
                            <div class="form-group mb-2">
                                <select name="crop_id" class="form-select">
                                    <?php foreach ($greenhouseCrops as $crop): ?>
                                        <option value="<?= $crop['id'] ?>">
                                            <?= htmlspecialchars(ucfirst($crop['name'])) ?>
                                            (<?= $crop['growth_time_hours'] ?>h)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Pflanzen</button>
                        </form>
                    <?php elseif ($gh['status'] === 'ready'): ?>
                        <form action="<?= BASE_URL ?>/fields/harvest" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $gh['id'] ?>">
                            <button type="submit" class="btn btn-success btn-block">Ernten</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Info-Box -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Gewächshaus-Pflanzen</h3>
    </div>
    <div class="card-body">
        <?php if (empty($greenhouseCrops)): ?>
            <div class="alert alert-warning">
                Du hast noch keine Gewächshaus-Kulturen erforscht. Erforsche "Gewächshaus-Kulturen" um Tomaten, Gurken und Paprika anzubauen!
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pflanze</th>
                        <th>Kategorie</th>
                        <th>Wachstumszeit</th>
                        <th>Ertrag/ha</th>
                        <th>Verkaufspreis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $categories = [
                        'vegetable' => 'Gemüse',
                        'fruit' => 'Obst',
                        'herb' => 'Kräuter'
                    ];
                    foreach ($greenhouseCrops as $crop):
                        $catName = $categories[$crop['category'] ?? 'vegetable'] ?? 'Sonstiges';
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(ucfirst($crop['name'])) ?></strong></td>
                            <td><span class="badge badge-outline"><?= $catName ?></span></td>
                            <td><?= $crop['growth_time_hours'] ?> Stunden</td>
                            <td><?= number_format($crop['yield_per_hectare']) ?></td>
                            <td><?= number_format($crop['sell_price'], 2, ',', '.') ?> T</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.field-nav { display: flex; gap: 0.5rem; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
@media (max-width: 992px) { .grid-3 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .grid-3 { grid-template-columns: 1fr; } }
.greenhouse-card { height: 100%; }
.form-inline { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.mr-3 { margin-right: 1rem; }
.ml-2 { margin-left: 0.5rem; }
.btn-block { width: 100%; }
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
</style>
