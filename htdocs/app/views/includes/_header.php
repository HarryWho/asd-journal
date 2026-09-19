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