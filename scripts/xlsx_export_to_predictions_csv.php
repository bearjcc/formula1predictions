<?php

/**
 * Parse xlsx export .txt files and emit CSVs of race predictions (year, race, predictor, position, driver_name).
 *
 * Usage: php xlsx_export_to_predictions_csv.php [--base-dir path]
 * Default base-dir: storage/app/xlsx_export
 * Writes: storage/app/xlsx_export/predictions_2022.csv, predictions_2023.csv
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$exportBase = $baseDir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'xlsx_export';

$args = array_slice($argv, 1);
foreach ($args as $i => $arg) {
    if ($arg === '--base-dir' && isset($args[$i + 1])) {
        $exportBase = $args[$i + 1];
        break;
    }
}

$parser = new XlsxExportPredictionsParser;
$parser->run($exportBase);

// region Parser

class XlsxExportPredictionsParser
{
    /** Sheets that are not per-race grids (skip or treat specially) */
    private const NON_RACE_SHEETS = ['PreSeason', 'MidSeason', 'Sheet1'];

    /** Filenames that are results/standings, not predictor workbooks */
    private const SKIP_FILES = ['Race Results', 'Running Points'];

    /** @var array<int, array{year: int, race: string, predictor: string, position: int, driver: string}> */
    private array $rows = [];

    public function run(string $exportBase): void
    {
        $this->rows = [];
        $years = [2022, 2023];
        foreach ($years as $year) {
            $dir = $exportBase.DIRECTORY_SEPARATOR.(string) $year;
            if (! is_dir($dir)) {
                continue;
            }
            $this->scanDir($dir, $year);
            $dummies = $dir.DIRECTORY_SEPARATOR.'Dummies';
            if (is_dir($dummies)) {
                $this->scanDir($dummies, $year);
            }
        }

        $byYear = [];
        foreach ($this->rows as $r) {
            $byYear[$r['year']][] = $r;
        }
        foreach ($byYear as $year => $list) {
            $path = $exportBase.DIRECTORY_SEPARATOR.'predictions_'.($year).'.csv';
            $this->writeCsv($path, $list);
            echo 'Wrote '.count($list).' rows to '.$path."\n";
        }
    }

    private function scanDir(string $dir, int $year): void
    {
        $files = glob($dir.DIRECTORY_SEPARATOR.'*.xlsx.txt') ?: [];
        foreach ($files as $path) {
            $base = basename($path, '.xlsx.txt');
            if (in_array($base, self::SKIP_FILES, true)) {
                continue;
            }
            $predictor = $this->predictorFromFilename($base);
            $this->parseFile($path, $year, $predictor);
        }
    }

    private function predictorFromFilename(string $base): string
    {
        if (preg_match('/^Formula 1 Prediction - (.+)$/', $base, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^(.+) - F1 2023 Predictions$/', $base, $m)) {
            return trim($m[1]);
        }

        return $base;
    }

    private function parseFile(string $path, int $year, string $predictor): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }
        $sections = preg_split('/\n## Sheet: /', "\n".$content, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($sections as $i => $block) {
            if ($i === 0) {
                continue;
            }
            if (! preg_match('/^([^\n]+)/', $block, $m)) {
                continue;
            }
            $sheetName = trim($m[1]);
            if (in_array($sheetName, self::NON_RACE_SHEETS, true)) {
                continue;
            }
            $this->parseSheetBlock($block, $year, $sheetName, $predictor);
        }
    }

    private function parseSheetBlock(string $block, int $year, string $race, string $predictor): void
    {
        $positions = [];
        $drivers = [];
        foreach (explode("\n", $block) as $line) {
            $line = trim($line);
            if (preg_match('/^A(\d+) = P(\d+)$/', $line, $m)) {
                $row = (int) $m[1];
                $pos = (int) $m[2];
                $positions[$row] = $pos;
            }
            if (preg_match('/^B(\d+) = (.+)$/', $line, $m)) {
                $row = (int) $m[1];
                $val = trim($m[2]);
                if ($val !== '' && $val !== '#REF!' && $val !== '#N/A' && ! preg_match('/^\d+\.?\d*$/', $val)) {
                    $drivers[$row] = $val;
                }
            }
        }
        foreach ($positions as $row => $position) {
            if (isset($drivers[$row])) {
                $this->rows[] = [
                    'year' => $year,
                    'race' => $race,
                    'predictor' => $predictor,
                    'position' => $position,
                    'driver' => $drivers[$row],
                ];
            }
        }
    }

    /**
     * @param  array<int, array{year: int, race: string, predictor: string, position: int, driver: string}>  $list
     */
    private function writeCsv(string $path, array $list): void
    {
        $fh = fopen($path, 'w');
        if (! $fh) {
            return;
        }
        fputcsv($fh, ['year', 'race', 'predictor', 'position', 'driver_name']);
        foreach ($list as $r) {
            fputcsv($fh, [$r['year'], $r['race'], $r['predictor'], $r['position'], $r['driver']]);
        }
        fclose($fh);
    }
}

// endregion
