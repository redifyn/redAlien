document.addEventListener(
    
    'DOMContentLoaded',
    function () {

        const voiceButton =
            document.getElementById(
                'voiceNoteButton'
            );

        const voiceRecorder =
            document.getElementById(
                'voiceRecorder'
            );

        const voicePreview =
            document.getElementById(
                'voicePreview'
            );

        const voiceTimer =
            document.getElementById(
                'voiceTimer'
            );

        const voiceDuration =
            document.getElementById(
                'voiceDuration'
            );

        const voiceAudio =
            document.getElementById(
                'voiceAudio'
            );

        const voicePlayButton =
            document.getElementById(
                'voicePlayButton'
            );

        const voiceCancelButton =
            document.getElementById(
                'voiceCancelButton'
            );

        const attachmentInput =
            document.getElementById(
                'attachmentInput'
            );

        if (
            !voiceButton ||
            !voiceRecorder ||
            !voicePreview ||
            !voiceTimer ||
            !voiceDuration ||
            !voiceAudio ||
            !voicePlayButton ||
            !voiceCancelButton ||
            !attachmentInput
        ) {
            return;
        }

        let mediaRecorder = null;
        let mediaStream = null;
        let audioChunks = [];
        let timerInterval = null;
        let recordingStartedAt = 0;
        let recordedAudioUrl = null;
        let isRecording = false;


        /*
        |--------------------------------------------------------------------------
        | Start or Stop Recording
        |--------------------------------------------------------------------------
        */

        voiceButton.addEventListener(
            'click',
            async function () {

                if (isRecording) {

                    mediaRecorder?.stop();

                    return;
                }

                try {

                    mediaStream =
                        await navigator.mediaDevices
                            .getUserMedia({
                                audio: true
                            });

                    audioChunks = [];

                    mediaRecorder =
                        createMediaRecorder(
                            mediaStream
                        );

                    mediaRecorder.addEventListener(
                        'dataavailable',
                        function (event) {

                            if (
                                event.data &&
                                event.data.size > 0
                            ) {
                                audioChunks.push(
                                    event.data
                                );
                            }

                        }
                    );

                    mediaRecorder.addEventListener(
                        'stop',
                        function () {

                            stopTimer();

                            const mimeType =
                                mediaRecorder.mimeType ||
                                'audio/webm';

                            const audioBlob =
                                new Blob(
                                    audioChunks,
                                    {
                                        type: mimeType
                                    }
                                );

                            const extension =
                                getAudioExtension(
                                    mimeType
                                );

                            const audioFile =
                                new File(
                                    [audioBlob],
                                    'voice-note-' +
                                    Date.now() +
                                    '.' +
                                    extension,
                                    {
                                        type: mimeType
                                    }
                                );

                            const dataTransfer =
                                new DataTransfer();

                            dataTransfer.items.add(
                                audioFile
                            );

                            attachmentInput.files =
                                dataTransfer.files;

                            attachmentInput.dispatchEvent(
                                new Event(
                                    'change',
                                    {
                                        bubbles: true
                                    }
                                )
                            );

                            if (recordedAudioUrl) {
                                URL.revokeObjectURL(
                                    recordedAudioUrl
                                );
                            }

                            recordedAudioUrl =
                                URL.createObjectURL(
                                    audioBlob
                                );

                            voiceAudio.src =
                                recordedAudioUrl;

                            voiceDuration.textContent =
                                formatDuration(
                                    Date.now() -
                                    recordingStartedAt
                                );

                            voiceRecorder.hidden = true;
                            voicePreview.hidden = false;

                            stopMicrophone();
                            setRecordingState(false);

                        }
                    );

                    mediaRecorder.start();

                    recordingStartedAt =
                        Date.now();

                    voicePreview.hidden = true;
                    voiceRecorder.hidden = false;

                    startTimer();
                    setRecordingState(true);

                } catch (error) {

                    console.error(
                        'Voice recording error:',
                        error
                    );

                    alert(
                        'Microphone access was denied or is unavailable.'
                    );

                    stopMicrophone();
                    stopTimer();
                    setRecordingState(false);

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Preview Playback
        |--------------------------------------------------------------------------
        */

        voicePlayButton.addEventListener(
            'click',
            function () {

                if (voiceAudio.paused) {

                    voiceAudio.play();

                    voicePlayButton.innerHTML =
                        '<i class="bi bi-pause-fill"></i>';

                } else {

                    voiceAudio.pause();

                    voicePlayButton.innerHTML =
                        '<i class="bi bi-play-fill"></i>';

                }

            }
        );

        voiceAudio.addEventListener(
            'ended',
            function () {

                voicePlayButton.innerHTML =
                    '<i class="bi bi-play-fill"></i>';

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Cancel Voice Note
        |--------------------------------------------------------------------------
        */

        voiceCancelButton.addEventListener(
            'click',
            function () {

                resetVoiceNote();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        function createMediaRecorder(stream) {

            const preferredTypes = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus'
            ];

            for (
                const mimeType
                of preferredTypes
            ) {
                if (
                    MediaRecorder
                        .isTypeSupported(
                            mimeType
                        )
                ) {
                    return new MediaRecorder(
                        stream,
                        {
                            mimeType
                        }
                    );
                }
            }

            return new MediaRecorder(
                stream
            );

        }


        function getAudioExtension(
            mimeType
        ) {

            if (
                mimeType.includes('ogg')
            ) {
                return 'ogg';
            }

            return 'webm';

        }


        function setRecordingState(
            recording
        ) {

            isRecording = recording;

            voiceButton.classList.toggle(
                'is-recording',
                recording
            );

            voiceButton.innerHTML =
                recording
                    ? '<i class="bi bi-stop-fill"></i>'
                    : '<i class="bi bi-mic"></i>';

            voiceButton.title =
                recording
                    ? 'Stop recording'
                    : 'Record voice note';

        }


        function startTimer() {

            stopTimer();

            voiceTimer.textContent =
                '00:00';

            timerInterval =
                window.setInterval(
                    function () {

                        voiceTimer.textContent =
                            formatDuration(
                                Date.now() -
                                recordingStartedAt
                            );

                    },
                    500
                );

        }


        function stopTimer() {

            if (timerInterval) {

                clearInterval(
                    timerInterval
                );

                timerInterval = null;

            }

        }


        function formatDuration(
            milliseconds
        ) {

            const totalSeconds =
                Math.max(
                    0,
                    Math.floor(
                        milliseconds / 1000
                    )
                );

            const minutes =
                Math.floor(
                    totalSeconds / 60
                );

            const seconds =
                totalSeconds % 60;

            return (
                String(minutes)
                    .padStart(2, '0') +
                ':' +
                String(seconds)
                    .padStart(2, '0')
            );

        }


        function stopMicrophone() {

            if (!mediaStream) {
                return;
            }

            mediaStream
                .getTracks()
                .forEach(
                    function (track) {
                        track.stop();
                    }
                );

            mediaStream = null;

        }


        function resetVoiceNote() {

    stopTimer();

    stopMicrophone();

    if (recordedAudioUrl) {

        URL.revokeObjectURL(
            recordedAudioUrl
        );

        recordedAudioUrl = null;

    }

    voiceAudio.pause();

    voiceAudio.removeAttribute(
        'src'
    );

    voiceAudio.load();

    attachmentInput.value = '';

    attachmentInput.dispatchEvent(
        new Event(
            'change',
            {
                bubbles: true
            }
        )
    );

    voiceRecorder.hidden = true;

    voicePreview.hidden = true;

    voiceTimer.textContent =
        '00:00';

    voiceDuration.textContent =
        '00:00';

    voicePlayButton.innerHTML =
        '<i class="bi bi-play-fill"></i>';

    audioChunks = [];

    mediaRecorder = null;

    setRecordingState(false);

}
       /*
        |--------------------------------------------------------------------------
        | Reset Preview After Successful Send
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'redalien:message-sent',
            function () {
                resetVoiceNote();
            }
        );

    }
    
    
);