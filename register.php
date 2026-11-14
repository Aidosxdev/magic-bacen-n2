<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (current_user_id() > 0) {
    redirect('/profile/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm_password'] ?? '');

    if (!csrf_valid()) {
        $error = "Sessiya eskirdi. Sahifani yangilab qayta urinib ko'ring.";
    } elseif ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = "Barcha maydonlarni to'ldiring!";
    } elseif (!preg_match('/^[\p{L}\p{N}_.\- ]{3,30}$/u', $username)) {
        $error = "Foydalanuvchi nomi 3–30 belgi bo'lib, harf, raqam, nuqta, tire yoki pastki chiziqdan iborat bo'lsin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $error = "Email manzili noto'g'ri.";
    } elseif (strlen($password) < 8) {
        $error = "Parol kamida 8 ta belgidan iborat bo'lsin.";
    } elseif (strlen($password) > 72) {
        $error = "Parol 72 belgidan oshmasligi kerak.";
    } elseif ($password !== $confirm) {
        $error = "Parollar mos kelmadi!";
    } elseif (login_throttled($pdo)) {
        $error = "Juda ko'p urinish. Keyinroq qayta urinib ko'ring.";
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $error = "Bu email yoki foydalanuvchi nomi allaqachon band qilingan!";
            } else {
                $insert = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                $insert->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
                login_user((int)$pdo->lastInsertId(), $username);
                redirect('/profile/index.php');
            }
        } catch (PDOException $e) {
            error_log('[register] ' . $e->getMessage());
            // 23000 = UNIQUE buzilishi (ikki so'rov bir vaqtda kelgan bo'lishi mumkin)
            $error = $e->getCode() === '23000'
                ? "Bu email yoki foydalanuvchi nomi allaqachon band qilingan!"
                : "Ro'yxatdan o'tishda xatolik yuz berdi. Keyinroq urinib ko'ring.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish - Magic Manga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { perspective: 1000px; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(56, 189, 248, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(36, 123, 255, 0.25);
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out;
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        .glow-orb { animation: pulseGlow 6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen relative flex items-center justify-center p-4">

    <!-- Orqa fondagi Anime Video -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <video autoplay muted loop playsinline class="w-full h-full object-cover scale-105 filter brightness-50 contrast-110">
            <source src="../assets/video/anime-bg.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-[2px]"></div>
    </div>

    <!-- Orqa fondagi yorug'lik nurlari -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/25 rounded-full blur-3xl pointer-events-none glow-orb z-0"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cyan-500/25 rounded-full blur-3xl pointer-events-none glow-orb z-0" style="animation-delay: 3s;"></div>

    <!-- Asosiy Forma Paneli -->
    <div id="parallaxCard" class="w-full max-w-md glass-panel rounded-3xl p-6 sm:p-8 relative z-10 my-auto">
        
        <div class="text-center mb-5">
            <a href="../index.php" class="inline-block mb-2 transform hover:scale-110 transition-transform duration-300">
                <img src="../assets/logo.jpg" alt="Logo" class="w-12 h-12 rounded-2xl mx-auto object-cover border-2 border-cyan-400/50 shadow-lg shadow-cyan-500/40">
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Hisob ochish</h1>
            <p class="text-slate-300 text-xs mt-0.5">Magic Manga oilasiga qo'shiling</p>
        </div>

        <!-- Login / Register Tabs -->
        <div class="flex rounded-xl bg-slate-900/80 p-1 mb-5 border border-slate-700/60 shadow-inner">
            <a href="login.php" class="flex-1 text-center py-2 text-xs font-bold rounded-lg text-slate-300 hover:text-white transition">Kirish</a>
            <a href="register.php" class="flex-1 text-center py-2 text-xs font-bold rounded-lg bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-md transition">Ro'yxatdan o'tish</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-2.5 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs text-center">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-3">
            <?= csrf_field() ?>
            <div>
                <label class="block text-[10px] font-bold text-cyan-300 uppercase tracking-wider mb-1">Foydalanuvchi nomi</label>
                <input type="text" name="username" required placeholder="Javohir" class="w-full bg-slate-900/70 border border-slate-700/80 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30 transition-all text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-300 uppercase tracking-wider mb-1">Email manzil</label>
                <input type="email" name="email" required placeholder="example@mail.com" class="w-full bg-slate-900/70 border border-slate-700/80 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30 transition-all text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-300 uppercase tracking-wider mb-1">Parol</label>
                <input type="password" id="passwordInput" name="password" required placeholder="••••••••" class="w-full bg-slate-900/70 border border-slate-700/80 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30 transition-all text-sm">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-cyan-300 uppercase tracking-wider mb-1">Parolni tasdiqlang</label>
                <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full bg-slate-900/70 border border-slate-700/80 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30 transition-all text-sm">
            </div>
            
            <!-- Shartli chiqadigan tugma -->
            <button type="submit" id="submitBtn" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 active:scale-[0.98] text-white font-bold transition-all duration-300 shadow-lg shadow-cyan-500/30 text-sm mt-2 opacity-0 pointer-events-none transform scale-95">
                Ro'yxatdan o'tish
            </button>
        </form>

        <!-- Google va Telegram orqali kirish -->
        <div class="mt-4">
            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-slate-700/60"></div>
                <span class="flex-shrink mx-3 text-[10px] uppercase tracking-wider text-slate-400 font-semibold">yoki ijtimoiy tarmoqlar</span>
                <div class="flex-grow border-t border-slate-700/60"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
                <a href="google-login.php" class="flex items-center justify-center gap-2 py-2 px-4 rounded-xl bg-slate-900/70 hover:bg-slate-800 active:scale-95 border border-slate-700/80 text-slate-200 text-xs font-semibold transition shadow-sm hover:border-cyan-400/50">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.6l3.1-3.1C17.3 1.8 14.8 1 12 1 7.4 1 3.5 3.6 1.6 7.4l3.7 2.9C6.2 7.2 8.9 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                        <path fill="#FBBC05" d="M5.3 14.7c-.2-.7-.4-1.5-.4-2.7s.2-2 .4-2.7L1.6 6.4C.6 8.4 0 10.6 0 13s.6 4.6 1.6 6.6l3.7-2.9z"/>
                        <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3.1 0-5.8-2.2-6.7-5.3L1.6 16c1.9 3.8 5.8 7 10.4 7z"/>
                    </svg>
                    Google
                </a>
                
                <div id="telegramLoginButton" class="flex items-center justify-center"></div>
            </div>
        </div>

    </div>

    <!-- Telegram Widget skripti -->
    <?php if (config('telegram_bot_username')): ?>
    <script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="<?= e(config('telegram_bot_username')) ?>" data-size="medium" data-auth-url="<?= e(rtrim(config('site_url', 'https://magicmanga.uz'), '/')) ?>/auth/telegram-callback.php" data-request-access="write"></script>
    <?php endif; ?>

    <script>
        const card = document.getElementById('parallaxCard');
        
        // 3D Tilt effekt
        document.addEventListener('mousemove', (e) => {
            if (!card) return;
            const xAxis = (window.innerWidth / 2 - e.clientX) / 30;
            const yAxis = (window.innerHeight / 2 - e.clientY) / 30;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg) scale3d(1.01, 1.01, 1.01)`;
        });

        document.addEventListener('mouseleave', () => {
            if (!card) return;
            card.style.transform = `rotateY(0deg) rotateX(0deg) scale3d(1, 1, 1)`;
        });

        // Parol kiritilganda tugmani animatsiya bilan chiqarish
        const passwordInput = document.getElementById('passwordInput');
        const submitBtn = document.getElementById('submitBtn');

        passwordInput.addEventListener('input', () => {
            if (passwordInput.value.length > 0) {
                submitBtn.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
                submitBtn.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');
            } else {
                submitBtn.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
                submitBtn.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
            }
        });
    </script>
</body>
</html>