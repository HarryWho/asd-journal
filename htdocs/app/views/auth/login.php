<div class="post-hero">
    <h1>Log in</h1>

    <form method="POST" action="<?= BASE_URL ?>auth/authenticate" class="auth-form">
        <?= csrf_field() ?>
        <label>
            Email
            <input type="email" name="email" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required>
        </label>

        <button type="submit">Log in</button>
    </form>

    <p>Not subscribed yet? <a href="<?= BASE_URL ?>auth/register">Subscribe</a></p>
</div>