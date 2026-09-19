<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
<h1>Edit entry</h1>

<?php if ($post['status'] === 'draft'): ?>
    <div class="private-link-box">
        <?php if ($activeLink): ?>
            <strong>Private link</strong> — active until <span id="expiry-time" data-expires="<?= htmlspecialchars($activeLink['expires_at']) ?>"><?= date('g:i A', strtotime($activeLink['expires_at'])) ?></span>
            (<span id="countdown"></span>)
            <div class="private-link-row">
                <input type="text" readonly value="<?= htmlspecialchars($activeLink['url']) ?>" id="private-link-input">
                <button type="button" id="copy-link-btn">Copy</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>post/generatelink/<?= (int) $post['id'] ?>" class="regen-form">
                <?= csrf_field() ?>
                <input type="hidden" name="minutes" value="30">
                <button type="submit" class="link-text-btn">Generate a new link instead (invalidates this one)</button>
            </form>
        <?php else: ?>
            <strong>Private link</strong> — none active right now.
            <form method="POST" action="<?= BASE_URL ?>post/generatelink/<?= (int) $post['id'] ?>" class="regen-form">
                <?= csrf_field() ?>
                <input type="hidden" name="minutes" value="30">
                <button type="submit">Generate a link (expires in 30 min)</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="post-card">
    <form method="POST" action="<?= BASE_URL ?>post/update/<?= (int) $post['id'] ?>" class="post-form" id="post-form">
        <?= csrf_field() ?>
        <input type="hidden" name="returnPath" value="<?= htmlspecialchars($returnPath ?? '') ?>">
        <label>
            Title
            <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
        </label>

        <label>
            Body
            <textarea id="editor" style="background-color: #fff;" name=" body"><?= $post['body'] ?></textarea>
        </label>

        <label>
            Tags
            <input type="text" name="tags" list="tag-suggestions"
                value="<?= htmlspecialchars(implode(', ', array_column($tags, 'name'))) ?>"
                placeholder="e.g. poetry, reflection">
            <datalist id="tag-suggestions">
                <?php foreach ($allTags as $t): ?>
                    <option value="<?= htmlspecialchars($t['name']) ?>">
                    <?php endforeach; ?>
            </datalist>
            <small>Separate multiple tags with commas</small>
        </label>

        <label>
            Mood <span class="mood-hint">(private — only you'll see this)</span>
            <div class="mood-selector">
                <?php foreach ($moodLabels as $value => $label): ?>
                    <label class="mood-option">
                        <input type="radio" name="mood" value="<?= $value ?>" <?= (int) ($post['mood_level'] ?? 0) === $value ? 'checked' : '' ?>>
                        <span><?= $value ?></span>
                        <small><?= htmlspecialchars($label) ?></small>
                    </label>
                <?php endforeach; ?>
                <label class="mood-option mood-none">
                    <input type="radio" name="mood" value="" <?= empty($post['mood_level']) ? 'checked' : '' ?>>
                    <span>—</span>
                    <small>Skip</small>
                </label>
            </div>
        </label>

        <label>
            Status
            <select name="status">
                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
        </label>

        <button type="submit">Save changes</button>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script>
    function uploadEntryImage(file, insertCallback) {
        var data = new FormData();
        data.append('file', file);
        data.append('csrf_token', document.querySelector('#post-form input[name="csrf_token"]').value);

        fetch(window.BASE_URL_JS + 'post/uploadimage', {
                method: 'POST',
                body: data
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(result) {
                if (result.url) {
                    insertCallback(result.url);
                } else {
                    alert('Image upload failed: ' + (result.error || 'unknown error'));
                }
            })
            .catch(function() {
                alert('Image upload failed - please check your connection and try again.');
            });
    }

    $('#editor').summernote({
        height: 300,
        background: '#000',
        callbacks: {
            onImageUpload: function(files) {
                var editor = this;
                uploadEntryImage(files[0], function(url) {
                    $(editor).summernote('insertImage', url);
                });
            }
        }

    });

    var copyBtn = document.getElementById('copy-link-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var input = document.getElementById('private-link-input');
            input.select();
            navigator.clipboard.writeText(input.value).then(function() {
                copyBtn.textContent = 'Copied!';
                setTimeout(function() {
                    copyBtn.textContent = 'Copy';
                }, 1500);
            });
        });
    }

    var expiryEl = document.getElementById('expiry-time');
    var countdownEl = document.getElementById('countdown');
    if (expiryEl && countdownEl) {
        // The server sends expires_at as local MySQL datetime; treat it as local time.
        var expiresAt = new Date(expiryEl.dataset.expires.replace(' ', 'T'));

        function updateCountdown() {
            var diffMs = expiresAt - new Date();
            if (diffMs <= 0) {
                countdownEl.textContent = 'expired — generate a new one';
                return;
            }
            var mins = Math.floor(diffMs / 60000);
            var secs = Math.floor((diffMs % 60000) / 1000);
            countdownEl.textContent = mins + 'm ' + secs + 's left';
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
</script>