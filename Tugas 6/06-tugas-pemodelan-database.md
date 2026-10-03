# Tugas Mandiri Modul 6: Perancangan ERD E-Library Kampus

| Keterangan | Isian |
|---|---|
| Nama | Heniel Putri Tangko Ramba Padang |
| NIM | D121241044 |
| Link Repositori | https://github.com/HenielPutri5/Tugas-Pemrograman-Web |

## 1. Skenario
Dirancang basis data relasional untuk sistem peminjaman buku perpustakaan kampus (E-Library). Sistem mencatat:

- data mahasiswa (peminjam),
- data buku beserta penerbitnya,
- riwayat peminjaman dan pengembalian buku.

### Aturan Bisnis (Asumsi Perancangan)

1. Satu penerbit dapat menerbitkan banyak buku, tetapi satu buku hanya diterbitkan oleh satu penerbit.
2. Satu mahasiswa dapat melakukan banyak transaksi peminjaman, dan satu transaksi hanya dimiliki oleh satu mahasiswa.
3. Satu buku dapat dipinjam berkali-kali (pada waktu berbeda), dan satu transaksi hanya mencatat satu judul buku. Jika mahasiswa meminjam dua buku sekaligus, tercatat dua transaksi.
4. Hubungan Mahasiswa dan Buku bersifat many-to-many, diselesaikan oleh entitas asosiatif Transaksi Peminjaman.
5. Lama peminjaman standar 7 hari, denda keterlambatan Rp1.000 per hari.
6. `tgl_kembali` bernilai `NULL` selama buku belum dikembalikan.
> Seluruh data contoh dalam dokumen ini bersifat **fiktif** dan dibuat untuk keperluan simulasi.