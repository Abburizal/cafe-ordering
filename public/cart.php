<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;
if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($products as $p) {
        $qty = $cart[$p['id']];
        $subtotal = $p['price'] * $qty;
        $items[] = ['product'=>$p, 'qty'=>$qty, 'subtotal'=>$subtotal];
        $total += $subtotal;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Keranjang</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Keranjang</h1>
    <?php if (empty($items)): ?>
      <div>Keranjang kosong. <a href="menu.php" class="text-blue-600">Kembali ke menu</a></div>
    <?php else: ?>
      <table class="w-full border">
        <thead><tr><th class="text-left p-2">Produk</th><th class="p-2">Qty</th><th class="p-2">Subtotal</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): ?>
            <tr>
              <td class="p-2"><?= htmlspecialchars($it['product']['name']) ?></td>
              <td class="p-2"><?= $it['qty'] ?></td>
              <td class="p-2"><?= currency($it['subtotal']) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr><td></td><td class="p-2 font-bold">Total</td><td class="p-2 font-bold"><?= currency($total) ?></td></tr>
        </tbody>
      </table>

      <div class="mt-4">
        <form action="checkout.php" method="get">
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Checkout</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
