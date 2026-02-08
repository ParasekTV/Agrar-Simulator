<div class="cooperative-page">
    <div class="page-header">
        <h1>Agrargenossenschaften</h1>
        <?php if (!$membership): ?>
            <button class="btn btn-primary" onclick="showCreateCoopModal()">Genossenschaft gründen</button>
        <?php endif; ?>
    </div>

    <?php if ($membership): ?>
        <!-- Navigation Tabs -->
        <div class="coop-nav">
            <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item active">Übersicht</a>
            <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
            <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
            <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
            <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
            <a href="<?= BASE_URL ?>/cooperative/board" class="coop-nav-item">Pinnwand</a>
            <a href="<?= BASE_URL ?>/cooperative/vehicles" class="coop-nav-item">Fahrzeugverleih</a>
            <a href="<?= BASE_URL ?>/cooperative/productions" class="coop-nav-item">Produktionen</a>
            <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
            <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
        </div>

        <!-- Eigene Genossenschaft -->
        <div class="card card-highlight">
            <div class="card-header">
                <h3>Meine Genossenschaft: <?= htmlspecialchars($membership['cooperative_name']) ?></h3>
                <?php
                $roleLabels = [
                    'founder' => 'Gründer',
                    'admin' => 'Administrator',
                    'fleet_manager' => 'Fuhrparkmanager',
                    'field_manager' => 'Feldmanager',
                    'animal_manager' => 'Tiermanager',
                    'production_manager' => 'Produktionsleiter',
                    'warehouse_manager' => 'Lagerverwaltung',
                    'treasurer' => 'Kassenwart',
                    'researcher' => 'Forschungsleiter',
                    'member' => 'Mitglied'
                ];
                $roleBadge = match($membership['role']) {
                    'founder' => 'warning',
                    'admin' => 'info',
                    default => 'secondary'
                };
                ?>
                <span class="badge badge-<?= $roleBadge ?>">
                    <?= $roleLabels[$membership['role']] ?? 'Mitglied' ?>
                </span>
            </div>
            <div class="card-body">
                <div class="coop-stats">
                    <div class="coop-stat">
                        <span class="stat-value"><?= number_format($membership['treasury'], 0, ',', '.') ?> T</span>
                        <span class="stat-label">Kasse</span>
                    </div>
                    <div class="coop-stat">
                        <span class="stat-value"><?= count($coopDetails['members'] ?? []) ?></span>
                        <span class="stat-label">Mitglieder</span>
                    </div>
                    <div class="coop-stat">
                        <span class="stat-value"><?= number_format($coopDetails['total_points'] ?? 0) ?></span>
                        <span class="stat-label">Gesamtpunkte</span>
                    </div>
                </div>

                <p><?= htmlspecialchars($coopDetails['description'] ?? 'Keine Beschreibung') ?></p>

                <div class="coop-actions">
                    <button class="btn btn-primary" onclick="showDonateModal()">Spenden</button>
                    <?php if ($membership['role'] !== 'founder'): ?>
                        <form action="<?= BASE_URL ?>/cooperative/leave" method="POST" class="inline-form"
                              onsubmit="return confirm('Wirklich verlassen?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-outline">Verlassen</button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Mitglieder -->
                <h4 class="mt-4">Mitglieder</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Rolle</th>
                            <th>Level</th>
                            <th>Beitrag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coopDetails['members'] ?? [] as $member): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['farm_name']) ?></td>
                                <td><span class="badge badge-secondary"><?= $roleLabels[$member['role']] ?? 'Mitglied' ?></span></td>
                                <td><?= $member['level'] ?></td>
                                <td><?= number_format($member['contribution_points']) ?> Punkte</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Geteilte Geräte -->
                <h4 class="mt-4">Geteilte Geräte</h4>
                <?php if (empty($coopDetails['shared_equipment'])): ?>
                    <p class="text-muted">Keine geteilten Geräte.</p>
                <?php else: ?>
                    <div class="shared-equipment">
                        <?php foreach ($coopDetails['shared_equipment'] as $equipment): ?>
                            <div class="equipment-item">
                                <span class="equipment-name"><?= htmlspecialchars($equipment['vehicle_name']) ?></span>
                                <span class="equipment-owner">von <?= htmlspecialchars($equipment['owner_name']) ?></span>
                                <span class="equipment-status <?= $equipment['available'] ? 'available' : 'in-use' ?>">
                                    <?= $equipment['available'] ? 'Verfügbar' : 'Verliehen' ?>
                                </span>
                                <?php if ($equipment['available'] && $equipment['owner_farm_id'] !== Session::getFarmId()): ?>
                                    <form action="<?= BASE_URL ?>/cooperative/borrow-equipment" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="equipment_id" value="<?= $equipment['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Ausleihen</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste aller Genossenschaften -->
        <div class="cooperatives-list">
            <?php if (empty($cooperatives)): ?>
                <div class="empty-state">
                    <span class="empty-icon">&#127968;</span>
                    <h3>Keine Genossenschaften vorhanden</h3>
                    <p>Sei der Erste und gründe eine!</p>
                </div>
            <?php else: ?>
                <?php foreach ($cooperatives as $coop): ?>
                    <div class="coop-card">
                        <div class="coop-card-header">
                            <h4><?= htmlspecialchars($coop['name']) ?></h4>
                            <span class="member-count"><?= $coop['member_count'] ?>/<?= $coop['member_limit'] ?> Mitglieder</span>
                        </div>
                        <p class="coop-description"><?= htmlspecialchars($coop['description'] ?: 'Keine Beschreibung') ?></p>
                        <div class="coop-card-footer">
                            <span class="coop-points"><?= number_format($coop['total_points']) ?> Punkte</span>
                            <?php if ($coop['member_count'] < $coop['member_limit']): ?>
                                <button class="btn btn-primary btn-sm" onclick="showApplyModal(<?= $coop['id'] ?>, '<?= htmlspecialchars($coop['name'], ENT_QUOTES) ?>')">Bewerben</button>
                            <?php else: ?>
                                <span class="badge badge-secondary">Voll</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Genossenschaft gründen -->
<div class="modal" id="create-coop-modal">
    <div class="modal-backdrop" onclick="closeCreateCoopModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Genossenschaft gründen</h3>
            <button class="modal-close" onclick="closeCreateCoopModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/cooperative/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="coop-name">Name</label>
                    <input type="text" name="name" id="coop-name" class="form-input" required minlength="3" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="coop-description">Beschreibung</label>
                    <textarea name="description" id="coop-description" class="form-input" rows="3"></textarea>
                </div>
                <p class="form-help">Gründungskosten: 5.000 T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCreateCoopModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Gründen</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Spenden -->
<div class="modal" id="donate-modal">
    <div class="modal-backdrop" onclick="closeDonateModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>An Genossenschaft spenden</h3>
            <button class="modal-close" onclick="closeDonateModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/cooperative/donate" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="donate-amount">Betrag (T)</label>
                    <input type="number" name="amount" id="donate-amount" class="form-input" min="100" step="100" required>
                </div>
                <p class="form-help">Du erhältst 1 Beitragspunkt pro 10 T.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeDonateModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Spenden</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Bewerben -->
<div class="modal" id="apply-modal">
    <div class="modal-backdrop" onclick="closeApplyModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bewerbung senden</h3>
            <button class="modal-close" onclick="closeApplyModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/cooperative/apply" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="cooperative_id" id="apply-coop-id">
            <div class="modal-body">
                <p>Bewerbung an: <strong id="apply-coop-name"></strong></p>
                <div class="form-group">
                    <label for="apply-message">Nachricht (optional)</label>
                    <textarea name="message" id="apply-message" class="form-input" rows="3" placeholder="Warum möchtest du beitreten?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeApplyModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Bewerben</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateCoopModal() { document.getElementById('create-coop-modal').classList.add('active'); }
function closeCreateCoopModal() { document.getElementById('create-coop-modal').classList.remove('active'); }
function showDonateModal() { document.getElementById('donate-modal').classList.add('active'); }
function closeDonateModal() { document.getElementById('donate-modal').classList.remove('active'); }
function showApplyModal(id, name) {
    document.getElementById('apply-coop-id').value = id;
    document.getElementById('apply-coop-name').textContent = name;
    document.getElementById('apply-modal').classList.add('active');
}
function closeApplyModal() { document.getElementById('apply-modal').classList.remove('active'); }
</script>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.coop-stats { display: flex; gap: 2rem; margin-bottom: 1rem; }
.coop-stat { text-align: center; }
.coop-stat .stat-value { font-size: 1.5rem; font-weight: 700; display: block; color: var(--color-primary); }
.coop-stat .stat-label { font-size: 0.85rem; color: var(--color-gray-600); }
.coop-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
.cooperatives-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.coop-card { background: white; border-radius: var(--radius-lg); padding: 1.25rem; box-shadow: var(--shadow-sm); }
.coop-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
.coop-card-header h4 { margin: 0; }
.member-count { font-size: 0.85rem; color: var(--color-gray-600); }
.coop-description { font-size: 0.9rem; color: var(--color-gray-600); margin-bottom: 1rem; }
.coop-card-footer { display: flex; justify-content: space-between; align-items: center; }
.coop-points { font-weight: 600; color: var(--color-primary); }
.shared-equipment { display: flex; flex-direction: column; gap: 0.5rem; }
.equipment-item { display: flex; align-items: center; gap: 1rem; padding: 0.5rem; background: var(--color-gray-100); border-radius: var(--radius); }
.equipment-name { font-weight: 500; }
.equipment-owner { font-size: 0.85rem; color: var(--color-gray-600); }
.equipment-status { margin-left: auto; font-size: 0.85rem; }
.equipment-status.available { color: var(--color-success); }
.equipment-status.in-use { color: var(--color-warning); }
</style>
