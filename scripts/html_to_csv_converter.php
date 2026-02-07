<?php

/**
 * HTML to CSV Converter for Formula 1 Predictions
 * Converts Google Sheets HTML export to CSV format
 */
class HtmlToCsvConverter
{
    private $inputDir;

    private $outputDir;

    public function __construct($inputDir, $outputDir = null)
    {
        $this->inputDir = rtrim($inputDir, '/');
        $this->outputDir = $outputDir ? rtrim($outputDir, '/') : $inputDir;
    }

    /**
     * Convert a single HTML file to CSV
     */
    public function convertFile($htmlFile)
    {
        $htmlPath = $this->inputDir.'/'.$htmlFile;

        if (! file_exists($htmlPath)) {
            echo "Error: File not found: {$htmlPath}\n";

            return false;
        }

        $htmlContent = file_get_contents($htmlPath);
        if (! $htmlContent) {
            echo "Error: Could not read file: {$htmlPath}\n";

            return false;
        }

        // Parse the HTML table
        $tableData = $this->parseHtmlTable($htmlContent);

        if (empty($tableData)) {
            echo "Error: No table data found in: {$htmlFile}\n";

            return false;
        }

        // Generate CSV filename
        $csvFile = str_replace('.html', '.csv', $htmlFile);
        $csvPath = $this->outputDir.'/'.$csvFile;

        // Write CSV file
        $success = $this->writeCsvFile($csvPath, $tableData);

        if ($success) {
            echo "Converted: {$htmlFile} -> {$csvFile}\n";
        } else {
            echo "Error: Failed to write CSV file: {$csvPath}\n";
        }

        return $success;
    }

    /**
     * Convert all HTML files in the directory
     */
    public function convertAllFiles()
    {
        $htmlFiles = glob($this->inputDir.'/*.html');

        if (empty($htmlFiles)) {
            echo "No HTML files found in: {$this->inputDir}\n";

            return;
        }

        echo 'Found '.count($htmlFiles)." HTML files to convert.\n\n";

        $successCount = 0;
        foreach ($htmlFiles as $htmlFile) {
            $filename = basename($htmlFile);
            if ($this->convertFile($filename)) {
                $successCount++;
            }
        }

        echo "\nConversion complete: {$successCount} files converted successfully.\n";
    }

    /**
     * Parse HTML table and extract data
     */
    private function parseHtmlTable($htmlContent)
    {
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
            echo "Error: No table with class 'waffle' found\n";

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
     * Write data to CSV file
     */
    private function writeCsvFile($csvPath, $tableData)
    {
        $handle = fopen($csvPath, 'w');
        if (! $handle) {
            return false;
        }

        foreach ($tableData as $row) {
            // Ensure all rows have the same number of columns
            $maxCols = max(array_map('count', $tableData));
            while (count($row) < $maxCols) {
                $row[] = '';
            }

            // Write the row to CSV
            fputcsv($handle, $row);
        }

        fclose($handle);

        return true;
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $inputDir = __DIR__.'/docs/predictions/2022/sunny';

    if (! is_dir($inputDir)) {
        echo "Error: Input directory not found: {$inputDir}\n";
        exit(1);
    }

    $converter = new HtmlToCsvConverter($inputDir);

    // Test with Abu Dhabi.html first
    echo "Testing conversion with Abu Dhabi.html...\n";
    $testResult = $converter->convertFile('Abu Dhabi.html');

    if ($testResult) {
        echo "\nTest successful! Converting all HTML files...\n\n";
        $converter->convertAllFiles();
    } else {
        echo "\nTest failed. Please check the HTML file format.\n";
        exit(1);
    }
} else {
    echo "This script is designed to run from the command line.\n";
    echo "Usage: php html_to_csv_converter.php\n";
}
