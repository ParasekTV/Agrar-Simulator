<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin/farms" class="btn btn-outline">&larr; Zurück</a>
        <h1><?= htmlspecialchars($farm['farm_name']) ?></h1>
    </div>

    <div class="admin-edit-grid">
        <!-- Farm-Formular -->
        <div class="card">
            <div class="card-header">
                <h3>Farm-Daten</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/admin/farms/<?= $farm['id'] ?>/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="farm_name">Farm-Name</label>
                        <input type="text" id="farm_name" name="farm_name" class="form-control"
                               value="<?= htmlspecialchars($farm['farm_name']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="money">Geld (T)</label>
                            <input type="number" id="money" name="money" class="form-control"
                                   value="<?= $farm['money'] ?>" step="1">
                        </div>
                        <div class="form-group">
                            <label for="points">Punkte</label>
                            <input type="number" id="points" name="points" class="form-control"
                                   value="<?= $farm['points'] ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="level">Level</label>
                            <input type="number" id="level" name="level" class="form-control"
                                   value="<?= $farm['level'] ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label for="experience">Erfahrung</label>
                            <input type="number" id="experience" name="experience" class="form-control"
                                   value="<?= $farm['experience'] ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Besitzer-Info -->
        <div class="card">
            <div class="card-header">
                <h3>Besitzer</h3>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Benutzername:</span>
                        <span class="info-value"><?= htmlspecialchars($farm['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">E-Mail:</span>
                        <span class="info-value"><?= htmlspecialchars($farm['email']) ?></span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/admin/users/<?= $farm['user_id'] ?>" class="btn btn-outline btn-block mt-4">
                    Benutzer bearbeiten
                </a>
            </div>
        </div>
    </div>

    <!-- Felder -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Felder (<?= count($fields) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($fields)): ?>
                <p class="text-muted">Keine Felder vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Größe</th>
                            <th>Status</th>
                            <th>Bodenqualität</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fields as $field): ?>
                            <tr>
                                <td><?= $field['id'] ?></td>
                                <td><?= $field['size_hectares'] ?> ha</td>
                                <td>
                                    <span class="status-badge status-<?= $field['status'] ?>">
                                        <?= ucfirst($field['status']) ?>
                                    </span>
                                </td>
                                <td><?= $field['soil_quality'] ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tiere -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Tiere (<?= count($animals) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($animals)): ?>
                <p class="text-muted">Keine Tiere vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tier</th>
                            <th>Anzahl</th>
                            <th>Gesundheit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animals as $animal): ?>
                            <tr>
                                <td><?= $animal['id'] ?></td>
                                <td><?= htmlspecialchars($animal['animal_name']) ?></td>
                                <td><?= $animal['quantity'] ?></td>
                                <td><?= $animal['health'] ?? 100 ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fahrzeuge -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Fahrzeuge (<?= count($vehicles) ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($vehicles)): ?>
                <p class="text-muted">Keine Fahrzeuge vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fahrzeug</th>
                            <th>Zustand</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr>
                                <td><?= $vehicle['id'] ?></td>
                                <td><?= htmlspecialchars($vehicle['vehicle_name']) ?></td>
                                <td><?= $vehicle['condition'] ?? $vehicle['condition_percent'] ?? 100 ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-edit-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 968px) {
    .admin-edit-grid {
        grid-template-columns: 1fr;
    }
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-actions {
    margin-top: 1.5rem;
}
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.info-item {
    display: flex;
    justify-content: space-between;
}
.info-label {
    color: var(--color-gray-600);
}
.info-value {
    font-weight: 500;
}
.mt-4 {
    margin-top: 1.5rem;
}
.status-empty { background: var(--color-gray-200); }
.status-growing { background: var(--color-warning); color: white; }
.status-ready { background: var(--color-success); color: white; }
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}
</style>
