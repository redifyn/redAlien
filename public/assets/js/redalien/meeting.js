document.addEventListener(
    'DOMContentLoaded',
    function () {

        const joinButton =
            document.getElementById(
                'joinMeetingButton'
            );

        const status =
            document.getElementById(
                'videoRoomStatus'
            );

        const channelInput =
            document.getElementById(
                'videoChannelId'
            );

        const canStartMeetingInput =
            document.getElementById(
                'videoCanStartMeeting'
            );

        const currentUserInput =
            document.getElementById(
                'videoCurrentUserId'
            );

        if (
            !joinButton ||
            !status ||
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
        const canStartMeeting =
                canStartMeetingInput &&
                canStartMeetingInput.value === '1';

        if (
            channelId === '' ||
            Number(channelId) <= 0
        ) {
            console.error(
                'A valid Pod ID was not found.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Meeting State
        |--------------------------------------------------------------------------
        */

        let meetingStatusPolling =
            false;

        let meetingStatusInitialized =
            false;

        let lastMeetingActive =
            null;

        let currentMeetingActive =
            false;

        let currentMeetingStartedBy =
            0;

        let joinedCurrentMeeting =
            false;


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        function setMeetingLiveStatus() {

            status.textContent =
                'Meeting Live';

            status.style.background =
                '#0f3b18';

            status.style.borderColor =
                '#7cff4b';

            status.style.color =
                '#7cff4b';

        }


        function setDisconnectedStatus() {

            status.textContent =
                'Not Connected';

            status.style.background =
                '';

            status.style.borderColor =
                '';

            status.style.color =
                '';

        }


        function localPreviewIsReady() {

            const localVideo =
                document.getElementById(
                    'localVideo'
                );

            return Boolean(
                localVideo &&
                localVideo.srcObject
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Preview Ready
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:preview-started',
            function () {

                /*
                |--------------------------------------------------------------------------
                | Existing meeting started by another user
                |--------------------------------------------------------------------------
                */

                if (
                    currentMeetingActive &&
                    currentMeetingStartedBy !==
                        currentUserId
                ) {

                    joinButton.disabled =
                        false;

                    joinButton.innerHTML = `
                        <i class="bi bi-box-arrow-in-right"></i>

                        <span>
                            Join Live Meeting
                        </span>
                    `;

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Current user already owns meeting
                |--------------------------------------------------------------------------
                */

                if (
                    currentMeetingActive &&
                    currentMeetingStartedBy ===
                        currentUserId
                ) {

                    joinButton.disabled =
                        true;

                    joinButton.innerHTML = `
                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Meeting Started
                        </span>
                    `;

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | No active meeting
                |--------------------------------------------------------------------------
                */

                joinButton.disabled =
                    false;

                joinButton.innerHTML = `
                    <i class="bi bi-broadcast"></i>

                    <span>
                        Join Meeting
                    </span>
                `;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Preview Stopped
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:preview-stopped',
            async function () {

                /*
                |--------------------------------------------------------------------------
                | Only host should attempt to end the actual meeting
                |--------------------------------------------------------------------------
                */

                if (
                    currentMeetingActive &&
                    currentMeetingStartedBy ===
                        currentUserId
                ) {

                    try {

                        const formData =
                            new FormData();

                        formData.append(
                            'channel_id',
                            channelId
                        );

                        const response =
                            await fetch(
                                window.REDALIEN_BASE_URL +
                                '/videoRoom/stop',
                                {
                                    method:
                                        'POST',

                                    body:
                                        formData,

                                    headers: {
                                        'X-Requested-With':
                                            'XMLHttpRequest',

                                        'Accept':
                                            'application/json'
                                    }
                                }
                            );

                        const result =
                            await response.json();

                        if (
                            !response.ok ||
                            !result.success
                        ) {
                            console.error(
                                result.message ||
                                'Unable to stop meeting.'
                            );
                        }

                    } catch (error) {

                        console.error(
                            'Unable to stop meeting:',
                            error
                        );

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Local UI Reset
                |--------------------------------------------------------------------------
                */

                joinedCurrentMeeting =
                    false;

                joinButton.disabled =
                    true;

                joinButton.innerHTML = `
                    <i class="bi bi-broadcast"></i>

                    <span>
                        Join Meeting
                    </span>
                `;

                /*
                |--------------------------------------------------------------------------
                | If participant stops preview while host meeting is still active,
                | keep the Meeting Live status.
                |--------------------------------------------------------------------------
                */

                if (
                    currentMeetingActive &&
                    currentMeetingStartedBy !==
                        currentUserId
                ) {

                    setMeetingLiveStatus();

                } else {

                    setDisconnectedStatus();

                }


                /*
                |--------------------------------------------------------------------------
                | Tell WebRTC local participation ended
                |--------------------------------------------------------------------------
                */

                document.dispatchEvent(
                    new CustomEvent(
                        'redalien:meeting-left'
                    )
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Poll Meeting Status
        |--------------------------------------------------------------------------
        */

        async function pollMeetingStatus() {

            if (
                meetingStatusPolling ||
                document.hidden
            ) {
                return;
            }

            meetingStatusPolling =
                true;

            try {

                const response =
                    await fetch(
                        window.REDALIEN_BASE_URL +
                        '/videoRoom/status/' +
                        encodeURIComponent(
                            channelId
                        ) +
                        '?_=' +
                        Date.now(),
                        {
                            method:
                                'GET',

                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',

                                'Accept':
                                    'application/json'
                            },

                            cache:
                                'no-store'
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

                const meetingActive =
                    Boolean(
                        result.meeting_active
                    );

                const meetingStartedBy =
                    Number(
                        result.meeting_started_by ||
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Store Current State
                |--------------------------------------------------------------------------
                */

                currentMeetingActive =
                    meetingActive;

                currentMeetingStartedBy =
                    meetingStartedBy;


                /*
                |--------------------------------------------------------------------------
                | Initial Poll
                |--------------------------------------------------------------------------
                */

                if (
                    !meetingStatusInitialized
                ) {

                    lastMeetingActive =
                        meetingActive;

                    meetingStatusInitialized =
                        true;

                }


                /*
                |--------------------------------------------------------------------------
                | Meeting Is Live
                |--------------------------------------------------------------------------
                */

                if (meetingActive) {

                    setMeetingLiveStatus();


                    /*
                    |--------------------------------------------------------------------------
                    | Current User Is Host
                    |--------------------------------------------------------------------------
                    */

                    if (
                        meetingStartedBy ===
                        currentUserId
                    ) {

                        joinButton.innerHTML = `
                            <i class="bi bi-check-circle-fill"></i>

                            <span>
                                Meeting Started
                            </span>
                        `;

                        joinButton.disabled =
                            true;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Another User Is Host
                    |--------------------------------------------------------------------------
                    */

                    else if (
                        localPreviewIsReady()
                    ) {

                        if (
                            joinedCurrentMeeting
                        ) {

                            joinButton.innerHTML = `
                                <i class="bi bi-check-circle-fill"></i>

                                <span>
                                    Joined Meeting
                                </span>
                            `;

                            joinButton.disabled =
                                true;

                        } else {

                            joinButton.innerHTML = `
                                <i class="bi bi-box-arrow-in-right"></i>

                                <span>
                                    Join Live Meeting
                                </span>
                            `;

                            joinButton.disabled =
                                false;

                        }

                    } else {

                        joinButton.innerHTML = `
                            <i class="bi bi-box-arrow-in-right"></i>

                            <span>
                                Start Preview to Join
                            </span>
                        `;

                        joinButton.disabled =
                            true;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | No Active Meeting
                |--------------------------------------------------------------------------
                */

                else {

    joinedCurrentMeeting =
        false;

    setDisconnectedStatus();

    /*
    |--------------------------------------------------------------------------
    | Owner / Admin / Moderator
    |--------------------------------------------------------------------------
    */

    if (canStartMeeting) {

        if (
            localPreviewIsReady()
        ) {

            joinButton.disabled =
                false;

        } else {

            joinButton.disabled =
                true;

        }

        joinButton.innerHTML = `
            <i class="bi bi-broadcast"></i>

            <span>
                Start Meeting
            </span>
        `;

    }

    /*
    |--------------------------------------------------------------------------
    | Ordinary Members
    |--------------------------------------------------------------------------
    */

    else {

        joinButton.disabled =
            true;

        joinButton.innerHTML = `
            <i class="bi bi-hourglass-split"></i>

            <span>
                Waiting for Meeting
            </span>
        `;

    }

}

                /*
                |--------------------------------------------------------------------------
                | Detect Meeting Ending
                |--------------------------------------------------------------------------
                */

                if (
                    lastMeetingActive ===
                        true &&
                    meetingActive ===
                        false
                ) {

                    joinedCurrentMeeting =
                        false;

                    document.dispatchEvent(
                        new CustomEvent(
                            'redalien:meeting-ended'
                        )
                    );

                }

                lastMeetingActive =
                    meetingActive;

            } catch (error) {

                console.error(
                    'Meeting status polling error:',
                    error
                );

            } finally {

                meetingStatusPolling =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Join / Start Meeting Button
        |--------------------------------------------------------------------------
        */

        joinButton.addEventListener(
            'click',
            async function () {

                if (
                    joinButton.disabled ||
                    !localPreviewIsReady()
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Meeting Already Exists
                |
                | User B joins it WITHOUT calling /videoRoom/start.
                |--------------------------------------------------------------------------
                */

                if (
                    currentMeetingActive &&
                    currentMeetingStartedBy !==
                        currentUserId
                ) {

                    joinedCurrentMeeting =
                        true;

                    setMeetingLiveStatus();

                    joinButton.innerHTML = `
                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Joined Meeting
                        </span>
                    `;

                    joinButton.disabled =
                        true;


                   /*
|--------------------------------------------------------------------------
| Notify WebRTC that participant is ready
|--------------------------------------------------------------------------
*/

document.dispatchEvent(
    new CustomEvent(
        'redalien:meeting-joined',
        {
            detail: {

                channelId:
                    Number(
                        channelId
                    ),

                hostUserId:
                    currentMeetingStartedBy

            }
        }
    )
);

return;
                    


if (
    !joinResponse.ok ||
    !joinResult.success
) {

    console.error(
        'Meeting join registration failed:',
        joinResult
    );

    alert(
        joinResult.message ||
        'Unable to join the meeting.'
    );

    return;
}


/*
|--------------------------------------------------------------------------
| Start WebRTC
|--------------------------------------------------------------------------
*/

document.dispatchEvent(
    new CustomEvent(
        'redalien:meeting-joined',
        {
            detail: {

                channelId:
                    Number(
                        channelId
                    ),

                hostUserId:
                    currentMeetingStartedBy

            }
        }
    )
);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Current User Starts A New Meeting
                |--------------------------------------------------------------------------
                */

                const originalButtonHtml =
                    joinButton.innerHTML;

                joinButton.disabled =
                    true;

                joinButton.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm"
                        aria-hidden="true"
                    ></span>

                    <span>
                        Joining...
                    </span>
                `;

                try {

                    const formData =
                        new FormData();

                    formData.append(
                        'channel_id',
                        channelId
                    );

                    const response =
                        await fetch(
                            window.REDALIEN_BASE_URL +
                            '/videoRoom/start',
                            {
                                method:
                                    'POST',

                                body:
                                    formData,

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
                            'Invalid meeting response:',
                            responseText
                        );

                        throw new Error(
                            'The server returned an invalid response.'
                        );

                    }

                    if (
                        !response.ok ||
                        !result.success
                    ) {
                        throw new Error(
                            result.message ||
                            'The meeting could not be started.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Local State Immediately
                    |--------------------------------------------------------------------------
                    */

                    currentMeetingActive =
                        true;

                    currentMeetingStartedBy =
                        currentUserId;

                    joinedCurrentMeeting =
                        true;


                    /*
                    |--------------------------------------------------------------------------
                    | Update UI
                    |--------------------------------------------------------------------------
                    */

                    setMeetingLiveStatus();

                    joinButton.innerHTML = `
                        <i class="bi bi-check-circle-fill"></i>

                        <span>
                            Meeting Started
                        </span>
                    `;

                    joinButton.disabled =
                        true;


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT:
                    | Tell webrtc.js that the HOST meeting is ready.
                    |--------------------------------------------------------------------------
                    */

                    document.dispatchEvent(
                        new CustomEvent(
                            'redalien:meeting-started',
                            {
                                detail: {
                                    channelId:
                                        Number(
                                            channelId
                                        ),

                                    hostUserId:
                                        currentUserId
                                }
                            }
                        )
                    );

                } catch (error) {

                    console.error(
                        'Start meeting error:',
                        error
                    );

                    alert(
                        error.message ||
                        'The meeting could not be started.'
                    );

                    joinButton.innerHTML =
                        originalButtonHtml;

                    joinButton.disabled =
                        false;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Start Meeting Status Polling
        |--------------------------------------------------------------------------
        */

        window.setInterval(
            pollMeetingStatus,
            2000
        );

        pollMeetingStatus();

    }
);