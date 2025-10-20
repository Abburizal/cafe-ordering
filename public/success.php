<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Ambil kode order
$order_code = $_GET['order'] ?? null;
if (!$order_code) {
    die("Kode order tidak ditemukan. <a href='menu.php'>Kembali</a>");
}

// Ambil data order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code=?");
$stmt->execute([$order_code]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order tidak ditemukan. <a href='menu.php'>Kembali</a>");
}

// Jika status belum selesai (pending / processing), anggap pembayaran sudah sukses
if ($order['status'] !== 'done') {
    $pdo->prepare("UPDATE orders SET status='done' WHERE order_code=?")->execute([$order_code]);
    unset($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran Berhasil</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 flex items-center justify-center min-h-screen p-6">
  <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md text-center">
    <div class="text-green-600 text-6xl mb-4">✅</div>
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
    <p class="text-gray-600 mb-4">Terima kasih! Pesanan Anda sedang diproses.</p>
    
    <div class="bg-gray-100 rounded-xl p-4 text-sm text-gray-700 mb-4">
      <p><strong>Kode Order:</strong> <?= htmlspecialchars($order['order_code']) ?></p>
      <p><strong>Total:</strong> Rp<?= number_format($order['total'], 0, ',', '.') ?></p>
      <p><strong>Metode:</strong> <?= strtoupper($order['payment_method']) ?></p>
    </div>

    <a href="menu.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
      Kembali ke Menu
    </a>
  </div>
</body>
</html>
