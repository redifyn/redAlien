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
                Start collaborating
            </span>

            <h1>
                Build your Galaxy.
                <span>Bring all Aliens together.</span>
            </h1>

            <p>
                Create your account and start chatting, meeting and sharing securely.
            </p>

        </div>

    </section>


    <section class="ra-auth-panel">

        <div class="ra-auth-card ra-auth-card-register">

            <div class="ra-auth-card-heading">

                <span class="ra-auth-kicker">
                    Join redAlien
                </span>

                <h2>Create your account</h2>

                <p>
                    Complete the form below to get started.
                </p>

            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <form
                action="<?= BASE_URL; ?>/auth/register"
                method="POST"
                novalidate
            >
                   <?= Csrf::field(); ?>
                <div class="row g-3">

                    <div class="col-12">

                        <label class="form-label">
                            Full name
                        </label>

                        <div class="ra-auth-input-wrap">

                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : ''; ?>"
                                value="<?= htmlspecialchars($form['full_name'] ?? ''); ?>"
                                placeholder="Anselm Dike"
                                autocomplete="name"
                            >

                        </div>

                        <?php if (!empty($errors['full_name'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['full_name']); ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Username
                        </label>

                        <div class="ra-auth-input-wrap">

                            <i class="bi bi-at"></i>

                            <input
                                type="text"
                                name="username"
                                class="form-control <?= !empty($errors['username']) ? 'is-invalid' : ''; ?>"
                                value="<?= htmlspecialchars($form['username'] ?? ''); ?>"
                                placeholder="anselmdike"
                                autocomplete="username"
                            >

                        </div>

                        <?php if (!empty($errors['username'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Email address
                        </label>

                        <div class="ra-auth-input-wrap">

                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                class="form-control <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>"
                                value="<?= htmlspecialchars($form['email'] ?? ''); ?>"
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


                    <div class="col-md-6">

                        <label class="form-label">
                            Password
                        </label>

                        <div class="ra-auth-input-wrap">

                            <i class="bi bi-lock"></i>

                            <input
                                type="password"
                                name="password"
                                id="registerPassword"
                                class="form-control <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>"
                                placeholder="Minimum 8 characters"
                                autocomplete="new-password"
                            >

                            <button
                                type="button"
                                class="ra-password-toggle"
                                data-password-target="registerPassword"
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


                    <div class="col-md-6">

                        <label class="form-label">
                            Confirm password
                        </label>

                        <div class="ra-auth-input-wrap">

                            <i class="bi bi-shield-lock"></i>

                            <input
                                type="password"
                                name="confirm_password"
                                id="confirmPassword"
                                class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                                placeholder="Repeat password"
                                autocomplete="new-password"
                            >

                            <button
                                type="button"
                                class="ra-password-toggle"
                                data-password-target="confirmPassword"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                        <?php if (!empty($errors['confirm_password'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['confirm_password']); ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

                <div class="form-check my-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="acceptTerms"
                        required
                    >

                    <label class="form-check-label" for="acceptTerms">
                        I agree to the Terms of Service and Privacy Policy.
                    </label>

                </div>

                <button
                    type="submit"
                    class="btn ra-auth-submit w-100"
                >
                    <span>Create Account</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>

            <div class="ra-auth-divider">
                <span>Already registered?</span>
            </div>

            <a
                href="<?= BASE_URL; ?>/auth/login"
                class="btn ra-auth-secondary w-100"
            >
                Sign in instead
            </a>

        </div>

    </section>

</div>