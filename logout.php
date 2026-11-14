<?php
require_once __DIR__ . '/../includes/functions.php';

// Chiqish faqat POST + CSRF token bilan (boshqa saytdan yashirin chiqarib yuborish oldini oladi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect('/auth/login.php');
}
redirect('/index.php');
