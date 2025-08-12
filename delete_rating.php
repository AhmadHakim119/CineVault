<?php
session_start();
include 'db.php';

if (!isset($_POST['content_id'], $_POST['content_type'], $_SESSION['user_id'])) {
    die("Invalid request.");
}

$contentId = (int)$_POST['content_id'];
$contentType = $_POST['content_type'];
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM ratings WHERE user_id = ? AND content_id = ?");
$stmt->bind_param("ii", $userId, $contentId);
$stmt->execute();
$stmt->close();

if ($contentType === 'movie') {
    header("Location: movie.php?id=" . $contentId);
    exit;
} elseif ($contentType === 'tv') {
    header("Location: tv_details.php?id=" . $contentId);
    exit;
} else {
    die("Invalid content type.");
}

