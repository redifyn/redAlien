<?php
require_once APPROOT . '/services/FileUpload.php';
class MessagesController extends Controller
{
    private Message $messageModel;
    private Channel $channelModel;
    private TypingStatus $typingStatusModel;
    private User $userModel;

    public function __construct()
    {
        Auth::requireLogin();

        $this->messageModel = $this->model('Message');
        $this->channelModel = $this->model('Channel');
        $this->typingStatusModel = $this->model('TypingStatus');
        $this->userModel = $this->model('User');
    }


    public function send(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('teams');
    }

    $isAjax =
        strtolower(
            $_SERVER['HTTP_X_REQUESTED_WITH']
            ?? ''
        ) === 'xmlhttprequest';

    $channelId =
        (int) ($_POST['channel_id'] ?? 0);

    $message =
        trim($_POST['message'] ?? '');

    $userId =
        (int) ($_SESSION['user_id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Validate Pod
    |--------------------------------------------------------------------------
    */

    if ($channelId <= 0) {
        $this->respondWithError(
            'Pod not found.',
            $channelId,
            $isAjax
        );
    }

    $pod =
        $this->channelModel->findPodForUser(
            $channelId,
            $userId
        );

    if (!$pod) {
        $this->respondWithError(
            'You do not have access to this Pod.',
            $channelId,
            $isAjax
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attachment
    |--------------------------------------------------------------------------
    */

    $attachment = null;

    if (
        isset($_FILES['attachment']) &&
        ($_FILES['attachment']['error']
            ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ) {
        $uploader =
            (new FileUpload(
                PUBLICROOT .
                '/uploads/messages'
            ))
                ->setAllowedExtensions([
                    // Images
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'webp',

                    // Documents
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',

                    // Archives
                    'zip',

                    // Voice notes / audio
                    'webm',
                    'ogg',
                    'mp3',
                    'wav',
                    'm4a'
                ])
                ->setAllowedMimeTypes([
                    // Images
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',

                    // PDF
                    'application/pdf',

                    // Word
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                    // Excel
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                    // PowerPoint
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',

                    // Text
                    'text/plain',

                    // ZIP
                    'application/zip',
                    'application/x-zip-compressed',

                    // Voice notes / audio
                    'audio/webm',
                    'audio/ogg',
                    'audio/mpeg',
                    'audio/wav',
                    'audio/x-wav',
                    'audio/mp4',
                    'audio/x-m4a',

                    // Some browsers may report WebM recordings this way
                    'video/webm'
                ])
                ->setMaxFileSize(
                    10 * 1024 * 1024
                );

        $attachment =
            $uploader->upload(
                $_FILES['attachment']
            );

        if (!$attachment) {
            $this->respondWithError(
                implode(
                    ' ',
                    $uploader->getErrors()
                ),
                $channelId,
                $isAjax
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Message
    |--------------------------------------------------------------------------
    */

    if (
        $message === '' &&
        !$attachment
    ) {
        $this->respondWithError(
            'Please enter a transmission or attach a file.',
            $channelId,
            $isAjax
        );
    }

    if (
        mb_strlen($message) > 5000
    ) {
        $this->respondWithError(
            'A transmission cannot exceed 5000 characters.',
            $channelId,
            $isAjax
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Determine Message Type
    |--------------------------------------------------------------------------
    */

    $messageType = 'text';

    if ($attachment) {
        $attachmentMimeType =
            strtolower(
                (string) (
                    $attachment['mime_type']
                    ?? ''
                )
            );

        $attachmentExtension =
            strtolower(
                (string) (
                    $attachment['extension']
                    ?? ''
                )
            );

        $audioExtensions = [
            'webm',
            'ogg',
            'mp3',
            'wav',
            'm4a'
        ];

        if (
            str_starts_with(
                $attachmentMimeType,
                'image/'
            )
        ) {
            $messageType = 'image';

        } elseif (
            str_starts_with(
                $attachmentMimeType,
                'audio/'
            ) ||
            (
                $attachmentMimeType ===
                    'video/webm' &&
                $attachmentExtension ===
                    'webm'
            ) ||
            in_array(
                $attachmentExtension,
                $audioExtensions,
                true
            )
        ) {
            $messageType = 'audio';

        } else {
            $messageType = 'file';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create Transmission
    |--------------------------------------------------------------------------
    */

    $messageId =
        $this->messageModel
            ->createTransmission([
                'channel_id' =>
                    $channelId,

                'sender_id' =>
                    $userId,

                'parent_message_id' =>
                    null,

                'message' =>
                    $message,

                'attachment_path' =>
                    $attachment
                        ? (
                            'uploads/messages/' .
                            $attachment['filename']
                        )
                        : null,

                'attachment_name' =>
                    $attachment['original_name']
                    ?? null,

                'attachment_size' =>
                    $attachment['size']
                    ?? null,

                'attachment_mime' =>
                    $attachment['mime_type']
                    ?? null,

                'message_type' =>
                    $messageType
            ]);

    if (!$messageId) {
        $this->respondWithError(
            'The transmission could not be sent.',
            $channelId,
            $isAjax
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Stop Typing Status
    |--------------------------------------------------------------------------
    */

    $this->typingStatusModel
        ->stopTyping(
            $channelId,
            $userId
        );


    /*
    |--------------------------------------------------------------------------
    | Load Saved Transmission
    |--------------------------------------------------------------------------
    */

    $savedTransmission =
        $this->messageModel->findById(
            (int) $messageId
        );

    if (!$savedTransmission) {
        $this->respondWithError(
            'The saved transmission could not be loaded.',
            $channelId,
            $isAjax
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX Response
    |--------------------------------------------------------------------------
    */

    if ($isAjax) {
        header(
            'Content-Type: application/json'
        );

        echo json_encode([
            'success' =>
                true,

            'message' =>
                'Transmission sent.',

            'transmission' => [
                'id' =>
                    (int) $savedTransmission['id'],

                'channel_id' =>
                    (int) $savedTransmission['channel_id'],

                'sender_id' =>
                    (int) $savedTransmission['sender_id'],

                'sender_name' =>
                    $savedTransmission['sender_name']
                    ?? $savedTransmission['sender_username']
                    ?? 'User',

                'sender_avatar' =>
                    $savedTransmission['sender_avatar']
                    ?? null,

                'message' =>
                    $savedTransmission['message']
                    ?? '',

                'attachment_path' =>
                    $savedTransmission['attachment_path']
                    ?? null,

                'attachment_name' =>
                    $savedTransmission['attachment_name']
                    ?? null,

                'attachment_size' =>
                    (int) (
                        $savedTransmission['attachment_size']
                        ?? 0
                    ),

                'attachment_mime' =>
                    $savedTransmission['attachment_mime']
                    ?? null,

                'message_type' =>
                    $savedTransmission['message_type']
                    ?? 'text',

                'is_edited' =>
                    (int) (
                        $savedTransmission['is_edited']
                        ?? 0
                    ),

                'created_at' =>
                    $savedTransmission['created_at'],

                'formatted_time' =>
                    date(
                        'g:i A',
                        strtotime(
                            $savedTransmission['created_at']
                        )
                    )
            ]
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Standard Response
    |--------------------------------------------------------------------------
    */

    Flash::success(
        'Transmission sent.'
    );

    $this->redirect(
        'channels/show/' .
        $channelId
    );
}




     
    public function typing($channelId = null): void
     
     
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

    $channelId = (int) $channelId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    $isTyping =
        filter_var(
            $_POST['is_typing'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    $pod = $this->channelModel->findPodForUser(
        $channelId,
        $userId
    );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You do not have access to this Pod.'
        ]);

        exit;
    }

    $updated = $this->typingStatusModel->setTyping(
        $channelId,
        $userId,
        $isTyping
    );

    echo json_encode([
        'success' => $updated
    ]);

    exit;
}

public function typingStatus($channelId = null): void
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

    $channelId = (int) $channelId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    $pod = $this->channelModel->findPodForUser(
        $channelId,
        $userId
    );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You do not have access to this Pod.'
        ]);

        exit;
    }

    $typers = $this->typingStatusModel->getActiveTypers(
        $channelId,
        $userId
    );

    echo json_encode([
        'success' => true,
        'typers' => array_map(
            function (array $typer): array {
                return [
                    'user_id' => (int) $typer['user_id'],
                    'name' =>
                        $typer['full_name']
                        ?? $typer['username']
                        ?? 'A crew member'
                ];
            },
            $typers
        )
    ]);

    exit;
}


public function heartbeat(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        echo json_encode([
            'success' => false
        ]);

        exit;
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {

        http_response_code(401);

        echo json_encode([
            'success' => false
        ]);

        exit;
    }

    $this->userModel->updatePresence($userId);

    echo json_encode([
        'success' => true
    ]);

    exit;
}


public function offline(): void
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

    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Unauthenticated.'
        ]);

        exit;
    }

    $updated =
        $this->userModel->markOffline($userId);

    echo json_encode([
        'success' => $updated
    ]);

    exit;
}




public function update($messageId = null): void
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

    $messageId = (int) $messageId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($messageId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    if ($message === '') {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission cannot be empty.'
        ]);

        exit;
    }

    if (mb_strlen($message) > 5000) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'A transmission cannot exceed 5000 characters.'
        ]);

        exit;
    }

    $transmission =
        $this->messageModel->findTransmissionForUser(
            $messageId,
            $userId
        );

    if (!$transmission) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    if ((int) $transmission['sender_id'] !== $userId) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You can only edit your own transmission.'
        ]);

        exit;
    }

    $updated =
        $this->messageModel->updateTransmission(
            $messageId,
            $userId,
            $message
        );

    if (!$updated) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'The transmission could not be updated.'
        ]);

        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Transmission updated.',
        'transmission' => [
            'id' => $messageId,
            'message' => $message,
            'is_edited' => 1
        ]
    ]);

    exit;
}


public function delete($messageId = null): void
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

    $messageId = (int) $messageId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($messageId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    $transmission =
        $this->messageModel->findTransmissionForUser(
            $messageId,
            $userId
        );

    if (!$transmission) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    if ((int) $transmission['sender_id'] !== $userId) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You can only delete your own transmission.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Soft delete the transmission
    |--------------------------------------------------------------------------
    */

    $deleted =
        $this->messageModel->deleteTransmission(
            $messageId,
            $userId
        );

    if (!$deleted) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'The transmission could not be deleted.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete physical attachment (if any)
    |--------------------------------------------------------------------------
    */

    $attachmentPath =
        trim(
            $transmission['attachment_path']
            ?? ''
        );

    if ($attachmentPath !== '') {

        $fullPath =
            PUBLICROOT .
            '/' .
            ltrim(
                $attachmentPath,
                '/'
            );

        $uploadsRoot =
            realpath(
                PUBLICROOT .
                '/uploads/messages'
            );

        $resolvedFile =
            realpath(
                $fullPath
            );

        if (
            $uploadsRoot !== false &&
            $resolvedFile !== false &&
            str_starts_with(
                $resolvedFile,
                $uploadsRoot . DIRECTORY_SEPARATOR
            ) &&
            is_file($resolvedFile)
        ) {

            @unlink($resolvedFile);

        }

    }

    echo json_encode([
        'success' => true,
        'message' => 'Transmission deleted.',
        'message_id' => $messageId
    ]);

    exit;
}


public function react($messageId = null): void
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

    $messageId = (int) $messageId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');

    $allowedEmojis = [
        '👍',
        '❤️',
        '😂',
        '😮',
        '😢',
        '🚀'
    ];

    if ($messageId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    if (!in_array($emoji, $allowedEmojis, true)) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid reaction.'
        ]);

        exit;
    }

    $transmission =
        $this->messageModel->findTransmissionForUser(
            $messageId,
            $userId
        );

    if (!$transmission) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Transmission not found.'
        ]);

        exit;
    }

    $result =
        $this->messageModel->toggleReaction(
            $messageId,
            $userId,
            $emoji
        );

    if (!$result['success']) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'The reaction could not be updated.'
        ]);

        exit;
    }

    $reactions =
        $this->messageModel->getMessageReactions(
            $messageId,
            $userId
        );

    echo json_encode([
        'success' => true,
        'action' => $result['action'],
        'message_id' => $messageId,
        'reactions' => $reactions
    ]);

    exit;
}




public function reactions($channelId = null): void
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

    $channelId = (int) $channelId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    $pod = $this->channelModel->findPodForUser(
        $channelId,
        $userId
    );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You do not have access to this Pod.'
        ]);

        exit;
    }

    $reactions =
        $this->messageModel->getChannelReactions(
            $channelId,
            $userId
        );

    echo json_encode([
        'success' => true,
        'reactions' => $reactions
    ]);

    exit;
}


public function clear($channelId = null): void
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
        (int) $channelId;

    $userId =
        (int) ($_SESSION['user_id'] ?? 0);

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Confirm access to the Pod
    |--------------------------------------------------------------------------
    */

    $pod =
        $this->channelModel->findPodForUser(
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

    /*
    |--------------------------------------------------------------------------
    | Only the Pod creator can clear the conversation
    |--------------------------------------------------------------------------
    */

    if (
        (int) ($pod['created_by'] ?? 0) !==
        $userId
    ) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' =>
                'Only the Pod creator can clear this conversation.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Collect attachments before deleting the database messages
    |--------------------------------------------------------------------------
    */

    $attachments =
        $this->messageModel
            ->getPodAttachments(
                $channelId
            );

    /*
    |--------------------------------------------------------------------------
    | Clear all messages
    |--------------------------------------------------------------------------
    |
    | Reactions should also be removed automatically if their foreign key
    | uses ON DELETE CASCADE.
    |
    */

    $cleared =
        $this->messageModel->clearPod(
            $channelId
        );

    if (!$cleared) {
        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' =>
                'The conversation could not be cleared.'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete physical attachment files
    |--------------------------------------------------------------------------
    */

    $uploadsRoot =
        realpath(
            PUBLICROOT .
            '/uploads/messages'
        );

    if ($uploadsRoot !== false) {

        foreach ($attachments as $attachment) {

            $attachmentPath =
                trim(
                    $attachment['attachment_path']
                    ?? ''
                );

            if ($attachmentPath === '') {
                continue;
            }

            $fullPath =
                PUBLICROOT .
                '/' .
                ltrim(
                    $attachmentPath,
                    '/'
                );

            $resolvedFile =
                realpath(
                    $fullPath
                );

            /*
            |--------------------------------------------------------------------------
            | Only remove files located inside uploads/messages
            |--------------------------------------------------------------------------
            */

            if (
                $resolvedFile !== false &&
                str_starts_with(
                    $resolvedFile,
                    $uploadsRoot .
                    DIRECTORY_SEPARATOR
                ) &&
                is_file($resolvedFile)
            ) {
                @unlink($resolvedFile);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Clear typing records for this Pod
    |--------------------------------------------------------------------------
    */

    $this->typingStatusModel
        ->clearChannel(
            $channelId
        );

    echo json_encode([
        'success' => true,
        'message' =>
            'The conversation has been cleared.',
        'channel_id' =>
            $channelId
    ]);

    exit;
}


    private function respondWithError(
        string $message,
        int $channelId,
        bool $isAjax
    ): void {
        if ($isAjax) {
            http_response_code(422);
            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'message' => $message
            ]);

            exit;
        }

        Flash::error($message);

        if ($channelId > 0) {
            $this->redirect(
                'channels/show/' . $channelId
            );
        }

        $this->redirect('teams');
    }


    
public function latest($channelId = null): void
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

    $channelId = (int) $channelId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $afterMessageId = (int) ($_GET['after_id'] ?? 0);

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    $pod = $this->channelModel->findPodForUser(
        $channelId,
        $userId
    );

    if (!$pod) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'You do not have access to this Pod.'
        ]);

        exit;
    }

    $transmissions =
        $this->messageModel->getNewTransmissions(
            $channelId,
            $afterMessageId
        );

    $formattedTransmissions = array_map(
        function (array $transmission): array {

            return [
                'id' => (int) $transmission['id'],

                'channel_id' =>
                    (int) $transmission['channel_id'],

                'sender_id' =>
                    (int) $transmission['sender_id'],

                'sender_name' =>
                    $transmission['sender_name']
                    ?? $transmission['sender_username']
                    ?? 'User',

                'sender_avatar' =>
                    $transmission['sender_avatar']
                    ?? null,

                'message' =>
                    $transmission['message']
                    ?? '',

                'message_type' =>
                    $transmission['message_type']
                    ?? 'text',

                'parent_message_id' =>
                    $transmission['parent_message_id']
                    ? (int) $transmission['parent_message_id']
                    : null,

                'parent_message' =>
                    $transmission['parent_message']
                    ?? null,

                'parent_sender_name' =>
                    $transmission['parent_sender_name']
                    ?? null,

                'attachment_path' =>
                    $transmission['attachment_path']
                    ?? null,

                'attachment_name' =>
                    $transmission['attachment_name']
                    ?? null,

                'attachment_size' =>
                    (int) (
                        $transmission['attachment_size']
                        ?? 0
                    ),

                'attachment_mime' =>
                    $transmission['attachment_mime']
                    ?? null,

                'is_edited' =>
                    (int) ($transmission['is_edited'] ?? 0),

                'created_at' =>
                    $transmission['created_at'],

                'formatted_time' => date(
                    'g:i A',
                    strtotime($transmission['created_at'])
                )
            ];

        },
        $transmissions
    );

    echo json_encode([
        'success' => true,
        'transmissions' => $formattedTransmissions
    ]);

    exit;
}


public function crewPresence($teamId = null): void
{
    header('Content-Type: application/json');

    $teamId = (int) $teamId;

    if ($teamId <= 0) {

        echo json_encode([
            'success' => false
        ]);

        exit;
    }

    $crew =
        $this->userModel->getAlienCrewPresence(
            $teamId
        );

    echo json_encode([
        'success' => true,
        'crew' => $crew
    ]);

    exit;
}



public function changes($channelId = null): void
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

        $channelId = (int) $channelId;
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $afterUpdatedAt =
            trim($_GET['after_updated_at'] ?? '');

        if ($channelId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Pod not found.'
            ]);

            exit;
        }

        $pod = $this->channelModel->findPodForUser(
            $channelId,
            $userId
        );

        if (!$pod) {
            http_response_code(403);

            echo json_encode([
                'success' => false,
                'message' => 'You do not have access to this Pod.'
            ]);

            exit;
        }

        if ($afterUpdatedAt === '') {
            $afterUpdatedAt = '1970-01-01 00:00:00';
        }

        $changes =
            $this->messageModel->getChangedTransmissions(
                $channelId,
                $afterUpdatedAt
            );

        echo json_encode([
            'success' => true,
            'changes' => $changes,
            'server_time' => date('Y-m-d H:i:s')
        ]);

        exit;
    }



    public function clearStatus(
    $channelId = null
): void {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed.'
        ]);

        exit;
    }

    $channelId = (int) $channelId;
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    if ($channelId <= 0) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Pod not found.'
        ]);

        exit;
    }

    $pod =
        $this->channelModel->findPodForUser(
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

    echo json_encode([
        'success' => true,
        'messages_cleared_at' =>
            $pod['messages_cleared_at']
            ?? null
    ]);

    exit;
}

}