<?php

namespace frontend\tests\unit\components;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

class DropUnusedTablesMigrationTest extends TestCase
{
    private $db;
    private $oldApp;
    private $oldAliases;

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/console/migrations/m130523_000001_create_legacy_application_tables.php';
        require_once dirname(__DIR__, 4) . '/console/migrations/m261114_000001_drop_unused_fwfhealth_and_user_tmp.php';
        $this->oldApp = Yii::$app;
        foreach (['@app', '@runtime'] as $alias) {
            $this->oldAliases[$alias] = Yii::getAlias($alias, false);
        }
        new \yii\console\Application([
            'id' => 'drop-unused-tables-test',
            'basePath' => dirname(__DIR__, 4) . '/console',
            'components' => ['errorHandler' => ['class' => DropUnusedTablesErrorHandler::class]],
        ]);
        $this->db = new Connection(['dsn' => 'sqlite::memory:', 'tablePrefix' => 'test_']);
        $this->db->open();
    }

    protected function tearDown(): void
    {
        $this->db->close();
        Yii::$app = $this->oldApp;
        foreach ($this->oldAliases as $alias => $value) {
            Yii::setAlias($alias, $value === false ? null : $value);
        }
    }

    public function testFreshInstallDoesNotCreateRetiredTables(): void
    {
        $tables = (new \ReflectionClass(\m130523_000001_create_legacy_application_tables::class))
            ->getConstant('TABLES');
        self::assertArrayNotHasKey('fwfhealth', $tables);
        self::assertArrayNotHasKey('user_tmp', $tables);
        self::assertArrayHasKey('xlsfileuploads', $tables);

        $migration = new \m261114_000001_drop_unused_fwfhealth_and_user_tmp(['db' => $this->db, 'compact' => true]);
        $migration->up();
        self::assertNull($this->db->getTableSchema('{{%fwfhealth}}', true));
        self::assertNull($this->db->getTableSchema('{{%user_tmp}}', true));
        self::assertFalse($migration->down());
    }

    public function testExistingTablesAndOnlyTheirLegacyStateAreRemoved(): void
    {
        foreach (['fwfhealth', 'user_tmp', 'keep_table'] as $table) {
            $this->db->createCommand()->createTable('{{%' . $table . '}}', ['id' => 'integer'])->execute();
        }
        $this->db->createCommand()->createTable('{{%m130523_legacy_table_state}}', ['table_name' => 'string'])->execute();
        foreach (['fwfhealth', 'user_tmp', 'keep_table'] as $table) {
            $this->db->createCommand()->insert('{{%m130523_legacy_table_state}}', ['table_name' => $table])->execute();
        }

        $migration = new \m261114_000001_drop_unused_fwfhealth_and_user_tmp(['db' => $this->db, 'compact' => true]);
        $migration->up();
        $migration->up();

        self::assertNull($this->db->getTableSchema('{{%fwfhealth}}', true));
        self::assertNull($this->db->getTableSchema('{{%user_tmp}}', true));
        self::assertNotNull($this->db->getTableSchema('{{%keep_table}}', true));
        self::assertSame(['keep_table'], (new \yii\db\Query())
            ->select('table_name')->from('{{%m130523_legacy_table_state}}')->column($this->db));
        $legacyMigration = new \m130523_000001_create_legacy_application_tables(['db' => $this->db, 'compact' => true]);
        $legacyMigration->safeDown();
        self::assertNull($this->db->getTableSchema('{{%keep_table}}', true));
        self::assertNull($this->db->getTableSchema('{{%m130523_legacy_table_state}}', true));
    }

    public function testPartiallyRemovedTablesCanBeRetriedWithoutStateTable(): void
    {
        $this->db->createCommand()->createTable('{{%user_tmp}}', ['id' => 'integer'])->execute();
        $migration = new \m261114_000001_drop_unused_fwfhealth_and_user_tmp(['db' => $this->db, 'compact' => true]);
        $migration->up();
        self::assertNull($this->db->getTableSchema('{{%user_tmp}}', true));
    }
}

class DropUnusedTablesErrorHandler extends \yii\console\ErrorHandler
{
    public function register()
    {
    }
}
