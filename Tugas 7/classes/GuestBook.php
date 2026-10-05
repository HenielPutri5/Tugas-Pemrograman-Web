<?php
declare(strict_types=1);

/**
 * Kelas GuestBook
 * Mengelola penyimpanan dan pengambilan pesan buku tamu lewat PDO.
 * Seluruh query memakai prepared statements.
 */
class GuestBook
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}