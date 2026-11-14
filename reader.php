<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

$manga_id = (int)($_GET['id'] ?? 0);
if ($manga_id <= 0) {
    not_found('Manga topilmadi!');
}

$stmt = $pdo->prepare('SELECT * FROM mangas WHERE id = ?');
$stmt->execute([$manga_id]);
$manga = $stmt->fetch();
if (!$manga) {
    not_found('Manga topilmadi!');
}

$chStmt = $pdo->prepare('SELECT id, chapter_number, title FROM chapters WHERE manga_id = ? ORDER BY chapter_number ASC, id ASC');
$chStmt->execute([$manga_id]);
$chapters = $chStmt->fetchAll();

// Ko'rishlar sonini bir sessiyada bir marta oshiramiz ("views" ustuni bo'lmasa jim o'tib ketadi)
if (empty($_SESSION['viewed'][$manga_id])) {
    $_SESSION['viewed'][$manga_id] = true;
    try {
        $pdo->prepare('UPDATE mangas SET views = views + 1 WHERE id = ?')->execute([$manga_id]);
    } catch (PDOException $e) {
    }
}

// Foydalanuvchiga xos ma'lumotlar: sevimli holati va qayerda to'xtagani
$uid = current_user_id();
$liked = false;
$resume = null;
if ($uid > 0) {
    try {
        $l = $pdo->prepare('SELECT 1 FROM manga_likes WHERE user_id = ? AND manga_id = ?');
        $l->execute([$uid, $manga_id]);
        $liked = (bool)$l->fetchColumn();

        $r = $pdo->prepare(
            'SELECT rp.chapter_id, rp.progress_percent, c.chapter_number
             FROM reading_progress rp JOIN chapters c ON c.id = rp.chapter_id
             WHERE rp.user_id = ? AND rp.manga_id = ?'
        );
        $r->execute([$uid, $manga_id]);
        $resume = $r->fetch() ?: null;
    } catch (PDOException $e) {
        error_log('[reader] ' . $e->getMessage());
    }
}

$pageTitle = $manga['title'] . " — Ma'lumotlar | Magic Manga";
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/menu.php';
?>
<div class="container">
    <a href="<?= url('index.php') ?>" class="back-btn">← Bosh sahifaga qaytish</a>

    <div class="manga-hero-box">
        <img src="<?= e(asset_url($manga['cover_image'] ?? '')) ?>" class="manga-poster" alt="<?= e($manga['title']) ?>">

        <h1 class="manga-title"><?= e($manga['title']) ?></h1>

        <div class="manga-meta">
            <span>Janr: <?= e($manga['genre'] ?? '—') ?></span>
            <span>Reyting: ⭐ <?= e($manga['rating'] ?? '—') ?></span>
            <span>Jami boblar: <?= count($chapters) ?> ta</span>
        </div>

        <p class="manga-desc"><?= nl2br(e($manga['description'] ?? 'Tavsif mavjud emas.')) ?></p>

        <button type="button" class="like-btn <?= $liked ? 'liked' : '' ?>"
                data-manga-id="<?= (int)$manga_id ?>" data-logged-in="<?= $uid > 0 ? '1' : '0' ?>">
            <?= $liked ? '♥ Tanlanganlarda' : "♡ Tanlanganlarga qo'shish" ?>
        </button>

        <?php if ($resume): ?>
            <a class="like-btn" style="text-decoration:none;" href="<?= url('const/view.php?chapter_id=' . (int)$resume['chapter_id']) ?>">
                Davom ettirish: <?= e($resume['chapter_number']) ?>-bob (<?= (int)$resume['progress_percent'] ?>%)
            </a>
        <?php elseif ($chapters): ?>
            <a class="like-btn" style="text-decoration:none;" href="<?= url('const/view.php?chapter_id=' . (int)$chapters[0]['id']) ?>">O'qishni boshlash</a>
        <?php endif; ?>
    </div>

    <div class="chapters-list">
        <h3>Boblar ro'yxati</h3>
        <?php if ($chapters): ?>
            <?php foreach ($chapters as $ch): ?>
                <a href="<?= url('const/view.php?chapter_id=' . (int)$ch['id']) ?>" class="chapter-item">
                    <span><?= e($ch['chapter_number']) ?>-bob<?= $ch['title'] ? ': ' . e($ch['title']) : '' ?></span>
                    <span style="font-size:10px;color:#888;">O'qish →</span>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#777;text-align:center;padding:20px;">Hozircha bu manga uchun boblar qo'shilmagan.</p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
