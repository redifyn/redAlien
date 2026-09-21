document.addEventListener('DOMContentLoaded', function() {

     /*
    |--------------------------------------------------------------------------
    | Create Alien page
    |--------------------------------------------------------------------------
    */

    const createAlienForm =
        document.getElementById('createAlienForm');

    if (createAlienForm) {

        const nameInput =
            document.getElementById('alienName');

        const descriptionInput =
            document.getElementById('alienDescription');

        const descriptionCounter =
            document.getElementById('descriptionCounter');

        const logoInput =
            document.getElementById('alienLogo');

        const logoPreview =
            document.getElementById('alienLogoPreview');

        const previewLogo =
            document.getElementById('previewAlienLogo');

        const previewName =
            document.getElementById('previewAlienName');

        const previewDescription =
            document.getElementById('previewAlienDescription');

        const previewVisibility =
            document.getElementById('previewVisibility');

        const createButton =
            document.getElementById('createAlienButton');


        nameInput?.addEventListener('input', function () {

            if (previewName) {
                previewName.textContent =
                    this.value.trim() || 'Untitled Alien';
            }

        });


        descriptionInput?.addEventListener('input', function () {

            if (previewDescription) {
                previewDescription.textContent =
                    this.value.trim() ||
                    'Your Alien description will appear here.';
            }

            if (descriptionCounter) {
                descriptionCounter.textContent =
                    this.value.length + ' / 1000';
            }

        });


        document
            .querySelectorAll('input[name="visibility"]')
            .forEach(function (radio) {

                radio.addEventListener('change', function () {

                    if (previewVisibility) {
                        previewVisibility.textContent =
                            this.value === 'public'
                                ? 'Public'
                                : 'Private';
                    }

                });

            });


        logoInput?.addEventListener('change', function () {

            const file = this.files?.[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image.');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Logo must not exceed 2MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                const imageUrl = event.target?.result;

                if (!imageUrl) {
                    return;
                }

                if (logoPreview) {
                    logoPreview.src = imageUrl;
                }

                if (previewLogo) {
                    previewLogo.src = imageUrl;
                }

            };

            reader.readAsDataURL(file);

        });


        createAlienForm.addEventListener('submit', function () {

            if (!createButton) {
                return;
            }

            createButton.disabled = true;

            const text = createButton.querySelector('span');
            const icon = createButton.querySelector('i');

            if (text) {
                text.textContent = 'Initializing Alien...';
            }

            if (icon) {
                icon.className =
                    'spinner-border spinner-border-sm';
            }

        });

    }

});