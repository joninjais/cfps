<?php

use yii\db\Migration;

/**
 * เปลี่ยนชื่อคอลัมน์ `fullname` เป็น `orgname` ในตาราง user
 * เพื่อสื่อความหมายว่าเป็น "ชื่อหน่วยบริการ" ไม่ใช่ชื่อบุคคล
 */
class m260316_120000_rename_user_fullname_to_orgname extends Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE {{%user}}
            CHANGE COLUMN `fullname` `orgname`
                VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                NULL DEFAULT NULL
                COMMENT 'ชื่อหน่วยบริการ'
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE {{%user}}
            CHANGE COLUMN `orgname` `fullname`
                VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                NULL DEFAULT NULL
                COMMENT 'ชื่อ-นามสกุล (เดิม)'
        ");
    }
}
