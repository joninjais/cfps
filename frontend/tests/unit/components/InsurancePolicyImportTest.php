<?php

namespace frontend\tests\unit\components;

use frontend\controllers\InsurancepoliciesController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;
use yii\web\Session;

class InsurancePolicyImportTest extends TestCase
{
    public static function formats(): array
    {
        return [
            '48 columns' => [48, false],
            '44 columns' => [44, false],
            'legacy' => [26, false],
            'invalid allocation' => [48, true],
        ];
    }

    #[DataProvider('formats')]
    public function testImport(int $count, bool $invalid): void
    {
        $previousApp = Yii::$app;
        $aliases = [];
        foreach (['@webroot', '@app', '@runtime'] as $alias) {
            $aliases[$alias] = Yii::getAlias($alias, false);
        }
        $dir = sys_get_temp_dir() . '/cfps-policy-' . bin2hex(random_bytes(8));
        mkdir($dir);
        mkdir($dir . '/uploads');
        $file = $dir . '/uploads/test.xlsx';

        try {
            $app = new Application([
                'id' => 'policy-test',
                'basePath' => dirname(__DIR__, 3),
                'components' => [
                    'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                    'request' => ['class' => \yii\web\Request::class, 'scriptUrl' => '/index.php', 'hostInfo' => 'http://localhost'],
                    'errorHandler' => ['class' => PolicyTestErrorHandler::class],
                ],
            ]);
            Yii::setAlias('@webroot', $dir);
            $session = $invalid ? $this->createMock(Session::class) : $this->createStub(Session::class);
            if ($invalid) {
                $session->expects(self::once())->method('addFlash')
                    ->with('error', self::stringContains('ยอดจัดสรรไม่ถูกต้องในแถว 9 คอลัมน์ AC'));
            }
            $app->set('session', $session);
            $app->db->createCommand('CREATE TABLE xlsfileuploads (xlsid INTEGER PRIMARY KEY, exsfile TEXT, startdatetime TEXT, enddatetime TEXT, importstatus INTEGER)')->execute();
            $app->db->createCommand()->insert('xlsfileuploads', ['xlsid' => 1, 'exsfile' => 'test.xlsx', 'importstatus' => 0])->execute();
            $app->db->createCommand('CREATE TABLE fwfdatabase (id INTEGER PRIMARY KEY)')->execute();
            $baseColumns = [
                'agency_code', 'agency_name', 'province', 'f_code', 'passport_no', 'citizen_id',
                'alien_ref_id', 'title_name', 'full_name', 'nationality', 'ethnicity', 'hn_code',
                'purchase_date', 'effective_date', 'expiry_date', 'policy_status', 'card_price',
                'coverage_period', 'father_passport_no', 'father_citizen_id', 'father_alien_ref_id',
                'father_full_name', 'mother_passport_no', 'mother_citizen_id', 'mother_alien_ref_id', 'mother_full_name',
            ];
            $definitions = array_map(static fn($column) => '"' . $column . '" TEXT', $baseColumns);
            $app->db->createCommand('CREATE TABLE insurance_policies (id INTEGER PRIMARY KEY, ' . implode(', ', $definitions) . ')')->execute();
            require_once dirname(__DIR__, 4) . '/console/migrations/m261111_000001_extend_insurance_policies_for_excel.php';
            $migration = new class(['db' => $app->db, 'compact' => true]) extends \m261111_000001_extend_insurance_policies_for_excel {
                public function addColumn($table, $column, $type)
                {
                    $type->comment = null;
                    parent::addColumn($table, $column, $type);
                }
            };
            $migration->up();

            $sheet = (new Spreadsheet())->getActiveSheet();
            $sheet->setCellValue('A5', 'รหัสหน่วยงาน');
            $sheet->setCellValue('B5', 'หน่วยงาน');
            if ($count > 26) {
                $sheet->setCellValue('L5', 'วัน/เดือน/ปี เกิด');
                $sheet->setCellValue('AC5', 'การจัดสรรเงินบัตรประกันสุขภาพ');
                $sheet->setCellValue('AC6', 'หน่วยบริการ');
                $sheet->setCellValue('AD7', 'PP');
                $row = [
                    '00123', 'agency', 'agency province', 'F1', 'passport', 'citizen', 'alien', 'title',
                    'name', 'nation', 'ethnicity', '01/02/2560', '7 years', 'HN1', '12/3', '4',
                    'alley', 'road', 'home province', 'subdistrict', 'district', '00100',
                    '02/03/2567', '03/03/2567', '03/03/2568', 'active', '0000123', 2000,
                    101, 102, 103, 104, 105, 106, 107, 'one year', 'fp', 'fc', 'fa', 'father',
                    'mp', 'mc', 'ma', 'mother', 'occupation', 'employer', 'workplace', '0012345678',
                ];
                $sheet->fromArray(array_slice($row, 0, $count), null, 'A8');
                if ($invalid) {
                    $row[3] = 'F2';
                    $row[28] = 'invalid';
                    $sheet->fromArray($row, null, 'A9');
                }
            } else {
                $row = ['00123', 'agency', 'province', 'F1', 'passport', 'citizen', 'alien', 'title',
                    'name', 'nation', 'ethnicity', 'HN1', '02/03/2567', '03/03/2567',
                    '03/03/2568', 'active', 2000, 'one year', 'fp', 'fc', 'fa', 'father',
                    'mp', 'mc', 'ma', 'mother'];
                $sheet->fromArray($row, null, 'A6');
            }
            (new Xlsx($sheet->getParent()))->save($file);

            $controller = new InsurancepoliciesController('insurancepolicies', $app);
            $app->controller = $controller;
            self::assertSame(302, $controller->actionImport(1)->statusCode);
            $imported = $app->db->createCommand('SELECT * FROM insurance_policies')->queryAll();
            if ($invalid) {
                self::assertCount(0, $imported);
                self::assertSame(0, (int)$app->db->createCommand('SELECT importstatus FROM xlsfileuploads')->queryScalar());
                return;
            }
            self::assertCount(1, $imported);
            $record = $imported[0];
            self::assertSame('00123', $record['agency_code']);
            self::assertSame('2024-03-02', $record['purchase_date']);
            self::assertSame('mother', $record['mother_full_name']);
            foreach ([
                'agency_name' => 'agency', 'f_code' => 'F1', 'passport_no' => 'passport',
                'citizen_id' => 'citizen', 'alien_ref_id' => 'alien', 'title_name' => 'title',
                'full_name' => 'name', 'nationality' => 'nation', 'ethnicity' => 'ethnicity',
                'hn_code' => 'HN1', 'effective_date' => '2024-03-03', 'expiry_date' => '2025-03-03',
                'policy_status' => 'active', 'card_price' => 2000, 'coverage_period' => 'one year',
                'father_passport_no' => 'fp', 'father_citizen_id' => 'fc', 'father_alien_ref_id' => 'fa',
                'father_full_name' => 'father', 'mother_passport_no' => 'mp', 'mother_citizen_id' => 'mc',
                'mother_alien_ref_id' => 'ma',
            ] as $column => $value) {
                self::assertEquals($value, $record[$column], $column);
            }
            if ($count > 26) {
                $expected = [
                    'birth_date' => '2017-02-01', 'age_at_purchase' => '7 years', 'house_no' => '12/3',
                    'village_no' => '4', 'alley' => 'alley', 'road' => 'road', 'address_province' => 'home province',
                    'subdistrict' => 'subdistrict', 'district' => 'district', 'postal_code' => '00100',
                    'reference_no' => '0000123', 'allocation_service_unit' => 101, 'allocation_provincial_pp' => 102,
                    'allocation_provincial_admin' => 103, 'allocation_provincial_total' => 104,
                    'allocation_central_hc' => 105, 'allocation_central_admin' => 106, 'allocation_central_total' => 107,
                ];
                if ($count === 48) {
                    $expected += ['occupation' => 'occupation', 'employer_name' => 'employer',
                        'employer_address' => 'workplace', 'employer_phone' => '0012345678'];
                }
                foreach ($expected as $column => $value) {
                    self::assertEquals($value, $record[$column], $column);
                }
                if ($count === 44) {
                    self::assertNull($record['employer_phone']);
                }
            } else {
                self::assertNull($record['birth_date']);
            }
            $controller->actionImport(1);
            self::assertSame(1, (int)$app->db->createCommand('SELECT COUNT(*) FROM insurance_policies')->queryScalar());
        } finally {
            Yii::$app = $previousApp;
            foreach ($aliases as $alias => $value) {
                Yii::setAlias($alias, $value === false ? null : $value);
            }
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($dir . '/uploads');
            rmdir($dir);
        }
    }
}

class PolicyTestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}
