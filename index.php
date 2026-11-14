<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$mangas = [];
$trending = [];
try {
    // Bosh sahifada hammasini emas, so'nggi 24 tasini ko'rsatamiz (hammasi Katalog'da)
    $mangas = $pdo->query(MANGA_SELECT . ' ORDER BY m.id DESC LIMIT 24')->fetchAll();
    $trending = $pdo->query(MANGA_SELECT . ' ORDER BY m.rating DESC LIMIT 5')->fetchAll();
} catch (PDOException $e) {
    error_log('[index] ' . $e->getMessage());
}

$pageTitle = 'Magic Manga | Premium';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/menu.php';
?>
<section class="section">
    <div class="hero-wrapper">
        <div class="carousel-container">
            <div class="carousel-track" id="track">
                <?php if ($mangas): ?>
                    <?php foreach (array_slice($trending ?: $mangas, 0, 5) as $slide): ?>
                        <?php $cover = asset_url($slide['cover_image'] ?? ''); ?>
                        <a href="<?= url('const/reader.php?id=' . (int)$slide['id']) ?>" class="hero-slide"
                           style="background-image: url('<?= e(str_replace(["'", '"', '(', ')'], ['%27', '%22', '%28', '%29'], $cover)) ?>');">
                            <div class="hero-info">
                                <div class="hero-meta"><span><?= e($slide['genre'] ?? 'MANHWA') ?></span><span>⭐ <?= e($slide['rating'] ?? '—') ?></span></div>
                                <h1><?= e($slide['title']) ?></h1>
                                <p class="hero-desc"><?= e(mb_strimwidth((string)($slide['description'] ?? ''), 0, 160, '…', 'UTF-8')) ?></p>
                            </div>
                            <div class="hero-img"><img src="<?= e($cover) ?>" alt="<?= e($slide['title']) ?>"></div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hero-slide">
                        <div class="hero-info">
                            <h1>Magic Manga</h1>
                            <p class="hero-desc">Hozircha mangalar qo'shilmagan. Tez orada shu yerda yangi asarlar paydo bo'ladi.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar-popular">
            <h3>Bugun Mashhur</h3>
            <?php foreach ($trending as $i => $pop): ?>
                <a href="<?= url('const/reader.php?id=' . (int)$pop['id']) ?>" class="pop-item">
                    <span class="rank-num"><?= $i + 1 ?></span>
                    <img src="<?= e(asset_url($pop['cover_image'] ?? '')) ?>" alt="">
                    <h4><?= e($pop['title']) ?></h4>
                </a>
            <?php endforeach; ?>
            <?php if (!$trending): ?><p class="empty-note">Hali ma'lumot yo'q.</p><?php endif; ?>
            <a href="<?= url('Popular.php') ?>" class="pop-item" style="color:#22d3ee;font-size:11px;">Hammasini ko'rish →</a>
        </div>
    </div>

    <h2 class="page-title">Yangi qo'shilganlar</h2>
    <div class="cards-container">
        <?php foreach ($mangas as $manga) render_manga_card($manga); ?>
        <?php if (!$mangas): ?><p class="empty-note">Hozircha mangalar qo'shilmagan.</p><?php endif; ?>
    </div>
    <?php if ($mangas): ?>
        <p style="text-align:center;margin-top:30px;"><a class="page-btn" href="<?= url('Katalog.php') ?>">Butun katalogni ko'rish →</a></p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
