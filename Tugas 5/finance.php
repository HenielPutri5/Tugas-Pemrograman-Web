<?php
declare(strict_types=1);
require_once './Transaction.php';

date_default_timezone_set('Asia/Makassar'); 

session_start();

// Inisialisasi awal saldo dan riwayat jika baru pertama kali diakses
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verifikasi CSRF Token
    $postToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postToken)) {
        die('Kesalahan Keamanan: Token CSRF tidak cocok.');
    }

    $type = trim($_POST['type'] ?? '');
    $amountRaw = trim($_POST['amount'] ?? '');

    // Validasi jenis transaksi
    if (!in_array($type, ['deposit', 'withdraw'])) {
        $errors[] = 'Jenis transaksi yang dipilih tidak valid.';
    }

    // Validasi jumlah transaksi (harus angka desimal positif)
    if (!is_numeric($amountRaw) || (float)$amountRaw <= 0) {
        $errors[] = 'Jumlah transaksi harus berupa angka desimal positif yang lebih besar dari 0.';
    }

    if (empty($errors)) {
        $amount = (float)$amountRaw;
        $id = uniqid('TRX-'); // Pembuatan ID transaksi acak
        
        $transaction = new Transaction($id, $type, $amount);
        $result = $transaction->process();

        if ($result === true) {
            $label = $type === 'deposit' ? 'Deposit' : 'Penarikan';
            $successMessage = "Transaksi {$label} sebesar Rp " . number_format($amount, 2, ',', '.') . " berhasil diproses!";
            
            // Regenerasi CSRF token setelah operasi berhasil
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            // Menangkap pesan error dari method process()
            $errors[] = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

    <div class="container" style="max-width: 700px;">
        
        <!-- Papan Saldo -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body bg-success text-white text-center rounded">
                <p class="mb-1">Saldo Saat Ini</p>
                <h2 class="fw-bold mb-0">Rp <?= number_format($_SESSION['balance'], 2, ',', '.') ?></h2>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h1 class="h5 mb-0">Pemrosesan Transaksi</h1>
            </div>
            <div class="card-body">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <form action="./finance.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" disabled selected>Pilih jenis transaksi...</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdraw">Penarikan</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="amount" class="form-label">Jumlah Transaksi (Rp)</label>
                        <!-- Mengizinkan desimal pada level input HTML -->
                        <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required placeholder="Contoh: 1500000">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Proses Transaksi</button>
                </form>
            </div>
        </div>

        <!-- Riwayat Transaksi -->
        <?php if (!empty($_SESSION['transactions'])): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h2 class="h5 mb-0">Riwayat Transaksi</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Waktu</th>
                                    <th>Jenis</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($_SESSION['transactions']) as $trx): ?>
                                    <tr>
                                        <!-- Pencegahan XSS dengan htmlspecialchars -->
                                        <td><small class="text-muted"><?= htmlspecialchars($trx['id']) ?></small></td>
                                        <td><?= htmlspecialchars($trx['date']) ?></td>
                                        <td>
                                            <?php if ($trx['type'] === 'deposit'): ?>
                                                <span class="badge bg-success">Deposit</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Penarikan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">Rp <?= number_format($trx['amount'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>