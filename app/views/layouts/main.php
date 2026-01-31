<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Landwirtschafts-Simulator') ?> - Farming Simulator</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/farm.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/responsive.css">
</head>
<body>
    <?php include VIEWS_PATH . '/layouts/navigation.php'; ?>

    <main class="main-content">
        <div class="container">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </div>
    </main>

    <?php include VIEWS_PATH . '/layouts/footer.php'; ?>

    <script src="<?= BASE_URL ?>/js/app.js"></script>
    <script src="<?= BASE_URL ?>/js/timers.js"></script>
    <script src="<?= BASE_URL ?>/js/farm.js"></script>
</body>
</html>
