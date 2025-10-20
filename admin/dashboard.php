<?php
require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';

require_admin();

// Ambil data ringkasan
$totalProduk = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPesanan = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Pendapatan hari ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE DATE(created_at)=?");
$stmt->execute([$today]);
$pendapatanHari = $stmt->fetchColumn() ?? 0;

// Pendapatan bulan ini
$month = date('Y-m');
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
$stmt->execute([$month]);
$pendapatanBulan = $stmt->fetchColumn() ?? 0;

$adminName = e($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes popup {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.05); opacity: 1; }
      100% { transform: scale(1); opacity: 1; }
    }
    .popup { animation: popup 0.4s ease-out; }
    .dropdown-enter { opacity: 0; transform: translateY(-10px); }
    .dropdown-enter-active { opacity: 1; transform: translateY(0); transition: all 0.3s ease; }
  </style>
</head>
<body class="bg-stone-100 min-h-screen">

  <!-- Navbar -->
  <header class="fixed top-3 left-0 right-0 z-50 flex justify-center">
    <div class="flex items-center justify-between w-[95%] md:w-[80%] lg:w-[70%] bg-white/80 backdrop-blur-lg border border-gray-200 rounded-3xl shadow px-5 py-3 relative">
      
      <!-- Logo -->
      <div class="flex items-center space-x-2">
        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" class="h-6" alt="">
      </div>

      <!-- Menu desktop -->
      <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <a href="dashboard.php" class="hover:text-blue-600">Dashboard</a>
        <a href="product.php" class="hover:text-blue-600">Produk</a>
        <a href="orders.php" class="hover:text-blue-600">Orders</a>
      </nav>

      <!-- Aksi kanan -->
      <div class="hidden md:flex items-center space-x-2">
        <a href="logout.php" class="px-3 py-1 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-100">Sign out</a>
        <a href="dashboard.php" class="px-3 py-1 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Dashboard</a>
      </div>

      <!-- Tombol menu mobile -->
      <button id="menuBtn" class="md:hidden flex items-center p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
        <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Dropdown mobile -->
      <div id="dropdownMenu" class="hidden absolute top-14 right-4 w-40 bg-white border border-gray-200 rounded-xl shadow-md py-2 text-sm">
        <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-100">Dashboard</a>
        <a href="product.php" class="block px-4 py-2 hover:bg-gray-100">Produk</a>
        <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100">Orders</a>
        <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
      </div>
    </div>
  </header>

  <!-- Konten -->
  <main class="pt-24 px-4 md:px-10">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Ringkasan Hari Ini</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <p class="text-gray-500 text-sm">Pendapatan Hari Ini</p>
        <h3 class="text-xl font-bold text-gray-800 mt-2">Rp<?= number_format($pendapatanHari, 0, ',', '.') ?></h3>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
        <h3 class="text-xl font-bold text-gray-800 mt-2">Rp<?= number_format($pendapatanBulan, 0, ',', '.') ?></h3>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <p class="text-gray-500 text-sm">Jumlah Produk</p>
        <h3 class="text-xl font-bold text-gray-800 mt-2"><?= $totalProduk ?></h3>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <p class="text-gray-500 text-sm">Total Pesanan</p>
        <h3 class="text-xl font-bold text-gray-800 mt-2"><?= $totalPesanan ?></h3>
      </div>
    </div>

    <div class="mt-8">
      <h2 class="text-xl font-bold mb-4 text-gray-800">Pesanan Terbaru</h2>
      <div class="overflow-x-auto bg-white rounded-xl shadow">
        <table class="min-w-full text-sm">
          <thead class="bg-stone-200">
            <tr>
              <th class="py-3 px-4 text-left">#</th>
              <th class="py-3 px-4 text-left">Meja</th>
              <th class="py-3 px-4 text-left">Total</th>
              <th class="py-3 px-4 text-left">Status</th>
              <th class="py-3 px-4 text-left">Metode</th>
              <th class="py-3 px-4 text-left">Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($orders as $o):
            ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="py-3 px-4"><?= $o['id'] ?></td>
                <td class="py-3 px-4"><?= $o['table_id'] ?></td>
                <td class="py-3 px-4">Rp<?= number_format($o['total'], 0, ',', '.') ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 rounded text-xs 
                    <?= $o['status']=='selesai' ? 'bg-green-100 text-green-600' : 
                        ($o['status']=='proses' ? 'bg-yellow-100 text-yellow-600' : 'bg-gray-100 text-gray-600') ?>">
                    <?= ucfirst($o['status']) ?>
                  </span>
                </td>
                <td class="py-3 px-4"><?= ucfirst($o['payment_method']) ?></td>
                <td class="py-3 px-4 text-gray-500 text-xs"><?= $o['created_at'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Popup kecil -->
  <div id="popup" class="popup fixed bottom-5 right-5 bg-stone-800 text-white px-4 py-2 rounded-lg shadow-md text-xs sm:text-sm">
    👋 Hai <b><?= $adminName ?></b>! Selamat datang kembali 😄
  </div>

  <script>
    // Popup auto-hide
    const popup = document.getElementById('popup');
    if (popup) {
      setTimeout(() => {
        popup.style.opacity = '0';
        popup.style.transform = 'scale(0.9)';
        setTimeout(() => popup.remove(), 400);
      }, 2500);
    }

    // Dropdown mobile toggle
    const menuBtn = document.getElementById('menuBtn');
    const dropdown = document.getElementById('dropdownMenu');
    let open = false;

    menuBtn.addEventListener('click', () => {
      open = !open;
      if (open) {
        dropdown.classList.remove('hidden');
        dropdown.classList.add('dropdown-enter-active');
      } else {
        dropdown.classList.add('hidden');
      }
    });
  </script>
</body>
</html>
