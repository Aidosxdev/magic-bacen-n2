<?php
/**
 * Yuqori menyu + yon menyu + profil oynasi.
 * Barcha havolalar ildizga nisbatan (/...), shuning uchun istalgan papkadan ishlaydi.
 */
require_once __DIR__ . '/functions.php';
if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}
$menuUser = current_user($pdo);
$menuName = $menuUser['username'] ?? ($_SESSION['username'] ?? 'Hisob');
$current  = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isActive = fn(string $file) => $current === $file ? 'active' : '';
?>
<header class="site-header">
    <a href="<?= url('index.php') ?>" class="logo">
        <img src="<?= url('images/logo.png') ?>" alt="Magic Manga"> Magic Manga
    </a>

    <div class="header-right">
        <nav class="nav-links" aria-label="Asosiy menyu">
            <a href="<?= url('index.php') ?>" class="<?= $isActive('index.php') ?>">Bosh sahifa</a>
            <a href="<?= url('Popular.php') ?>" class="<?= $isActive('Popular.php') ?>">Mashhur</a>
            <a href="<?= url('Katalog.php') ?>" class="<?= $isActive('Katalog.php') ?>">Katalog</a>
        </nav>

        <form class="search-box" action="<?= url('Katalog.php') ?>" method="get" role="search">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" name="q" placeholder="Qidiruv..." maxlength="100"
                   value="<?= e($_GET['q'] ?? '') ?>" aria-label="Qidiruv">
        </form>

        <div class="nav-links">
            <button type="button" data-action="toggle-profile"><?= e($menuName) ?></button>
            <button type="button" data-action="toggle-menu" aria-label="Menyu">
                <svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>
</header>

<!-- Profil oynasi -->
<div class="modal-overlay" id="profileModal" data-action="close-profile-overlay">
    <div class="profile-card" role="dialog" aria-label="Profil">
        <button type="button" class="close-modal" data-action="toggle-profile" aria-label="Yopish">✕</button>
        <?php if ($menuUser): ?>
            <div class="profile-avatar"><img src="<?= e(asset_url($menuUser['avatar'] ?? '', 'images/default-avatar.png')) ?>" alt=""></div>
            <h3 class="profile-name"><?= e($menuUser['username']) ?></h3>
            <p style="font-size:10px;color:#555;margin-bottom:10px;">Manga Reader | Level <?= (int)($menuUser['level'] ?? 1) ?></p>
            <div class="balance-box">
                <span style="font-size:14px;color:#22d3ee;font-weight:bold;"><?= (int)($menuUser['balance'] ?? 0) ?> COINS</span>
                <a href="<?= url('payment/payment.php') ?>" class="btn-shop">To'ldirish</a>
            </div>
            <div class="profile-links">
                <a href="<?= url('profile/index.php') ?>" class="social-btn">Mening profilim</a>
                <a href="<?= url('profile/settings.php') ?>" class="social-btn">Sozlamalar</a>
                <form action="<?= url('auth/logout.php') ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-logout">Chiqish</button>
                </form>
            </div>
        <?php else: ?>
            <div class="profile-avatar"><img src="<?= url('images/default-avatar.png') ?>" alt=""></div>
            <h3 class="profile-name">Mehmon</h3>
            <p style="font-size:11px;color:#888;margin:10px 0 18px;">O'qish jarayonini saqlash va sevimlilar uchun kiring.</p>
            <a href="<?= url('auth/login.php') ?>" class="social-btn">Kirish</a>
            <a href="<?= url('auth/register.php') ?>" class="social-btn">Ro'yxatdan o'tish</a>
        <?php endif; ?>
    </div>
</div>

<!-- Yon menyu -->
<aside class="side-menu" id="sideMenu">
    <div style="display:flex;justify-content:space-between;margin-bottom:30px;">
        <span style="font-size:12px;color:#22d3ee;">MENYU</span>
        <button type="button" data-action="toggle-menu" style="background:none;border:none;color:white;cursor:pointer;" aria-label="Yopish">✕</button>
    </div>
    <div class="side-menu-links">
        <a href="<?= url('index.php') ?>">Bosh sahifa</a>
        <a href="<?= url('arxiv.php') ?>">Tanlanganlar</a>
        <a href="<?= url('Popular.php') ?>">Mashhur</a>
        <a href="<?= url('Katalog.php') ?>">Katalog</a>
        <a href="<?= url('profile/index.php') ?>">Profil</a>
        <a href="<?= url('terms.php') ?>">Qoidalar</a>
        <a href="<?= url('faq.php') ?>">Ko'p so'raladigan savollar</a>
    </div>
</aside>
<div class="overlay-bg" id="overlayBg" data-action="toggle-menu"></div>
