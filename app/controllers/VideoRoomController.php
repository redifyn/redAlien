<?php

class VideoRoomController extends Controller
{
    private $channelModel;
    private $videoSignalModel;
    private $teamModel;
  

    public function __construct()
        {
            $this->channelModel =
                $this->model('Channel');

            $this->videoSignalModel =
                $this->model('VideoSignal');

            $this->teamModel =
                $this->model('Team');
        }


    /*
    |--------------------------------------------------------------------------
    | Video Room Page
    |--------------------------------------------------------------------------
    */

    public function index($channelId = null): void
        {
            $channelId =
                (int) $channelId;

            $userId =
                (int) (
                    $_SESSION['user_id']
                    ?? 0
                );

            if (
                $channelId <= 0 ||
                $userId <= 0
            ) {

                Flash::error(
                    'Pod not found.'
                );

                $this->redirect(
                    'teams'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Confirm User Can Access Pod
            |--------------------------------------------------------------------------
            */

            $pod =
                $this->channelModel
                    ->findPodForUser(
                        $channelId,
                        $userId
                    );

            if (!$pod) {

                Flash::error(
                    'You do not have access to this Pod.'
                );

                $this->redirect(
                    'teams'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Meeting Permission
            |--------------------------------------------------------------------------
            */

            $teamId =
                (int) (
                    $pod['team_id']
                    ?? 0
                );

            $canStartMeeting =
                $teamId > 0 &&
                $this->teamModel
                    ->canStartMeeting(
                        $teamId,
                        $userId
                    );


            /*
            |--------------------------------------------------------------------------
            | Load Video Room
            |--------------------------------------------------------------------------
            */

            $this->view(
                'video-room/index',
                [
                    'pageTitle' =>
                        'RedAlien Live',

                    'channelId' =>
                        $channelId,

                    'canStartMeeting' =>
                        $canStartMeeting
                ]
            );
        }


    /*
    |--------------------------------------------------------------------------
    | Start Meeting
    |--------------------------------------------------------------------------
    */

    public function start(): void
    {
        header(
            'Content-Type: application/json'
        );

        if (
            $_SERVER['REQUEST_METHOD'] !==
            'POST'
        ) {
            http_response_code(405);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Method not allowed.'
            ]);

            exit;
        }

        $channelId =
            (int) (
                $_POST['channel_id']
                ?? 0
            );

        $userId =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );

        if ($userId <= 0) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' =>
                    'You must be logged in.'
            ]);

            exit;
        }

        if ($channelId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Pod not found.'
            ]);

            exit;
        }

        $pod =
            $this->channelModel
                ->findPodForUser(
                    $channelId,
                    $userId
                );

        if (!$pod) {
            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' =>
                    'You do not have access to this Pod.'
            ]);

            exit;
        }


        $teamId =
    (int) (
        $pod['team_id']
        ?? 0
    );

if (
    $teamId <= 0 ||
    !$this->teamModel
        ->canStartMeeting(
            $teamId,
            $userId
        )
) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' =>
            'You do not have permission to start meetings in this Alien.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Register Host As Meeting Participant
|--------------------------------------------------------------------------
*/
        $started =
    $this->channelModel
        ->startMeeting(
            $channelId,
            $userId
        );

        if (!$started) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' =>
                    'The meeting could not be started.'
            ]);

            exit;
        }

        echo json_encode([
            'success' => true,
            'message' =>
                'Meeting started.',
            'channel_id' =>
                $channelId,
            'started_by' =>
                $userId
        ]);

        exit;
    }


    /*
|--------------------------------------------------------------------------
| Meeting Status
|--------------------------------------------------------------------------
*/

public function status($channelId = null): void
    {
        header(
            'Content-Type: application/json'
        );

        if (
            $_SERVER['REQUEST_METHOD'] !== 'GET'
        ) {
            http_response_code(405);

            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed.'
            ]);

            exit;
        }

        $channelId =
            (int) $channelId;

        $userId =
            (int) (
                $_SESSION['user_id']
                ?? 0
            );

        if ($channelId <= 0) {

            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Invalid Pod.'
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Confirm User Belongs To Pod
        |--------------------------------------------------------------------------
        */

        $pod =
            $this->channelModel
                ->findPodForUser(
                    $channelId,
                    $userId
                );

        if (!$pod) {

            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' => 'Access denied.'
            ]);

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Meeting Status
        |--------------------------------------------------------------------------
        */

        $meeting =
            $this->channelModel
                ->getMeetingStatus(
                    $channelId
                );

        if (!$meeting) {

            echo json_encode([
                'success' => true,
                'meeting_active' => false
            ]);

            exit;
        }

        echo json_encode([
            'success' => true,

            'meeting_active' =>
                (bool) (
                    $meeting['meeting_active']
                    ?? 0
                ),

            'meeting_started_by' =>
                (int) (
                    $meeting['meeting_started_by']
                    ?? 0
                ),

            'meeting_started_at' =>
                $meeting['meeting_started_at']
                ?? null
        ]);

        exit;
    }

    /*
|--------------------------------------------------------------------------
| Stop Meeting
|--------------------------------------------------------------------------
*/

public function stop(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $channelId =
        (int) ($_POST['channel_id'] ?? 0);

    $userId =
        (int) ($_SESSION['user_id'] ?? 0);

    if (
        $channelId <= 0 ||
        $userId <= 0
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid meeting.'
        ]);

        exit;
    }

    $pod =
        $this->channelModel
            ->findPodForUser(
                $channelId,
                $userId
            );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Access denied.'
        ]);

        exit;
    }

    $meeting =
        $this->channelModel
            ->getMeetingStatus(
                $channelId
            );

    /*
    |--------------------------------------------------------------------------
    | Only The User Who Started It Can End It
    |--------------------------------------------------------------------------
    */

    if (
        !$meeting ||
        (int) ($meeting['meeting_started_by'] ?? 0)
            !== $userId
    ) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' =>
                'Only the meeting host can end this meeting.'
        ]);

        exit;
    }

    $stopped =
        $this->channelModel
            ->stopMeeting(
                $channelId
            );

    if (!$stopped) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
                'The meeting could not be stopped.'
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Meeting ended.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Send WebRTC Signal
|--------------------------------------------------------------------------
*/

public function signal(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $channelId =
        (int) ($_POST['channel_id'] ?? 0);

    $receiverId =
        isset($_POST['receiver_id'])
            ? (int) $_POST['receiver_id']
            : null;

    $signalType =
        trim($_POST['signal_type'] ?? '');

    $signalData =
        trim($_POST['signal_data'] ?? '');

    $userId =
        (int) ($_SESSION['user_id'] ?? 0);

    if (
        $channelId <= 0 ||
        $userId <= 0
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid signaling request.'
        ]);

        exit;
    }

    if (
        !in_array(
            $signalType,
            ['offer', 'answer', 'ice'],
            true
        )
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid signal type.'
        ]);

        exit;
    }

    if ($signalData === '') {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Signal data is required.'
        ]);

        exit;
    }

    $pod =
        $this->channelModel
            ->findPodForUser(
                $channelId,
                $userId
            );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Access denied.'
        ]);

        exit;
    }

    $signalId =
        $this->videoSignalModel
            ->createSignal([
                'channel_id' =>
                    $channelId,

                'sender_id' =>
                    $userId,

                'receiver_id' =>
                    $receiverId,

                'signal_type' =>
                    $signalType,

                'signal_data' =>
                    $signalData
            ]);

    if (!$signalId) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
                'Signal could not be saved.'
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'signal_id' => $signalId
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get Pending WebRTC Signals
|--------------------------------------------------------------------------
*/

public function signals($channelId = null): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $channelId =
        (int) $channelId;

    $userId =
        (int) ($_SESSION['user_id'] ?? 0);

    if (
        $channelId <= 0 ||
        $userId <= 0
    ) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid signaling request.'
        ]);

        exit;
    }

    $pod =
        $this->channelModel
            ->findPodForUser(
                $channelId,
                $userId
            );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Access denied.'
        ]);

        exit;
    }

    $signals =
        $this->videoSignalModel
            ->getPendingSignals(
                $channelId,
                $userId
            );

    $formattedSignals = [];

    foreach ($signals as $signal) {

        $formattedSignals[] = [
            'id' =>
                (int) $signal['id'],

            'sender_id' =>
                (int) $signal['sender_id'],

            'receiver_id' =>
                $signal['receiver_id'] !== null
                    ? (int) $signal['receiver_id']
                    : null,

            'signal_type' =>
                $signal['signal_type'],

            'signal_data' =>
                $signal['signal_data']
        ];

        $this->videoSignalModel
            ->markProcessed(
                (int) $signal['id']
            );
    }

    echo json_encode([
        'success' => true,
        'signals' => $formattedSignals
    ]);

    exit;
}


    
}