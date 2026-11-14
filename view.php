<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$chapter_id = (int)($_GET['chapter_id'] ?? 0);
if ($chapter_id <= 0) {
    not_found('Bob topilmadi!');
}

$stmt = $pdo->prepare(
    'SELECT c.*, m.title AS manga_title FROM chapters c JOIN mangas m ON c.manga_id = m.id WHERE c.id = ?'
);
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch();
if (!$chapter) {
    not_found('Bob topilmadi!');
}
$manga_id = (int)$chapter['manga_id'];

// Bob rasmlari: avval chapter_images jadvali, bo'lmasa chapters.images (JSON)
$images = [];
try {
    $imgStmt = $pdo->prepare('SELECT image_path FROM chapter_images WHERE chapter_id = ? ORDER BY page_number ASC');
    $imgStmt->execute([$chapter_id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('[view] ' . $e->getMessage());
}
if (!$images && !empty($chapter['images'])) {
    $decoded = json_decode((string)$chapter['images'], true);
    $images = is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
}

// Oldingi / keyingi bob
$prevStmt = $pdo->prepare('SELECT id FROM chapters WHERE manga_id = ? AND chapter_number < ? ORDER BY chapter_number DESC LIMIT 1');
$prevStmt->execute([$manga_id, $chapter['chapter_number']]);
$prev = $prevStmt->fetchColumn();

$nextStmt = $pdo->prepare('SELECT id FROM chapters WHERE manga_id = ? AND chapter_number > ? ORDER BY chapter_number ASC LIMIT 1');
$nextStmt->execute([$manga_id, $chapter['chapter_number']]);
$next = $nextStmt->fetchColumn();

$prevUrl = $prev ? url('const/view.php?chapter_id=' . (int)$prev) : '#';
$nextUrl = $next ? url('const/view.php?chapter_id=' . (int)$next) : '#';

$pageTitle = $chapter['manga_title'] . ' — ' . $chapter['chapter_number'] . '-bob | Magic Manga';
$bodyClass = 'reader-body';
require __DIR__ . '/../includes/head.php';
?>
<div class="reader-nav">
    <a href="<?= url('const/reader.php?id=' . $manga_id) ?>">← Boblar</a>
    <span><?= e($chapter['manga_title']) ?> | <?= e($chapter['chapter_number']) ?>-bob</span>
    <a href="<?= url('index.php') ?>">Bosh sahifa</a>
</div>

<div class="manga-pages"
     data-manga-id="<?= $manga_id ?>" data-chapter-id="<?= (int)$chapter_id ?>"
     data-logged-in="<?= current_user_id() > 0 ? '1' : '0' ?>">
    <?php if ($images): ?>
        <?php foreach ($images as $i => $img): ?>
            <img src="<?= e(asset_url($img)) ?>" alt="<?= e($chapter['manga_title']) ?> — <?= $i + 1 ?>-sahifa" loading="lazy">
        <?php endforeach; ?>
    <?php else: ?>
        <p style="margin-top:100px;color:#777;">Bu bob uchun hali rasmlar yuklanmagan.</p>
    <?php endif; ?>
</div>

<div class="bottom-nav">
    <a href="<?= e($prevUrl) ?>" class="btn <?= $prev ? '' : 'disabled' ?>">← Oldingi bob</a>
    <a href="<?= url('const/reader.php?id=' . $manga_id) ?>" class="btn">Boblar ro'yxati</a>
    <a href="<?= e($nextUrl) ?>" class="btn <?= $next ? '' : 'disabled' ?>">Keyingi bob →</a>
</div>

<?php $showFooter = false; require __DIR__ . '/../includes/footer.php'; ?>
