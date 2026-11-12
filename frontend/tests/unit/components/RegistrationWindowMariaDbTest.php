<?php

namespace frontend\tests\unit\components;

use frontend\components\RegistrationWindow;
use frontend\controllers\SiteController;
use frontend\models\User;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;
use yii\web\Session;

class RegistrationWindowMariaDbTest extends TestCase
{
    private $previousApp;
    private $aliases = [];
    private $app;
    private $requestMethod;
    private $initialized = false;

    protected function setUp(): void
    {
        if (getenv('CFPS_MARIADB_REGISTRATION_TEST') !== '1') {
            self::markTestSkipped('Set CFPS_MARIADB_REGISTRATION_TEST=1 to run the MariaDB integration test.');
        }

        $this->previousApp = Yii::$app;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->initialized = true;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        foreach (['@app', '@runtime', '@webroot', '@web'] as $alias) {
            $this->aliases[$alias] = Yii::getAlias($alias, false);
        }
        $config = require dirname(__DIR__, 4) . '/common/config/main-local.php';
        $this->app = new Application([
            'id' => 'registration-mariadb-test',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'db' => $config['components']['db'],
                'request' => ['class' => \yii\web\Request::class, 'scriptUrl' => '/index.php', 'hostInfo' => 'http://localhost'],
                'user' => ['identityClass' => User::class, 'enableSession' => false],
                'errorHandler' => ['class' => RegistrationMariaDbTestErrorHandler::class],
                'facilityAccess' => ['class' => RegistrationMariaDbTestFacilityAccess::class],
            ],
        ]);
        $this->app->set('session', $this->createStub(Session::class));
        $db = $this->app->db;
        $db->createCommand('CREATE TEMPORARY TABLE registration_window (id INT PRIMARY KEY, token VARCHAR(64), expires_at INT) ENGINE=InnoDB')->execute();
        $userDefinition = $db->createCommand('SHOW CREATE TABLE user')->queryOne();
        $sql = preg_replace('/^CREATE TABLE /', 'CREATE TEMPORARY TABLE ', $userDefinition['Create Table'], 1);
        $db->createCommand($sql)->execute();
        $db->createCommand('CREATE TEMPORARY TABLE chospital2 (hospcode VARCHAR(5) PRIMARY KEY, hosname VARCHAR(100)) ENGINE=InnoDB')->execute();
        $db->createCommand()->insert('registration_window', ['id' => 1])->execute();
        $db->createCommand()->insert('chospital2', ['hospcode' => '12345', 'hosname' => 'Test Facility'])->execute();
    }

    protected function tearDown(): void
    {
        if (!$this->initialized) {
            return;
        }
        if ($this->app !== null) {
            $this->app->db->close();
            Yii::$app = $this->previousApp;
        }
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        foreach ($this->aliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testSignupUsesParameterizedLockAndCreatesPendingUser(): void
    {
        RegistrationWindow::open(30);
        $token = RegistrationWindow::current()['token'];
        $controller = new RegistrationMariaDbTestSiteController('site', $this->app);
        $this->app->controller = $controller;
        $this->app->request->setBodyParams(['SignupForm' => [
            'username' => 'test-registration',
            'email' => 'test-registration@example.test',
            'password' => 'Password1',
            'passwordConfirm' => 'Password1',
            'phonenumber' => '0812345678',
            'fname' => 'Test',
            'lname' => 'User',
            'hoscode' => '12345',
        ]]);
        $controller->actionSignup($token);
        $user = User::findOne(['user' => 'test-registration']);
        self::assertNotNull($user);
        self::assertSame(User::APPROVAL_PENDING, $user->approval_status);
        self::assertSame('12345', $user->hmain);
        self::assertSame('0812345678', $user->phonenumber);
        self::assertTrue($user->validatePassword('Password1'));
    }
}

class RegistrationMariaDbTestSiteController extends SiteController
{
    public function render($view, $params = [])
    {
        return $view;
    }
}

class RegistrationMariaDbTestFacilityAccess extends \yii\base\Component
{
    public function expectedStaffRole(string $code): ?string
    {
        return $code === '12345' ? 'hospitalStaff' : null;
    }

    public function facilityName(string $code): ?string
    {
        return $code === '12345' ? 'Test Facility' : null;
    }
}

class RegistrationMariaDbTestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}
