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
    <header class="site-header no-print">
        <a class="site-title" href="<?= BASE_URL ?>">‪<?= htmlspecialchars(SITE_NAME) ?></a>
        <nav>
            <a href="<?= BASE_URL ?>post/archive">Archive</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a href="<?= BASE_URL ?>post/create">New entry</a>
                    <a href="<?= BASE_URL ?>post/moodtracker">Mood tracker</a>
                    <?php
                    require_once __DIR__ . '/../../models/Comment.php';
                    $pendingCount = (new Comment())->countPending();
                    ?>
                    <a href="<?= BASE_URL ?>comment/moderate">
                        Comments<?php if ($pendingCount > 0): ?> <span class="nav-badge"><?= $pendingCount ?></span><?php endif; ?>
                    </a>
                <?php endif; ?>
                <span>Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="<?= BASE_URL ?>auth/logout">Log out</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>auth/login">Log in</a>
                <a href="<?= BASE_URL ?>auth/register">Subscribe</a>
            <?php endif; ?>
        </nav>
    </header>

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