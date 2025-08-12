<?php
session_start();

include 'db.php';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      if (password_verify($password, $user['password_hash'])) {
        if ($user['banned'] == 1) {
          echo "Your account has been banned.";
          exit; }
        elseif ($user['is_verified'] == 1) {
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['is_admin'] = ($user['username'] === 'dev') ? 1 : 0;
          header("Location: main.php");
          exit();      
          } else {
              $error = "Please verify your email before logging in.";
          }
      } else {
          $error = "Incorrect password.";
      }
  } else {
      $error = "User not found.";
  }
  
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">

</head>

<body class="text-light login-bg1">
  <h1 class="title">CineVault</h1>
  <p class="desc">Your Portal to Timeless Cinema</p>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div name="card" class="col-md-6">
        <div class="card p-4 shadow login-card">
          <h2 class="mb-4 text-center">Login</h2>
          
          <?php if ($error): ?>
            <div class="alert alert-danger" id="error-alert"><?php echo $error; ?></div>
          <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success" id="success-alert"><?php echo $success; ?></div>
        <?php endif; ?>

          <form class="login" action="index.php" method="POST">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
            </div>
            <button type="submit" class="btn btn-outline-secondary w-100">Login</button>
          </form>

          <p class="mt-3 text-center">Don't have an account? <a href="register.php" target="_blank">Register here</a></p>
        </div>
      </div>
    </div>
  </div>
  <script>
  setTimeout(function () {
    const successAlert = document.getElementById("success-alert");
    const errorAlert = document.getElementById("error-alert");

    [successAlert, errorAlert].forEach(function(alert) {
      if (alert) {
        alert.style.transition = "opacity 0.5s ease";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500); 
      }
    });
  }, 5000); 
</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
