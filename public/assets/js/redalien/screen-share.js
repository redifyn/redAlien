document.addEventListener(
    'DOMContentLoaded',
    function () {

        const button =
            document.getElementById(
                'screenShareButton'
            );

        if (!button) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | State
        |--------------------------------------------------------------------------
        */

        let screenStream =
            null;

        let screenTrack =
            null;

        let originalCameraStream =
            null;

        let originalCameraTrack =
            null;

        let videoSender =
            null;

        let sharing =
            false;

        let stopping =
            false;


        /*
        |--------------------------------------------------------------------------
        | Initially Disabled
        |--------------------------------------------------------------------------
        */

        button.disabled =
            true;


        /*
        |--------------------------------------------------------------------------
        | WebRTC Connected
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:webrtc-connected',
            function () {

                button.disabled =
                    false;

                console.log(
                    'Screen sharing enabled.'
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | WebRTC Disconnected
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:webrtc-disconnected',
            function () {

                button.disabled =
                    true;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Button
        |--------------------------------------------------------------------------
        */

        button.addEventListener(
            'click',
            async function () {

                if (sharing) {

                    await stopSharing();

                    return;
                }

                await startSharing();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Start Screen Sharing
        |--------------------------------------------------------------------------
        */

       async function startSharing() {

    try {

        const api =
            window.RedAlienWebRTC;

        if (!api) {

            alert(
                'WebRTC is not ready.'
            );

            return;
        }


        const peerConnection =
            api.getPeerConnection();

        const localVideo =
            api.getLocalVideo();


        /*
        |--------------------------------------------------------------------------
        | Meeting Must Be Connected
        |--------------------------------------------------------------------------
        */

        if (
            !peerConnection ||
            peerConnection.connectionState !==
                'connected'
        ) {

            alert(
                'Join the live meeting before sharing your screen.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Get Existing Camera Stream
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We do NOT replace localVideo.srcObject.
        | Your own camera preview remains untouched.
        |--------------------------------------------------------------------------
        */

        originalCameraStream =
            localVideo.srcObject;


        if (
            !originalCameraStream ||
            !(
                originalCameraStream
                instanceof MediaStream
            )
        ) {

            throw new Error(
                'Camera stream was not found.'
            );

        }


        originalCameraTrack =
            originalCameraStream
                .getVideoTracks()[0];


        if (!originalCameraTrack) {

            throw new Error(
                'Camera track was not found.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Find Current Video Sender
        |--------------------------------------------------------------------------
        */

        videoSender =
            peerConnection
                .getSenders()
                .find(
                    function (sender) {

                        return (
                            sender.track &&
                            sender.track.kind ===
                                'video'
                        );

                    }
                );


        if (!videoSender) {

            throw new Error(
                'WebRTC video sender was not found.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Ask User What To Share
        |--------------------------------------------------------------------------
        */

        screenStream =
            await navigator
                .mediaDevices
                .getDisplayMedia({
                    video: true,
                    audio: false
                });


        screenTrack =
            screenStream
                .getVideoTracks()[0];


        if (!screenTrack) {

            throw new Error(
                'Screen track could not be created.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Send Screen To Remote User
        |--------------------------------------------------------------------------
        |
        | Camera continues running locally.
        |--------------------------------------------------------------------------
        */

        await videoSender
            .replaceTrack(
                screenTrack
            );


        sharing =
            true;

        stopping =
            false;


        button.classList.add(
            'is-danger'
        );

        button.innerHTML = `
            <i class="bi bi-display-fill"></i>

            <span>
                Stop Sharing
            </span>
        `;


        console.log(
            'Screen sharing started.'
        );


        /*
        |--------------------------------------------------------------------------
        | Browser Native "Stop Sharing"
        |--------------------------------------------------------------------------
        */

        screenTrack.addEventListener(
            'ended',
            function () {

                if (
                    sharing &&
                    !stopping
                ) {

                    stopSharing();

                }

            },
            {
                once: true
            }
        );


    } catch (error) {

        if (
            error.name ===
            'NotAllowedError'
        ) {

            console.log(
                'Screen sharing cancelled.'
            );

            return;
        }


        console.error(
            'Screen sharing error:',
            error
        );


        alert(
            error.message ||
            'Screen sharing could not start.'
        );

    }

}


        /*
        |--------------------------------------------------------------------------
        | Stop Screen Sharing
        |--------------------------------------------------------------------------
        */

        async function stopSharing() {

    if (
        !sharing ||
        stopping
    ) {
        return;
    }


    stopping =
        true;

    button.disabled =
        true;


    try {

        /*
        |--------------------------------------------------------------------------
        | Make Sure Camera Still Exists
        |--------------------------------------------------------------------------
        */

        if (
            !originalCameraTrack ||
            originalCameraTrack.readyState !==
                'live'
        ) {

            throw new Error(
                'The original camera track is no longer available.'
            );

        }


        if (!videoSender) {

            throw new Error(
                'WebRTC video sender was not found.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Restore Camera To Remote User
        |--------------------------------------------------------------------------
        */

        await videoSender
            .replaceTrack(
                originalCameraTrack
            );


        console.log(
            'Camera restored to remote user.'
        );


        /*
        |--------------------------------------------------------------------------
        | Stop Screen Capture Only
        |--------------------------------------------------------------------------
        |
        | DO NOT stop the camera.
        |--------------------------------------------------------------------------
        */

        if (screenStream) {

            screenStream
                .getTracks()
                .forEach(
                    function (track) {

                        track.stop();

                    }
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Screen Share State
        |--------------------------------------------------------------------------
        */

        screenStream =
            null;

        screenTrack =
            null;

        videoSender =
            null;

        originalCameraStream =
            null;

        originalCameraTrack =
            null;

        sharing =
            false;

        stopping =
            false;


        /*
        |--------------------------------------------------------------------------
        | Reset Button
        |--------------------------------------------------------------------------
        */

        button.classList.remove(
            'is-danger'
        );

        button.innerHTML = `
            <i class="bi bi-display"></i>

            <span>
                Share Screen
            </span>
        `;


        button.disabled =
            false;


        console.log(
            'Screen sharing stopped. Camera restored.'
        );


    } catch (error) {

        stopping =
            false;

        button.disabled =
            false;


        console.error(
            'Stop screen sharing error:',
            error
        );


        alert(
            error.message ||
            'Camera could not be restored.'
        );

    }

}

    }
);