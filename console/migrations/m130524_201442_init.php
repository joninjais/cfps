<?php

use yii\db\Migration;

/**
 * Creates the initial CFPS user table used by the following user migrations.
 */
class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'userid' => $this->primaryKey(),
            'user' => $this->string(50)->notNull(),
            'pass' => $this->string(255)->notNull(),
            'fullname' => $this->string(255)->null(),
            'hoscode' => $this->string(5)->notNull(),
            'phonenumber' => $this->string(10)->null(),
            'email' => $this->string(255)->null(),
            'hmain' => $this->string(5)->notNull(),
            'must_change_password' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
