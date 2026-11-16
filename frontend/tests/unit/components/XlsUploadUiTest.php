<?php

namespace frontend\tests\unit\components;

use frontend\controllers\XlsfileuploadsController;
use frontend\models\Xlsfileuploads;
use frontend\models\XlsfileuploadsSearch;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Application;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Session;
use yii\web\UploadedFile;

class UploadUiErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}

class UploadUiController extends XlsfileuploadsController
{
    public $renderedModel;

    public function render($view, $params = [])
    {
        $this->renderedModel = $params['model'];
        return $view;
    }
}

class XlsUploadUiTest extends TestCase
{
    private $oldApp;
    private $oldAliases;
    private $oldFiles;
    private $app;
    private $controller;

    protected function setUp(): void
    {
        $this->oldApp = Yii::$app;
        $this->oldAliases = [];
        foreach (['@app', '@runtime', '@webroot', '@web'] as $alias) {
            $this->oldAliases[$alias] = Yii::getAlias($alias, false);
        }
        $this->oldFiles = $_FILES;
        $_FILES = [];
        UploadedFile::reset();
        $this->app = new Application([
            'id' => 'upload-ui-test',
            'basePath' => dirname(__DIR__, 3),
            'vendorPath' => dirname(__DIR__, 4) . '/vendor',
            'components' => [
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                'request' => [
                    'class' => \yii\web\Request::class,
                    'scriptUrl' => '/index.php',
                    'hostInfo' => 'http://localhost',
                    'url' => '/index.php?r=xlsfileuploads/create',
                    'enableCsrfValidation' => false,
                ],
                'errorHandler' => ['class' => UploadUiErrorHandler::class],
            ],
        ]);
        Yii::setAlias('@web', '');
        Yii::setAlias('@webroot', dirname(__DIR__, 3) . '/web');
        $this->app->assetManager->bundles = [
            'frontend\assets\CfpsThemeAsset' => new \yii\web\AssetBundle(),
            'yii\widgets\ActiveFormAsset' => new \yii\web\AssetBundle(),
            'yii\grid\GridViewAsset' => new \yii\web\AssetBundle(),
            'yii\web\YiiAsset' => new \yii\web\AssetBundle(),
            'yii\web\JqueryAsset' => new \yii\web\AssetBundle(),
        ];
        $session = $this->createStub(Session::class);
        $session->method('get')->willReturnCallback(static function ($key) {
            return ['user' => 'tester', 'hoscode' => '00064'][$key] ?? null;
        });
        $this->app->set('session', $session);
        $this->app->set('facilityAccess', new RetiredUploadFacilityAccess());
        $this->app->db->createCommand(
            'CREATE TABLE xlsfileuploads (xlsid INTEGER PRIMARY KEY, exsfile TEXT, hoscode TEXT, user TEXT, typefileupload TEXT, uploaddatetime TEXT, startdatetime TEXT, enddatetime TEXT, importstatus INTEGER DEFAULT 0)'
        )->execute();
        $this->controller = new XlsfileuploadsController('xlsfileuploads', $this->app);
        $this->app->controller = $this->controller;
    }

    protected function tearDown(): void
    {
        $_FILES = $this->oldFiles;
        UploadedFile::reset();
        Yii::$app = $this->oldApp;
        foreach ($this->oldAliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testCreatePageAndIndexShowWorkflow(): void
    {
        $this->app->request->setQueryParams(['typefile' => 'op pp']);
        $html = $this->controller->renderPartial('create', ['model' => new Xlsfileuploads()]);
        self::assertStringContainsString('ขั้นตอนที่ 1 จาก 2', $html);
        self::assertStringContainsString('option value="op pp" selected', $html);
        self::assertStringContainsString('accept=".xls,.xlsx"', $html);
        self::assertStringContainsString('hospcode | dateserv', $html);

        $model = new Xlsfileuploads();
        $model->xlsid = 12;
        $model->exsfile = 'file.xlsx';
        $model->typefileupload = 'op pp';
        $model->importstatus = 0;
        $provider = new ArrayDataProvider(['allModels' => [$model], 'pagination' => false]);
        $html = $this->controller->renderPartial('index', [
            'dataProvider' => $provider,
            'searchModel' => new XlsfileuploadsSearch(),
        ]);
        self::assertStringContainsString('รอนำเข้า', $html);
        self::assertStringContainsString('แสดงเฉพาะไฟล์ของรหัสสถานบริการที่เข้าสู่ระบบ', $html);
        self::assertStringContainsString('opdatainfo%2Fimport', $html);
        self::assertStringContainsString('ดาวน์โหลดไฟล์', $html);
        self::assertStringContainsString('เตรียมนำเข้าอีกครั้ง', $html);
    }

    public function testMissingFileIsReportedWithoutCreatingRecord(): void
    {
        $this->app->request->setBodyParams([
            'Xlsfileuploads' => ['user' => 'tester', 'hoscode' => '00064', 'typefileupload' => 'ip'],
        ]);
        $controller = new UploadUiController('xlsfileuploads', $this->app);
        self::assertSame('create', $controller->actionCreate());
        self::assertSame('กรุณาเลือกไฟล์ Excel', $controller->renderedModel->getFirstError('exsfile'));
        self::assertSame(0, (int)$this->app->db->createCommand('SELECT COUNT(*) FROM xlsfileuploads')->queryScalar());
    }

    public function testWrongFileExtensionIsReported(): void
    {
        $this->app->request->setBodyParams([
            'Xlsfileuploads' => ['user' => 'tester', 'hoscode' => '00064', 'typefileupload' => 'ip'],
        ]);
        $_FILES = ['Xlsfileuploads' => [
            'name' => ['exsfile' => 'wrong.txt'],
            'type' => ['exsfile' => 'text/plain'],
            'tmp_name' => ['exsfile' => __FILE__],
            'error' => ['exsfile' => UPLOAD_ERR_OK],
            'size' => ['exsfile' => filesize(__FILE__)],
        ]];
        UploadedFile::reset();
        $controller = new UploadUiController('xlsfileuploads', $this->app);
        self::assertSame('create', $controller->actionCreate());
        self::assertSame('กรุณาเลือกไฟล์ Excel นามสกุล .xls หรือ .xlsx', $controller->renderedModel->getFirstError('exsfile'));
        self::assertSame(0, (int)$this->app->db->createCommand('SELECT COUNT(*) FROM xlsfileuploads')->queryScalar());
    }

    public function testUpdatePreservesExistingFile(): void
    {
        $this->app->db->createCommand()->insert('xlsfileuploads', [
            'xlsid' => 1,
            'exsfile' => 'original.xlsx',
            'user' => 'tester',
            'hoscode' => '00064',
            'typefileupload' => 'ip',
        ])->execute();
        $this->app->request->setBodyParams([
            'Xlsfileuploads' => ['user' => 'tester', 'hoscode' => '00064', 'typefileupload' => 'op pp'],
        ]);
        self::assertSame(302, $this->controller->actionUpdate(1)->statusCode);
        $row = $this->app->db->createCommand('SELECT exsfile, typefileupload FROM xlsfileuploads WHERE xlsid = 1')->queryOne();
        self::assertSame('original.xlsx', $row['exsfile']);
        self::assertSame('op pp', $row['typefileupload']);
    }

    public function testRetiredUploadCanBeDeletedButNotReimported(): void
    {
        $access = new RetiredUploadFacilityAccess();
        $this->app->set('facilityAccess', $access);
        $this->app->db->createCommand()->insert('xlsfileuploads', [
            'xlsid' => 2,
            'exsfile' => 'retired-test.xlsx',
            'user' => 'tester',
            'hoscode' => '00064',
            'typefileupload' => 'fwfhealth',
            'importstatus' => 0,
        ])->execute();

        $model = Xlsfileuploads::findOne(2);
        $html = $this->controller->renderPartial('index', [
            'dataProvider' => new ArrayDataProvider(['allModels' => [$model], 'pagination' => false]),
            'searchModel' => new XlsfileuploadsSearch(),
        ]);
        self::assertStringNotContainsString('เตรียมนำเข้าอีกครั้ง', $html);
        self::assertStringContainsString('ยกเลิกการใช้งาน', $html);
        self::assertStringContainsString('ลบ', $html);
        try {
            $this->controller->actionReimport(2);
            self::fail('Retired uploads must not be reimported.');
        } catch (BadRequestHttpException $exception) {
            self::assertSame(0, (int)Xlsfileuploads::findOne(2)->importstatus);
        }

        $access->allowed = false;
        try {
            $this->controller->actionDelete(2);
            self::fail('Deleting a retired upload requires permission.');
        } catch (ForbiddenHttpException $exception) {
            self::assertNotNull(Xlsfileuploads::findOne(2));
        }
        $access->allowed = true;
        self::assertSame(302, $this->controller->actionDelete(2)->statusCode);
        self::assertNull(Xlsfileuploads::findOne(2));
    }

    public function testProvincialAdminCannotCreateOrUpdateRetiredUploadType(): void
    {
        $this->app->set('facilityAccess', new RetiredUploadFacilityAccess());
        $controller = new UploadUiController('xlsfileuploads', $this->app);
        $this->app->request->setBodyParams([
            'Xlsfileuploads' => ['user' => 'tester', 'hoscode' => '00064', 'typefileupload' => 'fwfhealth'],
        ]);
        self::assertSame('create', $controller->actionCreate());
        self::assertNotEmpty($controller->renderedModel->getErrors('typefileupload'));

        $this->app->db->createCommand()->insert('xlsfileuploads', [
            'xlsid' => 3, 'exsfile' => 'existing.xlsx', 'user' => 'tester',
            'hoscode' => '00064', 'typefileupload' => 'ip',
        ])->execute();
        self::assertSame('update', $controller->actionUpdate(3));
        self::assertNotEmpty($controller->renderedModel->getErrors('typefileupload'));
        self::assertSame('ip', Xlsfileuploads::findOne(3)->typefileupload);
    }
}

class RetiredUploadFacilityAccess extends \yii\base\Component
{
    public $allowed = true;

    public function isProvincialAdmin(): bool { return true; }
    public function canAccessFacility($code, $forWrite = false): bool { return true; }
    public function provincialFacilityCodes(): array { return ['00064']; }
    public function requirePermission($permission): void
    {
        if (!$this->allowed || $permission !== 'manageFwfData') {
            throw new ForbiddenHttpException();
        }
    }
}
