<?php
/**
 * Magic Manga — umumiy yordamchi funksiyalar.
 * Har bir sahifa boshida:  require_once __DIR__ . '/includes/functions.php';
 */
require_once __DIR__ . '/config.php';

/* ---------- Xatolar va xavfsizlik sarlavhalari ---------- */
if (config('debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/* ---------- Sessiya ---------- */
function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
app_session_start();

/* ---------- Chiqarish va yo'llar ---------- */
/** XSS himoyasi: HTML ga chiqariladigan hamma matn shu orqali o'tadi */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Saytning ildiziga nisbatan yo'l: url('const/reader.php?id=3') => /const/reader.php?id=3 */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** Rasm manzili: http(s) bo'lsa o'zi, yo'lni ildizga bog'laydi, bo'sh bo'lsa zaxira rasm */
function asset_url(?string $path, string $fallback = 'images/placeholder-cover.jpg'): string
{
    $path = trim((string)$path);
    if ($path === '') {
        return url($fallback);
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return url($path);
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function not_found(string $message = 'Sahifa topilmadi'): void
{
    http_response_code(404);
    $pageTitle = '404 | Magic Manga';
    require __DIR__ . '/head.php';
    echo '<div class="notice-box"><h2>' . e($message) . '</h2><a href="' . url('index.php') . '">Bosh sahifaga qaytish</a></div>';
    echo '</body></html>';
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

/** POST so'rovlarda tokenni majburiy tekshirish */
function csrf_require(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        exit('Sessiya muddati tugagan yoki so\'rov noto\'g\'ri. Sahifani yangilab qayta urinib ko\'ring.');
    }
}

/* ---------- Foydalanuvchi va autentifikatsiya ---------- */
function current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function current_user(PDO $pdo): ?array
{
    static $cache = [];
    $id = current_user_id();
    if ($id <= 0) {
        return null;
    }
    if (!array_key_exists($id, $cache)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $cache[$id] = $stmt->fetch() ?: null;
    }
    return $cache[$id];
}

function require_login(string $to = '/auth/login.php'): void
{
    if (current_user_id() <= 0) {
        redirect($to);
    }
}

/** Kirishdan keyin sessiyani yangilash (session fixation himoyasi) */
function login_user(int $id, string $username): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']  = $id;
    $_SESSION['username'] = $username;
    $_SESSION['csrf']     = bin2hex(random_bytes(32));
}

function client_ip(): string
{
    return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/** 15 daqiqada 10 tadan ko'p muvaffaqiyatsiz urinish bo'lsa true (login_attempts jadvali kerak) */
function login_throttled(PDO $pdo): bool
{
    try {
        $s = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)");
        $s->execute([client_ip()]);
        return (int)$s->fetchColumn() >= 10;
    } catch (PDOException $e) {
        return false; // jadval hali yaratilmagan bo'lsa saytni to'xtatmaymiz
    }
}

function login_record_failure(PDO $pdo): void
{
    try {
        $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)')->execute([client_ip()]);
    } catch (PDOException $e) {
    }
}

/* ---------- Fayl yuklash ---------- */
/**
 * Avatar rasmini xavfsiz saqlaydi. Muvaffaqiyatda nisbiy yo'lni qaytaradi,
 * xato bo'lsa RuntimeException tashlaydi.
 */
function save_avatar_upload(array $file, int $userId): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Rasmni yuklashda xatolik yuz berdi.');
    }
    if (($file['size'] ?? 0) > 3 * 1024 * 1024) {
        throw new RuntimeException('Rasm hajmi 3 MB dan oshmasligi kerak.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Noto\'g\'ri fayl.');
    }
    $info = @getimagesize($file['tmp_name']);
    $map  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) {
        throw new RuntimeException('Faqat JPG, PNG yoki WEBP rasm ruxsat etiladi.');
    }
    if ($info[0] > 4000 || $info[1] > 4000) {
        throw new RuntimeException('Rasm o\'lchami juda katta (maksimal 4000x4000).');
    }
    $dir = dirname(__DIR__) . '/assets/avatars/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException('Yuklash papkasi mavjud emas.');
    }
    $name = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $map[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        throw new RuntimeException('Rasmni serverga saqlab bo\'lmadi.');
    }
    @chmod($dir . $name, 0644);
    return 'assets/avatars/' . $name;
}

/* ---------- Manga so'rovlari va kartalar ---------- */
/** Manga + haqiqiy boblar soni */
const MANGA_SELECT = 'SELECT m.*, (SELECT COUNT(*) FROM chapters c WHERE c.manga_id = m.id) AS chapters_total FROM mangas m';

function render_manga_card(array $m): void
{
    $count = (int)($m['chapters_total'] ?? $m['chapters_count'] ?? 0);
    ?>
    <a href="<?= url('const/reader.php?id=' . (int)$m['id']) ?>" class="manga-card-small">
        <img src="<?= e(asset_url($m['cover_image'] ?? '')) ?>" alt="<?= e($m['title']) ?>" loading="lazy">
        <div class="card-info">
            <h2><?= e($m['title']) ?></h2>
            <div class="card-meta">Boblar: <span><?= $count ?></span> | ⭐ <?= e($m['rating'] ?? '—') ?></div>
            <div class="card-tags"><b><?= e($m['genre'] ?? 'Manga') ?></b></div>
        </div>
    </a>
    <?php
}

/** Sahifalash tugmalari: ?page=N, boshqa GET parametrlar saqlanadi */
function render_pagination(int $page, int $pages): void
{
    if ($pages <= 1) {
        return;
    }
    $link = function (int $p, string $label, string $class = '') {
        $q = $_GET;
        $q['page'] = $p;
        return '<a class="page-btn ' . $class . '" href="?' . e(http_build_query($q)) . '">' . $label . '</a>';
    };
    echo '<nav class="pagination" aria-label="Sahifalar">';
    if ($page > 1) echo $link($page - 1, '←');
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1) echo $link(1, '1') . ($start > 2 ? '<span class="page-dots">…</span>' : '');
    for ($i = $start; $i <= $end; $i++) echo $link($i, (string)$i, $i === $page ? 'active' : '');
    if ($end < $pages) echo ($end < $pages - 1 ? '<span class="page-dots">…</span>' : '') . $link($pages, (string)$pages);
    if ($page < $pages) echo $link($page + 1, '→');
    echo '</nav>';
}

/** Foydalanuvchi nomi band bo'lsa, oxiriga raqam qo'shib noyob nom beradi (OAuth uchun) */
function unique_username(PDO $pdo, string $base): string
{
    $base = trim($base) !== '' ? mb_substr(trim($base), 0, 24, 'UTF-8') : 'user';
    $name = $base;
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ?');
    for ($i = 0; $i < 5; $i++) {
        $stmt->execute([$name]);
        if (!$stmt->fetchColumn()) {
            return $name;
        }
        $name = $base . '_' . random_int(100, 9999);
    }
    return $base . '_' . bin2hex(random_bytes(3));
}
