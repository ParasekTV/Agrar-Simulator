SET NAMES utf8mb4;

-- Weekly Challenges - Namen korrigieren
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'koenig', 'könig') WHERE challenge_name LIKE '%koenig%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Koenig', 'König') WHERE challenge_name LIKE '%Koenig%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'kaese', 'käse') WHERE challenge_name LIKE '%kaese%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Kaese', 'Käse') WHERE challenge_name LIKE '%Kaese%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'gefluegel', 'geflügel') WHERE challenge_name LIKE '%gefluegel%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Gefluegel', 'Geflügel') WHERE challenge_name LIKE '%Gefluegel%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'baecker', 'bäcker') WHERE challenge_name LIKE '%baecker%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Baecker', 'Bäcker') WHERE challenge_name LIKE '%Baecker%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'verkaeufer', 'verkäufer') WHERE challenge_name LIKE '%verkaeufer%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Verkaeufer', 'Verkäufer') WHERE challenge_name LIKE '%Verkaeufer%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'jaeger', 'jäger') WHERE challenge_name LIKE '%jaeger%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Jaeger', 'Jäger') WHERE challenge_name LIKE '%Jaeger%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'zuechter', 'züchter') WHERE challenge_name LIKE '%zuechter%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Zuechter', 'Züchter') WHERE challenge_name LIKE '%Zuechter%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'grossgrund', 'großgrund') WHERE challenge_name LIKE '%grossgrund%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Grossgrund', 'Großgrund') WHERE challenge_name LIKE '%Grossgrund%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'vermoegen', 'vermögen') WHERE challenge_name LIKE '%vermoegen%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Vermoegen', 'Vermögen') WHERE challenge_name LIKE '%Vermoegen%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'oel', 'öl') WHERE challenge_name LIKE '%oel%';
UPDATE weekly_challenges SET challenge_name = REPLACE(challenge_name, 'Oel', 'Öl') WHERE challenge_name LIKE '%Oel%';

-- Weekly Challenges - Beschreibungen korrigieren
UPDATE weekly_challenges SET description = REPLACE(description, 'Fruechte', 'Früchte') WHERE description LIKE '%Fruechte%';
UPDATE weekly_challenges SET description = REPLACE(description, 'fruechte', 'früchte') WHERE description LIKE '%fruechte%';
UPDATE weekly_challenges SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE weekly_challenges SET description = REPLACE(description, 'kaese', 'käse') WHERE description LIKE '%kaese%';
UPDATE weekly_challenges SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE weekly_challenges SET description = REPLACE(description, 'Fuer', 'Für') WHERE description LIKE '%Fuer%';
UPDATE weekly_challenges SET description = REPLACE(description, 'Rueben', 'Rüben') WHERE description LIKE '%Rueben%';
UPDATE weekly_challenges SET description = REPLACE(description, 'rueben', 'rüben') WHERE description LIKE '%rueben%';
UPDATE weekly_challenges SET description = REPLACE(description, 'Verkaeufe', 'Verkäufe') WHERE description LIKE '%Verkaeufe%';
UPDATE weekly_challenges SET description = REPLACE(description, 'verkaeufe', 'verkäufe') WHERE description LIKE '%verkaeufe%';
UPDATE weekly_challenges SET description = REPLACE(description, 'Hoehe', 'Höhe') WHERE description LIKE '%Hoehe%';
UPDATE weekly_challenges SET description = REPLACE(description, 'hoehe', 'höhe') WHERE description LIKE '%hoehe%';

-- Challenge Templates - Namen korrigieren
UPDATE challenge_templates SET name = REPLACE(name, 'koenig', 'könig') WHERE name LIKE '%koenig%';
UPDATE challenge_templates SET name = REPLACE(name, 'Koenig', 'König') WHERE name LIKE '%Koenig%';
UPDATE challenge_templates SET name = REPLACE(name, 'kaese', 'käse') WHERE name LIKE '%kaese%';
UPDATE challenge_templates SET name = REPLACE(name, 'Kaese', 'Käse') WHERE name LIKE '%Kaese%';
UPDATE challenge_templates SET name = REPLACE(name, 'gefluegel', 'geflügel') WHERE name LIKE '%gefluegel%';
UPDATE challenge_templates SET name = REPLACE(name, 'Gefluegel', 'Geflügel') WHERE name LIKE '%Gefluegel%';
UPDATE challenge_templates SET name = REPLACE(name, 'baecker', 'bäcker') WHERE name LIKE '%baecker%';
UPDATE challenge_templates SET name = REPLACE(name, 'Baecker', 'Bäcker') WHERE name LIKE '%Baecker%';
UPDATE challenge_templates SET name = REPLACE(name, 'verkaeufer', 'verkäufer') WHERE name LIKE '%verkaeufer%';
UPDATE challenge_templates SET name = REPLACE(name, 'Verkaeufer', 'Verkäufer') WHERE name LIKE '%Verkaeufer%';
UPDATE challenge_templates SET name = REPLACE(name, 'jaeger', 'jäger') WHERE name LIKE '%jaeger%';
UPDATE challenge_templates SET name = REPLACE(name, 'Jaeger', 'Jäger') WHERE name LIKE '%Jaeger%';
UPDATE challenge_templates SET name = REPLACE(name, 'zuechter', 'züchter') WHERE name LIKE '%zuechter%';
UPDATE challenge_templates SET name = REPLACE(name, 'Zuechter', 'Züchter') WHERE name LIKE '%Zuechter%';
UPDATE challenge_templates SET name = REPLACE(name, 'grossgrund', 'großgrund') WHERE name LIKE '%grossgrund%';
UPDATE challenge_templates SET name = REPLACE(name, 'Grossgrund', 'Großgrund') WHERE name LIKE '%Grossgrund%';
UPDATE challenge_templates SET name = REPLACE(name, 'vermoegen', 'vermögen') WHERE name LIKE '%vermoegen%';
UPDATE challenge_templates SET name = REPLACE(name, 'Vermoegen', 'Vermögen') WHERE name LIKE '%Vermoegen%';
UPDATE challenge_templates SET name = REPLACE(name, 'oel', 'öl') WHERE name LIKE '%oel%';
UPDATE challenge_templates SET name = REPLACE(name, 'Oel', 'Öl') WHERE name LIKE '%Oel%';

-- Challenge Templates - Beschreibungen korrigieren
UPDATE challenge_templates SET description = REPLACE(description, 'Fruechte', 'Früchte') WHERE description LIKE '%Fruechte%';
UPDATE challenge_templates SET description = REPLACE(description, 'fruechte', 'früchte') WHERE description LIKE '%fruechte%';
UPDATE challenge_templates SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE challenge_templates SET description = REPLACE(description, 'kaese', 'käse') WHERE description LIKE '%kaese%';
UPDATE challenge_templates SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE challenge_templates SET description = REPLACE(description, 'Fuer', 'Für') WHERE description LIKE '%Fuer%';
UPDATE challenge_templates SET description = REPLACE(description, 'Rueben', 'Rüben') WHERE description LIKE '%Rueben%';
UPDATE challenge_templates SET description = REPLACE(description, 'rueben', 'rüben') WHERE description LIKE '%rueben%';
UPDATE challenge_templates SET description = REPLACE(description, 'Verkaeufe', 'Verkäufe') WHERE description LIKE '%Verkaeufe%';
UPDATE challenge_templates SET description = REPLACE(description, 'verkaeufe', 'verkäufe') WHERE description LIKE '%verkaeufe%';
UPDATE challenge_templates SET description = REPLACE(description, 'Hoehe', 'Höhe') WHERE description LIKE '%Hoehe%';
UPDATE challenge_templates SET description = REPLACE(description, 'hoehe', 'höhe') WHERE description LIKE '%hoehe%';
UPDATE challenge_templates SET description = REPLACE(description, 'schliesse', 'schließe') WHERE description LIKE '%schliesse%';
UPDATE challenge_templates SET description = REPLACE(description, 'Schliesse', 'Schließe') WHERE description LIKE '%Schliesse%';

-- Cooperative Challenge Templates - Namen korrigieren
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'koenig', 'könig') WHERE name LIKE '%koenig%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'Koenig', 'König') WHERE name LIKE '%Koenig%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'kaese', 'käse') WHERE name LIKE '%kaese%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'Kaese', 'Käse') WHERE name LIKE '%Kaese%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'baecker', 'bäcker') WHERE name LIKE '%baecker%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'Baecker', 'Bäcker') WHERE name LIKE '%Baecker%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'fuehrer', 'führer') WHERE name LIKE '%fuehrer%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'Fuehrer', 'Führer') WHERE name LIKE '%Fuehrer%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'grosszuegig', 'großzügig') WHERE name LIKE '%grosszuegig%';
UPDATE cooperative_challenge_templates SET name = REPLACE(name, 'Grosszuegig', 'Großzügig') WHERE name LIKE '%Grosszuegig%';

-- Cooperative Challenge Templates - Beschreibungen korrigieren
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fruechte', 'Früchte') WHERE description LIKE '%Fruechte%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'fruechte', 'früchte') WHERE description LIKE '%fruechte%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'kaese', 'käse') WHERE description LIKE '%kaese%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fuer', 'Für') WHERE description LIKE '%Fuer%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fuehrt', 'Führt') WHERE description LIKE '%Fuehrt%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'fuehrt', 'führt') WHERE description LIKE '%fuehrt%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Rueben', 'Rüben') WHERE description LIKE '%Rueben%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'rueben', 'rüben') WHERE description LIKE '%rueben%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Verkaeufe', 'Verkäufe') WHERE description LIKE '%Verkaeufe%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'verkaeufe', 'verkäufe') WHERE description LIKE '%verkaeufe%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Schliesst', 'Schließt') WHERE description LIKE '%Schliesst%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'schliesst', 'schließt') WHERE description LIKE '%schliesst%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'verfuegbar', 'verfügbar') WHERE description LIKE '%verfuegbar%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Verfuegbar', 'Verfügbar') WHERE description LIKE '%Verfuegbar%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'ueber', 'über') WHERE description LIKE '%ueber%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Ueber', 'Über') WHERE description LIKE '%Ueber%';
