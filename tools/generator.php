<?php
/**
 * Code Generator für Farming Simulator v2.0
 *
 * Liest Plan.txt und generiert automatisch:
 * - SQL-Migrationen
 * - PHP-Model-Methoden
 * - PHP-Controller-Methoden
 * - Views
 * - Cron-Jobs
 *
 * Usage:
 *   php generator.php --preview          Zeigt was generiert wird
 *   php generator.php --generate         Generiert alle Dateien
 *   php generator.php --sql-only         Nur SQL generieren
 *   php generator.php --feature=fields   Nur bestimmtes Feature
 */

// Autoload der Generator-Klassen
require_once __DIR__ . '/FeatureParser.php';
require_once __DIR__ . '/SQLGenerator.php';
require_once __DIR__ . '/PHPGenerator.php';

class CodeGenerator
{
    private array $config;
    private FeatureParser $parser;
    private SQLGenerator $sqlGenerator;
    private PHPGenerator $phpGenerator;
    private array $parsedFeatures = [];
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = __DIR__;
        $this->loadConfig();
        $this->parser = new FeatureParser($this->config);
        $this->sqlGenerator = new SQLGenerator($this->config);
        $this->phpGenerator = new PHPGenerator($this->config);
    }

    private function loadConfig(): void
    {
        $configFile = $this->baseDir . '/generator_config.json';
        if (!file_exists($configFile)) {
            throw new Exception("Konfigurationsdatei nicht gefunden: $configFile");
        }
        $this->config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Fehler beim Parsen der Konfiguration: " . json_last_error_msg());
        }
    }

    /**
     * Parst die Plan.txt und extrahiert Feature-Definitionen
     */
    public function parse(): void
    {
        $planFile = $this->baseDir . '/' . $this->config['plan_file'];
        if (!file_exists($planFile)) {
            throw new Exception("Plan.txt nicht gefunden: $planFile");
        }

        $content = file_get_contents($planFile);
        $this->parsedFeatures = $this->parser->parse($content);

        echo "Plan.txt erfolgreich geparst!\n";
        echo "Gefundene Features: " . count($this->parsedFeatures) . "\n\n";
    }

    /**
     * Zeigt eine Vorschau der zu generierenden Dateien
     */
    public function preview(): void
    {
        $this->parse();

        echo "=== VORSCHAU DER ZU GENERIERENDEN DATEIEN ===\n\n";

        // SQL Preview
        echo "--- SQL-MIGRATION ---\n";
        echo "Datei: " . $this->config['sql_output'] . "\n";
        echo "Neue Tabellen:\n";
        foreach ($this->config['features'] as $feature => $def) {
            if (!empty($def['new_tables'])) {
                foreach ($def['new_tables'] as $table) {
                    echo "  - $table\n";
                }
            }
        }
        echo "Tabellen-Erweiterungen:\n";
        foreach ($this->config['features'] as $feature => $def) {
            if (!empty($def['new_columns'])) {
                foreach ($def['new_columns'] as $table => $columns) {
                    echo "  - $table: " . implode(', ', $columns) . "\n";
                }
            }
        }

        echo "\n--- PHP-MODELS ---\n";
        foreach ($this->config['features'] as $feature => $def) {
            echo "  - {$def['model']}.php (Erweiterungen)\n";
        }

        echo "\n--- PHP-CONTROLLERS ---\n";
        foreach ($this->config['features'] as $feature => $def) {
            echo "  - {$def['controller']}.php (Neue Methoden)\n";
        }

        echo "\n--- VIEWS ---\n";
        echo "  - app/views/fields/meadows.php\n";
        echo "  - app/views/fields/greenhouses.php\n";
        echo "  - app/views/arena/index.php\n";
        echo "  - app/views/arena/rankings.php\n";
        echo "  - app/views/arena/pickban.php\n";
        echo "  - app/views/arena/match.php\n";
        echo "  - app/views/arena/results.php\n";

        echo "\n--- CRON-JOBS ---\n";
        echo "  - cron/vehicle_check.php\n";
        echo "  - cron/animal_check.php (Erweiterung)\n";
        echo "  - cron/harvest_check.php (Erweiterung)\n";

        echo "\n";
    }

    /**
     * Generiert alle Dateien
     */
    public function generate(?string $featureFilter = null): void
    {
        $this->parse();

        $outputDir = $this->baseDir . '/' . $this->config['output_dir'];
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        echo "=== GENERIERE DATEIEN ===\n\n";

        // SQL generieren
        $this->generateSQL($featureFilter);

        // PHP generieren
        $this->generatePHP($featureFilter);

        // Views generieren
        $this->generateViews($featureFilter);

        // Crons generieren
        $this->generateCrons($featureFilter);

        echo "\n=== GENERIERUNG ABGESCHLOSSEN ===\n";
        echo "Ausgabeverzeichnis: $outputDir\n";
    }

    /**
     * Generiert nur SQL
     */
    public function generateSQL(?string $featureFilter = null): void
    {
        $sql = $this->sqlGenerator->generateAll($this->parsedFeatures, $featureFilter);

        $outputFile = $this->baseDir . '/' . $this->config['output_dir'] . 'sql/v2.0_all.sql';
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($outputFile, $sql);
        echo "SQL generiert: $outputFile\n";
    }

    /**
     * Generiert PHP-Dateien
     */
    private function generatePHP(?string $featureFilter = null): void
    {
        $features = $featureFilter ? [$featureFilter => $this->config['features'][$featureFilter]] : $this->config['features'];

        foreach ($features as $feature => $def) {
            // Model-Erweiterungen
            $modelCode = $this->phpGenerator->generateModelExtension($feature, $def, $this->parsedFeatures[$feature] ?? []);
            $modelFile = $this->baseDir . '/' . $this->config['output_dir'] . "models/{$def['model']}Extension.php";
            $modelDir = dirname($modelFile);
            if (!is_dir($modelDir)) {
                mkdir($modelDir, 0755, true);
            }
            file_put_contents($modelFile, $modelCode);
            echo "Model generiert: $modelFile\n";

            // Controller-Erweiterungen
            $controllerCode = $this->phpGenerator->generateControllerExtension($feature, $def, $this->parsedFeatures[$feature] ?? []);
            $controllerFile = $this->baseDir . '/' . $this->config['output_dir'] . "controllers/{$def['controller']}Extension.php";
            $controllerDir = dirname($controllerFile);
            if (!is_dir($controllerDir)) {
                mkdir($controllerDir, 0755, true);
            }
            file_put_contents($controllerFile, $controllerCode);
            echo "Controller generiert: $controllerFile\n";
        }
    }

    /**
     * Generiert View-Dateien
     */
    private function generateViews(?string $featureFilter = null): void
    {
        $viewsDir = $this->baseDir . '/' . $this->config['output_dir'] . 'views/';

        // Felder-Views
        if (!$featureFilter || $featureFilter === 'fields') {
            $this->writeView($viewsDir . 'fields/meadows.php', $this->phpGenerator->generateMeadowsView());
            $this->writeView($viewsDir . 'fields/greenhouses.php', $this->phpGenerator->generateGreenhousesView());
        }

        // Arena-Views
        if (!$featureFilter || $featureFilter === 'arena') {
            $this->writeView($viewsDir . 'arena/index.php', $this->phpGenerator->generateArenaIndexView());
            $this->writeView($viewsDir . 'arena/rankings.php', $this->phpGenerator->generateArenaRankingsView());
            $this->writeView($viewsDir . 'arena/pickban.php', $this->phpGenerator->generateArenaPickBanView());
            $this->writeView($viewsDir . 'arena/match.php', $this->phpGenerator->generateArenaMatchView());
            $this->writeView($viewsDir . 'arena/results.php', $this->phpGenerator->generateArenaResultsView());
        }
    }

    /**
     * Schreibt eine View-Datei
     */
    private function writeView(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
        echo "View generiert: $path\n";
    }

    /**
     * Generiert Cron-Jobs
     */
    private function generateCrons(?string $featureFilter = null): void
    {
        $cronsDir = $this->baseDir . '/' . $this->config['output_dir'] . 'cron/';
        if (!is_dir($cronsDir)) {
            mkdir($cronsDir, 0755, true);
        }

        if (!$featureFilter || $featureFilter === 'vehicles') {
            $vehicleCron = $this->phpGenerator->generateVehicleCron();
            file_put_contents($cronsDir . 'vehicle_check.php', $vehicleCron);
            echo "Cron generiert: {$cronsDir}vehicle_check.php\n";
        }

        if (!$featureFilter || $featureFilter === 'animals') {
            $animalCron = $this->phpGenerator->generateAnimalCronExtension();
            file_put_contents($cronsDir . 'animal_check_extension.php', $animalCron);
            echo "Cron generiert: {$cronsDir}animal_check_extension.php\n";
        }

        if (!$featureFilter || $featureFilter === 'fields') {
            $harvestCron = $this->phpGenerator->generateHarvestCronExtension();
            file_put_contents($cronsDir . 'harvest_check_extension.php', $harvestCron);
            echo "Cron generiert: {$cronsDir}harvest_check_extension.php\n";
        }
    }
}

// CLI Handler
function main(): void
{
    global $argv;

    $options = getopt('', ['preview', 'generate', 'sql-only', 'feature::', 'help']);

    if (isset($options['help']) || count($argv) === 1) {
        echo <<<HELP
Farming Simulator v2.0 Code Generator

Usage:
  php generator.php [options]

Options:
  --preview           Zeigt Vorschau der zu generierenden Dateien
  --generate          Generiert alle Dateien
  --sql-only          Generiert nur SQL-Migrationen
  --feature=NAME      Nur bestimmtes Feature (fields, animals, vehicles, arena)
  --help              Diese Hilfe anzeigen

Beispiele:
  php generator.php --preview
  php generator.php --generate
  php generator.php --generate --feature=fields
  php generator.php --sql-only

HELP;
        return;
    }

    try {
        $generator = new CodeGenerator();

        $featureFilter = $options['feature'] ?? null;

        if (isset($options['preview'])) {
            $generator->preview();
        } elseif (isset($options['sql-only'])) {
            $generator->parse();
            $generator->generateSQL($featureFilter);
        } elseif (isset($options['generate'])) {
            $generator->generate($featureFilter);
        } else {
            echo "Bitte eine Option angeben. Verwende --help für Hilfe.\n";
        }
    } catch (Exception $e) {
        echo "FEHLER: " . $e->getMessage() . "\n";
        exit(1);
    }
}

main();
