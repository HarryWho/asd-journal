<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
<div class="post-hero">
    <h1>New entry</h1>

    <form method="POST" action="<?= BASE_URL ?>post/store" class="post-form" id="post-form">
        <?= csrf_field() ?>
        <label>
            Title
            <input type="text" name="title" required>
        </label>

        <label>
            Body
            <textarea id="editor" name="body"></textarea>
        </label>

        <label>
            Tags
            <input type="text" name="tags" list="tag-suggestions" placeholder="e.g. poetry, reflection">
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
                        <input type="radio" name="mood" value="<?= $value ?>">
                        <span><?= $value ?></span>
                        <small><?= htmlspecialchars($label) ?></small>
                    </label>
                <?php endforeach; ?>
                <label class="mood-option mood-none">
                    <input type="radio" name="mood" value="" checked>
                    <span>—</span>
                    <small>Skip</small>
                </label>
            </div>
        </label>

        <label>
            Status
            <select name="status">
                <option value="published">Published</option>
                <option value="draft">Draft (only you can see it)</option>
            </select>
        </label>

        <button type="submit">Save entry</button>
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
        placeholder: 'Write your entry...',
        height: 300,
        callbacks: {
            onImageUpload: function(files) {
                var editor = this;
                uploadEntryImage(files[0], function(url) {
                    $(editor).summernote('insertImage', url);
                });
            }
        }
    });
</script>