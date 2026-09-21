<?php
/*
|--------------------------------------------------------------------------
| Show the splash only once after login
|--------------------------------------------------------------------------
*/

$showSplash = empty($_SESSION['redalien_splash_seen']);

if ($showSplash) {
    $_SESSION['redalien_splash_seen'] = true;
}
?>

<?php if ($showSplash): ?>

    <div id="raSplash" class="ra-splash" aria-hidden="false">

        <div class="ra-splash-grid"></div>
        <div class="ra-splash-particles"></div>

        <div class="ra-splash-content">

            <div class="ra-splash-logo-wrap">

                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt="redAlien"
                    class="ra-splash-logo"
                    id="raSplashLogo"
                >

                <div class="ra-splash-scan-line"></div>

            </div>

            <h1 class="ra-splash-title">
                <span class="ra-splash-red">red</span><span class="ra-splash-white">Alien</span>
            </h1>

            <p class="ra-splash-status" id="raSplashStatus">
                Initializing Command Center...
            </p>

            <div class="ra-splash-progress-wrap">

                <div class="ra-splash-progress-track">
                    <div
                        class="ra-splash-progress-fill"
                        id="raSplashProgress"
                    ></div>
                </div>

                <span id="raSplashPercent">0%</span>

            </div>

        </div>

    </div>

<?php endif; ?>


<div
    id="raDashboardApp"
    class="<?= $showSplash ? 'ra-dashboard-waiting' : 'ra-dashboard-ready'; ?>"
>

    <main class="ra-main-content">

        <section class="ra-page-heading">

            <div>
                <span class="ra-eyebrow">Command center</span>

                <h1>
                    Welcome back,
                    <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    <span>👋</span>
                </h1>

                <p>Here’s what’s happening with your teams today.</p>
            </div>

            <button class="ra-period-btn" type="button">
                <i class="bi bi-calendar3"></i>
                This week
                <i class="bi bi-chevron-down"></i>
            </button>

        </section>


        <section class="row g-3 g-xl-4 mb-4">

            <?php
            $stats = [
                ['Teams', 12, 'bi-people', '20%'],
                ['Messages', 248, 'bi-chat-dots', '18%'],
                ['Meetings', 3, 'bi-calendar2-week', '10%'],
                ['Files', 18, 'bi-folder2-open', '15%'],
            ];

            foreach ($stats as $stat):
            ?>

                <div class="col-sm-6 col-xl-3">

                    <article class="ra-stat-card">

                        <div class="ra-stat-icon">
                            <i class="bi <?= htmlspecialchars($stat[2]); ?>"></i>
                        </div>

                        <div>
                            <span><?= htmlspecialchars($stat[0]); ?></span>

                            <strong><?= (int) $stat[1]; ?></strong>

                            <small>
                                <i class="bi bi-arrow-up-right"></i>
                                <?= htmlspecialchars($stat[3]); ?> vs last week
                            </small>
                        </div>

                    </article>

                </div>

            <?php endforeach; ?>

        </section>


        <section class="row g-4">

            <div class="col-xl-8">

                <article class="ra-panel ra-conversation-panel">

                    <div class="ra-panel-heading">

                        <div>
                            <span>Live activity</span>
                            <h2>Recent Conversations</h2>
                        </div>

                        <a href="#">
                            View all
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>

                    <?php
                    $conversations = [
                        ['John Doe', 'john.svg', 'Can you review the API documentation?', '10:24 AM', 2],
                        ['Sarah Williams', 'sarah.svg', 'Meeting starts in 15 minutes.', '10:15 AM', 1],
                        ['David Smith', 'david.svg', 'Screen sharing is ready.', '09:42 AM', 0],
                        ['Emily Johnson', 'emily.svg', 'Thanks! I’ll check it out.', 'Yesterday', 3],
                        ['Michael Brown', 'michael.svg', 'Let’s sync up later today.', 'Yesterday', 0],
                    ];

                    foreach ($conversations as $conversation):
                    ?>

                        <a href="#" class="ra-conversation-row">

                            <div class="ra-avatar-wrap">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/<?= htmlspecialchars($conversation[1]); ?>"
                                    alt="<?= htmlspecialchars($conversation[0]); ?>"
                                >

                                <i></i>

                            </div>

                            <div class="ra-conversation-copy">
                                <strong><?= htmlspecialchars($conversation[0]); ?></strong>
                                <span><?= htmlspecialchars($conversation[2]); ?></span>
                            </div>

                            <time><?= htmlspecialchars($conversation[3]); ?></time>

                            <?php if ($conversation[4] > 0): ?>
                                <em><?= (int) $conversation[4]; ?></em>
                            <?php endif; ?>

                        </a>

                    <?php endforeach; ?>

                </article>


                <article class="ra-panel mt-4">

                    <div class="ra-panel-heading">

                        <div>
                            <span>Launch pad</span>
                            <h2>Quick Actions</h2>
                        </div>

                    </div>

                    <div class="ra-quick-actions">

                        <button type="button">
                            <i class="bi bi-camera-video"></i>
                            <span>Start Meeting</span>
                        </button>

                        <button type="button">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>Join Meeting</span>
                        </button>

                        <button type="button">
                            <i class="bi bi-chat-square-dots"></i>
                            <span>New Chat</span>
                        </button>

                        <button type="button">
                            <i class="bi bi-people"></i>
                            <span>Create Team</span>
                        </button>

                        <button type="button">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Upload File</span>
                        </button>

                        <button type="button">
                            <i class="bi bi-display"></i>
                            <span>Share Screen</span>
                        </button>

                    </div>

                </article>

            </div>


            <div class="col-xl-4">

                <article class="ra-panel">

                    <div class="ra-panel-heading">

                        <div>
                            <span>Presence</span>
                            <h2>Online Members</h2>
                        </div>

                        <a href="#">View all</a>

                    </div>

                    <?php
                    $members = [
                        ['John Doe', 'Developer', 'john.svg'],
                        ['Sarah Williams', 'Designer', 'sarah.svg'],
                        ['David Smith', 'DevOps', 'david.svg'],
                        ['Emily Johnson', 'Product Manager', 'emily.svg'],
                        ['Michael Brown', 'QA Engineer', 'michael.svg'],
                    ];

                    foreach ($members as $member):
                    ?>

                        <div class="ra-member-row">

                            <div class="ra-avatar-wrap">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/<?= htmlspecialchars($member[2]); ?>"
                                    alt="<?= htmlspecialchars($member[0]); ?>"
                                >

                                <i></i>

                            </div>

                            <div>
                                <strong><?= htmlspecialchars($member[0]); ?></strong>
                                <span><?= htmlspecialchars($member[1]); ?></span>
                            </div>

                            <button type="button">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                        </div>

                    <?php endforeach; ?>

                </article>


                <article class="ra-panel mt-4">

                    <div class="ra-panel-heading">

                        <div>
                            <span>Schedule</span>
                            <h2>Upcoming Meetings</h2>
                        </div>

                        <a href="#">Calendar</a>

                    </div>

                    <div class="ra-meeting-item">

                        <div class="ra-date-box">
                            <small>MAY</small>
                            <strong>08</strong>
                        </div>

                        <div>
                            <h3>Backend Team Sync</h3>

                            <p>Today · 03:00 PM – 04:00 PM</p>

                            <div class="ra-mini-avatars">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/john.svg"
                                    alt=""
                                >

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/sarah.svg"
                                    alt=""
                                >

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/david.svg"
                                    alt=""
                                >

                                <span>+3</span>

                            </div>

                        </div>

                    </div>


                    <div class="ra-meeting-item">

                        <div class="ra-date-box">
                            <small>MAY</small>
                            <strong>09</strong>
                        </div>

                        <div>
                            <h3>Design Review</h3>

                            <p>Tomorrow · 10:00 AM – 11:00 AM</p>

                            <div class="ra-mini-avatars">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/emily.svg"
                                    alt=""
                                >

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/michael.svg"
                                    alt=""
                                >

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/avatars/sarah.svg"
                                    alt=""
                                >

                                <span>+2</span>

                            </div>

                        </div>

                    </div>

                </article>

            </div>

        </section>

    </main>

</div>