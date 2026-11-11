<?php

namespace frontend\tests\unit\components;

use frontend\models\XlsfileuploadsSearch;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;

class XlsUploadFacilitySearchTest extends TestCase
{
    private $previousApp;
    private $aliases;
    private $app;

    protected function setUp(): void
    {
        $this->previousApp = Yii::$app;
        foreach (['@app', '@runtime', '@webroot'] as $alias) {
            $this->aliases[$alias] = Yii::getAlias($alias, false);
        }
        $this->app = new Application([
            'id' => 'upload-facility-search-test',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                'user' => ['identityClass' => UploadSearchIdentity::class, 'enableSession' => false],
                'errorHandler' => ['class' => UploadSearchErrorHandler::class],
            ],
        ]);
        $this->app->db->createCommand(
            'CREATE TABLE xlsfileuploads (xlsid INTEGER PRIMARY KEY, hoscode TEXT, exsfile TEXT, user TEXT, typefileupload TEXT, uploaddatetime TEXT)'
        )->execute();
        foreach ([
            [1, '11354', 'first.xlsx', '2026-11-01'],
            [2, '09119', 'second.xlsx', '2026-11-02'],
            [3, '11354', 'third.xlsx', '2026-11-03'],
            [4, '00064', 'admin.xlsx', '2026-11-04'],
            [5, '113540', 'similar.xlsx', '2026-11-05'],
        ] as [$id, $hoscode, $file, $date]) {
            $this->app->db->createCommand()->insert('xlsfileuploads', [
                'xlsid' => $id,
                'hoscode' => $hoscode,
                'exsfile' => $file,
                'user' => 'tester',
                'typefileupload' => 'ip',
                'uploaddatetime' => $date,
            ])->execute();
        }
    }

    protected function tearDown(): void
    {
        Yii::$app = $this->previousApp;
        foreach ($this->aliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testListsOnlyExactSignedInFacilityAndPreservesFilters(): void
    {
        $this->app->user->setIdentity(new UploadSearchIdentity('11354'));
        $provider = (new XlsfileuploadsSearch())->search([]);
        self::assertSame(2, $provider->getTotalCount());
        self::assertSame([3, 1], array_map(static fn($model) => (int)$model->xlsid, $provider->getModels()));
        self::assertSame(0, (new XlsfileuploadsSearch())->search([
            'XlsfileuploadsSearch' => ['hoscode' => '09119'],
        ])->getTotalCount());
        self::assertSame(1, (new XlsfileuploadsSearch())->search([
            'XlsfileuploadsSearch' => ['exsfile' => 'third'],
        ])->getTotalCount());
        self::assertSame(2, (new XlsfileuploadsSearch())->search([
            'XlsfileuploadsSearch' => ['xlsid' => 'invalid'],
        ])->getTotalCount());
    }

    public function testProvincialAccountAlsoSeesOnlyItsOwnCode(): void
    {
        $this->app->user->setIdentity(new UploadSearchIdentity('00064'));
        $models = (new XlsfileuploadsSearch())->search([])->getModels();
        self::assertCount(1, $models);
        self::assertSame('00064', $models[0]->hoscode);
    }

    public function testMissingFacilityIsRejected(): void
    {
        $this->app->user->setIdentity(new UploadSearchIdentity(' '));
        $this->expectException(ForbiddenHttpException::class);
        (new XlsfileuploadsSearch())->search([]);
    }

    public function testMissingIdentityIsRejected(): void
    {
        $this->expectException(ForbiddenHttpException::class);
        (new XlsfileuploadsSearch())->search([]);
    }
}

class UploadSearchIdentity implements IdentityInterface
{
    public $hoscode;

    public function __construct(string $hoscode)
    {
        $this->hoscode = $hoscode;
    }

    public static function findIdentity($id) { return null; }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return 1; }
    public function getAuthKey() { return null; }
    public function validateAuthKey($authKey) { return false; }
}

class UploadSearchErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}
