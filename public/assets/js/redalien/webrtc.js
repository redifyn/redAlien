document.addEventListener(
    'DOMContentLoaded',
    function () {

        const localVideo =
            document.getElementById(
                'localVideo'
            );

        const remoteVideo =
            document.getElementById(
                'remoteVideo'
            );

        const channelInput =
            document.getElementById(
                'videoChannelId'
            );

        const currentUserInput =
            document.getElementById(
                'videoCurrentUserId'
            );

        if (
            !localVideo ||
            !remoteVideo ||
            !channelInput ||
            !currentUserInput
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Room Information
        |--------------------------------------------------------------------------
        */

        const channelId =
            String(
                channelInput.value || ''
            ).trim();

        const currentUserId =
            Number(
                currentUserInput.value || 0
            );

        if (
            channelId === '' ||
            Number(channelId) <= 0 ||
            currentUserId <= 0
        ) {

            console.error(
                'WebRTC room information is missing.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | WebRTC State
        |--------------------------------------------------------------------------
        */

        let peerConnection =
            null;

        let remoteUserId =
            null;

        let signalPollingInProgress =
            false;

        let pendingIceCandidates =
            [];


        /*
        |--------------------------------------------------------------------------
        | RTC Configuration
        |--------------------------------------------------------------------------
        */

        const rtcConfiguration = {

            iceServers: [

                {
                    urls:
                        'stun:stun.l.google.com:19302'
                }

            ]

        };


        /*
        |--------------------------------------------------------------------------
        | Local Stream
        |--------------------------------------------------------------------------
        */

        function getLocalStream() {

            const stream =
                localVideo.srcObject;

            if (
                !stream ||
                !(stream instanceof MediaStream)
            ) {
                return null;
            }

            return stream;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Peer Connection
        |--------------------------------------------------------------------------
        */

        function createPeerConnection() {

            if (
                peerConnection &&
                peerConnection.connectionState !==
                    'closed'
            ) {
                return peerConnection;
            }

            peerConnection =
                new RTCPeerConnection(
                    rtcConfiguration
                );

            pendingIceCandidates =
                [];


            /*
            |--------------------------------------------------------------------------
            | Add Camera + Microphone Tracks
            |--------------------------------------------------------------------------
            */

            const localStream =
                getLocalStream();

            if (localStream) {

                localStream
                    .getTracks()
                    .forEach(
                        function (track) {

                            peerConnection.addTrack(
                                track,
                                localStream
                            );

                        }
                    );

            } else {

                console.warn(
                    'No local media stream was available when WebRTC started.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Receive Remote Tracks
            |--------------------------------------------------------------------------
            */

            peerConnection.addEventListener(
                'track',
                async function (event) {

                    let remoteStream =
                        event.streams[0];

                    if (!remoteStream) {

                        remoteStream =
                            remoteVideo.srcObject
                            instanceof MediaStream

                                ? remoteVideo.srcObject

                                : new MediaStream();

                        remoteStream.addTrack(
                            event.track
                        );

                    }

                    remoteVideo.srcObject =
                        remoteStream;

                    remoteVideo.style.display =
                        'block';

                    try {

                        await remoteVideo.play();

                    } catch (error) {

                        console.warn(
                            'Remote video autoplay was blocked:',
                            error
                        );

                    }

                    console.log(
                        'Remote media received:',
                        event.track.kind
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ICE Candidate
            |--------------------------------------------------------------------------
            */

            peerConnection.addEventListener(
                'icecandidate',
                async function (event) {

                    if (
                        !event.candidate ||
                        !remoteUserId
                    ) {
                        return;
                    }

                    try {

                        const candidateData =
                            event.candidate.toJSON

                                ? event.candidate.toJSON()

                                : event.candidate;

                        await sendSignal(
                            'ice',
                            candidateData,
                            remoteUserId
                        );

                    } catch (error) {

                        console.error(
                            'Unable to send ICE candidate:',
                            error
                        );

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ICE State
            |--------------------------------------------------------------------------
            */

            peerConnection.addEventListener(
                'iceconnectionstatechange',
                function () {

                    console.log(
                        'ICE state:',
                        peerConnection
                            .iceConnectionState
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Connection State
            |--------------------------------------------------------------------------
            */

            peerConnection.addEventListener(
                'connectionstatechange',
                function () {

                    const connectionState =
                        peerConnection
                            .connectionState;

                    console.log(
                        'WebRTC state:',
                        connectionState
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Connected
                    |--------------------------------------------------------------------------
                    */

                    if (
                        connectionState ===
                        'connected'
                    ) {

                        console.log(
                            'RedAlien Live connected.'
                        );

                        document.dispatchEvent(
                            new CustomEvent(
                                'redalien:webrtc-connected'
                            )
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Disconnected
                    |--------------------------------------------------------------------------
                    */

                    if (
                        connectionState === 'failed' ||
                        connectionState === 'disconnected' ||
                        connectionState === 'closed'
                    ) {

                        console.warn(
                            'WebRTC connection ended:',
                            connectionState
                        );

                        document.dispatchEvent(
                            new CustomEvent(
                                'redalien:webrtc-disconnected'
                            )
                        );

                    }

                }
            );

            return peerConnection;
        }


        /*
        |--------------------------------------------------------------------------
        | Send Signal
        |--------------------------------------------------------------------------
        */

        async function sendSignal(
            signalType,
            signalData,
            receiverId
        ) {

            if (
                !receiverId ||
                Number(receiverId) <= 0
            ) {

                throw new Error(
                    'WebRTC signal receiver is missing.'
                );

            }

            const formData =
                new FormData();

            formData.append(
                'channel_id',
                channelId
            );

            formData.append(
                'receiver_id',
                String(receiverId)
            );

            formData.append(
                'signal_type',
                signalType
            );

            formData.append(
                'signal_data',
                JSON.stringify(
                    signalData
                )
            );

            const response =
                await fetch(
                    window.REDALIEN_BASE_URL +
                    '/videoRoom/signal',
                    {
                        method: 'POST',

                        body: formData,

                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest',

                            'Accept':
                                'application/json'
                        }
                    }
                );

            const responseText =
                await response.text();

            let result;

            try {

                result =
                    JSON.parse(
                        responseText
                    );

            } catch (error) {

                console.error(
                    'Invalid signal response:',
                    responseText
                );

                throw new Error(
                    'The signaling server returned an invalid response.'
                );

            }

            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    'Unable to send WebRTC signal.'
                );

            }

            return result;
        }


        /*
        |--------------------------------------------------------------------------
        | Add ICE Candidate Safely
        |--------------------------------------------------------------------------
        */

        async function addIceCandidateSafely(
            candidate
        ) {

            const connection =
                createPeerConnection();

            if (
                !connection.remoteDescription ||
                !connection.remoteDescription.type
            ) {

                pendingIceCandidates.push(
                    candidate
                );

                return;
            }

            await connection.addIceCandidate(
                new RTCIceCandidate(
                    candidate
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Flush Queued ICE
        |--------------------------------------------------------------------------
        */

        async function flushPendingIceCandidates() {

            if (
                !peerConnection ||
                !peerConnection.remoteDescription
            ) {
                return;
            }

            while (
                pendingIceCandidates.length > 0
            ) {

                const candidate =
                    pendingIceCandidates.shift();

                try {

                    await peerConnection
                        .addIceCandidate(
                            new RTCIceCandidate(
                                candidate
                            )
                        );

                } catch (error) {

                    console.error(
                        'Queued ICE candidate failed:',
                        error
                    );

                }

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Participant Creates Offer
        |--------------------------------------------------------------------------
        */

        async function createOfferForHost(
            hostUserId
        ) {

            remoteUserId =
                Number(
                    hostUserId
                );

            if (remoteUserId <= 0) {

                throw new Error(
                    'Meeting host could not be identified.'
                );

            }

            const localStream =
                getLocalStream();

            if (!localStream) {

                throw new Error(
                    'Start your camera and microphone before joining.'
                );

            }

            closePeerConnection();

            remoteUserId =
                Number(
                    hostUserId
                );

            const connection =
                createPeerConnection();

            const offer =
                await connection
                    .createOffer();

            await connection
                .setLocalDescription(
                    offer
                );

            await sendSignal(
                'offer',
                {
                    type:
                        connection
                            .localDescription
                            .type,

                    sdp:
                        connection
                            .localDescription
                            .sdp
                },
                remoteUserId
            );

            console.log(
                'WebRTC offer sent to host:',
                remoteUserId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Host Handles Offer
        |--------------------------------------------------------------------------
        */

        async function handleOffer(
            signal
        ) {

            const senderId =
                Number(
                    signal.sender_id
                );

            if (senderId <= 0) {
                return;
            }

            remoteUserId =
                senderId;

            const offer =
                JSON.parse(
                    signal.signal_data
                );

            if (!getLocalStream()) {

                console.warn(
                    'Offer received, but host preview is not running.'
                );

                return;
            }

            if (
                peerConnection &&
                peerConnection.signalingState !==
                    'stable'
            ) {

                closePeerConnection();

                remoteUserId =
                    senderId;
            }

            const connection =
                createPeerConnection();

            await connection
                .setRemoteDescription(
                    new RTCSessionDescription(
                        offer
                    )
                );

            await flushPendingIceCandidates();

            const answer =
                await connection
                    .createAnswer();

            await connection
                .setLocalDescription(
                    answer
                );

            await sendSignal(
                'answer',
                {
                    type:
                        connection
                            .localDescription
                            .type,

                    sdp:
                        connection
                            .localDescription
                            .sdp
                },
                remoteUserId
            );

            console.log(
                'WebRTC answer sent to:',
                remoteUserId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Participant Handles Answer
        |--------------------------------------------------------------------------
        */

        async function handleAnswer(
            signal
        ) {

            if (!peerConnection) {

                console.warn(
                    'Answer received without an active peer connection.'
                );

                return;
            }

            remoteUserId =
                Number(
                    signal.sender_id
                );

            const answer =
                JSON.parse(
                    signal.signal_data
                );

            if (
                peerConnection.signalingState !==
                'have-local-offer'
            ) {

                console.warn(
                    'Ignoring answer because signaling state is:',
                    peerConnection
                        .signalingState
                );

                return;
            }

            await peerConnection
                .setRemoteDescription(
                    new RTCSessionDescription(
                        answer
                    )
                );

            await flushPendingIceCandidates();

            console.log(
                'WebRTC answer received from:',
                remoteUserId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Handle ICE
        |--------------------------------------------------------------------------
        */

        async function handleIce(
            signal
        ) {

            const senderId =
                Number(
                    signal.sender_id
                );

            if (senderId <= 0) {
                return;
            }

            if (
                remoteUserId === null
            ) {

                remoteUserId =
                    senderId;
            }

            if (
                remoteUserId !==
                senderId
            ) {
                return;
            }

            const candidate =
                JSON.parse(
                    signal.signal_data
                );

            await addIceCandidateSafely(
                candidate
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Poll Signals
        |--------------------------------------------------------------------------
        */

        async function pollSignals() {

            if (
                signalPollingInProgress ||
                document.hidden
            ) {
                return;
            }

            signalPollingInProgress =
                true;

            try {

                const response =
                    await fetch(
                        window.REDALIEN_BASE_URL +
                        '/videoRoom/signals/' +
                        encodeURIComponent(
                            channelId
                        ) +
                        '?_=' +
                        Date.now(),
                        {
                            method: 'GET',

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json'
                            },

                            cache: 'no-store'
                        }
                    );

                const result =
                    await response.json();

                if (
                    !response.ok ||
                    !result.success
                ) {
                    return;
                }

                const signals =
                    Array.isArray(
                        result.signals
                    )
                        ? result.signals
                        : [];

                for (
                    const signal of signals
                ) {

                    try {

                        if (
                            signal.signal_type ===
                            'offer'
                        ) {

                            await handleOffer(
                                signal
                            );

                        } else if (
                            signal.signal_type ===
                            'answer'
                        ) {

                            await handleAnswer(
                                signal
                            );

                        } else if (
                            signal.signal_type ===
                            'ice'
                        ) {

                            await handleIce(
                                signal
                            );

                        }

                    } catch (error) {

                        console.error(
                            'Signal handling error:',
                            signal.signal_type,
                            error
                        );

                    }

                }

            } catch (error) {

                console.error(
                    'WebRTC polling error:',
                    error
                );

            } finally {

                signalPollingInProgress =
                    false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Close Peer Connection
        |--------------------------------------------------------------------------
        */

        function closePeerConnection() {

            pendingIceCandidates =
                [];

            remoteUserId =
                null;

            if (peerConnection) {

                try {

                    peerConnection.close();

                } catch (error) {

                    console.warn(
                        'WebRTC close error:',
                        error
                    );

                }

                peerConnection =
                    null;
            }

            if (remoteVideo) {

                remoteVideo.pause();

                remoteVideo.srcObject =
                    null;

                remoteVideo.style.display =
                    'none';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Host Starts Meeting
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:meeting-started',
            function (event) {

                console.log(
                    'Host waiting for participant:',
                    event.detail || {}
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Participant Joins Meeting
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:meeting-joined',
            async function (event) {

                try {

                    const hostUserId =
                        Number(
                            event.detail
                                ?.hostUserId ||
                            0
                        );

                    await createOfferForHost(
                        hostUserId
                    );

                } catch (error) {

                    console.error(
                        'Unable to join WebRTC meeting:',
                        error
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | User Leaves Meeting
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:meeting-left',
            function () {

                closePeerConnection();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Meeting Ends
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:meeting-ended',
            function () {

                closePeerConnection();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Public WebRTC API
        |--------------------------------------------------------------------------
        */

        window.RedAlienWebRTC = {

            getPeerConnection() {

                return peerConnection;
            },

            getLocalVideo() {

                return localVideo;
            },

            getLocalStream() {

                return getLocalStream();
            }

        };


        /*
        |--------------------------------------------------------------------------
        | Signal Polling
        |--------------------------------------------------------------------------
        */

        window.setInterval(
            pollSignals,
            1000
        );

        pollSignals();


        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'beforeunload',
            function () {

                if (peerConnection) {

                    peerConnection.close();
                }

            }
        );

    }
);