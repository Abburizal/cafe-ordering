<?php
session_start();

// Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cafe_ordering');   
define('DB_USER', 'root');     
define('DB_PASS', '');        

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Midtrans (sandbox)
define('MIDTRANS_SERVER_KEY', 'YOUR_MIDTRANS_SERVER_KEY');
define('MIDTRANS_IS_PRODUCTION', false); 
define('BASE_URL', 'http://localhost/cafe-ordering/public');
