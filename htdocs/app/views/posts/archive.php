<div class="post-hero">
    <h1>Archive</h1>

    <?php if (empty($byYear)): ?>
        <p>No entries yet.</p>
    <?php else: ?>
        <div class="archive-list">
            <?php foreach ($byYear as $year => $months): ?>
                <section class="archive-year">
                    <h2><?= htmlspecialchars($year) ?></h2>
                    <ul class="archive-months">
                        <?php foreach ($months as $m): ?>
                            <li>
                                <a href="<?= BASE_URL ?>post/archive/<?= urlencode($m['period']) ?>">
                                    <?= htmlspecialchars($m['label']) ?>
                                </a>
                                <span class="archive-count"><?= $m['count'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>