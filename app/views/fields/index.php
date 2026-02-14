<div class="fields-page">
    <div class="page-header">
        <h1>Meine Felder</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="showBuyFieldModal()">Neues Feld kaufen</button>
        </div>
    </div>

    <div class="field-nav mb-4">
        <a href="<?= BASE_URL ?>/fields" class="btn btn-primary">Felder</a>
        <a href="<?= BASE_URL ?>/fields/meadows" class="btn btn-outline">Wiesen</a>
        <a href="<?= BASE_URL ?>/fields/greenhouses" class="btn btn-outline">Gewächshäuser</a>
    </div>

    <div class="fields-grid">
        <?php foreach ($fields as $field): ?>
            <?php
            // pH-Wert Farbcodierung
            $soilPh = $field['soil_ph'] ?? 7.0;
            $phClass = 'text-success';
            if ($soilPh < 5.5 || $soilPh > 8.0) {
                $phClass = 'text-danger';
            } elseif ($soilPh < 6.0 || $soilPh > 7.5) {
                $phClass = 'text-warning';
            }
            ?>
            <?php
            $fieldTypeLabel = match($field['field_type'] ?? 'field') {
                'meadow' => 'Wiese',
                'greenhouse' => 'Gewächshaus',
                default => 'Feld'
            };
            $fieldTypeClass = $field['field_type'] ?? 'field';
            ?>
            <div class="field-card field-<?= $field['status'] ?> field-type-<?= $fieldTypeClass ?>" id="field-<?= $field['id'] ?>">
                <div class="field-header">
                    <span class="field-id"><?= $fieldTypeLabel ?> #<?= $field['id'] ?></span>
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
                                    <option value="">Frucht wählen...</option>
                                    <?php
                                    $currentCategory = '';
                                    $categoryNames = [
                                        'grain' => 'Getreide',
                                        'vegetable' => 'Gemüse',
                                        'industrial' => 'Industriepflanzen',
                                        'legume' => 'Hülsenfrüchte',
                                        'fodder' => 'Futterpflanzen',
                                        'oil' => 'Ölpflanzen',
                                        'fruit' => 'Obst'
                                    ];
                                    foreach ($availableCrops as $crop):
                                        $category = $crop['category'] ?? 'grain';
                                        if ($category !== $currentCategory):
                                            if ($currentCategory !== ''):
                                                echo '</optgroup>';
                                            endif;
                                            $currentCategory = $category;
                                            $categoryLabel = $categoryNames[$category] ?? ucfirst($category);
                                    ?>
                                    <optgroup label="<?= $categoryLabel ?>">
                                    <?php endif; ?>
                                        <?php
                                        // pH-Warnung prüfen
                                        $optPhMin = $crop['optimal_ph_min'] ?? 6.0;
                                        $optPhMax = $crop['optimal_ph_max'] ?? 7.5;
                                        $phWarning = ($soilPh < $optPhMin || $soilPh > $optPhMax);
                                        ?>
                                        <option value="<?= $crop['id'] ?>"
                                                data-ph-min="<?= $optPhMin ?>"
                                                data-ph-max="<?= $optPhMax ?>">
                                            <?= htmlspecialchars($crop['name']) ?>
                                            (<?= number_format($crop['buy_price'] * $field['size_hectares'], 0, ',', '.') ?> T)
                                            <?php if ($phWarning): ?> ⚠ pH<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if ($currentCategory !== ''): ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" class="btn btn-success btn-sm">Pflanzen</button>
                            </form>
                        </div>

                    <?php elseif ($field['status'] === 'growing'): ?>
                        <?php
                        // Bestimme Anzeigename basierend auf field_type
                        $displayName = $field['crop_name'] ?? null;
                        if (!$displayName) {
                            $displayName = match($field['field_type'] ?? 'field') {
                                'meadow' => 'Gras',
                                'greenhouse' => 'Gewächshaus-Pflanze',
                                default => 'Unbekannt'
                            };
                        }
                        ?>
                        <div class="field-growing">
                            <span class="field-icon"><?= ($field['field_type'] ?? 'field') === 'meadow' ? '&#127811;' : '&#127793;' ?></span>
                            <h4><?= htmlspecialchars($displayName) ?></h4>
                            <p>Wächst...</p>
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
                            <?php if (!empty($field['active_fertilizer_id'])): ?>
                                <span class="badge badge-success mt-2">Dünger aktiv</span>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($field['status'] === 'ready'): ?>
                        <?php
                        // Bestimme Anzeigename basierend auf field_type
                        $displayNameReady = $field['crop_name'] ?? null;
                        if (!$displayNameReady) {
                            $displayNameReady = match($field['field_type'] ?? 'field') {
                                'meadow' => 'Gras',
                                'greenhouse' => 'Gewächshaus-Pflanze',
                                default => 'Unbekannt'
                            };
                        }
                        $isMeadow = ($field['field_type'] ?? 'field') === 'meadow';
                        ?>
                        <div class="field-ready">
                            <span class="field-icon animate-bounce"><?= $isMeadow ? '&#127811;' : '&#127807;' ?></span>
                            <h4><?= htmlspecialchars($displayNameReady) ?></h4>
                            <p class="text-success"><?= $isMeadow ? 'Bereit zum Mähen!' : 'Bereit zur Ernte!' ?></p>
                            <?php if ($isMeadow): ?>
                            <form action="<?= BASE_URL ?>/fields/mow" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                <button type="submit" class="btn btn-success">Mähen</button>
                            </form>
                            <?php else: ?>
                            <form action="<?= BASE_URL ?>/fields/harvest" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                <button type="submit" class="btn btn-success">Ernten</button>
                            </form>
                            <?php endif; ?>
                            <?php if (!empty($field['active_fertilizer_id'])): ?>
                                <span class="badge badge-success mt-2">Dünger-Bonus aktiv</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="field-footer">
                    <!-- Bodenqualität -->
                    <div class="soil-quality">
                        <span>Qualität:</span>
                        <div class="progress-bar progress-bar-sm">
                            <div class="progress-bar-fill <?= $field['soil_quality'] < 60 ? 'bg-warning' : '' ?>"
                                 style="width: <?= $field['soil_quality'] ?>%"></div>
                        </div>
                        <span><?= $field['soil_quality'] ?>%</span>
                    </div>

                    <!-- pH-Wert -->
                    <div class="soil-ph">
                        <span>pH:</span>
                        <span class="<?= $phClass ?> fw-bold"><?= number_format($soilPh, 1) ?></span>
                    </div>

                    <!-- Bodenbehandlung (nur für leere Felder) -->
                    <?php if ($field['status'] === 'empty'): ?>
                        <div class="field-treatments">
                            <!-- Dünger-Dropdown -->
                            <?php if (!empty($availableFertilizers)): ?>
                                <div class="dropdown">
                                    <button class="btn btn-outline btn-sm dropdown-toggle" type="button"
                                            onclick="toggleDropdown('fert-<?= $field['id'] ?>', event)">
                                        Düngen
                                    </button>
                                    <div class="dropdown-menu" id="fert-<?= $field['id'] ?>">
                                        <?php foreach ($availableFertilizers as $fert): ?>
                                            <form action="<?= BASE_URL ?>/fields/apply-fertilizer" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                                <input type="hidden" name="fertilizer_type_id" value="<?= $fert['id'] ?>">
                                                <button type="submit" class="dropdown-item">
                                                    <strong><?= htmlspecialchars($fert['name']) ?></strong>
                                                    <small>
                                                        +<?= $fert['quality_boost'] ?>% Qualität
                                                        <?php if ($fert['yield_multiplier'] > 1): ?>
                                                            | +<?= (($fert['yield_multiplier'] - 1) * 100) ?>% Ertrag
                                                        <?php endif; ?>
                                                    </small>
                                                    <span class="price"><?= number_format($fert['cost_per_hectare'] * $field['size_hectares'], 0, ',', '.') ?> T</span>
                                                </button>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Kalk-Dropdown (nur wenn pH unter 7.5) -->
                            <?php if (!empty($availableLimeTypes) && $soilPh < 7.5): ?>
                                <div class="dropdown">
                                    <button class="btn btn-outline btn-sm dropdown-toggle" type="button"
                                            onclick="toggleDropdown('lime-<?= $field['id'] ?>', event)">
                                        Kalken
                                    </button>
                                    <div class="dropdown-menu" id="lime-<?= $field['id'] ?>">
                                        <?php foreach ($availableLimeTypes as $lime): ?>
                                            <form action="<?= BASE_URL ?>/fields/lime" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="field_id" value="<?= $field['id'] ?>">
                                                <input type="hidden" name="lime_type_id" value="<?= $lime['id'] ?>">
                                                <button type="submit" class="dropdown-item">
                                                    <strong><?= htmlspecialchars($lime['name']) ?></strong>
                                                    <small>+<?= $lime['ph_increase'] ?> pH</small>
                                                    <span class="price"><?= number_format($lime['cost_per_hectare'] * $field['size_hectares'], 0, ',', '.') ?> T</span>
                                                </button>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
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
                    <label for="field-size">Feldgröße (Hektar)</label>
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

function toggleDropdown(id, event) {
    event.stopPropagation();

    var menu = document.getElementById(id);
    var isCurrentlyShown = menu.classList.contains('show');

    // Alle Dropdowns schließen
    document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
        m.classList.remove('show');
    });

    // Wenn es geschlossen war, jetzt öffnen
    if (!isCurrentlyShown) {
        menu.classList.add('show');
    }
}

// Dropdowns schließen bei Klick außerhalb
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
});
</script>

<style>
/* Field Card overflow fix für Dropdowns */
.fields-page .field-card {
    overflow: visible !important;
}

/* Dropdown Styles für Feld-Behandlung */
.field-treatments {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    left: 0;
    bottom: 100%;
    margin-bottom: 8px;
    min-width: 250px;
    background: #1a1a2e;
    border: 1px solid #2d2d44;
    border-radius: 8px;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.4);
    z-index: 1000;
}

.dropdown-menu.show {
    display: block;
}

/* Pfeil unten am Dropdown */
.dropdown-menu::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 15px;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid #2d2d44;
}

.dropdown-menu::before {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 15px;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid #1a1a2e;
    z-index: 1;
}

.dropdown-item {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    background: #1a1a2e;
    text-align: left;
    cursor: pointer;
    border-bottom: 1px solid #2d2d44;
    color: #ffffff;
}

.dropdown-item:first-child {
    border-radius: 8px 8px 0 0;
}

.dropdown-item:last-child {
    border-bottom: none;
    border-radius: 0 0 8px 8px;
}

.dropdown-item:only-child {
    border-radius: 8px;
}

.dropdown-item:hover {
    background: #252542;
}

.dropdown-item strong {
    color: #ffffff;
}

.dropdown-item small {
    color: #a0a0b0;
    font-size: 0.8rem;
}

.dropdown-item .price {
    color: #4ade80;
    font-weight: 600;
    margin-top: 0.25rem;
}

/* Soil pH Display */
.soil-ph {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.field-footer {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid var(--color-border);
}

.soil-quality {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-info {
    background: var(--color-primary);
    color: white;
}

.mt-2 {
    margin-top: 0.5rem;
}

.fw-bold {
    font-weight: 700;
}

.text-success {
    color: var(--color-success);
}

.text-warning {
    color: var(--color-warning);
}

.text-danger {
    color: var(--color-danger);
}

/* Feld-Typ Styles */
.field-type-meadow .field-header {
    background: linear-gradient(135deg, #228B22, #32CD32);
}

.field-type-greenhouse .field-header {
    background: linear-gradient(135deg, #4682B4, #5F9EA0);
}

.field-type-meadow .field-id::before {
    content: '🌿 ';
}

.field-type-greenhouse .field-id::before {
    content: '🏠 ';
}
</style>
