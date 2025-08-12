<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$commentId = $_POST['comment_id'];
$reaction = $_POST['reaction'];

if (!in_array($reaction, ['like', 'dislike'])) {
    die("Invalid reaction");
}

$checkStmt = $conn->prepare("SELECT id FROM comment_reactions WHERE user_id = ? AND comment_id = ?");
$checkStmt->bind_param("ii", $userId, $commentId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE comment_reactions SET reaction = ?, created_at = NOW() WHERE user_id = ? AND comment_id = ?");
    $updateStmt->bind_param("sii", $reaction, $userId, $commentId);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    $insertStmt = $conn->prepare("INSERT INTO comment_reactions (user_id, comment_id, reaction) VALUES (?, ?, ?)");
    $insertStmt->bind_param("iis", $userId, $commentId, $reaction);
    $insertStmt->execute();
    $insertStmt->close();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
