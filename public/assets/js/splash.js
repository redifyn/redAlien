document.addEventListener('DOMContentLoaded', function () {

    const splash = document.getElementById('raSplash');
    const app = document.getElementById('raDashboardApp');
    const status = document.getElementById('raSplashStatus');
    const progress = document.getElementById('raSplashProgress');
    const percent = document.getElementById('raSplashPercent');

    /*
    |--------------------------------------------------------------------------
    | The splash is not rendered after it has already been shown
    |--------------------------------------------------------------------------
    */

    if (!splash) {

        if (app) {
            app.classList.remove('ra-dashboard-waiting');
            app.classList.add('ra-dashboard-ready');
        }

        return;
    }


    const stages = [
        {
            at: 0,
            progress: 5,
            message: 'Initializing Command Center...'
        },
        {
            at: 650,
            progress: 20,
            message: 'Waking alien interface...'
        },
        {
            at: 1200,
            progress: 38,
            message: 'Scanning system...'
        },
        {
            at: 1800,
            progress: 57,
            message: 'Establishing alien link...'
        },
        {
            at: 2400,
            progress: 74,
            message: 'Synchronizing pods...'
        },
        {
            at: 3000,
            progress: 90,
            message: 'Loading Command Center...'
        },
        {
            at: 3550,
            progress: 100,
            message: 'Command Center ready.'
        }
    ];


    function updateStage(stage) {

        if (status) {
            status.textContent = stage.message;
        }

        if (progress) {
            progress.style.width = stage.progress + '%';
        }

        if (percent) {
            percent.textContent = stage.progress + '%';
        }
    }


    stages.forEach(function (stage) {

        window.setTimeout(function () {
            updateStage(stage);
        }, stage.at);

    });


    window.setTimeout(function () {

        splash.classList.add('ra-splash-hidden');

        if (app) {
            app.classList.remove('ra-dashboard-waiting');
            app.classList.add('ra-dashboard-ready');
        }

    }, 4100);


    window.setTimeout(function () {

        splash.remove();

    }, 4900);

});