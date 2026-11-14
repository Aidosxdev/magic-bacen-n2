<?php
/**
 * To'lov provayderi (Click / Payme / ...) tasdiqlash so'rovi qabul qilinadigan manzil.
 *
 * DIQQAT: bu — umumiy namuna. Click va Payme har birining o'z formati va imzo
 * algoritmi bor; ularni ulashda provayder hujjatiga qarab "imzoni tekshirish" va
 * "javob formati" qismlarini moslashtiring. Muhim tamoyillar shu yerda tayyor:
 *   1) imzo tekshiriladi (kalitsiz hech kim balans qo'sha olmaydi),
 *   2) summa bazadagi bilan solishtiriladi,
 *   3) to'lov faqat BIR marta hisoblanadi (takroriy so'rov balansni oshirmaydi).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$secret = (string)config('payment_webhook_secret', '');
if ($secret === '') {
    json_response(['status' => 'error', 'message' => 'Webhook sozlanmagan'], 503);
}

$raw = file_get_contents('php://input');
$sig = (string)($_SERVER['HTTP_X_SIGNATURE'] ?? '');
if (!hash_equals(hash_hmac('sha256', (string)$raw, $secret), $sig)) {
    json_response(['status' => 'error', 'message' => 'Imzo noto\'g\'ri'], 403);
}

$data   = json_decode((string)$raw, true);
$txId   = is_array($data) ? (string)($data['transaction_id'] ?? '') : '';
$amount = is_array($data) ? (int)($data['amount'] ?? 0) : 0;
$status = is_array($data) ? (string)($data['status'] ?? '') : '';

if ($txId === '' || $status !== 'success') {
    json_response(['status' => 'ignored']);
}

$pdo->beginTransaction();
try {
    $p = $pdo->prepare('SELECT id, user_id, amount, status FROM payments WHERE transaction_id = ? FOR UPDATE');
    $p->execute([$txId]);
    $payment = $p->fetch();

    if (!$payment) {
        $pdo->rollBack();
        json_response(['status' => 'error', 'message' => 'To\'lov topilmadi'], 404);
    }
    if ((int)$payment['amount'] !== $amount) {
        $pdo->rollBack();
        json_response(['status' => 'error', 'message' => 'Summa mos kelmadi'], 409);
    }
    if ($payment['status'] === 'paid') {          // allaqachon hisoblangan
        $pdo->rollBack();
        json_response(['status' => 'ok', 'already' => true]);
    }

    $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = ?")->execute([$payment['id']]);
    $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$payment['amount'], $payment['user_id']]);
    $pdo->commit();
    json_response(['status' => 'ok']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[webhook] ' . $e->getMessage());
    json_response(['status' => 'error'], 500);
}
