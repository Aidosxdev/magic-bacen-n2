<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_login('/auth/login.php');

$user_id = current_user_id();
$message = '';

$user_stmt = $pdo->prepare('SELECT username, balance FROM users WHERE id = ?');
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount  = (int)($_POST['amount'] ?? 0);          // so'm butun son
    $method  = (string)($_POST['method'] ?? 'Click');
    $allowed = ['Click', 'Payme', 'Uzum', 'Card'];
    if (!in_array($method, $allowed, true)) {
        $method = 'Click';
    }

    if (!csrf_valid()) {
        $message = "Sessiya eskirdi. Sahifani yangilab qayta urinib ko'ring.";
    } elseif ($amount < 5000 || $amount > 10000000) {
        $message = "To'lov summasi 5 000 dan 10 000 000 so'mgacha bo'lishi kerak!";
    } else {
        $tx_id = 'MM-' . time() . '-' . bin2hex(random_bytes(4));
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, transaction_id, amount, method, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $tx_id, $amount, $method]);
        redirect('check_payment.php?payment_id=' . (int)$pdo->lastInsertId());
    }
}
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lov va Balans - Magic Manga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(56, 189, 248, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md glass-panel rounded-2xl p-8 relative overflow-hidden">
        <div class="flex items-center justify-between mb-6">
            <a href="../profile/index.php" class="text-sm text-slate-400 hover:text-cyan-400 transition">← Profilga qaytish</a>
            <img src="../assets/logo.jpg" alt="Logo" class="w-9 h-9 rounded-lg object-cover">
        </div>

        <div class="bg-slate-900/90 border border-cyan-500/30 rounded-xl p-4 mb-6">
            <p class="text-xs text-slate-400 uppercase">Joriy balans</p>
            <p class="text-2xl font-extrabold text-cyan-400 mt-1"><?= number_format((float)($user['balance'] ?? 0), 0, '.', ' ') ?> UZS</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm text-center">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">Summa (UZS)</label>
                <input type="number" name="amount" min="5000" step="1000" required placeholder="20000" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-cyan-400 transition text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-2">To'lov tizimi</label>
                <select name="method" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-cyan-400 transition text-sm">
                    <option value="Click">Click</option>
                    <option value="Payme">Payme</option>
                    <option value="Uzum">Uzum Bank</option>
                    <option value="Card">Uzcard / Humo</option>
                </select>
            </div>
            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg shadow-blue-500/25">
                To'lovga o'tish
            </button>
        </form>
    </div>
</body>
</html>
