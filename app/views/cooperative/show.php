<div class="cooperative-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/cooperative" class="btn btn-outline">&larr; Zurück</a>
        <h1><?= htmlspecialchars($cooperative['name']) ?></h1>
    </div>

    <div class="coop-layout">
        <!-- Hauptinfo -->
        <div class="coop-main-card">
            <div class="coop-banner">
                <div class="coop-icon">&#127968;</div>
                <div class="coop-title-info">
                    <h2><?= htmlspecialchars($cooperative['name']) ?></h2>
                    <span class="coop-level">Level <?= $cooperative['level'] ?></span>
                </div>
            </div>

            <?php if ($cooperative['description']): ?>
                <div class="coop-description">
                    <p><?= nl2br(htmlspecialchars($cooperative['description'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="coop-stats-grid">
                <div class="coop-stat">
                    <span class="stat-value"><?= number_format($cooperative['total_points']) ?></span>
                    <span class="stat-label">Gesamtpunkte</span>
                </div>
                <div class="coop-stat">
                    <span class="stat-value"><?= $cooperative['member_count'] ?>/<?= $cooperative['member_limit'] ?></span>
                    <span class="stat-label">Mitglieder</span>
                </div>
                <div class="coop-stat">
                    <span class="stat-value"><?= number_format($cooperative['treasury'], 0, ',', '.') ?> T</span>
                    <span class="stat-label">Kasse</span>
                </div>
                <div class="coop-stat">
                    <span class="stat-value"><?= date('d.m.Y', strtotime($cooperative['created_at'])) ?></span>
                    <span class="stat-label">Gegründet</span>
                </div>
            </div>

            <?php if ($isLeader): ?>
                <div class="coop-leader-actions">
                    <h4>Leiter-Aktionen</h4>
                    <div class="action-buttons">
                        <button class="btn btn-outline" onclick="openEditModal()">Beschreibung bearbeiten</button>
                        <form action="<?= BASE_URL ?>/cooperative/dissolve" method="POST" class="inline-form"
                              onsubmit="return confirm('Genossenschaft wirklich auflösen? Dies kann nicht rückgängig gemacht werden!')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-danger">Genossenschaft auflösen</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mitgliederliste -->
        <div class="card">
            <div class="card-header">
                <h3>Mitglieder</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Level</th>
                            <th>Punkte</th>
                            <th>Rolle</th>
                            <?php if ($isLeader): ?>
                                <th>Aktionen</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <?php $memberIsLeader = in_array($member['role'] ?? '', ['founder', 'leader', 'co_leader']); ?>
                            <tr class="<?= $member['farm_id'] === Session::getFarmId() ? 'highlight-row' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($member['farm_name']) ?></strong>
                                    <?php if ($memberIsLeader): ?>
                                        <span class="leader-badge">&#128081; Leiter</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $member['level'] ?></td>
                                <td><?= number_format($member['points']) ?></td>
                                <td>
                                    <?= $memberIsLeader ? 'Leiter' : 'Mitglied' ?>
                                    <br>
                                    <small class="text-muted">Beitritt: <?= date('d.m.Y', strtotime($member['joined_at'] ?? $member['created_at'] ?? 'now')) ?></small>
                                </td>
                                <?php if ($isLeader && !$memberIsLeader): ?>
                                    <td>
                                        <form action="<?= BASE_URL ?>/cooperative/kick" method="POST" class="inline-form"
                                              onsubmit="return confirm('Mitglied wirklich entfernen?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="farm_id" value="<?= $member['farm_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Entfernen</button>
                                        </form>
                                    </td>
                                <?php elseif ($isLeader): ?>
                                    <td>-</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Geteilte Ausrüstung -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Geteilte Ausrüstung</h3>
        </div>
        <div class="card-body">
            <?php if (empty($sharedEquipment)): ?>
                <div class="empty-state">
                    <p class="text-muted">Keine geteilte Ausrüstung verfügbar.</p>
                </div>
            <?php else: ?>
                <div class="equipment-grid">
                    <?php foreach ($sharedEquipment as $equipment): ?>
                        <div class="equipment-card <?= $equipment['is_borrowed'] ? 'borrowed' : 'available' ?>">
                            <div class="equipment-icon">&#128663;</div>
                            <div class="equipment-info">
                                <h4><?= htmlspecialchars($equipment['vehicle_name']) ?></h4>
                                <p class="text-muted">Von: <?= htmlspecialchars($equipment['owner_name']) ?></p>
                                <div class="equipment-stats">
                                    <span>Zustand: <?= $equipment['condition'] ?>%</span>
                                </div>
                            </div>
                            <div class="equipment-actions">
                                <?php if ($equipment['is_borrowed']): ?>
                                    <?php if ($equipment['borrower_id'] === Session::getFarmId()): ?>
                                        <form action="<?= BASE_URL ?>/cooperative/return-equipment" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="vehicle_id" value="<?= $equipment['vehicle_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Zurückgeben</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="status-badge borrowed">Ausgeliehen</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($equipment['owner_id'] !== Session::getFarmId()): ?>
                                        <form action="<?= BASE_URL ?>/cooperative/borrow-equipment" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="vehicle_id" value="<?= $equipment['vehicle_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Ausleihen</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= BASE_URL ?>/cooperative/unshare-equipment" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="vehicle_id" value="<?= $equipment['vehicle_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline">Nicht mehr teilen</button>
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

    <!-- Spenden -->
    <?php if ($isMember): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>An Genossenschaft spenden</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/cooperative/donate" method="POST" class="donate-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Betrag (T)</label>
                            <input type="number" name="amount" id="amount" min="100" step="100"
                                   class="form-control" placeholder="1000" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Spenden</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<?php if ($isLeader): ?>
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Beschreibung bearbeiten</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/cooperative/update" method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="form-group">
                    <label for="description">Beschreibung</label>
                    <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($cooperative['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Speichern</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}
</script>
<?php endif; ?>

<style>
.coop-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 968px) {
    .coop-layout {
        grid-template-columns: 1fr;
    }
}
.coop-main-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.coop-banner {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
    color: white;
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.coop-icon {
    font-size: 4rem;
}
.coop-title-info h2 {
    margin: 0 0 0.5rem;
    color: white;
}
.coop-level {
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
}
.coop-description {
    padding: 1.5rem;
    border-bottom: 1px solid var(--color-gray-200);
}
.coop-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: var(--color-gray-200);
}
.coop-stat {
    background: white;
    padding: 1rem;
    text-align: center;
}
.coop-stat .stat-value {
    display: block;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-primary);
}
.coop-stat .stat-label {
    font-size: 0.85rem;
    color: var(--color-gray-600);
}
.coop-leader-actions {
    padding: 1.5rem;
    background: var(--color-gray-50);
}
.coop-leader-actions h4 {
    margin-bottom: 1rem;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.inline-form {
    display: inline;
}
.leader-badge {
    font-size: 0.8rem;
    color: var(--color-warning);
}
.highlight-row {
    background: var(--color-primary-light);
    color: white;
}
.highlight-row .text-muted {
    color: rgba(255,255,255,0.8);
}
.equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.equipment-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-gray-50);
    border-radius: var(--radius);
    border: 2px solid transparent;
}
.equipment-card.available {
    border-color: var(--color-success);
}
.equipment-card.borrowed {
    border-color: var(--color-warning);
    opacity: 0.8;
}
.equipment-icon {
    font-size: 2rem;
}
.equipment-info {
    flex: 1;
}
.equipment-info h4 {
    margin: 0 0 0.25rem;
}
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}
.status-badge.borrowed {
    background: var(--color-warning);
    color: white;
}
.donate-form .form-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}
.donate-form .form-group {
    flex: 1;
    margin: 0;
}
.mt-4 {
    margin-top: 1.5rem;
}
</style>
