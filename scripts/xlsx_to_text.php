<?php

/**
 * Extract rules (formulas, validations) and values from .xlsx files to plain text.
 * .xlsx is a ZIP of XML; this script unzips and parses without external deps.
 *
 * Usage:
 *   php xlsx_to_text.php file1.xlsx [file2.xlsx ...]
 *   php xlsx_to_text.php --dir /path/to/folder
 *   php xlsx_to_text.php --dir . --out ./export
 *
 * Output: one .txt file per input (or stdout with --stdout), markdown-style
 * with sections per sheet: cell ref, value, formula (if any).
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);
$args = array_slice($argv, 1);
$outDir = $baseDir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'xlsx_export';
$stdout = false;
$paths = [];

for ($i = 0; $i < count($args); $i++) {
    if ($args[$i] === '--dir' && isset($args[$i + 1])) {
        $dir = $args[$i + 1];
        $i++;
        if ($dir === '.' || $dir === '') {
            $dir = getcwd() ?: $baseDir;
        } else {
            $dir = str_starts_with($dir, '/') || preg_match('#^[A-Za-z]:#', $dir) ? $dir : $baseDir.DIRECTORY_SEPARATOR.$dir;
        }
        $paths = array_merge($paths, glob($dir.DIRECTORY_SEPARATOR.'*.xlsx') ?: []);

        continue;
    }
    if ($args[$i] === '--out' && isset($args[$i + 1])) {
        $outDir = $args[$i + 1];
        $i++;

        continue;
    }
    if ($args[$i] === '--stdout') {
        $stdout = true;

        continue;
    }
    if (pathinfo($args[$i], PATHINFO_EXTENSION) === 'xlsx') {
        $paths[] = $args[$i];
    }
}

$paths = array_unique(array_filter($paths, 'is_file'));
if (empty($paths)) {
    echo "Usage: php xlsx_to_text.php file1.xlsx [file2.xlsx ...]\n";
    echo "       php xlsx_to_text.php --dir <folder> [--out <dir>] [--stdout]\n";
    exit(1);
}

if (! $stdout && ! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$extractor = new XlsxToTextExtractor;

foreach ($paths as $path) {
    $out = $extractor->extract($path);
    $basename = pathinfo($path, PATHINFO_FILENAME);
    if ($stdout) {
        echo "### FILE: {$basename}.xlsx\n\n".$out."\n\n";
    } else {
        $outPath = $outDir.'/'.$basename.'.xlsx.txt';
        file_put_contents($outPath, $out);
        echo "Wrote: {$outPath}\n";
    }
}

echo "Done.\n";

// region Extractor

class XlsxToTextExtractor
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const NS_PKG_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    public function extract(string $xlsxPath): string
    {
        $zip = new ZipArchive;
        if (! $zip->open($xlsxPath, ZipArchive::RDONLY)) {
            return "# Error: could not open as ZIP: {$xlsxPath}\n";
        }

        $sharedStrings = $this->loadSharedStrings($zip);
        $sheetList = $this->loadWorkbookSheets($zip);
        $buf = '# '.basename($xlsxPath)."\n\n";

        foreach ($sheetList as $name => $sheetPath) {
            $buf .= '## Sheet: '.$name."\n\n";
            $buf .= $this->dumpSheet($zip, 'xl/'.$sheetPath, $sharedStrings);
            $buf .= "\n";
        }

        $zip->close();

        return $buf;
    }

    /**
     * @return array<int, string>
     */
    private function loadSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }
        $doc = $this->loadXml($xml);
        if (! $doc) {
            return [];
        }
        $root = $doc->documentElement;
        $list = [];
        $idx = 0;
        foreach ($root->getElementsByTagNameNS(self::NS_MAIN, 'si') as $si) {
            $list[$idx++] = $this->getSiText($si);
        }

        return $list;
    }

    private function getSiText(\DOMElement $si): string
    {
        $t = $si->getElementsByTagNameNS(self::NS_MAIN, 't')->item(0);
        if ($t !== null) {
            return $t->textContent;
        }
        $parts = [];
        foreach ($si->getElementsByTagNameNS(self::NS_MAIN, 'r') as $r) {
            $t = $r->getElementsByTagNameNS(self::NS_MAIN, 't')->item(0);
            $parts[] = $t ? $t->textContent : '';
        }

        return implode('', $parts);
    }

    /**
     * @return array<string, string> sheet name => path (e.g. worksheets/sheet1.xml)
     */
    private function loadWorkbookSheets(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            return [];
        }
        $wb = $this->loadXml($workbookXml);
        $rels = $this->loadXml($relsXml);
        if (! $wb || ! $rels) {
            return [];
        }

        $idToTarget = [];
        $relList = $rels->documentElement->getElementsByTagNameNS(self::NS_PKG_REL, 'Relationship');
        if ($relList->length === 0) {
            foreach ($rels->documentElement->childNodes as $rel) {
                if ($rel instanceof \DOMElement && $rel->localName === 'Relationship') {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    if ($id !== '' && $target !== '') {
                        $idToTarget[$id] = $target;
                    }
                }
            }
        } else {
            foreach ($relList as $rel) {
                $id = $rel->getAttribute('Id');
                $target = $rel->getAttribute('Target');
                if ($id !== '' && $target !== '') {
                    $idToTarget[$id] = $target;
                }
            }
        }

        $sheets = [];
        foreach ($wb->documentElement->getElementsByTagNameNS(self::NS_MAIN, 'sheet') as $sheet) {
            $name = $sheet->getAttribute('name');
            $rid = $sheet->getAttributeNS(self::NS_REL, 'id') ?: $sheet->getAttribute('r:id');
            if ($name !== '' && $rid !== '' && isset($idToTarget[$rid])) {
                $sheets[$name] = $idToTarget[$rid];
            }
        }

        return $sheets;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function dumpSheet(ZipArchive $zip, string $entry, array $sharedStrings): string
    {
        $xml = $zip->getFromName($entry);
        if ($xml === false) {
            return "(could not read sheet)\n";
        }
        $doc = $this->loadXml($xml);
        if (! $doc) {
            return "(invalid sheet XML)\n";
        }

        $rows = [];
        $sheetData = $doc->getElementsByTagNameNS(self::NS_MAIN, 'sheetData')->item(0);
        if (! $sheetData) {
            return "(no sheetData)\n";
        }
        foreach ($sheetData->getElementsByTagNameNS(self::NS_MAIN, 'row') as $row) {
            $r = $row->getAttribute('r');
            foreach ($row->getElementsByTagNameNS(self::NS_MAIN, 'c') as $c) {
                $ref = $c->getAttribute('r');
                $t = $c->getAttribute('t');
                $f = $c->getAttribute('f');
                $vNode = $c->getElementsByTagNameNS(self::NS_MAIN, 'v')->item(0);
                $val = $vNode !== null ? trim($vNode->textContent) : '';

                if ($t === 's' && $val !== '' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }
                $line = $ref.' = '.$val;
                if ($f !== '') {
                    $line .= '  [formula: '.$f.']';
                }
                $rows[] = $line;
            }
        }

        $out = implode("\n", $rows);
        if ($out === '') {
            return "(no cells)\n";
        }

        $validations = $this->dumpDataValidations($doc);
        if ($validations !== '') {
            $out .= "\n\n### Data validations\n\n".$validations;
        }

        return $out."\n";
    }

    private function dumpDataValidations(\DOMDocument $doc): string
    {
        $dv = $doc->getElementsByTagNameNS(self::NS_MAIN, 'dataValidations')->item(0);
        if (! $dv) {
            return '';
        }
        $lines = [];
        foreach ($doc->getElementsByTagNameNS(self::NS_MAIN, 'dataValidation') as $d) {
            $ref = $d->getAttribute('sqref') ?: $d->getAttribute('type');
            $type = $d->getAttribute('type');
            $formula1 = $d->getElementsByTagNameNS(self::NS_MAIN, 'formula1')->item(0);
            $formula2 = $d->getElementsByTagNameNS(self::NS_MAIN, 'formula2')->item(0);
            $f1 = $formula1 ? $formula1->textContent : '';
            $f2 = $formula2 ? $formula2->textContent : '';
            $lines[] = $ref.' type='.$type.' formula1='.$f1.($f2 !== '' ? ' formula2='.$f2 : '');
        }

        return implode("\n", $lines);
    }

    private function loadXml(string $xml): ?\DOMDocument
    {
        $doc = new \DOMDocument;
        $doc->preserveWhiteSpace = false;
        $old = libxml_use_internal_errors(true);
        $ok = $doc->loadXML($xml);
        libxml_use_internal_errors($old);
        if (! $ok) {
            return null;
        }

        return $doc;
    }
}

// endregion
