<?php

namespace frontend\tests\unit\components;

use frontend\components\ExcelImportHeaders;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExcelImportHeadersTest extends TestCase
{
    public static function headerProvider(): array
    {
        return [
            'OP' => [[
                'hospcode', 'dateserv', 'name', 'fund', 'cid', 'hn', 'seq', 'icd10',
                'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8',
                'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16',
                'alltotal', 'refer_number', 'refertohospcode', 'sex', 'p_age', 'nation',
            ], ExcelImportHeaders::OP],
            'IP' => [[
                'hospcode', 'admitdate', 'dischartdate', 'name', 'fund', 'cid', 'hn',
                'an', 'icd10', 'icd9', 'los', 'cost', 'adjrw', 'costadjrw',
                'refer_number', 'refertohospcode', 'sex', 'p_age', 'nation',
            ], ExcelImportHeaders::IP],
        ];
    }

    #[DataProvider('headerProvider')]
    public function testValidAndInvalidHeaders(array $headers, array $expected): void
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        self::assertNull(ExcelImportHeaders::validate($sheet, $expected));

        $sheet->fromArray(array_map('strtoupper', $headers), null, 'A1');
        self::assertNull(ExcelImportHeaders::validate($sheet, $expected));
        $mixedCaseHeaders = array_map(
            static fn(string $header, int $index): string => $index % 2 === 0
                ? strtoupper($header)
                : $header,
            $headers,
            array_keys($headers)
        );
        $sheet->fromArray($mixedCaseHeaders, null, 'A1');
        self::assertNull(ExcelImportHeaders::validate($sheet, $expected));

        $sheet->fromArray($headers, null, 'A1');
        $sheet->setCellValue('A1', 'wrong');
        self::assertStringContainsString('A ต้องเป็น hospcode', ExcelImportHeaders::validate($sheet, $expected));
        $sheet->setCellValue('A1', 'hospcode');

        $sheet->setCellValue('B1', $headers[2]);
        $sheet->setCellValue('C1', $headers[1]);
        $error = ExcelImportHeaders::validate($sheet, $expected);
        self::assertStringContainsString('B ต้องเป็น ' . $headers[1], $error);
        self::assertStringContainsString('C ต้องเป็น ' . $headers[2], $error);
        $sheet->setCellValue('B1', $headers[1]);
        $sheet->setCellValue('C1', $headers[2]);

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->setCellValue($lastColumn . '1', null);
        self::assertStringContainsString($lastColumn . ' ต้องเป็น nation', ExcelImportHeaders::validate($sheet, $expected));
        $sheet->setCellValue($lastColumn . '1', 'nation');

        $extraColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers) + 1);
        $sheet->setCellValue($extraColumn . '1', '<script>alert(1)</script>');
        $error = ExcelImportHeaders::validate($sheet, $expected);
        self::assertStringContainsString($extraColumn . ' เป็นคอลัมน์เกิน', $error);
        self::assertStringNotContainsString('<script>', $error);
        $sheet->setCellValue($extraColumn . '1', null);
        self::assertNull(ExcelImportHeaders::validate($sheet, $expected));
    }
}
