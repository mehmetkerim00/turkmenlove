<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход администратора</title>
  <style>
    body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f2f2f2; }
    form { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    input { display: block; margin: 10px 0; padding: 10px; width: 100%; }
    button { padding: 10px 20px; background: #d44f68; color: white; border: none; border-radius: 5px; }
  </style>
</head>
<body>
<form method="post">
  <h2>Вход в админ-панель</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <input type="text" name="username" placeholder="Логин" required>
  <input type="password" name="password" placeholder="Пароль" required>
  <button type="submit">Войти</button>
</form>
</body>
</html>
