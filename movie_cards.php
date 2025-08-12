<?php
$apiKey = '8e20f5e5816216e33f65ccef48632afd';
$page = $_GET['page'] ?? 1;
$searchQuery = $_GET['query'] ?? '';
$type = $_GET['type'] ?? 'popular';
$genreId = $_GET['genre'] ?? '';

if (!empty($searchQuery)) {
    $encodedQuery = urlencode($searchQuery);
    $url = "https://api.themoviedb.org/3/search/multi?api_key=$apiKey&language=en-US&query=$encodedQuery&page=$page";
} else {
    $endpoints = [
        'popular' => 'movie/popular',
        'top_rated' => 'movie/top_rated',
        'upcoming' => 'movie/upcoming'
    ];
    $endpoint = $endpoints[$type] ?? 'movie/popular';
    $url = "https://api.themoviedb.org/3/$endpoint?api_key=$apiKey&language=en-US&page=$page";

    if (!empty($genreId)) {
        $url .= "&with_genres=$genreId";
    }
    $url .= "&include_adult=false";
}

$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['results' => []];

foreach ($data['results'] as $movie):
    $title = $movie['title'] ?? $movie['name'] ?? 'No title';
    $poster = $movie['poster_path'] ?? 'default_poster.png';
    $vote = isset($movie['vote_average']) && is_numeric($movie['vote_average']) ? number_format($movie['vote_average'], 1) . '/10' : 'No rating yet';
    $mediaType = $movie['media_type'] ?? 'movie';
    $link = ($mediaType === 'tv') ? "tv_details.php?id={$movie['id']}" : "movie.php?id={$movie['id']}";
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
<?php if (empty($data['results'])): ?>
  <p>Unable to fetch movies. Please try again later.</p>
<?php endif; ?>             
    <div id="loading" class="text-center my-4" style="display: none;">
        <img src="loading-spinner.gif" alt="Loading...">
    </div>