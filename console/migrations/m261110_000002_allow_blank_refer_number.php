<?php

use yii\db\Migration;

/**
 * Allow referral claims that do not have a refer number.
 */
class m261110_000002_allow_blank_refer_number extends Migration
{
    public function safeUp()
    {
        $table = $this->db->getTableSchema('{{%refer_claim}}', true);
        if ($table === null) {
            throw new \RuntimeException('The refer_claim table does not exist.');
        }

        $column = $table->getColumn('refer_number');
        if ($column === null) {
            throw new \RuntimeException('The refer_number column does not exist.');
        }
        if (!$column->allowNull) {
            $this->alterColumn(
                '{{%refer_claim}}',
                'refer_number',
                $this->string(50)->null()->comment('เลขที่ refer')
            );
        }
    }

    public function safeDown()
    {
        $table = $this->db->getTableSchema('{{%refer_claim}}', true);
        if ($table === null || $table->getColumn('refer_number') === null) {
            return;
        }

        $hasNullReferNumbers = (new \yii\db\Query())
            ->from('{{%refer_claim}}')
            ->where(['refer_number' => null])
            ->exists($this->db);
        if ($hasNullReferNumbers) {
            throw new \RuntimeException('Cannot restore NOT NULL while refer_claim rows have no refer number.');
        }

        $this->alterColumn(
            '{{%refer_claim}}',
            'refer_number',
            $this->string(50)->notNull()->comment('เลขที่ refer')
        );
    }
}
