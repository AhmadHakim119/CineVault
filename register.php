<?php
    session_start();
    include 'db.php';
    $error = "";
    $success = "";
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']))
        {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $conn ->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
            $stmt ->bind_param('ss', $username, $email);
            $stmt -> execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $error = "Username or Email already taken";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn -> prepare("INSERT INTO users (username, email, password_hash) VALUES (?,?,?)");
                $stmt ->bind_param("sss",$username,$email, $password_hash);
                $stmt -> execute();

                if($stmt->affected_rows > 0) {
                  $token = bin2hex(random_bytes(16));

                  $update = $conn -> prepare("UPDATE users SET verification_token = ? WHERE email = ?");
                  $update -> bind_param("ss", $token, $email);
                  $update -> execute();

                  $to = $email;
                  $subject = "Verify your CineVault account";
                  $verificationLink = "http://localhost/Movies/verify.php?token=" . $token;
                  
                    $message = "
                              <html>
                                <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px;'>
                                  <div style='max-width: 600px; margin: auto; background: #fff; padding: 30px; border: 1px solid #eee; border-radius: 10px;'>
                                    <div style='font-size: 24px; font-weight: bold; color: #e50914; text-align: center; margin-bottom: 20px;'>
                                      Welcome to CineVault, $username!
                                    </div>
                                    <p style='color: #333;'>Thanks for signing up. You're just one step away from unlocking a world of cinema.</p>
                                    <p style='color: #333;'>Click the button below to verify your email address:</p>
                                    <p style='text-align: center; margin: 30px 0;'>
                                      <a href='$verificationLink' style='background-color: #e50914; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Verify Email</a>
                                    </p>
                                    <p style='color: #666;'>If you didn’t request this, you can safely ignore this email.</p>
                                    <div style='font-size: 12px; color: #aaa; margin-top: 30px; text-align: center;'>
                                      &copy; " . date('Y') . " CineVault. All rights reserved.
                                    </div>
                                  </div>
                                </body>
                              </html>
                              ";
                  $headers = "MIME-Version: 1.0\r\n";
                  $headers .="Content-type:text/html;charset=UTF-8\r\n";
                  $headers .='From: CineVault <no-reply@cinevault.com>' . "\r\n";

                  if (mail($to, $subject, $message, $headers)) {
                    $_SESSION["success"] = "Registration successful! Check your email to verify your account";
                    header("Location: index.php");
                    exit();
                  } else {
                    $error = "Failed to send verification email.";
                  }
            } else {
                $error = "Error ocurred";
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill all the fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
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
          <h2 class="mb-4 text-center">Register</h2>

          <?php if ($error): ?>
    	      <div class="alert alert-danger" id="error-alert"><?php echo $error; ?></div>
            <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success" id="success-alert"><?php echo $success; ?></div>
          <?php endif; ?>

          <form class="login" action="register.php" method="POST">
            <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Enter your password" required />
            </div>
            <button type="submit" class="btn btn-outline-secondary w-100">Register</button>
          </form>
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
