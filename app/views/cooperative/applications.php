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
        <h1>Bewerbungen</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/board" class="coop-nav-item">Pinnwand</a>
        <a href="<?= BASE_URL ?>/cooperative/vehicles" class="coop-nav-item">Fahrzeugverleih</a>
        <a href="<?= BASE_URL ?>/cooperative/productions" class="coop-nav-item">Produktionen</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item active">Bewerbungen</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Offene Bewerbungen</h3>
        </div>
        <div class="card-body">
            <?php if (empty($applications)): ?>
                <p class="text-muted">Keine offenen Bewerbungen.</p>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="application-header">
                            <strong><?= htmlspecialchars($app['farm_name']) ?></strong>
                            <span class="text-muted">Level <?= $app['level'] ?> | <?= number_format($app['points']) ?> Punkte</span>
                        </div>
                        <?php if (!empty($app['message'])): ?>
                            <p class="application-message"><?= htmlspecialchars($app['message']) ?></p>
                        <?php endif; ?>
                        <div class="application-meta">
                            <span class="text-muted">Beworben am <?= date('d.m.Y H:i', strtotime($app['applied_at'])) ?></span>
                        </div>
                        <?php if ($canManage): ?>
                            <div class="application-actions">
                                <form action="<?= BASE_URL ?>/cooperative/process-application" method="POST" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-sm btn-primary">Annehmen</button>
                                </form>
                                <form action="<?= BASE_URL ?>/cooperative/process-application" method="POST" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Bewerbung ablehnen?')">Ablehnen</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><em>Du hast keine Berechtigung, Bewerbungen zu bearbeiten.</em></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.application-card { background: var(--color-gray-50); border-radius: var(--radius); padding: 1rem; margin-bottom: 0.75rem; border: 1px solid var(--color-gray-200); }
.application-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.application-message { margin: 0.5rem 0; padding: 0.5rem; background: white; border-radius: var(--radius); font-style: italic; }
.application-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }
</style>
