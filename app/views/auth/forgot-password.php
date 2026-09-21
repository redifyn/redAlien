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
                Account Recovery
            </span>

            <h2>
                Forgot your password?
            </h2>

            <p>
                Enter the email address connected to your
                RedAlien account and we'll send you a secure
                password reset link.
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


        <!-- Form -->
        <form
            action="<?= BASE_URL; ?>/auth/forgotPassword"
            method="POST"
            novalidate
        >

            <?= Csrf::field(); ?>


            <div class="mb-4">

                <label
                    for="forgotEmail"
                    class="form-label"
                >
                    Email address
                </label>


                <div class="ra-auth-input-wrap">

                    <i class="bi bi-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        id="forgotEmail"
                        class="form-control <?= !empty($errors['email'])
                            ? 'is-invalid'
                            : ''; ?>"
                        value="<?= htmlspecialchars(
                            $email ?? ''
                        ); ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                    >

                </div>


                <?php if (!empty($errors['email'])): ?>

                    <div class="invalid-feedback d-block">

                        <?= htmlspecialchars(
                            $errors['email']
                        ); ?>

                    </div>

                <?php endif; ?>

            </div>


            <button
                type="submit"
                class="btn ra-auth-submit w-100"
            >

                <span>
                    Send Reset Link
                </span>

                <i class="bi bi-send-fill"></i>

            </button>

        </form>


        <!-- Back -->
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