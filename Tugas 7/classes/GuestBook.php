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

        return $galat;
    }
}
