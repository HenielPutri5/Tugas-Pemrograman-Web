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