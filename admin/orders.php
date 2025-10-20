<?php
require_once '../config/config.php';
require_once '../app/helpers.php';

// Pastikan admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$message = '';

// === Ubah Status Pesanan ===
if (isset($_POST['update_status'])) {
  $id = (int)$_POST['order_id'];
  $status = $_POST['status'];

  $valid_status = ['pending', 'processing', 'done', 'cancelled'];
  if (!in_array($status, $valid_status)) {
    $message = "⚠️ Status tidak valid!";
  } else {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    $message = "✅ Status pesanan #$id berhasil diubah menjadi <b>$status</b>!";
  }
}

// === Filter Pesanan Berdasarkan Status ===
$filter = $_GET['status'] ?? 'semua';
if ($filter === 'semua') {
  $stmt = $pdo->query("SELECT o.*, t.name AS table_name FROM orders o 
                       LEFT JOIN tables t ON o.table_id = t.id 
                       ORDER BY o.created_at DESC");
} else {
  $stmt = $pdo->prepare("SELECT o.*, t.name AS table_name FROM orders o 
                         LEFT JOIN tables t ON o.table_id = t.id 
                         WHERE o.status = ? ORDER BY o.created_at DESC");
  $stmt->execute([$filter]);
}
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$adminName = e($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Pesanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes popup {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.05); opacity: 1; }
      100% { transform: scale(1); opacity: 1; }
    }
    .popup { animation: popup 0.4s ease-out; }
  </style>
</head>
<body class="bg-stone-100 min-h-screen">

  <!-- Navbar -->
  <header class="fixed top-3 left-0 right-0 z-50 flex justify-center">
    <div class="flex items-center justify-between w-[95%] md:w-[80%] lg:w-[70%] bg-white/80 backdrop-blur-lg border border-gray-200 rounded-3xl shadow px-5 py-3 relative">
      <div class="flex items-center space-x-2">
        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" class="h-6" alt="">
      </div>

      <!-- Menu Desktop -->
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="dashboard.php" class="hover:text-blue-600">Dashboard</a>
        <a href="product.php" class="hover:text-blue-600">Produk</a>
        <a href="orders.php" class="text-blue-600 font-semibold">Orders</a>
      </nav>

      <!-- Menu Kanan -->
      <div class="hidden md:flex items-center space-x-2">
        <a href="logout.php" class="px-3 py-1 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-100">Sign out</a>
      </div>

      <!-- Tombol Mobile -->
      <button id="menuBtn" class="md:hidden flex items-center p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
        <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Dropdown Mobile -->
      <div id="dropdownMenu" class="hidden absolute top-14 right-4 w-40 bg-white border border-gray-200 rounded-xl shadow-md py-2 text-sm">
        <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-100">Dashboard</a>
        <a href="product.php" class="block px-4 py-2 hover:bg-gray-100">Produk</a>
        <a href="orders.php" class="block px-4 py-2 bg-gray-100 font-semibold">Orders</a>
        <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
      </div>
    </div>
  </header>

  <!-- Konten -->
  <main class="pt-24 px-4 md:px-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">📋 Manajemen Pesanan</h2>
      <form method="GET" class="flex gap-2">
        <select name="status" class="border rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-400 outline-none">
          <option value="semua" <?= $filter==='semua'?'selected':'' ?>>Semua</option>
          <option value="pending" <?= $filter==='pending'?'selected':'' ?>>Pending</option>
          <option value="processing" <?= $filter==='processing'?'selected':'' ?>>Diproses</option>
          <option value="done" <?= $filter==='done'?'selected':'' ?>>Selesai</option>
          <option value="cancelled" <?= $filter==='cancelled'?'selected':'' ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg font-semibold text-sm">
          🔍 Filter
        </button>
      </form>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow">
      <table class="min-w-full text-sm">
        <thead class="bg-stone-200">
          <tr>
            <th class="py-3 px-4 text-left">#</th>
            <th class="py-3 px-4 text-left">Meja</th>
            <th class="py-3 px-4 text-left">Total</th>
            <th class="py-3 px-4 text-left">Metode</th>
            <th class="py-3 px-4 text-left">Status</th>
            <th class="py-3 px-4 text-left">Waktu</th>
            <th class="py-3 px-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="7" class="py-6 text-center text-gray-500">😴 Belum ada pesanan...</td></tr>
          <?php else: foreach ($orders as $o): ?>
          <tr class="border-t hover:bg-gray-50">
            <td class="py-3 px-4"><?= e($o['id']) ?></td>
            <td class="py-3 px-4"><?= e($o['table_name'] ?? '-') ?></td>
            <td class="py-3 px-4 text-blue-600 font-semibold"><?= rupiah($o['total']) ?></td>
            <td class="py-3 px-4"><?= ucfirst($o['payment_method']) ?></td>
            <td class="py-3 px-4">
              <span class="px-2 py-1 rounded text-xs 
                <?= $o['status']=='done' ? 'bg-green-100 text-green-600' :
                    ($o['status']=='processing' ? 'bg-yellow-100 text-yellow-600' :
                    ($o['status']=='cancelled' ? 'bg-red-100 text-red-600' :
                    'bg-gray-100 text-gray-600')) ?>">
                <?= ['pending'=>'Pending','processing'=>'Diproses','done'=>'Selesai','cancelled'=>'Dibatalkan'][$o['status']] ?? ucfirst($o['status']) ?>
              </span>
            </td>
            <td class="py-3 px-4 text-gray-500 text-xs"><?= e($o['created_at']) ?></td>
            <td class="py-3 px-4 text-center">
              <form method="POST" class="inline-flex items-center gap-1">
                <input type="hidden" name="order_id" value="<?= e($o['id']) ?>">
                <select name="status" class="border rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                  <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                  <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>Diproses</option>
                  <option value="done" <?= $o['status']=='done'?'selected':'' ?>>Selesai</option>
                  <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Dibatalkan</option>
                </select>
                <button type="submit" name="update_status" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1 rounded-lg font-semibold">
                  💾 Simpan
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Popup -->
  <?php if ($message): ?>
  <div id="popup" class="popup fixed top-5 right-5 bg-blue-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold z-50">
    <?= $message ?>
  </div>
  <script>
    const popup = document.getElementById('popup');
    setTimeout(() => {
      popup.style.opacity = '0';
      popup.style.transform = 'scale(0.9)';
      setTimeout(() => popup.remove(), 400);
    }, 3000);
  </script>
  <?php endif; ?>

  <script>
    // Toggle menu mobile
    const menuBtn = document.getElementById('menuBtn');
    const dropdown = document.getElementById('dropdownMenu');
    let open = false;
    menuBtn.addEventListener('click', () => {
      open = !open;
      dropdown.classList.toggle('hidden', !open);
    });
  </script>
</body>
</html>
