<div class="animals-page">
    <div class="page-header">
        <h1>Meine Tiere</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="showBuyAnimalModal()">Tiere kaufen</button>
        </div>
    </div>

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
                            <span class="stat-label">Glueck</span>
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
                                    Fuettern (<?= number_format($animal['feed_cost'] * $animal['quantity'], 0, ',', '.') ?> T)
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" disabled>Gefuettert</button>
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
                    <select name="animal_id" id="animal-type" class="form-select" required>
                        <option value="">Waehle eine Tierart...</option>
                        <?php foreach ($availableAnimals as $animal): ?>
                            <option value="<?= $animal['id'] ?>" data-cost="<?= $animal['cost'] ?>">
                                <?= htmlspecialchars($animal['name']) ?> - <?= number_format($animal['cost'], 0, ',', '.') ?> T/Stueck
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="animal-quantity">Anzahl</label>
                    <input type="number" name="quantity" id="animal-quantity" class="form-input"
                           min="1" max="100" value="1" required>
                </div>
                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBuyAnimalModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Kaufen</button>
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
</script>
