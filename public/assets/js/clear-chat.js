document.addEventListener(
    'DOMContentLoaded',
    function () {

        const button =
            document.getElementById(
                'clearPodButton'
            );

        const transmissionMessages =
            document.getElementById(
                'transmissionMessages'
            );

        const transmissionForm =
            document.getElementById(
                'transmissionForm'
            );

        const transmissionInput =
            document.getElementById(
                'transmissionInput'
            );

        const transmissionSendButton =
            document.getElementById(
                'transmissionSendButton'
            );

        const attachmentInput =
            document.getElementById(
                'attachmentInput'
            );

        const voiceRecorder =
            document.getElementById(
                'voiceRecorder'
            );

        const voicePreview =
            document.getElementById(
                'voicePreview'
            );

        const voiceAudio =
            document.getElementById(
                'voiceAudio'
            );

        const voiceTimer =
            document.getElementById(
                'voiceTimer'
            );

        const voiceDuration =
            document.getElementById(
                'voiceDuration'
            );

        const voicePlayButton =
            document.getElementById(
                'voicePlayButton'
            );

        if (
            !button ||
            !transmissionMessages
        ) {
            return;
        }

        const channelId =
            button.dataset.channelId;

        if (!channelId) {
            return;
        }

        // let lastKnownClearTime = null;
        // let clearStatusInitialized = false;
        // let clearPollingInProgress = false;


        /*
        |--------------------------------------------------------------------------
        | Empty Conversation State
        |--------------------------------------------------------------------------
        */

        function showEmptyConversation() {

            transmissionMessages.innerHTML = `
                <div class="ra-transmission-welcome">

                    <div class="ra-transmission-welcome-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>

                    <h3>
                        No transmissions yet
                    </h3>

                    <p>
                        Start the first transmission with your crew.
                    </p>

                </div>
            `;

        }


        /*
        |--------------------------------------------------------------------------
        | Stop Audio Players
        |--------------------------------------------------------------------------
        */

        function stopAllAudioPlayers() {

            document
                .querySelectorAll('audio')
                .forEach(
                    function (audio) {

                        audio.pause();

                        try {

                            audio.currentTime = 0;

                        } catch (error) {

                            // Audio may not be fully loaded.

                        }

                    }
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Composer
        |--------------------------------------------------------------------------
        */

        function resetComposer() {

            if (transmissionInput) {

                transmissionInput.value = '';

                transmissionInput.style.height =
                    'auto';

            }

            if (attachmentInput) {

                attachmentInput.value = '';

                attachmentInput.dispatchEvent(
                    new Event(
                        'change',
                        {
                            bubbles: true
                        }
                    )
                );

            }

            if (transmissionSendButton) {

                transmissionSendButton.disabled =
                    true;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Voice Note Preview
        |--------------------------------------------------------------------------
        */

        function resetVoicePreview() {

            if (voiceAudio) {

                voiceAudio.pause();

                voiceAudio.removeAttribute(
                    'src'
                );

                voiceAudio.load();

            }

            if (voiceRecorder) {

                voiceRecorder.hidden =
                    true;

            }

            if (voicePreview) {

                voicePreview.hidden =
                    true;

            }

            if (voiceTimer) {

                voiceTimer.textContent =
                    '00:00';

            }

            if (voiceDuration) {

                voiceDuration.textContent =
                    '00:00';

            }

            if (voicePlayButton) {

                voicePlayButton.innerHTML =
                    '<i class="bi bi-play-fill"></i>';

            }

            document.dispatchEvent(
                new CustomEvent(
                    'redalien:chat-cleared'
                )
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Clean Conversation Screen
        |--------------------------------------------------------------------------
        */

        function cleanConversationScreen() {

            stopAllAudioPlayers();

            resetComposer();

            resetVoicePreview();

            showEmptyConversation();

        }


        /*
        |--------------------------------------------------------------------------
        | Poll Clear Status
        |--------------------------------------------------------------------------
        */

        // async function pollClearStatus() {

        //     if (
        //         clearPollingInProgress ||
        //         document.hidden
        //     ) {
        //         return;
        //     }

        //     clearPollingInProgress =
        //         true;

        //     try {

        //         const response =
        //             await fetch(
        //                 window.REDALIEN_BASE_URL +
        //                 '/messages/clearStatus/' +
        //                 encodeURIComponent(
        //                     channelId
        //                 ) +
        //                 '?_=' +
        //                 Date.now(),
        //                 {
        //                     method: 'GET',

        //                     headers: {
        //                         'X-Requested-With':
        //                             'XMLHttpRequest',

        //                         'Accept':
        //                             'application/json'
        //                     },

        //                     cache: 'no-store'
        //                 }
        //             );

        //         const responseText =
        //             await response.text();

        //         let result;

        //         try {

        //             result =
        //                 JSON.parse(
        //                     responseText
        //                 );

        //         } catch (error) {

        //             console.error(
        //                 'Invalid clear-status response:',
        //                 responseText
        //             );

        //             return;

        //         }

        //         if (
        //             !response.ok ||
        //             !result.success
        //         ) {
        //             return;
        //         }

        //         const clearTime =
        //             result.messages_cleared_at
        //             || null;

        //         /*
        //         |--------------------------------------------------------------------------
        //         | Store Initial Clear Timestamp
        //         |--------------------------------------------------------------------------
        //         */

        //         if (!clearStatusInitialized) {

        //             lastKnownClearTime =
        //                 clearTime;

        //             clearStatusInitialized =
        //                 true;

        //             return;

        //         }

        //         /*
        //         |--------------------------------------------------------------------------
        //         | Another User Cleared the Conversation
        //         |--------------------------------------------------------------------------
        //         */

        //         if (
        //             clearTime !== null &&
        //             clearTime !==
        //                 lastKnownClearTime
        //         ) {

        //             cleanConversationScreen();

        //             lastKnownClearTime =
        //                 clearTime;

        //         }

        //     } catch (error) {

        //         console.error(
        //             'Clear status polling error:',
        //             error
        //         );

        //     } finally {

        //         clearPollingInProgress =
        //             false;

        //     }

        // }


        /*
        |--------------------------------------------------------------------------
        | Clear Chat
        |--------------------------------------------------------------------------
        */

        button.addEventListener(
            'click',
            async function () {

                const confirmed =
                    window.confirm(
                        'Clear this conversation for everyone?\n\n' +
                        'All transmissions and attachments will be permanently removed.'
                    );

                if (!confirmed) {
                    return;
                }

                button.disabled =
                    true;

                const originalButtonHtml =
                    button.innerHTML;

                button.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm"
                        aria-hidden="true"
                    ></span>
                `;

                try {

                    const formData =
                        new FormData();

                    const csrfToken =
                        transmissionForm
                            ?.querySelector(
                                'input[name="_token"]'
                            )
                            ?.value || '';

                    if (csrfToken !== '') {

                        formData.append(
                            '_token',
                            csrfToken
                        );

                    }

                    const response =
                        await fetch(
                            window.REDALIEN_BASE_URL +
                            '/messages/clear/' +
                            encodeURIComponent(
                                channelId
                            ),
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
                            'Invalid Clear Chat response:',
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
                            'The conversation could not be cleared.'
                        );
                    }

                    cleanConversationScreen();

                    // lastKnownClearTime =
                    //     result.messages_cleared_at
                    //     || null;

                    // clearStatusInitialized =
                    //     true;

                } catch (error) {

                    console.error(
                        'Clear chat error:',
                        error
                    );

                    alert(
                        error.message ||
                        'The conversation could not be cleared.'
                    );

                } finally {

                    button.disabled =
                        false;

                    button.innerHTML =
                        originalButtonHtml;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Start Clear Status Polling
        |--------------------------------------------------------------------------
        */

        // window.setInterval(
        //     pollClearStatus,
        //     3000
        // );

        // pollClearStatus();

    }
);