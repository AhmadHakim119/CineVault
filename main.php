<?php
session_start();

$apiKey = '8e20f5e5816216e33f65ccef48632afd';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$searchQuery = $_GET['query'] ?? '';
$type = $_GET['type'] ?? 'popular';
$genreId = $_GET['genre'] ?? '';

include 'db.php';
if (!empty($searchQuery)) {
    $encodedQuery = urlencode($searchQuery);
    $url = "https://api.themoviedb.org/3/search/multi?api_key=$apiKey&language=en-US&query=$encodedQuery&page=$page";
    $heading = "🔍 Search Results for \"$searchQuery\"";
} else {
    if (!empty($genreId)) {
        $endpoint = 'discover/movie';
        $url = "https://api.themoviedb.org/3/$endpoint?api_key=$apiKey&language=en-US&page=$page&with_genres=$genreId&include_adult=false";
        $heading = "🎯 Filtered by Genre";
    } else {
        $endpoints = [
            'popular' => 'movie/popular',
            'top_rated' => 'movie/top_rated',
            'upcoming' => 'movie/upcoming'
        ];
        $endpoint = $endpoints[$type] ?? 'movie/popular';
        $url = "https://api.themoviedb.org/3/$endpoint?api_key=$apiKey&language=en-US&page=$page&include_adult=false";

        $heading = [
            'popular' => '🎬 Most Popular Movies',
            'top_rated' => '🌟 Top Rated Movies',
            'upcoming' => '📅 Upcoming Movies'
        ][$type] ?? '🎬 Movies';
    }
}


$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['results' => []];



foreach ($data['results'] as $movie) {
    $movieId = $movie['id'];

    $checkQuery = "SELECT id FROM movies WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insertQuery = "INSERT INTO movies (id, title, poster_path, release_date, overview, vote_average)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssss",
            $movie['id'],
            $movie['title'],
            $movie['poster_path'],
            $movie['release_date'],
            $movie['overview'],
            $movie['vote_average']
        );
        $stmt->execute();
    }
}

$username = $_SESSION['username'] ?? 'Guest';
$isAdmin = $_SESSION['is_admin'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CineVault - Movies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="text-light main-bg">
<?php include 'navbar.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center">
  </div>
  
 
<form method="GET" id="genreForm" class="mb-4">
  <div class="row">
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text bg-dark text-light border-secondary">
          <i class="fas fa-filter"></i>
        </span>
        <select name="genre" class="form-select bg-dark text-light border-secondary" onchange="document.getElementById('genreForm').submit()">
          <option value="">Filter by Genre</option>
          <?php
          $genreUrl = "https://api.themoviedb.org/3/genre/movie/list?api_key=$apiKey&language=en-US";
          $genreResponse = @file_get_contents($genreUrl);
          $genreData = json_decode($genreResponse, true);

          foreach ($genreData['genres'] as $genre) {
              $selected = $genreId == $genre['id'] ? 'selected' : '';
              echo "<option value=\"{$genre['id']}\" $selected>{$genre['name']}</option>";
          }
          ?>
        </select>
      </div>
    </div>
  </div>
</form>
  <h2 class="mt-5 mb-4"><?= $heading ?></h2>

  <div class="row" id="movie-list">
    <?php if (!empty($data['results'])): ?>
      <?php foreach ($data['results'] as $movie): ?>
  <?php
    $title = $movie['title'] ?? $movie['name'] ?? 'No title';
    $poster = $movie['poster_path'] ?? 'default_poster.png';
    $vote = isset($movie['vote_average']) && is_numeric($movie['vote_average']) ? number_format($movie['vote_average'], 1) . '/10' : 'No rating yet';
    if (!empty($searchQuery)) {
        $mediaType = $movie['media_type'] ?? 'movie';
        $link = ($mediaType === 'tv') ? "tv_details.php?id={$movie['id']}" : "movie.php?id={$movie['id']}";
    } else {
        $link = "movie.php?id={$movie['id']}";
    }
  ?>
  <div class="col-md-3 mb-4">
    <a href="<?= $link ?>" class="text-decoration-none text-light">
      <div class="card movie-card h-100 bg-dark text-light shadow">
        <img src="https://image.tmdb.org/t/p/w500<?= $poster ?>" class="card-img-top" alt="<?= htmlspecialchars($title) ?>">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($title) ?></h5>
          <p class="card-text">⭐ <?= $vote ?></p>
        </div>
      </div>
    </a>
  </div>
<?php endforeach; ?>

    <?php else: ?>
      <p>Unable to fetch movies. Please try again later.</p>
    <?php endif; ?>
  </div>

  <div id="loading" class="text-center my-4" style="display: none;">
    <img src="loading-spinner.gif" alt="Loading...">
  </div>
</div>

<script>
  let currentPage = 1;
  const urlParams = new URLSearchParams(window.location.search);
  const currentType = urlParams.get('type') || 'popular';
  const currentQuery = urlParams.get('query') || '';
  const currentGenre = urlParams.get('genre') || '';
  let hasShownError = false;

function loadMoreMovies() {
  $('#loading').show();
  currentPage++;

  $.ajax({
    url: 'movie_page.php',
    type: 'GET',
    data: { 
      page: currentPage, 
      type: currentType,
      query: currentQuery,
      genre: currentGenre
    },
    success: function(response) {
      $('#movie-list').append(response);
      $('#loading').hide();

      if (currentPage >= 5) {
        $(window).off('scroll');
      }
    },
    error: function(xhr, status, error) {
      $('#loading').hide();
      if (!hasShownError) {
        console.error("Error loading more movies:", error);
        alert('No more movies to load.');
        hasShownError = true;
      }
    }
  });
}

  $(window).scroll(function() {
  const isSearching = '<?= !empty($searchQuery) ? "true" : "false" ?>' === 'true';

  if (!isSearching && $(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
    loadMoreMovies();
  }
});
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
