document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.like-widget').forEach(function (widget) {
        var btn = widget.querySelector('.like-btn');
        var countEl = widget.querySelector('.like-count');
        var postId = widget.dataset.postId;

        btn.addEventListener('click', function () {
            btn.disabled = true;

            fetch(window.BASE_URL_JS + 'like/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + encodeURIComponent(postId)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    countEl.textContent = data.likes;
                    btn.textContent = data.liked ? '♥' : '♡';
                    btn.classList.toggle('liked', data.liked);
                })
                .catch(function (err) {
                    console.error('Like failed:', err);
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
});
