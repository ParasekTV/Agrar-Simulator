<div class="cooperative-page">
    <div class="page-header">
        <h1>Genossenschaftslager</h1>
    </div>

    <div class="coop-nav">
        <a href="<?= BASE_URL ?>/cooperative" class="coop-nav-item">Übersicht</a>
        <a href="<?= BASE_URL ?>/cooperative/members" class="coop-nav-item">Mitglieder</a>
        <a href="<?= BASE_URL ?>/cooperative/warehouse" class="coop-nav-item active">Lager</a>
        <a href="<?= BASE_URL ?>/cooperative/finances" class="coop-nav-item">Finanzen</a>
        <a href="<?= BASE_URL ?>/cooperative/research" class="coop-nav-item">Forschung</a>
        <a href="<?= BASE_URL ?>/cooperative/board" class="coop-nav-item">Pinnwand</a>
        <a href="<?= BASE_URL ?>/cooperative/vehicles" class="coop-nav-item">Fahrzeugverleih</a>
        <a href="<?= BASE_URL ?>/cooperative/productions" class="coop-nav-item">Produktionen</a>
        <a href="<?= BASE_URL ?>/cooperative/challenges" class="coop-nav-item">Herausforderungen</a>
        <a href="<?= BASE_URL ?>/cooperative/applications" class="coop-nav-item">Bewerbungen</a>
    </div>

    <div class="grid grid-2">
        <!-- Lagerbestand -->
        <div class="card">
            <div class="card-header">
                <h3>Lagerbestand</h3>
            </div>
            <div class="card-body">
                <?php if (empty($warehouse)): ?>
                    <p class="text-muted">Das Lager ist leer.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Kategorie</th>
                                <th>Menge</th>
                                <?php if ($canWithdraw): ?>
                                    <th>Aktion</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($warehouse as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name'] ?? 'Unbekannt') ?></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($item['product_category'] ?? '-') ?></span></td>
                                    <td><?= number_format($item['quantity']) ?></td>
                                    <?php if ($canWithdraw): ?>
                                        <td>
                                            <form action="<?= BASE_URL ?>/cooperative/withdraw" method="POST" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                                <div class="input-group-sm">
                                                    <input type="number" name="quantity" min="1" max="<?= $item['quantity'] ?>" value="1" class="form-input form-input-sm">
                                                    <button type="submit" class="btn btn-sm btn-outline">Entnehmen</button>
                                                </div>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Einlagern -->
        <div class="card">
            <div class="card-header">
                <h3>Einlagern</h3>
            </div>
            <div class="card-body">
                <?php if (empty($farmInventory)): ?>
                    <p class="text-muted">Dein Inventar ist leer.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Menge</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($farmInventory as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name'] ?? 'Unbekannt') ?></td>
                                    <td><?= number_format($item['quantity']) ?></td>
                                    <td>
                                        <form action="<?= BASE_URL ?>/cooperative/deposit" method="POST" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                            <div class="input-group-sm">
                                                <input type="number" name="quantity" min="1" max="<?= $item['quantity'] ?>" value="1" class="form-input form-input-sm">
                                                <button type="submit" class="btn btn-sm btn-primary">Einlagern</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.coop-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-gray-100); padding: 0.25rem; border-radius: var(--radius-lg); }
.coop-nav-item { padding: 0.5rem 1rem; border-radius: var(--radius); text-decoration: none; color: var(--color-gray-600); font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.coop-nav-item:hover { background: white; color: var(--color-gray-900); }
.coop-nav-item.active { background: var(--color-primary); color: white; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
.input-group-sm { display: flex; gap: 0.25rem; align-items: center; }
.form-input-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; width: 70px; }
</style>
