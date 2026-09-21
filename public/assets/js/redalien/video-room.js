document.addEventListener(
    'DOMContentLoaded',
    function () {

        const startButton =
            document.getElementById(
                'startPreviewButton'
            );

        const stopButton =
            document.getElementById(
                'stopPreviewButton'
            );

        const muteButton =
            document.getElementById(
                'toggleMicrophoneButton'
            );

        const cameraButton =
            document.getElementById(
                'toggleCameraButton'
            );

        const video =
            document.getElementById(
                'localVideo'
            );

        const placeholder =
            document.getElementById(
                'videoPlaceholder'
            );

        const status =
            document.getElementById(
                'videoRoomStatus'
            );

        const microphoneMeter =
            document.getElementById(
                'microphoneMeter'
            );

        const microphoneMeterLevel =
            document.getElementById(
                'microphoneMeterLevel'
            );

        const localVideoWrap =
            document.getElementById(
                'localVideoWrap'
            );

        if (
            !startButton ||
            !stopButton ||
            !muteButton ||
            !cameraButton ||
            !video ||
            !placeholder ||
            !status ||
            !localVideoWrap
        ) {
            return;
        }

        let stream = null;

        let microphoneEnabled = true;
        let cameraEnabled = true;

        let audioContext = null;
        let analyser = null;
        let microphoneSource = null;
        let microphoneAnimation = null;


        /*
        |--------------------------------------------------------------------------
        | Microphone Meter
        |--------------------------------------------------------------------------
        */

        function startMicrophoneMeter() {

            if (
                !stream ||
                !microphoneMeter ||
                !microphoneMeterLevel
            ) {
                return;
            }

            stopMicrophoneMeter();

            microphoneMeter.hidden = false;

            const AudioContextClass =
                window.AudioContext ||
                window.webkitAudioContext;

            if (!AudioContextClass) {
                return;
            }

            audioContext =
                new AudioContextClass();

            analyser =
                audioContext.createAnalyser();

            analyser.fftSize = 256;

            microphoneSource =
                audioContext.createMediaStreamSource(
                    stream
                );

            microphoneSource.connect(
                analyser
            );

            const data =
                new Uint8Array(
                    analyser.frequencyBinCount
                );

            function animate() {

                if (
                    !analyser ||
                    !microphoneMeterLevel
                ) {
                    return;
                }

                analyser.getByteFrequencyData(
                    data
                );

                let sum = 0;

                for (
                    let index = 0;
                    index < data.length;
                    index++
                ) {
                    sum += data[index];
                }

                const average =
                    sum / data.length;

                const percentage =
                    Math.min(
                        100,
                        average * 1.5
                    );

                microphoneMeterLevel.style.width =
                    microphoneEnabled
                        ? percentage + '%'
                        : '0%';

                microphoneAnimation =
                    window.requestAnimationFrame(
                        animate
                    );

            }

            animate();

        }


        function stopMicrophoneMeter() {

            if (microphoneAnimation) {

                window.cancelAnimationFrame(
                    microphoneAnimation
                );

                microphoneAnimation = null;

            }

            if (microphoneSource) {

                try {
                    microphoneSource.disconnect();
                } catch (error) {
                    // Already disconnected.
                }

                microphoneSource = null;

            }

            analyser = null;

            if (audioContext) {

                audioContext
                    .close()
                    .catch(function () {
                        // Audio context may already be closed.
                    });

                audioContext = null;

            }

            if (microphoneMeter) {

                microphoneMeter.hidden =
                    true;

            }

            if (microphoneMeterLevel) {

                microphoneMeterLevel.style.width =
                    '0%';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Start Preview
        |--------------------------------------------------------------------------
        */

        startButton.addEventListener(
            'click',
            async function () {

                if (stream) {
                    return;
                }

                startButton.disabled =
                    true;

                const originalButtonHtml =
                    startButton.innerHTML;

                startButton.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm"
                        aria-hidden="true"
                    ></span>

                    <span>
                        Starting...
                    </span>
                `;

                try {

                    stream =
                        await navigator
                            .mediaDevices
                            .getUserMedia({
                                video: true,
                                audio: true
                            });

                    microphoneEnabled = true;
                    cameraEnabled = true;

                    const videoTrack =
                    stream.getVideoTracks()[0];

                if (videoTrack) {

                    videoTrack.addEventListener(
                        'ended',
                        function () {

                            console.warn(
                                'The camera track ended unexpectedly.'
                            );

                        }
                    );

                }

                   video.srcObject =
                        stream;

                    await video.play();

                    video.style.display =
                        'block';

                    localVideoWrap.hidden =
                        false;

                    placeholder.hidden =
                        true;

                    status.textContent =
                        'Camera Ready';

                    stopButton.disabled =
                        false;

                    muteButton.disabled =
                        false;

                    cameraButton.disabled =
                        false;

                    startMicrophoneMeter();

                    /*
                    |--------------------------------------------------------------------------
                    | Tell meeting.js that preview is truly ready
                    |--------------------------------------------------------------------------
                    */

                    document.dispatchEvent(
                        new CustomEvent(
                            'redalien:preview-started',
                            {
                                detail: {
                                    stream: stream
                                }
                            }
                        )
                    );

               } catch (error) {

    console.error(
        'Preview start error:',
        error.name,
        error.message,
        error
    );

    stream = null;

    let message =
        'Camera or microphone could not be started.';

    if (
        !window.isSecureContext
    ) {

        message =
            'This page is not running in a secure context. ' +
            'Camera and microphone normally require HTTPS.';

    } else if (
        error.name === 'NotAllowedError'
    ) {

        message =
            'Camera or microphone permission was blocked. ' +
            'Please allow both permissions in the browser.';

    } else if (
        error.name === 'NotFoundError'
    ) {

        message =
            'No usable camera or microphone was found on this computer.';

    } else if (
        error.name === 'NotReadableError'
    ) {

        message =
            'The camera or microphone exists but Windows or another application is preventing access to it.';

    } else if (
        error.name === 'OverconstrainedError'
    ) {

        message =
            'The available camera or microphone cannot satisfy the requested settings.';

    } else if (
        error.name
    ) {

        message +=
            '\n\nError: ' +
            error.name;

    }

    alert(message);

} finally {

                    startButton.innerHTML =
                        originalButtonHtml;

                    startButton.disabled =
                        Boolean(stream);

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Stop Preview
        |--------------------------------------------------------------------------
        */

        stopButton.addEventListener(
            'click',
            function () {

                if (!stream) {
                    return;
                }

                stream
                    .getTracks()
                    .forEach(
                        function (track) {
                            track.stop();
                        }
                    );

                stopMicrophoneMeter();

                video.srcObject =
                    null;

                video.style.display =
                    'none';

                localVideoWrap.hidden =
                    true;

                placeholder.hidden =
                    false;

                status.textContent =
                    'Not Connected';

                startButton.disabled =
                    false;

                stopButton.disabled =
                    true;

                muteButton.disabled =
                    true;

                cameraButton.disabled =
                    true;

                microphoneEnabled =
                    true;

                cameraEnabled =
                    true;

                muteButton.innerHTML = `
                    <i class="bi bi-mic"></i>
                    <span>Mute</span>
                `;

                cameraButton.innerHTML = `
                    <i class="bi bi-camera-video"></i>
                    <span>Camera Off</span>
                `;

                stream = null;

                /*
                |--------------------------------------------------------------------------
                | Tell meeting.js preview has stopped
                |--------------------------------------------------------------------------
                */

                document.dispatchEvent(
                    new CustomEvent(
                        'redalien:preview-stopped'
                    )
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Mute / Unmute
        |--------------------------------------------------------------------------
        */

        muteButton.addEventListener(
            'click',
            function () {

                if (!stream) {
                    return;
                }

                microphoneEnabled =
                    !microphoneEnabled;

                stream
                    .getAudioTracks()
                    .forEach(
                        function (track) {

                            track.enabled =
                                microphoneEnabled;

                        }
                    );

                muteButton.innerHTML =
                    microphoneEnabled
                        ? `
                            <i class="bi bi-mic"></i>
                            <span>Mute</span>
                        `
                        : `
                            <i class="bi bi-mic-mute"></i>
                            <span>Unmute</span>
                        `;

                if (
                    !microphoneEnabled &&
                    microphoneMeterLevel
                ) {
                    microphoneMeterLevel.style.width =
                        '0%';
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Camera On / Off
        |--------------------------------------------------------------------------
        */

        cameraButton.addEventListener(
            'click',
            function () {

                if (!stream) {
                    return;
                }

                cameraEnabled =
                    !cameraEnabled;

                stream
                    .getVideoTracks()
                    .forEach(
                        function (track) {

                            track.enabled =
                                cameraEnabled;

                        }
                    );

                cameraButton.innerHTML =
                    cameraEnabled
                        ? `
                            <i class="bi bi-camera-video"></i>
                            <span>Camera Off</span>
                        `
                        : `
                            <i class="bi bi-camera-video-off"></i>
                            <span>Camera On</span>
                        `;

            }
        );

    }
);