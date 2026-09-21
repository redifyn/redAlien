document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('raSidebar');
    const backdrop = document.getElementById('raSidebarBackdrop');

    const mobileOpenButton =
        document.getElementById('raMenuToggle');

    const mobileCloseButton =
        document.getElementById('raSidebarClose');

    const desktopToggle =
        document.getElementById('raDesktopSidebarToggle');


    function openMobileSidebar() {

        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.add('is-open');
        backdrop.classList.add('is-open');

        document.body.style.overflow = 'hidden';
    }


    function closeMobileSidebar() {

        if (!sidebar || !backdrop) {
            return;
        }

        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-open');

        document.body.style.overflow = '';
    }


    function setDesktopSidebarState(collapsed) {

        document.body.classList.toggle(
            'ra-sidebar-collapsed',
            collapsed
        );

        localStorage.setItem(
            'redAlienSidebarCollapsed',
            collapsed ? '1' : '0'
        );

        if (!desktopToggle) {
            return;
        }

        const icon =
            desktopToggle.querySelector('i');

        if (icon) {

            icon.className = collapsed
                ? 'bi bi-layout-sidebar'
                : 'bi bi-layout-sidebar-inset';

        }

        desktopToggle.setAttribute(
            'aria-label',
            collapsed
                ? 'Expand sidebar'
                : 'Collapse sidebar'
        );

        desktopToggle.title =
            collapsed
                ? 'Expand sidebar'
                : 'Collapse sidebar';
    }


    /*
    |--------------------------------------------------------------------------
    | Restore remembered desktop state
    |--------------------------------------------------------------------------
    */

    const savedSidebarState =
        localStorage.getItem(
            'redAlienSidebarCollapsed'
        );

    if (
        savedSidebarState === '1' &&
        window.innerWidth >= 1200
    ) {
        setDesktopSidebarState(true);
    }


    /*
    |--------------------------------------------------------------------------
    | Desktop toggle
    |--------------------------------------------------------------------------
    */

    desktopToggle?.addEventListener(
        'click',
        function () {

            const currentlyCollapsed =
                document.body.classList.contains(
                    'ra-sidebar-collapsed'
                );

            setDesktopSidebarState(
                !currentlyCollapsed
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Mobile controls
    |--------------------------------------------------------------------------
    */

    mobileOpenButton?.addEventListener(
        'click',
        openMobileSidebar
    );

    mobileCloseButton?.addEventListener(
        'click',
        closeMobileSidebar
    );

    backdrop?.addEventListener(
        'click',
        closeMobileSidebar
    );


    /*
    |--------------------------------------------------------------------------
    | Keyboard controls
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                closeMobileSidebar();
            }

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key === '/'
            ) {

                event.preventDefault();

                document
                    .querySelector(
                        '.ra-search-wrap input'
                    )
                    ?.focus();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Handle screen resizing
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth >= 1200) {
                closeMobileSidebar();

                const savedState =
                    localStorage.getItem(
                        'redAlienSidebarCollapsed'
                    );

                setDesktopSidebarState(
                    savedState === '1'
                );

            } else {

                document.body.classList.remove(
                    'ra-sidebar-collapsed'
                );
            }
        }
    );

});