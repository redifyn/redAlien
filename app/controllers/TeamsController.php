<?php

class TeamsController extends Controller
{
    private Team $teamModel;
    
    private AlienInvitation $alienInvitationModel;

    public function __construct()
    {
        Auth::requireLogin();

        $this->teamModel = $this->model('Team');
        $this->alienInvitationModel = $this->model('AlienInvitation');
    }


    /*
    |--------------------------------------------------------------------------
    | My Teams / Aliens
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $userId = (int) $_SESSION['user_id'];

        $aliens = $this->teamModel->getUserAliens($userId);

        $this->view('teams/index', [
            'title' => 'Teams / Aliens',
            'aliens' => $aliens
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create Alien Page
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        $this->view('teams/create', [
            'title' => 'Create Alien'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Store Alien
    |--------------------------------------------------------------------------
    */

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('teams');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $visibility = trim(
            $_POST['visibility'] ?? 'private'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Name
        |--------------------------------------------------------------------------
        */

        if ($name === '') {
            Flash::error('Please enter an Alien name.');
            $this->redirect('teams/create');
        }

        if (strlen($name) < 3) {
            Flash::error(
                'Alien name must contain at least 3 characters.'
            );

            $this->redirect('teams/create');
        }

        if (strlen($name) > 100) {
            Flash::error(
                'Alien name cannot exceed 100 characters.'
            );

            $this->redirect('teams/create');
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Visibility
        |--------------------------------------------------------------------------
        */

        $allowedVisibility = [
            'public',
            'private'
        ];

        if (!in_array(
            $visibility,
            $allowedVisibility,
            true
        )) {
            $visibility = 'private';
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Unique Slug
        |--------------------------------------------------------------------------
        */

        $slug = $this->generateSlug($name);


        /*
        |--------------------------------------------------------------------------
        | Generate Invite Code
        |--------------------------------------------------------------------------
        */

        $inviteCode = $this->generateInviteCode();


        /*
        |--------------------------------------------------------------------------
        | Upload Alien Logo
        |--------------------------------------------------------------------------
        */

        $logo = $this->uploadLogo();


        /*
        |--------------------------------------------------------------------------
        | Create Alien
        |--------------------------------------------------------------------------
        */

        $teamId = $this->teamModel->createAlien([
            'owner_id' => (int) $_SESSION['user_id'],
            'name' => $name,
            'slug' => $slug,
            'description' => $description ?: null,
            'logo' => $logo,
            'visibility' => $visibility,
            'invite_code' => $inviteCode
        ]);


        /*
        |--------------------------------------------------------------------------
        | Check Result
        |--------------------------------------------------------------------------
        */

        if (!$teamId) {
            Flash::error(
                'Something went wrong while creating your Alien.'
            );

            $this->redirect('teams/create');
        }


        Flash::success(
            'Alien created successfully. Your crew space is ready.'
        );

        $this->redirect(
            'teams/show/' . $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Alien
    |--------------------------------------------------------------------------
    */

    public function show($id = null): void
        {
            $teamId = (int) $id;

            if ($teamId <= 0) {
                Flash::error('Alien not found.');
                $this->redirect('teams');
            }

            $alien = $this->teamModel->findByIdForUser(
                $teamId,
                (int) $_SESSION['user_id']
            );

            if (!$alien) {
                Flash::error(
                    'You do not have access to this Alien.'
                );

                $this->redirect('teams');
            }

            $pods = $this->teamModel->getAlienPods($teamId);
            $crew = $this->teamModel->getAlienCrew($teamId);
            $messageCount = $this->teamModel->countAlienMessages($teamId);

            $this->view('teams/show', [
                'title' => $alien['name'],
                'alien' => $alien,
                'pods' => $pods,
                'crew' => $crew,
                'messageCount' => $messageCount
            ]);
        }




        public function inviteCrewByEmail(
            $teamId = null
        ): void {

    /*
    |--------------------------------------------------------------------------
    | POST Only
    |--------------------------------------------------------------------------
    */

    if (
        $_SERVER['REQUEST_METHOD']
        !== 'POST'
    ) {

        $this->redirect('teams');
    }


    $teamId =
        (int) $teamId;

    $userId =
        (int) (
            $_SESSION['user_id']
            ?? 0
        );


    /*
    |--------------------------------------------------------------------------
    | Validate Alien
    |--------------------------------------------------------------------------
    */

    if (
        $teamId <= 0 ||
        $userId <= 0
    ) {

        Flash::error(
            'Alien not found.'
        );

        $this->redirect('teams');
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Current User Has Access
    |--------------------------------------------------------------------------
    */

    $alien =
        $this->teamModel
            ->findByIdForUser(
                $teamId,
                $userId
            );


    if (!$alien) {

        Flash::error(
            'You do not have access to this Alien.'
        );

        $this->redirect('teams');
    }


    /*
    |--------------------------------------------------------------------------
    | Permission
    |--------------------------------------------------------------------------
    */

    if (
        !$this->teamModel
            ->canManageAlien(
                $teamId,
                $userId
            )
    ) {

        Flash::error(
            'You do not have permission to invite crew members.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    $email =
        strtolower(
            trim(
                $_POST['email']
                ?? ''
            )
        );


    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        Flash::error(
            'Please enter a valid email address.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Pending Invitation
    |--------------------------------------------------------------------------
    */

    $existingInvitation =
        $this->alienInvitationModel
            ->findPendingByEmail(
                $teamId,
                $email
            );


    if ($existingInvitation) {

        Flash::error(
            'A pending invitation has already been sent to this email address.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Secure Invitation Token
    |--------------------------------------------------------------------------
    */

    try {

        $token =
            bin2hex(
                random_bytes(32)
            );

    } catch (Throwable $exception) {

        error_log(
            'Alien invitation token error: ' .
            $exception->getMessage()
        );

        Flash::error(
            'The invitation could not be created.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Invitation Expires In 7 Days
    |--------------------------------------------------------------------------
    */

    $expiresAt =
        date(
            'Y-m-d H:i:s',
            strtotime('+7 days')
        );


    /*
    |--------------------------------------------------------------------------
    | Store Invitation
    |--------------------------------------------------------------------------
    */

    $invitationId =
        $this->alienInvitationModel
            ->createInvitation(
                $teamId,
                $userId,
                $email,
                $token,
                $expiresAt
            );


    if (!$invitationId) {

        Flash::error(
            'The invitation could not be created.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Acceptance Link
    |--------------------------------------------------------------------------
    */

    $acceptUrl =
    BASE_URL .
    '/invitations/accept/' .
    urlencode($token);

    /*
    |--------------------------------------------------------------------------
    | Alien Details
    |--------------------------------------------------------------------------
    */

    $alienName =
        htmlspecialchars(
            $alien['name']
            ?? 'RedAlien Crew',
            ENT_QUOTES,
            'UTF-8'
        );

    $inviteCode =
        htmlspecialchars(
            $alien['invite_code']
            ?? '',
            ENT_QUOTES,
            'UTF-8'
        );


    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    $subject =
        'You have been invited to join ' .
        ($alien['name'] ?? 'an Alien') .
        ' on RedAlien';


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
                    color:#7cff4b;
                    font-size:13px;
                    font-weight:bold;
                    letter-spacing:2px;
                    margin-bottom:10px;
                "
            >
               <span
        style="
            color:#ff3b3b;
            text-shadow:0 0 8px rgba(255,59,59,.45);
        "
    >RED</span><span
        style="
            color:#7cff4b;
            text-shadow:0 0 8px rgba(124,255,75,.35);
        "
    >ALIEN</span>
            </div>

            <h2
                style="
                    margin:0 0 16px;
                    color:#ffffff;
                "
            >
                You have been invited aboard

                <span
                    style="
                        color:#7cff4b;
                        font-size:28px;
                        text-shadow:
                            0 0 6px #7cff4b,
                            0 0 12px rgba(124,255,75,.8),
                            0 0 20px rgba(124,255,75,.5);
                    "
                >
                    👽
                </span>
            </h2>

            <p
                style="
                    color:#b7c0b9;
                    line-height:1.7;
                "
            >
                You have been invited to join
                <strong style="color:#ffffff;">
                    ' . $alienName . '
                </strong>
                on RedAlien.
            </p>

            <div
                style="
                    margin:24px 0;
                    padding:16px;
                    background:#0d1a0f;
                    border:1px solid #243528;
                    border-radius:10px;
                "
            >

                <div
                    style="
                        color:#87938a;
                        font-size:12px;
                        margin-bottom:6px;
                    "
                >
                    ALIEN INVITE CODE
                </div>

                <strong
                    style="
                        color:#7cff4b;
                        font-size:20px;
                        letter-spacing:2px;
                    "
                >
                    ' . $inviteCode . '
                </strong>

            </div>

            <p style="margin:28px 0;">

                <a
                    href="' .
                    htmlspecialchars(
                        $acceptUrl,
                        ENT_QUOTES,
                        'UTF-8'
                    ) .
                    '"
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
                    Join Alien
                </a>

            </p>

            <p
                style="
                    color:#78847b;
                    font-size:13px;
                    line-height:1.6;
                "
            >
                This invitation expires in 7 days.
                If you were not expecting this invitation,
                you can ignore this email.
            </p>

        </div>
    ';


    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    $sent =
        Mailer::send(
            $email,
            '',
            $subject,
            $htmlBody
        );


    if (!$sent) {

        /*
        | The invitation remains in the database.
        | We'll improve failed-email handling later.
        */

        Flash::error(
            'The invitation was created, but the email could not be sent.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    Flash::success(
        'Crew invitation sent successfully to ' .
        $email .
        '.'
    );


    $this->redirect(
        'teams/show/' .
        $teamId
    );
}


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);

        $slug = preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $slug
        );

        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'alien';
        }

        $originalSlug = $slug;

        $counter = 1;

        while ($this->teamModel->slugExists($slug)) {

            $slug =
                $originalSlug .
                '-' .
                $counter;

            $counter++;
        }

        return $slug;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Invite Code
    |--------------------------------------------------------------------------
    */

    private function generateInviteCode(): string
    {
        do {

            $code =
                'RA-' .
                strtoupper(
                    bin2hex(
                        random_bytes(3)
                    )
                );

        } while (
            $this->teamModel->inviteCodeExists($code)
        );

        return $code;
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Alien Logo
    |--------------------------------------------------------------------------
    */

    private function uploadLogo(): ?string
    {
        if (
            empty($_FILES['logo']) ||
            empty($_FILES['logo']['name'])
        ) {
            return null;
        }


        if (
            $_FILES['logo']['error']
            !== UPLOAD_ERR_OK
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Maximum Size: 2MB
        |--------------------------------------------------------------------------
        */

        if ($_FILES['logo']['size'] > 2097152) {

            Flash::error(
                'Alien logo must not exceed 2MB.'
            );

            $this->redirect('teams/create');
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Actual Image MIME Type
        |--------------------------------------------------------------------------
        */

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];


        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->file(
            $_FILES['logo']['tmp_name']
        );


        if (!isset($allowedMimeTypes[$mimeType])) {

            Flash::error(
                'Logo must be JPG, PNG or WEBP.'
            );

            $this->redirect('teams/create');
        }


        $extension =
            $allowedMimeTypes[$mimeType];


        /*
        |--------------------------------------------------------------------------
        | Generate Safe Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'alien_' .
            bin2hex(random_bytes(10)) .
            '.' .
            $extension;


        /*
        |--------------------------------------------------------------------------
        | Upload Directory
        |--------------------------------------------------------------------------
        */

        $uploadDirectory =
            dirname(__DIR__, 2) .
            '/public/assets/images/teams/';


        if (!is_dir($uploadDirectory)) {

            mkdir(
                $uploadDirectory,
                0755,
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Move Image
        |--------------------------------------------------------------------------
        */

        $destination =
            $uploadDirectory .
            $filename;


        if (!move_uploaded_file(
            $_FILES['logo']['tmp_name'],
            $destination
        )) {

            Flash::error(
                'Alien logo could not be uploaded.'
            );

            $this->redirect('teams/create');
        }


        return
            'assets/images/teams/' .
            $filename;
    }


    public function joinByInviteCode(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        $this->redirect('teams');

    }

    $inviteCode =
        trim(
            $_POST['invite_code'] ?? ''
        );

    if ($inviteCode === '') {

        Flash::error(
            'Please enter an invite code.'
        );

        $this->redirect('teams');

    }

    $team =
        $this->teamModel
            ->findByInviteCode(
                strtoupper($inviteCode)
            );

    if (!$team) {

        Flash::error(
            'Alien invite code not found.'
        );

        $this->redirect('teams');

    }

    $userId =
        (int) $_SESSION['user_id'];

    $joined =
        $this->teamModel
            ->joinAlien(
                (int) $team['id'],
                $userId
            );

    if (!$joined) {

        Flash::error(
            'Unable to join Alien.'
        );

        $this->redirect('teams');

    }

    Flash::success(
        'You successfully joined "' .
        $team['name'] .
        '".'
    );

    $this->redirect(
        'teams/show/' .
        $team['id']
    );
}



/*
|--------------------------------------------------------------------------
| Remove Crew Member
|--------------------------------------------------------------------------
*/

public function removeCrew(
    $teamId = null,
    $userId = null
): void {

    /*
    |--------------------------------------------------------------------------
    | POST Only
    |--------------------------------------------------------------------------
    */

    if (
        $_SERVER['REQUEST_METHOD']
        !== 'POST'
    ) {
        $this->redirect('teams');
    }


    $teamId =
        (int) $teamId;

    $targetUserId =
        (int) $userId;

    $currentUserId =
        (int) (
            $_SESSION['user_id']
            ?? 0
        );


    /*
    |--------------------------------------------------------------------------
    | Validate Request
    |--------------------------------------------------------------------------
    */

    if (
        $teamId <= 0 ||
        $targetUserId <= 0 ||
        $currentUserId <= 0
    ) {

        Flash::error(
            'Invalid crew removal request.'
        );

        $this->redirect('teams');
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Alien Access
    |--------------------------------------------------------------------------
    */

    $alien =
        $this->teamModel
            ->findByIdForUser(
                $teamId,
                $currentUserId
            );


    if (!$alien) {

        Flash::error(
            'You do not have access to this Alien.'
        );

        $this->redirect('teams');
    }


    /*
    |--------------------------------------------------------------------------
    | Find Target Crew Member
    |--------------------------------------------------------------------------
    */

    $crewMember =
        $this->teamModel
            ->getCrewMember(
                $teamId,
                $targetUserId
            );


    if (!$crewMember) {

        Flash::error(
            'Crew member not found.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Permission Check
    |--------------------------------------------------------------------------
    */

    if (
        !$this->teamModel
            ->canRemoveCrewMember(
                $teamId,
                $currentUserId,
                $targetUserId
            )
    ) {

        Flash::error(
            'You do not have permission to remove this crew member.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Member
    |--------------------------------------------------------------------------
    */

    $removed =
        $this->teamModel
            ->removeCrewMember(
                $teamId,
                $targetUserId
            );


    if (!$removed) {

        Flash::error(
            'The crew member could not be removed.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    Flash::success(
        ($crewMember['full_name'] ?? 'Crew member') .
        ' has been removed from this Alien.'
    );


    $this->redirect(
        'teams/show/' .
        $teamId
    );
}


/*
|--------------------------------------------------------------------------
| Block Crew Member
|--------------------------------------------------------------------------
*/

public function blockCrew(
    $teamId = null,
    $userId = null
): void {

    if (
        $_SERVER['REQUEST_METHOD']
        !== 'POST'
    ) {
        $this->redirect('teams');
    }


    $teamId =
        (int) $teamId;

    $targetUserId =
        (int) $userId;

    $currentUserId =
        (int) (
            $_SESSION['user_id']
            ?? 0
        );


    if (
        $teamId <= 0 ||
        $targetUserId <= 0 ||
        $currentUserId <= 0
    ) {

        Flash::error(
            'Invalid crew block request.'
        );

        $this->redirect('teams');
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Target Exists
    |--------------------------------------------------------------------------
    */

    $crewMember =
        $this->teamModel
            ->getCrewMember(
                $teamId,
                $targetUserId
            );


    if (!$crewMember) {

        Flash::error(
            'Crew member not found.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Same Permission Hierarchy As Removal
    |--------------------------------------------------------------------------
    */

    if (
        !$this->teamModel
            ->canRemoveCrewMember(
                $teamId,
                $currentUserId,
                $targetUserId
            )
    ) {

        Flash::error(
            'You do not have permission to block this crew member.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Optional Reason
    |--------------------------------------------------------------------------
    */

    $reason =
        trim(
            $_POST['reason']
            ?? ''
        );

    if (
        mb_strlen($reason) > 255
    ) {
        $reason =
            mb_substr(
                $reason,
                0,
                255
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Block
    |--------------------------------------------------------------------------
    */

    $blocked =
        $this->teamModel
            ->blockCrewMember(
                $teamId,
                $targetUserId,
                $currentUserId,
                $reason !== ''
                    ? $reason
                    : null
            );


    if (!$blocked) {

        Flash::error(
            'The crew member could not be blocked.'
        );

        $this->redirect(
            'teams/show/' .
            $teamId
        );
    }


    Flash::success(
        ($crewMember['full_name'] ?? 'Crew member') .
        ' has been blocked from this Alien.'
    );


    $this->redirect(
        'teams/show/' .
        $teamId
    );
}

}