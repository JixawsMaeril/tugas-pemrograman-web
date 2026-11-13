# Nama: Moh. Alhaji Putra Lede
# NIM: D121241041
# Tugas Pemrograman Web Kelas B

## Daftar Buku — Laravel, Blade, dan Tailwind CSS

Proyek ini merupakan aplikasi web sederhana untuk mengelola koleksi buku. Aplikasi dibuat menggunakan Laravel dan menyediakan fitur tambah, lihat, ubah, hapus, serta pencarian buku.

### Fitur Web

- Menampilkan koleksi buku dalam bentuk daftar
- Menambahkan data buku baru
- Melihat informasi buku
- Mengedit dan menghapus data buku
- Mencari buku berdasarkan judul atau nama penulis
- Menampilkan daftar buku dengan pagination
- Memvalidasi data sebelum disimpan

### Informasi Buku

Setiap data buku mencakup:

- Judul buku
- Nama penulis
- Tahun terbit
- Sinopsis

### Teknologi yang Digunakan

- PHP 8.2+
- Laravel 12
- SQLite
- Blade
- Tailwind CSS
- Vite

### Folder Project

Project Laravel berada di:

`daftar-buku/`

### Menjalankan Aplikasi

Pastikan PHP, Composer, Node.js, dan npm sudah tersedia. Proyek ini menggunakan SQLite, jadi MySQL tidak diperlukan.

Jalankan perintah berikut dari folder proyek:

```bash
composer install
npm install
php artisan migrate
```