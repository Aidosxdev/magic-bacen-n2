<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_login('/auth/login.php');

$user_id    = current_user_id();
$payment_id = (int)($_GET['payment_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND user_id = ?');
$stmt->execute([$payment_id, $user_id]);
$payment = $stmt->fetch();
if (!$payment) {
    http_response_code(404);
    exit("To'lov topilmadi!");
}

$demo = (bool)config('payment_demo_mode', false);

/*
 * MUHIM: balans faqat to'lov tizimi (Click/Payme/...) tasdiqlaganda to'ldiriladi —
 * buni payment/webhook.php bajaradi. Quyidagi tugma faqat sinov rejimida (payment_demo_mode)
 * ishlaydi; ishlab chiqarishda o'chiq turishi shart.
 */
if ($demo && $_SERVER['REQUEST_METHOD'] === 'POST' && $payment['status'] === 'pending' && csrf_valid()) {
    $pdo->beginTransaction();
    try {
        $lock = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ? AND status = 'pending'");
        $lock->execute([$payment_id]);
        if ($lock->rowCount() === 1) {   // faqat bir marta qo'shiladi
            $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$payment['amount'], $user_id]);
        }
        $pdo->commit();
        $payment['status'] = 'paid';
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('[check_payment] ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To'lovni tekshirish - Magic Manga</title>
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
    <div class="w-full max-w-md glass-panel rounded-2xl p-8 text-center">
        <h1 class="text-xl font-bold text-white mb-1">To'lov #<?= (int)$payment['id'] ?></h1>
        <p class="text-xs text-slate-500 mb-3"><?= e($payment['transaction_id'] ?? '') ?></p>
        <p class="text-slate-400 text-sm mb-6">Tizim: <span class="text-cyan-400 font-semibold"><?= e($payment['method']) ?></span></p>

        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800 mb-6 space-y-2">
            <p class="text-2xl font-extrabold text-white"><?= number_format((float)$payment['amount'], 0, '.', ' ') ?> UZS</p>
            <?php if ($payment['status'] === 'paid'): ?>
                <span class="inline-block px-3 py-1 rounded-full text-xs bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">To'langan</span>
            <?php else: ?>
                <span class="inline-block px-3 py-1 rounded-full text-xs bg-amber-500/15 text-amber-400 border border-amber-500/30">Kutilmoqda</span>
            <?php endif; ?>
        </div>

        <?php if ($payment['status'] === 'pending'): ?>
            <?php if ($demo): ?>
                <form method="POST" class="mb-4">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition">
                        To'lovni tasdiqlash (SINOV rejimi)
                    </button>
                </form>
            <?php else: ?>
                <p class="text-xs text-slate-400 mb-4">To'lov tizimi tasdiqlagach, balansingiz avtomatik to'ldiriladi.</p>
                <a href="?payment_id=<?= (int)$payment['id'] ?>" class="block w-full py-3 px-4 mb-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition text-sm">Holatni yangilash</a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="../profile/index.php" class="block w-full py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium transition">
            Profilga o'tish
        </a>
    </div>
</body>
</html>
