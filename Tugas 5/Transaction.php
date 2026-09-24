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

        return $processResult;
    }

    private function handleDeposit(): bool
    {
        // TODO: implementasi logika deposit
        return true;
    }

    private function handleWithdraw(): bool|string
    {
        // TODO: implementasi logika penarikan dengan validasi saldo
        return true;
    }
}