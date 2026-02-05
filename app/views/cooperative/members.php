<?php
$roleLabels = [
    'founder' => 'Gründer', 'admin' => 'Administrator', 'fleet_manager' => 'Fuhrparkmanager',
    'field_manager' => 'Feldmanager', 'animal_manager' => 'Tiermanager', 'production_manager' => 'Produktionsleiter',
    'warehouse_manager' => 'Lagerverwaltung', 'treasurer' => 'Kassenwart', 'researcher' => 'Forschungsleiter',
    'member' => 'Mitglied'
];
?>
<div class="cooperative-page">
    <div class="page-header">
        <h1>Mitglieder verwalten</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item active">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= htmlspecialchars($coopDetails['name'] ?? '') ?> - Mitglieder</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Farm</th>
                        <th>Rolle</th>
                        <th>Level</th>
                        <th>Beitragspunkte</th>
                        <?php if ($canManage): ?>
                            <th>Aktionen</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coopDetails['members'] ?? [] as $member): ?>
                        <tr>
                            <td><?= htmlspecialchars($member['farm_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $member['role'] === 'founder' ? 'warning' : ($member['role'] === 'admin' ? 'info' : 'secondary') ?>">
                                    <?= $roleLabels[$member['role']] ?? 'Mitglied' ?>
                                </span>
                            </td>
                            <td><?= $member['level'] ?></td>
                            <td><?= number_format($member['contribution_points']) ?></td>
                            <?php if ($canManage): ?>
                                <td>
                                    <?php if ($member['farm_id'] !== Session::getFarmId() && $member['role'] !== 'founder'): ?>
                                        <div class="member-actions">
                                            <!-- Rolle ändern -->
                                            <form action="<?= BASE_URL ?>/cooperative/assign-role" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="target_farm_id" value="<?= $member['farm_id'] ?>">
                                                <select name="role" class="form-input form-input-sm" onchange="this.form.submit()">
                                                    <option value="">Rolle ändern...</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <?php if ($role['role_key'] !== 'founder'): ?>
                                                            <option value="<?= $role['role_key'] ?>" <?= $member['role'] === $role['role_key'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($role['name']) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                            <!-- Entfernen -->
                                            <form action="<?= BASE_URL ?>/cooperative/kick" method="POST" class="inline-form"
                                                  onsubmit="return confirm('<?= htmlspecialchars($member['farm_name']) ?> wirklich entfernen?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="target_farm_id" value="<?= $member['farm_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Entfernen</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.member-actions { display: flex; gap: 0.5rem; align-items: center; }
.form-input-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
.btn-danger { background: var(--color-danger, #dc3545); color: white; border: none; }
.btn-danger:hover { opacity: 0.9; }
</style>
