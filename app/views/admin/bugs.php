<div class="admin-page">
    <div class="page-header">
        <h1>Bug-Meldungen</h1>
        <p class="page-description"><?= $total ?> Meldungen insgesamt</p>
    </div>

    <div class="admin-card">
        <?php if (empty($reports)): ?>
            <p class="text-muted text-center py-4">Keine Bug-Meldungen vorhanden.</p>
        <?php else: ?>
            <div class="bugs-list">
                <?php foreach ($reports as $report): ?>
                    <div class="bug-card">
                        <div class="bug-header">
                            <div class="bug-title-row">
                                <span class="bug-id">#<?= $report['id'] ?></span>
                                <h3><?= htmlspecialchars($report['title']) ?></h3>
                                <span class="status-badge status-<?= $report['status'] ?>">
                                    <?php
                                    $statusLabels = [
                                        'open' => 'Offen',
                                        'in_progress' => 'In Bearbeitung',
                                        'resolved' => 'Gelöst',
                                        'closed' => 'Geschlossen'
                                    ];
                                    echo $statusLabels[$report['status']] ?? $report['status'];
                                    ?>
                                </span>
                            </div>
                            <div class="bug-meta">
                                <span class="bug-user">
                                    <strong><?= htmlspecialchars($report['username']) ?></strong>
                                    (<?= htmlspecialchars($report['farm_name']) ?>)
                                </span>
                                <span class="bug-date"><?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="bug-body">
                            <p class="bug-description"><?= nl2br(htmlspecialchars($report['description'])) ?></p>

                            <?php if (!empty($report['admin_reason'])): ?>
                                <div class="admin-reason-display">
                                    <strong>Admin-Begründung:</strong>
                                    <p><?= nl2br(htmlspecialchars($report['admin_reason'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bug-actions">
                            <form action="<?= BASE_URL ?>/admin/bugs/<?= $report['id'] ?>/status" method="POST" class="status-form-extended">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Status ändern:</label>
                                        <select name="status" class="form-select">
                                            <option value="open" <?= $report['status'] === 'open' ? 'selected' : '' ?>>Offen</option>
                                            <option value="in_progress" <?= $report['status'] === 'in_progress' ? 'selected' : '' ?>>In Bearbeitung</option>
                                            <option value="resolved" <?= $report['status'] === 'resolved' ? 'selected' : '' ?>>Gelöst</option>
                                            <option value="closed" <?= $report['status'] === 'closed' ? 'selected' : '' ?>>Geschlossen</option>
                                        </select>
                                    </div>

                                    <div class="form-group form-group-reason">
                                        <label>Begründung (wird an Discord gesendet):</label>
                                        <input type="text" name="reason" class="form-input"
                                               placeholder="Optional: Grund für Statusänderung...">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Speichern</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= BASE_URL ?>/admin/bugs?page=<?= $i ?>"
                           class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.bugs-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.bug-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.bug-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--color-border);
}

.bug-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.bug-id {
    background: var(--color-primary);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.bug-title-row h3 {
    margin: 0;
    flex: 1;
    font-size: 1.1rem;
}

.bug-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--color-text-secondary);
}

.bug-body {
    padding: 1.25rem;
}

.bug-description {
    margin: 0;
    line-height: 1.6;
    color: var(--color-text);
}

.admin-reason-display {
    margin-top: 1rem;
    padding: 0.75rem;
    background: rgba(59, 130, 246, 0.1);
    border-left: 3px solid var(--color-primary);
    border-radius: 4px;
}

.admin-reason-display strong {
    color: var(--color-primary);
    font-size: 0.85rem;
}

.admin-reason-display p {
    margin: 0.25rem 0 0;
}

.bug-actions {
    padding: 1rem 1.25rem;
    background: var(--color-bg);
    border-top: 1px solid var(--color-border);
}

.status-form-extended .form-row {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    flex-wrap: wrap;
}

.status-form-extended .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.status-form-extended .form-group label {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.status-form-extended .form-group-reason {
    flex: 1;
    min-width: 200px;
}

.status-form-extended .form-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 0.9rem;
}

.status-form-extended .form-select {
    padding: 0.5rem 0.75rem;
    min-width: 150px;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-open {
    background: #ef4444;
    color: white;
}

.status-in_progress {
    background: #f59e0b;
    color: white;
}

.status-resolved {
    background: #10b981;
    color: white;
}

.status-closed {
    background: #6b7280;
    color: white;
}

.py-4 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

@media (max-width: 768px) {
    .status-form-extended .form-row {
        flex-direction: column;
        align-items: stretch;
    }

    .status-form-extended .form-row .btn {
        width: 100%;
    }
}
</style>
