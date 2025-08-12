<?php

$isAdmin = $_SESSION['is_admin'] ?? 0; 
?>

<nav class="navbar navbar-expand-lg nav1">
  <div class="container-fluid">
    <a class="navbar-brand" href="main.php">CineVault</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle nav-text" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Movies
          </a>
          <ul class="dropdown-menu bg-dark" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="main.php">Most Popular</a></li>
            <li><a class="dropdown-item" href="main.php?type=top_rated">Top Rated</a></li>
            <li><a class="dropdown-item" href="main.php?type=upcoming">Upcoming</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle nav-text" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            TV Shows
          </a>
          <ul class="dropdown-menu bg-dark">
            <li><a class="dropdown-item" href="tv.php">Most Popular</a></li>
            <li><a class="dropdown-item" href="tv.php?type=top_rated">Top Rated</a></li>
            <li><a class="dropdown-item" href="tv.php?type=upcoming">Currently Airing</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle nav-text" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle"></i> Profile
          </a>
          <ul class="dropdown-menu dropdown-menu bg-dark" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item text-light" href="profile.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
            <li><a class="dropdown-item text-light" href="favorites.php"><i class="fas fa-heart me-2"></i>My Favorites</a></li>
            <li><a class="dropdown-item text-light" href="ratings.php"><i class="fas fa-star me-2"></i>My Ratings</a></li>
            <li><hr class="dropdown-divider bg-secondary"></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <?php if ($isAdmin): ?>
            <a class="nav-link nav-text" href="admin.php">Admin Panel</a>
          <?php endif; ?>
        </li>
      </ul>
      
      <form class="d-flex" role="search" method="GET" action="main.php">
        <input class="form-control me-2 search-bar" type="search" name="query" placeholder="Search..." aria-label="Search">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
