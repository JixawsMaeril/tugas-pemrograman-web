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
Satu mahasiswa bisa meminjam **beberapa buku sekaligus** dalam satu transaksi, sehingga kolom buku berupa grup berulang:
```
Peminjaman (
    NIM, nama, departemen, no_hp, email,
    tanggal_pinjam, tanggal_kembali, tanggal_tenggat, status_pinjam,
    { judul_buku, tahun_terbit, nama_penerbit, kota_penerbit }   <-- grup berulang
)
```
Contoh:
| NIM | Nama | Departemen | Buku yang Dipinjam |
|---|---|---|---|
| D1212410XX | Budi Santiago | Teknik Informatika | 1. [1] Algoritma Pemrograman (Penerbit Andi Yogyakarta)<br>2. [2] Basis Data Lanjut (Penerbit Erlangga, Jakarta)<br>3. [3] Jaringan Komputer (Penerbit Andi, Yogyakarta) |

**`Satu sel harusnya cuma boleh satu nilai`**, tapi di sini satu sel justru ada list sendiri.

### 3.2 1NF &vert; First Normal Form
Satu buku = satu baris. Dan untuk mengeceknya lebih lanjut, tambahkan satu data (misal John) yang meminjam buku yang sama dengan Budi.

| NIM | Nama | Departemen | id_buku | Judul_Buku | Penerbit | Kota_Penerbit | 
|---|---|---|---|---|---|---|
| D121241002 | Budi Santiago | Teknik Informatika | 1 | Algoritma Pemrograman | Andi | Yogyakarta |
| D121241003 | John Wahyudi | Sistem Informasi | 1 | Algoritma Pemrograman | Andi | Yogyakarta |
| D121241002 | Budi Santiago | Teknik Informatika | 2 | Fundamental Basis Data | Andi | Yogyakarta |
| D121241002 | Budi Santiago | Teknik Informatika | 3 |Rekayasa Perangkat Lunak | Erlangga | Jakarta |

Primary Key sementara: (NIM, id_buku) — kombinasi ini dibutuhkan karena satu NIM bisa meminjam banyak buku berbeda, dan satu buku juga bisa dipinjam banyak mahasiswa berbeda.

Sekarang ada masalah baru. Ada beberapa data yang mengalami repetisi, seperti nama `'Budi Santiago'` yang muncul 3 kali. `'Andi'` dan `'Yogyakarta'` juga muncul 3 kali. Ini yang disebut **`redundansi`**, dan ini yang dibereskan di 2NF.

### 3.3 2NF &vert; Second Normal Form
Composite Key saat ini: (NIM, id_buku). Namun,
- `nama`, `departemen`, `no_hp`, `email` → cuma bergantung ke `NIM` saja (dependensi parsial) → dipisah ke tabel Mahasiswa
- `judul_buku`, `tahun_terbit`, `nama_penerbit`, `kota_penerbit` → cuma bergantung ke `id_buku` saja (dependensi parsial) → dipisah ke tabel Buku
- `tanggal_pinjam`, `tanggal_kembali`, `tanggal_tenggat`, `status_pinjam` → baru benar-benar bergantung ke kombinasi penuh (`NIM` + `id_buku`) → tetap di tabel peminjaman

Sehingga, permasalahan redundansi harus diselesaikan dengan memisahkannya dengan 3 tabel yang berbeda.

#### Tabel Mahasiswa (tiap orang cuma muncul 1x, bukan 3x lagi):
| NIM | Nama | Departemen |
|---|---|---|
| D121241002 | Budi Santiago | Teknik Informatika |
| D121241003 | John Wahyudi | Sistem Informasi |

#### Tabel Buku (tiap judul cuma muncul 1x):
| id_buku | Judul_Buku | Nama_Penerbit | Kota_Penerbit |
|---|---|---|---|
| 1 | Algoritma Pemrograman | Andi | Yogyakarta
| 2 | Fundamental Basis Data | Andi | Yogyakarta
| 3 | Rekayasa Perangkat Lunak | Erlangga | Jakarta

#### Tabel Transaksi Peminjaman (tidak ada redundansi lagi):
| NIM | id_buku | Tanggal Pinjam |
|---|---|---|
| D121241002 | 1 | 2026-09-01
| D121241002 | 2 | 2026-09-01
| D121241002 | 3 | 2026-09-01
| D121241003 | 1 | 2026-09-03

Sekarang "Budi Santiago" sekarang cuma ditulis 1 kali total (di tabel Mahasiswa), bukan 3 kali lagi. Itu hasil 2NF.

### 3.4 3NF &vert; Third Normal Form
Tabel Buku di atas: "Andi" dan "Yogyakarta" masih tertulis 2 kali (baris 1 dan 3), karena dua buku itu kebetulan dari penerbit yang sama. Ini kelihatan kecil, tapi bayangkan penerbit "Andi" punya 500 buku di perpustakaan — nama & kota penerbitnya bakal ketulis 500 kali, terulang-ulang.

Sehingga, pecah dulu jadi tabel `Penerbit` tersendiri, dan sekalian berikan `id_penerbit` sebagai primary key buatan (karena `nama_penerbit` sendirian kurang ideal jadi key — dua penerbit bisa saja punya nama mirip/sama)

#### Tabel Penerbit:
| id_penerbit | nama_penerbit | kota_penerbit |
|---|---|---|
| 1 | Andi | Yogyakarta |
| 2 | Erlangga | Jakarta |

#### Tabel Buku (dengan id_penerbit sebagai foreign key):
| id_buku | Judul_Buku | id_penerbit |
|---|---|---|
| 1 | Algoritma Pemrograman | 1 |
| 2 | Fundamental Basis Data | 1 |
| 3 | Rekayasa Perangkat Lunak | 2 |

Sekarang `"Andi"` dan `"Yogyakarta"` hanya ditulis 1 kali (di tabel `Penerbit`), sehingga meskipun dipakai buku apa pun dan berapa pun banyaknya &mdash; tabel `Buku` hanya menyimpan angka id_penerbit 1 atau 2 (nunjuk ke penerbit mana), bukan nulis ulang nama & kotanya tiap kali.

## 4. Rancangan Tabel Akhir
### Tabel Mahasiswa
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| NIM | VARCHAR(15) | Primary Key |
| nama | VARCHAR(100) | NOT NULL |
| departemen | VARCHAR(50) | NOT NULL |
| no_hp | VARCHAR(15) | NOT NULL |
| email | VARCHAR(100) | UNIQUE |

### Tabel Penerbit
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_penerbit | INT | Primary Key, AUTO_INCREMENT |
| nama_penerbit | VARCHAR(100) | NOT NULL |
| kota_penerbit | VARCHAR(50) | NOT NULL |

### Tabel Buku
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_buku | INT | Primary Key, AUTO_INCREMENT |
| judul_buku | VARCHAR(150) | NOT NULL |
| tahun_terbit | SMALLINT | NOT NULL |
| id_penerbit | INT | Foreign Key → `Penerbit(id_penerbit)` |

### Tabel Transaksi Peminjaman
| Kolom | Tipe Data | Keterangan |
|---|---|---|
| id_transaksi | INT | Primary Key, AUTO_INCREMENT |
| NIM | VARCHAR(15) | Foreign Key → `Mahasiswa(NIM)` |
| id_buku | INT | Foreign Key → `Buku(id_buku)` |
| tanggal_pinjam | DATE | NOT NULL |
| tanggal_kembali | DATE | NULL (diisi saat buku benar-benar dikembalikan) |
| tanggal_tenggat | DATE | NOT NULL |
| status_pinjam | ENUM('Dipinjam', 'Dikembalikan', 'Terlambat') | NOT NULL, DEFAULT 'Dipinjam' |

## 5. Diagram Relasi
 ```mermaid
 erDiagram
    MAHASISWA ||--o{TRANSAKSI_PEMINJAMAN: melakukan
    BUKU ||--o{TRANSAKSI_PEMINJAMAN : dipinjam_lewat
    PENERBIT ||--o{BUKU : menerbitkan

    MAHASISWA {
        varchar NIM PK
        varchar nama
        varchar departemen
        varchar no_hp
        varchar email
    }

    PENERBIT {
        int id_penerbit PK
        varchar nama_penerbit
        varchar kota_penerbit
    }

    BUKU {
        int id_buku PK
        varchar judul_buku
        smallint tahun_terbit
        int id_penerbit FK
    }

    TRANSAKSI_PEMINJAMAN    {
        int id_transaksi PK
        varchar NIM FK
        int id_buku FK
        date tanggal_pinjam
        date tanggal_kembali
        date tanggal_tenggat
        enum status_pinjam
    }
 ```

 **Kardinalitas:**
- Satu Mahasiswa bisa melakukan banyak Transaksi Peminjaman (1:N)
- Satu Buku bisa muncul di banyak Transaksi Peminjaman, di waktu yang berbeda (1:N)
- Satu Penerbit bisa menerbitkan banyak Buku (1:N)