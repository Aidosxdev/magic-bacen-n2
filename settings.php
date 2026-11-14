<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_login('/auth/login.php');

$user_id     = current_user_id();
$success_msg = '';
$error_msg   = '';

$user = current_user($pdo);
if (!$user) {
    $_SESSION = [];
    redirect('/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim((string)($_POST['username'] ?? ''));
    $new_password = (string)($_POST['new_password'] ?? '');
    $avatar_path  = $user['avatar'];
    $old_avatar   = $user['avatar'];
    $uploaded     = false;

    if (!csrf_valid()) {
        $error_msg = "Sessiya eskirdi. Sahifani yangilab qayta urinib ko'ring.";
    } elseif (!preg_match('/^[\p{L}\p{N}_.\- ]{3,30}$/u', $new_username)) {
        $error_msg = "Foydalanuvchi nomi 3–30 belgi bo'lib, harf, raqam, nuqta, tire yoki pastki chiziqdan iborat bo'lsin.";
    } elseif ($new_password !== '' && (strlen($new_password) < 8 || strlen($new_password) > 72)) {
        $error_msg = "Yangi parol 8–72 belgidan iborat bo'lishi kerak.";
    }

    if ($error_msg === '' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $avatar_path = save_avatar_upload($_FILES['avatar'], $user_id);
            $uploaded = true;
        } catch (RuntimeException $e) {
            $error_msg = $e->getMessage();
        }
    }

    if ($error_msg === '') {
        try {
            if ($new_password !== '') {
                $upd = $pdo->prepare('UPDATE users SET username = ?, password = ?, avatar = ? WHERE id = ?');
                $upd->execute([$new_username, password_hash($new_password, PASSWORD_DEFAULT), $avatar_path, $user_id]);
            } else {
                $upd = $pdo->prepare('UPDATE users SET username = ?, avatar = ? WHERE id = ?');
                $upd->execute([$new_username, $avatar_path, $user_id]);
            }
            $_SESSION['username'] = $new_username;
            $user['username'] = $new_username;
            $user['avatar'] = $avatar_path;
            $success_msg = "Profil sozlamalari muvaffaqiyatli saqlandi!";

            // Eski, serverda saqlangan avatarni o'chiramiz (faqat o'zimizning papkadan)
            if ($uploaded && $old_avatar && str_starts_with($old_avatar, 'assets/avatars/')) {
                $old = dirname(__DIR__) . '/assets/avatars/' . basename($old_avatar);
                if (is_file($old)) {
                    @unlink($old);
                }
            }
        } catch (PDOException $e) {
            error_log('[settings] ' . $e->getMessage());
            $error_msg = $e->getCode() === '23000'
                ? "Bu foydalanuvchi nomi band."
                : "Saqlashda xatolik yuz berdi. Keyinroq urinib ko'ring.";
        }
    }
}

$avatarUrl = asset_url($user['avatar'] ?? '', 'assets/logo.jpg');
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sozlamalari - Magic Manga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .pubg-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 41, 59, 0.8) 100%);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(245, 158, 11, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Orqa fon anime video -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <video autoplay muted loop playsinline class="w-full h-full object-cover scale-105 filter brightness-[0.25] contrast-125">
            <source src="../assets/video/anime-bg.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-slate-950/85"></div>
    </div>

    <div class="w-full max-w-md pubg-card rounded-3xl p-6 sm:p-8 relative z-10">
        <!-- Sarlavha va Orqaga qaytish -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2 text-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h1 class="text-base font-black uppercase tracking-wider">Sozlamalar</h1>
            </div>
            <a href="index.php" class="px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-xs font-bold text-slate-300 hover:text-white transition">
                ← Profilga qaytish
            </a>
        </div>

        <?php if ($success_msg): ?>
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold text-center">
                <?= e($success_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-bold text-center">
                <?= e($error_msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <!-- Avatar ko'rinishi va yuklash -->
            <div class="flex flex-col items-center gap-3 pb-2">
                <div class="w-24 h-24 rounded-2xl p-1 bg-gradient-to-tr from-amber-500 to-red-500 shadow-lg">
                    <img id="avatarPreview" src="<?= e($avatarUrl) ?>" alt="Avatar" class="w-full h-full object-cover rounded-[14px] bg-slate-900">
                </div>
                <label class="cursor-pointer px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-amber-500/30 text-amber-400 text-xs font-bold transition">
                    Rasmni o'zgartirish
                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewImage(event)">
                </label>
            </div>

            <div>
                <label class="block text-[10px] font-black text-amber-400/80 uppercase tracking-widest mb-1.5">Foydalanuvchi nomi</label>
                <input type="text" name="username" required value="<?= e($user['username']) ?>" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:border-amber-400 text-sm transition">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Email (O'zgartirib bo'lmaydi)</label>
                <input type="email" disabled value="<?= e($user['email']) ?>" class="w-full bg-slate-900/40 border border-slate-800 rounded-xl px-4 py-3 text-slate-500 text-sm cursor-not-allowed">
            </div>

            <div>
                <label class="block text-[10px] font-black text-amber-400/80 uppercase tracking-widest mb-1.5">Yangi parol (ixtiyoriy)</label>
                <input type="password" name="new_password" placeholder="O'zgartirish uchun yangi parol yozing" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-400 text-sm transition">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/30 transition active:scale-95">
                O'zgarishlarni saqlash
            </button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById('avatarPreview').src = reader.result;
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>