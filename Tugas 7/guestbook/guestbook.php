<?php

declare(strict_types=1);
session_start();
require_once __DIR__ . '/classes/GuestBook.php';

// hal-hal dibawah ini harus disesuaikan dengan konfigurasi database MySQL.

$host = 'localhost';
$port = 3306;
$dbname = 'akademik_db';
$username = 'guestbook_app';
$password = 'PASSWORD_MYSQL_KAMU';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
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
    exit(
        'Koneksi database gagal: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
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

try {
    $daftarPesan = $guestBook->ambilSemuaPesan();
} catch (PDOException $e) {
    $daftarPesan = [];
    $errors[] = 'Daftar pesan gagal dimuat.';
}

function escapeHtml(string $teks): string
{
    return htmlspecialchars(
        $teks,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Tamu Perpustakaan</title>
</head>
<body>

    <h1>Buku Tamu Perpustakaan</h1>

    <p>Silakan tuliskan pesan atau kesan kamu.</p>

<?php if ($successMessage !== ''): ?>
    <p><?= escapeHtml($successMessage) ?></p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= escapeHtml($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Tulis Pesan</h2>

<form method="POST" action="">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= escapeHtml($_SESSION['csrf_token']) ?>"
    >

    <label for="nama">Nama:</label><br>
    <input
        type="text"
        id="nama"
        name="nama"
        maxlength="100"
        value="<?= escapeHtml($nama) ?>"
        required
    ><br><br>

    <label for="email">Email:</label><br>
    <input
        type="email"
        id="email"
        name="email"
        maxlength="254"
        value="<?= escapeHtml($email) ?>"
        required
    ><br><br>

    <label for="pesan">Pesan:</label><br>
    <textarea
        id="pesan"
        name="pesan"
        minlength="5"
        required
    ><?= escapeHtml($pesan) ?></textarea><br><br>

    <button type="submit">Kirim Pesan</button>
</form>
<h2>Daftar Pesan</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Pesan</th>
            <th>Tanggal Kirim</th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($daftarPesan)): ?>
            <tr>
                <td colspan="5">Belum ada pesan.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($daftarPesan as $data): ?>
                <tr>
                    <td><?= escapeHtml((string) $data['id']) ?></td>
                    <td><?= escapeHtml($data['nama']) ?></td>
                    <td><?= escapeHtml($data['email']) ?></td>
                    <td><?= escapeHtml($data['pesan']) ?></td>
                    <td><?= escapeHtml($data['tanggal_kirim']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>