<div class="profile-page">
    <div class="profile-header">
        <div class="profile-picture-container">
            <?php if (!empty($player['profile_picture'])): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($player['profile_picture']) ?>"
                     alt="<?= htmlspecialchars($player['username']) ?>" class="profile-picture-xl">
            <?php else: ?>
                <div class="avatar-placeholder-xl"><?= strtoupper(substr($player['username'], 0, 1)) ?></div>
            <?php endif; ?>

            <?php if ($player['vacation_mode']): ?>
                <span class="status-badge badge-warning">Im Urlaub</span>
            <?php elseif ($player['is_online']): ?>
                <span class="status-badge badge-success">Online</span>
            <?php else: ?>
                <span class="status-badge badge-secondary">Offline</span>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h1><?= htmlspecialchars($player['username']) ?></h1>
            <p class="farm-name"><?= htmlspecialchars($player['farm_name']) ?></p>
            <div class="profile-badges">
                <span class="level-badge">Level <?= $player['level'] ?></span>
                <span class="member-since">Mitglied seit <?= date('d.m.Y', strtotime($player['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <div class="profile-stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128176;</div>
            <div class="stat-value"><?= number_format($player['money'], 0, ',', '.') ?> T</div>
            <div class="stat-label">Vermögen</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127942;</div>
            <div class="stat-value"><?= number_format($player['points']) ?></div>
            <div class="stat-label">Punkte</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127806;</div>
            <div class="stat-value"><?= $stats['fields'] ?></div>
            <div class="stat-label">Felder</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128004;</div>
            <div class="stat-value"><?= $stats['animals'] ?></div>
            <div class="stat-label">Tiere</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127981;</div>
            <div class="stat-value"><?= $stats['productions'] ?></div>
            <div class="stat-label">Produktionen</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128666;</div>
            <div class="stat-value"><?= $stats['vehicles'] ?></div>
            <div class="stat-label">Fahrzeuge</div>
        </div>
    </div>

    <?php if (!empty($player['cooperative_name'])): ?>
        <div class="profile-section">
            <h3>Genossenschaft</h3>
            <div class="cooperative-info">
                <a href="<?= BASE_URL ?>/cooperative/<?= $player['cooperative_id'] ?>" class="coop-link">
                    <span class="coop-icon">&#127793;</span>
                    <span class="coop-name"><?= htmlspecialchars($player['cooperative_name']) ?></span>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="profile-footer">
        <a href="<?= BASE_URL ?>/rankings" class="btn btn-outline">&larr; Zurück zur Rangliste</a>
    </div>
</div>

<style>
.profile-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 2rem;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.profile-picture-container {
    position: relative;
    flex-shrink: 0;
}

.profile-picture-xl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--color-primary);
}

.avatar-placeholder-xl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, #2563eb) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
    border: 4px solid var(--color-primary);
}

.status-badge {
    position: absolute;
    bottom: 5px;
    right: 5px;
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 12px;
    white-space: nowrap;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-warning {
    background: var(--color-warning);
    color: #000;
}

.badge-secondary {
    background: var(--color-gray-400);
    color: white;
}

.profile-info h1 {
    margin: 0 0 0.25rem;
    font-size: 1.75rem;
}

.farm-name {
    color: var(--color-text-secondary);
    margin: 0 0 0.75rem;
    font-size: 1rem;
}

.profile-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.level-badge {
    background: var(--color-primary);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.member-since {
    color: var(--color-text-secondary);
    font-size: 0.85rem;
    padding: 0.35rem 0;
}

.profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 1.25rem;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.profile-section {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.profile-section h3 {
    margin: 0 0 1rem;
    font-size: 1rem;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.coop-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
    text-decoration: none;
    color: var(--color-text);
    transition: background 0.2s;
}

.coop-link:hover {
    background: var(--color-primary);
    color: white;
}

.coop-icon {
    font-size: 1.5rem;
}

.coop-name {
    font-weight: 600;
    font-size: 1.1rem;
}

.profile-footer {
    text-align: center;
    padding-top: 1rem;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-text);
}

.btn-outline:hover {
    background: var(--color-bg-secondary);
}

@media (max-width: 600px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .profile-badges {
        justify-content: center;
    }

    .profile-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
