SET NAMES utf8mb4;

-- ============================================
-- RANKINGS ERWEITERUNG - Online-Status & Stats
-- ============================================

-- Letzte Aktivität tracken
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP NULL AFTER last_login;

-- View für erweiterte Ranking-Statistiken
DROP VIEW IF EXISTS ranking_extended;

CREATE VIEW ranking_extended AS
SELECT
    f.id AS farm_id,
    f.farm_name,
    f.user_id,
    u.username,
    u.last_login,
    u.last_activity,
    f.money,
    f.points,
    f.level,
    COALESCE((SELECT COUNT(*) FROM farm_animals WHERE farm_id = f.id), 0) AS animal_count,
    COALESCE((SELECT SUM(quantity) FROM farm_animals WHERE farm_id = f.id), 0) AS total_animals,
    COALESCE((SELECT COUNT(*) FROM farm_vehicles WHERE farm_id = f.id), 0) AS vehicle_count,
    COALESCE((SELECT COUNT(*) FROM fields WHERE farm_id = f.id), 0) AS field_count,
    COALESCE((SELECT COUNT(*) FROM farm_productions WHERE farm_id = f.id), 0) AS production_count,
    CASE
        WHEN u.last_activity >= NOW() - INTERVAL 15 MINUTE THEN 'online'
        WHEN u.last_activity >= NOW() - INTERVAL 24 HOUR THEN 'recent'
        WHEN u.last_activity >= NOW() - INTERVAL 7 DAY THEN 'away'
        ELSE 'offline'
    END AS online_status
FROM farms f
JOIN users u ON f.user_id = u.id
WHERE u.is_active = 1;

-- Index für Performance
CREATE INDEX IF NOT EXISTS idx_users_last_activity ON users(last_activity);
