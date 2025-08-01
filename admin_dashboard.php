<?php
session_start();
require_once 'db.php';

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | TurkmenLove</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <style>body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #f5f5f5;
}

.admin-header {
    background: #333;
    color: white;
    padding: 20px;
}

.admin-header h1 {
    margin: 0 0 10px;
}

.admin-header nav ul {
    list-style: none;
    padding: 0;
    display: flex;
    gap: 20px;
}

.admin-header nav ul li a {
    color: white;
    text-decoration: none;
}

.admin-main {
    padding: 30px;
}

.admin-stats {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    width: 200px;
    text-align: center;
}</style>
    <header class="admin-header">
        <h1>Панель администратора</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Главная</a></li>
                <li><a href="admin_products.php">Управление товарами</a></li>
                <li><a href="admin_orders.php">Заказы</a></li>
                <li><a href="admin_logout.php" style="color:red;">Выйти</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-main">
        <h2>Добро пожаловать в админ-панель</h2>
        <p>Здесь вы можете управлять товарами, просматривать заказы и контролировать работу сайта.</p>

        <section class="admin-stats">
            <div class="stat-card">
                <h3>Всего товаров</h3>
                <p>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM products");
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>

            <div class="stat-card">
                <h3>Всего заказов</h3>
                <p>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
        </section>
    </main>
</body>
</html>
