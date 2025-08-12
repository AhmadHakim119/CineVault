<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'db.php';
$viewingOwnProfile = true;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = (int) $_GET['user_id'];
    $viewingOwnProfile = ($userId === $_SESSION['user_id']);
} else {
    $userId = $_SESSION['user_id'];
}
$userQuery = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result()->fetch_assoc();

$countQuery = $conn->prepare("SELECT content_type, COUNT(*) as total FROM ratings WHERE user_id = ? GROUP BY content_type");
$countQuery->bind_param("i", $userId);
$countQuery->execute();
$countResult = $countQuery->get_result();

$movieCount = 0;
$tvCount = 0;
while ($row = $countResult->fetch_assoc()) {
    if ($row['content_type'] === 'movie') $movieCount = $row['total'];
    if ($row['content_type'] === 'tv') $tvCount = $row['total'];
}

$recentQuery = $conn->prepare("
    SELECT r.rating, r.content_type, r.content_id, 
           COALESCE(m.title, t.name) AS title, 
           COALESCE(m.poster_path, t.poster_path) AS poster_path 
    FROM ratings r
    LEFT JOIN movies m ON r.content_type = 'movie' AND r.content_id = m.id
    LEFT JOIN tv_shows t ON r.content_type = 'tv' AND r.content_id = t.id
    WHERE r.user_id = ?
    ORDER BY r.rated_at DESC
    LIMIT 5
");
$recentQuery->bind_param("i", $userId);
$recentQuery->execute();
$recentRatings = $recentQuery->get_result();
$favoritesQuery = $conn->prepare("
    SELECT f.content_type, f.content_id, 
           COALESCE(m.title, t.name) AS title, 
           COALESCE(m.poster_path, t.poster_path) AS poster_path
    FROM favorites f
    LEFT JOIN movies m ON f.content_type = 'movie' AND f.content_id = m.id
    LEFT JOIN tv_shows t ON f.content_type = 'tv' AND f.content_id = t.id
    WHERE f.user_id = ?
");
$favoritesQuery->bind_param("i", $userId);
$favoritesQuery->execute();
$favoritesResult = $favoritesQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
        }
    </style>
</head>
<body class="text-light main-bg-settings">
<div class="container-fluid content">
    <?php include 'sidebar.php'; ?>
    <div class="col p-4">
    <h2 class="mb-4 text-center">
  <span class="text-primary"><?= htmlspecialchars($userResult['username']) ?></span>'s Profile
  <small class="d-block text-light mt-2">Joined: <?= date("F Y", strtotime($userResult['created_at'])) ?></small>
</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-dark text-light h-100">
            <div class="card-body">
                <h5 class="card-title">📊 Rating Stats</h5>
                <p class="card-text">🎬 Movies Rated: <?= $movieCount ?></p>
                <p class="card-text">📺 TV Shows Rated: <?= $tvCount ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-dark text-light h-100">
            <div class="card-body">
                <h5 class="card-title">🕒 Recent Ratings</h5>
                <?php if ($recentRatings->num_rows > 0): ?>
                    <ul class="list-unstyled">
                        <?php while ($rating = $recentRatings->fetch_assoc()): 
                            $poster = $rating['poster_path'] 
                                ? "https://image.tmdb.org/t/p/w92" . $rating['poster_path'] 
                                : "default_poster.png";
                        ?>
                            <li class="d-flex align-items-center mb-2">
                                <img src="<?= $poster ?>" alt="<?= htmlspecialchars($rating['title']) ?>" style="width: 40px;" class="me-2 rounded">
                                <div>
                                    <strong><?= htmlspecialchars($rating['title']) ?></strong><br>
                                    <small>Rated: <?= $rating['rating'] ?>/10</small>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No recent ratings yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<div class="col-md-4">
    <div class="card bg-dark text-light h-100">
        <div class="card-body">
            <h5 class="card-title">❤️ Your Favorites</h5>
                <?php if ($favoritesResult->num_rows > 0): ?>
                    <ul class="list-unstyled">
                    <?php while ($favorite = $favoritesResult->fetch_assoc()): 
                        $poster = $favorite['poster_path'] 
                            ? "https://image.tmdb.org/t/p/w92" . $favorite['poster_path'] 
                            : "default_poster.png";
                    ?>
                            <li class="d-flex align-items-center mb-2">
                            <img src="<?= $poster ?>" alt="<?= htmlspecialchars($rating['title']) ?>" style="width: 40px;" class="me-2 rounded">
                            <div>
                                    <strong><?= htmlspecialchars($favorite['title']) ?></strong><br>
                                </div>
                            </li>
                    <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No favorites yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
