<?php

/**
 * CSV to Markdown Converter for Formula 1 Predictions
 * Extracts race information from CSV files and generates a consolidated markdown file
 */

class CsvToMarkdownConverter
{
    private $csvDir;
    private $outputFile;

    public function __construct($csvDir, $outputFile = null)
    {
        $this->csvDir = rtrim($csvDir, '/');
        $this->outputFile = $outputFile ?: $this->csvDir . '/predictions.md';
    }

    /**
     * Convert all CSV files to markdown format
     */
    public function convertAllFiles()
    {
        $csvFiles = glob($this->csvDir . '/*.csv');
        
        if (empty($csvFiles)) {
            echo "No CSV files found in: {$this->csvDir}\n";
            return;
        }

        echo "Found " . count($csvFiles) . " CSV files to process.\n\n";

        $markdownContent = "# Formula 1 2022 Predictions\n\n";
        $raceData = [];

        foreach ($csvFiles as $csvFile) {
            $filename = basename($csvFile, '.csv');
            echo "Processing: {$filename}.csv\n";
            
            $raceInfo = $this->extractRaceInfo($csvFile, $filename);
            if ($raceInfo) {
                $raceData[] = $raceInfo;
            }
        }

        // Sort races alphabetically
        usort($raceData, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Generate markdown content
        foreach ($raceData as $race) {
            $markdownContent .= $this->generateRaceMarkdown($race);
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
     * Extract race information from a CSV file
     */
    private function extractRaceInfo($csvFile, $raceName)
    {
        $csvData = $this->readCsvFile($csvFile);
        if (empty($csvData)) {
            echo "  Error: Could not read CSV file\n";
            return null;
        }

        $raceInfo = [
            'name' => $raceName,
            'fastest_lap' => null,
            'has_dnf' => false,
            'classification' => []
        ];

        // Check if file has DNF column (like Bahrain)
        $hasDnfColumn = false;
        if (isset($csvData[0])) {
            $headers = $csvData[0];
            $hasDnfColumn = in_array('DNF?', $headers);
            $raceInfo['has_dnf'] = $hasDnfColumn;
            

        }

        // Extract fastest lap
        $raceInfo['fastest_lap'] = $this->extractFastestLap($csvData);

        // Extract classification
        $raceInfo['classification'] = $this->extractClassification($csvData, $hasDnfColumn);

        return $raceInfo;
    }

    /**
     * Extract fastest lap information
     */
    private function extractFastestLap($csvData)
    {
        foreach ($csvData as $row) {
            // Check for FL in different possible positions
            if (isset($row[1]) && trim($row[1]) === 'FL') {
                return isset($row[2]) ? trim($row[2]) : null;
            }
            // Some files might have FL in a different position
            foreach ($row as $cell) {
                if (trim($cell) === 'FL') {
                    $index = array_search($cell, $row);
                    return isset($row[$index + 1]) ? trim($row[$index + 1]) : null;
                }
            }
        }
        return null;
    }

    /**
     * Extract classification information
     */
    private function extractClassification($csvData, $hasDnfColumn)
    {
        $classification = [];
        
        foreach ($csvData as $rowIndex => $row) {
            // Look for classification rows (P1, P2, etc.)
            $positionIndex = -1;
            $driverIndex = -1;
            $dnfIndex = -1;
            
            // Find the position of P1, P2, etc.
            foreach ($row as $index => $cell) {
                if (preg_match('/^P\d+$/', trim($cell))) {
                    $positionIndex = $index;
                    $driverIndex = $index + 1;
                    // For DNF files, the DNF column is right after the driver name
                    $dnfIndex = $index + 2;
                    break;
                }
            }
            
            if ($positionIndex >= 0 && isset($row[$driverIndex])) {
                $driver = trim($row[$driverIndex]);
                
                if (!empty($driver)) {
                    $entry = ['driver' => $driver];
                    
                    // Add DNF information if available
                    if ($hasDnfColumn && isset($row[$dnfIndex])) {
                        $dnfValue = trim($row[$dnfIndex]);
                        // Empty DNF column means TRUE, explicit FALSE means FALSE
                        $entry['dnf'] = empty($dnfValue) || strtoupper($dnfValue) === 'TRUE';
                        

                    }
                    
                    $classification[] = $entry;
                }
            }
        }
        
        // If no drivers found in main classification, try to extract from "Unpicked Drivers" section
        if (empty($classification)) {
            $classification = $this->extractFromUnpickedDrivers($csvData, $hasDnfColumn);
        }
        
        return $classification;
    }

    /**
     * Extract driver information from "Unpicked Drivers" section
     */
    private function extractFromUnpickedDrivers($csvData, $hasDnfColumn)
    {
        $classification = [];
        
        foreach ($csvData as $rowIndex => $row) {
            // Look for rows that have team names (indicating unpicked drivers section)
            foreach ($row as $index => $cell) {
                if (in_array(trim($cell), ['Ferrari', 'Red Bull', 'Mercedes', 'McLaren', 'Alpine', 'Alpha Tauri', 'Aston Martin', 'Williams', 'Alfa Romeo', 'Haas'])) {
                    // Found a team name, the driver should be in the next column
                    if (isset($row[$index + 1])) {
                        $driver = trim($row[$index + 1]);
                        if (!empty($driver) && !in_array($driver, ['Name', 'Team'])) {
                            $entry = ['driver' => $driver];
                            
                            // For Spain, all DNF values are FALSE
                            if ($hasDnfColumn) {
                                $entry['dnf'] = false;
                            }
                            
                            $classification[] = $entry;
                        }
                    }
                    break;
                }
            }
        }
        
        return $classification;
    }

    /**
     * Read CSV file and return as array
     */
    private function readCsvFile($csvFile)
    {
        if (!file_exists($csvFile)) {
            return [];
        }

        $data = [];
        $handle = fopen($csvFile, 'r');
        
        if (!$handle) {
            return [];
        }

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = $row;
        }

        fclose($handle);
        return $data;
    }

    /**
     * Generate markdown for a single race
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
        
        // DNF section if applicable
        if ($race['has_dnf']) {
            $markdown .= "Driver DNF\n";
        }
        
        // Classification
        foreach ($race['classification'] as $entry) {
            $driver = $entry['driver'];
            
            if ($race['has_dnf'] && isset($entry['dnf'])) {
                $dnfStatus = $entry['dnf'] ? 'TRUE' : 'FALSE';
                $markdown .= "{$driver}\t{$dnfStatus}\n";
            } else {
                $markdown .= "{$driver}\n";
            }
        }
        
        $markdown .= "\n";
        return $markdown;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $csvDir = __DIR__ . '/docs/predictions/2022/sunny';
    
    if (!is_dir($csvDir)) {
        echo "Error: CSV directory not found: {$csvDir}\n";
        exit(1);
    }

    $converter = new CsvToMarkdownConverter($csvDir);
    $converter->convertAllFiles();
} else {
    echo "This script is designed to run from the command line.\n";
    echo "Usage: php csv_to_markdown_converter.php\n";
}
