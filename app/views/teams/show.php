<?php
$alienLogo = !empty($alien['logo'])
    ? BASE_URL . '/' . ltrim($alien['logo'], '/')
    : BASE_URL . '/assets/images/redalien-logo.svg';

$isOwner = ($alien['member_role'] ?? '') === 'owner';

$visibilityLabel = ucfirst(
    $alien['visibility'] ?? 'private'
);

/*
|--------------------------------------------------------------------------
| Current Pod Context
|--------------------------------------------------------------------------
| On /channels/show/{id}, $pod is supplied by ChannelsController.
| On /teams/show/{id}, fall back to the first Pod so the page remains safe.
*/
$selectedPod = (
    isset($pod) &&
    is_array($pod) &&
    !empty($pod['id'])
)
    ? $pod
    : ($pods[0] ?? null);

$currentPodId = isset($currentPodId)
    ? (int) $currentPodId
    : (int) ($selectedPod['id'] ?? 0);

$currentPodName = $currentPodName
    ?? ($selectedPod['name'] ?? 'General');

$currentPodDescription = $currentPodDescription
    ?? ($selectedPod['description'] ?? '');

$currentPodType = $currentPodType
    ?? ($selectedPod['type'] ?? 'public');

$transmissions =
    $transmissions
    ?? [];


/*
|--------------------------------------------------------------------------
| Pod Management Permission
|--------------------------------------------------------------------------
*/

$canManagePod =
    !empty($pod) &&
    in_array(
        $pod['member_role']
        ?? '',
        [
            'owner',
            'admin',
            'moderator'
        ],
        true
    );


/*
|--------------------------------------------------------------------------
| Active Pod Count
|--------------------------------------------------------------------------
*/

$activePodCount =
    count(
        $pods
        ?? []
    );


/*
|--------------------------------------------------------------------------
| Can Delete Current Pod
|--------------------------------------------------------------------------
|
| A manager may delete a Pod only when
| the Alien has more than one active Pod.
|
*/

$canDeleteCurrentPod =
    $canManagePod &&
    $activePodCount > 1;


/*
|--------------------------------------------------------------------------
| General Pod
|--------------------------------------------------------------------------
|
| Kept for other UI purposes.
| It no longer controls deletion.
|
*/

$isGeneralPod =
    !empty($pod) &&
    strtolower(
        (string) (
            $pod['slug']
            ?? ''
        )
    ) === 'general';
?>



<main class="ra-main-content">

    <div class="ra-flash-container">
         <?php Flash::display(); ?>
    </div>

    <section class="row g-4 ra-alien-workspace">

        <!-- =====================================================
             LEFT COLUMN — PODS
        ====================================================== -->
        <div class="col-xl-3">

            <article class="ra-panel ra-pods-panel">

                <div class="ra-pods-header">

                    <div>
                        <span class="ra-eyebrow">
                            Alien Navigation
                        </span>

                        <h2>Alien Pods</h2>
                    </div>

                    <span class="ra-pod-count">
                        <?= count($pods); ?>
                    </span>

                </div>

                <nav class="ra-pods-nav">

                    <?php if (empty($pods)): ?>

                        <div class="ra-alien-empty-state">
                            <i class="bi bi-diagram-3"></i>

                            <h3>No Pods Yet</h3>

                            <p>
                                Create a communication channel for your crew.
                            </p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($pods as $podItem): ?>

                            <?php
                            $podItemId = (int) $podItem['id'];

                            $podIcon = ($podItem['type'] ?? 'public') === 'private'
                                ? 'bi-lock-fill'
                                : 'bi-hash';

                            $isActivePod =
                                $currentPodId > 0 &&
                                $currentPodId === $podItemId;
                            ?>

                            <a
                                href="<?= BASE_URL; ?>/channels/show/<?= $podItemId; ?>"
                                class="ra-pod-link <?= $isActivePod ? 'active' : ''; ?>"
                            >

                                <div class="ra-pod-left">

                                    <i class="bi <?= htmlspecialchars($podIcon); ?>"></i>

                                    <span>
                                        <?= htmlspecialchars($podItem['name']); ?>
                                    </span>

                                </div>

                                <i class="bi bi-chevron-right"></i>

                            </a>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </nav>

                <?php if ($isOwner): ?>

                    <button
                        type="button"
                        class="ra-create-pod-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#createPodModal"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Create Pod
                    </button>

                <?php endif; ?>

            </article>

        </div>


        <!-- =====================================================
             CENTER COLUMN — TRANSMISSION CONSOLE
        ====================================================== -->
        <div class="col-xl-6">

            <article class="ra-panel ra-transmission-panel">

                <!-- Console Header -->
                <div class="ra-transmission-header">

                    <div class="ra-transmission-title">

                        <div class="ra-transmission-icon">
                            <i class="bi bi-hash"></i>
                        </div>

                        <div>
                            <h2>
                                <?= htmlspecialchars($currentPodName ?? 'General'); ?>
                            </h2>

                            <span>
                                <?= !empty($currentPodDescription)
                                    ? htmlspecialchars($currentPodDescription)
                                    : 'Communication channel for this Alien'; 
                                ?>
                            </span>
                        </div>

                    </div>

                    <div class="ra-transmission-header-actions">

                        <span class="ra-transmission-online">
                            <i></i>
                            Online
                        </span>

                        <button
                            type="button"
                            class="ra-transmission-header-btn"
                            title="Search transmissions"
                        >
                            <i class="bi bi-search"></i>
                        </button>

                        <button
                            type="button"
                            class="ra-transmission-header-btn"
                            title="Pod information"
                        >
                            <i class="bi bi-info-circle"></i>
                        </button>

                        <button
                            type="button"
                            class="ra-channel-clear-button"
                            id="clearPodButton"
                            data-channel-id="<?= (int) $currentPodId; ?>"
                            title="Clear chat"
                            aria-label="Clear chat"
                        >
                            <i class="bi bi-trash3-fill"></i>
                        </button>

                       

                        <?php if ($canDeleteCurrentPod): ?>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteCurrentPodModal"
                                    title="Delete this Pod"
                                    aria-label="Delete this Pod"
                                >
                                    <i class="bi bi-x-octagon-fill me-1"></i>
                                    Delete Pod
                                </button>

                            <?php endif; ?>

                   
              

                    </div>

                </div>


                <!-- Messages Area -->
                <div
                    class="ra-transmission-messages"
                    id="transmissionMessages"
                    data-channel-id="<?= $currentPodId; ?>"
                >

                    <?php if (empty($transmissions)): ?>

                        <div class="ra-transmission-welcome">

                            <div class="ra-transmission-welcome-logo">

                                <img
                                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                                    alt="redAlien"
                                >

                            </div>

                            <h3>
                                Welcome to
                                <?= htmlspecialchars($currentPodName); ?>
                            </h3>

                            <p>
                                This is the beginning of the
                                <?= htmlspecialchars($currentPodName); ?> Pod.
                                Start the first transmission with your crew.
                            </p>

                            <div class="ra-transmission-welcome-meta">

                                <span>
                                    <i class="bi bi-people"></i>
                                    <?= count($crew); ?> Crew
                                </span>

                                <span>
                                    <i class="bi bi-shield-check"></i>
                                    <?= htmlspecialchars(ucfirst($currentPodType)); ?> Pod
                                </span>

                            </div>

                        </div>

                        <?php else: ?>

<div class="ra-transmission-list">

    <?php $previousSenderId = null; ?>

    <?php foreach ($transmissions as $transmission): ?>

        <?php

        $senderName =
            $transmission['sender_name']
            ?? $transmission['sender_username']
            ?? 'Unknown Crew Member';

        $storedAvatar =
            $transmission['sender_avatar']
            ?? '';

        if (
            $storedAvatar !== '' &&
            filter_var(
                $storedAvatar,
                FILTER_VALIDATE_URL
            )
        ) {

            $senderAvatar = $storedAvatar;

        } elseif ($storedAvatar !== '') {

            $senderAvatar =
                BASE_URL . '/' .
                ltrim($storedAvatar,'/');

        } else {

            $senderAvatar =
                BASE_URL .
                '/assets/images/avatars/default.svg';

        }

        $createdAt =
            $transmission['created_at']
            ?? '';

        $messageTime =
            $createdAt !== ''
                ? date(
                    'g:i A',
                    strtotime($createdAt)
                )
                : '';

        $currentSenderId =
            (int) (
                $transmission['sender_id']
                ?? 0
            );

        $isGrouped =
            $previousSenderId !== null &&
            $previousSenderId ===
            $currentSenderId;

        $isOwnTransmission =
            $currentSenderId ===
            (int) (
                $_SESSION['user_id']
                ?? 0
            );

        ?>

        <article
    class="
        ra-transmission-item
        <?= $isOwnTransmission ? 'is-own' : ''; ?>
        <?= $isGrouped ? 'is-grouped' : ''; ?>
    "
    data-message-id="<?= (int) $transmission['id']; ?>"
    data-sender-id="<?= $currentSenderId; ?>"
    data-is-owner="<?= $isOwnTransmission ? '1' : '0'; ?>"
>

    <?php if (!$isGrouped): ?>

        <img
            class="ra-transmission-avatar"
            src="<?= htmlspecialchars($senderAvatar); ?>"
            alt="<?= htmlspecialchars($senderName); ?>"
            onerror="this.src='<?= BASE_URL; ?>/assets/images/avatars/default.svg';"
        >

    <?php else: ?>

        <div class="ra-transmission-avatar-spacer"></div>

    <?php endif; ?>


    <div class="ra-transmission-body">

        <?php if (!$isGrouped): ?>

            <div class="ra-transmission-meta">

                <strong>
                    <?= htmlspecialchars($senderName); ?>
                </strong>

                <time
                    datetime="<?= htmlspecialchars($createdAt); ?>"
                >
                    <?= htmlspecialchars($messageTime); ?>
                </time>

                <?php if (!empty($transmission['is_edited'])): ?>

                    <small class="ra-transmission-edited-label">
                        (edited)
                    </small>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="ra-transmission-group-time">

                <time
                    datetime="<?= htmlspecialchars($createdAt); ?>"
                >
                    <?= htmlspecialchars($messageTime); ?>
                </time>

                <?php if (!empty($transmission['is_edited'])): ?>

                    <small class="ra-transmission-edited-label">
                        (edited)
                    </small>

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <?php if (!empty($transmission['parent_message_id'])): ?>

            <div class="ra-transmission-reply">

                <i class="bi bi-reply"></i>

                <span>
                    Replying to

                    <strong>
                        <?= htmlspecialchars(
                            $transmission['parent_sender_name']
                            ?? 'a crew member'
                        ); ?>
                    </strong>
                </span>

            </div>

        <?php endif; ?>



        <?php if (!empty($transmission['attachment_path'])): ?>

    <?php
    $attachmentUrl =
        BASE_URL . '/' .
        ltrim(
            $transmission['attachment_path'],
            '/'
        );

    $attachmentName =
        $transmission['attachment_name']
        ?? 'Image attachment';
    ?>

    <a
        class="ra-transmission-image-link"
        href="<?= htmlspecialchars($attachmentUrl); ?>"
        target="_blank"
        rel="noopener"
    >
        <img
            class="ra-transmission-image"
            src="<?= htmlspecialchars($attachmentUrl); ?>"
            alt="<?= htmlspecialchars($attachmentName); ?>"
            loading="lazy"
        >
    </a>

<?php endif; ?>


<?php if (!empty($transmission['attachment_path'])): ?>

    <?php
    $attachmentUrl =
        BASE_URL . '/' .
        ltrim(
            $transmission['attachment_path'],
            '/'
        );

    $attachmentName =
        $transmission['attachment_name']
        ?? 'Image attachment';
    ?>

    <a
        class="ra-transmission-image-link"
        href="<?= htmlspecialchars($attachmentUrl); ?>"
        target="_blank"
        rel="noopener"
    >
        <img
            class="ra-transmission-image"
            src="<?= htmlspecialchars($attachmentUrl); ?>"
            alt="<?= htmlspecialchars($attachmentName); ?>"
            loading="lazy"
        >
    </a>

<?php endif; ?>


        <?php if (!empty($transmission['attachment_path'])): ?>

    <?php
    $attachmentUrl =
        BASE_URL . '/' .
        ltrim(
            $transmission['attachment_path'],
            '/'
        );

    $attachmentName =
        $transmission['attachment_name']
        ?? 'Image attachment';
    ?>

    <a
        class="ra-transmission-image-link"
        href="<?= htmlspecialchars($attachmentUrl); ?>"
        target="_blank"
        rel="noopener"
    >
        <img
            class="ra-transmission-image"
            src="<?= htmlspecialchars($attachmentUrl); ?>"
            alt="<?= htmlspecialchars($attachmentName); ?>"
            loading="lazy"
        >
    </a>

<?php endif; ?>


<?php if (!empty($transmission['attachment_path'])): ?>

    <?php
    $attachmentUrl =
        BASE_URL . '/' .
        ltrim(
            $transmission['attachment_path'],
            '/'
        );

    $attachmentName =
        $transmission['attachment_name']
        ?? 'Image attachment';
    ?>

    <a
        class="ra-transmission-image-link"
        href="<?= htmlspecialchars($attachmentUrl); ?>"
        target="_blank"
        rel="noopener"
    >

     <img
            class="ra-transmission-image"
            src="<?= htmlspecialchars($attachmentUrl); ?>"
            alt="<?= htmlspecialchars($attachmentName); ?>"
            loading="lazy"
        >
    </a>

    <?php endif; ?>


    <?php if (!empty($transmission['message'])): ?>

        <div
            class="ra-transmission-text"
            data-message-text
        >
            <?= nl2br(
                htmlspecialchars(
                    $transmission['message']
                )
            ); ?>
        </div>

<?php else: ?>

        <div
            class="ra-transmission-text"
            data-message-text
            hidden
        ></div>

<?php endif; ?>


        
        <div
                class="ra-transmission-reactions"
                data-reactions>        
        </div>

    </div>


        <?php if ($isOwnTransmission): ?>
            
<div class="ra-reaction-control">

    <button
        type="button"
        class="ra-add-reaction-button"
        aria-label="Add reaction"
        title="Add reaction"
    >
        <i class="bi bi-emoji-smile"></i>
    </button>

    <div
        class="ra-reaction-picker"
        hidden
    >

        <?php
        $reactionEmojis = [
            '👍',
            '❤️',
            '😂',
            '😮',
            '😢',
            '🚀'
        ];
        ?>

        <?php foreach ($reactionEmojis as $emoji): ?>

            <button
                type="button"
                class="ra-reaction-option"
                data-emoji="<?= htmlspecialchars($emoji); ?>"
                data-message-id="<?= (int) $transmission['id']; ?>"
                aria-label="React with <?= htmlspecialchars($emoji); ?>"
            >
                <?= htmlspecialchars($emoji); ?>
            </button>

        <?php endforeach; ?>

    </div>

</div>

        <div class="dropdown ra-transmission-actions">

            <button
                type="button"
                class="ra-transmission-actions-button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Transmission actions"
                title="Transmission actions"
            >
                <i class="bi bi-three-dots-vertical"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <button
                        type="button"
                        class="dropdown-item ra-edit-transmission-button"
                        data-message-id="<?= (int) $transmission['id']; ?>"
                    >
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit transmission</span>
                    </button>
                </li>

                <li>
                    <button
                        type="button"
                        class="dropdown-item text-danger ra-delete-transmission-button"
                        data-message-id="<?= (int) $transmission['id']; ?>"
                    >
                        <i class="bi bi-trash3"></i>
                        <span>Delete transmission</span>
                    </button>
                </li>

            </ul>

        </div>

    <?php endif; ?>

</article>

        <?php
            $previousSenderId =
                $currentSenderId;
        ?>

    <?php endforeach; ?>

</div>

<?php endif; ?>

                </div>


                <!-- Typing Indicator -->
                <div
                    class="ra-typing-indicator"
                    id="typingIndicator"
                >

                    <span></span>
                    <span></span>
                    <span></span>

                    <small>
                        No crew member is transmitting
                    </small>

                </div>


                <!-- Message Composer -->
              
                    <form
                            action="<?= BASE_URL; ?>/messages/send"
                            method="POST"
                            class="ra-transmission-composer"
                            id="transmissionForm" enctype="multipart/form-data"
                        >
                    <?= Csrf::field(); ?>
                    <div class="ra-composer-tools">

                    <div class="ra-composer-emoji-wrap">

                        <button
                            type="button"
                            class="ra-composer-tool"
                            id="composerEmojiButton"
                            title="Add emoji"
                            aria-label="Add emoji"
                        >
                            <i class="bi bi-emoji-smile"></i>
                        </button>

                        <div
                            class="ra-composer-emoji-picker"
                            id="composerEmojiPicker"
                            hidden
                        >
                            <button type="button" data-composer-emoji="😀">😀</button>
                            <button type="button" data-composer-emoji="😁">😁</button>
                            <button type="button" data-composer-emoji="😂">😂</button>
                            <button type="button" data-composer-emoji="🤣">🤣</button>
                            <button type="button" data-composer-emoji="😊">😊</button>
                            <button type="button" data-composer-emoji="😍">😍</button>

                            <button type="button" data-composer-emoji="😎">😎</button>
                            <button type="button" data-composer-emoji="😭">😭</button>
                            <button type="button" data-composer-emoji="😡">😡</button>
                            <button type="button" data-composer-emoji="👍">👍</button>
                            <button type="button" data-composer-emoji="❤️">❤️</button>
                            <button type="button" data-composer-emoji="🔥">🔥</button>

                            <button type="button" data-composer-emoji="🚀">🚀</button>
                            <button type="button" data-composer-emoji="🎉">🎉</button>
                            <button type="button" data-composer-emoji="👏">👏</button>
                            <button type="button" data-composer-emoji="💯">💯</button>
                            <button type="button" data-composer-emoji="👀">👀</button>
                            <button type="button" data-composer-emoji="🙏">🙏</button>
                        </div>

                    </div>

                    <input
                        type="file"
                        id="attachmentInput"
                        name="attachment"
                        hidden
                        accept="image/*"
                    >
                    <button
                        type="button"
                        class="ra-composer-tool"
                        id="attachmentButton"
                        title="Attach a file"
                        aria-label="Attach a file"
                    >
                        <i class="bi bi-paperclip"></i>
                    </button>

                    <button
                        type="button"
                        class="ra-composer-tool"
                        id="voiceNoteButton"
                        title="Record voice note"
                        aria-label="Record voice note"
                    >
                        <i class="bi bi-mic"></i>
                    </button>

                </div>

                    <div class="ra-composer-input-wrap">
                        <input
                                type="hidden"
                                name="channel_id"
                                value="<?= $currentPodId; ?>"
                            >

                        <textarea
                            id="transmissionInput"
                            name="message"
                            rows="1"
                            maxlength="5000"
                            placeholder="Type a transmission..."
                            aria-label="Type a transmission"
                        ></textarea>
                    <div
                        id="voiceRecorder"
                        class="ra-voice-recorder"
                        hidden
                    >

                        <div class="ra-voice-status">

                            <span
                                class="ra-recording-dot"
                            ></span>

                            <span
                                id="voiceStatusText"
                            >
                                Recording...
                            </span>

                        </div>

                        <div
                            id="voiceTimer"
                            class="ra-voice-timer"
                        >
                            00:00
                        </div>

                    </div>

                     <div
                            id="voicePreview"
                            class="ra-voice-preview"
                            hidden
                        >

                    <div class="ra-voice-preview-left">

                        <button
                            type="button"
                            id="voicePlayButton"
                            class="ra-voice-play"
                        >
                            <i class="bi bi-play-fill"></i>
                        </button>

                        <div>

                            <strong>
                                Voice Note
                            </strong>

                            <div
                                id="voiceDuration"
                            >
                                00:00
                            </div>

                        </div>

                    </div>

                    <div class="ra-voice-preview-actions">

                        <button
                            type="button"
                            id="voiceCancelButton"
                            class="ra-voice-cancel"
                        >
                            Cancel
                        </button>

                    </div>

                <audio
                    id="voiceAudio"
                    hidden
                ></audio>

                     </div>

                    </div>

                    <button
                        type="submit"
                        class="ra-transmission-send"
                        id="transmissionSendButton"
                        title="Send transmission"
                        disabled
                    >
                        <i class="bi bi-send-fill"></i>
                        <span>Send</span>
                    </button>

                </form>

            </article>

        </div>


        <!-- =====================================================
             RIGHT COLUMN — CREW AND INVITE CODE
        ====================================================== -->
        <div class="col-xl-3">

            <article
                class="ra-panel ra-workspace-crew-panel"
                id="alienCrewPanel"
                data-team-id="<?= (int) $alien['id']; ?>"
            >

                <div class="ra-panel-heading">

                    <div>
                        <span>Members / Crew</span>
                        <h2>Alien Crew</h2>
                    </div>

                    <span class="ra-pod-count">
                        <?= count($crew); ?>
                    </span>

                </div>

                <?php if (empty($crew)): ?>

                    <div class="ra-alien-empty-state">
                        <i class="bi bi-people"></i>
                        <h3>No Crew Yet</h3>
                    </div>

                <?php else: ?>

                    <?php foreach (array_slice($crew, 0, 8) as $member): ?>

    <?php
    $memberAvatar = !empty($member['avatar'])
        ? (
            filter_var(
                $member['avatar'],
                FILTER_VALIDATE_URL
            )
                ? $member['avatar']
                : BASE_URL . '/' . ltrim(
                    $member['avatar'],
                    '/'
                )
        )
        : BASE_URL . '/assets/images/avatars/default.svg';


    /*
    |--------------------------------------------------------------------------
    | Initial Presence State
    |--------------------------------------------------------------------------
    |
    | A member is online when last_seen was updated within 45 seconds.
    |
    */

    $memberLastSeen =
        $member['last_seen'] ?? null;

    $lastSeenTimestamp =
        $memberLastSeen
            ? strtotime($memberLastSeen)
            : false;

    $isMemberOnline =
        $lastSeenTimestamp !== false &&
        $lastSeenTimestamp >= (time() - 45);

    $presenceLabel =
        $isMemberOnline
            ? 'Online'
            : 'Offline';
    ?>

    <div
        class="ra-member-row"
        data-crew-user-id="<?= (int) $member['user_id']; ?>"
    >

        <div class="ra-avatar-wrap">

            <img
                src="<?= htmlspecialchars($memberAvatar); ?>"
                alt="<?= htmlspecialchars($member['full_name']); ?>"
                onerror="this.src='<?= BASE_URL; ?>/assets/images/avatars/default.svg';"
            >

            <i
                class="ra-presence-dot <?= $isMemberOnline ? 'is-online' : 'is-offline'; ?>"
                data-presence-dot
            ></i>

        </div>

        <div class="ra-member-details">

            <strong>
                <?= htmlspecialchars($member['full_name']); ?>
            </strong>

            <span>
                <?= htmlspecialchars(
                    ucfirst($member['role'] ?? 'member')
                ); ?>
            </span>

            <small
                class="ra-member-presence <?= $isMemberOnline ? 'is-online' : 'is-offline'; ?>"
                data-presence-label
            >
                <?= htmlspecialchars($presenceLabel); ?>
            </small>

        </div>

        <?php

$currentUserId =
    (int) (
        $_SESSION['user_id']
        ?? 0
    );

$memberUserId =
    (int) (
        $member['user_id']
        ?? 0
    );

$memberRole =
    $member['role']
    ?? 'member';


$canRemoveThisMember = false;


/*
|--------------------------------------------------------------------------
| Owner Can Remove Everyone Except Themselves
|--------------------------------------------------------------------------
*/

if (
    $isOwner &&
    $memberUserId !== $currentUserId &&
    $memberRole !== 'owner'
) {
    $canRemoveThisMember = true;
}


/*
|--------------------------------------------------------------------------
| Admin Can Remove Moderators And Members
|--------------------------------------------------------------------------
*/

if (
    ($alien['member_role'] ?? '') === 'admin' &&
    $memberUserId !== $currentUserId &&
    in_array(
        $memberRole,
        [
            'moderator',
            'member'
        ],
        true
    )
) {
    $canRemoveThisMember = true;
}

?>


<?php if ($canRemoveThisMember): ?>

    <div class="dropdown">

        <button
            type="button"
            class="ra-crew-options-btn"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Crew member options"
            title="Crew member options"
        >
            <i class="bi bi-three-dots-vertical"></i>
        </button>


        <ul
            class="
                dropdown-menu
                dropdown-menu-end
                ra-crew-dropdown
            "
        >

            <li>

                <button
                    type="button"
                    class="
                        dropdown-item
                        text-danger
                        ra-remove-crew-trigger
                    "
                    data-bs-toggle="modal"
                    data-bs-target="#removeCrewModal"
                    data-team-id="<?= (int) $alien['id']; ?>"
                    data-user-id="<?= $memberUserId; ?>"
                    data-member-name="<?= htmlspecialchars(
                        $member['full_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >
                    <i class="bi bi-person-x-fill me-2"></i>

                    Remove from Alien
                </button>

            </li>
            <li>
    <hr class="dropdown-divider">
</li>

<li>
    <button
        type="button"
        class="
            dropdown-item
            text-danger
            ra-block-crew-trigger
        "
        data-bs-toggle="modal"
        data-bs-target="#blockCrewModal"

        data-team-id="<?= (int) $alien['id']; ?>"

        data-user-id="<?= $memberUserId; ?>"

        data-member-name="<?= htmlspecialchars(
            $member['full_name'],
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >
        <i class="bi bi-slash-circle-fill me-2"></i>

        Block from Alien
    </button>
</li>

        </ul>

    </div>

<?php else: ?>

    <span
        class="ra-crew-role-lock"
        title="This crew member cannot be removed"
    >
        <?php if ($memberRole === 'owner'): ?>

            <i class="bi bi-shield-lock-fill"></i>

        <?php endif; ?>
    </span>

<?php endif; ?>

    </div>

<?php endforeach; ?>

                <?php endif; ?>

            </article>


            <article class="ra-panel mt-4">

                <div class="ra-panel-heading">

                    <div>
                        <span>Access Code</span>
                        <h2>Alien Invite Code</h2>
                    </div>

                </div>

                <div class="ra-invite-code-box">

                    <i class="bi bi-fingerprint"></i>

                    <strong id="alienInviteCode">
                        <?= htmlspecialchars($alien['invite_code']); ?>
                    </strong>

                    <button
                        type="button"
                        id="copyAlienInviteCode"
                        title="Copy invite code"
                    >
                        <i class="bi bi-copy"></i>
                    </button>

                </div>

                <p class="ra-invite-code-help">
                    Share this code with users you want to recruit
                    into your Alien.
                </p>

                <?php if ($isOwner): ?>

                    <button
                        type="button"
                        class="btn ra-alien-outline-btn w-100 mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#inviteCrewModal"
                    >
                        <i class="bi bi-person-plus"></i>
                        Recruit Crew
                    </button>

                <?php endif; ?>

            </article>

        </div>

    </section>

</main>

<!-- =====================================================
     REMOVE CREW MEMBER MODAL
====================================================== -->

<div
    class="modal fade"
    id="removeCrewModal"
    tabindex="-1"
    aria-labelledby="removeCrewModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div
            class="
                modal-content
                position-relative
                overflow-hidden
                border-0
                rounded-4
            "
            style="
                background:
                    radial-gradient(
                        circle at center,
                        rgba(124,255,75,.07),
                        transparent 50%
                    ),
                    linear-gradient(
                        145deg,
                        #071108,
                        #0b1a0d
                    );

                border:
                    1px solid
                    rgba(255,80,90,.20)
                    !important;

                box-shadow:
                    0 0 40px
                    rgba(255,60,80,.10);
            "
        >


            <!-- Alien Logo Background -->

            <div
                aria-hidden="true"
                style="
                    position:absolute;
                    inset:0;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    pointer-events:none;
                    z-index:0;
                "
            >

                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt=""
                    style="
                        width:280px;
                        max-width:72%;
                        opacity:.07;

                        filter:
                            drop-shadow(
                                0 0 18px
                                rgba(124,255,75,.9)
                            )
                            drop-shadow(
                                0 0 50px
                                rgba(124,255,75,.35)
                            );
                    "
                >

            </div>


            <!-- Header -->

            <div
                class="
                    modal-header
                    border-0
                    position-relative
                "
                style="
                    z-index:2;

                    border-bottom:
                        1px solid
                        rgba(255,80,90,.12)
                        !important;
                "
            >

                <div
                    class="
                        d-flex
                        align-items-center
                        gap-3
                    "
                >

                    <div
                        class="
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                        "
                        style="
                            width:54px;
                            height:54px;
                            flex:0 0 54px;

                            background:
                                rgba(220,53,69,.13);

                            border:
                                1px solid
                                rgba(255,80,90,.30);

                            color:#ff5b6d;
                            font-size:23px;
                        "
                    >
                        <i class="bi bi-person-x-fill"></i>
                    </div>


                    <div>

                        <span
                            class="
                                d-block
                                text-uppercase
                                small
                                fw-bold
                                mb-1
                            "
                            style="
                                color:#ff6474;
                                letter-spacing:.12em;
                            "
                        >
                            Crew Management
                        </span>

                        <h5
                            class="
                                modal-title
                                text-white
                                mb-0
                            "
                            id="removeCrewModalLabel"
                        >
                            Remove Crew Member
                        </h5>

                    </div>

                </div>


                <button
                    type="button"
                    class="
                        btn-close
                        btn-close-white
                    "
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- Form -->

            <form
                id="removeCrewForm"
                method="POST"
                class="position-relative"
                style="z-index:2;"
            >

                <?= Csrf::field(); ?>


                <div class="modal-body p-4">

                    <div class="text-center">

                        <h4 class="text-white mb-3">

                            Remove

                            <span
                                id="removeCrewMemberName"
                                style="color:#ff6474;"
                            >
                                this crew member
                            </span>

                            ?

                        </h4>


                        <p
                            class="mb-0"
                            style="
                                color:#99a39c;
                                line-height:1.7;
                            "
                        >
                            This crew member will immediately
                            lose access to this Alien and its
                            Pods.
                        </p>

                    </div>


                    <div
                        class="
                            rounded-3
                            p-3
                            mt-4
                        "
                        style="
                            background:
                                rgba(255,255,255,.035);

                            border:
                                1px solid
                                rgba(255,255,255,.07);
                        "
                    >

                        <div
                            class="
                                d-flex
                                align-items-start
                                gap-3
                            "
                        >

                            <i
                                class="bi bi-info-circle"
                                style="
                                    color:#7cff4b;
                                    font-size:22px;
                                "
                            ></i>


                            <div>

                                <strong
                                    class="
                                        text-white
                                        d-block
                                        mb-1
                                    "
                                >
                                    They can be invited again
                                </strong>

                                <small
                                    style="
                                        color:#8d9890;
                                        line-height:1.6;
                                    "
                                >
                                    Removing a crew member does
                                    not permanently block their
                                    account. You can recruit them
                                    again later.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <div
                    class="
                        modal-footer
                        border-0
                        pt-0
                        px-4
                        pb-4
                    "
                >

                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            rounded-pill
                            px-4
                        "
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="
                            btn
                            btn-danger
                            rounded-pill
                            px-4
                        "
                    >

                        <i
                            class="
                                bi
                                bi-person-x-fill
                                me-1
                            "
                        ></i>

                        Remove Crew

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- =====================================================
     BLOCK CREW MEMBER MODAL
====================================================== -->

<div
    class="modal fade"
    id="blockCrewModal"
    tabindex="-1"
    aria-labelledby="blockCrewModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div
            class="modal-content position-relative overflow-hidden border-0 rounded-4"
            style="
                background:
                    radial-gradient(
                        circle at center,
                        rgba(255,60,80,.08),
                        transparent 50%
                    ),
                    linear-gradient(
                        145deg,
                        #071108,
                        #0b1a0d
                    );

                border:
                    1px solid rgba(255,80,90,.24) !important;

                box-shadow:
                    0 0 45px rgba(255,60,80,.12);
            "
        >

            <!-- Alien Background -->
            <div
                aria-hidden="true"
                style="
                    position:absolute;
                    inset:0;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    pointer-events:none;
                    z-index:0;
                "
            >
                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt=""
                    style="
                        width:280px;
                        max-width:72%;
                        opacity:.06;

                        filter:
                            drop-shadow(
                                0 0 18px
                                rgba(255,70,90,.65)
                            );
                    "
                >
            </div>


            <!-- Header -->
            <div
                class="modal-header border-0 position-relative"
                style="
                    z-index:2;
                    border-bottom:
                        1px solid rgba(255,80,90,.13)
                        !important;
                "
            >

                <div class="d-flex align-items-center gap-3">

                    <div
                        class="
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                        "
                        style="
                            width:54px;
                            height:54px;
                            flex:0 0 54px;

                            background:
                                rgba(220,53,69,.14);

                            border:
                                1px solid rgba(255,80,90,.32);

                            color:#ff5b6d;
                            font-size:23px;
                        "
                    >
                        <i class="bi bi-slash-circle-fill"></i>
                    </div>

                    <div>

                        <span
                            class="
                                d-block
                                text-uppercase
                                small
                                fw-bold
                                mb-1
                            "
                            style="
                                color:#ff6474;
                                letter-spacing:.12em;
                            "
                        >
                            Permanent Restriction
                        </span>

                        <h5
                            class="modal-title text-white mb-0"
                            id="blockCrewModalLabel"
                        >
                            Block Crew Member
                        </h5>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- Form -->
            <form
                id="blockCrewForm"
                method="POST"
                class="position-relative"
                style="z-index:2;"
            >

                <?= Csrf::field(); ?>


                <div class="modal-body p-4">

                    <div class="text-center mb-4">

                        <h4 class="text-white mb-3">

                            Block

                            <span
                                id="blockCrewMemberName"
                                style="color:#ff6474;"
                            >
                                this crew member
                            </span>

                            ?

                        </h4>


                        <p
                            class="mb-0"
                            style="
                                color:#99a39c;
                                line-height:1.7;
                            "
                        >
                            This user will be removed from the Alien
                            and prevented from joining again through
                            invite codes or email invitations.
                        </p>

                    </div>


                    <div class="mb-3">

                        <label
                            for="blockCrewReason"
                            class="form-label text-white fw-semibold"
                        >
                            Reason
                            <span
                                style="
                                    color:#7d897f;
                                    font-weight:400;
                                "
                            >
                                (optional)
                            </span>
                        </label>


                        <textarea
                            name="reason"
                            id="blockCrewReason"
                            class="form-control"
                            rows="3"
                            maxlength="255"
                            placeholder="Example: Removed from this project..."
                            style="
                                background:#0d1a0f;
                                border-color:
                                    rgba(255,80,90,.20);
                                color:#ffffff;
                            "
                        ></textarea>

                    </div>


                    <div
                        class="rounded-3 p-3"
                        style="
                            background:
                                rgba(255,70,90,.045);

                            border:
                                1px solid rgba(255,80,90,.10);
                        "
                    >

                        <div class="d-flex align-items-start gap-3">

                            <i
                                class="bi bi-shield-exclamation"
                                style="
                                    color:#ff6474;
                                    font-size:22px;
                                "
                            ></i>

                            <div>

                                <strong
                                    class="text-white d-block mb-1"
                                >
                                    Re-entry will be denied
                                </strong>

                                <small
                                    style="
                                        color:#8d9890;
                                        line-height:1.6;
                                    "
                                >
                                    The user remains blocked until
                                    an authorized Alien manager
                                    explicitly unblocks them.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Footer -->
                <div
                    class="
                        modal-footer
                        border-0
                        pt-0
                        px-4
                        pb-4
                    "
                >

                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            rounded-pill
                            px-4
                        "
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="
                            btn
                            btn-danger
                            rounded-pill
                            px-4
                        "
                    >
                        <i class="bi bi-slash-circle-fill me-1"></i>

                        Block User
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>



<?php

$canManagePods = in_array(
    $alien['member_role'] ?? '',
    [
        'owner',
        'admin',
        'moderator'
    ],
    true
);

?>


<?php if ($canManagePods): ?>

<!-- =====================================================
     CREATE POD MODAL
====================================================== -->

<div
    class="modal fade"
    id="createPodModal"
    tabindex="-1"
    aria-labelledby="createPodModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content ra-alien-modal">

            <div class="modal-header">

                <div>

                    <span class="ra-eyebrow">
                        Channels / Pods
                    </span>

                    <h5
                        class="modal-title"
                        id="createPodModalLabel"
                    >
                        Create a New Pod
                    </h5>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <form
                action="<?= BASE_URL; ?>/channels/store/<?= (int) $alien['id']; ?>"
                method="POST"
                id="createPodForm"
            >

                <?= Csrf::field(); ?>


                <div class="modal-body">

                    <!-- Pod Name -->
                    <div class="mb-4">

                        <label
                            for="podName"
                            class="form-label fw-semibold"
                        >
                            Pod Name
                        </label>

                        <div class="ra-alien-input-wrap">

                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="name"
                                id="podName"
                                class="form-control"
                                placeholder="Example: Backend"
                                minlength="2"
                                maxlength="120"
                                required
                            >

                        </div>

                        <p class="ra-field-help">
                            Give this communication channel a clear name.
                        </p>

                    </div>


                    <!-- Description -->
                    <div class="mb-4">

                        <label
                            for="podDescription"
                            class="form-label fw-semibold"
                        >
                            Description
                        </label>

                        <textarea
                            name="description"
                            id="podDescription"
                            class="form-control ra-alien-textarea"
                            rows="4"
                            maxlength="1000"
                            placeholder="What will your crew discuss here?"
                        ></textarea>

                    </div>


                    <!-- Visibility -->
                    <div>

                        <label class="form-label fw-semibold">
                            Pod Visibility
                        </label>


                        <div class="ra-visibility-grid">

                            <label class="ra-visibility-option">

                                <input
                                    type="radio"
                                    name="type"
                                    value="public"
                                    checked
                                >

                                <span class="ra-visibility-card">

                                    <i class="bi bi-hash"></i>

                                    <strong>
                                        Public Pod
                                    </strong>

                                    <small>
                                        All Alien crew members can enter.
                                    </small>

                                </span>

                            </label>


                            <label class="ra-visibility-option">

                                <input
                                    type="radio"
                                    name="type"
                                    value="private"
                                >

                                <span class="ra-visibility-card">

                                    <i class="bi bi-lock-fill"></i>

                                    <strong>
                                        Private Pod
                                    </strong>

                                    <small>
                                        Access is restricted to selected crew.
                                    </small>

                                </span>

                            </label>

                        </div>

                    </div>

                </div>


                <div class="modal-footer border-0 pt-0">

                    <button
                        type="button"
                        class="btn ra-alien-outline-btn"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="btn ra-create-alien-submit px-4"
                        id="createPodButton"
                    >
                        <i class="bi bi-plus-circle"></i>

                        <span>
                            Create Pod
                        </span>
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<?php endif; ?>





<!-- =====================================================
     DELETE POD MODAL
====================================================== -->
<?php if ($canDeleteCurrentPod): ?>
<div
    class="modal fade"
    id="deleteCurrentPodModal"
    tabindex="-1"
    aria-labelledby="deleteCurrentPodModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div
            class="modal-content position-relative overflow-hidden border-0 rounded-4"
            style="
                background:
                    radial-gradient(
                        circle at center,
                        rgba(124,255,75,.08),
                        transparent 48%
                    ),
                    linear-gradient(
                        145deg,
                        #071108,
                        #0b1a0d
                    );

                border:
                    1px solid rgba(
                        124,
                        255,
                        75,
                        .18
                    ) !important;

                box-shadow:
                    0 0 35px rgba(
                        124,
                        255,
                        75,
                        .12
                    ),
                    0 0 70px rgba(
                        255,
                        60,
                        80,
                        .08
                    );
            "
        >

            <!-- Alien Glow Background -->
            <div
                aria-hidden="true"
                style="
                    position:absolute;
                    inset:0;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    pointer-events:none;
                    overflow:hidden;
                    z-index:0;
                "
            >

                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt=""
                    style="
                        width:280px;
                        max-width:72%;
                        opacity:.10;

                        filter:
                            drop-shadow(
                                0 0 15px
                                rgba(124,255,75,.95)
                            )
                            drop-shadow(
                                0 0 40px
                                rgba(124,255,75,.55)
                            )
                            drop-shadow(
                                0 0 75px
                                rgba(124,255,75,.25)
                            );
                    "
                >

            </div>


            <!-- Header -->
            <div
                class="modal-header border-0 position-relative"
                style="
                    z-index:2;

                    background:
                        rgba(
                            120,
                            20,
                            30,
                            .17
                        );

                    border-bottom:
                        1px solid
                        rgba(
                            255,
                            80,
                            90,
                            .13
                        ) !important;
                "
            >

                <div class="d-flex align-items-center gap-3">

                    <div
                        class="
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                        "
                        style="
                            width:54px;
                            height:54px;
                            flex:0 0 54px;

                            background:
                                rgba(
                                    220,
                                    53,
                                    69,
                                    .13
                                );

                            border:
                                1px solid
                                rgba(
                                    255,
                                    80,
                                    90,
                                    .30
                                );

                            color:#ff5b6d;
                            font-size:23px;
                        "
                    >

                        <i
                            class="bi bi-exclamation-octagon-fill"
                        ></i>

                    </div>


                    <div>

                        <span
                            class="
                                d-block
                                text-uppercase
                                small
                                fw-bold
                                mb-1
                            "
                            style="
                                color:#ff6474;
                                letter-spacing:.12em;
                            "
                        >
                            Pod Management
                        </span>

                        <h5
                            class="modal-title text-white mb-0"
                            id="deleteCurrentPodModalLabel"
                        >
                            Delete Pod
                        </h5>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- Delete Form -->
            <form
                action="<?= BASE_URL; ?>/channels/delete/<?= (int) $currentPodId; ?>"
                method="POST"
                class="position-relative"
                style="z-index:2;"
            >

                <?= Csrf::field(); ?>


                <div class="modal-body p-4">

                    <div class="text-center mb-4">

                        <h4 class="text-white mb-3">

                            Delete

                            <span style="color:#ff6474;">
                                <?= htmlspecialchars(
                                    $currentPodName
                                    ?? 'this Pod'
                                ); ?>
                            </span>

                            ?

                        </h4>


                        <p
                            class="mb-0"
                            style="
                                color:#99a39c;
                                line-height:1.7;
                            "
                        >
                            This Pod will immediately disappear
                            from the Alien and crew members will
                            no longer be able to access it.
                        </p>

                    </div>


                    <div
                        class="rounded-3 p-3"
                        style="
                            background:
                                rgba(
                                    255,
                                    255,
                                    255,
                                    .035
                                );

                            border:
                                1px solid
                                rgba(
                                    255,
                                    255,
                                    255,
                                    .07
                                );

                            backdrop-filter:
                                blur(3px);
                        "
                    >

                        <div
                            class="
                                d-flex
                                align-items-start
                                gap-3
                            "
                        >

                            <i
                                class="bi bi-shield-check"
                                style="
                                    color:#7cff4b;
                                    font-size:22px;
                                "
                            ></i>


                            <div>

                                <strong
                                    class="
                                        text-white
                                        d-block
                                        mb-1
                                    "
                                >
                                    Your Pod data is preserved
                                </strong>


                                <small
                                    style="
                                        color:#8d9890;
                                        line-height:1.6;
                                    "
                                >
                                    RedAlien uses soft deletion,
                                    so this Pod can be recovered
                                    later if needed.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <div
                    class="
                        modal-footer
                        border-0
                        pt-0
                        px-4
                        pb-4
                    "
                >

                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            rounded-pill
                            px-4
                        "
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="
                            btn
                            btn-danger
                            rounded-pill
                            px-4
                        "
                    >

                        <i
                            class="
                                bi
                                bi-x-octagon-fill
                                me-1
                            "
                        ></i>

                        Delete Pod

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>
<?php endif; ?>


<?php if ($isOwner): ?>

<!-- =====================================================
     INVITE CREW BY EMAIL MODAL
====================================================== -->

<div
    class="modal fade"
    id="inviteCrewModal"
    tabindex="-1"
    aria-labelledby="inviteCrewModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div
            class="modal-content position-relative overflow-hidden border-0 rounded-4"
            style="
                background:
                    radial-gradient(
                        circle at center,
                        rgba(124,255,75,.09),
                        transparent 50%
                    ),
                    linear-gradient(
                        145deg,
                        #071108,
                        #0b1a0d
                    );

                border:
                    1px solid
                    rgba(124,255,75,.18) !important;

                box-shadow:
                    0 0 35px
                    rgba(124,255,75,.12);
            "
        >

            <!-- Alien Logo Background -->

            <div
                aria-hidden="true"
                style="
                    position:absolute;
                    inset:0;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    pointer-events:none;
                    overflow:hidden;
                    z-index:0;
                "
            >

                <img
                    src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                    alt=""
                    style="
                        width:280px;
                        max-width:72%;
                        opacity:.07;

                        filter:
                            drop-shadow(
                                0 0 18px
                                rgba(124,255,75,.9)
                            )
                            drop-shadow(
                                0 0 50px
                                rgba(124,255,75,.4)
                            );
                    "
                >

            </div>


            <!-- Header -->

            <div
                class="modal-header border-0 position-relative"
                style="
                    z-index:2;

                    border-bottom:
                        1px solid
                        rgba(124,255,75,.12)
                        !important;
                "
            >

                <div class="d-flex align-items-center gap-3">

                    <div
                        class="
                            d-flex
                            align-items-center
                            justify-content-center
                            rounded-circle
                        "
                        style="
                            width:54px;
                            height:54px;
                            flex:0 0 54px;

                            background:
                                rgba(124,255,75,.10);

                            border:
                                1px solid
                                rgba(124,255,75,.25);

                            color:#7cff4b;

                            font-size:23px;
                        "
                    >

                        <i class="bi bi-person-plus-fill"></i>

                    </div>


                    <div>

                        <span
                            class="
                                d-block
                                text-uppercase
                                small
                                fw-bold
                                mb-1
                            "
                            style="
                                color:#7cff4b;
                                letter-spacing:.12em;
                            "
                        >
                            Crew Recruitment
                        </span>

                        <h5
                            class="modal-title text-white mb-0"
                            id="inviteCrewModalLabel"
                        >
                            Recruit Crew
                        </h5>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>


            <!-- Invitation Form -->

            <form
                action="<?= BASE_URL; ?>/teams/inviteCrewByEmail/<?= (int) $alien['id']; ?>"
                method="POST"
                class="position-relative"
                style="z-index:2;"
            >

                <?= Csrf::field(); ?>


                <div class="modal-body p-4">

                    <p
                        class="mb-4"
                        style="
                            color:#9ca79f;
                            line-height:1.7;
                        "
                    >
                        Send an email invitation to someone
                        you want to recruit into

                        <strong style="color:#ffffff;">
                            <?= htmlspecialchars($alien['name']); ?>
                        </strong>.
                    </p>


                    <!-- Email -->

                    <div class="mb-4">

                        <label
                            for="crewInviteEmail"
                            class="form-label text-white fw-semibold"
                        >
                            Crew Member Email
                        </label>


                        <div
                            class="input-group"
                        >

                            <span
                                class="input-group-text"
                                style="
                                    background:#0d1a0f;
                                    border-color:
                                        rgba(124,255,75,.18);
                                    color:#7cff4b;
                                "
                            >
                                <i class="bi bi-envelope-fill"></i>
                            </span>


                            <input
                                type="email"
                                name="email"
                                id="crewInviteEmail"
                                class="form-control"
                                placeholder="crew@example.com"
                                maxlength="190"
                                autocomplete="email"
                                required
                                style="
                                    background:#0d1a0f;
                                    border-color:
                                        rgba(124,255,75,.18);
                                    color:#ffffff;
                                "
                            >

                        </div>

                    </div>


                    <!-- Alien -->

                    <div
                        class="rounded-3 p-3"
                        style="
                            background:
                                rgba(255,255,255,.035);

                            border:
                                1px solid
                                rgba(255,255,255,.07);
                        "
                    >

                        <div
                            class="
                                d-flex
                                align-items-center
                                gap-3
                            "
                        >

                            <img
                                src="<?= htmlspecialchars($alienLogo); ?>"
                                alt=""
                                width="46"
                                height="46"
                                style="
                                    object-fit:cover;
                                    border-radius:50%;
                                "
                            >


                            <div>

                                <strong
                                    class="text-white d-block"
                                >
                                    <?= htmlspecialchars($alien['name']); ?>
                                </strong>

                                <small
                                    style="color:#8d9890;"
                                >
                                    Invite code:
                                    <?= htmlspecialchars(
                                        $alien['invite_code']
                                    ); ?>
                                </small>

                            </div>

                        </div>

                    </div>


                    <p
                        class="mt-3 mb-0"
                        style="
                            color:#78847b;
                            font-size:13px;
                            line-height:1.6;
                        "
                    >
                        <i class="bi bi-clock me-1"></i>

                        The email invitation will remain
                        valid for 7 days.
                    </p>

                </div>


                <!-- Footer -->

                <div
                    class="
                        modal-footer
                        border-0
                        pt-0
                        px-4
                        pb-4
                    "
                >

                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-secondary
                            rounded-pill
                            px-4
                        "
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="
                            btn
                            rounded-pill
                            px-4
                        "
                        style="
                            background:#7cff4b;
                            color:#071108;
                            font-weight:700;
                        "
                    >

                        <i class="bi bi-send-fill me-1"></i>

                        Send Invitation

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php endif; ?>






<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const removeButtons =
            document.querySelectorAll(
                '.ra-remove-crew-trigger'
            );

        const removeForm =
            document.getElementById(
                'removeCrewForm'
            );

        const memberName =
            document.getElementById(
                'removeCrewMemberName'
            );


        removeButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const teamId =
                            this.dataset.teamId;

                        const userId =
                            this.dataset.userId;

                        const name =
                            this.dataset.memberName;


                        if (
                            !teamId ||
                            !userId
                        ) {
                            return;
                        }


                        removeForm.action =
                            '<?= BASE_URL; ?>' +
                            '/teams/removeCrew/' +
                            teamId +
                            '/' +
                            userId;


                        memberName.textContent =
                            name ||
                            'this crew member';
                    }
                );

            }
        );

    }

    
);






</script>


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const blockButtons =
            document.querySelectorAll(
                '.ra-block-crew-trigger'
            );

        const blockForm =
            document.getElementById(
                'blockCrewForm'
            );

        const blockMemberName =
            document.getElementById(
                'blockCrewMemberName'
            );

        const reasonField =
            document.getElementById(
                'blockCrewReason'
            );


        blockButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const teamId =
                            this.dataset.teamId;

                        const userId =
                            this.dataset.userId;

                        const name =
                            this.dataset.memberName;


                        if (
                            !teamId ||
                            !userId
                        ) {
                            return;
                        }


                        blockForm.action =
                            '<?= BASE_URL; ?>' +
                            '/teams/blockCrew/' +
                            teamId +
                            '/' +
                            userId;


                        blockMemberName.textContent =
                            name ||
                            'this crew member';


                        if (reasonField) {
                            reasonField.value = '';
                        }

                    }
                );

            }
        );

    }
);
</script>