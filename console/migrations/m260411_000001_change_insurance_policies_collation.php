<?php

use yii\db\Migration;

/**
 * Change collation of insurance_policies table to utf8mb4_general_ci.
 */
class m260411_000001_change_insurance_policies_collation extends Migration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE `insurance_policies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE `insurance_policies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }
}
