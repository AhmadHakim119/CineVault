<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to rate.";
    exit;
}

$userId = $_SESSION['user_id'];
$contentId = $_POST['content_id'];
$contentType = $_POST['content_type']; 
$rating = $_POST['rating'];

if (!in_array($contentType, ['movie', 'tv']) || !is_numeric($rating) || $rating < 0 || $rating > 10) {
    echo "Invalid input.";
    exit;
}

$query = "INSERT INTO ratings (user_id, content_id, content_type, rating)
          VALUES (?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE rating = VALUES(rating), rated_at = CURRENT_TIMESTAMP";

$stmt = $conn->prepare($query);
$stmt->bind_param("iisd", $userId, $contentId, $contentType, $rating);

if ($stmt->execute()) {
    echo "Rating successfully submitted";
} else {
    echo "Error: " . $stmt->error;
}

?>
