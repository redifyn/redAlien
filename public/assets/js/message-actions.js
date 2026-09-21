

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Core Elements
    |--------------------------------------------------------------------------
    */

    const transmissionForm =
        document.getElementById('transmissionForm');

    const transmissionMessages =
        document.getElementById('transmissionMessages');

    if (
        !transmissionForm ||
        !transmissionMessages
    ) {
        return;
    }


    function getCsrfToken() {

        return (
            transmissionForm
                .querySelector(
                    'input[name="_token"]'
                )
                ?.value || ''
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Edit Transmission
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async function (event) {

            const button =
                event.target.closest(
                    '.ra-edit-transmission-button'
                );

            if (!button) {
                return;
            }
            


            event.preventDefault();
            event.stopPropagation();

            const article =
                button.closest(
                    '.ra-transmission-item'
                );

            if (!article) {
                return;
            }

            const messageId =
                button.dataset.messageId;

            const messageElement =
                article.querySelector(
                    '[data-message-text]'
                );

            if (
                !messageId ||
                !messageElement
            ) {
                return;
            }

            const originalMessage =
                messageElement.innerText.trim();

            const updatedMessage =
                window.prompt(
                    'Edit transmission',
                    originalMessage
                );

            if (
                updatedMessage === null ||
                updatedMessage.trim() === '' ||
                updatedMessage.trim() === originalMessage
            ) {
                return;
            }

            button.disabled = true;

            try {

                const formData =
                    new FormData();

                formData.append(
                    'message',
                    updatedMessage.trim()
                );

                const csrfToken =
                    getCsrfToken();

                if (csrfToken !== '') {
                    formData.append(
                        '_token',
                        csrfToken
                    );
                }

                const response = await fetch(
                    window.REDALIEN_BASE_URL +
                    '/messages/update/' +
                    encodeURIComponent(messageId),
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
                        'The transmission could not be updated.'
                    );
                }

                const savedMessage =
                    result.transmission?.message ??
                    updatedMessage.trim();

                messageElement.innerHTML =
                    RedAlienUtils.escapeHtml(
                        savedMessage
                    ).replace(
                        /\n/g,
                        '<br>'
                    );

                addEditedLabel(
                    article
                );

            } catch (error) {

                console.error(
                    'Edit transmission error:',
                    error
                );

                alert(
                    error.message ||
                    'The transmission could not be updated.'
                );

            } finally {

                button.disabled = false;

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Edited Label
    |--------------------------------------------------------------------------
    */

    function addEditedLabel(article) {

        let editedLabel =
            article.querySelector(
                '.ra-transmission-edited-label'
            );

        if (editedLabel) {
            return;
        }

        editedLabel =
            document.createElement('small');

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


    /*
    |--------------------------------------------------------------------------
    | Delete Transmission
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async function (event) {

            const button =
                event.target.closest(
                    '.ra-delete-transmission-button'
                );

            if (!button) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const messageId =
                button.dataset.messageId;

            const article =
                button.closest(
                    '.ra-transmission-item'
                );

            if (
                !messageId ||
                !article
            ) {
                return;
            }

            const confirmed =
                window.confirm(
                    'Delete this transmission?\n\n' +
                    'This action cannot be undone.'
                );

            if (!confirmed) {
                return;
            }

            button.disabled = true;

            const originalButtonHtml =
                button.innerHTML;

            button.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm"
                    aria-hidden="true"
                ></span>

                <span>Deleting...</span>
            `;

            try {

                const formData =
                    new FormData();

                const csrfToken =
                    getCsrfToken();

                if (csrfToken !== '') {
                    formData.append(
                        '_token',
                        csrfToken
                    );
                }

                const response = await fetch(
                    window.REDALIEN_BASE_URL +
                    '/messages/delete/' +
                    encodeURIComponent(messageId),
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
                        'The transmission could not be deleted.'
                    );
                }

                markTransmissionDeleted(
                    article
                );

            } catch (error) {

                console.error(
                    'Delete transmission error:',
                    error
                );

                alert(
                    error.message ||
                    'The transmission could not be deleted.'
                );

                button.disabled = false;

                button.innerHTML =
                    originalButtonHtml;

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Mark Transmission Deleted
    |--------------------------------------------------------------------------
    */

  function markTransmissionDeleted(article) {

    if (!article) {
        return;
    }

    const messageElement =
        article.querySelector(
            '[data-message-text]'
        );

    /*
    |--------------------------------------------------------------------------
    | Remove Every Attachment Type
    |--------------------------------------------------------------------------
    */

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
    | Show Deleted Label
    |--------------------------------------------------------------------------
    */

    if (messageElement) {

        messageElement.hidden = false;

        messageElement.textContent =
            'This transmission was deleted.';

        messageElement.classList.add(
            'is-deleted'
        );

    }

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

}

});