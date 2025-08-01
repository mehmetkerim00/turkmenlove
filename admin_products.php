<?php
session_start();
require_once '../db.php';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Обработка удаления товара
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: admin_products.php");
    exit;
}

// Обработка добавления товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    move_uploaded_file($tmp, "../img/$image");

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $image, $category]);
    header("Location: admin_products.php");
    exit;
}

// Получение всех товаров
$stmt = $conn->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Управление товарами</title>
  <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
<div class="admin-container">
  <h2>Управление товарами</h2>
  <a href="admin_dashboard.php" class="admin-btn">⬅ Назад в панель</a>
  <br><br>

  <h3>Добавить новый товар</h3>
  <form method="post" enctype="multipart/form-data" class="admin-form">
    <input type="text" name="name" placeholder="Название" required>
    <textarea name="description" placeholder="Описание" required></textarea>
    <input type="number" name="price" placeholder="Цена" required>
    <select name="category" required>
      <option value="flower">Цветы</option>
      <option value="cake">Торты</option>
      <option value="giftcard">Подарочные карты</option>
      <option value="toy">Игрушки</option>
    </select>
    <input type="file" name="image" required>
    <button type="submit" name="add_product">Добавить товар</button>
  </form>

  <h3>Список товаров</h3>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Категория</th>
        <th>Изображение</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
      <tr>
        <td><?= $product['id'] ?></td>
        <td><?= htmlspecialchars($product['name']) ?></td>
        <td><?= $product['price'] ?></td>
        <td><?= $product['category'] ?></td>
        <td><img src="../img/<?= $product['image'] ?>" alt="" height="50"></td>
        <td>
          <!-- Можно позже добавить редактирование -->
          <a href="?delete=<?= $product['id'] ?>" onclick="return confirm('Удалить товар?')">Удалить</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
        