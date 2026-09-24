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

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $id = uniqid('TRX-');

        $transaction = new Transaction($id, $type, $amount);
        $result = $transaction->process();

        if ($result === true) {
            $label = $type === 'deposit' ? 'Deposit' : 'Penarikan';
            $successMessage = "Transaksi {$label} sebesar Rp " . number_format($amount, 2, ',', '.') . " berhasil diproses!";
        } else {
            $errors[] = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Manajemen Keuangan</title>
</head>
<body>
    <h1>Saldo: <?= $_SESSION['balance'] ?></h1>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <p><?= $successMessage ?></p>
    <?php endif; ?>

    <form action="./finance.php" method="POST">
        <select name="type">
            <option value="" disabled selected>Pilih jenis transaksi...</option>
            <option value="deposit">Deposit</option>
            <option value="withdraw">Penarikan</option>
        </select>
        <input type="number" step="0.01" min="0.01" name="amount">
        <button type="submit">Proses</button>
    </form>
</body>
</html>