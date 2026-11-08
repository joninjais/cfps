<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ต่อท้ายตาราง {{%opdata}} และ {{%ipdata}}
 * refer_number, refertohospcode, sex, p_age, nation
 */
class m261104_000001_add_refer_sex_age_nation_to_opdata_ipdata extends Migration
{
    private const TABLES = ['{{%opdata}}', '{{%ipdata}}'];

    public function safeUp()
    {
        foreach (self::TABLES as $t) {
            $this->addColumn($t, 'refer_number', $this->string(50)->null()->comment('เลขที่ refer'));
            $this->addColumn($t, 'refertohospcode', $this->string(5)->null()->comment('รหัสหน่วยบริการที่ส่งต่อ'));
            $this->addColumn($t, 'sex', $this->char(1)->null()->comment('1=ชาย, 2=หญิง'));
            $this->addColumn($t, 'p_age', $this->smallInteger()->unsigned()->null()->comment('อายุเป็นปี'));
            $this->addColumn($t, 'nation', $this->char(3)->null()->comment('048=เมียนมา, 265=เมียนมา(แรงงาน), 056=ลาว, 266=ลาว(แรงงาน), 046=เวียดนาม, 057=กัมพูชา, 264=กัมพูชา(แรงงาน)'));
        }
    }

    public function safeDown()
    {
        foreach (self::TABLES as $t) {
            foreach (['nation', 'p_age', 'sex', 'refertohospcode', 'refer_number'] as $c) {
                $this->dropColumn($t, $c);
            }
        }
    }
}
