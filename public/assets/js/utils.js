window.RedAlienUtils = {

    escapeHtml(value) {
        const element =
            document.createElement('div');

        element.textContent =
            String(value ?? '');

        return element.innerHTML;
    },

    getAvatar(avatar) {
        if (
            typeof avatar === 'string' &&
            /^https?:\/\//i.test(avatar)
        ) {
            return avatar;
        }

        if (
            typeof avatar === 'string' &&
            avatar.trim() !== ''
        ) {
            return (
                window.REDALIEN_BASE_URL +
                '/' +
                avatar.replace(/^\/+/, '')
            );
        }

        return (
            window.REDALIEN_BASE_URL +
            '/assets/images/avatars/default.svg'
        );
    },

    removeTransmissionWelcome(container) {
        const welcome =
            container?.querySelector(
                '.ra-transmission-welcome'
            );

        welcome?.remove();
    },

    scrollToBottom(container) {
        if (!container) {
            return;
        }

        container.scrollTop =
            container.scrollHeight;
    },

    formatLastSeen(lastSeen) {
        if (!lastSeen) {
            return 'Offline';
        }

        const parsedDate =
            new Date(
                String(lastSeen).replace(' ', 'T')
            );

        if (Number.isNaN(parsedDate.getTime())) {
            return 'Offline';
        }

        const differenceSeconds =
            Math.max(
                0,
                Math.floor(
                    (Date.now() - parsedDate.getTime()) /
                    1000
                )
            );

        if (differenceSeconds < 60) {
            return 'Offline just now';
        }

        const differenceMinutes =
            Math.floor(
                differenceSeconds / 60
            );

        if (differenceMinutes < 60) {
            return (
                'Last seen ' +
                differenceMinutes +
                ' minute' +
                (differenceMinutes === 1 ? '' : 's') +
                ' ago'
            );
        }

        const differenceHours =
            Math.floor(
                differenceMinutes / 60
            );

        if (differenceHours < 24) {
            return (
                'Last seen ' +
                differenceHours +
                ' hour' +
                (differenceHours === 1 ? '' : 's') +
                ' ago'
            );
        }

        return (
            'Last seen ' +
            parsedDate.toLocaleDateString()
        );
    }

};

function escapeTransmissionHtml(value) {

    const element =
        document.createElement('div');

    element.textContent =
        String(value ?? '');

    return element.innerHTML;

}

function getTransmissionAvatar(avatar) {

    if (
        typeof avatar === 'string' &&
        /^https?:\/\//i.test(avatar)
    ) {
        return avatar;
    }

    if (
        typeof avatar === 'string' &&
        avatar.trim() !== ''
    ) {
        return (
            window.REDALIEN_BASE_URL +
            '/' +
            avatar.replace(/^\/+/, '')
        );
    }

    return (
        window.REDALIEN_BASE_URL +
        '/assets/images/avatars/default.svg'
    );

}



function removeTransmissionWelcome() {

    const welcome =
        transmissionMessages?.querySelector(
            '.ra-transmission-welcome'
        );

    welcome?.remove();

}



function scrollTransmissionConsoleToBottom() {

    if (!transmissionMessages) {
        return;
    }

    transmissionMessages.scrollTop =
        transmissionMessages.scrollHeight;

}




function formatLastSeen(lastSeen) {

    if (!lastSeen) {
        return 'Offline';
    }

    const parsedDate =
        new Date(
            String(lastSeen).replace(' ', 'T')
        );

    if (Number.isNaN(parsedDate.getTime())) {
        return 'Offline';
    }

    const differenceSeconds =
        Math.max(
            0,
            Math.floor(
                (Date.now() - parsedDate.getTime()) /
                1000
            )
        );

    if (differenceSeconds < 60) {
        return 'Offline just now';
    }

    const differenceMinutes =
        Math.floor(
            differenceSeconds / 60
        );

    if (differenceMinutes < 60) {
        return (
            'Last seen ' +
            differenceMinutes +
            ' minute' +
            (differenceMinutes === 1 ? '' : 's') +
            ' ago'
        );
    }

    const differenceHours =
        Math.floor(
            differenceMinutes / 60
        );

    if (differenceHours < 24) {
        return (
            'Last seen ' +
            differenceHours +
            ' hour' +
            (differenceHours === 1 ? '' : 's') +
            ' ago'
        );
    }

    return (
        'Last seen ' +
        parsedDate.toLocaleDateString()
    );

}