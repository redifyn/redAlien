<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document
        .querySelectorAll('[data-password-target]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const field =
                    document.getElementById(
                        this.dataset.passwordTarget
                    );

                if (!field) {
                    return;
                }

                const icon =
                    this.querySelector('i');

                if (field.type === 'password') {

                    field.type = 'text';

                    if (icon) {
                        icon.className = 'bi bi-eye-slash';
                    }

                    this.setAttribute(
                        'aria-label',
                        'Hide password'
                    );

                } else {

                    field.type = 'password';

                    if (icon) {
                        icon.className = 'bi bi-eye';
                    }

                    this.setAttribute(
                        'aria-label',
                        'Show password'
                    );
                }

            });

        });

});
</script>
</body>
</html>