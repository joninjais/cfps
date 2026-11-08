<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ บริหารจัดการ / PP ต่อวัน และเฉพาะปีงบ ในตาราง {{%fwf_fy_claim}}
 * หลัง migrate ให้กด "นำเข้า" อีกครั้งเพื่อเติมค่าให้แถวเดิม
 */
class m260325_000001_add_manage_pp_to_fwf_fy_claim extends Migration
{
    public function safeUp()
    {
        $t = '{{%fwf_fy_claim}}';
        $this->addColumn($t, 'manage_ssj_per_day', $this->decimal(12, 4)->null()->comment('บริหารจัดการต่อวัน')->after('claim_in_FY'));
        $this->addColumn($t, 'pp_per_day', $this->decimal(12, 4)->null()->comment('PP ต่อวัน')->after('manage_ssj_per_day'));
        $this->addColumn($t, 'manage_ssj_in_FY', $this->decimal(12, 2)->null()->comment('บริหารจัดการเฉพาะปีงบนั้น')->after('pp_per_day'));
        $this->addColumn($t, 'pp_in_FY', $this->decimal(12, 2)->null()->comment('PP เฉพาะปีงบนั้น')->after('manage_ssj_in_FY'));
    }

    public function safeDown()
    {
        $t = '{{%fwf_fy_claim}}';
        foreach (['pp_in_FY', 'manage_ssj_in_FY', 'pp_per_day', 'manage_ssj_per_day'] as $c) {
            $this->dropColumn($t, $c);
        }
    }
}
