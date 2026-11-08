<?php

namespace frontend\tests\unit\components;

use frontend\components\ExcelImportHeaders;
use frontend\controllers\IpdatainfoController;
use frontend\controllers\OpdatainfoController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;
use yii\web\Session;

class TestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}

class ExcelImportControllerTest extends TestCase
{
    public static function importProvider(): array
    {
        return [
            'OP invalid' => [OpdatainfoController::class, ExcelImportHeaders::OP, false],
            'IP invalid' => [IpdatainfoController::class, ExcelImportHeaders::IP, false],
            'OP valid' => [OpdatainfoController::class, ExcelImportHeaders::OP, true],
            'IP valid' => [IpdatainfoController::class, ExcelImportHeaders::IP, true],
        ];
    }

    #[DataProvider('importProvider')]
    public function testHeaderValidationBeforeDatabaseWrites(string $controllerClass, array $headers, bool $valid): void
    {
        $previousApp = Yii::$app;
        $previousWebroot = Yii::getAlias('@webroot', false);
        $previousAppAlias = Yii::getAlias('@app', false);
        $previousRuntime = Yii::getAlias('@runtime', false);
        $dir = sys_get_temp_dir() . '/cfps-excel-test-' . bin2hex(random_bytes(8));
        mkdir($dir);
        mkdir($dir . '/uploads');
        $file = $dir . '/uploads/test.xlsx';

        try {
            $app = new Application([
                'id' => 'excel-import-test',
                'basePath' => dirname(__DIR__, 3),
                'components' => [
                    'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                    'request' => ['class' => \yii\web\Request::class, 'scriptUrl' => '/index.php', 'hostInfo' => 'http://localhost'],
                    'errorHandler' => ['class' => TestErrorHandler::class],
                ],
            ]);
            Yii::setAlias('@webroot', $dir);

            $session = $this->createMock(Session::class);
            $session->method('get')->willReturn('00064');
            $session->expects($valid ? self::never() : self::once())
                ->method('addFlash')
                ->with('error', self::stringContains('A ต้องเป็น hospcode'));
            $app->set('session', $session);

            $app->db->createCommand(
                'CREATE TABLE xlsfileuploads (xlsid INTEGER PRIMARY KEY, exsfile TEXT, hoscode TEXT, startdatetime TEXT, enddatetime TEXT, importstatus INTEGER)'
            )->execute();
            $app->db->createCommand(
                'CREATE TABLE fwfdatabase (CID TEXT, FundHolderID TEXT, PeriodStartDate TEXT, PeriodEndDate TEXT)'
            )->execute();
            $table = $controllerClass === OpdatainfoController::class ? 'opdata' : 'ipdata';
            $columns = array_merge($headers, ['FundHolderID', 'exsfile']);
            if ($table === 'opdata') {
                $columns[] = 'op_pp_type';
                $app->db->pdo->sqliteCreateFunction('fn_get_op_pp_type', static fn($icd10) => 'OP', 1);
            }
            $columnDefinitions = array_map(static fn($column) => '"' . $column . '" TEXT', $columns);
            $app->db->createCommand('CREATE TABLE ' . $table . ' (' . implode(', ', $columnDefinitions) . ')')->execute();
            $app->db->createCommand()->insert('xlsfileuploads', [
                'xlsid' => 1,
                'exsfile' => 'test.xlsx',
                'hoscode' => '00064',
                'startdatetime' => '2020-01-01 00:00:00',
                'importstatus' => 0,
            ])->execute();

            if (!$valid) {
                $headers[0] = 'wrong';
            }
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($headers, null, 'A1');
            $values = array_fill_keys($valid ? $headers : array_merge(['hospcode'], array_slice($headers, 1)), '');
            $values['hospcode'] = '00064';
            $values['name'] = 'Test';
            $values['nation'] = '048';
            $values['refer_number'] = 'REF123';
            $values['refertohospcode'] = '09113';
            $values['sex'] = '1';
            $values['p_age'] = 35;
            if ($table === 'opdata') {
                $values['dateserv'] = '2024-01-02';
                $values['seq'] = 'SEQ123';
                $values['icd10'] = 'A00';
                $values['m1'] = 100;
                $values['m2'] = 200;
            } else {
                $values['admitdate'] = '2024-01-02';
                $values['dischartdate'] = '2024-01-03';
                $values['an'] = 'AN123';
                $values['adjrw'] = 1;
            }
            $spreadsheet->getActiveSheet()->fromArray(array_values($values), null, 'A2');
            (new Xlsx($spreadsheet))->save($file);

            $controller = new $controllerClass('import-test', $app);
            $app->controller = $controller;
            $response = $controller->actionImport(1);
            self::assertSame(302, $response->statusCode);

            $row = $app->db->createCommand('SELECT startdatetime, importstatus FROM xlsfileuploads WHERE xlsid = 1')->queryOne();
            if ($valid) {
                self::assertNotSame('2020-01-01 00:00:00', $row['startdatetime']);
                self::assertSame(1, (int)$row['importstatus']);
                $imported = $app->db->createCommand('SELECT * FROM ' . $table)->queryAll();
                self::assertCount(1, $imported);
                self::assertSame('REF123', $imported[0]['refer_number']);
                self::assertSame('09113', $imported[0]['refertohospcode']);
                self::assertSame('048', $imported[0]['nation']);
                self::assertSame('35', $imported[0]['p_age']);
                self::assertSame($table === 'opdata' ? '300' : '8600',
                    $imported[0][$table === 'opdata' ? 'alltotal' : 'costadjrw']);
            } else {
                self::assertSame('2020-01-01 00:00:00', $row['startdatetime']);
                self::assertSame(0, (int)$row['importstatus']);
                self::assertSame(0, (int)$app->db->createCommand('SELECT COUNT(*) FROM ' . $table)->queryScalar());
            }
        } finally {
            Yii::$app = $previousApp;
            Yii::setAlias('@webroot', $previousWebroot === false ? null : $previousWebroot);
            Yii::setAlias('@app', $previousAppAlias === false ? null : $previousAppAlias);
            Yii::setAlias('@runtime', $previousRuntime === false ? null : $previousRuntime);
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($dir . '/uploads');
            rmdir($dir);
        }
    }
}
