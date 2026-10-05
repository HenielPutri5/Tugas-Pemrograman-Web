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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenKiriman = $_POST['csrf_token'] ?? '';

    if (!is_string($tokenKiriman) || !hash_equals($_SESSION['csrf_token'], $tokenKiriman)) {
        http_response_code(403);
        exit('Permintaan ditolak: token CSRF tidak valid.');
    }

    $nama  = trim((string) ($_POST['nama']  ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $pesan = trim((string) ($_POST['pesan'] ?? ''));

    $galat = $guestBook->validasi($nama, $email, $pesan);

    
    if (!$galat) {
        try {
            $guestBook->simpan($nama, $email, $pesan);
            $_SESSION['sukses']     = 'Terima kasih, pesan Anda sudah tersimpan.';
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // token baru
            header('Location: guestbook.php'); // Post/Redirect/Get: cegah kirim ganda saat refresh
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $galat['umum'] = 'Pesan gagal disimpan. Silakan coba lagi.';
        }
    }
}

$sukses = $_SESSION['sukses'] ?? '';
unset($_SESSION['sukses']);

try {
    $daftarPesan = $guestBook->semua();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $daftarPesan = [];
    $galat['umum'] = 'Daftar pesan tidak dapat dimuat.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu Perpustakaan</title>
</head>
<body>
    <header class="topbar">
        <div class="wrap">
            <div class="brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Buku Tamu
            </div>
            <span class="tagline">Perpustakaan</span>
        </div>
    </header>

    <main class="wrap layout">
        <section class="card form-card" aria-labelledby="judul-form">
            <h2 id="judul-form">Tulis pesan</h2>
            <p class="hint">Kesan, saran, atau pertanyaan Anda untuk perpustakaan.</p>

            <?php if ($sukses): ?>
                <div class="alert alert-ok" role="status"><?= e($sukses) ?></div>
            <?php endif; ?>
            <?php if (isset($galat['umum'])): ?>
                <div class="alert alert-error" role="alert"><?= e($galat['umum']) ?></div>
            <?php endif; ?>

            <form method="post" action="guestbook.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" maxlength="<?= GuestBook::MAKS_NAMA ?>"
                       value="<?= e($nama) ?>" <?= isset($galat['nama']) ? 'class="invalid" aria-invalid="true"' : '' ?>>
                <?php if (isset($galat['nama'])): ?><p class="error"><?= e($galat['nama']) ?></p><?php endif; ?>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="<?= GuestBook::MAKS_EMAIL ?>"
                       value="<?= e($email) ?>" <?= isset($galat['email']) ? 'class="invalid" aria-invalid="true"' : '' ?>>
                <?php if (isset($galat['email'])): ?><p class="error"><?= e($galat['email']) ?></p><?php endif; ?>

                <label for="pesan">Pesan</label>
                <textarea id="pesan" name="pesan" rows="5" maxlength="<?= GuestBook::MAKS_PESAN ?>"
                          <?= isset($galat['pesan']) ? 'class="invalid" aria-invalid="true"' : '' ?>><?= e($pesan) ?></textarea>
                <?php if (isset($galat['pesan'])): ?><p class="error"><?= e($galat['pesan']) ?></p><?php endif; ?>

                <button type="submit" class="btn">Kirim pesan</button>
            </form>
        </section>
    </main>
</body>
</html>