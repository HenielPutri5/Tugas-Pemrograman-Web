<?php

declare(strict_types=1);

class Transaction
{
    public function __construct(
        private string $id,
        private string $type,
        private float $amount
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function process(): bool|string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Inisialisasi saldo di sesi jika belum ada
        if (!isset($_SESSION['balance'])) {
            $_SESSION['balance'] = 0.0;
        }
        if (!isset($_SESSION['transactions'])) {
            $_SESSION['transactions'] = [];
        }

        // Ekspresi match untuk mencocokkan jenis transaksi
        $processResult = match ($this->type) {
            'deposit' => $this->handleDeposit(),
            'withdraw' => $this->handleWithdraw(),
            default => 'Jenis transaksi tidak valid.'
        };

        // Jika berhasil, simpan ke riwayat transaksi
        if ($processResult === true) {
            $_SESSION['transactions'][] = [
                'id' => $this->id,
                'type' => $this->type,
                'amount' => $this->amount,
                'date' => date('Y-m-d H:i:s')
            ];
            return true;
        }

        // Kembalikan pesan error jika gagal (misal saldo tidak cukup)
        return $processResult;
    }

    private function handleDeposit(): bool
    {
        $_SESSION['balance'] += $this->amount;
        return true;
    }

    private function handleWithdraw(): bool|string
    {
        if ($_SESSION['balance'] < $this->amount) {
            return 'Penarikan ditolak: Saldo dalam sesi tidak mencukupi.';
        }
        $_SESSION['balance'] -= $this->amount;
        return true;
    }
}