<?php

namespace frontend\tests\unit\components;

use frontend\controllers\DeptorController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;

class DeptorTableErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}

class DeptorTableTest extends TestCase
{
    private $oldApp;
    private $oldAliases;
    private $app;
    private $controller;

    protected function setUp(): void
    {
        $this->oldApp = Yii::$app;
        foreach (['@app', '@runtime', '@webroot', '@web'] as $alias) {
            $this->oldAliases[$alias] = Yii::getAlias($alias, false);
        }
        $this->app = new Application([
            'id' => 'deptor-table-test',
            'basePath' => dirname(__DIR__, 3),
            'vendorPath' => dirname(__DIR__, 4) . '/vendor',
            'components' => [
                'errorHandler' => ['class' => DeptorTableErrorHandler::class],
                'request' => [
                    'class' => \yii\web\Request::class,
                    'scriptUrl' => '/index.php',
                    'hostInfo' => 'http://localhost',
                    'url' => '/index.php?r=deptor/index',
                ],
            ],
        ]);
        $this->controller = new DeptorController('deptor', $this->app);
        $this->app->controller = $this->controller;
    }

    protected function tearDown(): void
    {
        Yii::$app = $this->oldApp;
        foreach ($this->oldAliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    private function providers(array $params): array
    {
        $this->app->request->setQueryParams($params);
        $rows = [];
        for ($i = 1; $i <= 44; $i++) {
            $rows[] = [
                'hospcode' => sprintf('%05d', $i),
                'hosname' => $i === 1 ? '<script>alert(1)</script>' : 'Hospital ' . $i,
                'total_visit' => $i,
                'claim' => $i + 0.25,
                'alltotal' => $i + 0.5,
            ];
        }
        $method = new \ReflectionMethod(DeptorController::class, 'createReportProviders');
        return $method->invoke($this->controller, $params, ['op' => $rows, 'pp' => $rows, 'ip' => []]);
    }

    public function testPaginationSortingAndAllAreIndependent(): void
    {
        $providers = $this->providers(['op-page' => '2', 'pp-all' => '1', 'op-sort' => '-total_visit', 'date1' => '2568-10-01']);
        self::assertCount(10, $providers['op']->getModels());
        self::assertSame(34, array_values($providers['op']->getModels())[0]['total_visit']);
        self::assertCount(44, $providers['pp']->getModels());
        self::assertSame(1, array_values($providers['pp']->getModels())[0]['total_visit']);
        self::assertCount(0, $providers['ip']->getModels());

        $html = $this->controller->renderPartial('_table', ['type' => 'op', 'provider' => $providers['op']]);
        self::assertStringContainsString('แสดง 11–20 จาก 44 รายการ', $html);
        self::assertStringContainsString('op-all=1', $html);
        self::assertStringContainsString('pp-all=1', $html);
        self::assertStringContainsString('date1=2568-10-01', $html);
        self::assertStringContainsString('aria-sort="descending"', $html);
        self::assertStringContainsString('format=xlsx', $html);

        $all = $this->providers(['all' => '1', 'pp-all' => '0']);
        self::assertCount(44, $all['op']->getModels());
        self::assertCount(10, $all['pp']->getModels());
        $html = $this->controller->renderPartial('_table', ['type' => 'op', 'provider' => $all['op']]);
        self::assertStringContainsString('แสดงทั้งหมด 44 รายการ', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        $html = $this->controller->renderPartial('_table', ['type' => 'ip', 'provider' => $all['ip']]);
        self::assertStringContainsString('ไม่พบข้อมูลในช่วงวันที่เลือก', $html);
    }

    public function testExportCurrentPageAndProtectSpreadsheetCells(): void
    {
        $providers = $this->providers(['op-page' => '2']);
        $method = new \ReflectionMethod(DeptorController::class, 'exportTable');
        $csv = $method->invoke($this->controller, $providers['op'], 'op', 'csv')->content;
        self::assertStringContainsString('00011', $csv);
        self::assertStringNotContainsString('00021', $csv);

        $providers = $this->providers(['op-all' => '1']);
        $providers['op']->allModels[0]['hosname'] = '=1+1';
        $providers['op']->allModels[0]['claim'] = '-12.50';
        $csv = $method->invoke($this->controller, $providers['op'], 'op', 'csv')->content;
        self::assertStringContainsString("'=1+1", $csv);
        self::assertStringContainsString('00044', $csv);
        self::assertStringContainsString(',-12.50,', $csv);
        $text = $method->invoke($this->controller, $providers['op'], 'op', 'txt')->content;
        self::assertStringContainsString("'=1+1", $text);
        $xlsx = $method->invoke($this->controller, $providers['op'], 'op', 'xlsx')->content;
        $path = tempnam(sys_get_temp_dir(), 'deptor-test-');
        try {
            file_put_contents($path, $xlsx);
            $sheet = IOFactory::createReader('Xlsx')->load($path)->getActiveSheet();
            self::assertSame('00001', $sheet->getCell('B2')->getValue());
            self::assertSame('=1+1', $sheet->getCell('C2')->getValue());
            self::assertSame('s', $sheet->getCell('C2')->getDataType());
            self::assertSame(44.0, $sheet->getCell('D45')->getValue());
        } finally {
            unlink($path);
        }
    }
}
