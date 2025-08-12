<?php
session_start();
$apiKey = "8e20f5e5816216e33f65ccef48632afd";
include 'db.php';

if (!isset($_GET["id"])) {
    echo "Movie ID not provided.";
    exit;
}

$movieId = $_GET["id"];
$userId = $_SESSION['user_id'] ?? null; 

$creditsUrl = "https://api.themoviedb.org/3/movie/$movieId/credits?api_key=$apiKey";
$creditsJson = file_get_contents($creditsUrl);
$credits = json_decode($creditsJson, true);
$cast = array_slice($credits['cast'], 0, 10);
$query = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    $url = "https://api.themoviedb.org/3/movie/$movieId?api_key=$apiKey&language=en-US";
    $response = file_get_contents($url);
    $tmdbData = json_decode($response, true);

    if ($tmdbData && isset($tmdbData['id'])) {
        $insertQuery = "INSERT INTO movies (id, title, poster_path, release_date, overview, vote_average)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssss", 
            $tmdbData['id'], 
            $tmdbData['title'], 
            $tmdbData['poster_path'], 
            $tmdbData['release_date'], 
            $tmdbData['overview'], 
            $tmdbData['vote_average']
        );
        $stmt->execute();

        $movie = $tmdbData;
    } else {
        die("Movie not found.");
    }
} else {
    $url = "https://api.themoviedb.org/3/movie/$movieId?api_key=$apiKey&language=en-US";
    $response = file_get_contents($url);
    $tmdbData = json_decode($response, true);
}

$genres = array_map(fn($g) => $g['name'], $tmdbData['genres']);
$genreList = implode(', ', $genres);
$runtime = $tmdbData['runtime'] ?? 0;
$userRating = null;

if ($userId) {
  $contentType = 'movie';
  $ratingQuery = "SELECT rating FROM ratings WHERE user_id = ? AND content_id = ? AND content_type = ?";
  $stmt = $conn->prepare($ratingQuery);
  $stmt->bind_param("iis", $userId, $movieId, $contentType);
  $stmt->execute();
  $result = $stmt->get_result();
  $userRatingData = $result->fetch_assoc();

  if ($userRatingData) {
      $userRating = $userRatingData['rating'];
  } else {
      $userRating = null; 
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($movie['title']) ?> - Details</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cousine:ital,wght@0,400;0,700;1,400;1,700&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=play_arrow" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
      </head>
  <body class="text-light main-bg">
    <?php include 'navbar.php'; ?>

<div class="movie-hero position-relative">
  <div class="faded-background" 
       style="background-image: url('https://image.tmdb.org/t/p/original<?= $movie['poster_path'] ?>');">
  </div>

  <div class="container position-relative text-light py-5">
    <div class="row align-items-start">
      <div class="col-md-4">
        <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" 
             alt="<?= htmlspecialchars($movie['title']) ?>" 
             class="img-fluid rounded shadow movie-poster">
      </div>
          <div class="col-md-8 movie-details">
        <h1 class="header"><?= htmlspecialchars($movie['title']) ?></h1>
        <p class="movie-details"><strong>⭐ Rating:</strong> <?= number_format($movie['vote_average'], 1) ?>/10</p>
        <p class="movie-details">
  <?php if ($userRating !== null): ?>
  <div class="d-flex align-items-center mt-2">
  <strong class="me-2">🎯 Your Rating:</strong>
    <span id="yourRatingNumber"><?= number_format($userRating, 1) ?></span>
    <span id="yourRatingSuffix">/10</span>
    <button id="editRatingButton" class="btn btn-warning btn-sm ms-3">Edit Rating</button>
    <form method="POST" action="delete_rating.php" class="ms-2 mb-0">
      <input type="hidden" name="content_id" value="<?= $movieId ?>">
      <input type="hidden" name="content_type" value="movie">
      <button type="submit" class="btn btn-danger btn-sm">Delete Rating</button>
    </form>
  </div>
<?php else: ?>
    <span id="yourRatingNumber">Not Rated Yet</span>
    <span id="yourRatingSuffix"></span>
    <button id="editRatingButton" class="btn btn-primary btn-sm ms-2">Rate</button>
  <?php endif; ?>
</p>

        <form id="ratingForm" method="POST" action="rate.php" style="display: none; align-items: center;">
            <input type="hidden" name="content_id" value="<?= $movieId ?>">
            <input type="hidden" name="content_type" value="movie">
            <input type="range" id="ratingSlider" name="rating" min="0" max="10" step="0.5" value="<?= $userRating ?? 5 ?>" 
            oninput="this.nextElementSibling.value = this.value" class="form-range me-2" <?= $userRating ? 'disabled' : '' ?>>
            <output id="sliderValue"><?= $userRating?? 5 ?></output>/10
            <button type="submit" id="rateButton" class="btn btn-primary btn-sm ms-2" <?= $userRating ? 'style="display:none;"' : '' ?>>Rate</button>
        </form>
      <div id="ratingMessage" class="alert alert-success" style="display: none; position: absolute; top: 20px; left: 50%; transform: translateX(-50%);">
        Rating successfully submitted!
      </div>
        <p class="movie-details"><strong>Genres:</strong> <?= $genreList ?></p>
        <p class="movie-details"><strong>⏱ Runtime:</strong> <?= floor($runtime / 60) . 'h ' . ($runtime % 60) . 'm' ?></p>
        <p class="movie-details"><strong>🗓 Release Date:</strong> <?= $movie['release_date'] ?></p>
        <p class="movie-details"><strong>Overview</strong><br><br> <?= $movie['overview'] ?></p>
        <?php
            $isFavorited = false;
            if (isset($_SESSION['user_id'])) {
                $checkFav = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_id = ? AND content_type = ?");
                $checkFav->bind_param("iis", $_SESSION['user_id'], $movieId, $contentType);
                $checkFav->execute();
                $checkFav->store_result();
                $isFavorited = $checkFav->num_rows > 0;
                $checkFav->close();
            }
          ?>

        <?php if (isset($_SESSION['user_id'])): ?>
          <button id="favorite-btn"
                class="btn <?= $isFavorited ? 'btn-secondary' : 'btn-success' ?>"
                data-content-id="<?= $movieId ?>"
                data-content-type="<?= $contentType ?>"
                <?= $isFavorited ? 'disabled' : '' ?>>
          <?= $isFavorited ? 'Added to Favorites' : 'Add to Favorites' ?>
        </button>

        <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
    <div class="container my-5">
  <h3 class="text-light mb-4">Top Cast</h3>
  <div class="d-flex flex-row overflow-auto gap-3 actor-cards">

    <?php foreach ($cast as $actor): ?>
      <div class="card bg-dark text-white actor-cards" style="min-width: 150px; border-radius: 12px;">
        <img src="https://image.tmdb.org/t/p/w185<?= $actor['profile_path'] ?>" 
             alt="<?= htmlspecialchars($actor['name']) ?>" 
             class="card-img-top rounded-top">
        <div class="card-body p-2">
          <h6 class="card-title mb-0"><?= htmlspecialchars($actor['name']) ?></h6>
          <small class="text-secondary"><?= htmlspecialchars($actor['character']) ?></small>
        </div>
      </div>
    <?php endforeach; ?>

      </div>
    </div>
    <div class="container mt-5">
  <h3 class="text-light com">Comments & Reviews</h3>

  <?php if ($userId): ?>
    <form method="POST" action="submit_comment.php" class="mb-4">
    <input type="hidden" name="content_id" value="<?= $movieId ?>">
    <input type="hidden" name="content_type" value="movie">
      <div class="mb-3">
        <textarea name="comment" class="form-control" rows="3" placeholder="Write your comment here..." required></textarea>
      </div>
      <button type="submit" class="btn btn-outline-primary">Post Comment</button>
    </form>
  <?php else: ?>
    <p><a href="index.php">Login</a> to write a comment.</p>
  <?php endif; ?>

  <?php
$commentQuery = "SELECT c.id, c.comment, c.created_at, c.user_id, u.username 
FROM comments c 
JOIN users u ON c.user_id = u.id 
WHERE c.content_id = ? AND c.content_type = 'movie' AND c.hidden = 0
ORDER BY c.created_at DESC";
  $stmt = $conn->prepare($commentQuery);
  $stmt->bind_param("i", $movieId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()):
    $commentId = $row['id'];
  
    $reactionStmt = $conn->prepare("SELECT 
        SUM(reaction = 'like') AS likes, 
        SUM(reaction = 'dislike') AS dislikes 
      FROM comment_reactions 
      WHERE comment_id = ?");
    $reactionStmt->bind_param("i", $commentId);
    $reactionStmt->execute();
    $reactionData = $reactionStmt->get_result()->fetch_assoc();
    $likes = $reactionData['likes'] ?? 0;
    $dislikes = $reactionData['dislikes'] ?? 0;
    $reactionStmt->close();
  
    $commentUserId = $row['user_id'];
    $hasReported = false;
  
    if ($userId && $userId != $commentUserId) {
      $reportCheckStmt = $conn->prepare("SELECT id FROM comment_reports WHERE comment_id = ? AND reported_by = ?");
      $reportCheckStmt->bind_param("ii", $commentId, $userId);
      $reportCheckStmt->execute();
      $reportCheckStmt->store_result();
      $hasReported = $reportCheckStmt->num_rows > 0;
      $reportCheckStmt->close();
    }
  ?>
    <div class="card bg-dark text-light mb-3">
      <div class="card-body position-relative">
      <h6 class="card-title mb-1">
        <a href="profile.php?user_id=<?= $row['user_id'] ?>" class="text-decoration-none text-light pfp">
          @<?= htmlspecialchars($row['username']) ?>
        </a>
          <small class="text-secondary float-end"><?= date("M d, Y H:i", strtotime($row['created_at'])) ?></small>
        </h6>
        <p class="card-text"><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
  
        <?php if ($userId): ?>
          <div class="mt-2">
            <form action="react_comment.php" method="POST" class="d-inline">
              <input type="hidden" name="comment_id" value="<?= $commentId ?>">
              <input type="hidden" name="reaction" value="like">
              <button type="submit" class="btn btn-sm btn-outline-success">
                👍 <?= $likes ?>
              </button>
            </form>
  
            <form action="react_comment.php" method="POST" class="d-inline ms-2">
              <input type="hidden" name="comment_id" value="<?= $commentId ?>">
              <input type="hidden" name="reaction" value="dislike">
              <button type="submit" class="btn btn-sm btn-outline-danger">
                👎 <?= $dislikes ?>
              </button>
            </form>
          </div>
  
          <?php if ($userId != $commentUserId && !$hasReported): ?>
            <button type="button" class="btn btn-sm btn-outline-warning position-absolute bottom-0 end-0 m-2" data-bs-toggle="modal" data-bs-target="#reportModal<?= $commentId ?>">
              🚩 Report
            </button>
              <div class="modal fade" id="reportModal<?= $commentId ?>" tabindex="-1" aria-labelledby="reportModalLabel<?= $commentId ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                  <form method="POST" action="report_comment.php">
                    <div class="modal-header">
                      <h5 class="modal-title" id="reportModalLabel<?= $commentId ?>">Report Comment</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                      <textarea name="reason" class="form-control" rows="3" placeholder="Why are you reporting this comment?" required></textarea>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-danger">Submit Report</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php elseif ($hasReported): ?>
            <span class="text-warning position-absolute bottom-0 end-0 m-2 small">🚩 Reported</span>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
    <script src="moviebuttons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>