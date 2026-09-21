<?php

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '',
    PHP_URL_PATH
);

$isTeamDashboard = preg_match(
    '#/teams/show/[0-9]+#',
    $currentPath
);

$isPodPage = preg_match(
    '#/channels/show/[0-9]+#',
    $currentPath
);

$isVideoRoomPage = preg_match(
    '#/videoRoom/[0-9]+#',
    $currentPath
);

$isAlienPage =
    $isTeamDashboard ||
    $isPodPage ||
    $isVideoRoomPage;

$currentPodId = isset($pod['id'])
    ? (int) $pod['id']
    : null;

$currentTeamId = isset($alien['id'])
    ? (int) $alien['id']
    : null;

/*
|--------------------------------------------------------------------------
| When inside the video room there is no $pod variable,
| so recover the Pod ID from the URL.
|--------------------------------------------------------------------------
*/

if (
    $currentPodId === null &&
    preg_match(
        '#/videoRoom/([0-9]+)#',
        $currentPath,
        $matches
    )
) {
    $currentPodId = (int) $matches[1];
}

?>

<aside class="ra-sidebar" id="raSidebar">
    <div class="ra-brand">

    <img
        src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
        alt="redAlien logo"
    >

    <span class="ra-brand-name">
        <span class="ra-brand-red">red</span><span class="ra-brand-white">Alien</span>
    </span>

 

    <button
        class="ra-icon-btn ra-sidebar-close d-xl-none"
        id="raSidebarClose"
        type="button"
        aria-label="Close sidebar"
    >
        <i class="bi bi-x-lg"></i>
    </button>

    <button
        class="ra-icon-btn ra-desktop-sidebar-toggle d-none d-xl-inline-grid"
        id="raDesktopSidebarToggle"
        type="button"
        aria-label="Collapse sidebar"
        title="Collapse sidebar"
    >
        <i class="bi bi-layout-sidebar-inset"></i>
    </button>

</div>
    

    <?php if (!$isAlienPage): ?>

<nav class="ra-nav">

    <!-- MAIN -->
    <span class="ra-nav-heading">MAIN</span>

    <a href="<?= BASE_URL; ?>/dashboard" class="active">
        <i class="bi bi-house-door"></i>
        <span>Dashboard</span>
    </a>

    <a href="#">
        <i class="bi bi-chat-dots"></i>
        <span>Chat</span>
        <em>12</em>
    </a>


    <!-- WORKSPACE -->
    <span class="ra-nav-heading mt-3">WORKSPACE</span>

    <a href="<?= BASE_URL; ?>/teams">
        <i class="bi bi-people"></i>
        <span>Teams / Aliens</span>
    </a>

    <a href="#">
        <i class="bi bi-person-badge"></i>
        <span>Crew</span>
    </a>

    <a
            href="#"
            data-bs-toggle="modal"
            data-bs-target="#joinAlienModal">

            <i class="bi bi-key-fill"></i>
            <span>Join Alien</span>
    </a>


    <!-- COLLABORATION -->
    <span class="ra-nav-heading mt-3">COLLABORATION</span>

    <a href="#">
        <i class="bi bi-camera-video"></i>
        <span>Meetings</span>
    </a>

    <a href="#">
        <i class="bi bi-folder"></i>
        <span>Vault</span>
    </a>

    <a href="#">
        <i class="bi bi-bell"></i>
        <span>Notifications</span>
        <em>8</em>
    </a>


    <!-- SYSTEM -->
    <span class="ra-nav-heading mt-3">SYSTEM</span>

    <a href="#">
        <i class="bi bi-gear"></i>
        <span>Settings</span>
    </a>

</nav>

<?php else: ?>

    <nav class="ra-nav ra-alien-nav">

        <span class="ra-nav-heading">CURRENT ALIEN</span>

        <a
            href="<?= $currentTeamId
                ? BASE_URL . '/teams/show/' . $currentTeamId
                : BASE_URL . '/teams'; ?>"
            class="<?= $isTeamDashboard ? 'active' : ''; ?>"
            data-title="<?= htmlspecialchars($alien['name'] ?? 'Current Alien'); ?>"
        >

        <span class="ra-nav-heading mt-3">PODS</span>

        <?php if (!empty($pods)): ?>

            <?php foreach ($pods as $sidebarPod): ?>

                <?php
                $sidebarPodId = (int) $sidebarPod['id'];

                $isActivePod =
                    $currentPodId !== null &&
                    $currentPodId === $sidebarPodId;

                $podIcon =
                    ($sidebarPod['type'] ?? 'public') === 'private'
                        ? 'bi-lock-fill'
                        : 'bi-hash';
                ?>

                <a
                    href="<?= BASE_URL; ?>/channels/show/<?= $sidebarPodId; ?>"
                    class="<?= $isActivePod ? 'active' : ''; ?>"
                    data-title="<?= htmlspecialchars($sidebarPod['name']); ?>"
                >
                    <i class="bi <?= htmlspecialchars($podIcon); ?>"></i>

                    <span>
                        <?= htmlspecialchars($sidebarPod['name']); ?>
                    </span>
                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="px-3 py-2 text-secondary small">
                No Pods available
            </div>

        <?php endif; ?>

        <?php if (
            $currentPodId !== null &&
            $currentPodId > 0
        ): ?>

            <a
                href="<?= BASE_URL; ?>/videoRoom/<?= (int) $currentPodId; ?>"
                class="<?= $isVideoRoomPage ? 'active' : ''; ?>"
                data-title="RedAlien Live"
            >
                <i class="bi bi-camera-video-fill"></i>

                <span>
                    RedAlien Live
                </span>
            </a>

        <?php endif; ?>

        <a
            href="#"
            data-bs-toggle="modal"
            data-bs-target="#createPodModal"
        >
            <i class="bi bi-plus-circle"></i>
            <span>Create Pod</span>
        </a>

        <span class="ra-nav-heading mt-3">ALIEN</span>

        <a href="#">
            <i class="bi bi-people"></i>
            <span>Crew</span>
        </a>

        <a href="#">
            <i class="bi bi-gear"></i>
            <span>Alien Settings</span>
        </a>

        <a href="<?= BASE_URL; ?>/teams">
            <i class="bi bi-arrow-left"></i>
            <span>Back to My Aliens</span>
        </a>

    </nav>

<?php endif; ?>

    

    <div class="ra-sidebar-spacer"></div>

    <div class="ra-system-card">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>System Status</strong>
            <i class="bi bi-activity"></i>
        </div>
       
        <p>All systems operational</p>
        <div class="ra-uptime">
            <span><i></i> 99.9% uptime</span>
            <small>Live</small>
        </div>
    </div>

     <a href="<?= BASE_URL; ?>/auth/logout" class="ra-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    
</aside>

<div class="ra-sidebar-backdrop" id="raSidebarBackdrop"></div>


<div
    class="modal fade"
    id="joinAlienModal"
    tabindex="-1"
    aria-labelledby="joinAlienModalLabel"
    aria-hidden="true"
>
    <div
        class="modal-dialog
               modal-dialog-centered"
    >
        <div
            class="modal-content
                   ra-join-alien-modal
                   border-0
                   shadow-lg
                   rounded-4"
        >
           <div class="ra-portal-particles">

                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <span></span>

            </div>

            <!-- HEADER -->
            <div class="modal-header ra-join-header">

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal"
    ></button>

    <div class="ra-join-hero">

        <div class="ra-join-avatar">

            <img
                src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                alt="RedAlien"
            >

        </div>

        <h3>
            Join an Alien
        </h3>

        <p>
            Secure Alien Invitation Portal
        </p>

    </div>

</div>


            <!-- FORM -->
            <form
                action="<?= BASE_URL; ?>/teams/joinByInviteCode"
                method="POST"
            >

                <div class="modal-body">

                    <p class="text-muted mb-4">
                        Enter the invite code shared with you
                        by your Alien owner.
                    </p>

                    <label
                        for="joinAlienInviteCode"
                        class="form-label fw-semibold"
                    >
                        Invite Code
                    </label>

                    <input
                        type="text"
                        id="joinAlienInviteCode"
                        name="invite_code"
                        class="
                            form-control
                            form-control-lg
                            text-uppercase
                        "
                        placeholder="RA-XXXXXX"
                        autocomplete="off"
                        required
                    >

                </div>


                <!-- FOOTER -->
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        <i
                            class="
                                bi
                                bi-box-arrow-in-right
                                me-1
                            "
                        ></i>

                        Join Alien

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


