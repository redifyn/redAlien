<header class="ra-topbar">

    <div class="ra-topbar-inner">

        <button
            class="ra-icon-btn ra-menu-toggle d-xl-none"
            id="raMenuToggle"
            type="button"
            aria-label="Open navigation"
        >
            <i class="bi bi-list"></i>
        </button>


        <div class="ra-search-wrap">

            <i class="bi bi-search"></i>

            <input
                type="search"
                placeholder="Search messages, files, teams..."
                aria-label="Search"
            >

            <kbd>Ctrl /</kbd>

        </div>


        <div class="ra-topbar-actions">

    


            <button
                class="ra-icon-btn ra-primary-action"
                type="button"
                title="Create new"
                aria-label="Create new"
            >
                <i class="bi bi-plus-lg"></i>
            </button>


            <button
                class="ra-icon-btn position-relative"
                type="button"
                title="Notifications"
                aria-label="Notifications"
            >
                <i class="bi bi-bell"></i>

                <span class="ra-count-badge">
                    8
                </span>
            </button>


            <button
                class="ra-icon-btn position-relative"
                type="button"
                title="Messages"
                aria-label="Messages"
            >
                <i class="bi bi-chat-dots"></i>

                <span class="ra-count-badge">
                    3
                </span>
            </button>


            <?php
            $avatarPath =
                !empty($_SESSION['avatar'])
                    ? (
                        BASE_URL .
                        '/' .
                        ltrim(
                            $_SESSION['avatar'],
                            '/'
                        )
                    )
                    : (
                        BASE_URL .
                        '/assets/images/avatars/default.svg'
                    );

            $currentUserName =
                $_SESSION['full_name']
                ?? 'User';
            ?>


            <div class="ra-user-menu">

                <img
                    src="<?= htmlspecialchars($avatarPath); ?>"
                    alt="<?= htmlspecialchars($currentUserName); ?>"
                    onerror="this.src='<?= BASE_URL; ?>/assets/images/avatars/default.svg';"
                >

                <div class="d-none d-md-block">

                    <strong>
                        <?= htmlspecialchars($currentUserName); ?>
                    </strong>

                    <span>
                        <i class="ra-online-dot"></i>
                        Online
                    </span>

                </div>

                <i
                    class="
                        bi
                        bi-chevron-down
                        d-none
                        d-md-inline
                    "
                ></i>

            </div>

        </div>

    </div>

</header>
