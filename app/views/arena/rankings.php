<div class="page-header">
    <h1>Arena-Rangliste</h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/arena" class="btn btn-outline">Zurück zur Arena</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($rankings)): ?>
            <p class="text-muted">Noch keine Rankings vorhanden. Starte das erste Match!</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Genossenschaft</th>
                        <th>Matches</th>
                        <th>Siege</th>
                        <th>Niederlagen</th>
                        <th>Unentschieden</th>
                        <th>Siegquote</th>
                        <th>Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $i => $rank): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php if ($i === 0): ?>
                                        <span style="color: gold;">&#127942;</span>
                                    <?php elseif ($i === 1): ?>
                                        <span style="color: silver;">&#129352;</span>
                                    <?php elseif ($i === 2): ?>
                                        <span style="color: #cd7f32;">&#129353;</span>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </strong>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/cooperative/<?= $rank['cooperative_id'] ?>">
                                    <?= htmlspecialchars($rank['cooperative_name']) ?>
                                </a>
                            </td>
                            <td><?= $rank['total_matches'] ?></td>
                            <td class="text-success"><?= $rank['wins'] ?></td>
                            <td class="text-danger"><?= $rank['losses'] ?></td>
                            <td class="text-muted"><?= $rank['draws'] ?></td>
                            <td>
                                <?php
                                $winRate = $rank['total_matches'] > 0
                                    ? round(($rank['wins'] / $rank['total_matches']) * 100, 1)
                                    : 0;
                                ?>
                                <?= $winRate ?>%
                            </td>
                            <td><strong><?= number_format($rank['ranking_points']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
