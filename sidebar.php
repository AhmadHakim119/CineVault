<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$userId = $_SESSION['user_id'] ?? null;
$isAdmin = false;

if ($userId) {
    $userQuery = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $userQuery->bind_param("i", $userId);
    $userQuery->execute();
    $result = $userQuery->get_result();
    $user = $result->fetch_assoc();
    $isAdmin = isset($user['username']) && strtolower($user['username']) === 'dev';
}
?>

<div class="sidebar">
    <h3>Dashboard</h3>
    <a href="main.php"><i class="fas fa-home"></i> Return to Homepage</a>
    <a href="profile.php">Profile</a>
    <a href="ratings.php">Ratings</a>
    <a href="favorites.php">Favorites</a>
    <a href="settings.php">User Settings</a>
    <?php if ($isAdmin): ?>
        <a href="admin.php">Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
</div>
