<?php
declare(strict_types=1);

/**
 * Kelas GuestBook
 * Mengelola penyimpanan dan pengambilan pesan buku tamu lewat PDO.
 * Seluruh query memakai prepared statements.
 */
class GuestBook
{
    public const MAKS_NAMA = 100;
    public const MAKS_EMAIL = 100;
    public const MAKS_PESAN = 1000;
    public const MIN_PESAN  = 5;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Validasi masukan.
     * @return array<string,string> pesan galat per kolom (kosong jika valid)
     */
    public function validasi(string $nama, string $email, string $pesan): array
    {
        $galat = [];

        if ($nama === '') {
            $galat['nama'] = 'Nama tidak boleh kosong.';
        } elseif (mb_strlen($nama) > self::MAKS_NAMA) {
            $galat['nama'] = 'Nama maksimal ' . self::MAKS_NAMA . ' karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $galat['email'] = 'Format email tidak valid.';
        } elseif (mb_strlen($email) > self::MAKS_EMAIL) {
            $galat['email'] = 'Email maksimal ' . self::MAKS_EMAIL . ' karakter.';
        }

        if (mb_strlen($pesan) < self::MIN_PESAN) {
            $galat['pesan'] = 'Pesan minimal ' . self::MIN_PESAN . ' karakter.';
        } elseif (mb_strlen($pesan) > self::MAKS_PESAN) {
            $galat['pesan'] = 'Pesan maksimal ' . self::MAKS_PESAN . ' karakter.';
        }

        return $galat;
    }
}
