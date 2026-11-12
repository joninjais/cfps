<?php

use yii\db\Migration;

class m261112_000001_create_registration_window extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%registration_window}}', [
            'id' => $this->primaryKey(),
            'token' => $this->string(64),
            'expires_at' => $this->integer(),
        ]);
        $this->insert('{{%registration_window}}', ['id' => 1]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%registration_window}}');
    }
}
