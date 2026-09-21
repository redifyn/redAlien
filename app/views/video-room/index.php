<section class="ra-video-room">

    <div class="ra-video-room-header">

        <div>
            <span class="ra-video-room-kicker">
                RedAlien Live
            </span>

            <h1>
                Camera & Microphone Preview
            </h1>

            <p>
                Test your camera and microphone before joining.
            </p>
        </div>

        <span
            class="ra-video-room-status"
            id="videoRoomStatus"
        >
            Not connected
        </span>

    </div>


    <div class="ra-video-stage">

    <!-- Remote user: main screen -->
        <video
            id="remoteVideo"
            class="ra-video-feed ra-remote-video"
            autoplay
            playsinline
        ></video>

    <!-- Local user: small preview -->
        <div
            class="ra-local-video-wrap"
            id="localVideoWrap"
            hidden
        >
            <video
                id="localVideo"
                class="ra-local-video"
                autoplay
                muted
                playsinline
            ></video>

            <span class="ra-video-name">
                You
            </span>
        </div>

        <div
            class="ra-video-placeholder"
            id="videoPlaceholder"
        >
            <i class="bi bi-camera-video-off"></i>

            <strong>
                No one is connected
            </strong>

            <span>
                Start your preview before joining.
            </span>
        </div>

</div>

    <input
    type="hidden"
    id="videoCurrentUserId"
    value="<?= (int) ($_SESSION['user_id'] ?? 0); ?>"
>

<input
    type="hidden"
    id="videoChannelId"
    value="<?= (int) ($channelId ?? 0); ?>"
>

<input
    type="hidden"
    id="videoCanStartMeeting"
    value="<?= !empty($canStartMeeting) ? '1' : '0'; ?>"
>


<div class="ra-video-controls">

    <!-- Start Camera / Microphone Preview -->
    <button
        type="button"
        class="ra-video-control is-primary"
        id="startPreviewButton"
    >
        <i class="bi bi-camera-video"></i>

        <span>
            Start Preview
        </span>
    </button>


    <!-- Meeting Action -->
    <button
        type="button"
        class="ra-video-control is-success"
        id="joinMeetingButton"
        disabled
    >
        <?php if (!empty($canStartMeeting)): ?>

            <i class="bi bi-broadcast"></i>

            <span>
                Start Meeting
            </span>

        <?php else: ?>

            <i class="bi bi-hourglass-split"></i>

            <span>
                Waiting for Meeting
            </span>

        <?php endif; ?>
    </button>


    <!-- Microphone -->
    <button
        type="button"
        class="ra-video-control"
        id="toggleMicrophoneButton"
        disabled
    >
        <i class="bi bi-mic"></i>

        <span>
            Mute
        </span>
    </button>


    <!-- Camera -->
    <button
        type="button"
        class="ra-video-control"
        id="toggleCameraButton"
        disabled
    >
        <i class="bi bi-camera-video"></i>

        <span>
            Camera Off
        </span>
    </button>
 <!-- Screen Share -->
    <button
        type="button"
        class="ra-video-control"
        id="screenShareButton"
        disabled
    >
     <i class="bi bi-display"></i>

    <span>
        Share Screen
    </span>
</button>


    <!-- Stop Preview -->
    <button
        type="button"
        class="ra-video-control is-danger"
        id="stopPreviewButton"
        disabled
    >
        <i class="bi bi-telephone-x"></i>

        <span>
            Stop
        </span>
    </button>

</div>

</section>