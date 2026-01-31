<header class="header">
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="<?= BASE_URL ?>/dashboard" class="logo">
                <span class="logo-icon">&#127806;</span>
                <span class="logo-text">Agrar Simulator</span>
            </a>
        </div>

        <?php if (isset($currentFarm) && $currentFarm): ?>
            <div class="navbar-stats">
                <div class="stat-item">
                    <span class="stat-icon">&#128176;</span>
                    <span class="stat-value" id="farm-money"><?= number_format($currentFarm['money'], 0, ',', '.') ?> T</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">&#11088;</span>
                    <span class="stat-value" id="farm-points"><?= number_format($currentFarm['points']) ?> Punkte</span>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">&#127942;</span>
                    <span class="stat-value" id="farm-level">Level <?= $currentFarm['level'] ?></span>
                </div>
            </div>

            <button class="navbar-toggle" id="navbar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="navbar-menu" id="navbar-menu">
                <li><a href="<?= BASE_URL ?>/dashboard" class="nav-link">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/fields" class="nav-link">Felder</a></li>
                <li><a href="<?= BASE_URL ?>/animals" class="nav-link">Tiere</a></li>
                <li><a href="<?= BASE_URL ?>/vehicles" class="nav-link">Fahrzeuge</a></li>
                <li><a href="<?= BASE_URL ?>/research" class="nav-link">Forschung</a></li>
                <li><a href="<?= BASE_URL ?>/market" class="nav-link">Marktplatz</a></li>
                <li><a href="<?= BASE_URL ?>/cooperative" class="nav-link">Genossenschaft</a></li>
                <li><a href="<?= BASE_URL ?>/news" class="nav-link">Zeitung</a></li>
                <li><a href="<?= BASE_URL ?>/rankings" class="nav-link">Rangliste</a></li>
                <?php if (isset($currentUser) && $currentUser['is_admin']): ?>
                    <li><a href="<?= BASE_URL ?>/admin" class="nav-link nav-link-admin">Admin</a></li>
                <?php endif; ?>
                <li class="nav-divider"></li>
                <li>
                    <a href="<?= BASE_URL ?>/logout" class="nav-link nav-link-logout">
                        Abmelden (<?= htmlspecialchars($currentUser['username'] ?? '') ?>)
                    </a>
                </li>
            </ul>
        <?php endif; ?>
    </nav>
</header>
