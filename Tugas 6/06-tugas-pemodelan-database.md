# Tugas 6: Pemodelan Database &vert; Perancangan ERD E-Library Kampus

## 1. Deskripsi Sistem
Sebuah sistem database yang digunakan untuk perancangan sistem E-Library kampus yang memuat audit peminjaman buku beserta statusnya. ERD ini memiliki 4 entitas utama yaitu:
- Mahasiswa: Peminjam Buku
- Buku: Objek yang dipinjam
- Penerbit: Penerbit dari setiap Buku
- Transaksi Peminjaman: Catatan dari setiap buku yang berisi tanggal pinjam, tanggal kembali, siapa yang meminjam, dan status buku (sudah dikembalikan atau belum)

## 2. Spesifikasi Entitas dan Atribut
| Entity | Atribute | Keterangan |
|---|---|---|
| Mahasiswa | `NIM`, `nama`, `departemen`, `no_hp`, `email` | `NIM = Primary Key` |
| Buku | `id_buku`, `id_penerbit`, `judul_buku`, `tahun_terbit` | `id_buku = Primary Key`, `id_penerbit = Foreign Key ke Penerbit` |
| Penerbit | `id_penerbit`, `nama_penerbit`, `kota_penerbit` | `id_penerbit = Primary Key` |
| Transaksi Peminjaman | `id_transaksi`, `NIM`, `id_buku`, `tanggal_pinjam`, `tanggal_kembali`, `tanggal_tenggat`, `status_pinjam` | `id_transaksi = Primary Key`, `id_buku = Foreign Key ke Buku`, `NIM = Foreign Key ke Mahasiswa` |

## 3. Simulasi Normalisasi
### 3.1 UNF &vert; Unnormalized Form

