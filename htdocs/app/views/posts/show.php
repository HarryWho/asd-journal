<article class="post-full">
    <?php if (!empty($isPrivateView)): ?>
        <p class="private-view-banner">This is an unpublished draft, shared via a private link.</p>
    <?php endif; ?>

    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="post-meta no-print"><?= date('F j, Y', strtotime($post['created_at'])) ?></p>

    <?php if (!empty($tags)): ?>
        <div class="tag-list no-print">
            <?php foreach ($tags as $t): ?>
                <a class="tag-chip" href="<?= BASE_URL ?>post/tag/<?= urlencode($t['slug']) ?>">
                    <?= htmlspecialchars($t['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="post-body">
        <?= $post['body'] ?>
    </div>

    <div class="like-widget no-print" data-post-id="<?= (int) $post['id'] ?>">
        <button class="like-btn">♡</button>
        <span class="like-count"><?= (int) $post['like_count'] ?></span>
    </div>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <div class="admin-actions">
            <a href="<?= BASE_URL ?>post/edit/<?= urlencode($post['slug']) ?>?page=<?= $page ?>">Edit</a>
            <form method="POST" action="<?= BASE_URL ?>post/delete/<?= (int) $post['id'] ?>"
                onsubmit="return confirm('Delete this entry?');" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit">Delete</button>
            </form>
            <a href="<?= BASE_URL ?>?page=<?= $page ?>">Return to list</a>
        </div>
    <?php endif; ?>
</article>

<section class="comments">
    <h3><?= count($comments) ?> comment<?= count($comments) === 1 ? '' : 's' ?></h3>

    <?php foreach ($comments as $comment): ?>
        <div class="comment <?= $comment['status'] === 'pending' ? 'comment-pending' : '' ?>">
            <strong><?= htmlspecialchars($comment['username']) ?></strong>
            <span class="comment-date"><?= date('M j, Y', strtotime($comment['created_at'])) ?></span>
            <?php if ($comment['status'] === 'pending'): ?>
                <span class="pending-badge">Awaiting approval — only you can see this</span>
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($comment['body'])) ?></p>
        </div>
    <?php endforeach; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST" action="<?= BASE_URL ?>comment/store" class="comment-form">
            <?= csrf_field() ?>
            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
            <textarea name="body" placeholder="Add a comment..." required></textarea>
            <button type="submit">Post comment</button>
        </form>
        <p class="comment-note">Comments are reviewed before they're shown publicly — yours will appear here right away, but others won't see it until it's approved.</p>
    <?php else: ?>
        <p><a href="<?= BASE_URL ?>auth/register">Subscribe</a> to leave a comment.</p>
    <?php endif; ?>
</section>