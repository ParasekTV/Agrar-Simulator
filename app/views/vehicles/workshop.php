<div class="page-header">
    <h1>Werkstatt</h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/vehicles" class="btn btn-outline">Zurück zu Fahrzeugen</a>
    </div>
</div>

<!-- Fahrzeuge in Reparatur -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Fahrzeuge in Reparatur</h3>
    </div>
    <div class="card-body">
        <?php if (empty($vehiclesInWorkshop)): ?>
            <p class="text-muted">Keine Fahrzeuge in der Werkstatt.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fahrzeug</th>
                        <th>Marke</th>
                        <th>Zustand vorher</th>
                        <th>Kosten</th>
                        <th>Fertig um</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehiclesInWorkshop as $vehicle): ?>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['name']) ?></td>
                            <td><?= htmlspecialchars($vehicle['brand_name'] ?? '-') ?></td>
                            <td><?= $vehicle['condition_before'] ?? '-' ?>%</td>
                            <td><?= number_format($vehicle['repair_cost'] ?? 0, 0, ',', '.') ?> T</td>
                            <td>
                                <?php if ($vehicle['workshop_finished_at']): ?>
                                    <?= date('d.m.Y H:i', strtotime($vehicle['workshop_finished_at'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Fahrzeuge die Reparatur brauchen -->
<div class="card mb-4">
    <div class="card-header">
        <h3>Reparatur erforderlich</h3>
    </div>
    <div class="card-body">
        <?php if (empty($vehiclesNeedingRepair)): ?>
            <p class="text-success">Alle Fahrzeuge sind in gutem Zustand!</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fahrzeug</th>
                        <th>Marke</th>
                        <th>Aktueller Zustand</th>
                        <th>Gesch. Kosten</th>
                        <th>Gesch. Dauer</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehiclesNeedingRepair as $vehicle): ?>
                        <?php
                        $repairNeeded = 100 - $vehicle['condition_percent'];
                        $estimatedCost = ($vehicle['price'] * 0.1) * ($repairNeeded / 100);
                        $estimatedHours = max(1, ceil($repairNeeded / 20));
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['name']) ?></td>
                            <td><?= htmlspecialchars($vehicle['brand_name'] ?? '-') ?></td>
                            <td>
                                <span class="<?= $vehicle['condition_percent'] < 30 ? 'text-danger' : ($vehicle['condition_percent'] < 50 ? 'text-warning' : '') ?>">
                                    <?= $vehicle['condition_percent'] ?>%
                                </span>
                            </td>
                            <td><?= number_format($estimatedCost, 0, ',', '.') ?> T</td>
                            <td><?= $estimatedHours ?> Stunde(n)</td>
                            <td>
                                <form action="<?= BASE_URL ?>/vehicles/send-to-workshop" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="farm_vehicle_id" value="<?= $vehicle['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Zur Werkstatt</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Diesel-Statistik -->
<div class="card">
    <div class="card-header">
        <h3>Diesel-Verbrauch (letzte 7 Tage)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($dieselStats)): ?>
            <p class="text-muted">Keine Verbrauchsdaten vorhanden.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Verbrauch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dieselStats as $stat): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($stat['date'])) ?></td>
                            <td><?= number_format($stat['total_liters'], 1, ',', '.') ?> Liter</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
