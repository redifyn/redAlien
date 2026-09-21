<?php


class InvitationsController extends Controller
{
    private AlienInvitation $invitationModel;
    private Team $teamModel;
    private User $userModel;


    public function __construct()
    {
        $this->invitationModel =
            $this->model(
                'AlienInvitation'
            );

        $this->teamModel =
            $this->model(
                'Team'
            );

        $this->userModel =
            $this->model(
                'User'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Accept Alien Invitation
    |--------------------------------------------------------------------------
    */

    public function accept(
        $token = null
    ): void {

        $token =
            trim(
                (string) $token
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Token
        |--------------------------------------------------------------------------
        */

        if ($token === '') {

            Flash::error(
                'The Alien invitation link is invalid.'
            );

            $this->redirect(
                'auth/login'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Invitation
        |--------------------------------------------------------------------------
        */

        $invitation =
            $this->invitationModel
                ->findByToken(
                    $token
                );


        if (!$invitation) {

            Flash::error(
                'This Alien invitation could not be found.'
            );

            $this->redirect(
                'auth/login'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invitation Must Still Be Pending
        |--------------------------------------------------------------------------
        */

        if (
            (
                $invitation['status']
                ?? ''
            ) !== 'pending'
        ) {

            Flash::error(
                'This Alien invitation is no longer active.'
            );

            $this->redirect(
                'home/index'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Expiration
        |--------------------------------------------------------------------------
        */

        $expiresAt =
            strtotime(
                $invitation[
                    'expires_at'
                ]
                ?? ''
            );


        if (
            !$expiresAt ||
            $expiresAt <= time()
        ) {

            Flash::error(
                'This Alien invitation has expired.'
            );

            $this->redirect(
                'auth/login'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User Not Logged In
        |--------------------------------------------------------------------------
        |
        | Remember the invitation and send the recipient to login.
        |
        */

        $userId =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );


        if ($userId <= 0) {

            $_SESSION[
                'pending_alien_invite_token'
            ] =
                $token;


            $_SESSION[
                'pending_alien_invite_email'
            ] =
                strtolower(
                    $invitation['email']
                    ?? ''
                );


            Flash::success(
                'Sign in or create a RedAlien account to accept your crew invitation.'
            );


            $this->redirect(
                'auth/login'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Find Logged-In User
        |--------------------------------------------------------------------------
        */

            $user =
        $this->userModel
            ->find(
                $userId
            );


        if (!$user) {

            Flash::error(
                'Your RedAlien account could not be found.'
            );

            $this->redirect(
                'auth/login'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invitation Must Match Logged-In Email
        |--------------------------------------------------------------------------
        */

        $invitedEmail =
            strtolower(
                trim(
                    $invitation['email']
                    ?? ''
                )
            );


        $loggedInEmail =
            strtolower(
                trim(
                    $user['email']
                    ?? ''
                )
            );


        if (
            $invitedEmail === '' ||
            $loggedInEmail !==
                $invitedEmail
        ) {

            Flash::error(
                'This invitation was sent to a different email address.'
            );

            $this->redirect(
                'home/index'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Join Alien
        |--------------------------------------------------------------------------
        */

        $teamId =
            (int) (
                $invitation['team_id']
                ?? 0
            );


        if ($teamId <= 0) {

            Flash::error(
                'The Alien attached to this invitation could not be found.'
            );

            $this->redirect(
                'home/index'
            );
        }


        $joined =
            $this->teamModel
                ->joinAlien(
                    $teamId,
                    $userId
                );


        if (!$joined) {

            Flash::error(
                'RedAlien could not add you to this Alien.'
            );

            $this->redirect(
                'home/index'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mark Invitation Accepted
        |--------------------------------------------------------------------------
        */

        $this->invitationModel
            ->markAccepted(
                (int) $invitation['id']
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Pending Invitation
        |--------------------------------------------------------------------------
        */

        unset(
            $_SESSION[
                'pending_alien_invite_token'
            ],
            $_SESSION[
                'pending_alien_invite_email'
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        Flash::success(
            'Welcome aboard! You successfully joined "' .
            (
                $invitation['alien_name']
                ?? 'the Alien'
            ) .
            '".'
        );


        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }
}