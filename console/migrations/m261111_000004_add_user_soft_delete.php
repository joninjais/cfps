<?php

use yii\db\Migration;

class m261111_000004_add_user_soft_delete extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        if ((new \yii\db\Query())->from('{{%user}}')->where(['is_deleted' => 1])->exists($this->db)) {
            echo "Cannot remove is_deleted while deleted users exist.\n";
            return false;
        }

        $this->dropColumn('{{%user}}', 'is_deleted');
    }
}
