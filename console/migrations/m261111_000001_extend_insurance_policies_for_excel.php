<?php

use yii\db\Migration;

class m261111_000001_extend_insurance_policies_for_excel extends Migration
{
    private function columns()
    {
        return [
            'birth_date' => $this->date()->null()->comment('วันเดือนปีเกิด'),
            'age_at_purchase' => $this->string(100)->null()->comment('อายุ ณ วันที่ซื้อ'),
            'house_no' => $this->string(255)->null()->comment('บ้านเลขที่'),
            'village_no' => $this->string(100)->null()->comment('หมู่'),
            'alley' => $this->string(255)->null()->comment('ซอย'),
            'road' => $this->string(255)->null()->comment('ถนน'),
            'address_province' => $this->string(100)->null()->comment('จังหวัดที่อยู่'),
            'subdistrict' => $this->string(100)->null()->comment('ตำบล'),
            'district' => $this->string(100)->null()->comment('อำเภอ'),
            'postal_code' => $this->string(20)->null()->comment('รหัสไปรษณีย์'),
            'reference_no' => $this->string(255)->null()->comment('เลขอ้างอิง'),
            'allocation_service_unit' => $this->decimal(12, 2)->null()->comment('จัดสรรหน่วยบริการ'),
            'allocation_provincial_pp' => $this->decimal(12, 2)->null()->comment('จัดสรร สสจ. PP'),
            'allocation_provincial_admin' => $this->decimal(12, 2)->null()->comment('จัดสรร สสจ. ค่าบริหารจัดการ'),
            'allocation_provincial_total' => $this->decimal(12, 2)->null()->comment('จัดสรร สสจ. รวม'),
            'allocation_central_hc' => $this->decimal(12, 2)->null()->comment('จัดสรรส่วนกลาง HC'),
            'allocation_central_admin' => $this->decimal(12, 2)->null()->comment('จัดสรรส่วนกลางค่าบริหารจัดการ'),
            'allocation_central_total' => $this->decimal(12, 2)->null()->comment('จัดสรรส่วนกลางรวม'),
            'occupation' => $this->string(255)->null()->comment('อาชีพ'),
            'employer_name' => $this->string(255)->null()->comment('ชื่อนายจ้าง'),
            'employer_address' => $this->text()->null()->comment('ที่อยู่สถานประกอบการนายจ้าง'),
            'employer_phone' => $this->string(100)->null()->comment('เบอร์โทรนายจ้าง'),
        ];
    }

    public function up()
    {
        foreach ($this->columns() as $name => $definition) {
            $this->addColumn('{{%insurance_policies}}', $name, $definition);
        }
    }

    public function down()
    {
        echo "Cannot revert migration: the added columns may contain imported data.\n";
        return false;
    }
}
