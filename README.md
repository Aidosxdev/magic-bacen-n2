# Magic Manga — o'rnatish va tuzilma

## Birinchi ishga tushirish
1. **Eski parollarni almashtiring** (baza paroli va Google client secret oldin ochiq fayllarda turgan edi).
2. `config.local.php.example` ni `config.local.php` nomi bilan nusxalab, qiymatlarni to'ldiring.
3. `database/schema.sql` ni tekshiring (mavjud bazaga faqat oxiridagi `ALTER` larni qo'llang).
4. `assets/avatars/` va `images/uploads/` papkalariga serverda yozish huquqi bering (`chmod 755`, egasi — veb-server foydalanuvchisi).
5. Ixtiyoriy: `assets/video/anime-bg.mp4` ni qo'ying (login/sozlamalar fon videosi). Bo'lmasa fon oddiy rangda qoladi.

## Tuzilma
- `includes/` — yadro (`config`, `db`, `functions`, `head`, `menu`, `footer`). Brauzerdan yopilgan (`.htaccess`).
- Har bir sahifa: `functions.php` → `db.php` → ma'lumot → `head.php` → `menu.php` → kontent → `footer.php`.
- Barcha havolalar ildizga nisbatan (`url('...')` yoki `/...`) — istalgan papkadan to'g'ri ishlaydi.
- Yangi sahifa qo'shish: `Katalog.php` ni namuna qilib oling.

## Xavfsizlik qoidalari
- HTML ga chiqarish: faqat `e($x)`. SQL: faqat `prepare()` + `execute([...])`.
- POST forma: `<?= csrf_field() ?>` + `csrf_valid()` tekshiruvi. AJAX: `X-CSRF-Token` sarlavhasi (`MM.csrf`).
- Kirish/ro'yxatdan o'tishdan keyin `login_user()` (sessiyani yangilaydi).
- To'lov: balans faqat `payment/webhook.php` (imzo tekshiruvi bilan) orqali. `payment_demo_mode` ishlab chiqarishda `false`.
