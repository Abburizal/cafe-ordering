<?php
// public/pay_qris_mock.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;

// Ambil action dari tombol simulate
$simulate = $_POST['simulate'] ?? null;

// Ambil cart dan meja dari session
$cart = $_SESSION['cart'] ?? [];
$table_id = $_SESSION['table_id'] ?? null;

if (empty($cart) || !$table_id) {
    die("Sesi tidak valid. <a href='menu.php'>Kembali</a>");
}

// Ambil produk & hitung total
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach ($products as $p) {
    $total += $p['price'] * $cart[$p['id']];
}

// Jika tombol Simulate Paid / Cancel ditekan -> update order status
// Kita akan meletakkan logic simulate di bawah (setelah pembuatan order)

// Jika belum ada order dibuat dalam request ini, buat order dulu
// (kami membuat order dengan status 'pending' untuk QR)
if (!isset($_SESSION['last_mock_order']) || empty($_SESSION['last_mock_order'])) {

    // buat order di DB
    $order_code = generateOrderCode(); // pastikan helper sesuai
    $payment_method = 'qris_mock';
    $insert = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_id, total, payment_method, status)
                         VALUES (?, NULL, NULL, ?, ?, 'pending')");
$insert->execute([$order_code, $total, $payment_method]);

    $order_id = $pdo->lastInsertId();

    // simpan order_items
    $insertItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    foreach($products as $p) {
        $qty = $cart[$p['id']];
        $insertItem->execute([$order_id, $p['id'], $qty, $p['price']]);
    }

    // simpan referensi order di session agar operasi simulate tahu apa yang harus diupdate
    $_SESSION['last_mock_order'] = [
        'id' => $order_id,
        'code' => $order_code,
        'total' => $total
    ];
} else {
    $order_id = (int)$_SESSION['last_mock_order']['id'];
    $order_code = $_SESSION['last_mock_order']['code'];
    $total = $_SESSION['last_mock_order']['total'];
}

// Jika user menekan simulate action
if ($simulate && isset($_SESSION['last_mock_order'])) {
    if ($simulate === 'success') {
        // Tandai order sebagai 'processing' atau 'done' (pilih sesuai alur)
        // Saya set ke 'processing' untuk menandai kasir mulai memproses pesanan
        $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?")->execute([$order_id]);


        // Kosongkan keranjang setelah pembayaran berhasil
        unset($_SESSION['cart']);
        // Hapus last_mock_order agar bisa buat order baru berikutnya
        unset($_SESSION['last_mock_order']);

        // Redirect ke halaman sukses (kamu bisa menyesuaikan success.php menerima order param)
        header('Location: success.php?order=' . urlencode($order_code));
        exit;
    } elseif ($simulate === 'cancel') {
        // Tandai order cancelled
        $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute([$order_id]);
        unset($_SESSION['last_mock_order']);
        // Biar user tahu, redirect kembali ke menu
        header('Location: menu.php?msg=' . urlencode('Pembayaran dibatalkan.'));
        exit;
    }
}

$qr_payload = "ORDER:{$order_code};AMT:{$total};TABLE:{$table_id}";
$qrCode = new QrCode($qr_payload);
$qrCode->setEncoding(new Encoding('UTF-8'));
$qrCode->setSize(350);
$qrCode->setMargin(10);
$qrCode->setForegroundColor(new Color(0,0,0));
$qrCode->setBackgroundColor(new Color(255,255,255));

$writer = new PngWriter();

// optional logo
$logoPath = __DIR__ . '/../public/logo.png';
if (file_exists($logoPath)) {
    $logo = Logo::create($logoPath)->setResizeToWidth(50);
    $result = $writer->write($qrCode, $logo);
} else {
    $result = $writer->write($qrCode);
}

// encode base64
$qr_url = 'data:image/png;base64,' . base64_encode($result->getString());

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pembayaran QRIS (Prototype)</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full bg-white rounded-xl shadow p-6 text-center">
    <h1 class="text-xl font-bold mb-2">Pembayaran QRIS (Prototype)</h1>
    <p class="text-sm text-gray-500 mb-4">Scan QR berikut dengan aplikasi pembayaran (atau gunakan tombol simulasi).</p>

    <div class="mb-4">
      <img src="<?= e($qr_url) ?>" alt="QRIS Prototype" class="mx-auto rounded-lg shadow-sm">
    </div>

    <div class="text-left mb-4">
      <p class="text-sm text-gray-600">Order: <strong><?= e($order_code) ?></strong></p>
      <p class="text-sm text-gray-600">Nomor Meja: <strong><?= e($table_id) ?></strong></p>
      <p class="text-sm text-gray-600">Total: <strong><?= currency($total) ?></strong></p>
    </div>

    <div class="flex gap-3 justify-center">
      <form method="post" style="display:inline;">
        <input type="hidden" name="simulate" value="success">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">Simulate Paid ✅</button>
      </form>

      <form method="post" style="display:inline;">
        <input type="hidden" name="simulate" value="cancel">
        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg shadow hover:bg-red-600">Simulate Cancel ✖️</button>
      </form>
    </div>

    <div class="mt-4 text-sm">
      <a href="menu.php" class="text-gray-600 hover:underline">Kembali ke Menu</a>
    </div>
  </div>
</body>
</html>
