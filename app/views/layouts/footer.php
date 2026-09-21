</div>
   
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= BASE_URL; ?>/assets/js/redalien.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/splash.js"></script>
<script>
    window.REDALIEN_BASE_URL =
        <?= json_encode(BASE_URL); ?>;

    window.REDALIEN_USER_ID =
        <?= json_encode((int) ($_SESSION['user_id'] ?? 0)); ?>;
</script>

<script src="<?= BASE_URL; ?>/assets/js/create-alien.js"></script>

<script src="<?= BASE_URL; ?>/assets/js/clipboard.js"></script>

<script src="<?= BASE_URL; ?>/assets/js/pods.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/utils.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/reactions.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/messages.js"></script>

<script src="<?= BASE_URL; ?>/assets/js/redalien/video-room.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/redalien/meeting.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/redalien/webrtc.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/redalien/screen-share.js"></script>


<script src="<?= BASE_URL; ?>/assets/js/voice-notes.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/attachments.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/clear-chat.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/composer-emoji.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/typing.js"></script>
<script
    src="<?= BASE_URL; ?>/assets/js/message-actions.js?v=<?= time(); ?>"
></script>
<script src="<?= BASE_URL; ?>/assets/js/presence.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/polling.js"></script>

<script src="<?= BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>





