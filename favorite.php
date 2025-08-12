<?php
session_start();
include 'db.php';

header('Content-Type: application/json'); 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to favorite content.']);
    exit;
}

$userId = $_SESSION['user_id'];
$contentId = $_POST['content_id'];
$contentType = $_POST['content_type'];
$query = "SELECT id FROM favorites WHERE user_id = ? AND content_id = ? AND content_type = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $userId, $contentId, $contentType);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Already in favorites']);
    exit;
}

// Insert the favorite
$query = "INSERT INTO favorites (user_id, content_id, content_type) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $userId, $contentId, $contentType);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
