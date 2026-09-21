
document.addEventListener('DOMContentLoaded', function () {

    const attachmentInput =
    document.getElementById('attachmentInput');
    /*
    |--------------------------------------------------------------------------
    | Core Elements
    |--------------------------------------------------------------------------
    */

    const transmissionForm =
        document.getElementById('transmissionForm');

    const transmissionInput =
        document.getElementById('transmissionInput');

    const transmissionSendButton =
        document.getElementById('transmissionSendButton');

    const transmissionMessages =
        document.getElementById('transmissionMessages');

    const crewPanel =
        document.getElementById('alienCrewPanel');

    const channelId =
        transmissionMessages?.dataset.channelId || '';

    const teamId =
        crewPanel?.dataset.teamId || '';

    let messagePollingInProgress = false;
    let presencePollingInProgress = false;


            /*
        |--------------------------------------------------------------------------
        | Tracks the last edit/delete change we've seen
        |--------------------------------------------------------------------------
        */

let lastChangesCheck =
    new Date()
        .toISOString()
        .slice(0, 19)
        .replace('T', ' ');


async function pollMessageChanges() {

    if (
        !channelId ||
        document.hidden
    ) {
        return;
    }

    try {

        const url =
            window.REDALIEN_BASE_URL +
            '/messages/changes/' +
            encodeURIComponent(channelId) +
            '?after_updated_at=' +
            encodeURIComponent(
                lastChangesCheck
            );

        const response = await fetch(
            url,
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
            throw new Error(
                result.message ||
                'Unable to load message changes.'
            );
        }

        const changes =
            Array.isArray(result.changes)
                ? result.changes
                : [];

        changes.forEach(
            function (change) {

                const messageId =
                    String(change.id ?? '');

                if (messageId === '') {
                    return;
                }

                const article =
                    transmissionMessages.querySelector(
                        `[data-message-id="${messageId}"]`
                    );

                if (!article) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Deleted Transmission
                |--------------------------------------------------------------------------
                */

                if (
                    Number(
                        change.is_deleted ?? 0
                    ) === 1
                ) {

                    const messageElement =
                        article.querySelector(
                            '[data-message-text]'
                        );

                    article
                    .querySelectorAll(
                        [
                            '.ra-transmission-image-link',
                            '.ra-transmission-file',
                            '.ra-transmission-audio'
                        ].join(',')
                    )
                    .forEach(
                        function (attachment) {
                            attachment.remove();
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Show deleted message text
                    |--------------------------------------------------------------------------
                    */

                    if (messageElement) {

                        messageElement.hidden =
                            false;

                        messageElement.textContent =
                            'This transmission was deleted.';

                        messageElement.classList.add(
                            'is-deleted'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Clean message controls
                    |--------------------------------------------------------------------------
                    */

                    article.classList.add(
                        'is-deleted'
                    );

                    article
                        .querySelector(
                            '.ra-transmission-actions'
                        )
                        ?.remove();

                    article
                        .querySelector(
                            '.ra-reaction-control'
                        )
                        ?.remove();

                    article
                        .querySelector(
                            '[data-reactions]'
                        )
                        ?.replaceChildren();

                    article
                        .querySelector(
                            '.ra-transmission-edited-label'
                        )
                        ?.remove();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Edited Transmission
                |--------------------------------------------------------------------------
                */

                if (
                    Number(
                        change.is_edited ?? 0
                    ) === 1
                ) {

                    const messageElement =
                        article.querySelector(
                            '[data-message-text]'
                        );

                    if (messageElement) {

                        messageElement.hidden =
                            false;

                        messageElement.innerHTML =
                            RedAlienUtils.escapeHtml(
                                change.message || ''
                            ).replace(
                                /\n/g,
                                '<br>'
                            );

                        messageElement.classList.remove(
                            'is-deleted'
                        );

                    }

                    let editedLabel =
                        article.querySelector(
                            '.ra-transmission-edited-label'
                        );

                    if (!editedLabel) {

                        editedLabel =
                            document.createElement(
                                'small'
                            );

                        editedLabel.className =
                            'ra-transmission-edited-label';

                        editedLabel.textContent =
                            '(edited)';

                        const meta =
                            article.querySelector(
                                '.ra-transmission-meta'
                            ) ||
                            article.querySelector(
                                '.ra-transmission-group-time'
                            );

                        meta?.appendChild(
                            editedLabel
                        );

                    }

                }

            }
        );

        if (result.server_time) {

            lastChangesCheck =
                result.server_time;

        }

    } catch (error) {

        console.error(
            'Message changes polling error:',
            error
        );

    }

}


    /*
    |--------------------------------------------------------------------------
    | Stop If This Is Not a Transmission Page
    |--------------------------------------------------------------------------
    */

    if (
        !transmissionForm ||
        !transmissionInput ||
        !transmissionSendButton ||
        !transmissionMessages ||
        !channelId
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Transmission Composer
    |--------------------------------------------------------------------------
    */

    function resizeTransmissionInput() {

        transmissionInput.style.height = 'auto';

        transmissionInput.style.height =
            Math.min(
                transmissionInput.scrollHeight,
                140
            ) + 'px';

    }


        function updateTransmissionButton() {

        const hasMessage =
            transmissionInput.value.trim() !== '';

        const hasAttachment =
            attachmentInput &&
            attachmentInput.files &&
            attachmentInput.files.length > 0;

        transmissionSendButton.disabled =
            !(hasMessage || hasAttachment);

    }


        attachmentInput?.addEventListener(
        'change',
        function () {

            updateTransmissionButton();

        }
    );

    transmissionInput.addEventListener(
        'input',
        function () {

            resizeTransmissionInput();
            updateTransmissionButton();

        }
    );


    transmissionInput.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Enter' &&
                !event.shiftKey
            ) {
                event.preventDefault();

                if (!transmissionSendButton.disabled) {
                    transmissionForm.requestSubmit();
                }
            }

        }
    );


    updateTransmissionButton();


  /*
|--------------------------------------------------------------------------
| AJAX Transmission Submission
|--------------------------------------------------------------------------
*/
transmissionForm.addEventListener(
    'submit',
    async function (event) {

        event.preventDefault();

        const message =
            transmissionInput.value.trim();

        const hasAttachment =
            attachmentInput &&
            attachmentInput.files &&
            attachmentInput.files.length > 0;

        if (
            message === '' &&
            !hasAttachment
        ) {
            return;
        }

        transmissionSendButton.disabled = true;

        const originalButtonHtml =
            transmissionSendButton.innerHTML;

        transmissionSendButton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
                aria-hidden="true"
            ></span>

            <span>Sending...</span>
        `;

        try {

            const formData =
                new FormData(transmissionForm);

            const response = await fetch(
                transmissionForm.action,
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
                throw new Error(
                    result.message ||
                    'The transmission could not be sent.'
                );
            }

            RedAlienUtils
                .removeTransmissionWelcome(
                    transmissionMessages
                );

            appendTransmission(
                result.transmission
            );

            transmissionInput.value = '';

            if (attachmentInput) {
                attachmentInput.value = '';
            }

            document.dispatchEvent(
                new CustomEvent(
                    'redalien:message-sent'
                )
            );

            resizeTransmissionInput();
            updateTransmissionButton();

            RedAlienUtils.scrollToBottom(
                transmissionMessages
            );

        } catch (error) {

            console.error(
                'Transmission error:',
                error
            );

            alert(
                error.message ||
                'The transmission could not be sent.'
            );

        } finally {

            transmissionSendButton.innerHTML =
                originalButtonHtml;

            updateTransmissionButton();

        }

    }
);


/*
|--------------------------------------------------------------------------
| Append Transmission
|--------------------------------------------------------------------------
*/

function appendTransmission(transmission) {

    if (!transmission) {
        return;
    }

    const messageId =
        String(transmission.id ?? '');

    if (
        messageId !== '' &&
        transmissionMessages.querySelector(
            `[data-message-id="${messageId}"]`
        )
    ) {
        return;
    }

    let transmissionList =
        transmissionMessages.querySelector(
            '.ra-transmission-list'
        );

    if (!transmissionList) {

        transmissionList =
            document.createElement('div');

        transmissionList.className =
            'ra-transmission-list';

        transmissionMessages.appendChild(
            transmissionList
        );

    }

    const senderName =
        RedAlienUtils.escapeHtml(
            transmission.sender_name || 'User'
        );

    const message =
        RedAlienUtils.escapeHtml(
            transmission.message || ''
        );

    const time =
        RedAlienUtils.escapeHtml(
            transmission.formatted_time || ''
        );

    const avatar =
        RedAlienUtils.getAvatar(
            transmission.sender_avatar
        );

    const senderId =
        String(transmission.sender_id ?? '');

    const lastTransmission =
        transmissionList.querySelector(
            '.ra-transmission-item:last-child'
        );

    const isGrouped =
        Boolean(lastTransmission) &&
        lastTransmission.dataset.senderId ===
            senderId;

    const currentUserId =
        String(
            window.REDALIEN_USER_ID ?? ''
        );

    const isOwn =
        senderId !== '' &&
        senderId === currentUserId;


    /*
    |--------------------------------------------------------------------------
    | Attachment
    |--------------------------------------------------------------------------
    */

    const attachmentPath =
        typeof transmission.attachment_path ===
            'string'
            ? transmission.attachment_path.trim()
            : '';

    const attachmentName =
        RedAlienUtils.escapeHtml(
            transmission.attachment_name ||
            'Attachment'
        );

    const attachmentMime =
        String(
            transmission.attachment_mime || ''
        ).toLowerCase();

    const messageType =
        String(
            transmission.message_type || ''
        ).toLowerCase();

    const attachmentUrl =
        attachmentPath !== ''
            ? (
                window.REDALIEN_BASE_URL +
                '/' +
                attachmentPath.replace(
                    /^\/+/,
                    ''
                )
            )
            : '';

    const isImageAttachment =
        attachmentPath !== '' &&
        (
            attachmentMime.startsWith(
                'image/'
            ) ||
            messageType === 'image'
        );

            const isAudioAttachment =
            attachmentPath !== '' &&
            (
                attachmentMime.startsWith(
                    'audio/'
                ) ||
                attachmentMime.startsWith(
                    'video/webm'
                ) ||
                messageType === 'audio'
            );

    const isFileAttachment =
        attachmentPath !== '' &&
        !isImageAttachment &&
        !isAudioAttachment;

    let attachmentHtml = '';

    if (isImageAttachment) {

        attachmentHtml = `
            <a
                class="ra-transmission-image-link"
                href="${attachmentUrl}"
                target="_blank"
                rel="noopener"
            >
                <img
                    class="ra-transmission-image"
                    src="${attachmentUrl}"
                    alt="${attachmentName}"
                    loading="lazy"
                >
            </a>
        `;

    } else if (isAudioAttachment) {

        attachmentHtml = `
            <div class="ra-transmission-audio">

                <div
                    class="ra-transmission-audio-label"
                >
                    <i class="bi bi-mic-fill"></i>

                    <span>
                        Voice note
                    </span>
                </div>

                <audio
                    class="ra-transmission-audio-player"
                    controls
                    preload="metadata"
                    src="${attachmentUrl}"
                >
                    Your browser does not support
                    audio playback.
                </audio>

            </div>
        `;

    } else if (isFileAttachment) {

        attachmentHtml = `
            <a
                class="ra-transmission-file"
                href="${attachmentUrl}"
                target="_blank"
                rel="noopener"
                download
            >
                <div
                    class="ra-transmission-file-icon"
                >
                    <i
                        class="bi bi-file-earmark"
                    ></i>
                </div>

                <div
                    class="ra-transmission-file-info"
                >
                    <strong>
                        ${attachmentName}
                    </strong>

                    <small>
                        Download file
                    </small>
                </div>
            </a>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Message Text
    |--------------------------------------------------------------------------
    */

    const messageHtml =
        message !== ''
            ? `
                <div
                    class="ra-transmission-text"
                    data-message-text
                >
                    ${message.replace(
                        /\n/g,
                        '<br>'
                    )}
                </div>
            `
            : `
                <div
                    class="ra-transmission-text"
                    data-message-text
                    hidden
                ></div>
            `;


    /*
    |--------------------------------------------------------------------------
    | Edit/Delete Actions
    |--------------------------------------------------------------------------
    */

    const actionsHtml =
        isOwn
            ? `
                <div
                    class="dropdown ra-transmission-actions"
                >
                    <button
                        type="button"
                        class="ra-transmission-actions-button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Transmission actions"
                    >
                        <i
                            class="bi bi-three-dots-vertical"
                        ></i>
                    </button>

                    <ul
                        class="dropdown-menu dropdown-menu-end"
                    >
                        <li>
                            <button
                                type="button"
                                class="dropdown-item ra-edit-transmission-button"
                                data-message-id="${messageId}"
                            >
                                <i
                                    class="bi bi-pencil-square"
                                ></i>

                                <span>
                                    Edit transmission
                                </span>
                            </button>
                        </li>

                        <li>
                            <button
                                type="button"
                                class="dropdown-item text-danger ra-delete-transmission-button"
                                data-message-id="${messageId}"
                            >
                                <i
                                    class="bi bi-trash3"
                                ></i>

                                <span>
                                    Delete transmission
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            `
            : '';


    /*
    |--------------------------------------------------------------------------
    | Reaction Picker
    |--------------------------------------------------------------------------
    */

    const reactionControlHtml =
        window.RedAlienReactions
            ?.buildControlHtml(
                messageId
            ) || '';


    /*
    |--------------------------------------------------------------------------
    | Build Transmission Element
    |--------------------------------------------------------------------------
    */

    const article =
        document.createElement('article');

    article.className =
        'ra-transmission-item' +
        (isOwn ? ' is-own' : '') +
        (isGrouped ? ' is-grouped' : '');

    article.dataset.messageId =
        messageId;

    article.dataset.senderId =
        senderId;

    article.innerHTML = `

        ${
            isGrouped
                ? `
                    <div
                        class="ra-transmission-avatar-spacer"
                    ></div>
                `
                : `
                    <img
                        class="ra-transmission-avatar"
                        src="${avatar}"
                        alt="${senderName}"
                        onerror="this.src='${window.REDALIEN_BASE_URL}/assets/images/avatars/default.svg';"
                    >
                `
        }

        <div class="ra-transmission-body">

            ${
                isGrouped
                    ? `
                        <div
                            class="ra-transmission-group-time"
                        >
                            <time>
                                ${time}
                            </time>
                        </div>
                    `
                    : `
                        <div
                            class="ra-transmission-meta"
                        >
                            <strong>
                                ${senderName}
                            </strong>

                            <time>
                                ${time}
                            </time>
                        </div>
                    `
            }

            ${attachmentHtml}

            ${messageHtml}

            <div
                class="ra-transmission-reactions"
                data-reactions
            ></div>

        </div>

        ${reactionControlHtml}

        ${actionsHtml}
    `;

    transmissionList.appendChild(
        article
    );

}


    /*
    |--------------------------------------------------------------------------
    | Latest Visible Message ID
    |--------------------------------------------------------------------------
    */

    function getLatestVisibleMessageId() {

        const messages =
            transmissionMessages.querySelectorAll(
                '.ra-transmission-item[data-message-id]'
            );

        if (messages.length === 0) {
            return 0;
        }

        const lastMessage =
            messages[messages.length - 1];

        return Number(
            lastMessage.dataset.messageId || 0
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Check Console Scroll Position
    |--------------------------------------------------------------------------
    */

    function isConsoleNearBottom() {

        const distanceFromBottom =
            transmissionMessages.scrollHeight -
            transmissionMessages.scrollTop -
            transmissionMessages.clientHeight;

        return distanceFromBottom < 120;

    }


    /*
    |--------------------------------------------------------------------------
    | Poll New Transmissions
    |--------------------------------------------------------------------------
    */

    async function pollNewTransmissions() {

        if (
            messagePollingInProgress ||
            document.hidden
        ) {
            return;
        }

        messagePollingInProgress = true;

        const shouldAutoScroll =
            isConsoleNearBottom();

        const afterMessageId =
            getLatestVisibleMessageId();

        try {

            const url =
                window.REDALIEN_BASE_URL +
                '/messages/latest/' +
                encodeURIComponent(channelId) +
                '?after_id=' +
                encodeURIComponent(
                    afterMessageId
                );

            const response = await fetch(
                url,
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
                throw new Error(
                    result.message ||
                    'Unable to load new transmissions.'
                );
            }

            const transmissions =
                Array.isArray(
                    result.transmissions
                )
                    ? result.transmissions
                    : [];

             

            if (transmissions.length === 0) {
                return;
            }

            RedAlienUtils
                .removeTransmissionWelcome(
                    transmissionMessages
                );

            transmissions.forEach(
                function (transmission) {

                    appendTransmission(
                        transmission
                    );

                }
            );

            if (shouldAutoScroll) {

                RedAlienUtils.scrollToBottom(
                    transmissionMessages
                );

            }

        } catch (error) {

            console.error(
                'Transmission polling error:',
                error
            );

        } finally {

            messagePollingInProgress = false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Online Presence Heartbeat
    |--------------------------------------------------------------------------
    */

    async function sendHeartbeat() {

        const formData =
            new FormData();

        const csrfToken =
            transmissionForm
                .querySelector(
                    'input[name="_token"]'
                )
                ?.value || '';

        if (csrfToken !== '') {

            formData.append(
                '_token',
                csrfToken
            );

        }

        try {

            const response = await fetch(
                window.REDALIEN_BASE_URL +
                '/messages/heartbeat',
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
                console.error(
                    'Heartbeat rejected:',
                    result
                );
            }

        } catch (error) {

            console.error(
                'Heartbeat failed:',
                error
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Poll Crew Presence
    |--------------------------------------------------------------------------
    */

    async function pollCrewPresence() {

        if (
            !crewPanel ||
            !teamId ||
            presencePollingInProgress ||
            document.hidden
        ) {
            return;
        }

        presencePollingInProgress = true;

        try {

            const response = await fetch(
                window.REDALIEN_BASE_URL +
                '/messages/crewPresence/' +
                encodeURIComponent(teamId),
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
                throw new Error(
                    result.message ||
                    'Unable to load crew presence.'
                );
            }

            const crew =
                Array.isArray(result.crew)
                    ? result.crew
                    : [];

            crew.forEach(
                function (member) {

                    updateCrewPresence(
                        member
                    );

                }
            );

        } catch (error) {

            console.error(
                'Crew presence polling error:',
                error
            );

        } finally {

            presencePollingInProgress = false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Crew Presence
    |--------------------------------------------------------------------------
    */

    function updateCrewPresence(member) {

        const userId =
            String(member.id ?? '');

        if (
            userId === '' ||
            !crewPanel
        ) {
            return;
        }

        const row =
            crewPanel.querySelector(
                `[data-crew-user-id="${userId}"]`
            );

        if (!row) {
            return;
        }

        const dot =
            row.querySelector(
                '[data-presence-dot]'
            );

        const label =
            row.querySelector(
                '[data-presence-label]'
            );

        const isOnline =
            Number(
                member.is_online ?? 0
            ) === 1;

        if (dot) {

            dot.classList.toggle(
                'is-online',
                isOnline
            );

            dot.classList.toggle(
                'is-offline',
                !isOnline
            );

        }

        if (label) {

            label.classList.toggle(
                'is-online',
                isOnline
            );

            label.classList.toggle(
                'is-offline',
                !isOnline
            );

            label.textContent =
                isOnline
                    ? 'Online'
                    : RedAlienUtils
                        .formatLastSeen(
                            member.last_seen
                        );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Start Polling Timers
    |--------------------------------------------------------------------------
    */

    window.setInterval(
        pollNewTransmissions,
        3000
    );

    window.setInterval(
    pollMessageChanges,
    3000
    );

    window.setInterval(
        sendHeartbeat,
        10000
    );

    window.setInterval(
        pollCrewPresence,
        5000
    );

    pollCrewPresence();
    sendHeartbeat();


    /*
    |--------------------------------------------------------------------------
    | Mark User Offline When Leaving
    |--------------------------------------------------------------------------
    */


    /*
|--------------------------------------------------------------------------
| Edit Transmission
|--------------------------------------------------------------------------
*/





    window.addEventListener(
        'beforeunload',
        function () {

            const offlineData =
                new FormData();

            const csrfToken =
                transmissionForm
                    .querySelector(
                        'input[name="_token"]'
                    )
                    ?.value || '';

            if (csrfToken !== '') {

                offlineData.append(
                    '_token',
                    csrfToken
                );

            }

            navigator.sendBeacon(
                window.REDALIEN_BASE_URL +
                '/messages/offline',
                offlineData
            );

        }
    );

});