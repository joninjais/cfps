<?php

namespace frontend\components;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelImportHeaders
{
    public const OP = [
        'hospcode', 'dateserv', 'name', 'fund', 'cid', 'hn', 'seq', 'icd10',
        'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8',
        'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16',
        'alltotal', 'refer_number', 'refertohospcode', 'sex', 'p_age', 'nation',
    ];

    public const IP = [
        'hospcode', 'admitdate', 'dischartdate', 'name', 'fund', 'cid', 'hn',
        'an', 'icd10', 'icd9', 'los', 'cost', 'adjrw', 'costadjrw',
        'refer_number', 'refertohospcode', 'sex', 'p_age', 'nation',
    ];

    public static function validate(Worksheet $sheet, array $expected): ?string
    {
        $lastColumn = max(count($expected), Coordinate::columnIndexFromString($sheet->getHighestDataColumn(1)));
        $errors = [];

        for ($i = 1; $i <= $lastColumn; $i++) {
            $column = Coordinate::stringFromColumnIndex($i);
            $value = $sheet->getCell($column . '1')->getValue();
            if ($i <= count($expected)) {
                if (!is_scalar($value) || strcasecmp((string)$value, $expected[$i - 1]) !== 0) {
                    $errors[] = $column . ' ต้องเป็น ' . $expected[$i - 1];
                }
            } elseif ($value !== null && $value !== '') {
                $errors[] = $column . ' เป็นคอลัมน์เกิน';
            }
        }

        return $errors === [] ? null : 'ไม่นำเข้าข้อมูล: หัวตารางแถวที่ 1 ไม่ตรงกับรูปแบบที่กำหนด: '
            . implode(', ', $errors);
    }
}
