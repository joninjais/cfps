<?php

namespace frontend\tests\unit\models;

use frontend\components\FacilityAccess;
use frontend\controllers\BaseController;
use frontend\controllers\SiteController;
use frontend\controllers\UserController;
use frontend\models\User;
use frontend\models\UserSearch;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Action;
use yii\rbac\DbManager;
use yii\web\Application;
use yii\web\NotFoundHttpException;
use yii\web\Session;

class UserSoftDeleteTest extends TestCase
{
    private $previousApp;
    private $aliases;
    private $app;
    private $controller;

    protected function setUp(): void
    {
        $this->previousApp = Yii::$app;
        foreach (['@app', '@runtime', '@webroot', '@web'] as $alias) {
            $this->aliases[$alias] = Yii::getAlias($alias, false);
        }
        $this->app = new Application([
            'id' => 'user-soft-delete-test',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                'request' => [
                    'class' => \yii\web\Request::class,
                    'scriptUrl' => '/index.php',
                    'hostInfo' => 'http://localhost',
                ],
                'response' => ['class' => SoftDeleteTestResponse::class],
                'errorHandler' => ['class' => SoftDeleteTestErrorHandler::class],
                'cache' => ['class' => \yii\caching\ArrayCache::class],
                'user' => ['identityClass' => User::class, 'enableSession' => false],
                'authManager' => ['class' => DbManager::class],
                'facilityAccess' => ['class' => SoftDeleteTestFacilityAccess::class],
            ],
        ]);
        $this->app->set('session', $this->createStub(Session::class));
        $db = $this->app->db;
        $db->createCommand('CREATE TABLE user (userid INTEGER PRIMARY KEY, user TEXT, pass TEXT, fname TEXT, lname TEXT, orgname TEXT, hoscode TEXT, phonenumber TEXT, email TEXT, approval_status TEXT, is_deleted INTEGER NOT NULL DEFAULT 0)')->execute();
        $db->createCommand('CREATE TABLE auth_assignment (item_name TEXT, user_id TEXT, created_at INTEGER, PRIMARY KEY (item_name, user_id))')->execute();
        foreach ([
            [1, 'admin', 'approved', '00064'],
            [2, 'active', 'approved', '00001'],
            [3, 'pending', 'pending', '00001'],
            [4, 'outside', 'approved', '00002'],
        ] as [$id, $username, $approval, $facility]) {
            $db->createCommand()->insert('user', [
                'userid' => $id,
                'user' => $username,
                'pass' => 'password',
                'hoscode' => $facility,
                'approval_status' => $approval,
            ])->execute();
        }
        $db->createCommand()->insert('auth_assignment', ['item_name' => 'provincialAdmin', 'user_id' => '1'])->execute();
        $db->createCommand()->insert('auth_assignment', ['item_name' => 'hospitalStaff', 'user_id' => '2'])->execute();
        $this->app->user->setIdentity(User::findIdentity(1));
        $this->controller = new UserController('user', $this->app);
        $this->app->controller = $this->controller;
    }

    protected function tearDown(): void
    {
        Yii::$app = $this->previousApp;
        foreach ($this->aliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testDeleteRetainsRecordRevokesRolesAndHidesAccount(): void
    {
        self::assertNotNull(User::findByUsername('active'));
        self::assertNotNull(User::findIdentity(2));
        self::assertNotNull(User::findByUsername('pending'));
        self::assertNull(User::findIdentity(3));

        $this->controller->actionDelete(2);

        $deleted = User::findOne(2);
        self::assertNotNull($deleted);
        self::assertSame(User::DELETED, (int)$deleted->is_deleted);
        self::assertSame('approved', $deleted->approval_status);
        self::assertNull(User::findByUsername('active'));
        self::assertNull(User::findIdentity(2));
        self::assertNull($this->app->authManager->getAssignment('hospitalStaff', '2'));
        $ids = array_map(static fn($user) => (int)$user->userid, (new UserSearch())->search([])->getModels());
        self::assertSame([3, 1], $ids);

        $this->expectException(NotFoundHttpException::class);
        $this->controller->actionDelete(2);
    }

    public function testNewestUsersAppearFirstAcrossPagesAndFilters(): void
    {
        for ($id = 5; $id <= 27; $id++) {
            $this->app->db->createCommand()->insert('user', [
                'userid' => $id,
                'user' => 'new-user-' . $id,
                'hoscode' => '00001',
                'approval_status' => User::APPROVAL_PENDING,
            ])->execute();
        }
        $this->app->db->createCommand()->update('user', ['is_deleted' => 1], ['userid' => 27])->execute();
        $provider = (new UserSearch())->search([]);
        $ids = array_map(static fn($user) => (int)$user->userid, $provider->getModels());
        self::assertCount(20, $ids);
        self::assertSame(26, $ids[0]);
        self::assertNotContains(27, $ids);
        self::assertNotContains(4, $ids);
        $provider->pagination->setPage(1);
        $provider->refresh();
        $secondPage = array_map(static fn($user) => (int)$user->userid, $provider->getModels());
        self::assertSame([6, 5, 3, 2, 1], $secondPage);

        $filtered = (new UserSearch())->search(['UserSearch' => ['user' => 'new-user-2']]);
        self::assertSame([26, 25, 24, 23, 22, 21, 20], array_map(
            static fn($user) => (int)$user->userid,
            $filtered->getModels()
        ));
    }

    public function testExplicitUserSortOverridesDefaultOrder(): void
    {
        $this->app->request->setQueryParams(['sort' => 'user']);
        $provider = (new UserSearch())->search([]);
        self::assertSame(['active', 'admin', 'pending'], array_map(
            static fn($user) => $user->user,
            $provider->getModels()
        ));
    }

    public function testPendingAccountCannotBeApprovedAfterDeletion(): void
    {
        $this->controller->actionDelete(3);
        self::assertNotNull(User::findOne(3));
        self::assertNull(User::findByUsername('pending'));
        $this->expectException(NotFoundHttpException::class);
        $this->controller->actionApprove(3);
    }

    public function testCannotDeleteOwnAccountOrOutsideFacility(): void
    {
        $this->controller->actionDelete(1);
        self::assertSame(User::NOT_DELETED, (int)User::findOne(1)->is_deleted);
        self::assertNotNull($this->app->authManager->getAssignment('provincialAdmin', '1'));

        $this->expectException(NotFoundHttpException::class);
        $this->controller->actionDelete(4);
    }

    public function testDeleteRollsBackRoleRevocationOnSaveFailure(): void
    {
        $this->app->db->open();
        $this->app->db->pdo->exec("CREATE TRIGGER prevent_delete BEFORE UPDATE OF is_deleted ON user BEGIN SELECT RAISE(FAIL, 'test failure'); END");
        $this->controller->actionDelete(2);
        self::assertSame(User::NOT_DELETED, (int)User::findOne(2)->is_deleted);
        self::assertNotNull($this->app->authManager->getAssignment('hospitalStaff', '2'));
    }

    public function testExistingLegacySessionIsRejectedAfterDeletion(): void
    {
        $this->controller->actionDelete(2);
        $session = $this->createMock(Session::class);
        $session->method('offsetExists')->willReturn(true);
        $session->method('offsetGet')->willReturn('active');
        $session->method('get')->willReturnCallback(static function ($key) {
            return ['userid' => 2, 'user' => 'active', 'login_time' => time()][$key] ?? null;
        });
        $session->expects(self::once())->method('destroy');
        $session->expects(self::once())->method('setFlash')->with('error', self::anything());
        $this->app->set('session', $session);
        $controller = new BaseController('test', $this->app);
        self::assertFalse($controller->beforeAction(new Action('index', $controller)));
        self::assertSame(302, $this->app->response->statusCode);
    }

    public function testLoginPageDoesNotRedirectDeletedSessionHome(): void
    {
        $this->controller->actionDelete(2);
        $session = $this->createMock(Session::class);
        $session->method('offsetExists')->willReturn(true);
        $session->method('offsetGet')->willReturn('active');
        $session->method('get')->willReturn(2);
        $session->expects(self::once())->method('destroy');
        $this->app->set('session', $session);
        $controller = new SoftDeleteTestSiteController('site', $this->app);
        self::assertSame('login', $controller->actionLogin());
    }

    public function testDeletedUserCannotLogInWithCorrectPassword(): void
    {
        $this->controller->actionDelete(2);
        $this->app->db->createCommand('CREATE TABLE login_history (id INTEGER PRIMARY KEY, user TEXT, hoscode TEXT, ip_address TEXT, user_agent TEXT, device_type TEXT, login_at INTEGER, status TEXT, action TEXT, session_id TEXT, fail_reason TEXT)')->execute();
        $session = $this->createMock(Session::class);
        $session->method('offsetExists')->willReturn(false);
        $session->expects(self::once())->method('setFlash')->with('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        $this->app->set('session', $session);
        $this->app->request->setBodyParams(['LoginForm' => ['username' => 'active', 'password' => 'password']]);
        $controller = new SoftDeleteTestSiteController('site', $this->app);
        self::assertSame('login', $controller->actionLogin());
        $history = $this->app->db->createCommand('SELECT status, fail_reason FROM login_history')->queryOne();
        self::assertSame(['status' => 'failed', 'fail_reason' => 'invalid_credentials'], $history);
        self::assertSame('password', User::findOne(2)->pass);
    }

    public function testMigrationDefaultsToVisibleAndRefusesUnsafeRollback(): void
    {
        require_once dirname(__DIR__, 4) . '/console/migrations/m261111_000004_add_user_soft_delete.php';
        $db = $this->app->db;
        $db->createCommand('ALTER TABLE user DROP COLUMN is_deleted')->execute();
        $migration = new class(['db' => $db, 'compact' => true]) extends \m261111_000004_add_user_soft_delete {
            public function dropColumn($table, $column)
            {
                $this->db->createCommand('ALTER TABLE user DROP COLUMN is_deleted')->execute();
            }
        };
        ob_start();
        try {
            $migration->safeUp();
            self::assertSame(0, (int)$db->createCommand('SELECT is_deleted FROM user WHERE userid = 2')->queryScalar());
            $db->createCommand()->update('user', ['is_deleted' => 1], ['userid' => 2])->execute();
            self::assertFalse($migration->safeDown());
            self::assertSame(1, (int)$db->createCommand('SELECT is_deleted FROM user WHERE userid = 2')->queryScalar());
            $db->createCommand()->update('user', ['is_deleted' => 0], ['userid' => 2])->execute();
            $migration->safeDown();
            self::assertNull($db->schema->getTableSchema('user', true)->getColumn('is_deleted'));
        } finally {
            ob_end_clean();
        }
    }
}

class SoftDeleteTestFacilityAccess extends FacilityAccess
{
    public function userManagementFacilityCodes(): array
    {
        return ['00064', '00001'];
    }
}

class SoftDeleteTestResponse extends \yii\web\Response
{
    public function send()
    {
    }
}

class SoftDeleteTestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}

class SoftDeleteTestSiteController extends SiteController
{
    public function render($view, $params = [])
    {
        return $view;
    }
}
