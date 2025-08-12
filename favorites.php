<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

$query = "SELECT f.content_id, f.content_type, 
                 m.title, m.poster_path AS movie_poster, t.name, t.poster_path AS tv_poster
          FROM favorites f
          LEFT JOIN movies m ON f.content_id = m.id AND f.content_type = 'movie'
          LEFT JOIN tv_shows t ON f.content_id = t.id AND f.content_type = 'tv'
          WHERE f.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all favorites
$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

// Handle removing favorite
if (isset($_GET['remove'])) {
    $content_id = $_GET['remove'];
    $content_type = $_GET['type'];

    $removeQuery = "DELETE FROM favorites WHERE user_id = ? AND content_id = ? AND content_type = ?";
    $removeStmt = $conn->prepare($removeQuery);
    $removeStmt->bind_param('iis', $user_id, $content_id, $content_type);
    $removeStmt->execute();

    // Redirect to refresh the page after removal
    header('Location: favorites.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="text-light main-bg-settings m-0 p-0">
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4 content">
        <h2>My Favorites</h2>

        <?php if (empty($favorites)): ?>
            <p>You have no favorites yet.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="col-md-3 mb-4">
                        <div class="movie-card h-100 text-light shadow">
                            <?php 
                                if ($favorite['content_type'] === 'movie') {
                                    $title = $favorite['title'];
                                    $poster_path = "https://image.tmdb.org/t/p/w500" . $favorite['movie_poster'];
                                    $link = "movie.php?id=" . $favorite['content_id'];
                                } else {
                                    $title = $favorite['name'];
                                    $poster_path = "https://image.tmdb.org/t/p/w500" . $favorite['tv_poster'];
                                    $link = "tv_details.php?id=" . $favorite['content_id'];
                                }
                            ?>
                            <img src="<?= $poster_path ?>" class="card-img-top" alt="<?= htmlspecialchars($title) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($title) ?></h5>
                                <a href="<?= $link ?>" class="btn btn-primary">View</a>
                                <a href="?remove=<?= $favorite['content_id'] ?>&type=<?= $favorite['content_type'] ?>" class="btn btn-danger">Remove</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <footer class="mt-5 py-4 bg-dark text-center text-light">
    <div class="container">
        <p class="mb-1">&copy; 2025 <strong>CineVault</strong>. All rights reserved.</p>
        <p class="mb-1">
            <a href="terms.html" class="text-light text-decoration-underline">Terms of Service</a>
        </p>
        <p class="mb-0">
            Contact us at 
            <a href="mailto:support@cinevault.com" class="text-light text-decoration-underline">
                doorwindowall@gmail.com
            </a>
        </p>
    </div>
</footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
