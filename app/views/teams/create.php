<main class="ra-main-content">

    <section class="ra-page-heading">

        <div>
            <span class="ra-eyebrow">Teams / Aliens</span>

            <h1>Create a New Alien 👽</h1>

            <p>
                Create a secure team workspace for your crew.
                A General Pod will be created automatically.
            </p>
        </div>

        <a
            href="<?= BASE_URL; ?>/teams"
            class="ra-period-btn text-decoration-none"
        >
            <i class="bi bi-arrow-left"></i>
            My Aliens
        </a>

    </section>

    <!-- <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">

    <h5 class="mb-3">
        Join an Alien
    </h5>

    <form
        action="<?= BASE_URL; ?>/teams/joinByInviteCode"
        method="POST"
    >

        <div class="input-group">

            <input
                type="text"
                name="invite_code"
                class="form-control"
                placeholder="Enter invite code e.g. RA-C2314F"
                required
            >

            <button
                type="submit"
                class="btn btn-success"
            >
                <i class="bi bi-box-arrow-in-right"></i>
                Join Alien
            </button>

        </div>

    </form>

    </div> -->

    <?php Flash::display(); ?>

    <form
        action="<?= BASE_URL; ?>/teams/store"
        method="POST"
        enctype="multipart/form-data"
        id="createAlienForm"
    >
<?= Csrf::field(); ?>
        <div class="row g-4">

            <div class="col-xl-8">

                <article class="ra-panel ra-create-alien-panel">

                    <div class="ra-panel-heading">

                        <div>
                            <span>Alien configuration</span>
                            <h2>Workspace Details</h2>
                        </div>

                        <i class="bi bi-stars text-success fs-4"></i>

                    </div>

                    <div class="ra-create-form-body">

                        <div class="ra-form-section">

                            <div class="ra-section-number">
                                01
                            </div>

                            <div class="flex-grow-1">

                                <label
                                    for="alienLogo"
                                    class="form-label fw-semibold"
                                >
                                    Alien Logo
                                </label>

                                <div class="ra-logo-upload">

                                    <div class="ra-logo-preview-wrap">

                                        <img
                                            src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                                            alt="Alien logo preview"
                                            id="alienLogoPreview"
                                        >

                                        <span class="ra-logo-scan"></span>

                                    </div>

                                    <div>

                                        <label
                                            for="alienLogo"
                                            class="btn ra-upload-button"
                                        >
                                            <i class="bi bi-cloud-arrow-up"></i>
                                            Upload Logo
                                        </label>

                                        <input
                                            type="file"
                                            name="logo"
                                            id="alienLogo"
                                            class="d-none"
                                            accept=".jpg,.jpeg,.png,.webp"
                                        >

                                        <p class="ra-field-help">
                                            JPG, PNG or WEBP. Maximum size: 2MB.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="ra-form-section">

                            <div class="ra-section-number">
                                02
                            </div>

                            <div class="flex-grow-1">

                                <label
                                    for="alienName"
                                    class="form-label fw-semibold"
                                >
                                    Alien Name
                                </label>

                                <div class="ra-alien-input-wrap">

                                    <i class="bi bi-people"></i>

                                    <input
                                        type="text"
                                        name="name"
                                        id="alienName"
                                        class="form-control"
                                        placeholder="Example: PHP Developers"
                                        minlength="3"
                                        maxlength="100"
                                        required
                                    >

                                </div>

                                <p class="ra-field-help">
                                    This is the team name your crew will see.
                                </p>

                            </div>

                        </div>

                        <div class="ra-form-section">

                            <div class="ra-section-number">
                                03
                            </div>

                            <div class="flex-grow-1">

                                <label
                                    for="alienDescription"
                                    class="form-label fw-semibold"
                                >
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    id="alienDescription"
                                    class="form-control ra-alien-textarea"
                                    rows="5"
                                    maxlength="1000"
                                    placeholder="Describe the purpose of this Alien..."
                                ></textarea>

                                <div class="ra-textarea-footer">
                                    <span>
                                        Tell your crew what this workspace is for.
                                    </span>

                                    <small id="descriptionCounter">
                                        0 / 1000
                                    </small>
                                </div>

                            </div>

                        </div>

                        <div class="ra-form-section border-0">

                            <div class="ra-section-number">
                                04
                            </div>

                            <div class="flex-grow-1">

                                <label class="form-label fw-semibold">
                                    Visibility
                                </label>

                                <div class="ra-visibility-grid">

                                    <label class="ra-visibility-option">

                                        <input
                                            type="radio"
                                            name="visibility"
                                            value="private"
                                            checked
                                        >

                                        <span class="ra-visibility-card">

                                            <i class="bi bi-lock-fill"></i>

                                            <strong>Private Alien</strong>

                                            <small>
                                                Only invited crew members can join.
                                            </small>

                                        </span>

                                    </label>

                                    <label class="ra-visibility-option">

                                        <input
                                            type="radio"
                                            name="visibility"
                                            value="public"
                                        >

                                        <span class="ra-visibility-card">

                                            <i class="bi bi-globe2"></i>

                                            <strong>Public Alien</strong>

                                            <small>
                                                Other users can discover this workspace.
                                            </small>

                                        </span>

                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>

                </article>

            </div>

            <div class="col-xl-4">

                <div class="ra-create-alien-sidebar">

                    <article class="ra-panel ra-alien-preview-card">

                        <div class="ra-panel-heading">

                            <div>
                                <span>Live preview</span>
                                <h2>Your Alien</h2>
                            </div>

                            <span class="ra-preview-status">
                                Preview
                            </span>

                        </div>

                        <div class="ra-alien-preview-body">

                            <div class="ra-preview-orbit">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                                    alt="Alien preview"
                                    id="previewAlienLogo"
                                >

                                <span></span>

                            </div>

                            <h3 id="previewAlienName">
                                Untitled Alien
                            </h3>

                            <p id="previewAlienDescription">
                                Your Alien description will appear here.
                            </p>

                            <div class="ra-preview-meta">

                                <div>
                                    <i class="bi bi-person-badge"></i>

                                    <span>
                                        Captain
                                        <strong>
                                            <?= htmlspecialchars(
                                                $_SESSION['full_name']
                                                ?? $_SESSION['username']
                                                ?? 'User'
                                            ); ?>
                                        </strong>
                                    </span>
                                </div>

                                <div>
                                    <i class="bi bi-shield-lock"></i>

                                    <span>
                                        Visibility
                                        <strong id="previewVisibility">
                                            Private
                                        </strong>
                                    </span>
                                </div>

                                <div>
                                    <i class="bi bi-diagram-3"></i>

                                    <span>
                                        Default Pod
                                        <strong>General</strong>
                                    </span>
                                </div>

                                <div>
                                    <i class="bi bi-people"></i>

                                    <span>
                                        Crew
                                        <strong>1 member</strong>
                                    </span>
                                </div>

                            </div>

                        </div>

                    </article>

                    <article class="ra-panel mt-4 ra-launch-panel">

                        <div class="ra-launch-icon">
                            <i class="bi bi-rocket-takeoff"></i>
                        </div>

                        <h3>Ready to launch?</h3>

                        <p>
                            Your invite code and General Pod will be
                            generated automatically.
                        </p>

                        <button
                            type="submit"
                            class="btn ra-create-alien-submit w-100"
                            id="createAlienButton"
                        >
                            <span>Create Alien</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>

                    </article>

                </div>

            </div>

        </div>

    </form>

</main>