document.addEventListener('DOMContentLoaded', function() {
  /*
    |--------------------------------------------------------------------------
    | Copy invite code
    |--------------------------------------------------------------------------
    */

    const mainCopyButton =
        document.getElementById('copyAlienInviteCode');

    mainCopyButton?.addEventListener('click', function () {

        const code = document
            .getElementById('alienInviteCode')
            ?.textContent
            .trim();

        if (!code) {
            return;
        }

        navigator.clipboard.writeText(code);

        const icon = this.querySelector('i');

        if (icon) {
            icon.className = 'bi bi-check-lg';
        }

        window.setTimeout(function () {

            if (icon) {
                icon.className = 'bi bi-copy';
            }

        }, 1500);

    });


    document
        .querySelectorAll('.ra-copy-code-button')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const code = this.dataset.code;

                if (!code) {
                    return;
                }

                navigator.clipboard.writeText(code);

                const icon = this.querySelector('i');

                if (icon) {
                    icon.className = 'bi bi-check-lg';
                }

                window.setTimeout(function () {

                    if (icon) {
                        icon.className = 'bi bi-copy';
                    }

                }, 1500);

            });

        });
});