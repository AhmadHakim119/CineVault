<?php
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $userId = $user['id'];

        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        $update->execute();

        echo "<h2>Email verified! You can now <a href='index.php'>log in</a>.</h2>";
    } else {
        echo "<h2>Invalid or expired token.</h2>";
    }
} else {
    echo "<h2>No token provided.</h2>";
}
?>
