<div class="ra-forgot-page">

    <!-- Background Alien -->
    <div
        class="ra-forgot-alien-bg"
        aria-hidden="true"
    >
        <img
            src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
            alt=""
        >
    </div>


    <div class="ra-forgot-card">

        <!-- Logo -->
        <div class="ra-forgot-logo">

            <img
                src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                alt="RedAlien"
            >

        </div>


        <!-- Brand -->
        <div class="ra-forgot-brand">

            <span class="ra-forgot-red">
                Red
            </span><span class="ra-forgot-alien">
                Alien
            </span>

        </div>


        <!-- Heading -->
        <div class="ra-forgot-heading">

            <span class="ra-auth-kicker">
                Account Security
            </span>

            <h2>
                Create a new password
            </h2>

            <p>
                Choose a new secure password for your
                RedAlien account.
            </p>

        </div>


        <?php Flash::display(); ?>


        <?php if (!empty($errors['general'])): ?>

            <div class="alert alert-danger">

                <?= htmlspecialchars(
                    $errors['general']
                ); ?>

            </div>

        <?php endif; ?>


        <!-- Reset Form -->
        <form
            action="<?= BASE_URL; ?>/auth/resetPassword/<?= urlencode($token); ?>"
            method="POST"
            novalidate
        >

            <?= Csrf::field(); ?>


            <!-- New Password -->
            <div class="mb-3">

                <label
                    for="password"
                    class="form-label"
                >
                    New Password
                </label>


                <div class="ra-auth-input-wrap">

                    <i class="bi bi-lock"></i>

                    <input
                        type="password"
                        name="password"
                        id="resetPassword"
                        class="form-control <?= !empty($errors['password'])
                            ? 'is-invalid'
                            : ''; ?>"
                        placeholder="Enter new password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >


                    <button
                        type="button"
                        class="ra-password-toggle"
                        data-password-target="resetPassword"
                        aria-label="Show password"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                </div>


                <?php if (!empty($errors['password'])): ?>

                    <div class="invalid-feedback d-block">

                        <?= htmlspecialchars(
                            $errors['password']
                        ); ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- Confirm Password -->
            <div class="mb-4">

                <label
                    for="confirm_password"
                    class="form-label"
                >
                    Confirm New Password
                </label>


                <div class="ra-auth-input-wrap">

                    <i class="bi bi-shield-lock"></i>

                    <input
                        type="password"
                        name="confirm_password"
                        id="confirmResetPassword"
                        class="form-control <?= !empty($errors['confirm_password'])
                            ? 'is-invalid'
                            : ''; ?>"
                        placeholder="Confirm new password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >


                    <button
                        type="button"
                        class="ra-password-toggle"
                        data-password-target="confirmResetPassword"
                        aria-label="Show password"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                </div>


                <?php if (!empty($errors['confirm_password'])): ?>

                    <div class="invalid-feedback d-block">

                        <?= htmlspecialchars(
                            $errors['confirm_password']
                        ); ?>

                    </div>

                <?php endif; ?>

            </div>


            <button
                type="submit"
                class="btn ra-auth-submit w-100"
            >

                <span>
                    Reset Password
                </span>

                <i class="bi bi-shield-lock-fill"></i>

            </button>

        </form>


        <div class="ra-forgot-back">

            <a
                href="<?= BASE_URL; ?>/auth/login"
            >
                <i class="bi bi-arrow-left"></i>

                Back to Sign In
            </a>

        </div>

    </div>

</div>