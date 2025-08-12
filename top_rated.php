<?php
session_start();

$apiKey = '8e20f5e5816216e33f65ccef48632afd';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$url = "https://api.themoviedb.org/3/movie/popular?api_key=$apiKey&language=en-US&page=1";

$response = file_get_contents($url);
$data = json_decode($response, true);

$username = $_SESSION['username'] ?? 'Guest';
$isAdmin = $_SESSION['is_admin'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CineVault - Top Rated Movies</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
</head>
<body class="text-light main-bg">
<nav class="navbar navbar-expand-lg nav1">
    <div class="container-fluid">
    <a class="navbar-brand" href="main.php">CineVault</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle nav-text" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Movies
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Top Rated</a></li>
            <li><a class="dropdown-item" href="main.php">Most Popular</a></li>
            <li><a class="dropdown-item" href="#">Upcoming</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <?php if ($isAdmin): ?>
            <a class="nav-link" href="admin.php">Admin Controls</a>
          <?php endif; ?>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center">
  </div>

  <?php if ($isAdmin): ?>
    <div class="admin-panel mt-3 p-3 bg-secondary rounded">
      <h2>🛠 Admin Panel</h2>
      <a href="add_movie.php" class="btn btn-warning">Add New Movie</a>
    </div>
  <?php endif; ?>

  <h2 class="mt-5 mb-4">🎬 Most Popular Movies</h2>
  <div class="row" id="movie-list">
    <?php if (!empty($data['results'])): ?>
      <?php foreach ($data['results'] as $movie): ?>
        <div class="col-md-3 mb-4">
          <a href="movie.php?id=<?= $movie['id'] ?>" class="text-decoration-none text-light">
            <div class="card movie-card h-100 bg-dark text-light shadow">
              <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                <p class="card-text">⭐ <?= number_format($movie['vote_average'], 1) ?>/10</p>
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

  function loadMoreMovies() {
    $('#loading').show();

    currentPage++;

    $.ajax({
      url: 'movie_page.php', 
      type: 'GET',
      data: { page: currentPage },
      success: function(response) {
        console.log("Data received:", response); 
       
        $('#movie-list').append(response);

        $('#loading').hide();
      },
      error: function(xhr, status, error) {
        $('#loading').hide();
        console.error("Error loading more movies:", error); 
        alert('Failed to load more movies.');
      }
    });
  }

  $(window).scroll(function() {
    if ($(window).scrollTop() + $(window).height() >= $(document).height()) {
      console.log("Bottom reached, loading more movies...");
      loadMoreMovies();
    }
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
