-- Magic Manga — baza sxemasi (yangi o'rnatish uchun).
-- Mavjud bazangiz bo'lsa, faqat pastdagi "MAVJUD BAZA UCHUN" bo'limini ishlating.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL,
    email      VARCHAR(190) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    avatar     VARCHAR(500) NULL,
    balance    INT UNSIGNED NOT NULL DEFAULT 0,
    level      INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mangas (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    description TEXT NULL,
    cover_image VARCHAR(500) NULL,
    genre       VARCHAR(100) NULL,
    rating      DECIMAL(3,1) NOT NULL DEFAULT 0,
    views       INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_mangas_rating (rating),
    KEY idx_mangas_views (views)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapters (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    manga_id       INT UNSIGNED NOT NULL,
    chapter_number DECIMAL(8,2) NOT NULL,
    title          VARCHAR(255) NULL,
    images         LONGTEXT NULL,                -- ixtiyoriy: ["images/..jpg", ...] JSON
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_chapters_manga (manga_id, chapter_number),
    CONSTRAINT fk_chapters_manga FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chapter_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapter_id  INT UNSIGNED NOT NULL,
    page_number INT UNSIGNED NOT NULL,
    image_path  VARCHAR(500) NOT NULL,
    KEY idx_images_chapter (chapter_id, page_number),
    CONSTRAINT fk_images_chapter FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reading_progress (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    manga_id         INT UNSIGNED NOT NULL,
    chapter_id       INT UNSIGNED NOT NULL,
    last_page        INT UNSIGNED NOT NULL DEFAULT 1,
    progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_progress_user_manga (user_id, manga_id)   -- ON DUPLICATE KEY UPDATE shu indeksga tayanadi
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manga_likes (
    user_id    INT UNSIGNED NOT NULL,
    manga_id   INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, manga_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    transaction_id VARCHAR(64) NOT NULL,
    amount         INT UNSIGNED NOT NULL,
    method         VARCHAR(20) NOT NULL,
    status         ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    paid_at        DATETIME NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payments_tx (transaction_id),
    KEY idx_payments_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip         VARCHAR(45) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_attempts_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* =====================  MAVJUD BAZA UCHUN (kerak bo'lganlarini bittadan ishga tushiring) =====================
-- 1) Rate-limit jadvali (yuqoridagi login_attempts CREATE TABLE ni ishlating)
-- 2) Mashhurlik tartibi uchun:
ALTER TABLE mangas ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0;
-- 3) O'qish jarayoni saqlanishi uchun (avval takroriy qatorlar bo'lsa, ularni tozalang):
ALTER TABLE reading_progress ADD UNIQUE KEY uq_progress_user_manga (user_id, manga_id);
-- 4) Takroriy ro'yxatdan o'tishdan himoya:
ALTER TABLE users ADD UNIQUE KEY uq_users_email (email), ADD UNIQUE KEY uq_users_username (username);
-- 5) To'lov tranzaksiyasi takrorlanmasligi uchun:
ALTER TABLE payments ADD UNIQUE KEY uq_payments_tx (transaction_id);
-- 6) Google/Telegram avatar havolalari uzun bo'ladi:
ALTER TABLE users MODIFY avatar VARCHAR(500) NULL;
============================================================================================================= */
