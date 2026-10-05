<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/classes/GuestBook.php';

// ---------- Konfigurasi koneksi (sesuaikan dengan lingkungan lokal) ----------
const DB_HOST = 'localhost';
const DB_NAME = 'perpustakaan';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement asli
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Tidak dapat terhubung ke basis data.');
}

/** Sanitasi keluaran agar aman dari XSS. */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$guestBook = new GuestBook($pdo);
$galat     = [];
$nama = $email = $pesan = '';