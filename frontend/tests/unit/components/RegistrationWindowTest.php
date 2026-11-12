<?php

namespace frontend\tests\unit\components;

use frontend\components\RegistrationWindow;
use frontend\controllers\SiteController;
use frontend\controllers\UserController;
use frontend\models\RegistrationWindowForm;
use frontend\models\SignupForm;
use frontend\models\User;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Action;
use yii\web\Application;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Session;

class RegistrationWindowTest extends TestCase
{
    private $previousApp;
    private $aliases;
    private $app;
    private $requestMethod;

    protected function setUp(): void
    {
        $this->previousApp = Yii::$app;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        foreach (['@app', '@runtime', '@webroot', '@web'] as $alias) {
            $this->aliases[$alias] = Yii::getAlias($alias, false);
        }
        $this->app = new Application([
            'id' => 'registration-test',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                'request' => ['class' => \yii\web\Request::class, 'scriptUrl' => '/index.php', 'hostInfo' => 'http://localhost', 'cookieValidationKey' => 'test-only-key'],
                'assetManager' => [
                    'basePath' => dirname(__DIR__, 3) . '/web/assets',
                    'baseUrl' => '/assets',
                    'bundles' => [
                        \yii\web\JqueryAsset::class => false,
                        \yii\bootstrap\BootstrapAsset::class => false,
                    ],
                ],
                'errorHandler' => ['class' => RegistrationTestErrorHandler::class],
                'user' => ['identityClass' => User::class, 'enableSession' => false],
                'cache' => ['class' => \yii\caching\ArrayCache::class],
                'facilityAccess' => ['class' => RegistrationTestFacilityAccess::class],
            ],
        ]);
        $this->app->set('session', $this->createStub(Session::class));
        $db = $this->app->db;
        $db->createCommand('CREATE TABLE registration_window (id INTEGER PRIMARY KEY, token TEXT, expires_at INTEGER)')->execute();
        $db->createCommand()->insert('registration_window', ['id' => 1])->execute();
        $db->createCommand('CREATE TABLE user (userid INTEGER PRIMARY KEY, user TEXT, pass TEXT, fname TEXT, lname TEXT, orgname TEXT, hoscode TEXT, hmain TEXT, phonenumber TEXT, email TEXT, approval_status TEXT, requested_role TEXT, must_change_password INTEGER, is_deleted INTEGER DEFAULT 0)')->execute();
        $db->createCommand('CREATE TABLE chospital2 (hospcode TEXT, hosname TEXT)')->execute();
        $db->createCommand()->insert('chospital2', ['hospcode' => '12345', 'hosname' => 'Facility'])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app = $this->previousApp;
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        foreach ($this->aliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testDurationValidation(): void
    {
        foreach ([[30, 'minutes', 30], [1, 'hours', 60], [2, 'hours', 120], [525600, 'minutes', 525600], [8760, 'hours', 525600]] as [$duration, $unit, $minutes]) {
            $form = new RegistrationWindowForm(['duration' => $duration, 'unit' => $unit]);
            self::assertTrue($form->validate());
            self::assertSame($minutes, $form->minutes());
        }
        foreach ([[0, 'minutes'], [-1, 'hours'], [1.5, 'minutes'], [8761, 'hours'], [525601, 'minutes'], [1, 'days']] as [$duration, $unit]) {
            self::assertFalse((new RegistrationWindowForm(['duration' => $duration, 'unit' => $unit]))->validate());
        }
    }

    public function testLegacyHmainDefaultsForNewUsersWithoutOverwritingExplicitValue(): void
    {
        $user = new User(['hoscode' => '12345']);
        self::assertTrue($user->beforeValidate());
        self::assertSame('12345', $user->hmain);
        $user->hmain = '54321';
        self::assertTrue($user->beforeValidate());
        self::assertSame('54321', $user->hmain);
    }

    public function testStatusRotationCloseAndExpiry(): void
    {
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        self::assertSame(['open' => false], $controller->actionRegistrationStatus());
        RegistrationWindow::open(30);
        $first = RegistrationWindow::current();
        $status = $controller->actionRegistrationStatus();
        self::assertTrue($status['open']);
        self::assertStringContainsString($first['token'], $status['url']);
        self::assertGreaterThan(0, $status['remaining']);
        self::assertLessThanOrEqual(1800, $status['remaining']);
        RegistrationWindow::open(60);
        $second = RegistrationWindow::current();
        self::assertNotSame($first['token'], $second['token']);
        self::assertFalse(RegistrationWindow::isOpen($second, $first['token']));
        self::assertTrue(RegistrationWindow::isOpen($second, $second['token']));
        RegistrationWindow::close();
        self::assertFalse(RegistrationWindow::isOpen(RegistrationWindow::current(), $second['token']));
        RegistrationWindow::open(1);
        $this->app->db->createCommand()->update('registration_window', ['expires_at' => time()], ['id' => 1])->execute();
        self::assertSame(['open' => false], $controller->actionRegistrationStatus());
    }

    public function testManagementStatusRendersOpenAndClosedStates(): void
    {
        $view = $this->app->view;
        $path = Yii::getAlias('@app/views/user/_registration-status.php');
        $closed = $view->renderFile($path, ['window' => RegistrationWindow::current()]);
        self::assertStringContainsString('class="registration-status-open" hidden', $closed);
        self::assertStringContainsString('class="registration-status-closed" >ขณะนี้ปิดรับลงทะเบียน', $closed);
        RegistrationWindow::open(30);
        $open = $view->renderFile($path, ['window' => RegistrationWindow::current()]);
        self::assertStringContainsString('class="registration-status-open" >', $open);
        self::assertStringContainsString('class="registration-status-closed" hidden', $open);
        self::assertStringContainsString('registration-status-fallback', $open);
        self::assertSame(1, preg_match('/data-remaining="(\d+)"/', $open, $matches));
        self::assertGreaterThan(0, (int)$matches[1]);
        self::assertLessThanOrEqual(1800, (int)$matches[1]);
        self::assertStringContainsString('site%2Fregistration-status', $open);
    }

    public function testSignupCreatesPendingUserOnlyDuringCurrentWindow(): void
    {
        RegistrationWindow::open(30);
        $token = RegistrationWindow::current()['token'];
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        self::assertSame('signup', $controller->actionSignup($token));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->app->request->setBodyParams(['SignupForm' => [
            'username' => 'newuser', 'email' => 'user@example.test', 'password' => 'Password1', 'passwordConfirm' => 'Password1', 'phonenumber' => '0812345678',
            'fname' => 'First', 'lname' => 'Last', 'hoscode' => '12345',
        ]]);
        $controller->actionSignup($token);
        $user = User::findOne(['user' => 'newuser']);
        self::assertNotNull($user);
        self::assertSame(User::APPROVAL_PENDING, $user->approval_status);
        self::assertSame('hospitalStaff', $user->requested_role);
        self::assertSame('0812345678', $user->phonenumber);
        self::assertSame('12345', $user->hmain);
        self::assertTrue($user->validatePassword('Password1'));
        RegistrationWindow::close();
        $this->expectException(NotFoundHttpException::class);
        $controller->actionSignup($token);
    }

    public function testSignupRequiresPhoneAndMatchingPasswordConfirmation(): void
    {
        $valid = [
            'username' => 'newuser', 'email' => 'user@example.test', 'password' => 'Password1',
            'passwordConfirm' => 'Password1', 'phonenumber' => '0812345678',
            'fname' => 'First', 'lname' => 'Last', 'hoscode' => '12345',
        ];
        foreach ([
            [['phonenumber' => ''], 'phonenumber'],
            [['phonenumber' => '   '], 'phonenumber'],
            [['phonenumber' => '01234567890'], 'phonenumber'],
            [['passwordConfirm' => ''], 'passwordConfirm'],
            [['passwordConfirm' => 'Different1'], 'passwordConfirm'],
        ] as [$changes, $field]) {
            $model = new SignupForm(array_merge($valid, $changes));
            self::assertNull($model->signup());
            self::assertTrue($model->hasErrors($field));
            self::assertSame(0, (int)User::find()->count());
        }
        $model = new SignupForm(array_merge($valid, ['phonenumber' => ' 0812345678 ']));
        $user = $model->signup();
        self::assertNotNull($user);
        self::assertSame('0812345678', $user->phonenumber);
        self::assertSame(User::APPROVAL_PENDING, $user->approval_status);
    }

    public function testSignupFormIncludesPhoneAndEmptyPasswordFieldsOnValidationError(): void
    {
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        $this->app->request->setUrl('/site/signup?token=test-token');
        $model = new SignupForm(['password' => 'Password1', 'passwordConfirm' => 'Different1']);
        $model->validate();
        $html = $this->app->view->renderFile(Yii::getAlias('@app/views/site/signup.php'), ['model' => $model]);
        self::assertStringContainsString('name="SignupForm[phonenumber]"', $html);
        self::assertStringContainsString('name="SignupForm[passwordConfirm]"', $html);
        self::assertStringNotContainsString('Password1', $html);
        self::assertStringNotContainsString('Different1', $html);
        self::assertStringContainsString('รหัสผ่านและการยืนยันต้องตรงกัน', $html);
    }

    public function testRegisteredUserCannotLogInUntilApproved(): void
    {
        $db = $this->app->db;
        $db->createCommand('ALTER TABLE user ADD COLUMN approved_by INTEGER')->execute();
        $db->createCommand('ALTER TABLE user ADD COLUMN approved_at INTEGER')->execute();
        $db->createCommand('CREATE TABLE login_history (id INTEGER PRIMARY KEY, user TEXT, hoscode TEXT, ip_address TEXT, user_agent TEXT, device_type TEXT, login_at INTEGER, status TEXT, action TEXT, session_id TEXT, fail_reason TEXT)')->execute();
        $db->createCommand('CREATE TABLE auth_item (name TEXT PRIMARY KEY, type INTEGER, description TEXT, rule_name TEXT, data TEXT, created_at INTEGER, updated_at INTEGER)')->execute();
        $db->createCommand('CREATE TABLE auth_assignment (item_name TEXT, user_id TEXT, created_at INTEGER, PRIMARY KEY (item_name, user_id))')->execute();
        $this->app->set('authManager', ['class' => \yii\rbac\DbManager::class]);
        $this->app->authManager->add($this->app->authManager->createRole('hospitalStaff'));
        $flashes = [];
        $session = $this->createStub(Session::class);
        $session->method('offsetExists')->willReturn(false);
        $session->method('setFlash')->willReturnCallback(static function ($key, $message) use (&$flashes) {
            $flashes[$key] = $message;
        });
        $this->app->set('session', $session);

        RegistrationWindow::open(30);
        $token = RegistrationWindow::current()['token'];
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->app->request->setBodyParams(['SignupForm' => [
            'username' => 'newuser', 'email' => 'user@example.test', 'password' => 'Password1', 'passwordConfirm' => 'Password1', 'phonenumber' => '0812345678',
            'fname' => 'First', 'lname' => 'Last', 'hoscode' => '12345',
        ]]);
        $controller->actionSignup($token);
        $user = User::findOne(['user' => 'newuser']);
        self::assertNotNull($user);
        self::assertSame(User::APPROVAL_PENDING, $user->approval_status);
        self::assertNull(User::findIdentity($user->userid));

        $this->app->request->setBodyParams(['LoginForm' => ['username' => 'newuser', 'password' => 'Password1']]);
        self::assertSame('login', $controller->actionLogin());
        self::assertTrue($this->app->user->isGuest);
        self::assertSame('บัญชีของท่านอยู่ระหว่างรออนุมัติจาก สสจ.', $flashes['error']);
        self::assertSame('approval_pending', $db->createCommand('SELECT fail_reason FROM login_history')->queryScalar());

        $user->approval_status = User::APPROVAL_REJECTED;
        $user->save(false, ['approval_status']);
        self::assertNull(User::findIdentity($user->userid));
        self::assertSame('login', $controller->actionLogin());
        self::assertTrue($this->app->user->isGuest);
        self::assertSame('บัญชีนี้ยังไม่ได้รับอนุมัติ กรุณาติดต่อผู้ดูแลระบบ', $flashes['error']);
        $user->approval_status = User::APPROVAL_PENDING;
        $user->save(false, ['approval_status']);

        $db->createCommand()->insert('user', [
            'userid' => 99, 'user' => 'admin', 'hoscode' => '12345',
            'approval_status' => User::APPROVAL_APPROVED, 'is_deleted' => 0,
        ])->execute();
        $this->app->user->setIdentity(User::findIdentity(99));
        $manager = new UserController('user', $this->app);
        self::assertSame(['POST'], $manager->behaviors()['verbs']['actions']['approve']);
        $this->app->controller = $manager;
        $this->app->request->setBodyParams([]);
        $manager->actionApprove($user->userid);
        $user->refresh();
        self::assertSame(User::APPROVAL_APPROVED, $user->approval_status);
        self::assertSame(99, (int)$user->approved_by);
        self::assertNotNull($this->app->authManager->getAssignment('hospitalStaff', (string)$user->userid));

        $this->app->user->setIdentity(null);
        $this->app->controller = $controller;
        $this->app->request->setBodyParams(['LoginForm' => ['username' => 'newuser', 'password' => 'Password1']]);
        $response = $controller->actionLogin();
        self::assertSame(302, $response->statusCode);
        self::assertSame((string)$user->userid, (string)$this->app->user->id);
        self::assertSame('success', $db->createCommand('SELECT status FROM login_history ORDER BY id DESC')->queryScalar());
    }

    public function testOldLinkIsRejectedAfterReopening(): void
    {
        RegistrationWindow::open(30);
        $old = RegistrationWindow::current()['token'];
        RegistrationWindow::open(30);
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        $this->expectException(NotFoundHttpException::class);
        $controller->actionSignup($old);
    }

    public function testExpiredLinkCannotSubmit(): void
    {
        RegistrationWindow::open(1);
        $token = RegistrationWindow::current()['token'];
        $this->app->db->createCommand()->update('registration_window', ['expires_at' => time()], ['id' => 1])->execute();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        $this->expectException(NotFoundHttpException::class);
        $controller->actionSignup($token);
    }

    public function testSignupRollsBackIfWindowExpiresDuringSubmission(): void
    {
        RegistrationWindow::open(1);
        $token = RegistrationWindow::current()['token'];
        $this->app->db->createCommand()->update('registration_window', ['expires_at' => time() + 1], ['id' => 1])->execute();
        $this->app->db->pdo->sqliteCreateFunction('registration_test_delay', static function () {
            sleep(2);
            return 0;
        }, 0);
        $this->app->db->pdo->exec('CREATE TRIGGER delay_signup AFTER INSERT ON user BEGIN SELECT registration_test_delay(); END');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->app->request->setBodyParams(['SignupForm' => [
            'username' => 'lateuser', 'email' => 'late@example.test', 'password' => 'Password1', 'passwordConfirm' => 'Password1', 'phonenumber' => '0812345678',
            'fname' => 'First', 'lname' => 'Last', 'hoscode' => '12345',
        ]]);
        $controller = new RegistrationTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        try {
            $controller->actionSignup($token);
            self::fail('Expired submission was accepted');
        } catch (NotFoundHttpException $exception) {
            self::assertNull(User::findOne(['user' => 'lateuser']));
        }
    }

    public function testAdminCloseRequiresPost(): void
    {
        $controller = new UserController('user', $this->app);
        self::assertSame(['POST'], $controller->behaviors()['verbs']['actions']['registration-close']);
    }

    public function testRegistrationManagementRequiresManageUsersPermission(): void
    {
        $this->app->db->createCommand()->insert('user', [
            'userid' => 1, 'user' => 'staff', 'hoscode' => '12345',
            'approval_status' => 'approved', 'is_deleted' => 0,
        ])->execute();
        $this->app->user->setIdentity(User::findIdentity(1));
        $session = $this->createStub(Session::class);
        $session->method('offsetExists')->willReturn(true);
        $session->method('offsetGet')->willReturn('staff');
        $session->method('get')->willReturnCallback(static function ($key) {
            return ['userid' => 1, 'user' => 'staff', 'login_time' => time()][$key] ?? null;
        });
        $this->app->set('session', $session);
        $controller = new UserController('user', $this->app);
        $this->app->controller = $controller;
        $this->expectException(ForbiddenHttpException::class);
        $controller->beforeAction(new Action('registration', $controller));
    }

    public function testMigrationCreatesClosedWindowAndRollsBack(): void
    {
        require_once dirname(__DIR__, 4) . '/console/migrations/m261112_000001_create_registration_window.php';
        $this->app->db->createCommand()->dropTable('registration_window')->execute();
        $migration = new \m261112_000001_create_registration_window(['db' => $this->app->db, 'compact' => true]);
        ob_start();
        try {
            $migration->safeUp();
            self::assertFalse(RegistrationWindow::isOpen(RegistrationWindow::current()));
            $migration->safeDown();
            self::assertNull($this->app->db->schema->getTableSchema('registration_window', true));
        } finally {
            ob_end_clean();
        }
    }
}

class RegistrationTestSiteController extends SiteController
{
    public function render($view, $params = [])
    {
        return $view;
    }
}

class RegistrationTestFacilityAccess extends \yii\base\Component
{
    public function requireAnyRole(array $roles): void
    {
    }

    public function requireFacilityRoleMatches(): void
    {
    }

    public function requirePermission(string $permission): void
    {
        if ($permission === 'manageUsers') {
            throw new ForbiddenHttpException('Forbidden');
        }
    }

    public function expectedStaffRole(string $code): ?string
    {
        return $code === '12345' ? 'hospitalStaff' : null;
    }

    public function facilityName(string $code): ?string
    {
        return $code === '12345' ? 'Facility' : null;
    }

    public function canManageUsersAtFacility(string $code): bool
    {
        return $code === '12345';
    }
}

class RegistrationTestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}
