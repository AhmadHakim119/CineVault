<?php
session_start();
include 'db.php';

$userId       = $_SESSION['user_id'] ?? null;
$contentId    = $_POST['content_id'] ?? null;
$contentType  = $_POST['content_type'] ?? null;
$comment      = trim($_POST['comment'] ?? '');

if (!$userId || !$contentId || empty($comment) || !in_array($contentType, ['movie', 'tv'])) {
    die("Invalid request.");
}

$query = "INSERT INTO comments (user_id, content_id, content_type, comment) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiss", $userId, $contentId, $contentType, $comment);
$stmt->execute();

$redirectPage = ($contentType === 'tv') ? "tv_details.php" : "movie.php";
header("Location: {$redirectPage}?id=$contentId");
exit;
?>
