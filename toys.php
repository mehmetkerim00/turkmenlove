<?php

include 'header.php';

try {
$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'toy'");
$stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}
?>
<main>
    <section class="products">
      <div class="product-grid">
    <?php foreach ($products as $product): ?>
        <?php
        $priceUSD = floatval($product['price']);
        $rate = $rates[$currency] ?? 1;
        $symbol = $symbols[$currency] ?? '$';
      $convertedPrice = ceil(($priceUSD * $rate) / 10) * 10;
        ?>
        <div class="product-card">
            <img src="img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <p><?= htmlspecialchars($product['name']) ?></p>
            <span class="price__span">Цена: <?= $symbol . $convertedPrice ?> (<?= $currency ?>)</span>
        <form method="post" action="add_to_cart.php" style="display:inline;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <button type="submit" class="main__btn">Оформить заказ</button>
</form>

        </div>
    <?php endforeach; ?>
</div>

      
    </section>
</main>
<?php include 'footer.php'; ?>


</body>
</html>
