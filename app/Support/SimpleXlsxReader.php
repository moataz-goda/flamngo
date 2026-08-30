<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Minimal XLSX reader for simple tabular sheets (shared strings + inline strings).
 */
class SimpleXlsxReader
{
    /**
     * @return list<list<string>>
     */
    public function rows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException(__('Unable to read Excel file.'));
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('Unable to read Excel file.'));
        }

        $sharedStrings = $this->parseSharedStrings($zip->getFromName('xl/sharedStrings.xml') ?: '');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

        if ($sheetXml === false) {
            // Some files use different sheet names; take the first worksheet entry.
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (is_string($name) && str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            throw new RuntimeException(__('Unable to read Excel file.'));
        }

        return $this->parseSheet($sheetXml, $sharedStrings);
    }

    /**
     * @return list<string>
     */
    protected function parseSharedStrings(string $xml): array
    {
        if ($xml === '') {
            return [];
        }

        $strings = [];
        $sx = @simplexml_load_string($xml);

        if (! $sx) {
            return [];
        }

        foreach ($sx->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;

                continue;
            }

            $text = '';
            if (isset($si->r)) {
                foreach ($si->r as $run) {
                    $text .= (string) ($run->t ?? '');
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param  list<string>  $sharedStrings
     * @return list<list<string>>
     */
    protected function parseSheet(string $xml, array $sharedStrings): array
    {
        $sx = @simplexml_load_string($xml);

        if (! $sx || ! isset($sx->sheetData)) {
            throw new RuntimeException(__('Unable to read Excel file.'));
        }

        $rows = [];

        foreach ($sx->sheetData->row as $row) {
            $cells = [];
            $maxIndex = -1;

            foreach ($row->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $colIndex = $this->columnIndex($ref);
                $maxIndex = max($maxIndex, $colIndex);
                $cells[$colIndex] = $this->cellValue($cell, $sharedStrings);
            }

            if ($maxIndex < 0) {
                $rows[] = [];

                continue;
            }

            $line = [];
            for ($i = 0; $i <= $maxIndex; $i++) {
                $line[] = $cells[$i] ?? '';
            }
            $rows[] = $line;
        }

        return $rows;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    protected function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 's') {
            $index = (int) ($cell->v ?? -1);

            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string) ($cell->is->t ?? '');
        }

        if ($type === 'b') {
            return ((string) ($cell->v ?? '0')) === '1' ? '1' : '0';
        }

        return (string) ($cell->v ?? '');
    }

    protected function columnIndex(string $cellRef): int
    {
        if (! preg_match('/^([A-Z]+)/', strtoupper($cellRef), $matches)) {
            return 0;
        }

        $letters = $matches[1];
        $index = 0;

        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }
}
