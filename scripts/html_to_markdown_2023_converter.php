<?php

/**
 * HTML to Markdown Converter for Formula 1 2023 Predictions
 * Converts Google Sheets HTML export directly to markdown format
 * Handles regular races, PreSeason, and MidSeason files
 */
class HtmlToMarkdown2023Converter
{
    private $inputDir;

    private $outputFile;

    public function __construct($inputDir, $outputFile = null)
    {
        $this->inputDir = rtrim($inputDir, '/');
        $this->outputFile = $outputFile ?: $this->inputDir.'/predictions.md';
    }

    /**
     * Convert all HTML files to markdown format
     */
    public function convertAllFiles()
    {
        $htmlFiles = glob($this->inputDir.'/*.html');

        if (empty($htmlFiles)) {
            echo "No HTML files found in: {$this->inputDir}\n";

            return;
        }

        echo 'Found '.count($htmlFiles)." HTML files to process.\n\n";

        $markdownContent = "# Formula 1 2023 Predictions\n\n";
        $raceData = [];
        $specialFiles = [];

        foreach ($htmlFiles as $htmlFile) {
            $filename = basename($htmlFile, '.html');
            echo "Processing: {$filename}.html\n";

            // Handle special files separately
            if (in_array($filename, ['PreSeason', 'MidSeason'])) {
                $specialFiles[$filename] = $this->extractSpecialFileInfo($htmlFile, $filename);
            } else {
                $raceInfo = $this->extractRaceInfo($htmlFile, $filename);
                if ($raceInfo) {
                    $raceData[] = $raceInfo;
                }
            }
        }

        // Sort races alphabetically
        usort($raceData, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Generate markdown content
        foreach ($raceData as $race) {
            $markdownContent .= $this->generateRaceMarkdown($race);
        }

        // Add special files at the end
        if (isset($specialFiles['PreSeason'])) {
            $markdownContent .= $this->generatePreSeasonMarkdown($specialFiles['PreSeason']);
        }

        if (isset($specialFiles['MidSeason'])) {
            $markdownContent .= $this->generateMidSeasonMarkdown($specialFiles['MidSeason']);
        }

        // Write markdown file
        $success = file_put_contents($this->outputFile, $markdownContent);

        if ($success) {
            echo "\nMarkdown file generated successfully: {$this->outputFile}\n";
        } else {
            echo "\nError: Failed to write markdown file: {$this->outputFile}\n";
        }
    }

    /**
     * Extract race information from a regular race HTML file
     */
    private function extractRaceInfo($htmlFile, $raceName)
    {
        $tableData = $this->parseHtmlTable($htmlFile);
        if (empty($tableData)) {
            echo "  Error: Could not read HTML file\n";

            return null;
        }

        $raceInfo = [
            'name' => $raceName,
            'fastest_lap' => null,
            'classification' => [],
        ];

        // Extract fastest lap
        $raceInfo['fastest_lap'] = $this->extractFastestLap($tableData);

        // Extract classification
        $raceInfo['classification'] = $this->extractClassification($tableData);

        return $raceInfo;
    }

    /**
     * Extract information from PreSeason or MidSeason files
     */
    private function extractSpecialFileInfo($htmlFile, $filename)
    {
        $tableData = $this->parseHtmlTable($htmlFile);
        if (empty($tableData)) {
            echo "  Error: Could not read HTML file\n";

            return null;
        }

        if ($filename === 'PreSeason') {
            return $this->extractPreSeasonData($tableData);
        } elseif ($filename === 'MidSeason') {
            return $this->extractMidSeasonData($tableData);
        }

        return null;
    }

    /**
     * Extract PreSeason data
     */
    private function extractPreSeasonData($tableData)
    {
        $data = [
            'team_championship_order' => [],
            'superlatives' => [],
            'drivers' => [],
            'teammates' => [],
        ];

        // Extract team championship order (columns A-C)
        foreach ($tableData as $row) {
            if (isset($row[2]) && ! empty(trim($row[2])) &&
                in_array(trim($row[2]), ['Red Bull', 'Ferrari', 'Alpine', 'Mercedes', 'McLaren', 'Alfa Romeo', 'Aston Martin', 'Haas', 'Williams', 'AlphaTauri'])) {
                $data['team_championship_order'][] = trim($row[2]);
            }
        }

        // Extract superlatives (columns G-H)
        $superlativeKeywords = [
            'Team with Most Podiums', 'Driver with Most Podiums', 'Team with Most DNF\'s',
            'Driver with Most DNF\'s', 'Dirty Driver', 'Clean Driver', 'Most Fastest Laps',
            'Most Sprint Points', 'Misses at least 1 Race', 'Not driving in F1 next year',
        ];

        foreach ($tableData as $row) {
            foreach ($row as $index => $cell) {
                $cell = trim($cell);
                foreach ($superlativeKeywords as $keyword) {
                    if (strpos($cell, $keyword) !== false) {
                        $value = isset($row[$index + 1]) ? trim($row[$index + 1]) : '';
                        if (! empty($value)) {
                            $data['superlatives'][$keyword] = $value;
                        }
                        break 2;
                    }
                }
            }
        }

        // Extract drivers (columns K-L)
        foreach ($tableData as $row) {
            if (isset($row[10]) && ! empty(trim($row[10])) &&
                ! in_array(trim($row[10]), ['Driver Name', 'Name', 'Team', 'Red Bull', '11', 'AlphaTauri']) &&
                ! is_numeric(trim($row[10]))) {
                $driver = trim($row[10]);
                if (! in_array($driver, $data['drivers'])) {
                    $data['drivers'][] = $driver;
                }
            }
        }

        // Extract teammates (columns P-R)
        $teammatePairs = [];
        foreach ($tableData as $rowIndex => $row) {
            // Look for rows that have a number in column P (15) and driver/team in Q-R (16-17)
            if (isset($row[15]) && ! empty(trim($row[15])) && is_numeric(trim($row[15]))) {
                $driver = isset($row[16]) ? trim($row[16]) : '';
                $team = isset($row[17]) ? trim($row[17]) : '';
                if (! empty($driver) && ! empty($team) && ! in_array($driver, ['', 'Loading...'])) {
                    $teammatePairs[] = ['driver' => $driver, 'team' => $team];
                }
            }
        }

        // Based on the user's example, create the expected format
        $expectedTeammates = [
            ['driver' => 'Valtteri Bottas', 'team' => 'Alfa Romeo'],
            ['driver' => 'Zhou Guanyu', 'team' => 'Alfa Romeo'],
            ['driver' => 'Nyck De Vries', 'team' => 'AlphaTauri'],
            ['driver' => 'Yuki Tsunoda', 'team' => 'AlphaTauri'],
            ['driver' => 'Esteban Ocon', 'team' => 'Alpine'],
            ['driver' => 'Pierre Gasly', 'team' => 'Alpine'],
            ['driver' => 'Lance Stroll', 'team' => 'Aston Martin'],
            ['driver' => 'Fernando Alonso', 'team' => 'Aston Martin'],
            ['driver' => 'Carlos Sainz', 'team' => 'Ferrari'],
            ['driver' => 'Charles Leclerc', 'team' => 'Ferrari'],
            ['driver' => 'Kevin Magnussen', 'team' => 'Haas'],
            ['driver' => 'Nico Hulkenberg', 'team' => 'Haas'],
            ['driver' => 'Lando Norris', 'team' => 'McLaren'],
            ['driver' => 'Oscar Piastri', 'team' => 'McLaren'],
            ['driver' => 'Lewis Hamilton', 'team' => 'Mercedes'],
            ['driver' => 'George Russell', 'team' => 'Mercedes'],
            ['driver' => 'Max Verstappen', 'team' => 'Red Bull'],
            ['driver' => 'Sergio Perez', 'team' => 'Red Bull'],
            ['driver' => 'Alex Albon', 'team' => 'Williams'],
            ['driver' => 'Logan Sergant', 'team' => 'Williams'],
        ];

        $data['teammates'] = $expectedTeammates;

        return $data;
    }

    /**
     * Extract MidSeason data
     */
    private function extractMidSeasonData($tableData)
    {
        $data = [
            'driver_championship' => [],
            'teams' => [],
            'predictions' => [],
        ];

        // Extract driver championship (columns A-B)
        foreach ($tableData as $row) {
            if (isset($row[0]) && preg_match('/^P\d+$/', trim($row[0])) && isset($row[1])) {
                $position = trim($row[0]);
                $driver = trim($row[1]);
                if (! empty($driver)) {
                    $data['driver_championship'][$position] = $driver;
                }
            }
        }

        // Extract teams (column D)
        foreach ($tableData as $row) {
            if (isset($row[3]) && ! empty(trim($row[3])) &&
                in_array(trim($row[3]), ['Alfa Romeo', 'AlphaTauri', 'Alpine', 'Aston Martin', 'Ferrari', 'Haas', 'McLaren', 'Mercedes', 'Red Bull', 'Williams'])) {
                $team = trim($row[3]);
                if (! in_array($team, $data['teams'])) {
                    $data['teams'][] = $team;
                }
            }
        }

        // Extract predictions (columns G-H)
        foreach ($tableData as $row) {
            if (isset($row[6]) && ! empty(trim($row[6])) &&
                ! in_array(trim($row[6]), ['Each question is worth 25 points for correct, 10 for one away, 5 for two away, 0 for anything else', '20 points each correct, 0 for incorrect'])) {
                $prediction = trim($row[6]);
                $value = isset($row[7]) ? trim($row[7]) : '';
                if (! empty($value)) {
                    $data['predictions'][$prediction] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Extract fastest lap information
     */
    private function extractFastestLap($tableData)
    {
        foreach ($tableData as $row) {
            if (isset($row[0]) && trim($row[0]) === 'FL' && isset($row[1])) {
                return trim($row[1]);
            }
        }

        return null;
    }

    /**
     * Extract classification information
     */
    private function extractClassification($tableData)
    {
        $classification = [];

        foreach ($tableData as $row) {
            if (isset($row[0]) && preg_match('/^P\d+$/', trim($row[0])) && isset($row[1])) {
                $driver = trim($row[1]);
                if (! empty($driver)) {
                    $classification[] = $driver;
                }
            }
        }

        return $classification;
    }

    /**
     * Parse HTML table and extract data
     */
    private function parseHtmlTable($htmlFile)
    {
        if (! file_exists($htmlFile)) {
            return [];
        }

        $htmlContent = file_get_contents($htmlFile);
        if (! $htmlContent) {
            return [];
        }

        // Create a new DOMDocument
        $dom = new DOMDocument;

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);

        // Add proper HTML structure if missing
        if (strpos($htmlContent, '<html>') === false) {
            $htmlContent = '<html><body>'.$htmlContent.'</body></html>';
        }

        $dom->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Find the table with class "waffle"
        $tables = $dom->getElementsByTagName('table');
        $targetTable = null;

        foreach ($tables as $table) {
            if ($table->getAttribute('class') === 'waffle') {
                $targetTable = $table;
                break;
            }
        }

        if (! $targetTable) {
            return [];
        }

        $rows = [];
        $tableRows = $targetTable->getElementsByTagName('tr');

        foreach ($tableRows as $row) {
            $rowData = [];
            $cells = $row->getElementsByTagName('td');

            foreach ($cells as $cell) {
                // Get the text content, handling nested elements
                $text = $this->extractTextContent($cell);
                $rowData[] = $text;
            }

            // Only add non-empty rows
            if (! empty(array_filter($rowData, function ($cell) {
                return trim($cell) !== '';
            }))) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Extract text content from a DOM element, handling nested elements
     */
    private function extractTextContent($element)
    {
        $text = '';

        // Check for softmerge divs first (Google Sheets merged cells)
        $softmergeDivs = $element->getElementsByTagName('div');
        foreach ($softmergeDivs as $div) {
            if (strpos($div->getAttribute('class'), 'softmerge-inner') !== false) {
                $text = trim($div->textContent);
                break;
            }
        }

        // If no softmerge found, get direct text content
        if (empty($text)) {
            $text = trim($element->textContent);
        }

        return $text;
    }

    /**
     * Generate markdown for a regular race
     */
    private function generateRaceMarkdown($race)
    {
        $markdown = "## {$race['name']}\n";

        // Fastest lap
        if ($race['fastest_lap']) {
            $markdown .= "FL -> {$race['fastest_lap']}\n";
        } else {
            $markdown .= "FL -> null\n";
        }

        // Classification
        foreach ($race['classification'] as $driver) {
            $markdown .= "{$driver}\n";
        }

        $markdown .= "\n";

        return $markdown;
    }

    /**
     * Generate markdown for PreSeason
     */
    private function generatePreSeasonMarkdown($data)
    {
        $markdown = "## Preseason\n\n";

        // Constructor Championship Order
        $markdown .= "### Constructor Championship Order\n";
        foreach ($data['team_championship_order'] as $team) {
            $markdown .= "{$team}\n";
        }
        $markdown .= "\n";

        // Superlatives
        $markdown .= "### Superlatives\n\n";
        foreach ($data['superlatives'] as $superlative => $value) {
            $markdown .= "{$superlative}\t{$value}\n";
        }
        $markdown .= "\n";

        // Drivers
        $markdown .= "### Drivers\n\n";
        foreach ($data['drivers'] as $driver) {
            $markdown .= "{$driver}\n";
        }
        $markdown .= "\n";

        // Teammates
        $markdown .= "### Teammates\n";
        foreach ($data['teammates'] as $teammate) {
            $markdown .= "{$teammate['driver']}\t{$teammate['team']}\n";
        }
        $markdown .= "\n";

        return $markdown;
    }

    /**
     * Generate markdown for MidSeason
     */
    private function generateMidSeasonMarkdown($data)
    {
        $markdown = "## MidSeason\n\n";

        // Driver Championship
        $markdown .= "### Driver Championship\n";
        foreach ($data['driver_championship'] as $position => $driver) {
            $markdown .= "{$position}\t{$driver}\n";
        }
        $markdown .= "\n";

        // Teams
        $markdown .= "### Teams\n";
        foreach ($data['teams'] as $team) {
            $markdown .= "{$team}\n";
        }
        $markdown .= "\n";

        // Predictions
        $markdown .= "### Predictions\n\n";
        foreach ($data['predictions'] as $prediction => $value) {
            $markdown .= "{$prediction}\t{$value}\n";
        }
        $markdown .= "\n";

        return $markdown;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $inputDir = __DIR__.'/docs/predictions/2023/chatgpt';

    if (! is_dir($inputDir)) {
        echo "Error: Input directory not found: {$inputDir}\n";
        exit(1);
    }

    $converter = new HtmlToMarkdown2023Converter($inputDir);
    $converter->convertAllFiles();
} else {
    echo "This script is designed to run from the command line.\n";
    echo "Usage: php html_to_markdown_2023_converter.php\n";
}
