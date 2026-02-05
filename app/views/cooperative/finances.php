<div class="cooperative-page">
    <div class="page-header">
        <h1>Finanzen</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item active">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <!-- Kassenstand -->
    <div class="card card-highlight">
        <div class="card-body">
            <div class="finance-overview">
                <div class="finance-stat">
                    <span class="finance-value"><?= number_format($coopDetails['treasury'] ?? 0, 0, ',', '.') ?> T</span>
                    <span class="finance-label">Kassenstand</span>
                </div>
                <?php if ($canManage): ?>
                    <div class="finance-actions">
                        <button class="btn btn-primary" onclick="showWithdrawModal()">Geld entnehmen</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transaktionshistorie -->
    <div class="card">
        <div class="card-header">
            <h3>Transaktionshistorie</h3>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <p class="text-muted">Keine Transaktionen vorhanden.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Typ</th>
                            <th>Betrag</th>
                            <th>Beschreibung</th>
                            <th>Mitglied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $typeLabels = [
                            'donation' => 'Spende',
                            'payout' => 'Auszahlung',
                            'purchase' => 'Kauf',
                            'sale' => 'Verkauf',
                            'reward' => 'Belohnung',
                            'fee' => 'Verwaltung'
                        ];
                        ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($tx['created_at'])) ?></td>
                                <td><span class="badge badge-secondary"><?= $typeLabels[$tx['transaction_type']] ?? $tx['transaction_type'] ?></span></td>
                                <td class="<?= $tx['amount'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $tx['amount'] >= 0 ? '+' : '' ?><?= number_format($tx['amount'], 0, ',', '.') ?> T
                                </td>
                                <td><?= htmlspecialchars($tx['description']) ?></td>
                                <td><?= htmlspecialchars($tx['farm_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Geld entnehmen -->
<?php if ($canManage): ?>
<div class="modal" id="withdraw-modal">
    <div class="modal-backdrop" onclick="closeWithdrawModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Geld entnehmen</h3>
            <button class="modal-close" onclick="closeWithdrawModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/cooperative/withdraw-money" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="withdraw-amount">Betrag (T)</label>
                    <input type="number" name="amount" id="withdraw-amount" class="form-input" min="100" step="100" max="<?= $coopDetails['treasury'] ?? 0 ?>" required>
                </div>
                <div class="form-group">
                    <label for="withdraw-reason">Grund</label>
                    <input type="text" name="reason" id="withdraw-reason" class="form-input" placeholder="z.B. Investition in Fahrzeuge">
                </div>
                <p class="form-help">Verfügbar: <?= number_format($coopDetails['treasury'] ?? 0, 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeWithdrawModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Entnehmen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showWithdrawModal() { document.getElementById('withdraw-modal').classList.add('active'); }
function closeWithdrawModal() { document.getElementById('withdraw-modal').classList.remove('active'); }
</script>
<?php endif; ?>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.finance-overview { display: flex; justify-content: space-between; align-items: center; }
.finance-value { font-size: 2rem; font-weight: 700; color: var(--color-primary); display: block; }
.finance-label { font-size: 0.9rem; color: var(--color-gray-600); }
.text-success { color: var(--color-success); font-weight: 600; }
.text-danger { color: var(--color-danger, #dc3545); font-weight: 600; }
</style>
