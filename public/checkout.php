<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("Keranjang kosong. <a href='menu.php'>Kembali</a>");
}
$table_id = $_SESSION['table_id'] ?? null;
if (!$table_id) {
    die("Nomor meja tidak terdeteksi. Scan QR meja terlebih dahulu.");
}

// hitung total
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach($products as $p) {
    $total += $p['price'] * $cart[$p['id']];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Checkout</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
  <div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Checkout</h1>
    <div class="mb-3">Nomor meja: <strong><?= htmlspecialchars($table_id) ?></strong></div>
    <div class="mb-3">Total: <strong><?= currency($total) ?></strong></div>

    <form action="pay_qris.php" method="post">
      <input type="hidden" name="action" value="qris">
      <button class="px-4 py-2 bg-blue-600 text-white rounded" name="pay" value="qris">Bayar QRIS</button>
    </form>

    <form action="pay_qris.php" method="post" class="mt-2">
      <input type="hidden" name="action" value="cash">
      <button class="px-4 py-2 bg-green-600 text-white rounded" name="pay" value="cash">Bayar Tunai (Pesan saja)</button>
    </form>
  </div>
</body>
</html>
