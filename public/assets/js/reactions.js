/*
|--------------------------------------------------------------------------
| redAlien Message Reactions
|--------------------------------------------------------------------------
|
| Builds reaction controls, handles reaction clicks and keeps reaction
| counts synchronized across browsers.
|
*/


/*
|--------------------------------------------------------------------------
| Reaction Configuration
|--------------------------------------------------------------------------
*/

const REDALIEN_REACTION_EMOJIS = [
    '👍',
    '❤️',
    '😂',
    '😮',
    '😢',
    '🚀'
];


/*
|--------------------------------------------------------------------------
| Public Reaction Markup Builder
|--------------------------------------------------------------------------
|
| messages.js uses this when it creates a new message through AJAX.
|
*/

window.RedAlienReactions = {

    buildControlHtml(messageId) {

        const safeMessageId =
            String(messageId ?? '');

        const reactionOptionsHtml =
            REDALIEN_REACTION_EMOJIS
                .map(function (emoji) {

                    return `
                        <button
                            type="button"
                            class="ra-reaction-option"
                            data-emoji="${emoji}"
                            data-message-id="${safeMessageId}"
                            aria-label="React with ${emoji}"
                        >
                            ${emoji}
                        </button>
                    `;

                })
                .join('');

        return `
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
                    ${reactionOptionsHtml}
                </div>

            </div>
        `;

    }

};


document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Core Elements
        |--------------------------------------------------------------------------
        */

        const transmissionForm =
            document.getElementById(
                'transmissionForm'
            );

        const transmissionMessages =
            document.getElementById(
                'transmissionMessages'
            );

        const channelId =
            transmissionMessages
                ?.dataset.channelId || '';

        const csrfToken =
            transmissionForm
                ?.querySelector(
                    'input[name="_token"]'
                )
                ?.value || '';

        let reactionPollingInProgress = false;


        if (
            !transmissionMessages ||
            !channelId
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Open and Close Reaction Picker
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (event) {

                const addButton =
                    event.target.closest(
                        '.ra-add-reaction-button'
                    );

                if (addButton) {

                    event.preventDefault();
                    event.stopPropagation();

                    const control =
                        addButton.closest(
                            '.ra-reaction-control'
                        );

                    const picker =
                        control?.querySelector(
                            '.ra-reaction-picker'
                        );

                    if (!picker) {
                        return;
                    }

                    closeAllReactionPickers(
                        picker
                    );

                    picker.hidden =
                        !picker.hidden;

                    return;
                }

                if (
                    !event.target.closest(
                        '.ra-reaction-picker'
                    )
                ) {
                    closeAllReactionPickers();
                }

            }
        );


        function closeAllReactionPickers(
            exceptPicker = null
        ) {

            document
                .querySelectorAll(
                    '.ra-reaction-picker'
                )
                .forEach(function (picker) {

                    if (picker !== exceptPicker) {
                        picker.hidden = true;
                    }

                });

        }


        /*
        |--------------------------------------------------------------------------
        | Toggle a Reaction
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            async function (event) {

                const button =
                    event.target.closest(
                        '.ra-reaction-option'
                    );

                if (!button) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const messageId =
                    button.dataset.messageId;

                const emoji =
                    button.dataset.emoji;

                if (
                    !messageId ||
                    !emoji
                ) {
                    return;
                }

                button.disabled = true;

                try {

                    const formData =
                        new FormData();

                    formData.append(
                        'emoji',
                        emoji
                    );

                    if (csrfToken !== '') {

                        formData.append(
                            '_token',
                            csrfToken
                        );

                    }

                    const response = await fetch(
                        window.REDALIEN_BASE_URL +
                        '/messages/react/' +
                        encodeURIComponent(
                            messageId
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

                    const result =
                        await response.json();

                    if (
                        !response.ok ||
                        !result.success
                    ) {
                        throw new Error(
                            result.message ||
                            'The reaction could not be updated.'
                        );
                    }

                    renderMessageReactions(
                        messageId,
                        Array.isArray(
                            result.reactions
                        )
                            ? result.reactions
                            : []
                    );

                    closeAllReactionPickers();

                } catch (error) {

                    console.error(
                        'Reaction error:',
                        error
                    );

                    alert(
                        error.message ||
                        'The reaction could not be updated.'
                    );

                } finally {

                    button.disabled = false;

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Click an Existing Reaction Chip
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (event) {

                const chip =
                    event.target.closest(
                        '.ra-reaction-chip'
                    );

                if (!chip) {
                    return;
                }

                const article =
                    chip.closest(
                        '.ra-transmission-item'
                    );

                const messageId =
                    article?.dataset.messageId;

                const emoji =
                    chip.dataset.emoji;

                if (
                    !messageId ||
                    !emoji
                ) {
                    return;
                }

                const matchingOption =
                    article.querySelector(
                        `.ra-reaction-option[data-emoji="${emoji}"]`
                    );

                matchingOption?.click();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Render One Message's Reactions
        |--------------------------------------------------------------------------
        */

        function renderMessageReactions(
            messageId,
            reactions
        ) {

            const article =
                transmissionMessages.querySelector(
                    `[data-message-id="${messageId}"]`
                );

            if (!article) {
                return;
            }

            const container =
                article.querySelector(
                    '[data-reactions]'
                );

            if (!container) {
                return;
            }

            if (reactions.length === 0) {

                container.innerHTML = '';

                return;
            }

            container.innerHTML =
                reactions
                    .map(function (reaction) {

                        const emoji =
                            RedAlienUtils.escapeHtml(
                                reaction.emoji || ''
                            );

                        const total =
                            Number(
                                reaction.total || 0
                            );

                        const isReacted =
                            Number(
                                reaction
                                    .reacted_by_current_user
                                    ?? 0
                            ) === 1;

                        return `
                            <button
                                type="button"
                                class="
                                    ra-reaction-chip
                                    ${isReacted
                                        ? 'is-reacted'
                                        : ''}
                                "
                                data-emoji="${emoji}"
                                aria-label="${emoji} reaction"
                            >
                                <span
                                    class="ra-reaction-emoji"
                                >
                                    ${emoji}
                                </span>

                                <span
                                    class="ra-reaction-count"
                                >
                                    ${total}
                                </span>
                            </button>
                        `;

                    })
                    .join('');

        }


        /*
        |--------------------------------------------------------------------------
        | Poll All Reactions in Current Pod
        |--------------------------------------------------------------------------
        */

        async function pollChannelReactions() {

            if (
                reactionPollingInProgress ||
                document.hidden
            ) {
                return;
            }

            reactionPollingInProgress = true;

            try {

                const response = await fetch(
                    window.REDALIEN_BASE_URL +
                    '/messages/reactions/' +
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
                    throw new Error(
                        result.message ||
                        'Unable to load reactions.'
                    );
                }

                const groupedReactions = {};

                const reactions =
                    Array.isArray(
                        result.reactions
                    )
                        ? result.reactions
                        : [];

                reactions.forEach(
                    function (reaction) {

                        const messageId =
                            String(
                                reaction.message_id
                                ?? ''
                            );

                        if (messageId === '') {
                            return;
                        }

                        if (
                            !groupedReactions[
                                messageId
                            ]
                        ) {
                            groupedReactions[
                                messageId
                            ] = [];
                        }

                        groupedReactions[
                            messageId
                        ].push(reaction);

                    }
                );

                transmissionMessages
                    .querySelectorAll(
                        '.ra-transmission-item[data-message-id]'
                    )
                    .forEach(
                        function (article) {

                            const messageId =
                                article.dataset
                                    .messageId;

                            renderMessageReactions(
                                messageId,
                                groupedReactions[
                                    messageId
                                ] || []
                            );

                        }
                    );

            } catch (error) {

                console.error(
                    'Reaction polling error:',
                    error
                );

            } finally {

                reactionPollingInProgress =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Start Reaction Polling
        |--------------------------------------------------------------------------
        */

        window.setInterval(
            pollChannelReactions,
            3000
        );

        pollChannelReactions();

    }
);