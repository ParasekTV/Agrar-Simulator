<div class="cooperative-page">
    <div class="page-header">
        <h1>Fahrzeugverleih</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/board" class="coop-nav-item">Pinnwand</a>
        <a href="<?= BASE_URL ?>/cooperative/vehicles" class="coop-nav-item active">Fahrzeugverleih</a>
        <a href="<?= BASE_URL ?>/cooperative/productions" class="coop-nav-item">Produktionen</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <div class="row">
        <!-- Meine Fahrzeuge zum Verleihen -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Meine Fahrzeuge</h3>
                </div>
                <div class="card-body">
                    <?php
                    $availableVehicles = array_filter($myVehicles, function($v) {
                        return empty($v['lent_to_cooperative_id']);
                    });
                    ?>
                    <?php if (empty($availableVehicles)): ?>
                        <p class="text-muted">Keine Fahrzeuge zum Verleihen verfügbar.</p>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/cooperative/vehicles/lend" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <div class="form-group">
                                <label for="vehicle">Fahrzeug wählen</label>
                                <select name="farm_vehicle_id" id="vehicle" class="form-select" required>
                                    <option value="">Bitte wählen...</option>
                                    <?php foreach ($availableVehicles as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="duration">Dauer (Stunden)</label>
                                <input type="number" name="duration_hours" id="duration" class="form-input"
                                       value="24" min="1" max="168" required>
                            </div>
                            <div class="form-group">
                                <label for="fee">Stundengebühr (T) - optional</label>
                                <input type="number" name="hourly_fee" id="fee" class="form-input"
                                       value="0" min="0" step="0.01">
                            </div>
                            <button type="submit" class="btn btn-primary">Verleihen</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    $lentByMe = array_filter($myVehicles, function($v) use ($membership) {
                        return $v['lent_to_cooperative_id'] == $membership['cooperative_id'];
                    });
                    ?>
                    <?php if (!empty($lentByMe)): ?>
                        <h4 class="mt-4">Von mir verliehen</h4>
                        <div class="vehicle-list">
                            <?php foreach ($lentByMe as $v): ?>
                                <div class="vehicle-item">
                                    <span><?= htmlspecialchars($v['name']) ?></span>
                                    <form action="<?= BASE_URL ?>/cooperative/vehicles/return" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="farm_vehicle_id" value="<?= $v['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">Zurückholen</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Verliehene Fahrzeuge der Genossenschaft -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Geteilte Fahrzeuge</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($lentVehicles)): ?>
                        <p class="text-muted">Keine Fahrzeuge geteilt. Verleihe dein Fahrzeug an die Genossenschaft!</p>
                    <?php else: ?>
                        <div class="vehicle-list">
                            <?php foreach ($lentVehicles as $v): ?>
                                <div class="vehicle-card">
                                    <div class="vehicle-info">
                                        <h4><?= htmlspecialchars($v['vehicle_name']) ?></h4>
                                        <span class="text-muted">Von: <?= htmlspecialchars($v['lender_name']) ?></span>
                                        <?php if ($v['hourly_fee'] > 0): ?>
                                            <span class="fee"><?= number_format($v['hourly_fee'], 2, ',', '.') ?> T/h</span>
                                        <?php else: ?>
                                            <span class="fee free">Kostenlos</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="vehicle-status">
                                        <?php if ($v['loan_status'] === 'borrowed'): ?>
                                            <span class="badge badge-warning">In Nutzung von <?= htmlspecialchars($v['borrower_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Verfügbar</span>
                                            <?php if ($v['farm_id'] !== $this->getFarmId()): ?>
                                                <form action="<?= BASE_URL ?>/cooperative/vehicles/borrow" method="POST" class="inline-form mt-2">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="farm_vehicle_id" value="<?= $v['id'] ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm">Ausleihen</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100, #f3f4f6); padding: 0.25rem; border-radius: 8px; }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; color: var(--color-gray-600, #6b7280); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900, #111827); }
.coop-nav-item.active { background: var(--color-primary); color: white; }

.vehicle-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.vehicle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.vehicle-card {
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.vehicle-info h4 {
    margin: 0 0 0.25rem 0;
}

.vehicle-info .fee {
    display: inline-block;
    margin-top: 0.25rem;
    padding: 0.2rem 0.5rem;
    background: var(--color-warning);
    color: #000;
    border-radius: 4px;
    font-size: 0.85rem;
}

.vehicle-info .fee.free {
    background: var(--color-success);
    color: #fff;
}

.inline-form {
    display: inline-block;
}

.row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.col-md-6 {
    flex: 1;
    min-width: 300px;
}
</style>
