<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foydalanish shartlari (Terms) — Magic Manga</title>
    <link rel="icon" href="/assets/logo.jpg" type="image/jpeg">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/menu.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.383.0/dist/umd/lucide.min.js"></script>
    <style>
        .box-panel {
            background: rgba(13, 19, 33, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(56, 189, 248, 0.15);
        }
        .box-card {
            background: rgba(22, 30, 49, 0.6);
            border: 1px solid rgba(56, 189, 248, 0.1);
        }
    </style>
</head>
<body class="bg-[#0b101b] text-slate-100 min-h-screen selection:bg-cyan-500 selection:text-slate-950">

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/menu.php'; ?>

    <!-- Asosiy Kontent -->
    <main class="max-w-4xl mx-auto px-4 pt-32 pb-20 space-y-8">
        
        <div class="text-center space-y-3">
            <h1 class="font-display text-3xl font-black text-white uppercase tracking-wider">Foydalanish shartlari</h1>
            <p class="text-xs text-slate-400">Magic Manga platformasidan foydalanish qoidalari va shartnomasi</p>
        </div>

        <div class="box-card rounded-3xl p-6 sm:p-8 space-y-6 text-xs text-slate-300 leading-relaxed">
            <div>
                <h2 class="text-sm font-black text-cyan-400 uppercase tracking-wider mb-2">1. Umume'tirof etilgan qoidalar</h2>
                <p>
                    Magic Manga platformasiga kirish va undan foydalanish orqali siz ushbu foydalanish shartlariga to'liq rozilik bildirasiz. Agar siz ushbu shartlarning biror qismiga rozi bo'lmasangiz, iltimos, platformadan foydalanishni to'xtating.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-black text-cyan-400 uppercase tracking-wider mb-2">2. Mualliflik huquqi va kontent</h2>
                <p>
                    Platformadagi barcha tarjimalar, dizayn elementlari va logotiplar Magic Manga jamoasiga tegishli yoki tegishli manbalardan foydalanish huquqi asosida joylashtirilgan. Materiallarni ma'muriyat ruxsatisiz tijorat maqsadlarida ko'chirish yoki tarqatish qat'iyan taqiqlanadi.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-black text-cyan-400 uppercase tracking-wider mb-2">3. Foydalanuvchi hisobvarag'i</h2>
                <p>
                    Ro'yxatdan o'tish jarayonida kiritilgan ma'lumotlarning to'g'riligi va xavfsizligi uchun foydalanuvchining o'zi mas'uldir. Shubhali harakatlar aniqlanganda ma'muriyat hisobni bloklash huquqini o'zida saqlab qoladi.
                </p>
            </div>

            <div>
                <h2 class="text-sm font-black text-cyan-400 uppercase tracking-wider mb-2">4. To'lovlar va qaytarish siyosati</h2>
                <p>
                    Platformada amalga oshirilgan balans to'ldirish va VIP xaridlar raqamli xizmat bo'lgani sababli, xarid qilingandan so'ng mablag'lar qaytarib berilmaydi.
                </p>
            </div>
        </div>

        <div class="text-center pt-2">
            <a href="/index.php" class="px-6 py-2.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs uppercase tracking-wider hover:bg-cyan-400 transition">
                ← Bosh sahifaga qaytish
            </a>
        </div>
    </main>

    <script>window.MM = { csrf: <?= json_encode(csrf_token()) ?> };</script>
    <script src="/assets/js/app.js"></script>
</body>
</html>