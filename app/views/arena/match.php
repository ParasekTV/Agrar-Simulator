<div class="page-header">
    <h1>Match: <?= htmlspecialchars($match['challenger_name']) ?> vs <?= htmlspecialchars($match['defender_name']) ?></h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/arena" class="btn btn-outline">Zurück zur Arena</a>
    </div>
</div>

<!-- Match Status -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Match-Status</h3>
    </div>
    <div class="card-body">
        <div class="match-status-bar">
            <?php
            $statusLabels = [
                'pending' => 'Ausstehend',
                'pick_ban' => 'Pick & Ban',
                'ready' => 'Bereit',
                'in_progress' => 'Läuft',
                'finished' => 'Beendet'
            ];
            $statusClasses = [
                'pending' => 'badge-warning',
                'pick_ban' => 'badge-info',
                'ready' => 'badge-success',
                'in_progress' => 'badge-primary',
                'finished' => 'badge-secondary'
            ];
            ?>
            <span class="badge <?= $statusClasses[$match['status']] ?? 'badge-secondary' ?>">
                <?= $statusLabels[$match['status']] ?? $match['status'] ?>
            </span>
        </div>

        <?php if ($match['status'] === 'finished'): ?>
            <div class="match-result mt-3">
                <div class="row text-center">
                    <div class="col-5">
                        <h4><?= htmlspecialchars($match['challenger_name']) ?></h4>
                        <p class="score"><?= number_format($match['challenger_score']) ?></p>
                    </div>
                    <div class="col-2">
                        <h4>vs</h4>
                    </div>
                    <div class="col-5">
                        <h4><?= htmlspecialchars($match['defender_name']) ?></h4>
                        <p class="score"><?= number_format($match['defender_score']) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($match['status'] === 'pick_ban'): ?>
<!-- Pick & Ban Phase -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Pick & Ban Phase</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Challenger Picks -->
            <div class="col-md-6">
                <h5><?= htmlspecialchars($match['challenger_name']) ?></h5>
                <p><strong>Picks:</strong></p>
                <ul>
                    <?php foreach ($pickBanState['challenger_picks'] as $pick): ?>
                        <li class="text-success"><?= htmlspecialchars($pick['vehicle_name']) ?></li>
                    <?php endforeach; ?>
                    <?php for ($i = count($pickBanState['challenger_picks']); $i < 3; $i++): ?>
                        <li class="text-muted">-- offen --</li>
                    <?php endfor; ?>
                </ul>
                <p><strong>Bans:</strong></p>
                <ul>
                    <?php foreach ($pickBanState['challenger_bans'] as $ban): ?>
                        <li class="text-danger"><?= htmlspecialchars($ban['vehicle_name']) ?></li>
                    <?php endforeach; ?>
                    <?php for ($i = count($pickBanState['challenger_bans']); $i < 2; $i++): ?>
                        <li class="text-muted">-- offen --</li>
                    <?php endfor; ?>
                </ul>
            </div>

            <!-- Defender Picks -->
            <div class="col-md-6">
                <h5><?= htmlspecialchars($match['defender_name']) ?></h5>
                <p><strong>Picks:</strong></p>
                <ul>
                    <?php foreach ($pickBanState['defender_picks'] as $pick): ?>
                        <li class="text-success"><?= htmlspecialchars($pick['vehicle_name']) ?></li>
                    <?php endforeach; ?>
                    <?php for ($i = count($pickBanState['defender_picks']); $i < 3; $i++): ?>
                        <li class="text-muted">-- offen --</li>
                    <?php endfor; ?>
                </ul>
                <p><strong>Bans:</strong></p>
                <ul>
                    <?php foreach ($pickBanState['defender_bans'] as $ban): ?>
                        <li class="text-danger"><?= htmlspecialchars($ban['vehicle_name']) ?></li>
                    <?php endforeach; ?>
                    <?php for ($i = count($pickBanState['defender_bans']); $i < 2; $i++): ?>
                        <li class="text-muted">-- offen --</li>
                    <?php endfor; ?>
                </ul>
            </div>
        </div>

        <?php if (!empty($availableVehicles)): ?>
            <hr>
            <h5>Verfügbare Fahrzeuge</h5>
            <div class="row">
                <?php foreach ($availableVehicles as $vehicle): ?>
                    <div class="col-md-4 mb-2">
                        <div class="card">
                            <div class="card-body p-2">
                                <strong><?= htmlspecialchars($vehicle['name']) ?></strong>
                                <br>
                                <small><?= $vehicle['power_hp'] ?> PS - <?= htmlspecialchars($vehicle['brand_name'] ?? '') ?></small>
                                <div class="mt-2">
                                    <form action="<?= BASE_URL ?>/arena/pick" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                                        <input type="hidden" name="vehicle_id" value="<?= $vehicle['vehicle_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Pick</button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/arena/ban" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                                        <input type="hidden" name="vehicle_id" value="<?= $vehicle['vehicle_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Ban</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Teilnehmer -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Teilnehmer</h3>
    </div>
    <div class="card-body">
        <?php if (empty($participants)): ?>
            <p class="text-muted">Noch keine Teilnehmer zugewiesen.</p>
        <?php else: ?>
            <div class="row">
                <?php
                $roleNames = [
                    'harvest_specialist' => 'Ernte-Spezialist',
                    'bale_producer' => 'Ballen-Produzent',
                    'transport' => 'Transport'
                ];
                ?>
                <?php foreach ($participants as $participant): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5><?= htmlspecialchars($participant['farm_name']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($participant['username']) ?></p>
                                <p>
                                    <strong>Rolle:</strong>
                                    <?= $roleNames[$participant['role']] ?? 'Keine' ?>
                                </p>
                                <?php if ($participant['is_ready']): ?>
                                    <span class="badge badge-success">Bereit</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nicht bereit</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($match['status'] === 'pick_ban' || $match['status'] === 'ready'): ?>
            <hr>
            <h5>Rolle wählen</h5>
            <form action="<?= BASE_URL ?>/arena/assign-role" method="POST" class="form-inline mb-3">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                <select name="role" class="form-select mr-2" required>
                    <option value="">Rolle wählen...</option>
                    <option value="harvest_specialist">Ernte-Spezialist</option>
                    <option value="bale_producer">Ballen-Produzent</option>
                    <option value="transport">Transport</option>
                </select>
                <button type="submit" class="btn btn-primary">Rolle zuweisen</button>
            </form>

            <form action="<?= BASE_URL ?>/arena/ready" method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                <button type="submit" class="btn btn-success">Bereit melden</button>
            </form>
        <?php endif; ?>

        <?php if ($match['status'] === 'ready'): ?>
            <form action="<?= BASE_URL ?>/arena/start" method="POST" class="d-inline ml-2">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                <button type="submit" class="btn btn-primary">Match starten</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.score { font-size: 2rem; font-weight: bold; }
.form-inline { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.mr-2 { margin-right: 0.5rem; }
.ml-2 { margin-left: 0.5rem; }
.d-inline { display: inline-block; }
</style>
