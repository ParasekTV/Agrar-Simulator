<div class="admin-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline">&larr; Zurück</a>
        <h1>Benutzer #<?= $user['id'] ?> bearbeiten</h1>
    </div>

    <div class="admin-edit-grid">
        <!-- Benutzer-Formular -->
        <div class="card">
            <div class="card-header">
                <h3>Benutzer-Daten</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="username">Benutzername</label>
                        <input type="text" id="username" name="username" class="form-control"
                               value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Neues Passwort (leer lassen = nicht ändern)</label>
                        <input type="password" id="new_password" name="new_password" class="form-control"
                               placeholder="Neues Passwort eingeben...">
                    </div>

                    <div class="form-row checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                            Aktiv
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
                            Administrator
                        </label>
                    </div>

                    <hr>

                    <h4>Farm-Daten</h4>

                    <?php if ($user['farm_id']): ?>
                        <div class="form-group">
                            <label for="farm_name">Farm-Name</label>
                            <input type="text" id="farm_name" name="farm_name" class="form-control"
                                   value="<?= htmlspecialchars($user['farm_name']) ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="money">Geld (T)</label>
                                <input type="number" id="money" name="money" class="form-control"
                                       value="<?= $user['money'] ?>" step="1">
                            </div>
                            <div class="form-group">
                                <label for="points">Punkte</label>
                                <input type="number" id="points" name="points" class="form-control"
                                       value="<?= $user['points'] ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="level">Level</label>
                                <input type="number" id="level" name="level" class="form-control"
                                       value="<?= $user['level'] ?>" min="1">
                            </div>
                            <div class="form-group">
                                <label for="experience">Erfahrung</label>
                                <input type="number" id="experience" name="experience" class="form-control"
                                       value="<?= $user['experience'] ?>">
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Dieser Benutzer hat noch keine Farm.</p>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aktionen -->
        <div class="card">
            <div class="card-header">
                <h3>Aktionen</h3>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Registriert:</span>
                        <span class="info-value"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Letzter Login:</span>
                        <span class="info-value">
                            <?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Nie' ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Verifiziert:</span>
                        <span class="info-value">
                            <?php if ($user['is_verified']): ?>
                                <span class="status-badge status-verified">Ja</span>
                            <?php else: ?>
                                <span class="status-badge status-unverified">Nein</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <?php if (!$user['is_verified']): ?>
                    <hr>

                    <div class="verification-zone">
                        <h4>E-Mail-Verifizierung</h4>
                        <p class="verification-hint">Der Benutzer hat seine E-Mail-Adresse noch nicht bestätigt.</p>
                        <form action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/verify" method="POST"
                              onsubmit="return confirm('Benutzer manuell verifizieren?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-success btn-block">
                                Manuell verifizieren
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <hr>

                <div class="danger-zone">
                    <h4>Gefahrenzone</h4>
                    <form action="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/delete" method="POST"
                          onsubmit="return confirm('Benutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden!')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-danger btn-block">
                            Benutzer löschen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-edit-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 968px) {
    .admin-edit-grid {
        grid-template-columns: 1fr;
    }
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-row.checkboxes {
    display: flex;
    gap: 2rem;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}
.form-actions {
    margin-top: 1.5rem;
}
hr {
    margin: 1.5rem 0;
    border: none;
    border-top: 1px solid var(--color-gray-200);
}
.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.info-item {
    display: flex;
    justify-content: space-between;
}
.info-label {
    color: var(--color-gray-600);
}
.info-value {
    font-weight: 500;
}
.danger-zone {
    background: #fff5f5;
    border: 1px solid var(--color-danger);
    border-radius: var(--radius);
    padding: 1rem;
}
.danger-zone h4 {
    color: var(--color-danger);
    margin-bottom: 1rem;
}
.verification-zone {
    background: #d4edda;
    border: 1px solid var(--color-success);
    border-radius: var(--radius);
    padding: 1rem;
    margin-bottom: 1rem;
}
.verification-zone h4 {
    color: var(--color-success);
    margin-bottom: 0.5rem;
}
.verification-hint {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
}
.status-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-verified {
    background: var(--color-success);
    color: white;
}
.status-unverified {
    background: var(--color-warning);
    color: #856404;
}
</style>
