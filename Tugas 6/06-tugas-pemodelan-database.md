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

### 3.3 Bentuk Normal Kedua (2NF)

**Syarat 2NF:** sudah 1NF dan tidak ada ketergantungan parsial, yaitu atribut non-kunci tidak boleh bergantung hanya pada sebagian dari primary key gabungan.

**Analisis ketergantungan fungsional** (PK = nim, id_buku, tgl_pinjam):

| Ketergantungan | Jenis |
|---|---|
| nim → nama_mahasiswa, angkatan, program_studi, email, no_hp | **Parsial** (hanya bergantung pada `nim`) |
| id_buku → judul, isbn, pengarang, tahun_terbit, kategori, jumlah_stok, id_penerbit, nama_penerbit, alamat, kota_penerbit, telepon | **Parsial** (hanya bergantung pada `id_buku`) |
| (nim, id_buku, tgl_pinjam) → tgl_jatuh_tempo, tgl_kembali, status_pinjam, denda | **Penuh** |

**Tindakan:** pecah menjadi tiga tabel.

**Tabel Mahasiswa** (PK: nim)

| **nim** | nama_mahasiswa | angkatan | program_studi | email | no_hp |
|---|---|---|---|---|---|
| 1012401 | Rizky Putra | 2024 | Teknik Informatika | Putrariz22@kampus.ac.id | 081234560001 |
| 1012402 | Putri Ayu | 2024 | Sistem Informasi | Putri4yu@kampus.ac.id | 081234560002 |
| 1012305 | Jingga Mawar | 2023 | Teknik Informatika | Rosee53@kampus.ac.id | 081234560003 |

**Tabel Buku** (PK: id_buku)

| **id_buku** | judul | isbn | pengarang | tahun_terbit | kategori | jumlah_stok | id_penerbit | nama_penerbit | kota_penerbit |
|---|---|---|---|---|---|---|---|---|---|
| B001 | Basis Data | 978-602-1234-01-1 | Andi Fahri | 2020 | Basis Data | 5 | P01 | Informatika Bandung | Bandung |
| B002 | Algoritma dan Pemrograman | 978-602-1234-02-8 | Burhan Ahmad | 2019 | Pemrograman | 3 | P02 | Andi Offset | Yogyakarta |
| B003 | Jaringan Komputer | 978-602-1234-03-5 | Uan Santoso | 2021 | Jaringan | 4 | P03 | Erlangga | Jakarta |

**Tabel Peminjaman** (PK: nim, id_buku, tgl_pinjam)

| **nim** | **id_buku** | **tgl_pinjam** | tgl_jatuh_tempo | tgl_kembali | status_pinjam | denda |
|---|---|---|---|---|---|---|
| 1012401 | B001 | 2026-09-01 | 2026-09-08 | 2026-09-07 | DIKEMBALIKAN | 0 |
| 1012401 | B002 | 2026-09-01 | 2026-09-08 | 2026-09-10 | TERLAMBAT | 2000 |
| 1012402 | B002 | 2026-09-10 | 2026-09-17 | NULL | DIPINJAM | 0 |
| 1012305 | B003 | 2026-09-15 | 2026-09-22 | NULL | DIPINJAM | 0 |

**Status:** tidak ada lagi ketergantungan parsial, sehingga tabel sudah 2NF. Masih ada redundansi pada tabel Buku (data penerbit berulang untuk setiap buku dari penerbit yang sama).

### 3.4 Bentuk Normal Ketiga (3NF)

**Syarat 3NF:** sudah 2NF dan tidak ada ketergantungan transitif, yaitu atribut non-kunci tidak boleh bergantung pada atribut non-kunci lain.

**Analisis pada tabel Buku:**

```
id_buku → id_penerbit → nama_penerbit, alamat, kota, telepon
```

nama_penerbit, alamat, kota, dan telepon bergantung pada id_penerbit (bukan kunci), sehingga terjadi ketergantungan transitif.

**Tindakan:** pisahkan data penerbit ke tabel sendiri. id_penerbit tetap di tabel Buku sebagai Foreign Key.

**Tabel Penerbit** (PK: id_penerbit)

| **id_penerbit** | nama_penerbit | alamat | kota | telepon |
|---|---|---|---|---|
| P01 | Informatika Bandung | Jl. Palasari No. 12 | Bandung | 022-5550101 |
| P02 | Andi Offset | Jl. Beo No. 38 | Yogyakarta | 0274-5550202 |
| P03 | Erlangga | Jl. H. Baping No. 100 | Jakarta | 021-5550303 |

**Tabel Buku** (PK: id_buku, FK: id_penerbit)

| **id_buku** | judul | isbn | pengarang | tahun_terbit | kategori | jumlah_stok | *id_penerbit* |
|---|---|---|---|---|---|---|---|
| B001 | Basis Data | 978-602-1234-01-1 | Andi Fahri | 2020 | Basis Data | 5 | P01 |
| B002 | Algoritma dan Pemrograman | 978-602-1234-02-8 | Burhan Ahmad | 2019 | Pemrograman | 3 | P02 |
| B003 | Jaringan Komputer | 978-602-1234-03-5 | Uan Santoso | 2021 | Jaringan | 4 | P03 |

**Tabel Mahasiswa:** tidak berubah (sudah 3NF).

**Tabel Peminjaman:** ditambahkan *surrogate key* id_transaksi sebagai PK yang lebih ringkas. Kombinasi lama (nim, id_buku, tgl_pinjam) dijadikan batasan UNIQUE. Karena nim dan id_buku adalah FK, tabel ini menjadi penghubung antara Mahasiswa dan Buku.

| **id_transaksi** | *nim* | *id_buku* | tgl_pinjam | tgl_jatuh_tempo | tgl_kembali | status_pinjam | denda |
|---|---|---|---|---|---|---|---|
| T001 | 1012401 | B001 | 2026-09-01 | 2026-09-08 | 2026-09-07 | DIKEMBALIKAN | 0 |
| T002 | 1012401 | B002 | 2026-09-01 | 2026-09-08 | 2026-09-10 | TERLAMBAT | 2000 |
| T003 | 1012402 | B002 | 2026-09-10 | 2026-09-17 | NULL | DIPINJAM | 0 |
| T004 | 1012305 | B003 | 2026-09-15 | 2026-09-22 | NULL | DIPINJAM | 0 |

> **Catatan desain:** tgl_jatuh_tempo dan denda secara teknis dapat dihitung dari kolom lain. Keduanya sengaja disimpan sebagai data historis, karena kebijakan lama peminjaman dan tarif denda dapat berubah di masa depan tanpa boleh mengubah transaksi lampau.

**Status akhir:** seluruh tabel memenuhi 3NF (empat tabel: mahasiswa, penerbit, buku, peminjaman).

### 3.5 Ringkasan Alur Normalisasi

```
UNF  ──►  1NF  ──►  2NF  ──►  3NF
 │         │         │         │
 │         │         │         └─ Hilangkan ketergantungan transitif
 │         │         │            (pisahkan PENERBIT dari BUKU)
 │         │         └─ Hilangkan ketergantungan parsial
 │         │            (pisahkan MAHASISWA, BUKU, PEMINJAMAN)
 │         └─ Pecah repeating group, atomisasi nilai, tetapkan PK
 └─ Satu tabel besar dengan banyak nilai per sel
```
## 4. Rancangan Tabel Akhir (Dilengkapi Tipe Data)

Tipe data mengacu pada MySQL/MariaDB.

### 4.1 Tabel `mahasiswa`

| Kolom | Tipe Data | Konstrain | Keterangan |
|---|---|---|---|
| nim | VARCHAR(15) | **PRIMARY KEY** | Nomor Induk Mahasiswa |
| nama_mahasiswa | VARCHAR(100) | NOT NULL | Nama lengkap |
| angkatan | SMALLINT | NOT NULL | Tahun masuk |
| program_studi | VARCHAR(50) | NOT NULL | Program studi |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Email mahasiswa |
| no_hp | VARCHAR(15) | NULL | Nomor telepon |

### 4.2 Tabel `penerbit`

| Kolom | Tipe Data | Konstrain | Keterangan |
|---|---|---|---|
| id_penerbit | VARCHAR(10) | **PRIMARY KEY** | Kode penerbit |
| nama_penerbit | VARCHAR(100) | NOT NULL | Nama penerbit |
| alamat | VARCHAR(255) | NULL | Alamat lengkap |
| kota | VARCHAR(50) | NOT NULL | Kota penerbit |
| telepon | VARCHAR(20) | NULL | Nomor telepon |

### 4.3 Tabel `buku`

| Kolom | Tipe Data | Konstrain | Keterangan |
|---|---|---|---|
| id_buku | VARCHAR(10) | **PRIMARY KEY** | Kode buku |
| isbn | VARCHAR(17) | NOT NULL, UNIQUE | Nomor ISBN |
| judul | VARCHAR(200) | NOT NULL | Judul buku |
| pengarang | VARCHAR(100) | NOT NULL | Nama pengarang |
| tahun_terbit | SMALLINT | NOT NULL | Tahun terbit |
| kategori | VARCHAR(50) | NULL | Kategori buku |
| jumlah_stok | INT | NOT NULL, DEFAULT 0, CHECK (jumlah_stok >= 0) | Stok eksemplar |
| id_penerbit | VARCHAR(10) | NOT NULL, **FOREIGN KEY** → penerbit(id_penerbit) | Penerbit buku |

### 4.4 Tabel `peminjaman`

| Kolom | Tipe Data | Konstrain | Keterangan |
|---|---|---|---|
| id_transaksi | VARCHAR(10) | **PRIMARY KEY** | Kode transaksi |
| nim | VARCHAR(15) | NOT NULL, **FOREIGN KEY** → mahasiswa(nim) | Peminjam |
| id_buku | VARCHAR(10) | NOT NULL, **FOREIGN KEY** → buku(id_buku) | Buku yang dipinjam |
| tgl_pinjam | DATE | NOT NULL | Tanggal pinjam |
| tgl_jatuh_tempo | DATE | NOT NULL | Batas pengembalian |
| tgl_kembali | DATE | NULL | Tanggal kembali (NULL = belum kembali) |
| status_pinjam | ENUM('DIPINJAM','DIKEMBALIKAN','TERLAMBAT') | NOT NULL, DEFAULT 'DIPINJAM' | Status transaksi |
| denda | DECIMAL(10,2) | NOT NULL, DEFAULT 0.00 | Denda dalam rupiah |

Batasan tambahan: `UNIQUE (nim, id_buku, tgl_pinjam)` dan `CHECK (tgl_jatuh_tempo >= tgl_pinjam)`.

### 4.5 Skrip SQL (DDL)

```sql
CREATE DATABASE IF NOT EXISTS e_library_kampus;
USE e_library_kampus;

CREATE TABLE mahasiswa (
    nim             VARCHAR(15)  PRIMARY KEY,
    nama_mahasiswa  VARCHAR(100) NOT NULL,
    angkatan        SMALLINT     NOT NULL,
    program_studi   VARCHAR(50)  NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    no_hp           VARCHAR(15)
);

CREATE TABLE penerbit (
    id_penerbit     VARCHAR(10)  PRIMARY KEY,
    nama_penerbit   VARCHAR(100) NOT NULL,
    alamat          VARCHAR(255),
    kota            VARCHAR(50)  NOT NULL,
    telepon         VARCHAR(20)
);

CREATE TABLE buku (
    id_buku         VARCHAR(10)  PRIMARY KEY,
    isbn            VARCHAR(17)  NOT NULL UNIQUE,
    judul           VARCHAR(200) NOT NULL,
    pengarang       VARCHAR(100) NOT NULL,
    tahun_terbit    SMALLINT     NOT NULL,
    kategori        VARCHAR(50),
    jumlah_stok     INT          NOT NULL DEFAULT 0 CHECK (jumlah_stok >= 0),
    id_penerbit     VARCHAR(10)  NOT NULL,
    CONSTRAINT fk_buku_penerbit
        FOREIGN KEY (id_penerbit) REFERENCES penerbit (id_penerbit)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE peminjaman (
    id_transaksi     VARCHAR(10)  PRIMARY KEY,
    nim              VARCHAR(15)  NOT NULL,
    id_buku          VARCHAR(10)  NOT NULL,
    tgl_pinjam       DATE         NOT NULL,
    tgl_jatuh_tempo  DATE         NOT NULL,
    tgl_kembali      DATE         NULL,
    status_pinjam    ENUM('DIPINJAM','DIKEMBALIKAN','TERLAMBAT')
                                  NOT NULL DEFAULT 'DIPINJAM',
    denda            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT uq_peminjaman UNIQUE (nim, id_buku, tgl_pinjam),
    CONSTRAINT chk_tanggal CHECK (tgl_jatuh_tempo >= tgl_pinjam),
    CONSTRAINT fk_peminjaman_mahasiswa
        FOREIGN KEY (nim) REFERENCES mahasiswa (nim)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_peminjaman_buku
        FOREIGN KEY (id_buku) REFERENCES buku (id_buku)
        ON UPDATE CASCADE ON DELETE RESTRICT
);
```