<div class="post-hero">
    <p>Create a free account so you can comment on entries.</p>

    <form method="POST" action="<?= BASE_URL ?>auth/store" class="auth-form">
        <?= csrf_field() ?>
        <label>
            Username
            <input type="text" name="username" required>
        </label>

        <label>
            Email
            <input type="email" name="email" required>
        </label>

        <label>
            Password
            <input type="password" name="password" required minlength="8">
        </label>

        <button type="submit">Create account</button>
    </form>

    <p>Already have an account? <a href="<?= BASE_URL ?>auth/login">Log in</a></p>
</div>