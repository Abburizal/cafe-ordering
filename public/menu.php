<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

$stmt = $pdo->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$table_id = $_SESSION['table_id'] ?? null;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Menu</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-orange-50 p-6">
  <div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-orange-700">🍽️ Daftar Menu</h1>
      <a href="cart.php" class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
        🛒 Keranjang
      </a>
    </div>

    <?php if (!$table_id): ?>
      <div class="mb-4 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
        Nomor meja tidak terdeteksi. Silakan scan QR meja terlebih dahulu.
      </div>
    <?php else: ?>
      <div class="mb-4 text-sm text-gray-700">
        Nomor meja: <strong><?= e($table_id) ?></strong>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach($products as $p): ?>
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
          
          <!-- Gambar Produk -->
          <?php if (!empty($p['image']) && file_exists(__DIR__ . "/assets/images/" . $p['image'])): ?>
            <img src="assets/images/<?= e($p['image']) ?>" 
                 alt="<?= e($p['name']) ?>" 
                 class="w-full h-48 object-cover">
          <?php else: ?>
            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
              Tidak ada gambar
            </div>
          <?php endif; ?>

          <!-- Info Produk -->
          <div class="p-4">
            <h3 class="font-semibold text-lg text-gray-800"><?= e($p['name']) ?></h3>
            <p class="text-sm text-gray-600 mt-1"><?= e($p['description'] ?? '') ?></p>
            <div class="mt-3 text-orange-600 font-bold text-lg"><?= currency($p['price']) ?></div>
            
            <form action="add_cart.php" method="post" class="mt-4 flex items-center gap-2">
              <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
              <input type="number" name="qty" value="1" min="1" class="w-20 px-2 py-1 border rounded">
              <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                Tambah
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
