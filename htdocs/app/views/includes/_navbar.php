<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/"><?= SITE_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/home/about">About</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                       <?= isLoggedIn() ? currentUser()->user_name : 'Account' ?>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (isLoggedIn()): ?>
                            <li><a class="dropdown-item" href="/user/logout">Logout</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="/user/login">Login</a></li>
                            <li><a class="dropdown-item" href="/user/register">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
            <?php if (!isLoggedIn()): ?>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-primary" href="/user/login">Login</a>
                    <a class="btn btn-primary" href="/user/register">Get started</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>