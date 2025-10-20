<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers.php';

// Jika ada parameter table (?table=1)
if (isset($_GET['table'])) {
    $_SESSION['table_id'] = (int)$_GET['table'];
    header('Location: menu.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Scan QR Meja - RestoKu</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@tailwindcss/forms"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-pink-50 min-h-screen flex flex-col items-center justify-center text-gray-800">
  <div class="bg-white shadow-xl rounded-2xl p-8 max-w-lg w-full text-center">
    <img src="https://cdn-icons-png.flaticon.com/512/833/833281.png" alt="QR Code" class="w-20 mx-auto mb-4 animate-bounce">
    
    <h1 class="text-3xl font-extrabold mb-2 text-indigo-700">Selamat Datang di RestoKu 🍽️</h1>
    <p class="text-gray-600 mb-6">Silakan <span class="font-medium text-indigo-600">scan QR</span> yang ada di meja Anda.  
    Untuk simulasi, pilih meja di bawah ini:</p>

    <!-- daftar meja -->
    <div class="grid grid-cols-2 gap-4 mt-6">
      <a href="index.php?table=1" class="rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 text-white py-3 font-semibold hover:scale-105 hover:shadow-lg transition transform">🪑 Meja 1</a>
      <a href="index.php?table=2" class="rounded-xl bg-gradient-to-r from-pink-500 to-red-500 text-white py-3 font-semibold hover:scale-105 hover:shadow-lg transition transform">🪑 Meja 2</a>
      <a href="index.php?table=3" class="rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 text-white py-3 font-semibold hover:scale-105 hover:shadow-lg transition transform">🪑 Meja 3</a>
      <a href="index.php?table=4" class="rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-3 font-semibold hover:scale-105 hover:shadow-lg transition transform">🪑 Meja 4</a>
    </div>

    <div class="mt-8 text-sm text-gray-500">
      <i data-feather="info"></i> Jika QR rusak, silakan hubungi pelayan kami.
    </div>
  </div>

  <script>feather.replace();</script>
</body>
</html>
