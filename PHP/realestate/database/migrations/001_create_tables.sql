-- =========================================================
--  NextGen Real Estate – Master Database Schema
--  Engine: InnoDB | Charset: utf8mb4 | Collation: unicode_ci
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `realestate_db`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `realestate_db`;

-- ─────────────────────────────────────────────────────────
-- 1. ROLES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`         TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)      NOT NULL UNIQUE,  -- admin | agent | buyer
    `label`      VARCHAR(100)     NOT NULL,
    `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT IGNORE INTO `roles` (`name`, `label`) VALUES
    ('admin', 'Administrator'),
    ('agent', 'Real Estate Agent'),
    ('buyer', 'Buyer / Tenant');

-- ─────────────────────────────────────────────────────────
-- 2. USERS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `role_id`           TINYINT UNSIGNED NOT NULL DEFAULT 3,     -- buyer by default
    `name`              VARCHAR(150)     NOT NULL,
    `email`             VARCHAR(191)     NOT NULL UNIQUE,
    `phone`             VARCHAR(20)      NULL,
    `password`          VARCHAR(255)     NOT NULL,
    `avatar`            VARCHAR(255)     NULL,
    `bio`               TEXT             NULL,
    `address`           VARCHAR(255)     NULL,
    `otp_code`          VARCHAR(10)      NULL,
    `otp_expires_at`    DATETIME         NULL,
    `email_verified_at` DATETIME         NULL,
    `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
    `last_login_at`     DATETIME         NULL,
    `last_login_ip`     VARCHAR(45)      NULL,
    `remember_token`    VARCHAR(100)     NULL,
    `lang`              VARCHAR(5)       NOT NULL DEFAULT 'en',
    `created_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME         NULL,
    PRIMARY KEY (`id`),
    KEY `idx_users_email`    (`email`),
    KEY `idx_users_role`     (`role_id`),
    KEY `idx_users_active`   (`is_active`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 3. PROPERTIES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `properties` (
    `id`               BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `agent_id`         BIGINT UNSIGNED   NOT NULL,
    `title`            VARCHAR(255)      NOT NULL,
    `slug`             VARCHAR(300)      NOT NULL UNIQUE,
    `description`      LONGTEXT          NOT NULL,
    `type`             ENUM('apartment','house','commercial','land','villa','office') NOT NULL DEFAULT 'apartment',
    `status`           ENUM('sale','rent','sold','pending') NOT NULL DEFAULT 'sale',
    `approval_status`  ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `price`            DECIMAL(18,2)     NOT NULL,
    `area_sqft`        DECIMAL(10,2)     NULL,
    `bedrooms`         TINYINT UNSIGNED  NULL,
    `bathrooms`        TINYINT UNSIGNED  NULL,
    `floors`           TINYINT UNSIGNED  NULL,
    `parking`          TINYINT(1)        NOT NULL DEFAULT 0,
    `furnished`        TINYINT(1)        NOT NULL DEFAULT 0,
    `address`          VARCHAR(255)      NOT NULL,
    `city`             VARCHAR(100)      NOT NULL,
    `area`             VARCHAR(100)      NOT NULL,
    `division`         VARCHAR(100)      NOT NULL,
    `zip_code`         VARCHAR(20)       NULL,
    `latitude`         DECIMAL(10,7)     NULL,
    `longitude`        DECIMAL(10,7)     NULL,
    `is_featured`      TINYINT(1)        NOT NULL DEFAULT 0,
    `featured_until`   DATETIME          NULL,
    `views`            BIGINT UNSIGNED   NOT NULL DEFAULT 0,
    `meta_title`       VARCHAR(255)      NULL,
    `meta_description` VARCHAR(500)      NULL,
    `youtube_url`      VARCHAR(500)      NULL,
    `virtual_tour_url` VARCHAR(500)      NULL,
    `created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME          NULL,
    PRIMARY KEY (`id`),
    KEY `idx_prop_slug`     (`slug`),
    KEY `idx_prop_price`    (`price`),
    KEY `idx_prop_city`     (`city`),
    KEY `idx_prop_area`     (`area`),
    KEY `idx_prop_type`     (`type`),
    KEY `idx_prop_status`   (`status`),
    KEY `idx_prop_approval` (`approval_status`),
    KEY `idx_prop_featured` (`is_featured`),
    KEY `idx_prop_agent`    (`agent_id`),
    CONSTRAINT `fk_prop_agent` FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 4. PROPERTY IMAGES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `property_images` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `file_name`   VARCHAR(255)    NOT NULL,
    `original`    VARCHAR(255)    NOT NULL,   -- original filename
    `thumbnail`   VARCHAR(255)    NULL,
    `webp`        VARCHAR(255)    NULL,
    `is_primary`  TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pimg_property` (`property_id`),
    CONSTRAINT `fk_pimg_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 5. PROPERTY VIDEOS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `property_videos` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `file_name`   VARCHAR(255)    NULL,
    `youtube_url` VARCHAR(500)    NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_pvid_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 6. WISHLIST
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_wishlist` (`user_id`, `property_id`),
    CONSTRAINT `fk_wl_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_wl_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 7. INQUIRIES (Buyer ↔ Agent messaging)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `sender_id`   BIGINT UNSIGNED NULL,       -- null = guest inquiry
    `name`        VARCHAR(150)    NOT NULL,
    `email`       VARCHAR(191)    NOT NULL,
    `phone`       VARCHAR(20)     NULL,
    `message`     TEXT            NOT NULL,
    `status`      ENUM('new','read','replied','closed') NOT NULL DEFAULT 'new',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_inq_property` (`property_id`),
    KEY `idx_inq_status`   (`status`),
    CONSTRAINT `fk_inq_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inq_sender`   FOREIGN KEY (`sender_id`)   REFERENCES `users`(`id`)      ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 8. REVIEWS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`        ENUM('property','agent') NOT NULL DEFAULT 'property',
    `target_id`   BIGINT UNSIGNED NOT NULL,   -- property_id or agent user_id
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `rating`      TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title`       VARCHAR(255)    NULL,
    `body`        TEXT            NOT NULL,
    `is_approved` TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rev_target` (`type`, `target_id`),
    CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 9. BOOKINGS (Schedule a Tour)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id`  BIGINT UNSIGNED NOT NULL,
    `user_id`      BIGINT UNSIGNED NULL,
    `name`         VARCHAR(150)    NOT NULL,
    `email`        VARCHAR(191)    NOT NULL,
    `phone`        VARCHAR(20)     NOT NULL,
    `visit_date`   DATE            NOT NULL,
    `visit_time`   TIME            NOT NULL,
    `message`      TEXT            NULL,
    `status`       ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_book_property` (`property_id`),
    KEY `idx_book_date`     (`visit_date`),
    KEY `idx_book_status`   (`status`),
    CONSTRAINT `fk_book_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_book_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 10. MATERIAL RATES (Construction Cost Estimator)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `material_rates` (
    `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)      NOT NULL,   -- Cement, Bricks, Sand, Rod, Labor
    `unit`       VARCHAR(30)       NOT NULL,   -- bag, piece, cft, ton, sqft
    `rate`       DECIMAL(12,2)     NOT NULL,   -- price per unit in BDT
    `updated_by` BIGINT UNSIGNED   NULL,
    `updated_at` DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_mat_name` (`name`)
) ENGINE=InnoDB;

INSERT IGNORE INTO `material_rates` (`name`, `unit`, `rate`) VALUES
    ('Cement',    'bag',   550.00),
    ('Bricks',    'piece', 12.00),
    ('Sand',      'cft',   45.00),
    ('Rod',       'ton',   75000.00),
    ('Labor',     'sqft',  350.00),
    ('Paint',     'liter', 220.00),
    ('Tile',      'sqft',  120.00);

-- ─────────────────────────────────────────────────────────
-- 11. SUBSCRIPTIONS (Agent Plans)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(100)      NOT NULL,
    `price`        DECIMAL(10,2)     NOT NULL,
    `duration_days` SMALLINT          NOT NULL DEFAULT 30,
    `listing_limit` SMALLINT          NOT NULL DEFAULT 10,
    `featured_slots` TINYINT          NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT IGNORE INTO `subscription_plans` (`name`, `price`, `duration_days`, `listing_limit`, `featured_slots`) VALUES
    ('Basic',      500.00,   30, 5,  0),
    ('Standard',   1500.00,  30, 20, 2),
    ('Premium',    3000.00,  30, 99, 5),
    ('Enterprise', 5000.00,  30, 999, 10);

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id`          BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED   NOT NULL,
    `plan_id`     SMALLINT UNSIGNED NOT NULL,
    `starts_at`   DATE              NOT NULL,
    `expires_at`  DATE              NOT NULL,
    `status`      ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    `created_at`  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sub_user`   (`user_id`),
    KEY `idx_sub_status` (`status`),
    CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 12. PAYMENTS & TRANSACTIONS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `transactions` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`         BIGINT UNSIGNED NOT NULL,
    `type`            ENUM('subscription','featured','other') NOT NULL,
    `reference_id`    BIGINT UNSIGNED NULL,    -- subscription id / property id
    `gateway`         VARCHAR(50)     NOT NULL, -- sslcommerz | bkash | nagad
    `gateway_txn_id`  VARCHAR(255)    NULL,
    `amount`          DECIMAL(12,2)   NOT NULL,
    `currency`        VARCHAR(5)      NOT NULL DEFAULT 'BDT',
    `status`          ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    `metadata`        JSON            NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_txn_user`   (`user_id`),
    KEY `idx_txn_status` (`status`),
    CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 13. COUPONS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `coupons` (
    `id`           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`         VARCHAR(50)       NOT NULL UNIQUE,
    `type`         ENUM('percent','flat') NOT NULL DEFAULT 'percent',
    `value`        DECIMAL(10,2)     NOT NULL,
    `max_uses`     INT               NULL,
    `used_count`   INT               NOT NULL DEFAULT 0,
    `expires_at`   DATE              NULL,
    `is_active`    TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 14. ANALYTICS (Property view tracking)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `analytics` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `user_id`     BIGINT UNSIGNED NULL,
    `session_id`  VARCHAR(128)    NULL,
    `ip`          VARCHAR(45)     NOT NULL,
    `user_agent`  VARCHAR(500)    NULL,
    `referrer`    VARCHAR(500)    NULL,
    `viewed_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_analytics_property` (`property_id`),
    KEY `idx_analytics_date`     (`viewed_at`),
    CONSTRAINT `fk_analytics_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 15. RECENTLY VIEWED
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `recently_viewed` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NOT NULL,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `viewed_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_rv` (`user_id`, `property_id`),
    CONSTRAINT `fk_rv_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_rv_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 16. SAVED SEARCHES
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `saved_searches` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    BIGINT UNSIGNED NOT NULL,
    `label`      VARCHAR(200)    NULL,
    `params`     JSON            NOT NULL,  -- search filters as JSON
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ss_user` (`user_id`),
    CONSTRAINT `fk_ss_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────
-- 17. SETTINGS
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `key`        VARCHAR(100) NOT NULL,
    `value`      LONGTEXT     NULL,
    `group`      VARCHAR(50)  NOT NULL DEFAULT 'general',
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB;

INSERT IGNORE INTO `settings` (`key`, `value`, `group`) VALUES
    ('site_name',         'NextGen Real Estate', 'general'),
    ('site_email',        'info@realestate.com', 'general'),
    ('site_phone',        '+8801700000000',       'general'),
    ('currency',          'BDT',                  'finance'),
    ('currency_symbol',   '৳',                    'finance'),
    ('featured_price',    '500',                  'finance'),
    ('maintenance_mode',  '0',                    'general');

-- ─────────────────────────────────────────────────────────
-- 18. LOGS (Activity + Error)
-- ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`       ENUM('activity','error','security') NOT NULL DEFAULT 'activity',
    `user_id`    BIGINT UNSIGNED NULL,
    `action`     VARCHAR(200)    NOT NULL,
    `details`    TEXT            NULL,
    `ip`         VARCHAR(45)     NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_logs_type` (`type`),
    KEY `idx_logs_date` (`created_at`),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────
-- END OF SCHEMA
-- ─────────────────────────────────────────────────────────
