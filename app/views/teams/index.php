<main class="ra-main-content">

    <section class="ra-page-heading">

        <div>
            <span class="ra-eyebrow">Teams / Aliens</span>

            <h1>My Aliens 👽</h1>

            <p>
                Manage the teams and workspaces you belong to.
            </p>
        </div>

        <a
            href="<?= BASE_URL; ?>/teams/create"
            class="btn ra-create-alien-submit text-decoration-none px-4"
        >
            <i class="bi bi-plus-circle"></i>
            <span>Create Alien</span>
        </a>

    </section>

    <?php Flash::display(); ?>

    <?php if (empty($aliens)): ?>

        <article class="ra-panel text-center py-5">

            <div class="ra-empty-alien-icon">
                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt="Alien"
                >
            </div>

            <h2 class="fw-bold mt-4">
                No Aliens Yet
            </h2>

            <p class="text-secondary mb-4">
                Create your first team workspace and begin recruiting your crew.
            </p>

            <a
                href="<?= BASE_URL; ?>/teams/create"
                class="btn ra-create-alien-submit text-decoration-none px-4"
            >
                <i class="bi bi-rocket-takeoff"></i>
                <span>Create Your First Alien</span>
            </a>

        </article>

    <?php else: ?>

        <section class="row g-4">

            <?php foreach ($aliens as $alien): ?>

                <?php
                $logoPath = !empty($alien['logo'])
                    ? BASE_URL . '/' . ltrim($alien['logo'], '/')
                    : BASE_URL . '/assets/images/redalien-logo.svg';

                $visibility = ucfirst(
                    $alien['visibility'] ?? 'private'
                );

                $memberRole = ucfirst(
                    $alien['member_role'] ?? 'member'
                );
                ?>

                <div class="col-md-6 col-xl-4">

                    <article class="ra-panel ra-alien-card h-100">

                        <div class="ra-alien-card-top">

                            <div class="ra-alien-card-logo">

                                <img
                                    src="<?= htmlspecialchars($logoPath); ?>"
                                    alt="<?= htmlspecialchars($alien['name']); ?>"
                                    onerror="this.src='<?= BASE_URL; ?>/assets/images/redalien-logo.svg';"
                                >

                            </div>

                            <div class="ra-alien-card-status">

                                <?php if (($alien['visibility'] ?? '') === 'public'): ?>

                                    <span class="ra-alien-badge">
                                        <i class="bi bi-globe2"></i>
                                        Public
                                    </span>

                                <?php else: ?>

                                    <span class="ra-alien-badge">
                                        <i class="bi bi-lock-fill"></i>
                                        Private
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="ra-alien-card-content">

                            <span class="ra-alien-role">
                                <?= htmlspecialchars($memberRole); ?>
                            </span>

                            <h2>
                                <?= htmlspecialchars($alien['name']); ?>
                            </h2>

                            <p>
                                <?= !empty($alien['description'])
                                    ? htmlspecialchars($alien['description'])
                                    : 'No description has been added yet.'; ?>
                            </p>

                            <div class="ra-alien-card-stats">

                                <div>
                                    <i class="bi bi-people"></i>

                                    <span>
                                        <strong>
                                            <?= (int) ($alien['member_count'] ?? 0); ?>
                                        </strong>

                                        Crew
                                    </span>
                                </div>

                                <div>
                                    <i class="bi bi-diagram-3"></i>

                                    <span>
                                        <strong>
                                            <?= (int) ($alien['channel_count'] ?? 0); ?>
                                        </strong>

                                        Pods
                                    </span>
                                </div>

                            </div>

                            <div class="ra-alien-owner">

                                <i class="bi bi-person-badge"></i>

                                <span>
                                    Captain:
                                    <strong>
                                        <?= htmlspecialchars(
                                            $alien['owner_name'] ?? 'Unknown'
                                        ); ?>
                                    </strong>
                                </span>

                            </div>

                        </div>

                        <div class="ra-alien-card-footer">

                            <a
                                href="<?= BASE_URL; ?>/teams/show/<?= (int) $alien['id']; ?>"
                                class="btn ra-enter-alien-btn"
                            >
                                Enter Alien
                                <i class="bi bi-arrow-right"></i>
                            </a>

                        </div>

                    </article>

                </div>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>