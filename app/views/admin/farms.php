<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin" class="btn btn-outline">&larr; Zurueck</a>
        <h1>Hoefe verwalten</h1>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= number_format($total) ?> Hoefe</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Farm-Name</th>
                        <th>Besitzer</th>
                        <th>Level</th>
                        <th>Punkte</th>
                        <th>Geld</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($farms as $farm): ?>
                        <tr>
                            <td><?= $farm['id'] ?></td>
                            <td><strong><?= htmlspecialchars($farm['farm_name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($farm['username']) ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($farm['email']) ?></small>
                            </td>
                            <td><?= $farm['level'] ?></td>
                            <td><?= number_format($farm['points']) ?></td>
                            <td><?= number_format($farm['money'], 0, ',', '.') ?> T</td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/farms/<?= $farm['id'] ?>" class="btn btn-sm btn-outline">Bearbeiten</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination mt-4">
            <?php if ($page > 1): ?>
                <a href="<?= BASE_URL ?>/admin/farms?page=<?= $page - 1 ?>" class="pagination-link">&laquo;</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="<?= BASE_URL ?>/admin/farms?page=<?= $i ?>"
                   class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= BASE_URL ?>/admin/farms?page=<?= $page + 1 ?>" class="pagination-link">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.mt-4 {
    margin-top: 1.5rem;
}
</style>
