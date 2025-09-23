<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Фильтр по дате
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$where_clause = '';
$params = [];

if ($start_date && $end_date) {
    $where_clause = 'WHERE delivery_date BETWEEN :start_date AND :end_date';
    $params = [':start_date' => $start_date, ':end_date' => $end_date];
}

// Получение заказов
$stmt_orders = $conn->prepare("SELECT o.*, p.name AS product_name, p.price AS product_price, p.image AS product_image 
    FROM orders o 
    JOIN products p ON o.product_id = p.id $where_clause 
    ORDER BY o.id DESC");
$stmt_orders->execute($params);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// Получение товаров
$stmt_products = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$total_orders = count($orders);
$total_amount = 0;
foreach ($orders as $order) {
    $total_amount += $order['quantity'] * $order['product_price'];
}

// CSRF-токен для форм
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .modal-lg { max-width: 800px; }
        .tab-content { margin-top: 20px; }
        .product-img { max-width: 50px; height: auto; }
    </style>
</head>
<body class="container mt-5">
    <h1>Админ-панель</h1>
    <a href="admin_logout.php" class="btn btn-secondary mb-3">Выйти</a>

    <?php if (isset($_SESSION['admin_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_success']) ?></div>
        <?php unset($_SESSION['admin_success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['admin_error'])): ?>
        <div class="alert alert-danger">
            <?php if (is_array($_SESSION['admin_error'])): ?>
                <ul>
                    <?php foreach ($_SESSION['admin_error'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= htmlspecialchars($_SESSION['admin_error']) ?>
            <?php endif; ?>
        </div>
        <?php unset($_SESSION['admin_error']); ?>
    <?php endif; ?>

    <!-- Вкладки -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#orders">Заказы</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#products">Товары</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#stats">Статистика</a>
        </li>
    </ul>

    <div class="tab-content">
     <!-- Заказы -->
<div class="tab-pane fade show active" id="orders">
    <h2>Заказы</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addOrderModal">Добавить заказ</button>
    <form method="get" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Дата с</label>
                <input type="text" class="form-control flatpickr" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Дата по</label>
                <input type="text" class="form-control flatpickr" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Фильтровать</button>
            </div>
        </div>
    </form>
    <?php if (empty($orders)): ?>
        <p>Нет заказов.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Клиент</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Получатель</th>
                    <th>Город</th>
                    <th>Дата доставки</th>
                    <th>Время доставки</th>
                    <th>Товар</th>
                    <th>Фото</th>
                    <th>Количество</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                    // Добавляем префикс img/ к пути изображения
                    $image_path = strpos($order['product_image'], 'img/') === 0 ? $order['product_image'] : 'img/' . $order['product_image'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['client_name']) ?></td>
                        <td><?= htmlspecialchars($order['client_email']) ?></td>
                        <td><?= htmlspecialchars($order['client_phone']) ?></td>
                        <td><?= htmlspecialchars($order['reciever_name']) ?></td>
                        <td><?= htmlspecialchars($order['city']) ?></td>
                        <td><?= htmlspecialchars($order['delivery_date']) ?></td>
                        <td><?= htmlspecialchars($order['delivery_time']) ?></td>
                        <td><?= htmlspecialchars($order['product_name']) ?></td>
                        <td>
                            <?php if (!empty($order['product_image']) && file_exists($image_path)): ?>
                                <img src="/<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" class="product-img">
                            <?php else: ?>
                                <span>Изображение отсутствует (путь: <?= htmlspecialchars($image_path) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($order['quantity']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editOrderModal<?= $order['id'] ?>">Редактировать</button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOrderModal<?= $order['id'] ?>">Удалить</button>
                        </td>
                    </tr>
                    <!-- Модальное окно для редактирования заказа (без изменений) -->
                    <div class="modal fade" id="editOrderModal<?= $order['id'] ?>" tabindex="-1">
                        <!-- ... (код модального окна остаётся без изменений) -->
                    </div>
                    <!-- Модальное окно для удаления заказа (без изменений) -->
                    <div class="modal fade" id="deleteOrderModal<?= $order['id'] ?>" tabindex="-1">
                        <!-- ... (код модального окна остаётся без изменений) -->
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Товары -->
<div class="tab-pane fade" id="products">
    <h2>Товары</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Добавить товар</button>
    <?php if (empty($products)): ?>
        <p>Нет товаров.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Цена (USD)</th>
                    <th>Изображение</th>
                    <th>Категория</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <?php
                    // Добавляем префикс img/ к пути изображения
                    $image_path = strpos($product['image'], 'img/') === 0 ? $product['image'] : 'img/' . $product['image'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($product['id']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?></td>
                        <td>
                            <?php if (!empty($product['image']) && file_exists($image_path)): ?>
                                <img src="/<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img">
                            <?php else: ?>
                                <span>Изображение отсутствует (путь: <?= htmlspecialchars($image_path) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['category']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $product['id'] ?>">Редактировать</button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?= $product['id'] ?>">Удалить</button>
                        </td>
                    </tr>
                            <!-- Модальное окно для редактирования заказа -->
                            <div class="modal fade" id="editOrderModal<?= $order['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать заказ #<?= $order['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="admin_edit_order.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Клиент</label>
                                                    <input type="text" class="form-control" name="client_name" value="<?= htmlspecialchars($order['client_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="client_email" value="<?= htmlspecialchars($order['client_email']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Телефон клиента</label>
                                                    <input type="tel" class="form-control" name="client_phone" value="<?= htmlspecialchars($order['client_phone']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Получатель</label>
                                                    <input type="text" class="form-control" name="reciever_name" value="<?= htmlspecialchars($order['reciever_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Телефон получателя</label>
                                                    <input type="tel" class="form-control" name="reciever_phone" value="<?= htmlspecialchars($order['reciever_phone']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Город</label>
                                                    <select class="form-control" name="city" required>
                                                        <option value="Ашхабад" <?= $order['city'] === 'Ашхабад' ? 'selected' : '' ?>>Ашхабад</option>
                                                        <option value="Туркменабат" <?= $order['city'] === 'Туркменабат' ? 'selected' : '' ?>>Туркменабат</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Дата доставки</label>
                                                    <input type="text" class="form-control flatpickr" name="delivery_date" value="<?= htmlspecialchars($order['delivery_date']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Время доставки</label>
                                                    <select class="form-control" name="delivery_time" required>
                                                        <?php
                                                        $times = ['10:00–12:00', '11:00–13:00', '12:00–14:00', '13:00–15:00', '14:00–16:00', '15:00–17:00', '16:00–18:00', '17:00–19:00', '18:00–20:00', '19:00–21:00', '20:00–22:00', '21:00–23:00', '22:00–00:00'];
                                                        foreach ($times as $time) {
                                                            echo "<option value='$time' " . ($order['delivery_time'] === $time ? 'selected' : '') . ">$time</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Товар</label>
                                                    <select class="form-control" name="product_id" required>
                                                        <?php foreach ($products as $product): ?>
                                                            <option value="<?= $product['id'] ?>" <?= $order['product_id'] === $product['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($product['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Количество</label>
                                                    <input type="number" class="form-control" name="quantity" value="<?= htmlspecialchars($order['quantity']) ?>" min="1" required>
                                                </div>
                                                <div class="mb-3 form-check">
                                                    <input type="checkbox" class="form-check-input" name="no_address" id="no_address_<?= $order['id'] ?>" <?= $order['no_address'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="no_address_<?= $order['id'] ?>">Не знаю адрес</label>
                                                </div>
                                                <div class="address-fields" style="display: <?= $order['no_address'] ? 'none' : 'block' ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Адрес</label>
                                                        <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($order['address'] ?? '') ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Квартира</label>
                                                        <input type="number" class="form-control" name="apartment" value="<?= htmlspecialchars($order['apartment'] ?? '') ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Подъезд</label>
                                                        <input type="number" class="form-control" name="gate" value="<?= htmlspecialchars($order['gate'] ?? '') ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Этаж</label>
                                                        <input type="number" class="form-control" name="floor" value="<?= htmlspecialchars($order['floor'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Комментарий</label>
                                                    <textarea class="form-control" name="comment"><?= htmlspecialchars($order['comment'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Модальное окно для удаления заказа -->
                            <div class="modal fade" id="deleteOrderModal<?= $order['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Удалить заказ #<?= $order['id'] ?>?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="admin_delete_order.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <p>Вы уверены, что хотите удалить заказ #<?= $order['id'] ?> для клиента <?= htmlspecialchars($order['client_name']) ?>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-danger">Удалить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Товары -->
        <div class="tab-pane fade" id="products">
            <h2>Товары</h2>
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Добавить товар</button>
            <?php if (empty($products)): ?>
                <p>Нет товаров.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Цена (USD)</th>
                            <th>Изображение</th>
                            <th>Категория</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['id']) ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['price']) ?></td>
                                <td><img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img"></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $product['id'] ?>">Редактировать</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal<?= $product['id'] ?>">Удалить</button>
                                </td>
                            </tr>
                            <!-- Модальное окно для редактирования товара -->
                            <div class="modal fade" id="editProductModal<?= $product['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать товар #<?= $product['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="admin_edit_product.php" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Название</label>
                                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Цена (USD)</label>
                                                    <input type="number" class="form-control" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Текущее изображение</label>
                                                    <div><img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Новое изображение (JPG/PNG, до 5 МБ)</label>
                                                    <input type="file" class="form-control" name="image" accept="image/jpeg,image/png">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Категория</label>
                                                    <select class="form-control" name="category" required>
                                                        <option value="flower" <?= $product['category'] === 'flower' ? 'selected' : '' ?>>Цветы</option>
                                                        <option value="cake" <?= $product['category'] === 'cake' ? 'selected' : '' ?>>Торты</option>
                                                        <option value="toy" <?= $product['category'] === 'toy' ? 'selected' : '' ?>>Игрушки</option>
                                                        <option value="giftcard" <?= $product['category'] === 'giftcard' ? 'selected' : '' ?>>Подарочные карты</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Модальное окно для удаления товара -->
                            <div class="modal fade" id="deleteProductModal<?= $product['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Удалить товар #<?= $product['id'] ?>?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="post" action="admin_delete_product.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <p>Вы уверены, что хотите удалить товар "<?= htmlspecialchars($product['name']) ?>"?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-danger">Удалить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Статистика -->
        <div class="tab-pane fade" id="stats">
            <h2>Статистика</h2>
            <p><strong>Общее количество заказов:</strong> <?= $total_orders ?></p>
            <p><strong>Общая сумма заказов (USD):</strong> <?= number_format($total_amount, 2) ?></p>
        </div>
    </div>

    <!-- Модальное окно для добавления заказа -->
    <div class="modal fade" id="addOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить заказ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="admin_add_order.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Клиент</label>
                            <input type="text" class="form-control" name="client_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="client_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон клиента</label>
                            <input type="tel" class="form-control" name="client_phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Получатель</label>
                            <input type="text" class="form-control" name="reciever_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон получателя</label>
                            <input type="tel" class="form-control" name="reciever_phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Город</label>
                            <select class="form-control" name="city" required>
                                <option value="Ашхабад">Ашхабад</option>
                                <option value="Туркменабат">Туркменабат</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Дата доставки</label>
                            <input type="text" class="form-control flatpickr" name="delivery_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Время доставки</label>
                            <select class="form-control" name="delivery_time" required>
                                <?php foreach ($times as $time): ?>
                                    <option value="<?= $time ?>"><?= $time ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Товар</label>
                            <select class="form-control" name="product_id" required>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Количество</label>
                            <input type="number" class="form-control" name="quantity" min="1" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="no_address" id="no_address_new">
                            <label class="form-check-label" for="no_address_new">Не знаю адрес</label>
                        </div>
                        <div class="address-fields" style="display: block">
                            <div class="mb-3">
                                <label class="form-label">Адрес</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Квартира</label>
                                <input type="number" class="form-control" name="apartment">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Подъезд</label>
                                <input type="number" class="form-control" name="gate">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Этаж</label>
                                <input type="number" class="form-control" name="floor">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Комментарий</label>
                            <textarea class="form-control" name="comment"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления товара -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добавить товар</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="admin_add_product.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Название</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Цена (USD)</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Изображение (JPG/PNG, до 5 МБ)</label>
                            <input type="file" class="form-control" name="image" accept="image/jpeg,image/png" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Категория</label>
                            <select class="form-control" name="category" required>
                                <option value="flower">Цветы</option>
                                <option value="cake">Торты</option>
                                <option value="toy">Игрушки</option>
                                <option value="giftcard">Подарочные карты</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <script>
        flatpickr(".flatpickr", {
            locale: "ru",
            dateFormat: "d F Y",
            defaultDate: "today",
            minDate: "today",
            disableMobile: false,
            clickOpens: true,
            allowInput: false
        });

        document.querySelectorAll('[name="no_address"]').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const addressFields = this.closest('.modal-body').querySelector('.address-fields');
                addressFields.style.display = this.checked ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>