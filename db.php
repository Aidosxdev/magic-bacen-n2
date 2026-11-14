<?php
require_once __DIR__ . '/config.php';

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    config('db_host', 'localhost'),
    config('db_name', '')
);

try {
    $pdo = new PDO($dsn, (string)config('db_user', ''), (string)config('db_pass', ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Haqiqiy xatoni faqat logga yozamiz, foydalanuvchiga ko'rsatmaymiz
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    exit('Server vaqtincha ishlamayapti. Iltimos, keyinroq urinib ko\'ring.');
}
