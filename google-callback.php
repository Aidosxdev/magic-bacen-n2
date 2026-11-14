<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

/** Google API ga kichik cURL so'rov (timeout bilan, SSL tekshiruvi yoqilgan) */
function google_request(string $url, ?array $post = null, array $headers = []): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $res = curl_exec($ch);
    curl_close($ch);
    $data = is_string($res) ? json_decode($res, true) : null;
    return is_array($data) ? $data : null;
}

$state = (string)($_GET['state'] ?? '');
$code  = (string)($_GET['code'] ?? '');
$saved = (string)($_SESSION['oauth_state'] ?? '');
unset($_SESSION['oauth_state']);

if ($code === '' || $saved === '' || !hash_equals($saved, $state)) {
    redirect('/auth/login.php');
}

$token = google_request('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => config('google_client_id', ''),
    'client_secret' => config('google_client_secret', ''),
    'redirect_uri'  => config('google_redirect_uri', ''),
    'grant_type'    => 'authorization_code',
]);
if (empty($token['access_token'])) {
    redirect('/auth/login.php');
}

$g = google_request('https://www.googleapis.com/oauth2/v2/userinfo', null, ['Authorization: Bearer ' . $token['access_token']]);
// Faqat Google tasdiqlagan email bilan kiramiz
if (empty($g['email']) || empty($g['verified_email'])) {
    redirect('/auth/login.php');
}

$email = (string)$g['email'];
$stmt = $pdo->prepare('SELECT id, username FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    login_user((int)$user['id'], $user['username']);
} else {
    $username = unique_username($pdo, (string)($g['name'] ?? explode('@', $email)[0]));
    $avatar   = (string)($g['picture'] ?? '');
    $pdo->prepare('INSERT INTO users (username, email, password, avatar) VALUES (?, ?, ?, ?)')
        ->execute([$username, $email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), $avatar !== '' ? $avatar : null]);
    login_user((int)$pdo->lastInsertId(), $username);
}
redirect('/profile/index.php');
