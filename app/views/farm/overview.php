<div class="farm-overview">
    <div class="page-header">
        <h1><?= htmlspecialchars($farm['farm_name']) ?></h1>
        <span class="farm-level">Level <?= $farm['level'] ?></span>
    </div>

    <!-- Statistiken -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128176;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
                <span class="stat-label">Guthaben</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">&#11088;</div>
            <div class="stat-info">
                <span class="stat-value"><?= number_format($farm['points']) ?></span>
                <span class="stat-label">Punkte</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">&#127806;</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['fields_total'] ?></span>
                <span class="stat-label">Felder</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">&#128046;</div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['animals'] ?></span>
                <span class="stat-label">Tiere</span>
            </div>
        </div>
    </div>

    <div class="overview-grid">
        <!-- Felder -->
        <div class="card">
            <div class="card-header">
                <h3>Felder (<?= $stats['fields_total'] ?>)</h3>
                <a href="<?= BASE_URL ?>/fields" class="btn btn-sm btn-outline">Verwalten</a>
            </div>
            <div class="card-body">
                <div class="mini-stats">
                    <div class="mini-stat">
                        <span class="value"><?= $stats['fields_growing'] ?></span>
                        <span class="label">Wachsend</span>
                    </div>
                    <div class="mini-stat">
                        <span class="value text-success"><?= $stats['fields_ready'] ?></span>
                        <span class="label">Bereit</span>
                    </div>
                    <div class="mini-stat">
                        <span class="value"><?= $stats['fields_total'] - $stats['fields_growing'] - $stats['fields_ready'] ?></span>
                        <span class="label">Leer</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tiere -->
        <div class="card">
            <div class="card-header">
                <h3>Tiere (<?= $stats['animals'] ?>)</h3>
                <a href="<?= BASE_URL ?>/animals" class="btn btn-sm btn-outline">Verwalten</a>
            </div>
            <div class="card-body">
                <?php if (empty($animals)): ?>
                    <p class="text-muted">Keine Tiere vorhanden.</p>
                <?php else: ?>
                    <ul class="animal-list">
                        <?php foreach ($animals as $animal): ?>
                            <li>
                                <span><?= htmlspecialchars($animal['name']) ?></span>
                                <span class="count"><?= $animal['quantity'] ?>x</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Fahrzeuge -->
        <div class="card">
            <div class="card-header">
                <h3>Fahrzeuge (<?= $stats['vehicles'] ?>)</h3>
                <a href="<?= BASE_URL ?>/vehicles" class="btn btn-sm btn-outline">Verwalten</a>
            </div>
            <div class="card-body">
                <?php if (empty($vehicles)): ?>
                    <p class="text-muted">Keine Fahrzeuge vorhanden.</p>
                <?php else: ?>
                    <ul class="vehicle-list">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <li>
                                <span><?= htmlspecialchars($vehicle['name']) ?></span>
                                <span class="condition <?= $vehicle['condition_percent'] < 50 ? 'text-danger' : '' ?>">
                                    <?= $vehicle['condition_percent'] ?>%
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gebaeude -->
        <div class="card">
            <div class="card-header">
                <h3>Gebaeude (<?= $stats['buildings'] ?>)</h3>
            </div>
            <div class="card-body">
                <?php if (empty($buildings)): ?>
                    <p class="text-muted">Keine Gebaeude vorhanden.</p>
                <?php else: ?>
                    <ul class="building-list">
                        <?php foreach ($buildings as $building): ?>
                            <li>
                                <span><?= htmlspecialchars($building['name']) ?></span>
                                <span class="level">Stufe <?= $building['level'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Schnellaktionen</h3>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>/fields" class="action-btn">
                <span class="action-icon">&#127806;</span>
                <span>Felder</span>
            </a>
            <a href="<?= BASE_URL ?>/animals" class="action-btn">
                <span class="action-icon">&#128046;</span>
                <span>Tiere</span>
            </a>
            <a href="<?= BASE_URL ?>/vehicles" class="action-btn">
                <span class="action-icon">&#128666;</span>
                <span>Fahrzeuge</span>
            </a>
            <a href="<?= BASE_URL ?>/research" class="action-btn">
                <span class="action-icon">&#128300;</span>
                <span>Forschung</span>
            </a>
            <a href="<?= BASE_URL ?>/market" class="action-btn">
                <span class="action-icon">&#128722;</span>
                <span>Markt</span>
            </a>
            <a href="<?= BASE_URL ?>/inventory" class="action-btn">
                <span class="action-icon">&#128230;</span>
                <span>Inventar</span>
            </a>
        </div>
    </div>
</div>

<style>
.farm-level {
    background: var(--color-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--radius);
    font-weight: 600;
}
.overview-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.mini-stats {
    display: flex;
    justify-content: space-around;
}
.mini-stat {
    text-align: center;
}
.mini-stat .value {
    font-size: 1.5rem;
    font-weight: 700;
    display: block;
}
.mini-stat .label {
    font-size: 0.85rem;
    color: var(--color-gray-600);
}
.animal-list, .vehicle-list, .building-list {
    list-style: none;
}
.animal-list li, .vehicle-list li, .building-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-gray-200);
}
.animal-list li:last-child, .vehicle-list li:last-child, .building-list li:last-child {
    border-bottom: none;
}
.count, .condition, .level {
    font-weight: 500;
    color: var(--color-gray-600);
}
.quick-actions {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.quick-actions h3 {
    margin-bottom: 1rem;
}
.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}
.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: var(--color-gray-100);
    border-radius: var(--radius);
    color: var(--color-gray-700);
    transition: var(--transition);
}
.action-btn:hover {
    background: var(--color-primary);
    color: white;
}
.action-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
@media (max-width: 768px) {
    .overview-grid {
        grid-template-columns: 1fr;
    }
}
</style>
