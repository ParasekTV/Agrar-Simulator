-- ============================================
-- Fix: Research-Tree Icons korrekt setzen
-- Erstellt: 2026-02-05
-- ============================================
-- 1. Produktions-Icons aus productions-Tabelle uebernehmen
-- 2. Marken-Icons aus brands-Verzeichnis setzen
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- 1. PRODUKTIONS-FORSCHUNGEN (ID 100-170)
-- ============================================
-- Icon aus der zugehoerigen Produktion uebernehmen

UPDATE research_tree rt
JOIN productions p ON p.required_research_id = rt.id
SET rt.icon = CONCAT('productions/', p.icon)
WHERE rt.id BETWEEN 100 AND 199
  AND p.icon IS NOT NULL
  AND p.icon != '';

-- ============================================
-- 2. MARKEN-FORSCHUNGEN (ID 300+)
-- ============================================
-- Icons aus brands/fahrzeuge/ oder brands/geraete/

UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-johndeere.png' WHERE id = 300;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-fendt.png' WHERE id = 301;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-claas.png' WHERE id = 302;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-caseih.png' WHERE id = 303;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-newholland.png' WHERE id = 304;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-masseyferguson.png' WHERE id = 305;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-deutzfahr.png' WHERE id = 306;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-kubota.png' WHERE id = 307;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-valtra.png' WHERE id = 308;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-steyr.png' WHERE id = 309;
UPDATE research_tree SET icon = 'brands/geraete/logo-krone.png' WHERE id = 310;
UPDATE research_tree SET icon = 'brands/geraete/logo-grimme.png' WHERE id = 311;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-abi.png' WHERE id = 312;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-agco.png' WHERE id = 313;
UPDATE research_tree SET icon = 'brands/geraete/logo-agibatco.png' WHERE id = 314;
UPDATE research_tree SET icon = 'brands/geraete/logo-agibatco.png' WHERE id = 315;
UPDATE research_tree SET icon = 'brands/geraete/logo-agistorm.png' WHERE id = 317;
UPDATE research_tree SET icon = 'brands/geraete/logo-agiwestfield.png' WHERE id = 319;
UPDATE research_tree SET icon = 'brands/geraete/logo-agrifac.png' WHERE id = 320;
UPDATE research_tree SET icon = 'brands/geraete/logo-amazone.png' WHERE id = 321;
UPDATE research_tree SET icon = 'brands/geraete/logo-bergmann.png' WHERE id = 322;
UPDATE research_tree SET icon = 'brands/geraete/logo-bredal.png' WHERE id = 323;
UPDATE research_tree SET icon = 'brands/geraete/logo-fliegl.png' WHERE id = 324;
UPDATE research_tree SET icon = 'brands/geraete/logo-horsch.png' WHERE id = 325;
UPDATE research_tree SET icon = 'brands/geraete/logo-krampe.png' WHERE id = 326;
UPDATE research_tree SET icon = 'brands/geraete/logo-kuhn.png' WHERE id = 327;
UPDATE research_tree SET icon = 'brands/geraete/logo-lemken.png' WHERE id = 328;
UPDATE research_tree SET icon = 'brands/geraete/logo-poettinger.png' WHERE id = 329;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-volvo.png' WHERE id = 330;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-manitou.png' WHERE id = 331;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-jcb.png' WHERE id = 332;
UPDATE research_tree SET icon = 'brands/geraete/logo-kverneland.png' WHERE id = 333;
UPDATE research_tree SET icon = 'brands/geraete/logo-zunhammer.png' WHERE id = 334;
UPDATE research_tree SET icon = 'brands/geraete/logo-kroeger.png' WHERE id = 335;
UPDATE research_tree SET icon = 'brands/geraete/logo-annaburger.png' WHERE id = 336;
UPDATE research_tree SET icon = 'brands/geraete/logo-samsonagro.png' WHERE id = 337;

-- ============================================
-- 3. BASIS-FORSCHUNGEN KORRIGIEREN (ID 1-17)
-- ============================================
-- Passende thematische Icons

UPDATE research_tree SET icon = 'misc/aus_eigener_ernte.png' WHERE id = 1;
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall.png' WHERE id = 2;
UPDATE research_tree SET icon = 'brands/fahrzeuge/logo-fendt.png' WHERE id = 3;
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_2.png' WHERE id = 4;
UPDATE research_tree SET icon = 'misc/schweine_aus_einem_schweinestall.png' WHERE id = 5;
UPDATE research_tree SET icon = 'brands/geraete/logo-claas.png' WHERE id = 6;
UPDATE research_tree SET icon = 'misc/aus_einem_gewaechshaus.png' WHERE id = 7;
UPDATE research_tree SET icon = 'misc/aus_dem_labor.png' WHERE id = 8;
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_3.png' WHERE id = 9;

-- Crops Phase 1 (IDs 10-17)
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_4.png' WHERE id = 10;
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_luzerne_heu.png' WHERE id = 11;
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_5.png' WHERE id = 12;
UPDATE research_tree SET icon = 'misc/aus_der_gemuesefabrik.png' WHERE id = 13;
UPDATE research_tree SET icon = 'misc/aus_einer_obstplantage.png' WHERE id = 14;
UPDATE research_tree SET icon = 'misc/von_der_duengerproduktion.png' WHERE id = 15;
UPDATE research_tree SET icon = 'misc/aus_dem_labor_2.png' WHERE id = 16;
UPDATE research_tree SET icon = 'products/erde.png' WHERE id = 17;

-- ============================================
-- 4. TIERHALTUNG (ID 180-187)
-- ============================================

UPDATE research_tree SET icon = 'misc/aus_einem_huehnerstall.png' WHERE id = 180;
UPDATE research_tree SET icon = 'misc/eier_aus_einem_huehnerstall.png' WHERE id = 181;
UPDATE research_tree SET icon = 'misc/schweine_aus_einem_schweinestall.png' WHERE id = 182;
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall.png' WHERE id = 183;
UPDATE research_tree SET icon = 'products/lammfleisch.png' WHERE id = 184;
UPDATE research_tree SET icon = 'misc/ziegenmilch_aus_einem_ziegenstall.png' WHERE id = 185;
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall_oder_ziegenstall.png' WHERE id = 186;
UPDATE research_tree SET icon = 'products/bienenstock.png' WHERE id = 187;

-- ============================================
-- VERIFIZIERUNG
-- ============================================
-- SELECT id, name, icon FROM research_tree WHERE icon IS NOT NULL ORDER BY id;
