<?php
session_start();
include 'db.php';

$apiKey = '8e20f5e5816216e33f65ccef48632afd';
$page = $_GET['page'] ?? 1;
$genreId = $_GET['genre'] ?? '';
$searchQuery = $_GET['query'] ?? '';
$type = $_GET['type'] ?? 'popular';

// Genre or Search
if (!empty($searchQuery)) {
    $encodedQuery = urlencode($searchQuery);
    $url = "https://api.themoviedb.org/3/search/tv?api_key=$apiKey&language=en-US&query=$encodedQuery&page=$page";
    $heading = "🔍 TV Results for \"$searchQuery\"";
} elseif (!empty($genreId)) {
    $url = "https://api.themoviedb.org/3/discover/tv?api_key=$apiKey&with_genres=$genreId&language=en-US&page=$page";
    $heading = "📂 TV Shows by Genre";
} else {
    $endpoints = [
        'popular' => 'tv/popular',
        'top_rated' => 'tv/top_rated',
        'upcoming' => 'tv/on_the_air'
    ];
    $endpoint = $endpoints[$type] ?? 'tv/popular';
    $url = "https://api.themoviedb.org/3/$endpoint?api_key=$apiKey&language=en-US&page=$page";
    $heading = [
        'popular' => '📺 Popular TV Shows',
        'top_rated' => '🌟 Top Rated TV Shows',
        'upcoming' => '📅 Currently Airing'
    ][$type] ?? '📺 TV Shows';
}

$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['results' => []];

// Save to DB
foreach ($data['results'] as $tv) {
    $tvId = $tv['id'];

    $checkQuery = "SELECT id FROM tv_shows WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $tvId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insertQuery = "INSERT INTO tv_shows (id, name, poster_path, first_air_date, overview, vote_average)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssss",
            $tv['id'],
            $tv['name'],
            $tv['poster_path'],
            $tv['first_air_date'],
            $tv['overview'],
            $tv['vote_average']
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
  <title>CineVault - TV Shows</title>
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
          $genreUrl = "https://api.themoviedb.org/3/genre/tv/list?api_key=$apiKey&language=en-US";
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

  <h2 class="mt-3 mb-4"><?= $heading ?></h2>

  <div class="row" id="tv-list">
    <?php foreach ($data['results'] as $tv): ?>
      <div class="col-md-3 mb-4">
        <a href="tv_details.php?id=<?= $tv['id'] ?>" class="text-decoration-none text-light">
          <div class="card movie-card h-100 bg-dark text-light shadow">
            <?php if (!empty($tv['poster_path'])): ?>
              <img src="https://image.tmdb.org/t/p/w500<?= $tv['poster_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($tv['name']) ?>">
            <?php else: ?>
              <img src="default_poster.png" class="card-img-top" alt="No poster available">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($tv['name']) ?></h5>
              <p class="card-text">⭐ <?= number_format($tv['vote_average'], 1) ?>/10</p>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="loading" class="text-center my-4" style="display: none;">
    <img src="loading-spinner.gif" alt="Loading...">
  </div>
</div>

<script>
  let currentPage = <?= $page ?>;
  let loading = false;

  function loadMoreTV() {
    if (loading) return;
    loading = true;
    $('#loading').show();
    currentPage++;

    $.ajax({
      url: 'tv.php',
      type: 'GET',
      data: {
        page: currentPage,
        query: '<?= $searchQuery ?>',
        type: '<?= $type ?>',
        genre: '<?= $genreId ?>'
      },
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(response) {
        const newCards = $(response).find('#tv-list').html();
        $('#tv-list').append(newCards);
        $('#loading').hide();
        loading = false;
      },
      error: function() {
        $('#loading').hide();
        loading = false;
        alert('Error loading more TV shows.');
      }
    });
  }

  $(window).scroll(function() {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
      loadMoreTV();
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
