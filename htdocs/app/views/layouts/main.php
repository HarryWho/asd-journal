<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>

<body>
    <?php include_once __DIR__ . '/../includes/_header.php'; ?>

    <main class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?></p>
    </footer>

    <script>
        window.BASE_URL_JS = "<?= BASE_URL ?>";
    </script>
   
    <script src="<?= BASE_URL ?>js/likes.js"></script>
</body>

</html>