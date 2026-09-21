document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Typing Elements
    |--------------------------------------------------------------------------
    */

    const transmissionForm =
        document.getElementById('transmissionForm');

    const transmissionInput =
        document.getElementById('transmissionInput');

    const transmissionMessages =
        document.getElementById('transmissionMessages');

    const typingIndicator =
        document.getElementById('typingIndicator');

    const channelId =
        transmissionMessages
            ?.dataset.channelId || '';

    const csrfToken =
        transmissionForm
            ?.querySelector(
                'input[name="_token"]'
            )
            ?.value || '';

    let typingTimeout = null;
    let lastTypingState = false;
    let typingPollingInProgress = false;


    /*
    |--------------------------------------------------------------------------
    | Stop If Typing Elements Are Missing
    |--------------------------------------------------------------------------
    */

    if (
        !transmissionInput ||
        !typingIndicator ||
        !channelId
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Send Typing State
    |--------------------------------------------------------------------------
    */

    async function sendTypingState(
        isTyping
    ) {

        if (
            lastTypingState === isTyping
        ) {
            return;
        }

        const previousState =
            lastTypingState;

        lastTypingState =
            isTyping;

        const formData =
            new FormData();

        formData.append(
            'is_typing',
            isTyping ? '1' : '0'
        );

        if (csrfToken !== '') {

            formData.append(
                '_token',
                csrfToken
            );

        }

        try {

            const response = await fetch(
                window.REDALIEN_BASE_URL +
                '/messages/typing/' +
                encodeURIComponent(channelId),
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

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                lastTypingState =
                    previousState;
            }

        } catch (error) {

            lastTypingState =
                previousState;

            console.error(
                'Typing status error:',
                error
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Detect User Typing
    |--------------------------------------------------------------------------
    */

    transmissionInput.addEventListener(
        'input',
        function () {

            const hasText =
                transmissionInput
                    .value
                    .trim() !== '';

            sendTypingState(
                hasText
            );

            window.clearTimeout(
                typingTimeout
            );

            if (hasText) {

                typingTimeout =
                    window.setTimeout(
                        function () {

                            sendTypingState(
                                false
                            );

                        },
                        1500
                    );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Stop Typing After Message Is Sent
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'redalien:message-sent',
        function () {

            window.clearTimeout(
                typingTimeout
            );

            sendTypingState(
                false
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Poll Typing Status
    |--------------------------------------------------------------------------
    */

    async function pollTypingStatus() {

        if (
            document.hidden ||
            typingPollingInProgress
        ) {
            return;
        }

        typingPollingInProgress = true;

        try {

            const response = await fetch(
                window.REDALIEN_BASE_URL +
                '/messages/typingStatus/' +
                encodeURIComponent(channelId),
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

            renderTypingIndicator(
                Array.isArray(
                    result.typers
                )
                    ? result.typers
                    : []
            );

        } catch (error) {

            console.error(
                'Typing indicator polling error:',
                error
            );

        } finally {

            typingPollingInProgress = false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Render Typing Indicator
    |--------------------------------------------------------------------------
    */

    function renderTypingIndicator(
        typers
    ) {

        const label =
            typingIndicator.querySelector(
                'small'
            );

        if (!label) {
            return;
        }

        if (typers.length === 0) {

            typingIndicator.classList.remove(
                'is-active'
            );

            label.textContent =
                'No crew member is transmitting';

            return;
        }

        const names =
            typers
                .map(function (typer) {

                    return typer.name;

                })
                .filter(Boolean);

        if (names.length === 0) {
            return;
        }

        typingIndicator.classList.add(
            'is-active'
        );

        if (names.length === 1) {

            label.textContent =
                names[0] +
                ' is transmitting...';

        } else if (names.length === 2) {

            label.textContent =
                names[0] +
                ' and ' +
                names[1] +
                ' are transmitting...';

        } else {

            label.textContent =
                names[0] +
                ' and ' +
                (names.length - 1) +
                ' others are transmitting...';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Start Typing Polling
    |--------------------------------------------------------------------------
    */

    window.setInterval(
        pollTypingStatus,
        1000
    );

    pollTypingStatus();

});