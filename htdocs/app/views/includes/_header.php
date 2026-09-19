<header class="site-header no-print">
    <a class="site-title" href="<?= BASE_URL ?>">‪<?= htmlspecialchars(SITE_NAME) ?></a>
    <nav>
        <a href="<?= BASE_URL ?>post/archive">Archive</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <div class="dropdown">
                    <a href="#" onclick="myFunction('myDropdown'); return false" class="dropbtn">Admin</a>
                    <div id="myDropdown" class="dropdown-content">
                        <a href="<?= BASE_URL ?>post/create"><i class="bi bi-file-earmark-plus"></i> New entry</a>
                        <a href="<?= BASE_URL ?>post/moodtracker"><i class="bi bi-graph-up"></i> Mood tracker</a>
                        <?php
                        require_once __DIR__ . '/../../models/Comment.php';
                        $pendingCount = (new Comment())->countPending();
                        ?>
                        <a href="<?= BASE_URL ?>comment/moderate">
                            <i class="bi bi-chat-left-text"></i> Comments<?php if ($pendingCount > 0): ?> <span class="nav-badge"><?= $pendingCount ?></span><?php endif; ?>
                        </a>
                    </div>


                </div>
            <?php endif; ?>
            <div class="dropdown">
                <a href="#" onclick="myFunction('profileDropdown'); return false" class="dropbtn">Profile</a>
                <div id="profileDropdown" class="dropdown-content">
                    <a href="#"> <?= htmlspecialchars($_SESSION['username']) ?></a>

                    <a href="<?= BASE_URL ?>auth/logout"> Log out</a>
                </div>


            </div>


        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login">Log in</a>
            <a href="<?= BASE_URL ?>auth/register">Subscribe</a>
        <?php endif; ?>

    </nav>
</header>

<script>
    /*  When the user clicks on the button,
        toggle between hiding and showing the dropdown content */
    function myFunction(id) {
        document.getElementById(id).classList.toggle("show");
    }

    // Close the dropdown menu if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            var i;
            for (i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>