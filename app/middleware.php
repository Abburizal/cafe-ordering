<?php
// app/middleware.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mengecek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Mengecek apakah user adalah admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Middleware proteksi halaman admin
 */
function require_admin() {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}
