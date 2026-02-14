<?php
/**
 * Feature Parser für Plan.txt
 *
 * Parst die Plan.txt und extrahiert Feature-Definitionen
 */

class FeatureParser
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Parst den kompletten Plan-Inhalt
     */
    public function parse(string $content): array
    {
        $features = [];

        // Teile den Inhalt in Sektionen
        $sections = $this->splitIntoSections($content);

        foreach ($sections as $section) {
            $parsed = $this->parseSection($section);
            if ($parsed) {
                $features[$parsed['type']] = $parsed;
            }
        }

        return $features;
    }

    /**
     * Teilt den Inhalt in Sektionen auf
     */
    private function splitIntoSections(string $content): array
    {
        // Jede Sektion beginnt mit "- Bereich"
        $sections = preg_split('/(?=- Bereich)/i', $content, -1, PREG_SPLIT_NO_EMPTY);
        return array_map('trim', $sections);
    }

    /**
     * Parst eine einzelne Sektion
     */
    private function parseSection(string $section): ?array
    {
        // Extrahiere den Bereichsnamen
        if (preg_match('/^- Bereich\s+(\w+)/i', $section, $matches)) {
            $type = strtolower($matches[1]);

            switch ($type) {
                case 'fields':
                    return $this->parseFieldsSection($section);
                case 'animals':
                    return $this->parseAnimalsSection($section);
                case 'vehicles':
                    return $this->parseVehiclesSection($section);
                default:
                    // Arena wird separat behandelt
                    if (strpos(strtolower($section), 'wettkampf') !== false) {
                        return $this->parseArenaSection($section);
                    }
                    return null;
            }
        }

        // Check für Wettkampf-Sektion
        if (strpos(strtolower($section), 'wettkampf') !== false || strpos(strtolower($section), 'arena') !== false) {
            return $this->parseArenaSection($section);
        }

        return null;
    }

    /**
     * Parst die Felder-Sektion
     */
    private function parseFieldsSection(string $section): array
    {
        return [
            'type' => 'fields',
            'raw' => $section,
            'features' => [
                'field_limits' => $this->config['field_limits'] ?? [],
                'cultivation' => [
                    'grubbing' => true,
                    'plowing' => true,
                ],
                'meadows' => true,
                'greenhouses' => true,
                'growth_stages' => true,
                'weeds' => true,
                'herbicides' => true,
            ],
            'greenhouse_crops' => ['Tomaten', 'Gurken', 'Paprika'],
            'meadow_products' => ['Gras'],
        ];
    }

    /**
     * Parst die Tiere-Sektion
     */
    private function parseAnimalsSection(string $section): array
    {
        return [
            'type' => 'animals',
            'raw' => $section,
            'features' => [
                'health_system' => true,
                'sickness' => true,
                'medicine' => true,
                'straw' => true,
                'mucking_out' => true,
                'water' => true,
                'reproduction' => true,
                'aging' => true,
                'death' => [
                    'age_days' => 14,
                    'random' => true,
                ],
                'specific_feed' => true,
            ],
            'feed_mapping' => $this->config['feed_mapping'] ?? [],
        ];
    }

    /**
     * Parst die Fahrzeuge-Sektion
     */
    private function parseVehiclesSection(string $section): array
    {
        return [
            'type' => 'vehicles',
            'raw' => $section,
            'features' => [
                'operating_hours' => [
                    'daily_min' => 5,
                    'daily_max' => 10,
                    'random' => true,
                ],
                'condition_degradation' => true,
                'workshop' => [
                    'wait_time' => true,
                    'efficiency_reduction' => 0.5,
                ],
                'diesel' => [
                    'consumption' => true,
                    'from_storage' => true,
                ],
            ],
        ];
    }

    /**
     * Parst die Arena-Sektion
     */
    private function parseArenaSection(string $section): array
    {
        return [
            'type' => 'arena',
            'raw' => $section,
            'features' => [
                'challenge_system' => true,
                'pick_ban' => true,
                'roles' => [
                    'harvest_specialist' => [
                        'name' => 'Ernte-Spezialist',
                        'task' => 'Weizen ernten',
                        'bonus' => 'Erhöht Multiplikator',
                    ],
                    'bale_producer' => [
                        'name' => 'Ballen-Produzent',
                        'task' => 'Ballen pressen',
                        'bonus' => 'Punkte pro Balle',
                    ],
                    'transport' => [
                        'name' => 'Transport',
                        'task' => 'Ballen liefern',
                        'bonus' => 'Bonus bei schneller Lieferung',
                    ],
                ],
                'scoring' => [
                    'wheat_multiplier' => true,
                    'bale_points' => 10,
                    'upper_level_bonus' => 1.5,
                ],
                'match_duration' => 15, // Minuten
            ],
        ];
    }

    /**
     * Extrahiert Futterarten aus der Tiere-Sektion
     */
    public function extractFeedTypes(string $section): array
    {
        $feedTypes = [];

        // Pattern: "Tierart braucht/brauchen Futter1 oder Futter2"
        $patterns = [
            '/Hühner.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Schafe.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Schweine.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Kühe.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Pferde.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Kaninchen.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Bienen.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Ziegen.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Enten.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Gänse.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Truthahn.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
            '/Wasserbüffel.*?(?:brauchen?|benötigen?)\s+([^.]+)/iu',
        ];

        $animals = ['chicken', 'sheep', 'pig', 'cow', 'horse', 'rabbit', 'bee', 'goat', 'duck', 'goose', 'turkey', 'buffalo'];

        foreach ($patterns as $i => $pattern) {
            if (preg_match($pattern, $section, $matches)) {
                $feeds = preg_split('/\s+oder\s+/iu', $matches[1]);
                $feeds = array_map('trim', $feeds);
                $feeds = array_filter($feeds, fn($f) => !empty($f) && $f !== ',');
                $feedTypes[$animals[$i]] = $feeds;
            }
        }

        return $feedTypes;
    }
}
