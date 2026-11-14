<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$botToken = (string)config('telegram_bot_token', '');
$auth = $_GET;
$hash = (string)($auth['hash'] ?? '');
unset($auth['hash']);

if ($botToken === '' || $hash === '' || empty($auth['id']) || empty($auth['auth_date'])) {
    redirect('/auth/login.php');
}

$pairs = [];
foreach ($auth as $k => $v) {
    $pairs[] = $k . '=' . $v;
}
sort($pairs);
$expected = hash_hmac('sha256', implode("\n", $pairs), hash('sha256', $botToken, true));

// Imzo to'g'ri va ma'lumot 24 soatdan eski emas bo'lishi shart
if (!hash_equals($expected, $hash) || (time() - (int)$auth['auth_date']) > 86400) {
    redirect('/auth/login.php');
}

$tgId  = (int)$auth['id'];
$email = 'tg_' . $tgId . '@magicmanga.uz';   // virtual email

$stmt = $pdo->prepare('SELECT id, username FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    login_user((int)$user['id'], $user['username']);
} else {
    $username = unique_username($pdo, (string)($auth['username'] ?? $auth['first_name'] ?? 'TelegramUser'));
    $avatar   = (string)($auth['photo_url'] ?? '');
    $pdo->prepare('INSERT INTO users (username, email, password, avatar) VALUES (?, ?, ?, ?)')
        ->execute([$username, $email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), $avatar !== '' ? $avatar : null]);
    login_user((int)$pdo->lastInsertId(), $username);
}
redirect('/profile/index.php');
