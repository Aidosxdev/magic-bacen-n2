<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

const POPULAR_PER_PAGE = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * POPULAR_PER_PAGE;

$total = 0;
$items = [];
try {
    $total = (int)$pdo->query('SELECT COUNT(*) FROM mangas')->fetchColumn();
    // Avval ko'rishlar soni bo'yicha; "views" ustuni hali bo'lmasa, reytingga qaytamiz
    foreach (['m.views DESC, m.rating DESC, m.id DESC', 'm.rating DESC, m.id DESC'] as $order) {
        try {
            $stmt = $pdo->prepare(MANGA_SELECT . " ORDER BY $order LIMIT :lim OFFSET :off");
            $stmt->bindValue(':lim', POPULAR_PER_PAGE, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
            break;
        } catch (PDOException $e) {
            continue;
        }
    }
} catch (PDOException $e) {
    error_log('[popular] ' . $e->getMessage());
}
$pages = max(1, (int)ceil($total / POPULAR_PER_PAGE));

$pageTitle = 'Eng mashhur asarlar | Magic Manga';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/menu.php';
?>
<section class="section">
    <h1 class="page-title">Eng mashhur asarlar</h1>
    <div class="cards-container">
        <?php foreach ($items as $manga) render_manga_card($manga); ?>
        <?php if (!$items): ?><p class="empty-note">Hozircha asarlar yo'q.</p><?php endif; ?>
    </div>
    <?php render_pagination($page, $pages); ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
