# E-Library Kampus: Perancangan ERD

Tugas Mandiri Modul 6, Pemodelan Basis Data.


**Nama** : Heniel Putri 

**NIM** : D121241044

## Ringkasan

Folder ini berisi rancangan basis data relasional untuk sistem peminjaman buku perpustakaan kampus. Rancangannya terdiri dari empat tabel, yaitu mahasiswa, penerbit, buku, dan peminjaman, yang sudah dinormalisasi sampai bentuk 3NF.

Satu asumsi penting dalam rancangan ini: satu transaksi hanya mencatat satu buku. Jika mahasiswa meminjam dua buku sekaligus, tercatat dua transaksi. Asumsi ini dan aturan bisnis lainnya dijelaskan di bagian 1 dokumen utama.

## Relasi Antar Tabel

| Relasi | Jenis | Kunci penghubung |
|---|---|---|
| penerbit → buku | 1 : N | `buku.id_penerbit` |
| mahasiswa → peminjaman | 1 : N | `peminjaman.nim` |
| buku → peminjaman | 1 : N | `peminjaman.id_buku` |

Hubungan mahasiswa dan buku bersifat many-to-many, sehingga diselesaikan lewat tabel `peminjaman` sebagai penghubung.

## Alat yang Dipakai

- **VS Code** untuk menulis dokumen Markdown dan melihat pratinjaunya
- **Mermaid** (`erDiagram`) untuk menuliskan diagram ERD
- **Mermaid Live Editor** untuk memeriksa diagram dan mengekspornya ke `ERD.png`
- **Git dan GitHub** untuk menyimpan riwayat pengerjaan secara bertahap

## Transparansi Penggunaan AI

Saya memakai Claude (Anthropic, model Claude Sonnet 5.5) sebagai alat bantu untuk menyusun draf awal rancangan: identifikasi entitas, simulasi normalisasi, tabel akhir, dan kode diagram Mermaid.

Setelah draf awal tersedia, saya mengerjakan sendiri hal-hal berikut:

- mengganti data contoh (nama mahasiswa, NIM, email, dan pengarang buku) dengan data buatan saya sendiri;
- membaca dan memeriksa tiap bagian, lalu merapikan atau menghapus bagian yang tidak saya perlukan;
- memastikan diagram Mermaid dapat tampil dan mengekspornya menjadi gambar;
- menyusun pengerjaan menjadi commit-commit bertahap per bagian dokumen.