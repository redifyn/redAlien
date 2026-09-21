<?php

class Flash
{
    public static function success(string $message): void
    {
        $_SESSION['flash_success'] = $message;
    }

    public static function error(string $message): void
    {
        $_SESSION['flash_error'] = $message;
    }

    public static function display(): void
{
    /*
    |--------------------------------------------------------------------------
    | Success Message
    |--------------------------------------------------------------------------
    */

    if (!empty($_SESSION['flash_success'])) {

        $message =
            htmlspecialchars(
                $_SESSION['flash_success'],
                ENT_QUOTES,
                'UTF-8'
            );

        echo '
            <div
                class="
                    ra-flash-message
                    ra-flash-success
                    alert
                    alert-dismissible
                    fade
                    show
                "
                role="alert"
            >

                <div class="ra-flash-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div class="ra-flash-content">

                    <div class="ra-flash-title">
                        Transmission Successful
                    </div>

                    <div class="ra-flash-text">
                        ' . $message . '
                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>

            </div>
        ';

        unset(
            $_SESSION['flash_success']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Error Message
    |--------------------------------------------------------------------------
    */

    if (!empty($_SESSION['flash_error'])) {

        $message =
            htmlspecialchars(
                $_SESSION['flash_error'],
                ENT_QUOTES,
                'UTF-8'
            );

        echo '
            <div
                class="
                    ra-flash-message
                    ra-flash-error
                    alert
                    alert-dismissible
                    fade
                    show
                "
                role="alert"
            >

                <div class="ra-flash-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <div class="ra-flash-content">

                    <div class="ra-flash-title">
                        Transmission Failed
                    </div>

                    <div class="ra-flash-text">
                        ' . $message . '
                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>

            </div>
        ';

        unset(
            $_SESSION['flash_error']
        );
    }
}
}