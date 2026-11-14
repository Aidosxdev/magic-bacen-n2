<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_login('/auth/login.php');

$items = [];
try {
    $stmt = $pdo->prepare(
        'SELECT m.*, (SELECT COUNT(*) FROM chapters c WHERE c.manga_id = m.id) AS chapters_total
         FROM manga_likes l JOIN mangas m ON m.id = l.manga_id
         WHERE l.user_id = ? ORDER BY m.title ASC'
    );
    $stmt->execute([current_user_id()]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[arxiv] ' . $e->getMessage());
}

$pageTitle = 'Tanlanganlar | Magic Manga';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/menu.php';
?>
<section class="section">
    <h1 class="page-title">Tanlanganlar</h1>
    <div class="cards-container">
        <?php foreach ($items as $manga) render_manga_card($manga); ?>
        <?php if (!$items): ?>
            <p class="empty-note">Hali hech narsa tanlamagansiz. Manga sahifasida «Tanlanganlarga qo'shish» tugmasini bosing.</p>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
