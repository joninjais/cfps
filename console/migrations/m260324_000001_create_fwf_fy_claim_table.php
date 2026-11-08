<?php

use yii\db\Migration;

/**
 * Creates table {{%fwf_fy_claim}}
 * เก็บข้อมูลการคำนวณ claim แยกตามปีงบประมาณ (จาก fwfdatabase × fy_range)
 */
class m260324_000001_create_fwf_fy_claim_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%fwf_fy_claim}}', [
            'id'                 => $this->primaryKey(),
            'FundHolderID'       => $this->string(5)->notNull()->comment('รหัสโรงพยาบาลเจ้าของกองทุน'),
            'hosname'            => $this->string(255)->null()->comment('ชื่อโรงพยาบาล'),
            'CID'                => $this->string(255)->notNull()->comment('เลขประจำตัวผู้เอาประกัน'),
            'PeriodStartDate'    => $this->date()->notNull()->comment('วันเริ่มต้นงวด (ต้นฉบับ)'),
            'PeriodEndDate'      => $this->date()->notNull()->comment('วันสิ้นสุดงวด (ต้นฉบับ)'),
            'timeregis'          => $this->string(255)->notNull()->comment('งวดที่ลงทะเบียน'),
            'FiscalYear'         => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'FY_Start'           => $this->date()->notNull()->comment('วันเริ่มต้นปีงบประมาณ'),
            'FY_End'             => $this->date()->notNull()->comment('วันสิ้นสุดปีงบประมาณ'),
            'Period_in_FY_Start' => $this->date()->null()->comment('วันเริ่มต้นที่ทับซ้อนในปีงบนั้น'),
            'Period_in_FY_End'   => $this->date()->null()->comment('วันสิ้นสุดที่ทับซ้อนในปีงบนั้น'),
            'Days_in_FY'         => $this->integer()->null()->comment('จำนวนวันในปีงบนั้น'),
            'DaysBetween'        => $this->integer()->null()->comment('จำนวนวันทั้งหมดของ record'),
            'card_price'         => $this->decimal(10, 2)->null()->comment('ราคาบัตร'),
            'claim'              => $this->decimal(12, 2)->null()->comment('ค่า claim รวม'),
            'claim_per_day'      => $this->decimal(12, 4)->null()->comment('claim ต่อวัน'),
            'claim_in_FY'        => $this->decimal(12, 2)->null()->comment('claim เฉพาะปีงบนั้น'),
            'imported_at'        => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('เวลานำเข้า'),
        ]);

        // Unique key: หนึ่ง CID + FundHolderID + งวด + ปีงบ = 1 แถว (ป้องกันนำเข้าซ้ำ)
        $this->createIndex(
            'uq_fwf_fy_claim_key',
            '{{%fwf_fy_claim}}',
            ['FundHolderID', 'CID', 'PeriodStartDate', 'PeriodEndDate', 'FiscalYear'],
            true
        );

        $this->createIndex('idx_fwf_fy_claim_fy',        '{{%fwf_fy_claim}}', 'FiscalYear');
        $this->createIndex('idx_fwf_fy_claim_holder',     '{{%fwf_fy_claim}}', 'FundHolderID');
        $this->createIndex('idx_fwf_fy_claim_cid',        '{{%fwf_fy_claim}}', 'CID');
    }

    public function safeDown()
    {
        $this->dropTable('{{%fwf_fy_claim}}');
    }
}
