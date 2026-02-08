SET NAMES utf8mb4;

-- ============================================
-- GENOSSENSCHAFTS-PINNWAND - v1.2 Migration
-- ============================================
-- Internes Forum fuer Genossenschaftsmitglieder

-- ============================================
-- 1. PINNWAND-BEITRAEGE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    author_farm_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    is_pinned TINYINT(1) DEFAULT 0,
    is_announcement TINYINT(1) DEFAULT 0,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (author_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_coop_posts (cooperative_id, created_at),
    INDEX idx_coop_pinned (cooperative_id, is_pinned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. KOMMENTARE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_farm_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cooperative_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_post_comments (post_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. LIKES/REAKTIONEN
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    farm_id INT NOT NULL,
    reaction_type ENUM('like', 'love', 'laugh', 'wow', 'sad') DEFAULT 'like',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cooperative_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_like (post_id, farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. GELESEN-STATUS (fuer ungelesene Beitraege)
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_post_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    farm_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cooperative_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_read (post_id, farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

