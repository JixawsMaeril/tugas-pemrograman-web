<?php

declare(strict_types=1);
session_start();
require_once __DIR__ . '/GuestBook.php';

$host = '127.0.0.1';
$dbname = 'akademik_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $guestBook = new GuestBook($pdo);

} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi database.');
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$nama = '';
$email = '';
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    }
    
    else {
        $nama = isset($_POST['nama']) && is_string($_POST['nama'])
            ? trim($_POST['nama'])
            : '';

        $email = isset($_POST['email']) && is_string($_POST['email'])
            ? trim($_POST['email'])
            : '';

        $pesan = isset($_POST['pesan']) && is_string($_POST['pesan'])
            ? trim($_POST['pesan'])
            : '';

        if ($nama === '') {
            $errors[] = 'Nama tidak boleh kosong.';
        } elseif (strlen($nama) > 100) {
            $errors[] = 'Nama maksimal 100 karakter.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }

        if (preg_match_all('/./us', $pesan) < 5) {
            $errors[] = 'Pesan harus memiliki minimal 5 karakter.';
        }

        if (empty($errors)) {
            try {
                $guestBook->simpanPesan($nama, $email, $pesan);

                $_SESSION['success_message'] = 'Pesan berhasil dikirim.';

                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;

            } catch (PDOException $e) {
                $errors[] = 'Pesan gagal disimpan. Silakan coba lagi.';
            }
        }
    }

}