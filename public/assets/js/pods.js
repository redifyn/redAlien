document.addEventListener('DOMContentLoaded', function() {

     /*
    |--------------------------------------------------------------------------
    | Create Pod form
    |--------------------------------------------------------------------------
    */

    const createPodForm =
        document.getElementById('createPodForm');

    const createPodButton =
        document.getElementById('createPodButton');

    createPodForm?.addEventListener('submit', function () {

        if (!createPodButton) {
            return;
        }

        createPodButton.disabled = true;

        const icon = createPodButton.querySelector('i');
        const text = createPodButton.querySelector('span');

        if (icon) {
            icon.className =
                'spinner-border spinner-border-sm';
        }

        if (text) {
            text.textContent = 'Creating Pod...';
        }

    });


});