<?php
require_once '../config/config.php';
require_once '../app/helpers.php';

// Hapus atau komentar kode redirect ke login di sini
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: login.php');
//     exit;
// }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($name === '' || $email === '' || $password === '') {
        $error = "Semua field wajib diisi.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$name, $email, $password]);
        $success = "Admin baru berhasil diregistrasi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Registrasi Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-100 to-yellow-50">
  <div class="bg-white shadow-2xl rounded-2xl w-full max-w-md p-8 border border-orange-200">
    <h1 class="text-3xl font-bold text-center text-orange-600 mb-6">🧑‍💼 Register Admin</h1>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center text-sm">
        <?= e($error) ?>
      </div>
    <?php elseif ($success): ?>
      <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center text-sm">
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label class="block text-gray-600 mb-1 font-medium">Nama</label>
        <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-gray-600 mb-1 font-medium">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-gray-600 mb-1 font-medium">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 rounded-lg transition-all">
        Daftar
      </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">
      Sudah punya akun? <a href="login.php" class="text-orange-500 hover:underline">Login</a>
    </p>
  </div>
</body>
</html>
