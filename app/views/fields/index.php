<div class="fields-page">
    <div class="page-header">
        <h1>Meine Felder</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="showBuyFieldModal()">Neues Feld kaufen</button>
        </div>
    </div>

    <div class="fields-grid">
        <?php foreach ($fields as $field): ?>
            <div class="field-card field-<?= $field['status'] ?>" id="field-<?= $field['id'] ?>">
                <div class="field-header">
                    <span class="field-id">Feld #<?= $field['id'] ?></span>
                    <span class="field-size"><?= $field['size_hectares'] ?> Hektar</span>
                </div>

                <div class="field-content">
                    <?php if ($field['status'] === 'empty'): ?>
                        <div class="field-empty">
                            <span class="field-icon">&#127806;</span>
                            <p>Feld ist leer</p>
                            <form action="<?= BASE_URL ?>/fields/plant" method="POST" class="plant-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                <select name="crop_id" class="form-select" required>
                                    <option value="">Frucht waehlen...</option>
                                    <?php foreach ($availableCrops as $crop): ?>
                                        <option value="<?= $crop['id'] ?>">
                                            <?= htmlspecialchars($crop['name']) ?>
                                            (<?= number_format($crop['buy_price'] * $field['size_hectares'], 0, ',', '.') ?> T)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-success btn-sm">Pflanzen</button>
                            </form>
                        </div>

                    <?php elseif ($field['status'] === 'growing'): ?>
                        <div class="field-growing">
                            <span class="field-icon">&#127793;</span>
                            <h4><?= htmlspecialchars($field['crop_name']) ?></h4>
                            <p>Waechst...</p>
                            <div class="timer-display">
                                <span class="field-timer" data-harvest-time="<?= $field['harvest_ready_at'] ?>">
                                    Berechne...
                                </span>
                            </div>
                            <div class="progress-bar">
                                <?php
                                $planted = strtotime($field['planted_at']);
                                $ready = strtotime($field['harvest_ready_at']);
                                $now = time();
                                $progress = min(100, (($now - $planted) / ($ready - $planted)) * 100);
                                ?>
                                <div class="progress-bar-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>

                    <?php elseif ($field['status'] === 'ready'): ?>
                        <div class="field-ready">
                            <span class="field-icon animate-bounce">&#127807;</span>
                            <h4><?= htmlspecialchars($field['crop_name']) ?></h4>
                            <p class="text-success">Bereit zur Ernte!</p>
                            <form action="<?= BASE_URL ?>/fields/harvest" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                <button type="submit" class="btn btn-success">Ernten</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-footer">
                    <div class="soil-quality">
                        <span>Bodenqualitaet:</span>
                        <div class="progress-bar progress-bar-sm">
                            <div class="progress-bar-fill <?= $field['soil_quality'] < 60 ? 'bg-warning' : '' ?>"
                                 style="width: <?= $field['soil_quality'] ?>%"></div>
                        </div>
                        <span><?= $field['soil_quality'] ?>%</span>
                    </div>
                    <?php if ($field['soil_quality'] < 100 && $field['status'] === 'empty'): ?>
                        <form action="<?= BASE_URL ?>/fields/fertilize" method="POST" class="fertilize-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm">Duengen</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: Neues Feld kaufen -->
<div class="modal" id="buy-field-modal">
    <div class="modal-backdrop" onclick="closeBuyFieldModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Neues Feld kaufen</h3>
            <button class="modal-close" onclick="closeBuyFieldModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/fields/buy" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="field-size">Feldgroesse (Hektar)</label>
                    <select name="size" id="field-size" class="form-select" required>
                        <option value="1">1 Hektar - 2.000 T</option>
                        <option value="2">2 Hektar - 4.000 T</option>
                        <option value="3">3 Hektar - 6.000 T</option>
                        <option value="5">5 Hektar - 10.000 T</option>
                        <option value="10">10 Hektar - 20.000 T</option>
                    </select>
                </div>
                <p class="form-help">Preis: 2.000 T pro Hektar</p>
                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBuyFieldModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Kaufen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBuyFieldModal() {
    document.getElementById('buy-field-modal').classList.add('active');
}

function closeBuyFieldModal() {
    document.getElementById('buy-field-modal').classList.remove('active');
}
</script>
