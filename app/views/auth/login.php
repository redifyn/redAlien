<div class="ra-auth-shell">

    <section class="ra-auth-visual">

        <div class="ra-auth-brand">
            <img
                src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                alt="redAlien logo"
            >

            <span>
                <strong>red</strong>Alien
            </span>
        </div>

        <div class="ra-auth-visual-copy">

            <span class="ra-auth-kicker">
                Secure collaboration
            </span>

            <h1>
                Connect your Aliens.
                <span>Move faster.</span>
            </h1>

            <p>
                Chat, meet, share files and collaborate in one futuristic workspace.
            </p>

            <div class="ra-auth-feature-grid">

                <div>
                    <i class="bi bi-chat-dots"></i>
                    <span>Real-time messaging</span>
                </div>

                <div>
                    <i class="bi bi-camera-video"></i>
                    <span>Video meetings</span>
                </div>

                <div>
                    <i class="bi bi-display"></i>
                    <span>Screen sharing</span>
                </div>

                <div>
                    <i class="bi bi-folder2-open"></i>
                    <span>Team files</span>
                </div>

            </div>

        </div>

    </section>


    <section class="ra-auth-panel">

        <div class="ra-auth-card">

            <div class="ra-auth-card-heading">

                <span class="ra-auth-kicker">
                    Welcome back
                </span>

                <h2>Sign in to <span class="text-danger">Red</span>Alien</h2>

                <p>
                    Enter your account details to continue.
                </p>

            </div>

            <?php Flash::display(); ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form
                action="<?= BASE_URL; ?>/auth/login"
                method="POST"
                novalidate
            >
                     <?= Csrf::field(); ?>
                <div class="mb-3">

                    <label class="form-label">
                        Email address
                    </label>

                    <div class="ra-auth-input-wrap">

                        <i class="bi bi-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            class="form-control <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>"
                            value="<?= htmlspecialchars($email ?? ''); ?>"
                            placeholder="you@example.com"
                            autocomplete="email"
                        >

                    </div>

                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= htmlspecialchars($errors['email']); ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="mb-3">

                    <div class="d-flex justify-content-between gap-3">
                        <label class="form-label">
                            Password
                        </label>

                        <a
                            href="<?= BASE_URL; ?>/auth/forgotPassword"
                            class="ra-auth-small-link"
                        >
                            Forgot password?
                        </a>
                    </div>

                    <div class="ra-auth-input-wrap">

                        <i class="bi bi-lock"></i>

                        <input
                            type="password"
                            name="password"
                            id="loginPassword"
                            class="form-control <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >

                        <button
                            type="button"
                            class="ra-password-toggle"
                            data-password-target="loginPassword"
                            aria-label="Show password"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback d-block">
                            <?= htmlspecialchars($errors['password']); ?>
                        </div>
                    <?php endif; ?>

                </div>
                

                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="rememberMe"
                    >

                    <label class="form-check-label" for="rememberMe">
                        Keep me signed in
                    </label>

                </div>

                <button
                    type="submit"
                    class="btn ra-auth-submit w-100"
                >
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <div class="ra-auth-divider">
                <span>New to redAlien?</span>
            </div>

            <a
                href="<?= BASE_URL; ?>/auth/register"
                class="btn ra-auth-secondary w-100"
            >
                Create an account
            </a>

        </div>

    </section>

</div>