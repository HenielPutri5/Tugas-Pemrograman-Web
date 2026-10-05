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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f3f5f8;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --accent: #0f766e;
            --accent-dark: #0b5c56;
            --accent-soft: #d9f3ef;
            --danger: #dc2626;
            --danger-soft: #fdecec;
            --ok: #166534;
            --ok-soft: #e5f6ea;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); line-height: 1.55;
               font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

        .topbar { background: var(--surface); border-bottom: 1px solid var(--line); }
        .topbar .wrap { display: flex; align-items: center; justify-content: space-between; height: 60px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.1rem; }
        .brand svg { width: 30px; height: 30px; padding: 6px; border-radius: 9px; background: var(--accent); color: #fff; }
        .tagline { color: var(--muted); font-size: .9rem; }

        .layout { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 24px;
                  align-items: start; padding-top: 28px; padding-bottom: 56px; }
        .card { background: var(--surface); border: 1px solid var(--line); border-radius: 16px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 28px -20px rgba(15, 23, 42, .25); }
        .form-card { padding: 22px; position: sticky; top: 20px; }
        h2 { font-size: 1.15rem; margin: 0 0 4px; }
        .hint { margin: 0 0 6px; color: var(--muted); font-size: .9rem; }

        label { display: block; font-weight: 600; font-size: .9rem; margin: 16px 0 6px; }
        input, textarea { width: 100%; padding: 10px 12px; font: inherit; color: var(--ink);
                          background: #f8fafc; border: 1.5px solid var(--line); border-radius: 10px;
                          transition: border-color .15s, background .15s; }
        textarea { resize: vertical; min-height: 120px; }
        input:focus-visible, textarea:focus-visible { outline: none; background: #fff;
                          border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-soft); }
        .invalid { border-color: var(--danger); }
        .error { color: var(--danger); font-size: .85rem; margin: 6px 0 0; }

        .btn { margin-top: 20px; width: 100%; padding: 12px 18px; font: inherit; font-weight: 600;
               color: #fff; background: var(--accent); border: 0; border-radius: 10px; cursor: pointer;
               transition: background .15s; }
        .btn:hover { background: var(--accent-dark); }
        .btn:focus-visible, .hapus:focus-visible { outline: 3px solid var(--accent-soft); outline-offset: 2px; }

        .alert { padding: 11px 14px; border-radius: 10px; margin: 14px 0 0; font-size: .9rem; }
        .alert-ok { background: var(--ok-soft); color: var(--ok); }
        .alert-error { background: var(--danger-soft); color: var(--danger); }

        .list-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .list-head h2 { margin: 0; font-size: 1.25rem; }
        .count { background: var(--accent-soft); color: var(--accent-dark); font-weight: 600;
                 font-size: .8rem; padding: 4px 12px; border-radius: 999px; }
        .tabel-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { text-align: left; font-weight: 600; font-size: .8rem; color: var(--muted);
             padding: 14px 16px; border-bottom: 1px solid var(--line); }
        td { padding: 16px; border-bottom: 1px solid var(--line); vertical-align: top; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #f8fafc; }
        .num { color: var(--muted); width: 40px; }
        .who { display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .avatar { flex: none; width: 34px; height: 34px; border-radius: 50%; background: var(--accent-soft);
                  color: var(--accent-dark); display: grid; place-items: center; font-weight: 700; }
        .mail { color: var(--muted); min-width: 220px; overflow-wrap: break-word; }
        .pesan { min-width: 240px; }
        .tgl { white-space: nowrap; color: var(--muted); font-size: .9rem; font-variant-numeric: tabular-nums; }
        .aksi form { margin: 0; }
        .hapus { padding: 6px 12px; font: inherit; font-size: .85rem; font-weight: 600; color: var(--danger);
                 background: transparent; border: 1.5px solid #f3c4c4; border-radius: 8px; cursor: pointer;
                 transition: background .15s; }
        .hapus:hover { background: var(--danger-soft); }
        .kosong { text-align: center; color: var(--muted); padding: 40px 16px; }

        @media (max-width: 860px) {
            .layout { grid-template-columns: 1fr; }
            .form-card { position: static; }
            .tagline { display: none; }
        }
    </style>
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
        
        <section aria-labelledby="judul-daftar">
            <div class="list-head">
                <h2 id="judul-daftar">Daftar pesan</h2>
                <span class="count"><?= count($daftarPesan) ?> pesan</span>
            </div>
            <div class="card tabel-wrap">
                <table>
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>Email</th><th>Pesan</th><th>Tanggal kirim</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$daftarPesan): ?>
                        <tr><td colspan="5" class="kosong">Belum ada pesan. Tulis pesan pertama lewat formulir.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daftarPesan as $i => $baris): ?>
                            <tr>
                                <td class="num"><?= $i + 1 ?></td>
                                <td>
                                    <div class="who">
                                        <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($baris['nama'], 0, 1))) ?></span>
                                        <?= e($baris['nama']) ?>
                                    </div>
                                </td>
                                <td class="mail"><?= e($baris['email']) ?></td>
                                <td class="pesan"><?= nl2br(e($baris['pesan'])) ?></td>
                                <td class="tgl"><?= e(date('d-m-Y H:i', strtotime($baris['tanggal_kirim']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>