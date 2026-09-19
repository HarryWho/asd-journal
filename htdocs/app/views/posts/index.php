<?php if (!empty($pinned) && empty($tagLabel) && empty($archiveLabel) && ($page ?? 1) === 1): ?>
    <article class="post-hero">
        <p class="hero-label"><i class="bi bi-pin-angle"></i> Pinned</p>
        <h2><a href="<?= BASE_URL ?>post/show/<?= urlencode($pinned['slug']) ?>">
                <?= htmlspecialchars($pinned['title']) ?>
            </a>
            <?php if ($pinned['status'] === 'draft'): ?>
                <span class="draft-badge">Draft</span>
            <?php endif; ?>
        </h2>
        <p class="post-meta"><?= date('F j, Y', strtotime($pinned['created_at'])) ?></p>

        <?php if (!empty($pinned['tags'])): ?>
            <div class="tag-list">
                <?php foreach ($pinned['tags'] as $t): ?>
                    <a class="tag-chip" href="<?= BASE_URL ?>post/tag/<?= urlencode($t['slug']) ?>">
                        <?= htmlspecialchars($t['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="post-excerpt">
            <?= htmlspecialchars(mb_strimwidth(strip_tags($pinned['body']), 0, 260, '…')) ?>
        </p>

        <div class="like-widget" data-post-id="<?= (int) $pinned['id'] ?>">
            <button class="like-btn">♡</button>
            <span class="like-count"><?= (int) $pinned['like_count'] ?></span>
        </div>

        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <form method="POST" action="<?= BASE_URL ?>post/unpin/<?= (int) $pinned['id'] ?>" class="admin-actions">
                <?= csrf_field() ?>
                <button type="submit" class="link-text-btn">Unpin this entry</button>
            </form>
        <?php endif; ?>
    </article>
<?php endif; ?>

<?php if (!empty($tagLabel)): ?>
    <p class="tag-filter-heading">Showing entries tagged "<?= htmlspecialchars($tagLabel) ?>" — <a href="<?= BASE_URL ?>">view all</a></p>
<?php endif; ?>

<?php if (!empty($archiveLabel)): ?>
    <p class="tag-filter-heading"><?= htmlspecialchars($archiveLabel) ?> — <a href="<?= BASE_URL ?>post/archive">view archive</a></p>
<?php endif; ?>

<div class="post-list">
    <?php if (empty($posts)): ?>
        <p>No entries yet.</p>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <article class="post-card">
            <h2><a href="<?= BASE_URL ?>post/show/<?= urlencode($post['slug']) ?>?page=<?= $page ?? 1 ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
                <?php if ($post['status'] === 'draft'): ?>
                    <span class="draft-badge">Draft</span>
                <?php endif; ?>
            </h2>
            <p class="post-meta">
                <?= date('F j, Y', strtotime($post['created_at'])) ?>
            </p>

            <?php if (!empty($post['tags'])): ?>
                <div class="tag-list">
                    <?php foreach ($post['tags'] as $t): ?>
                        <a class="tag-chip" href="<?= BASE_URL ?>post/tag/<?= urlencode($t['slug']) ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p class="post-excerpt">
                <?= htmlspecialchars(mb_strimwidth(strip_tags($post['body']), 0, 200, '…')) ?>
            </p>

            <div class="like-widget" data-post-id="<?= (int) $post['id'] ?>">
                <button class="like-btn">♡</button>
                <span class="like-count"><?= (int) $post['like_count'] ?></span>
            </div>

            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <form method="POST" action="<?= BASE_URL ?>post/pin/<?= (int) $post['id'] ?>" class="admin-actions">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-text-btn">Pin to top</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
    <nav class="pagination">
        <?php
        if (!empty($tagSlug)) {
            $baseUrl = BASE_URL . 'post/tag/' . urlencode($tagSlug);
        } elseif (!empty($archivePeriod)) {
            $baseUrl = BASE_URL . 'post/archive/' . urlencode($archivePeriod);
        } else {
            $baseUrl = BASE_URL;
        }
        $sep = str_contains($baseUrl, '?') ? '&' : '?';
        ?>
        <?php if ($page > 1): ?>
            <a href="<?= $baseUrl . $sep ?>page=<?= $page - 1 ?>">&larr; Newer</a>
        <?php else: ?>
            <span class="disabled">&larr; Newer</span>
        <?php endif; ?>

        <span class="page-indicator">Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="<?= $baseUrl . $sep ?>page=<?= $page + 1 ?>">Older &rarr;</a>
        <?php else: ?>
            <span class="disabled">Older &rarr;</span>
        <?php endif; ?>
    </nav>
<?php endif; ?>