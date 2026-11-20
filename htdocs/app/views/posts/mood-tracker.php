<div class="post-hero">
    <h1>Mood tracker</h1>
    <p class="mood-tracker-intro">Private to you — tracked from the mood you log on each entry.</p>

    <?php if (empty($history)): ?>
        <p>No mood data yet. Start logging a mood when you write an entry, and it'll show up here.</p>
    <?php else: ?>

        <section class="mood-section">
            <h2>Monthly average</h2>
            <div class="mood-bar-chart">
                <?php foreach ($monthly as $m): ?>
                    <div class="mood-bar-col">
                        <div class="mood-bar" style="height: <?= (int) ($m['average'] * 16) ?>px;" title="<?= $m['average'] ?> / 5"></div>
                        <span class="mood-bar-label"><?= htmlspecialchars($m['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mood-section">
            <h2>Every logged entry</h2>
            <div class="mood-dot-row">
                <?php foreach ($history as $entry): ?>
                    <a href="<?= BASE_URL ?>post/show/<?= urlencode($entry['slug']) ?>"
                        class="mood-dot mood-dot-<?= (int) $entry['mood_level'] ?>"
                        title="<?= htmlspecialchars($entry['title']) ?> — <?= date('M j, Y', strtotime($entry['created_at'])) ?>">
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="mood-legend">
                <?php foreach (\PostController::MOOD_LABELS as $value => $label): ?>
                    <span class="mood-legend-item"><span class="mood-dot mood-dot-<?= $value ?> mood-dot-small"></span> <?= htmlspecialchars($label) ?></span>
                <?php endforeach; ?>
            </p>
        </section>

    <?php endif; ?>
</div>