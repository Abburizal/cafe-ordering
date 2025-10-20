<?php
require_once '../config/config.php';
require_once '../app/helpers.php';

// Jika SUDAH login dan role admin, langsung ke dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']); // diperbaiki
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // Cek user di database berdasarkan kolom 'name'
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

     if ($user && $password === $user['password']) {
    // Set session lengkap
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['username'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    header('Location: dashboard.php');
    exit;
} else {
    $error = "Username atau password salah.";
}

    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-100 to-yellow-50">
  <div class="bg-white shadow-2xl rounded-2xl w-full max-w-md p-8 border border-orange-200">
    <h1 class="text-3xl font-bold text-center text-orange-600 mb-6">🍽️ Login Admin</h1>
    
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center text-sm">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label class="block text-gray-600 mb-1 font-medium">Username</label>
        <input type="text" name="username" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <div>
        <label class="block text-gray-600 mb-1 font-medium">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 outline-none">
      </div>
      <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 rounded-lg transition-all">
        Masuk
      </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">
     <a href="register_admin.php">register</a>
    </p>
  </div>
</body>
</html>
