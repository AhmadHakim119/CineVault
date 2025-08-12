<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'db.php';
$userId = $_SESSION['user_id'];

$type = $_GET['type'] ?? 'movie'; 
$sort = $_GET['sort'] ?? 'desc'; 

$titleColumn = $type === 'tv' ? 'name' : 'title';
$sortColumn = $sort === 'asc' ? 'r.rating ASC' : 'r.rating DESC';
$table = $type === 'tv' ? 'tv_shows' : 'movies';

$query = "
    SELECT r.rating, r.content_type, r.content_id, c.$titleColumn AS title, c.poster_path 
    FROM ratings r
    JOIN $table c ON r.content_id = c.id
    WHERE r.user_id = ? AND r.content_type = ?
    ORDER BY $sortColumn
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $userId, $type);
$stmt->execute();
$result = $stmt->get_result();

// Debug info (you can remove later)
echo "<pre>Type: $type\nSort: $sort\nRows: " . $result->num_rows . "</pre>";

$listItems = '';
while ($row = $result->fetch_assoc()) {
    $poster = $row['poster_path']
        ? "https://image.tmdb.org/t/p/w92" . $row['poster_path']
        : "default_poster.png";

    $listItems .= '
        <a href="' . $type . '.php?id=' . $row['content_id'] . '" 
           class="list-group-item list-group-item-action d-flex align-items-top bg-dark text-light border-secondary mb-2 rating-card">
            <img src="' . $poster . '" alt="' . htmlspecialchars($row['title']) . '" 
                 class="me-3 rounded" style="width: 60px; height: auto;">
            <div>
                <h5 class="mb-1">' . htmlspecialchars($row['title']) . '</h5>
                <p class="mb-0 rating">Rating: ' . $row['rating'] . '/10</p>
            </div>
        </a>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="text-light main-bg-settings m-0 p-0">

<div class="d-flex w-100">
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="container">
            <h2 class="mb-4">Your Ratings</h2>

            <form method="get" class="d-flex flex-wrap gap-2 mb-4">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                
                <div class="btn-group" role="group">
                    <a href="?type=movie&sort=<?= $sort ?>" class="btn btn-outline-light <?= $type === 'movie' ? 'active' : '' ?>">Only Movies</a>
                    <a href="?type=tv&sort=<?= $sort ?>" class="btn btn-outline-light <?= $type === 'tv' ? 'active' : '' ?>">Only TV Shows</a>
                </div>

                <div class="btn-group ms-2" role="group">
                    <a href="?type=<?= $type ?>&sort=desc" class="btn btn-outline-light <?= $sort === 'desc' ? 'active' : '' ?>">Highest to Lowest</a>
                    <a href="?type=<?= $type ?>&sort=asc" class="btn btn-outline-light <?= $sort === 'asc' ? 'active' : '' ?>">Lowest to Highest</a>
                </div>
            </form>

            <?php if ($listItems): ?>
                <div class="list-group"><?= $listItems ?></div>
            <?php else: ?>
                <div class="alert alert-warning">You haven't rated any <?= $type === 'movie' ? 'movies' : 'TV shows' ?> yet.</div>
            <?php endif; ?>
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
