<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$commentId = $_POST['comment_id'] ?? null;
$reason = trim($_POST['reason'] ?? '');

if (!$commentId || empty($reason)) {
    die("Invalid input.");
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO comment_reports (comment_id, reported_by, reason) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $commentId, $userId, $reason);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
