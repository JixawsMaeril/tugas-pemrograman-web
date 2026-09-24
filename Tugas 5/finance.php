<?php
require_once __DIR__ . '/Transaction.php';

session_start();

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 0.0;
}

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

if (!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$statusMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $errors[] = 'Token keamanan (CSRF) tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $rawType = $_POST['type'] ?? '';
        $rawAmount = $_POST['amount'] ?? '';
 
        $type = match ($rawType) {
            'deposit', 'withdrawal' => $rawType,
            default => null,
        };
 
        if ($type === null) {
            $errors[] = 'Jenis transaksi tidak valid.';
        }
        $amount = filter_var($rawAmount, FILTER_VALIDATE_FLOAT);
    
        if ($amount === false || $amount <= 0) {
            $errors[] = 'Jumlah transaksi harus berupa angka desimal positif.';
        }
    
        if (empty($errors)) {
            $id = count($_SESSION['transactions']) + 1;
            $transaction = new Transaction($id, $type, $amount);
    
            $balance = $_SESSION['balance'];
            $result = $transaction->process($balance);
            $_SESSION['balance'] = $balance;
    
            if ($result['success']) {
                $_SESSION['transactions'][] = $transaction;
            }
    
            $statusMessage = $result;
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

$csrfToken = $_SESSION['csrf_token'];
$balance = $_SESSION['balance'];
$transactions = $_SESSION['transactions'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan Sederhana</title>
</head>
<body>
 
    <main>
        <h1>Sistem Manajemen Keuangan Sederhana</h1>
 
        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
 
        <?php if ($statusMessage !== null): ?>
            <div class="alert <?= $statusMessage['success'] ? 'alert--success' : 'alert--error' ?>">
                <?= htmlspecialchars($statusMessage['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
 
        <section>
            <h2>Saldo Saat Ini</h2>
            <p class="balance">
                Rp <?= htmlspecialchars(number_format($balance, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </section>
 
        <section>
            <h2>Transaksi Baru</h2>
            <form method="post" action="finance.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
 
                <label for="type">Jenis Transaksi</label>
                <select name="type" id="type" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Penarikan</option>
                </select>
 
                <label for="amount">Jumlah</label>
                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
 
                <button type="submit">Proses Transaksi</button>
            </form>
        </section>
 
        <section>
            <h2>Riwayat Transaksi</h2>
 
            <?php if (empty($transactions)): ?>
                <p>Belum ada transaksi.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Jenis</th>
                            <th scope="col">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trx): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $trx->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        $trx->getType() === 'deposit' ? 'Deposit' : 'Penarikan',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                <td><?= htmlspecialchars(number_format($trx->getAmount(), 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
 
</body>
</html>
