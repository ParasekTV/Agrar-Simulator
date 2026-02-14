<div class="page-header">
    <h1>Wettkampf-Arena</h1>
    <p class="text-muted">FSL-inspirierter Genossenschafts-Wettkampf</p>
</div>

<div class="grid grid-2">
    <!-- Herausforderung senden -->
    <div class="card">
        <div class="card-header">
            <h3>Genossenschaft herausfordern</h3>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/arena/challenge" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="form-group">
                    <label for="defender_coop_id">Genossenschaft auswählen</label>
                    <select name="defender_coop_id" id="defender_coop_id" class="form-select" required>
                        <option value="">-- Wähle eine Genossenschaft --</option>
                        <?php foreach ($cooperatives as $coop): ?>
                            <?php if ($coop['id'] !== $membership['cooperative_id']): ?>
                                <option value="<?= $coop['id'] ?>"><?= htmlspecialchars($coop['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Herausfordern</button>
            </form>
        </div>
    </div>

    <!-- Ausstehende Herausforderungen -->
    <div class="card">
        <div class="card-header">
            <h3>Eingehende Herausforderungen</h3>
        </div>
        <div class="card-body">
            <?php if (empty($pendingChallenges)): ?>
                <p class="text-muted">Keine ausstehenden Herausforderungen</p>
            <?php else: ?>
                <?php foreach ($pendingChallenges as $challenge): ?>
                    <div class="challenge-item mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($challenge['challenger_name']) ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($challenge['challenge_sent_at'])) ?>
                                </small>
                            </div>
                            <div>
                                <form action="<?= BASE_URL ?>/arena/accept" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="match_id" value="<?= $challenge['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Annehmen</button>
                                </form>
                                <form action="<?= BASE_URL ?>/arena/decline" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="match_id" value="<?= $challenge['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Ablehnen</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Aktive Matches -->
<?php if (!empty($activeMatches)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3>Aktive Matches</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Herausforderer</th>
                        <th>Verteidiger</th>
                        <th>Status</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeMatches as $match): ?>
                        <tr>
                            <td><?= htmlspecialchars($match['challenger_name']) ?></td>
                            <td><?= htmlspecialchars($match['defender_name']) ?></td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'pending' => '<span class="badge badge-warning">Ausstehend</span>',
                                    'pick_ban' => '<span class="badge badge-info">Pick & Ban</span>',
                                    'ready' => '<span class="badge badge-success">Bereit</span>',
                                    'in_progress' => '<span class="badge badge-primary">Läuft</span>'
                                ];
                                echo $statusLabels[$match['status']] ?? $match['status'];
                                ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/arena/match/<?= $match['id'] ?>" class="btn btn-sm btn-outline">
                                    Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Rangliste -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Top 10 Rangliste</h3>
        <a href="<?= BASE_URL ?>/arena/rankings" class="btn btn-sm btn-outline">Vollständige Rangliste</a>
    </div>
    <div class="card-body">
        <?php if (empty($rankings)): ?>
            <p class="text-muted">Noch keine Rankings vorhanden</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Genossenschaft</th>
                        <th>Matches</th>
                        <th>S/N/U</th>
                        <th>Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $i => $rank): ?>
                        <tr <?= $rank['cooperative_id'] === $membership['cooperative_id'] ? 'class="table-primary"' : '' ?>>
                            <td><strong><?= $i + 1 ?></strong></td>
                            <td><?= htmlspecialchars($rank['cooperative_name']) ?></td>
                            <td><?= $rank['total_matches'] ?></td>
                            <td>
                                <span class="text-success"><?= $rank['wins'] ?></span> /
                                <span class="text-danger"><?= $rank['losses'] ?></span> /
                                <span class="text-muted"><?= $rank['draws'] ?></span>
                            </td>
                            <td><strong><?= number_format($rank['ranking_points']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Spielregeln -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Spielregeln</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h5>1. Herausforderung</h5>
                <p>Eine Genossenschaft fordert eine andere heraus. Die gegnerische Genossenschaft kann annehmen oder ablehnen.</p>
            </div>
            <div class="col-md-4">
                <h5>2. Pick & Ban</h5>
                <p>Beide Teams wählen abwechselnd Fahrzeuge aus und bannen gegnerische Optionen. Je 3 Picks und 2 Bans.</p>
            </div>
            <div class="col-md-4">
                <h5>3. Rollen</h5>
                <p>3 Spieler pro Team mit Rollen: <strong>Ernte-Spezialist</strong>, <strong>Ballen-Produzent</strong>, <strong>Transport</strong>.</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h5>Punktesystem</h5>
                <ul>
                    <li>Weizen ernten erhöht den Multiplikator</li>
                    <li>Ballen produzieren: 10 Punkte × Multiplikator</li>
                    <li>Transport: 15 Punkte × Multiplikator</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5>Gewinnbedingungen</h5>
                <ul>
                    <li>Match-Dauer: 15 Minuten</li>
                    <li>Höchste Punktzahl gewinnt</li>
                    <li>Ranking-Punkte: +25 Sieg, -15 Niederlage, +5 Unentschieden</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.challenge-item {
    border: 1px solid var(--color-gray-200);
}
.d-flex { display: flex; }
.justify-content-between { justify-content: space-between; }
.align-items-center { align-items: center; }
.d-inline { display: inline-block; }
.bg-light { background: var(--color-gray-100); }
.rounded { border-radius: var(--radius); }
.mb-3 { margin-bottom: 1rem; }
.p-3 { padding: 1rem; }
.table-primary { background: var(--color-primary-light) !important; }
</style>
