<?php
session_start();
include 'db.php';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_user_id'])) {
        $banId = $_POST['ban_user_id'];
        $stmt = $conn->prepare("UPDATE users SET banned = 1 WHERE id = ?");
        $stmt->bind_param("i", $banId);
        $stmt->execute();
        $_SESSION['success'] = "User has been banned.";
        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['reinstate_user_id'])) {
        $reinstateId = $_POST['reinstate_user_id'];
        $stmt = $conn->prepare("UPDATE users SET banned = 0 WHERE id = ?");
        $stmt->bind_param("i", $reinstateId);
        $stmt->execute();
        $_SESSION['success'] = "User has been reinstated.";
        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['hide_comment_id'])) {
        $commentId = $_POST['hide_comment_id'];
        $stmt = $conn->prepare("UPDATE comments SET hidden = 1 WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $_SESSION['success'] = "Comment has been hidden.";
        header("Location: admin.php");
        exit();
    }
}

// Fetch data
$reportResult = $conn->query("
    SELECT 
        cr.id AS report_id,
        cr.reason,
        cr.reported_at,
        cr.handled,
        cr.reported_by,
        rb.username AS reported_by_username,
        c.id AS comment_id,
        c.comment,
        c.user_id AS comment_author_id,
        ca.username AS comment_author_username,
        c.hidden,
        ca.banned
    FROM comment_reports cr
    JOIN comments c ON cr.comment_id = c.id
    JOIN users rb ON cr.reported_by = rb.id
    JOIN users ca ON c.user_id = ca.id
    ORDER BY cr.reported_at DESC
");

$usersResult = $conn->query("SELECT id, username, banned FROM users ORDER BY username");

$hiddenResult = $conn->query("
    SELECT c.comment, u.username, c.created_at 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.hidden = 1
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Special+Gothic&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&family=Bebas+Neue&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
        }
    </style>
</head>
<body class="bg-dark text-light">
<?php include 'sidebar.php'; ?>
<div class="container mt-5 content">
  <h2 class="mb-4">Reported Comments</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>

  <table class="table table-bordered table-dark table-striped">
    <thead>
      <tr>
        <th>Comment</th>
        <th>Commented By</th>
        <th>Reported By</th>
        <th>Reason</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $reportResult->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['comment']); ?></td>
          <td><?php echo htmlspecialchars($row['comment_author_username']); ?></td>
          <td><?php echo htmlspecialchars($row['reported_by_username']); ?></td>
          <td><?php echo htmlspecialchars($row['reason']); ?></td>
          <td><?php echo $row['reported_at']; ?></td>
          <td>
            <!-- Ban -->
            <?php if ($row['banned'] == 0): ?>
              <form action="admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="ban_user_id" value="<?php echo $row['comment_author_id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Ban User</button>
              </form>
            <?php else: ?>
              <span class="badge bg-secondary">User Banned</span>
            <?php endif; ?>

            <!-- Hide Comment -->
            <?php if ($row['hidden'] == 0): ?>
              <form action="admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="hide_comment_id" value="<?php echo $row['comment_id']; ?>">
                <button type="submit" class="btn btn-warning btn-sm">Hide Comment</button>
              </form>
            <?php else: ?>
              <span class="badge bg-secondary">Comment Hidden</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <h3 class="mt-5">All Users</h3>
  <input type="text" id="userSearch" class="form-control mb-3" placeholder="Search by username...">

  <table class="table table-bordered table-dark table-hover" id="userTable">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Username</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($user = $usersResult->fetch_assoc()): ?>
        <tr>
          <td><?php echo $user['id']; ?></td>
          <td class="username"><?php echo htmlspecialchars($user['username']); ?></td>
          <td>
            <?php if ($user['banned'] == 1): ?>
              <span class="badge bg-danger">Banned</span>
            <?php else: ?>
              <span class="badge bg-success">Active</span>
            <?php endif; ?>
          </td>
          <td>
            <!-- Ban Button for Active Users -->
            <?php if ($user['banned'] == 0): ?>
              <form method="POST" action="admin.php">
                <input type="hidden" name="ban_user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Ban User</button>
              </form>
            <?php else: ?>
              <form method="POST" action="admin.php">
                <input type="hidden" name="reinstate_user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" class="btn btn-success btn-sm">Reinstate</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <h3 class="mt-5">Hidden Comments</h3>
  <table class="table table-bordered table-dark table-hover">
    <thead>
      <tr>
        <th>Comment</th>
        <th>By User</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($hidden = $hiddenResult->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($hidden['comment']); ?></td>
          <td><?php echo htmlspecialchars($hidden['username']); ?></td>
          <td><?php echo $hidden['created_at']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Live Search Script -->
<script>
  $('#userSearch').on('keyup', function() {
    let value = $(this).val().toLowerCase();
    $('#userTable tbody tr').filter(function() {
      $(this).toggle($(this).find(".username").text().toLowerCase().indexOf(value) > -1)
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
