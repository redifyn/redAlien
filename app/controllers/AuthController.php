<?php

class AuthController extends Controller
{
    private User $userModel;
    private PasswordReset $passwordResetModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->passwordResetModel = $this->model('PasswordReset');
    }

    /*
    |--------------------------------------------------------------------------
    | Registration page
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        Auth::requireGuest();

        $data = [
            'title' => 'Create Account',
            'errors' => [],
            'form' => [
                'full_name' => '',
                'username' => '',
                'email' => ''
            ]
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->handleRegistration();
        }

        $this->authView('auth/register', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Process registration
    |--------------------------------------------------------------------------
    */

    private function handleRegistration(): array
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Please enter your full name.';
        } elseif (mb_strlen($fullName) < 3) {
            $errors['full_name'] = 'Your full name must contain at least 3 characters.';
        }

        if ($username === '') {
            $errors['username'] = 'Please choose a username.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $errors['username'] =
                'Username must be 3–30 characters and contain only letters, numbers and underscores.';
        } elseif ($this->userModel->findByUsername($username)) {
            $errors['username'] = 'That username is already taken.';
        }

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($this->userModel->findByEmail($email)) {
            $errors['email'] = 'An account already exists with this email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Please create a password.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Your password must contain at least 8 characters.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'The passwords do not match.';
        }

        $data = [
            'title' => 'Create Account',
            'errors' => $errors,
            'form' => [
                'full_name' => $fullName,
                'username' => $username,
                'email' => $email
            ]
        ];

        if (!empty($errors)) {
            return $data;
        }

        $created = $this->userModel->create([
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);

        if (!$created) {
            $data['errors']['general'] =
                'Your account could not be created. Please try again.';

            return $data;
        }

        Flash::success('Account created successfully. You can now sign in.');

        $this->redirect('auth/login');
    }

    /*
    |--------------------------------------------------------------------------
    | Login page
    |--------------------------------------------------------------------------
    */

    public function login(): void
    {
        Auth::requireGuest();

        $data = [
            'title' => 'Sign In',
            'errors' => [],
            'email' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->handleLogin();
        }

        $this->authView('auth/login', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Process login
    |--------------------------------------------------------------------------
    */

    private function handleLogin(): array
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Please enter your password.';
        }

        $data = [
            'title' => 'Sign In',
            'errors' => $errors,
            'email' => $email
        ];

        if (!empty($errors)) {
            return $data;
        }

        $user = $this->userModel->login($email, $password);

        if (!$user) {
            $data['errors']['general'] =
                'The email address or password is incorrect.';

            return $data;
        }

        if (($user['status'] ?? '') === 'suspended') {
            $data['errors']['general'] =
                'Your account has been suspended. Please contact an administrator.';

            return $data;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar'] = $user['avatar'];

        /*
        |--------------------------------------------------------------------------
        | Show RedAlien Splash After Login
        |--------------------------------------------------------------------------
        */

        unset(
            $_SESSION['redalien_splash_seen']
        );

        Flash::success(
    'Welcome back, ' .
    $user['full_name'] .
    '.'
    );


    /*
    |--------------------------------------------------------------------------
    | Continue Pending Alien Invitation
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $_SESSION['pending_alien_invite_token']
        )
    ) {

        $token =
            $_SESSION[
                'pending_alien_invite_token'
            ];

        $this->redirect(
            'invitations/accept/' .
            urlencode($token)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normal Login
    |--------------------------------------------------------------------------
    */

    $this->redirect(
        'home/index'
    );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): void
    {
        Auth::logout();

        Flash::success('You have been signed out successfully.');

        $this->redirect('auth/login');
    }

    /*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

public function forgotPassword(): void
{
    Auth::requireGuest();

    $data = [
        'title' => 'Forgot Password',
        'errors' => [],
        'email' => ''
    ];


    if (
        $_SERVER['REQUEST_METHOD'] ===
        'POST'
    ) {

        $email =
            strtolower(
                trim(
                    $_POST['email']
                    ?? ''
                )
            );

        $data['email'] =
            $email;


        if (
            $email === '' ||
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $data['errors']['email'] =
                'Please enter a valid email address.';

            $this->authView(
                'auth/forgot-password',
                $data
            );

            return;
        }


        $user =
            $this->userModel
                ->findByEmail(
                    $email
                );


        /*
        |--------------------------------------------------------------------------
        | Do Not Reveal Whether The Account Exists
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            Flash::success(
                'If an account exists with that email address, a password reset link has been sent.'
            );

            $this->redirect(
                'auth/forgotPassword'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invalidate Older Reset Links
        |--------------------------------------------------------------------------
        */

        $this->passwordResetModel
            ->invalidateForUser(
                (int) $user['id']
            );


        /*
        |--------------------------------------------------------------------------
        | Generate Secure Token
        |--------------------------------------------------------------------------
        */

        $token =
            bin2hex(
                random_bytes(32)
            );


        $tokenHash =
            hash(
                'sha256',
                $token
            );


        $expiresAt =
            date(
                'Y-m-d H:i:s',
                strtotime('+30 minutes')
            );


        /*
        |--------------------------------------------------------------------------
        | Save Reset
        |--------------------------------------------------------------------------
        */

        $resetId =
            $this->passwordResetModel
                ->createReset(
                    (int) $user['id'],
                    $tokenHash,
                    $expiresAt
                );


        if (!$resetId) {

            $data['errors']['general'] =
                'The reset request could not be created. Please try again.';

            $this->authView(
                'auth/forgot-password',
                $data
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Reset URL
        |--------------------------------------------------------------------------
        */

        $resetUrl =
            BASE_URL .
            '/auth/resetPassword/' .
            urlencode($token);


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        $safeName =
            htmlspecialchars(
                $user['full_name']
                ?? 'RedAlien User',
                ENT_QUOTES,
                'UTF-8'
            );


        $safeUrl =
            htmlspecialchars(
                $resetUrl,
                ENT_QUOTES,
                'UTF-8'
            );


        $htmlBody = '
            <div
                style="
                    max-width:600px;
                    margin:0 auto;
                    padding:32px;
                    background:#071108;
                    color:#ffffff;
                    font-family:Arial,sans-serif;
                    border-radius:16px;
                "
            >

                <div
                    style="
                        font-size:20px;
                        font-weight:900;
                        letter-spacing:2px;
                        margin-bottom:18px;
                    "
                >
                    <span style="color:#ff3b3b;">
                        RED
                    </span><span style="color:#7cff4b;">
                        ALIEN
                    </span>
                </div>

                <h2
                    style="
                        margin:0 0 16px;
                        color:#ffffff;
                    "
                >
                    Reset your password
                </h2>

                <p
                    style="
                        color:#b7c0b9;
                        line-height:1.7;
                    "
                >
                    Hello ' . $safeName . ',
                    we received a request to reset your
                    RedAlien password.
                </p>

                <p
                    style="
                        color:#b7c0b9;
                        line-height:1.7;
                    "
                >
                    Click the button below to create a new password.
                </p>

                <p style="margin:28px 0;">

                    <a
                        href="' . $safeUrl . '"
                        style="
                            display:inline-block;
                            background:#7cff4b;
                            color:#071108;
                            padding:13px 24px;
                            border-radius:30px;
                            text-decoration:none;
                            font-weight:bold;
                        "
                    >
                        Reset Password
                    </a>

                </p>

                <p
                    style="
                        color:#78847b;
                        font-size:13px;
                        line-height:1.6;
                    "
                >
                    This reset link expires in 30 minutes.
                    If you did not request a password reset,
                    you can safely ignore this email.
                </p>

            </div>
        ';


        $sent =
            Mailer::send(
                $email,
                $user['full_name']
                    ?? '',
                'Reset your RedAlien password',
                $htmlBody
            );


        if (!$sent) {

            $data['errors']['general'] =
                'The reset email could not be sent. Please try again.';

            $this->authView(
                'auth/forgot-password',
                $data
            );

            return;
        }


        Flash::success(
            'If an account exists with that email address, a password reset link has been sent.'
        );


        $this->redirect(
            'auth/forgotPassword'
        );
    }


    $this->authView(
        'auth/forgot-password',
        $data
    );
}


/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
*/

public function resetPassword(
    $token = null
): void {

    Auth::requireGuest();


    $token =
        trim(
            (string) $token
        );


    if ($token === '') {

        Flash::error(
            'The password reset link is invalid.'
        );

        $this->redirect(
            'auth/forgotPassword'
        );
    }


    $tokenHash =
        hash(
            'sha256',
            $token
        );


    $reset =
        $this->passwordResetModel
            ->findValidByTokenHash(
                $tokenHash
            );


    if (!$reset) {

        Flash::error(
            'This password reset link is invalid or has expired.'
        );

        $this->redirect(
            'auth/forgotPassword'
        );
    }


    $data = [
        'title' => 'Reset Password',
        'errors' => [],
        'token' => $token
    ];


    if (
        $_SERVER['REQUEST_METHOD'] ===
        'POST'
    ) {

        $password =
            $_POST['password']
            ?? '';

        $confirmPassword =
            $_POST['confirm_password']
            ?? '';


        if ($password === '') {

            $data['errors']['password'] =
                'Please enter a new password.';

        } elseif (
            strlen($password) < 8
        ) {

            $data['errors']['password'] =
                'Your password must contain at least 8 characters.';
        }


        if ($confirmPassword === '') {

            $data['errors']['confirm_password'] =
                'Please confirm your new password.';

        } elseif (
            $password !==
            $confirmPassword
        ) {

            $data['errors']['confirm_password'] =
                'The passwords do not match.';
        }


        if (empty($data['errors'])) {

            $updated =
                $this->userModel
                    ->updatePassword(
                        (int) $reset['user_id'],
                        $password
                    );


            if (!$updated) {

                $data['errors']['general'] =
                    'Your password could not be updated. Please try again.';

            } else {

                $this->passwordResetModel
                    ->markUsed(
                        (int) $reset['id']
                    );


                Flash::success(
                    'Your password has been reset successfully. You can now sign in.'
                );


                $this->redirect(
                    'auth/login'
                );
            }
        }
    }


    $this->authView(
        'auth/reset-password',
        $data
    );
}
}