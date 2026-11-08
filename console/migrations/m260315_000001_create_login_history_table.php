<?php

use yii\db\Migration;

/**
 * Creates table login_history for tracking user access (IP, device, time).
 */
class m260315_000001_create_login_history_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%login_history}}', [
            'id'         => $this->primaryKey(),
            'user'       => $this->string(100)->notNull(),
            'hoscode'    => $this->string(20)->defaultValue(null),
            'ip_address' => $this->string(45)->notNull(),
            'user_agent' => $this->text()->defaultValue(null),
            'device_type'=> $this->string(20)->defaultValue(null),   // desktop / mobile / tablet
            'login_at'   => $this->integer()->unsigned()->notNull(),
            'status'     => $this->string(10)->defaultValue('success'), // success / failed
        ]);

        $this->createIndex('idx_login_history_user',     '{{%login_history}}', 'user');
        $this->createIndex('idx_login_history_login_at', '{{%login_history}}', 'login_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%login_history}}');
    }
}
