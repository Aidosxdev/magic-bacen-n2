/* Magic Manga — yagona JS. Barcha sahifalarga includes/footer.php orqali ulanadi. */
(function () {
    'use strict';

    const MM = window.MM || { csrf: '' };
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* ---------- Menyu va profil oynasi ---------- */
    function toggleMenu() {
        const menu = $('#sideMenu'), bg = $('#overlayBg');
        if (!menu || !bg) return;
        menu.classList.toggle('active');
        bg.style.display = menu.classList.contains('active') ? 'block' : 'none';
    }

    function toggleProfile() {
        const modal = $('#profileModal');
        if (!modal) return;
        modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
    }

    // Eski kodlar bilan moslik uchun global qilib qo'yamiz
    window.toggleMenu = toggleMenu;
    window.toggleProfile = toggleProfile;

    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-action]');
        if (!el) return;
        const action = el.dataset.action;
        if (action === 'toggle-menu') toggleMenu();
        else if (action === 'toggle-profile') toggleProfile();
        else if (action === 'close-profile-overlay' && e.target === el) toggleProfile();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const modal = $('#profileModal');
        if (modal && modal.style.display === 'flex') toggleProfile();
        const menu = $('#sideMenu');
        if (menu && menu.classList.contains('active')) toggleMenu();
    });

    /* ---------- Bosh sahifa slayderi ---------- */
    function initCarousel() {
        const track = $('#track');
        if (!track) return;
        const count = $$('.hero-slide', track).length;
        if (count < 2) return;
        let idx = 0, timer = null;
        const go = (i) => {
            idx = (i + count) % count;
            track.style.transform = 'translateX(-' + idx * 100 + '%)';
        };
        const start = () => { timer = setInterval(() => go(idx + 1), 5000); };
        const stop = () => { clearInterval(timer); };
        start();
        const box = track.parentElement;
        box.addEventListener('mouseenter', stop);
        box.addEventListener('mouseleave', start);
    }

    /* ---------- Parallax (ixtiyoriy, .parallax-box) ---------- */
    function initParallax() {
        const boxes = $$('.parallax-box');
        if (!boxes.length) return;
        document.addEventListener('mousemove', (e) => {
            const mx = e.clientX / window.innerWidth - 0.5;
            const my = e.clientY / window.innerHeight - 0.5;
            boxes.forEach((box) => {
                const speed = parseFloat(box.getAttribute('data-speed')) || 20;
                box.style.transform = 'translate(' + (-mx * speed) + 'px,' + (-my * speed) + 'px)';
            });
        });
    }

    /* ---------- Serverga so'rov yuboruvchi yordamchi ---------- */
    function postJSON(path, payload) {
        return fetch(path, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': MM.csrf },
            body: JSON.stringify(payload)
        }).then((r) => r.json().then((data) => ({ ok: r.ok, status: r.status, data })));
    }

    /* ---------- O'qish jarayonini saqlash ---------- */
    function saveProgress(mangaId, chapterId, lastPage, progress, silent) {
        return postJSON('/api/save_progress.php', {
            manga_id: mangaId, chapter_id: chapterId, last_page: lastPage, progress: progress
        }).then((res) => {
            if (!silent) alert(res.ok ? "O'qish jarayoni saqlandi!" : 'Avval tizimga kiring.');
            return res;
        }).catch(() => {});
    }
    window.saveProgress = saveProgress;

    function initReaderProgress() {
        const box = $('.manga-pages[data-chapter-id]');
        if (!box || box.dataset.loggedIn !== '1') return;
        const mangaId = parseInt(box.dataset.mangaId, 10);
        const chapterId = parseInt(box.dataset.chapterId, 10);
        const imgs = $$('img', box);
        let timer = null, lastSent = -1;

        function currentState() {
            const total = document.documentElement.scrollHeight - window.innerHeight;
            const progress = total > 0 ? Math.min(100, Math.round((window.scrollY / total) * 100)) : 100;
            let page = 1;
            imgs.forEach((img, i) => { if (img.getBoundingClientRect().top < window.innerHeight / 2) page = i + 1; });
            return { progress, page };
        }
        function send() {
            const s = currentState();
            if (s.progress === lastSent) return;
            lastSent = s.progress;
            saveProgress(mangaId, chapterId, s.page, s.progress, true);
        }
        window.addEventListener('scroll', () => { clearTimeout(timer); timer = setTimeout(send, 1500); }, { passive: true });
        window.addEventListener('pagehide', send);
    }

    /* ---------- Sevimlilar (like) tugmasi ---------- */
    function initLike() {
        const btn = $('.like-btn[data-manga-id]');
        if (!btn) return;
        btn.addEventListener('click', () => {
            if (btn.dataset.loggedIn !== '1') { window.location.href = '/auth/login.php'; return; }
            btn.disabled = true;
            postJSON('/api/toggle_like.php', { manga_id: parseInt(btn.dataset.mangaId, 10) })
                .then((res) => {
                    if (res.ok && res.data.status === 'ok') {
                        btn.classList.toggle('liked', !!res.data.liked);
                        btn.textContent = res.data.liked ? "♥ Tanlanganlarda" : "♡ Tanlanganlarga qo'shish";
                    }
                })
                .catch(() => {})
                .finally(() => { btn.disabled = false; });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
        initCarousel();
        initParallax();
        initReaderProgress();
        initLike();
    });
})();
