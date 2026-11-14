<?php
/** Pastki qism + JS. Sahifa $showFooter = false qo'yib, faqat skriptlarni olishi mumkin. */
require_once __DIR__ . '/functions.php';
$jsFile = dirname(__DIR__) . '/assets/js/app.js';
$jsVer  = is_file($jsFile) ? filemtime($jsFile) : 1;
if ($showFooter ?? true): ?>
<footer>
    <div class="footer-content">
        <div class="footer-info">
            <a href="<?= url('index.php') ?>" class="footer-logo">Magic Manga</a>
            <p class="footer-text">O'zbekistondagi eng sifatli manga va manhwa portali. Biz bilan sevimli asarlaringizni yuqori sifatda o'qing.</p>
            <div class="footer-socials">
                <a href="https://t.me/MagicManga" rel="noopener" target="_blank"><img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" alt="Telegram"></a>
                <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/e/e7/Instagram_logo_2016.svg" alt="Instagram"></a>
                <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp"></a>
            </div>
        </div>

        <div class="footer-team">
            <h4>Jamoa</h4>
            <div class="team-grid">
                <div class="team-item"><b>Asoschi:</b> Emperor</div>
                <div class="team-item"><b>Manager:</b> Falcon</div>
                <div class="team-item"><b>Dasturchi:</b> Arkenyo</div>
                <div class="team-item"><b>Dizayner:</b> Galarx</div>
            </div>
        </div>

        <div class="footer-team">
            <h4>Yordam</h4>
            <div class="team-grid">
                <div class="team-item">Reklama</div>
                <div class="team-item"><a href="<?= url('terms.php') ?>" style="color:inherit;text-decoration:none;">Qoidalar</a></div>
                <div class="team-item"><a href="<?= url('faq.php') ?>" style="color:inherit;text-decoration:none;">FAQ</a></div>
                <div class="team-item"><a href="https://t.me/MagicManga" style="color:inherit;text-decoration:none;">Aloqa</a></div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Magic Manga. Barcha huquqlar himoyalangan. | Vizual dizayn: Galarx
    </div>
</footer>
<?php endif; ?>

<script>window.MM = { csrf: <?= json_encode(csrf_token()) ?>, base: '' };</script>
<script src="<?= url('assets/js/app.js') ?>?v=<?= (int)$jsVer ?>"></script>
</body>
</html>
