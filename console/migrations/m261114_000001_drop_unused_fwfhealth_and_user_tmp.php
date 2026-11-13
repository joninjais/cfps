<?php

use yii\db\Migration;

class m261114_000001_drop_unused_fwfhealth_and_user_tmp extends Migration
{
    public function up()
    {
        $stateTable = '{{%m130523_legacy_table_state}}';
        foreach (['fwfhealth', 'user_tmp'] as $table) {
            $tableName = '{{%' . $table . '}}';
            if ($this->db->getTableSchema($tableName, true) !== null) {
                $this->dropTable($tableName);
            }
            if ($this->db->getTableSchema($stateTable, true) !== null) {
                $this->delete($stateTable, ['table_name' => $table]);
            }
        }
    }

    public function down()
    {
        echo "Cannot revert migration: the removed tables and their data cannot be restored.\n";
        return false;
    }
}
