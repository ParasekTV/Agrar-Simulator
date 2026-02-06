SET NAMES utf8mb4;

DELETE FROM productions WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM productions WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM products WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM products WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM research_tree WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM research_tree WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM selling_points WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM selling_points WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM dealers WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM dealers WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM animals WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM animals WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

DELETE FROM vehicles WHERE HEX(name) LIKE '%C383%' AND BINARY CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) IN (SELECT BINARY name FROM (SELECT name FROM vehicles WHERE HEX(name) NOT LIKE '%C383%') AS tmp);

UPDATE crops SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE fertilizer_types SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE fertilizer_types SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE game_events SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE lime_types SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE lime_types SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE productions SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE productions SET name_de = CONVERT(CAST(CONVERT(name_de USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name_de) LIKE '%C383%';
UPDATE productions SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE products SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE products SET name_de = CONVERT(CAST(CONVERT(name_de USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name_de) LIKE '%C383%';
UPDATE products SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE selling_points SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE selling_points SET name_de = CONVERT(CAST(CONVERT(name_de USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name_de) LIKE '%C383%';
UPDATE selling_points SET location = CONVERT(CAST(CONVERT(location USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(location) LIKE '%C383%';
UPDATE selling_points SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE dealers SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE dealers SET name_de = CONVERT(CAST(CONVERT(name_de USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name_de) LIKE '%C383%';
UPDATE dealers SET location = CONVERT(CAST(CONVERT(location USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(location) LIKE '%C383%';
UPDATE dealers SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE research_tree SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE research_tree SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE animals SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';

UPDATE vehicles SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE vehicles SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE news_posts SET title = CONVERT(CAST(CONVERT(title USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(title) LIKE '%C383%';
UPDATE news_posts SET content = CONVERT(CAST(CONVERT(content USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(content) LIKE '%C383%';

UPDATE challenge_templates SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE challenge_templates SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE vehicle_brands SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';

UPDATE weekly_challenges SET challenge_name = CONVERT(CAST(CONVERT(challenge_name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(challenge_name) LIKE '%C383%';
UPDATE weekly_challenges SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';

UPDATE cooperative_challenge_templates SET name = CONVERT(CAST(CONVERT(name USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(name) LIKE '%C383%';
UPDATE cooperative_challenge_templates SET description = CONVERT(CAST(CONVERT(description USING latin1) AS BINARY) USING utf8mb4) WHERE HEX(description) LIKE '%C383%';
