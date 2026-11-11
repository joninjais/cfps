<?php

namespace frontend\tests\unit\components;

use frontend\controllers\InsurancepoliciesController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Session;

class InsurancePolicyDashboardTest extends TestCase
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
            'id' => 'policy-dashboard-test',
            'basePath' => dirname(__DIR__, 3),
            'components' => [
                'db' => ['class' => \yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
                'request' => ['class' => \yii\web\Request::class, 'scriptUrl' => '/index.php', 'hostInfo' => 'http://localhost'],
                'errorHandler' => ['class' => DashboardTestErrorHandler::class],
            ],
        ]);
        $db = $this->app->db;
        $db->createCommand('CREATE TABLE insurance_policies (agency_code TEXT, agency_name TEXT, purchase_date TEXT, card_price DECIMAL, policy_status TEXT)')->execute();
        $db->pdo->sqliteCreateFunction('DATE_FORMAT', static fn($date, $format) => substr($date, 0, 7), 2);
        foreach ([
            ['00001', '2023-09-30', 1],
            ['00001', '2023-10-01', 10],
            ['00001', '2024-02-28', 20],
            ['00001', '2024-02-29', 30],
            ['00001', '2024-03-01', 40],
            ['00001', '2024-09-30', 50],
            ['00001', '2024-10-01', 60],
            ['00002', '2024-02-29', 100],
            ['00001', null, 200],
        ] as [$agency, $date, $price]) {
            $db->createCommand()->insert('insurance_policies', [
                'agency_code' => $agency, 'agency_name' => $agency,
                'purchase_date' => $date, 'card_price' => $price, 'policy_status' => 'active',
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

    public static function periods(): array
    {
        return [
            'fiscal year includes Oct to Sep' => [['fiscal_year' => '2567'], '00001', 5, 150, 5],
            'October belongs to preceding calendar year' => [['fiscal_year' => '2567', 'month' => '10'], '00001', 1, 10, 1],
            'leap February' => [['fiscal_year' => '2567', 'month' => '2'], '00001', 2, 50, 2],
            'empty month' => [['fiscal_year' => '2568', 'month' => '2'], '00001', 0, 0, 0],
            'next fiscal year' => [['fiscal_year' => '2568'], '00001', 1, 60, 1],
            'admin includes all agencies' => [['fiscal_year' => '2567', 'month' => '2'], '00064', 3, 150, 3],
            'admin selects first agency' => [['fiscal_year' => '2567', 'month' => '2', 'agency' => '00001'], '00064', 2, 50, 2],
            'admin selects second agency' => [['fiscal_year' => '2567', 'month' => '2', 'agency' => '00002'], '00064', 1, 100, 1],
            'admin selects agency for all time' => [['all' => '1', 'agency' => '00002'], '00064', 1, 100, 1],
            'all includes undated records' => [['all' => '1'], '00001', 8, 411, 7],
        ];
    }

    #[DataProvider('periods')]
    public function testPeriodAppliesToAllAggregates(array $params, string $agency, int $count, int $amount, int $datedCount): void
    {
        $result = $this->dashboard($params, $agency);
        self::assertSame($count, (int)$result['totals']['cnt']);
        self::assertSame($amount, (int)$result['totals']['total_amount']);
        foreach (['byPrice', 'byAgency'] as $group) {
            self::assertSame($count, array_sum(array_column($result[$group], 'cnt')), $group);
            self::assertSame($amount, array_sum(array_column($result[$group], 'total_amount')), $group);
        }
        self::assertSame($datedCount, array_sum(array_column($result['byMonth'], 'cnt')));
        if (empty($params['all'])) {
            self::assertSame($amount, array_sum(array_column($result['byMonth'], 'total_amount')));
        }
    }

    public function testDefaultIsCurrentFiscalYear(): void
    {
        $result = $this->dashboard([], '00064');
        $today = new \DateTimeImmutable('today');
        $year = (int)$today->format('Y') + ((int)$today->format('n') >= 10 ? 1 : 0);
        self::assertSame($year + 543, $result['fiscalYear']);
        self::assertSame(($year - 1) . '-10-01', $result['start']->format('Y-m-d'));
        self::assertSame($year . '-10-01', $result['end']->format('Y-m-d'));
    }

    public function testAgencySelectionIsExactAndOptionsCoverAllPeriods(): void
    {
        $this->app->db->createCommand()->insert('insurance_policies', [
            'agency_code' => '100001', 'agency_name' => '<hospital>',
            'purchase_date' => '2024-02-29', 'card_price' => 999, 'policy_status' => 'active',
        ])->execute();
        $result = $this->dashboard(['fiscal_year' => '2568', 'agency' => '00002'], '00064');
        self::assertSame(0, (int)$result['totals']['cnt']);
        self::assertArrayHasKey('00002', $result['agencies']);
        self::assertArrayHasKey('100001', $result['agencies']);
        $html = $this->app->view->renderFile(dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php', $result);
        self::assertStringContainsString('&lt;hospital&gt;', $html);
        self::assertStringNotContainsString('<hospital>', $html);
        $result = $this->dashboard(['fiscal_year' => '2567', 'month' => '2', 'agency' => '00001'], '00064');
        self::assertSame(2, (int)$result['totals']['cnt']);
        self::assertSame('00001', $result['selectedAgency']);
    }

    public function testAdminViewPreservesAgencyAndPeriod(): void
    {
        $result = $this->dashboard(['fiscal_year' => '2567', 'month' => '2', 'agency' => '00002'], '00064');
        $html = $this->app->view->renderFile(dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php', $result);
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($document);
        self::assertSame(1, $xpath->query('//select[@name="agency"]/option[@value="00002" and @selected]')->length);
        self::assertSame(1, $xpath->query('//input[@name="fiscal_year" and @value="2567"]')->length);
        self::assertSame(1, $xpath->query('//input[@name="month" and @value="2"]')->length);
        $links = $xpath->query('//section[@class="db-filter"]//a');
        self::assertGreaterThan(10, $links->length);
        foreach ($links as $link) {
            parse_str((string)parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
            self::assertSame('00002', $query['agency'] ?? null);
        }
        $allResult = $this->dashboard(['all' => '1', 'fiscal_year' => '2567', 'agency' => '00002'], '00064');
        $allHtml = $this->app->view->renderFile(dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php', $allResult);
        self::assertStringContainsString('name="all" value="1"', $allHtml);
        self::assertStringContainsString('name="fiscal_year" value="2567"', $allHtml);
    }

    public function testNonAdminCannotSelectAnotherAgency(): void
    {
        $this->expectException(ForbiddenHttpException::class);
        $this->dashboard(['agency' => '00002'], '00001');
    }

    public function testNonAdminRejectsArrayAgencyParameter(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->dashboard(['agency' => ['00002']], '00001');
    }

    public function testNonAdminViewDoesNotShowAgencySelector(): void
    {
        $html = $this->app->view->renderFile(
            dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php',
            $this->dashboard([], '00001')
        );
        self::assertStringNotContainsString('id="policy-agency"', $html);
    }

    public function testViewRendersSelectedMonthAndYearLinks(): void
    {
        $result = $this->dashboard(['fiscal_year' => '2567', 'month' => '2'], '00001');
        $html = $this->app->view->renderFile(
            dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php',
            $result
        );
        self::assertStringContainsString('ปีงบประมาณ 2567', $html);
        self::assertStringContainsString('ทั้งปีงบ 2567', $html);
        self::assertStringContainsString('01/02/2567', $html);
        self::assertStringContainsString('29/02/2567', $html);
        self::assertStringContainsString('ก.พ. 67', $html);
        self::assertStringContainsString('ทุกช่วงเวลา', $html);
        self::assertStringContainsString('aria-current="true"', $html);
        $html = $this->app->view->renderFile(
            dirname(__DIR__, 3) . '/views/insurancepolicies/dashboard.php',
            $this->dashboard(['fiscal_year' => '2568', 'month' => '2'], '00001')
        );
        self::assertStringContainsString('28/02/2568', $html);
    }

    public static function invalidPeriods(): array
    {
        return [
            [['fiscal_year' => 'invalid']],
            [['fiscal_year' => ['2567']]],
            [['fiscal_year' => '2443']],
            [['month' => '0']],
            [['month' => '13']],
            [['month' => ['2']]],
            [['all' => 'yes']],
            [['all' => '1', 'month' => '2']],
            [['agency' => ['00001']]],
            [['agency' => 'unknown']],
            [['agency' => str_repeat('a', 21)]],
        ];
    }

    #[DataProvider('invalidPeriods')]
    public function testInvalidParametersAreRejected(array $params): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->dashboard($params, '00064');
    }

    public function testMissingAgencyIsRejected(): void
    {
        $this->expectException(ForbiddenHttpException::class);
        $this->dashboard([], '');
    }

    private function dashboard(array $params, string $agency): array
    {
        $session = new DashboardTestSession();
        $session->agency = $agency;
        $this->app->set('session', $session);
        $this->app->request->setQueryParams($params);
        $controller = new PolicyDashboardTestController('insurancepolicies', $this->app);
        $this->app->controller = $controller;
        return $controller->actionDashboard();
    }
}

class PolicyDashboardTestController extends InsurancepoliciesController
{
    public function render($view, $params = [])
    {
        return $params;
    }
}

class DashboardTestErrorHandler extends \yii\web\ErrorHandler
{
    public function register()
    {
    }
}

class DashboardTestSession extends Session
{
    public $agency;

    public function offsetGet($offset): mixed
    {
        return $offset === 'hoscode' ? $this->agency : null;
    }
}
