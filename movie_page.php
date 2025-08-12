<?php
$apiKey = '8e20f5e5816216e33f65ccef48632afd';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$type = $_GET['type'] ?? 'popular';
$query = $_GET['query'] ?? '';
$genreFilter = $_GET['genre'] ?? '';
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
}

if (!empty($genreFilter)) {
  $url .= "&with_genres=$genreFilter";
}
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : [];

if (empty($data['results'])) {
    http_response_code(204); 
    exit;
}

foreach ($data['results'] as $item) {
  $title = $item['title'] ?? $item['name'] ?? 'No title';
  $poster = $item['poster_path'] ?? 'default_poster.png';
  $vote = $item['vote_average'] ?? 0;
  $id = $item['id'];
  if (!empty($query)) {
    $mediaType = $item['media_type'] ?? 'movie';
} else {
    $mediaType = 'movie'; 
}
  $link = $mediaType === 'tv' ? "tv_details.php?id=$id" : "movie.php?id=$id";

  echo '<div class="col-md-3 mb-4">
          <a href="' . $link . '" class="text-decoration-none text-light">
            <div class="card movie-card h-100 bg-dark text-light shadow">
              <img src="https://image.tmdb.org/t/p/w500' . $poster . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">
              <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($title) . '</h5>
                <p class="card-text">⭐ ' . (is_numeric($vote) ? number_format($vote, 1) . '/10' : 'No rating yet') . '</p>
              </div>
            </div>
          </a>
        </div>';
}
?>
