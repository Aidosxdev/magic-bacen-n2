<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}
if (current_user_id() <= 0) {
    json_response(['status' => 'error', 'message' => 'Unauthorized'], 401);
}
if (!csrf_valid()) {
    json_response(['status' => 'error', 'message' => 'Bad CSRF token'], 419);
}

$data     = json_decode(file_get_contents('php://input'), true);
$manga_id = (int)(is_array($data) ? ($data['manga_id'] ?? 0) : 0);
$uid      = current_user_id();

$exists = $pdo->prepare('SELECT 1 FROM mangas WHERE id = ?');
$exists->execute([$manga_id]);
if (!$exists->fetchColumn()) {
    json_response(['status' => 'error', 'message' => 'Manga not found'], 404);
}

$has = $pdo->prepare('SELECT 1 FROM manga_likes WHERE user_id = ? AND manga_id = ?');
$has->execute([$uid, $manga_id]);

if ($has->fetchColumn()) {
    $pdo->prepare('DELETE FROM manga_likes WHERE user_id = ? AND manga_id = ?')->execute([$uid, $manga_id]);
    json_response(['status' => 'ok', 'liked' => false]);
}
$pdo->prepare('INSERT INTO manga_likes (user_id, manga_id) VALUES (?, ?)')->execute([$uid, $manga_id]);
json_response(['status' => 'ok', 'liked' => true]);
