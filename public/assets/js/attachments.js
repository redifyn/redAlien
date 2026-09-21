document.addEventListener(
    'DOMContentLoaded',
    function () {

        const button =
            document.getElementById(
                'attachmentButton'
            );

        const input =
            document.getElementById(
                'attachmentInput'
            );

        /*
        |--------------------------------------------------------------------------
        | This file is loaded globally, so quietly stop on pages
        | without an attachment composer.
        |--------------------------------------------------------------------------
        */

        if (
            !button ||
            !input
        ) {
            return;
        }

        button.addEventListener(
            'click',
            function () {

                input.click();

            }
        );

    }
);