<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body>
  <?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  session_start();

  if (!isset($_SESSION['user'])) {
      echo "<p class='text-center text-2xl'>You are not logged in. You will be redirected to the login page in a few seconds.</p>";
      echo "<script>
              setTimeout(function() {
                  window.location.href = 'login.html';
              }, 4000);
            </script>";
      exit();
  }

  $userEmail = $_SESSION['user']['email'];

  require __DIR__ . "/../backend/database/connectDB.php";


  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  if (!$stmt) {
      echo json_encode([
          "success" => false,
          "message" => "DB Error: " . $conn->error
      ]);
      exit();
  }

  $stmt->bind_param("s", $userEmail);
  if (!$stmt->execute()) {
      echo json_encode([
          "success" => false,
          "message" => "Error fetching details: " . $stmt->error
      ]);
      exit();
  }

  $result = $stmt->get_result()->fetch_assoc();
  $stmt->close();


  if (!isset($result['name'])) {
        echo "<p>Unable to fetch user details.</p>";
        die();
  }

$user_name = $result['name'];

  ?>
  <h1 class="text-4xl text-center">Welcome <?php echo htmlspecialchars($user_name); ?></h1>
</body>
</html>