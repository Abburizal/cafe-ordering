<?php
// app/helpers.php

// Pastikan file config sudah di-include sebelum helper ini
if (!isset($pdo)) {
    die("Config belum di-load. Pastikan include config.php sebelum helpers.php");
}

/**
 * Escape HTML untuk keamanan output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format rupiah (contoh: Rp 12.000)
 */
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Ambil role user yang sedang login
 */
function userRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Generate kode order unik (contoh: ORD-20251017-XYZ123)
 */
function generateOrderCode() {
    $rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    return 'ORD-' . date('Ymd') . '-' . $rand;
}

/**
 * Hitung total harga keranjang
 */
function cartTotal($pdo, $cart) {
    $total = 0;
    if (empty($cart)) return 0;

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $cart[$p['id']] ?? 0;
        $total += ($p['price'] * $qty);
    }

    return $total;
}

/**
 * Simpan order baru ke database
 */
function createOrder($pdo, $user_id, $table_id, $cart, $payment_method, $total, $midtrans_id = null) {
    $order_code = generateOrderCode();

    // Insert ke tabel orders
    $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, table_id, total, payment_method, midtrans_id, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$order_code, $user_id, $table_id, $total, $payment_method, $midtrans_id]);
    $order_id = $pdo->lastInsertId();

    // Insert detail item
    $placeholders = implode(',', array_fill(0, count($cart), '(?, ?, ?, ?)'));
    $values = [];
    $stmt2 = $pdo->prepare("SELECT id, price FROM products WHERE id IN (" . implode(',', array_fill(0, count($cart), '?')) . ")");
    $stmt2->execute(array_keys($cart));
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $cart[$p['id']];
        $values[] = $order_id;
        $values[] = $p['id'];
        $values[] = $qty;
        $values[] = $p['price'];
    }

    $sql = "INSERT INTO order_items (order_id, product_id, qty, price) VALUES $placeholders";
    $stmt3 = $pdo->prepare($sql);
    $stmt3->execute($values);

    return $order_code;
}

/**
 * Ambil daftar pesanan (untuk admin)
 */
function getOrders($pdo, $status = null) {
    $sql = "SELECT o.*, t.name AS table_name, u.name AS customer_name 
            FROM orders o 
            LEFT JOIN tables t ON o.table_id = t.id 
            LEFT JOIN users u ON o.user_id = u.id";
    if ($status) {
        $sql .= " WHERE o.status = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update status pesanan
 */
function updateOrderStatus($pdo, $order_code, $status) {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE order_code=?");
    return $stmt->execute([$status, $order_code]);
}

/**
 * Hitung total pendapatan hari ini
 */
function getTodayIncome($pdo) {
    $stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE DATE(created_at)=CURDATE() AND status IN ('processing','done')");
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Hitung total pendapatan bulanan
 */
function getMonthlyIncome($pdo) {
    $stmt = $pdo->query("SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as total 
                         FROM orders WHERE status IN ('processing','done') 
                         GROUP BY YEAR(created_at), MONTH(created_at)
                         ORDER BY year DESC, month DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Format Rupiah
function currency($number) {
    if (!is_numeric($number)) return 'Rp 0';
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Redirect helper

// Debug helper (optional)
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}
