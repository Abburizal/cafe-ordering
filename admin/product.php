<?php
require_once '../config/config.php';
require_once '../app/helpers.php';
require_once '../app/middleware.php';

require_admin();

$message = '';

// === Tambah Produk ===
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $image = $_FILES['image']['name'] ?? '';

    if ($name !== '' && $price !== '' && $image !== '') {
        $targetDir = "../public/assets/images/";
        $targetFile = $targetDir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

        $stmt = $pdo->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $image]);
        $message = "🎉 Produk baru berhasil ditambahkan!";
    } else {
        $message = "⚠️ Semua kolom wajib diisi!";
    }
}

// === Hapus Produk ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $message = "🗑️ Produk berhasil dihapus!";
}

// Ambil semua produk
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$adminName = e($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Produk - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes popup {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.05); opacity: 1; }
      100% { transform: scale(1); }
    }
    .popup { animation: popup 0.4s ease-out; }
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
        <a href="product.php" class="text-blue-600 font-semibold">Produk</a>
        <a href="orders.php" class="hover:text-blue-600">Orders</a>
      </nav>

      <!-- Tombol kanan -->
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
        <a href="product.php" class="block px-4 py-2 hover:bg-gray-100 text-blue-600 font-semibold">Produk</a>
        <a href="orders.php" class="block px-4 py-2 hover:bg-gray-100">Orders</a>
        <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
      </div>
    </div>
  </header>

  <!-- Konten -->
  <main class="pt-24 px-4 md:px-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Manajemen Produk</h2>

    <!-- Form Tambah Produk -->
    <div class="bg-white rounded-2xl p-6 shadow mb-8">
      <h3 class="text-lg font-semibold mb-4 text-gray-700">Tambah Produk Baru</h3>
      <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="name" placeholder="Nama Produk" required class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
        <input type="number" name="price" placeholder="Harga" required class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
        <div class="space-y-8 max-w-md mx-auto">
      <input type="file" name="image" accept="image/*"
        class="w-full text-slate-500 font-medium text-sm bg-gray-100 file:cursor-pointer cursor-pointer file:border-0 file:py-2 file:px-4 file:mr-4 file:bg-gray-800 file:hover:bg-gray-700 file:text-white rounded" />
</div>
        <button type="submit" name="add" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 font-semibold transition-all">
          ➕ Tambah
        </button>
      </form>
    </div>

    <!-- Tabel Produk -->
    <div class="bg-white rounded-2xl shadow-md p-6 overflow-x-auto">
      <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden">
        <thead class="bg-stone-200 text-gray-700">
          <tr>
            <th class="py-3 px-4 text-left">#</th>
            <th class="py-3 px-4 text-left">Gambar</th>
            <th class="py-3 px-4 text-left">Nama Produk</th>
            <th class="py-3 px-4 text-left">Harga</th>
            <th class="py-3 px-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="5" class="text-center text-gray-500 py-6">😴 Belum ada produk...</td>
          </tr>
        <?php else: foreach ($products as $p): ?>
          <tr class="border-t hover:bg-gray-50 transition">
            <td class="py-3 px-4"><?= $p['id'] ?></td>
            <td class="py-3 px-4">
              <img src="../public/assets/images/<?= e($p['image']) ?>" alt="img" class="w-14 h-14 rounded-xl object-cover">
            </td>
            <td class="py-3 px-4 font-medium text-gray-700"><?= e($p['name']) ?></td>
            <td class="py-3 px-4 text-blue-600 font-semibold">Rp<?= number_format($p['price'], 0, ',', '.') ?></td>
            <td class="py-3 px-4 text-center">
              <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini? 😢')" class="text-red-500 hover:text-red-700 font-semibold">Hapus</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Popup kecil -->
  <?php if ($message): ?>
  <div id="popup" class="popup fixed bottom-5 right-5 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-semibold z-50">
    <?= e($message) ?>
  </div>
  <script>
    const popup = document.getElementById('popup');
    setTimeout(() => {
      popup.style.opacity = '0';
      popup.style.transform = 'scale(0.9)';
      setTimeout(() => popup.remove(), 500);
    }, 2500);
  </script>
  <?php endif; ?>

  <script>
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
