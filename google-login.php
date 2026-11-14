<?php
require_once __DIR__ . '/../includes/functions.php';

$clientId    = (string)config('google_client_id', '');
$redirectUri = (string)config('google_redirect_uri', '');
if ($clientId === '' || $redirectUri === '') {
    redirect('/auth/login.php');
}

// CSRF himoyasi: tasodifiy "state" sessiyada saqlanadi, Google qaytarganda solishtiriladi
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));

redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
    'state'         => $_SESSION['oauth_state'],
]));
