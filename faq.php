<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
?>
<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ko'p so'raladigan savollar (FAQ) — Magic Manga</title>
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
            <h1 class="font-display text-3xl font-black text-white uppercase tracking-wider">Ko'p so'raladigan savollar</h1>
            <p class="text-xs text-slate-400">Magic Manga platformasidan foydalanish bo'yicha eng ko'p beriladigan savollar va javoblar</p>
        </div>

        <div class="space-y-4">
            <details class="box-card rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm text-white flex justify-between items-center">
                    <span>1. Manga va manhwalarni o'qish mutlaqo bepulmi?</span>
                    <span class="text-cyan-400 group-open:rotate-180 transition">▼</span>
                </summary>
                <p class="mt-3 text-xs text-slate-300 leading-relaxed">
                    Ha, Magic Manga platformasidagi minglab asarlar va boblar mutlaqo bepul taqdim etiladi. Ba'zi eksklyuziv yoki yangi chiqqan premyum boblarni ochish uchun esa platform ichidagi Crystal (balans) talab qilinishi mumkin.
                </p>
            </details>

            <details class="box-card rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm text-white flex justify-between items-center">
                    <span>2. Balansni (Crystal) qanday to'ldirish mumkin?</span>
                    <span class="text-cyan-400 group-open:rotate-180 transition">▼</span>
                </summary>
                <p class="mt-3 text-xs text-slate-300 leading-relaxed">
                    Profil sahifangizga o'tib, "Pro olish" yoki "Balansni to'ldirish" tugmasini bosish orqali to'lov sahifasiga o'tishingiz va o'zingizga qulay usulda hisobingizni to'ldirishingiz mumkin.
                </p>
            </details>

            <details class="box-card rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm text-white flex justify-between items-center">
                    <span>3. O'qish tarixi saqlanib qoladimi?</span>
                    <span class="text-cyan-400 group-open:rotate-180 transition">▼</span>
                </summary>
                <p class="mt-3 text-xs text-slate-300 leading-relaxed">
                    Agar siz o'z profilingizga kirgan (tizimga kirish qilgan) bo'lsangiz, o'qigan barcha boblaringiz va qayerda to'xtaganingiz avtomatik ravishda profilingizdagi "Davom ettirish" bo'limida saqlanib boradi.
                </p>
            </details>

            <details class="box-card rounded-2xl p-5 cursor-pointer group">
                <summary class="font-bold text-sm text-white flex justify-between items-center">
                    <span>4. O'z tarjimalarimizni yuklay olamizmi?</span>
                    <span class="text-cyan-400 group-open:rotate-180 transition">▼</span>
                </summary>
                <p class="mt-3 text-xs text-slate-300 leading-relaxed">
                    Hozircha tarjimalar faqat maxsus admin va tarjimonlar jamoasi tomonidan amalga oshiriladi. Kelgusida jamoamizga qo'shilish uchun tez orada maxsus ariza topshirish funksiyasi qo'shiladi.
                </p>
            </details>
        </div>

        <div class="text-center pt-6">
            <a href="/index.php" class="px-6 py-2.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs uppercase tracking-wider hover:bg-cyan-400 transition">
                ← Bosh sahifaga qaytish
            </a>
        </div>
    </main>

    <script>window.MM = { csrf: <?= json_encode(csrf_token()) ?> };</script>
    <script src="/assets/js/app.js"></script>
</body>
</html>