<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

const CATALOG_PER_PAGE = 24;

$q      = trim((string)($_GET['q'] ?? ''));
$q      = mb_substr($q, 0, 100, 'UTF-8');
$genre  = trim((string)($_GET['genre'] ?? ''));
$sort   = (string)($_GET['sort'] ?? 'new');
$page   = max(1, (int)($_GET['page'] ?? 1));

// Tartiblash faqat oq ro'yxatdan (SQL ga foydalanuvchi matni qo'yilmaydi)
$sorts = [
    'new'    => ['Yangilari', 'm.id DESC'],
    'rating' => ['Reyting', 'm.rating DESC, m.id DESC'],
    'title'  => ['Nomi (A–Z)', 'm.title ASC'],
];
if (!isset($sorts[$sort])) $sort = 'new';

$where = [];
$params = [];
if ($q !== '') {
    // LIKE maxsus belgilarini (%, _) oddiy matn sifatida qaraymiz
    $like = '%' . addcslashes($q, '\\%_') . '%';
    $where[] = '(m.title LIKE ? OR m.description LIKE ?)';
    array_push($params, $like, $like);
}
if ($genre !== '') {
    $where[] = 'm.genre = ?';
    $params[] = $genre;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$genres = $items = [];
$total = 0;
try {
    $genres = $pdo->query("SELECT DISTINCT genre FROM mangas WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM mangas m' . $whereSql);
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();

    $pages = max(1, (int)ceil($total / CATALOG_PER_PAGE));
    $page = min($page, $pages);

    $stmt = $pdo->prepare(MANGA_SELECT . $whereSql . ' ORDER BY ' . $sorts[$sort][1] . ' LIMIT ' . CATALOG_PER_PAGE . ' OFFSET ' . (($page - 1) * CATALOG_PER_PAGE));
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[katalog] ' . $e->getMessage());
}
$pages = max(1, (int)ceil($total / CATALOG_PER_PAGE));

$pageTitle = 'Katalog | Magic Manga';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/menu.php';
?>
<section class="section">
    <h1 class="page-title">Katalog <span style="font-size:12px;color:#888;">(<?= $total ?> ta)</span></h1>

    <form class="filter-bar" method="get" action="<?= url('Katalog.php') ?>">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Nomi yoki tavsifi bo'yicha..." maxlength="100">
        <select name="genre">
            <option value="">Barcha janrlar</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= e($g) ?>" <?= $g === $genre ? 'selected' : '' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort">
            <?php foreach ($sorts as $key => [$label]): ?>
                <option value="<?= e($key) ?>" <?= $key === $sort ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Qidirish</button>
    </form>

    <div class="cards-container">
        <?php foreach ($items as $manga) render_manga_card($manga); ?>
        <?php if (!$items): ?><p class="empty-note">Hech narsa topilmadi.</p><?php endif; ?>
    </div>
    <?php render_pagination($page, $pages); ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
