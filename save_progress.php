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

$data       = json_decode(file_get_contents('php://input'), true);
$data       = is_array($data) ? $data : [];
$manga_id   = (int)($data['manga_id'] ?? 0);
$chapter_id = (int)($data['chapter_id'] ?? 0);
$last_page  = max(1, (int)($data['last_page'] ?? 1));
$progress   = min(100, max(0, (int)($data['progress'] ?? 0)));

if ($manga_id <= 0 || $chapter_id <= 0) {
    json_response(['status' => 'error', 'message' => 'Invalid parameters'], 400);
}

// Bob haqiqatan shu mangaga tegishli ekanini tekshiramiz
$chk = $pdo->prepare('SELECT 1 FROM chapters WHERE id = ? AND manga_id = ?');
$chk->execute([$chapter_id, $manga_id]);
if (!$chk->fetchColumn()) {
    json_response(['status' => 'error', 'message' => 'Chapter not found'], 404);
}

$stmt = $pdo->prepare("
    INSERT INTO reading_progress (user_id, manga_id, chapter_id, last_page, progress_percent)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        chapter_id = VALUES(chapter_id),
        last_page = VALUES(last_page),
        progress_percent = VALUES(progress_percent)
");
$stmt->execute([current_user_id(), $manga_id, $chapter_id, $last_page, $progress]);
json_response(['status' => 'ok']);
