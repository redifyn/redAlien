<?php
$currentPodId = (int) $pod['id'];
$currentPodName = $pod['name'];
$currentPodDescription = $pod['description'] ?? '';
$currentPodType = $pod['type'] ?? 'public';

require APPROOT . '/views/teams/show.php';