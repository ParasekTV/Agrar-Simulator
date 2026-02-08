<div class="animals-page">
    <div class="page-header">
        <h1>Meine Tiere</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="showBuyAnimalModal()">Tiere kaufen</button>
        </div>
    </div>

    <!-- Kapazitätsübersicht -->
    <?php if (!empty($housings)): ?>
        <div class="capacity-overview">
            <h3>Stallkapazität</h3>
            <div class="capacity-grid">
                <?php foreach ($capacityOverview as $type => $info): ?>
                    <?php if ($info['capacity'] > 0): ?>
                        <div class="capacity-card">
                            <span class="capacity-type"><?= htmlspecialchars(ucfirst($info['type_name'] ?? $type)) ?></span>
                            <div class="capacity-bar">
                                <?php $percent = $info['capacity'] > 0 ? ($info['count'] / $info['capacity']) * 100 : 0; ?>
                                <div class="capacity-fill <?= $percent >= 90 ? 'full' : ($percent >= 70 ? 'warning' : '') ?>"
                                     style="width: <?= min(100, $percent) ?>%"></div>
                            </div>
                            <span class="capacity-text"><?= $info['count'] ?> / <?= $info['capacity'] ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="no-housing-warning">
            <p><strong>Hinweis:</strong> Du hast noch keine Ställe gebaut. Kaufe Ställe unter <a href="<?= BASE_URL ?>/productions/shop">Produktionen</a>, um Tiere halten zu können.</p>
        </div>
    <?php endif; ?>

    <?php if (empty($farmAnimals)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#128046;</span>
            <h3>Keine Tiere vorhanden</h3>
            <p>Kaufe deine ersten Tiere und starte mit der Viehzucht!</p>
            <button class="btn btn-primary" onclick="showBuyAnimalModal()">Tiere kaufen</button>
        </div>
    <?php else: ?>
        <div class="animals-grid">
            <?php foreach ($farmAnimals as $animal): ?>
                <div class="animal-card">
                    <div class="animal-header">
                        <span class="animal-icon">
                            <?php
                            $icons = [
                                'cow' => '&#128004;',
                                'chicken' => '&#128020;',
                                'pig' => '&#128055;',
                                'sheep' => '&#128017;',
                                'horse' => '&#128014;'
                            ];
                            echo $icons[$animal['type']] ?? '&#128046;';
                            ?>
                        </span>
                        <div class="animal-info">
                            <h4><?= htmlspecialchars($animal['name']) ?></h4>
                            <span class="animal-count">Anzahl: <?= $animal['quantity'] ?></span>
                        </div>
                    </div>

                    <div class="animal-stats">
                        <div class="animal-stat">
                            <span class="stat-label">Gesundheit</span>
                            <div class="progress-bar progress-bar-sm">
                                <div class="progress-bar-fill <?= $animal['health_status'] < 50 ? 'bg-danger' : ($animal['health_status'] < 75 ? 'bg-warning' : '') ?>"
                                     style="width: <?= $animal['health_status'] ?>%"></div>
                            </div>
                            <span class="stat-value"><?= $animal['health_status'] ?>%</span>
                        </div>
                        <div class="animal-stat">
                            <span class="stat-label">Glück</span>
                            <div class="progress-bar progress-bar-sm">
                                <div class="progress-bar-fill <?= $animal['happiness'] < 50 ? 'bg-danger' : ($animal['happiness'] < 75 ? 'bg-warning' : '') ?>"
                                     style="width: <?= $animal['happiness'] ?>%"></div>
                            </div>
                            <span class="stat-value"><?= $animal['happiness'] ?>%</span>
                        </div>
                    </div>

                    <div class="animal-production">
                        <span class="production-item">Produziert: <?= htmlspecialchars($animal['production_item']) ?></span>
                        <?php if ($animal['production_ready']): ?>
                            <span class="production-status ready">Bereit zum Sammeln!</span>
                        <?php else: ?>
                            <span class="production-status animal-timer" data-collection-time="<?= $animal['production_ready_at'] ?>">
                                Berechne...
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="animal-actions">
                        <?php if ($animal['needs_feeding']): ?>
                            <form action="<?= BASE_URL ?>/animals/feed" method="POST" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="farm_animal_id" value="<?= $animal['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    Füttern (<?= number_format($animal['feed_cost'] * $animal['quantity'], 0, ',', '.') ?> T)
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" disabled>Gefüttert</button>
                        <?php endif; ?>

                        <?php if ($animal['production_ready']): ?>
                            <form action="<?= BASE_URL ?>/animals/collect" method="POST" class="inline-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="farm_animal_id" value="<?= $animal['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Sammeln</button>
                            </form>
                        <?php endif; ?>

                        <button class="btn btn-outline btn-sm" onclick="showSellAnimalModal(<?= $animal['id'] ?>, '<?= htmlspecialchars($animal['name']) ?>', <?= $animal['quantity'] ?>)">
                            Verkaufen
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Tiere kaufen -->
<div class="modal" id="buy-animal-modal">
    <div class="modal-backdrop" onclick="closeBuyAnimalModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tiere kaufen</h3>
            <button class="modal-close" onclick="closeBuyAnimalModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/animals/buy" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="animal-type">Tierart</label>
                    <select name="animal_id" id="animal-type" class="form-select" required onchange="updateCapacityInfo()">
                        <option value="">Wähle eine Tierart...</option>
                        <?php foreach ($availableAnimals as $animal): ?>
                            <option value="<?= $animal['id'] ?>"
                                    data-cost="<?= $animal['cost'] ?>"
                                    data-capacity="<?= $animal['capacity'] ?>"
                                    data-current="<?= $animal['current_count'] ?>"
                                    data-available="<?= $animal['available_slots'] ?>"
                                    data-has-housing="<?= $animal['has_housing'] ? '1' : '0' ?>">
                                <?= htmlspecialchars($animal['name']) ?> - <?= number_format($animal['cost'], 0, ',', '.') ?> T/Stück
                                <?php if (!$animal['has_housing']): ?>
                                    (Kein Stall!)
                                <?php else: ?>
                                    (<?= $animal['available_slots'] ?> Plätze frei)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="capacity-info" class="capacity-info-box" style="display:none;">
                    <span id="capacity-status"></span>
                </div>
                <div class="form-group">
                    <label for="animal-quantity">Anzahl</label>
                    <input type="number" name="quantity" id="animal-quantity" class="form-input"
                           min="1" max="100" value="1" required onchange="updateCapacityInfo()">
                </div>
                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBuyAnimalModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary" id="buy-btn">Kaufen</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tiere verkaufen -->
<div class="modal" id="sell-animal-modal">
    <div class="modal-backdrop" onclick="closeSellAnimalModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tiere verkaufen</h3>
            <button class="modal-close" onclick="closeSellAnimalModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/animals/sell" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="farm_animal_id" id="sell-animal-id">
            <div class="modal-body">
                <p>Tierart: <strong id="sell-animal-name"></strong></p>
                <div class="form-group">
                    <label for="sell-quantity">Anzahl (max: <span id="sell-max-quantity"></span>)</label>
                    <input type="number" name="quantity" id="sell-quantity" class="form-input"
                           min="1" value="1" required>
                </div>
                <p class="form-help text-warning">Verkaufspreis: 50% des Kaufpreises</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSellAnimalModal()">Abbrechen</button>
                <button type="submit" class="btn btn-danger">Verkaufen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBuyAnimalModal() {
    document.getElementById('buy-animal-modal').classList.add('active');
    updateCapacityInfo();
}

function closeBuyAnimalModal() {
    document.getElementById('buy-animal-modal').classList.remove('active');
}

function showSellAnimalModal(id, name, maxQuantity) {
    document.getElementById('sell-animal-id').value = id;
    document.getElementById('sell-animal-name').textContent = name;
    document.getElementById('sell-max-quantity').textContent = maxQuantity;
    document.getElementById('sell-quantity').max = maxQuantity;
    document.getElementById('sell-animal-modal').classList.add('active');
}

function closeSellAnimalModal() {
    document.getElementById('sell-animal-modal').classList.remove('active');
}

function updateCapacityInfo() {
    const select = document.getElementById('animal-type');
    const quantityInput = document.getElementById('animal-quantity');
    const infoBox = document.getElementById('capacity-info');
    const statusSpan = document.getElementById('capacity-status');
    const buyBtn = document.getElementById('buy-btn');

    if (!select.value) {
        infoBox.style.display = 'none';
        buyBtn.disabled = false;
        return;
    }

    const option = select.options[select.selectedIndex];
    const hasHousing = option.dataset.hasHousing === '1';
    const capacity = parseInt(option.dataset.capacity) || 0;
    const current = parseInt(option.dataset.current) || 0;
    const available = parseInt(option.dataset.available) || 0;
    const quantity = parseInt(quantityInput.value) || 1;

    infoBox.style.display = 'block';

    if (!hasHousing) {
        statusSpan.innerHTML = '<span class="text-danger">Kein Stall vorhanden! Baue zuerst einen passenden Stall.</span>';
        buyBtn.disabled = true;
        infoBox.className = 'capacity-info-box error';
    } else if (quantity > available) {
        statusSpan.innerHTML = '<span class="text-danger">Nicht genug Platz! Verfügbar: ' + available + ' / ' + capacity + '</span>';
        buyBtn.disabled = true;
        infoBox.className = 'capacity-info-box error';
    } else {
        statusSpan.innerHTML = '<span class="text-success">Platz verfügbar: ' + available + ' / ' + capacity + '</span>';
        buyBtn.disabled = false;
        infoBox.className = 'capacity-info-box success';
    }

    // Begrenze max auf verfügbare Slots
    quantityInput.max = Math.max(1, available);
}
</script>

<style>
/* Kapazitätsübersicht */
.capacity-overview {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.capacity-overview h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.capacity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.capacity-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.capacity-type {
    font-weight: 600;
    min-width: 80px;
}

.capacity-bar {
    flex: 1;
    height: 8px;
    background: var(--color-border);
    border-radius: 4px;
    overflow: hidden;
}

.capacity-fill {
    height: 100%;
    background: var(--color-success);
    transition: width 0.3s;
}

.capacity-fill.warning {
    background: var(--color-warning);
}

.capacity-fill.full {
    background: var(--color-danger);
}

.capacity-text {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    min-width: 60px;
    text-align: right;
}

.no-housing-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 2rem;
}

.no-housing-warning p {
    margin: 0;
    color: #856404;
}

/* Kapazitäts-Info im Modal */
.capacity-info-box {
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.capacity-info-box.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
}

.capacity-info-box.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
}

.text-success { color: #155724; }
.text-danger { color: #721c24; }
</style>
