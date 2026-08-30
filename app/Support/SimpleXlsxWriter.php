<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Minimal XLSX writer (no external package). Opens correctly in Excel / LibreOffice.
 */
class SimpleXlsxWriter
{
    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function download(string $filename, array $headers, array $rows, string $sheetName = 'Sheet1'): never
    {
        $path = $this->writeTemp($headers, $rows, $sheetName);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.(string) filesize($path));
        header('Cache-Control: max-age=0');

        readfile($path);
        @unlink($path);
        exit;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    public function writeTemp(array $headers, array $rows, string $sheetName = 'Sheet1'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx_');
        if ($path === false) {
            throw new RuntimeException('Unable to create temporary Excel file.');
        }

        $xlsxPath = $path.'.xlsx';
        @unlink($path);

        $zip = new ZipArchive;
        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create Excel archive.');
        }

        $sheetName = $this->sanitizeSheetName($sheetName);
        $sheetXml = $this->sheetXml($headers, $rows);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->close();

        return $xlsxPath;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string|int|float|null>>  $rows
     */
    protected function sheetXml(array $headers, array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetData>';

        $xml .= $this->rowXml(1, $headers, true);

        $rowIndex = 2;
        foreach ($rows as $row) {
            $xml .= $this->rowXml($rowIndex, $row, false);
            $rowIndex++;
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    /**
     * @param  list<string|int|float|null>  $cells
     */
    protected function rowXml(int $rowIndex, array $cells, bool $header): string
    {
        $xml = '<row r="'.$rowIndex.'">';
        $col = 0;

        foreach ($cells as $value) {
            $ref = $this->cellRef($col, $rowIndex);
            $style = $header ? ' s="1"' : '';

            if (is_int($value) || is_float($value)) {
                $xml .= '<c r="'.$ref.'"'.$style.'><v>'.$this->xmlNumber($value).'</v></c>';
            } else {
                $text = $this->xml(trim((string) ($value ?? '')));
                $xml .= '<c r="'.$ref.'"'.$style.' t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>';
            }

            $col++;
        }

        return $xml.'</row>';
    }

    protected function cellRef(int $colIndex, int $rowIndex): string
    {
        $col = '';
        $n = $colIndex;

        do {
            $col = chr(65 + ($n % 26)).$col;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $col.$rowIndex;
    }

    protected function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    protected function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected function workbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$this->xml($sheetName).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    protected function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    protected function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            .'<borders count="1"><border/></borders>'
            .'<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            .'<cellXfs count="2">'
            .'<xf xfId="0"/>'
            .'<xf fontId="1" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'</styleSheet>';
    }

    protected function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\\\\\/\\?\\*\\[\\]:]/', '', $name) ?: 'Sheet1';

        return mb_substr($name, 0, 31);
    }

    protected function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function xmlNumber(int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    }
}
