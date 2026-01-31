-- Agrar Simulator - Admin Update
-- Fuehre dieses Script aus um Admin-Funktionalitaet hinzuzufuegen

-- Fuege is_admin Spalte hinzu falls nicht vorhanden
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE;

-- Setze den ersten Benutzer als Admin (optional - anpassen nach Bedarf)
-- UPDATE users SET is_admin = TRUE WHERE id = 1;
