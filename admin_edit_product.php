<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    $_SESSION['admin_error'] = 'Ошибка безопасности';
    header('Location: admin.php');
    exit;
}

$product_id = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
$errors = [];
$name = trim(htmlspecialchars($_POST['name'] ?? ''));
$price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
$category = trim(htmlspecialchars($_POST['category'] ?? ''));

if (!$product_id) {
    $errors[] = 'Некорректный ID товара';
}

if (empty($name)) {
    $errors[] = 'Название товара обязательно';
} elseif (!preg_match('/^[\w\s.,-]{2,100}$/u', $name)) {
    $errors[] = 'Некорректное название (2–100 символов)';
}

if ($price === false || $price < 0) {
    $errors[] = 'Некорректная цена (должна быть числом ≥ 0)';
}

$allowed_categories = ['flower', 'cake', 'toy', 'giftcard'];
if (!in_array($category, $allowed_categories)) {
    $errors[] = 'Некорректная категория';
}

// Получение текущего изображения
$stmt = $conn->prepare("SELECT image FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$current_image = $stmt->fetchColumn();

$image_path = $current_image;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5 МБ

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Ошибка загрузки изображения';
    } elseif (!in_array($file['type'], $allowed_types)) {
        $errors[] = 'Разрешены только JPG и PNG';
    } elseif ($file['size'] > $max_size) {
        $errors[] = 'Изображение слишком большое (максимум 5 МБ)';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $image_path = 'img/' . uniqid('product_') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $image_path)) {
            $errors[] = 'Не удалось сохранить изображение';
        } elseif ($current_image && file_exists($current_image)) {
            unlink($current_image); // Удаляем старое изображение
        }
    }
}

if (!empty($errors)) {
    $_SESSION['admin_error'] = $errors;
    header('Location: admin.php');
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE products SET name = :name, price = :price, image = :image, category = :category WHERE id = :id");
    $stmt->execute([
        ':name' => $name,
        ':price' => $price,
        ':image' => $image_path,
        ':category' => $category,
        ':id' => $product_id
    ]);
    $_SESSION['admin_success'] = 'Товар успешно обновлён';
    header('Location: admin.php');
    exit;
} catch (PDOException $e) {
    error_log("Edit Product Error: " . $e->getMessage());
    $_SESSION['admin_error'] = ['Ошибка при обновлении товара'];
    header('Location: admin.php');
    exit;
}
?>