document.addEventListener('DOMContentLoaded', function () {

    const transmissionInput =
        document.getElementById('transmissionInput');

    const emojiButton =
        document.getElementById('composerEmojiButton');

    const emojiPicker =
        document.getElementById('composerEmojiPicker');

    if (
        !transmissionInput ||
        !emojiButton ||
        !emojiPicker
    ) {
        return;
    }

    emojiButton.addEventListener(
        'click',
        function (event) {

            event.preventDefault();
            event.stopPropagation();

            emojiPicker.hidden =
                !emojiPicker.hidden;

        }
    );


    emojiPicker.addEventListener(
        'click',
        function (event) {

            const emojiOption =
                event.target.closest(
                    '[data-composer-emoji]'
                );

            if (!emojiOption) {
                return;
            }

            event.preventDefault();

            const emoji =
                emojiOption.dataset.composerEmoji;

            if (!emoji) {
                return;
            }

            insertEmojiAtCursor(
                transmissionInput,
                emoji
            );

            emojiPicker.hidden = true;

            transmissionInput.dispatchEvent(
                new Event(
                    'input',
                    {
                        bubbles: true
                    }
                )
            );

            transmissionInput.focus();

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '#composerEmojiPicker'
                ) ||
                event.target.closest(
                    '#composerEmojiButton'
                )
            ) {
                return;
            }

            emojiPicker.hidden = true;

        }
    );


    function insertEmojiAtCursor(
        input,
        emoji
    ) {

        const start =
            input.selectionStart ??
            input.value.length;

        const end =
            input.selectionEnd ??
            input.value.length;

        const before =
            input.value.substring(
                0,
                start
            );

        const after =
            input.value.substring(
                end
            );

        input.value =
            before +
            emoji +
            after;

        const nextCursorPosition =
            start +
            emoji.length;

        input.setSelectionRange(
            nextCursorPosition,
            nextCursorPosition
        );

    }

});