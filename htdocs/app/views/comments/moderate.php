<h1>Comments awaiting approval</h1>

<?php if (empty($pending)): ?>
    <p>Nothing waiting right now.</p>
<?php else: ?>
    <div class="moderation-list">
        <?php foreach ($pending as $comment): ?>
            <div class="moderation-item">
                <p class="post-meta">
                    On <a href="<?= BASE_URL ?>post/show/<?= urlencode($comment['post_slug']) ?>">
                        <?= htmlspecialchars($comment['post_title']) ?>
                    </a>
                    — <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?>
                </p>
                <p><strong><?= htmlspecialchars($comment['username']) ?></strong></p>
                <p><?= nl2br(htmlspecialchars($comment['body'])) ?></p>

                <div class="moderation-actions">
                    <form method="POST" action="<?= BASE_URL ?>comment/approve/<?= (int) $comment['id'] ?>">
    <?= csrf_field() ?>
                        <button type="submit">Approve</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>comment/delete/<?= (int) $comment['id'] ?>"
                          onsubmit="return confirm('Reject and delete this comment?');">
    <?= csrf_field() ?>
                        <button type="submit" class="reject-btn">Reject</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
