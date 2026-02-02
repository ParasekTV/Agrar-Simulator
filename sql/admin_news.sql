-- ============================================
-- Admin News/Changelog Feature - Migration
-- ============================================

SET NAMES utf8mb4;

-- Erweitere news_posts Kategorie um 'changelog' und 'admin_news'
-- und erlaube NULL für author_farm_id (Admin-Posts haben keine Farm)
ALTER TABLE news_posts
MODIFY COLUMN category ENUM('announcement', 'market', 'cooperative', 'tips', 'offtopic', 'changelog', 'admin_news') NOT NULL,
MODIFY COLUMN author_farm_id INT NULL;

-- Füge is_admin_post Flag hinzu
ALTER TABLE news_posts
ADD COLUMN IF NOT EXISTS is_admin_post BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS admin_user_id INT NULL;

-- Index für Admin-Posts
CREATE INDEX IF NOT EXISTS idx_admin_posts ON news_posts(is_admin_post, category);
