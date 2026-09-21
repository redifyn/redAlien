<?php

$showSplash =
    empty(
        $_SESSION[
            'redalien_splash_seen'
        ]
    );

if ($showSplash) {

    $_SESSION[
        'redalien_splash_seen'
    ] = true;
}

?>


<?php if ($showSplash): ?>

<div
    id="raSplash"
    class="ra-splash"
    aria-hidden="false"
>

    <div class="ra-splash-grid"></div>

    <div class="ra-splash-particles"></div>


    <div class="ra-splash-content">

        <div class="ra-splash-logo-wrap">

            <img
                src="<?= BASE_URL; ?>/assets/images/redalien-logo.svg"
                alt="RedAlien"
                class="ra-splash-logo"
                id="raSplashLogo"
            >

            <div
                class="ra-splash-scan-line"
            ></div>

        </div>


        <h1 class="ra-splash-title">

            <span class="ra-splash-red">
                red
            </span>

            <span class="ra-splash-white">
                Alien
            </span>

        </h1>


        <p
            class="ra-splash-status"
            id="raSplashStatus"
        >
            Initializing Command Center...
        </p>


        <div class="ra-splash-progress-wrap">

            <div class="ra-splash-progress-track">

                <div
                    class="ra-splash-progress-fill"
                    id="raSplashProgress"
                ></div>

            </div>

            <span id="raSplashPercent">
                0%
            </span>

        </div>

    </div>

</div>

<?php endif; ?>