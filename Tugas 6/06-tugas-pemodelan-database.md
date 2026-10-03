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

## 2. Identifikasi Entitas dan Atribut
### 2.1 Entitas Mahasiswa

| Atribut | Keterangan | Kunci |
|---|---|---|
| nim | Nomor Induk Mahasiswa | **PK** |
| nama_mahasiswa | Nama lengkap mahasiswa | |
| program_studi | Program studi mahasiswa | |
| angkatan | Tahun masuk | |
| email | Email mahasiswa (unik) | |
| no_hp | Nomor telepon | |

### 2.2 Entitas Penerbit

| Atribut | Keterangan | Kunci |
|---|---|---|
| id_penerbit | Kode penerbit | **PK** |
| nama_penerbit | Nama penerbit | |
| alamat | Alamat penerbit | |
| kota | Kota penerbit | |
| telepon | Nomor telepon penerbit | |

### 2.3 Entitas Buku

| Atribut | Keterangan | Kunci |
|---|---|---|
| id_buku | Kode buku | **PK** |
| isbn | Nomor ISBN (unik) | |
| judul | Judul buku | |
| pengarang | Nama pengarang | |
| tahun_terbit | Tahun terbit | |
| kategori | Kategori/subjek buku | |
| jumlah_stok | Jumlah eksemplar tersedia | |
| id_penerbit | Penerbit buku | **FK** → penerbit |

### 2.4 Entitas Transaksi Peminjaman

| Atribut | Keterangan | Kunci |
|---|---|---|
| id_transaksi | Kode transaksi | **PK** |
| nim | Mahasiswa peminjam | **FK** → mahasiswa |
| id_buku | Buku yang dipinjam | **FK** → buku |
| tgl_pinjam | Tanggal peminjaman | |
| tgl_jatuh_tempo | Batas tanggal pengembalian | |
| tgl_kembali | Tanggal pengembalian aktual (`NULL` jika belum kembali) | |
| status_pinjam | `DIPINJAM`, `DIKEMBALIKAN`, atau `TERLAMBAT` | |
| denda | Denda keterlambatan (rupiah) | |

### 2.5 Ringkasan Primary Key dan Foreign Key

| Tabel | Primary Key | Foreign Key |
|---|---|---|
| mahasiswa | nim | - |
| penerbit | id_penerbit | - |
| buku | id_buku | id_penerbit → penerbit(id_penerbit) |
| peminjaman | id_transaksi | nim → mahasiswa(nim); id_buku → buku(id_buku) |

## 3. Simulasi Normalisasi
### 3.1 Bentuk Tidak Normal (UNF)

Data mentah dari kartu/slip peminjaman disimpan dalam satu tabel besar. Satu baris dapat berisi beberapa buku sekaligus dalam satu sel (*repeating group*).

**Atribut UNF:** nim, nama_mahasiswa, program_studi, email, {id_buku, judul, isbn, pengarang, id_penerbit, nama_penerbit, kota_penerbit, tgl_pinjam, tgl_jatuh_tempo, tgl_kembali, status_pinjam, denda}

> Atribut lain (angkatan, no_hp, tahun_terbit, kategori, jumlah_stok, alamat, telepon) tidak ditampilkan agar tabel ringkas. Atribut tersebut mengikuti determinannya pada tahap berikutnya.

| nim | nama_mahasiswa | program_studi | email | id_buku | judul | isbn | pengarang | id_penerbit | nama_penerbit | kota_penerbit | tgl_pinjam | tgl_jatuh_tempo | tgl_kembali | status_pinjam | denda |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1012401 | Rizky Putra | Teknik Informatika | Putrariz22@kampus.ac.id | B001, B002 | Basis Data, Algoritma dan Pemrograman | 978-602-1234-01-1, 978-602-1234-02-8 | Andi Fahri, Burhan Ahmad | P01, P02 | Informatika Bandung, Andi Offset | Bandung, Yogyakarta | 2026-09-01, 2026-09-01 | 2026-09-08, 2026-09-08 | 2026-09-07, 2026-09-10 | DIKEMBALIKAN, TERLAMBAT | 0, 2000 |
| 1012402 | Putri Ayu | Sistem Informasi | Putri4yu@kampus.ac.id | B002 | Algoritma dan Pemrograman | 978-602-1234-02-8 | Burhan Ahmad | P02 | Andi Offset | Yogyakarta | 2026-09-10 | 2026-09-17 | NULL | DIPINJAM | 0 |
| 1012305 | Jingga Mawar | Teknik Informatika | Rosee53@kampus.ac.id | B003 | Jaringan Komputer | 978-602-1234-03-5 | Uan Santoso | P03 | Erlangga | Jakarta | 2026-09-15 | 2026-09-22 | NULL | DIPINJAM | 0 |

**Masalah pada UNF:**

- Sel berisi banyak nilai (tidak atomik), sehingga sulit dicari dan diperbarui.
- Terjadi pengulangan data (misalnya data buku B002 muncul di dua baris).

### 3.2 Bentuk Normal Pertama (1NF)

**Syarat 1NF:**

1. Setiap sel hanya berisi satu nilai (atomik).
2. Tidak ada kelompok atribut yang berulang.
3. Setiap baris dapat diidentifikasi dengan primary key.

**Tindakan:** baris dengan banyak buku dipecah menjadi satu baris per buku. Primary key gabungan: (nim, id_buku, tgl_pinjam).

| **nim** | nama_mahasiswa | program_studi | email | **id_buku** | judul | isbn | pengarang | id_penerbit | nama_penerbit | kota_penerbit | **tgl_pinjam** | tgl_jatuh_tempo | tgl_kembali | status_pinjam | denda |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1012401 | Rizky Putra | Teknik Informatika | Putrariz22@kampus.ac.id | B001 | Basis Data | 978-602-1234-01-1 | Andi Fahri | P01 | Informatika Bandung | Bandung | 2026-09-01 | 2026-09-08 | 2026-09-07 | DIKEMBALIKAN | 0 |
| 1012401 | Rizky Putra | Teknik Informatika | Putrariz22@kampus.ac.id | B002 | Algoritma dan Pemrograman | 978-602-1234-02-8 | Burhan Ahmad | P02 | Andi Offset | Yogyakarta | 2026-09-01 | 2026-09-08 | 2026-09-10 | TERLAMBAT | 2000 |
| 1012402 | Putri Ayu | Sistem Informasi | Putri4yu@kampus.ac.id | B002 | Algoritma dan Pemrograman | 978-602-1234-02-8 | Burhan Ahmad | P02 | Andi Offset | Yogyakarta | 2026-09-10 | 2026-09-17 | NULL | DIPINJAM | 0 |
| 1012305 | Jingga Mawar | Teknik Informatika | Rosee53@kampus.ac.id | B003 | Jaringan Komputer | 978-602-1234-03-5 | Uan Santoso | P03 | Erlangga | Jakarta | 2026-09-15 | 2026-09-22 | NULL | DIPINJAM | 0 |

**Status:** semua sel atomik dan tiap baris punya kunci. Tabel sudah 1NF, tetapi masih ada redundansi (data mahasiswa dan buku berulang).