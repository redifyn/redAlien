<?php
$pageTitle = $title ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle); ?> | redAlien</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL; ?>/assets/css/redalien.css" rel="stylesheet">
    <link href="<?= BASE_URL; ?>/assets/css/video-room.css" rel="stylesheet">
    <link href="<?= BASE_URL; ?>/assets/css/splash.css" rel="stylesheet">
  
</head>
<body>
<div class="ra-app-shell">
