<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "insurance_policies".
 *
 * @property int $id
 * @property string $agency_code รหัสหน่วยงาน
 * @property string $agency_name หน่วยงาน
 * @property string $province จังหวัด
 * @property string $f_code FCode
 * @property string $passport_no หมายเลขหนังสือเดินทาง
 * @property string $citizen_id เลขบัตรประจำตัว
 * @property string $alien_ref_id เลขอ้างอิงคนต่างด้าว
 * @property string $title_name คำนำหน้า
 * @property string $full_name ชื่อ-นามสกุล
 * @property string $nationality สัญชาติ
 * @property string $ethnicity เชื้อชาติ
 * @property string|null $birth_date วันเดือนปีเกิด
 * @property string|null $age_at_purchase อายุ ณ วันที่ซื้อ
 * @property string|null $house_no บ้านเลขที่
 * @property string|null $village_no หมู่
 * @property string|null $alley ซอย
 * @property string|null $road ถนน
 * @property string|null $address_province จังหวัดที่อยู่
 * @property string|null $subdistrict ตำบล
 * @property string|null $district อำเภอ
 * @property string|null $postal_code รหัสไปรษณีย์
 * @property string|null $reference_no เลขอ้างอิง
 * @property string|null $allocation_service_unit จัดสรรหน่วยบริการ
 * @property string|null $allocation_provincial_pp จัดสรร สสจ. PP
 * @property string|null $allocation_provincial_admin จัดสรร สสจ. ค่าบริหารจัดการ
 * @property string|null $allocation_provincial_total จัดสรร สสจ. รวม
 * @property string|null $allocation_central_hc จัดสรรส่วนกลาง HC
 * @property string|null $allocation_central_admin จัดสรรส่วนกลางค่าบริหารจัดการ
 * @property string|null $allocation_central_total จัดสรรส่วนกลางรวม
 * @property string|null $occupation อาชีพ
 * @property string|null $employer_name ชื่อนายจ้าง
 * @property string|null $employer_address ที่อยู่สถานประกอบการนายจ้าง
 * @property string|null $employer_phone เบอร์โทรนายจ้าง
 * @property string $hn_code HN
 * @property string $purchase_date วันที่ซื้อกรมธรรม์
 * @property string $effective_date วันที่เกิดสิทธิ
 * @property string $expiry_date วันที่หมดอายุ
 * @property string $policy_status สถานะกรมธรรม์
 * @property string $card_price ราคาบัตร
 * @property string $coverage_period ระยะเวลาคุ้มครอง
 * @property string $father_passport_no หมายเลขหนังสือเดินทาง บิดา
 * @property string $father_citizen_id เลขบัตรประจำตัว บิดา
 * @property string $father_alien_ref_id เลขอ้างอิงคนต่างด้าว บิดา
 * @property string $father_full_name ชื่อ-นามสกุล บิดา
 * @property string $mother_passport_no หมายเลขหนังสือเดินทาง มารดา
 * @property string $mother_citizen_id เลขบัตรประจำตัว มารดา
 * @property string $mother_alien_ref_id เลขอ้างอิงคนต่างด้าว มารดา
 * @property string $mother_full_name ชื่อ-นามสกุล มารดา
 * @property string $created_at
 */
class Insurancepolicies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'insurance_policies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['birth_date', 'purchase_date', 'effective_date', 'expiry_date', 'created_at'], 'safe'],
            [['card_price', 'allocation_service_unit', 'allocation_provincial_pp', 'allocation_provincial_admin', 'allocation_provincial_total', 'allocation_central_hc', 'allocation_central_admin', 'allocation_central_total'], 'number'],
            [['coverage_period', 'employer_address'], 'string'],
            [['postal_code'], 'string', 'max' => 20],
            [['age_at_purchase', 'village_no', 'address_province', 'subdistrict', 'district', 'employer_phone'], 'string', 'max' => 100],
            [['house_no', 'alley', 'road', 'reference_no', 'occupation', 'employer_name'], 'string', 'max' => 255],
            [['agency_code', 'citizen_id', 'alien_ref_id', 'father_citizen_id', 'father_alien_ref_id', 'mother_citizen_id', 'mother_alien_ref_id'], 'string', 'max' => 20],
            [['agency_name', 'full_name', 'father_full_name', 'mother_full_name'], 'string', 'max' => 255],
            [['province', 'nationality', 'ethnicity', 'policy_status'], 'string', 'max' => 100],
            [['f_code', 'passport_no', 'title_name', 'hn_code', 'father_passport_no', 'mother_passport_no'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agency_code' => 'รหัสหน่วยงาน',
            'agency_name' => 'หน่วยงาน',
            'province' => 'จังหวัด',
            'f_code' => 'FCode',
            'passport_no' => 'หมายเลขหนังสือเดินทาง',
            'citizen_id' => 'เลขบัตรประจำตัว',
            'alien_ref_id' => 'เลขอ้างอิงคนต่างด้าว',
            'title_name' => 'คำนำหน้า',
            'full_name' => 'ชื่อ-นามสกุล',
            'nationality' => 'สัญชาติ',
            'ethnicity' => 'เชื้อชาติ',
            'birth_date' => 'วัน/เดือน/ปี เกิด',
            'age_at_purchase' => 'อายุ ณ วันที่ซื้อ',
            'house_no' => 'บ้านเลขที่',
            'village_no' => 'หมู่',
            'alley' => 'ซอย',
            'road' => 'ถนน',
            'address_province' => 'จังหวัดที่อยู่',
            'subdistrict' => 'ตำบล',
            'district' => 'อำเภอ',
            'postal_code' => 'รหัสไปรษณีย์',
            'reference_no' => 'เลขอ้างอิง',
            'allocation_service_unit' => 'จัดสรรหน่วยบริการ',
            'allocation_provincial_pp' => 'จัดสรร สสจ. PP',
            'allocation_provincial_admin' => 'จัดสรร สสจ. ค่าบริหารจัดการ',
            'allocation_provincial_total' => 'จัดสรร สสจ. รวม',
            'allocation_central_hc' => 'จัดสรรส่วนกลาง HC',
            'allocation_central_admin' => 'จัดสรรส่วนกลางค่าบริหารจัดการ',
            'allocation_central_total' => 'จัดสรรส่วนกลางรวม',
            'occupation' => 'อาชีพ',
            'employer_name' => 'ชื่อนายจ้าง',
            'employer_address' => 'ที่อยู่/สถานประกอบการ นายจ้าง',
            'employer_phone' => 'เบอร์โทรนายจ้าง',
            'hn_code' => 'HN',
            'purchase_date' => 'วันที่ซื้อกรมธรรม์',
            'effective_date' => 'วันที่เกิดสิทธิ',
            'expiry_date' => 'วันที่หมดอายุ',
            'policy_status' => 'สถานะกรมธรรม์',
            'card_price' => 'ราคาบัตร',
            'coverage_period' => 'ระยะเวลาคุ้มครอง',
            'father_passport_no' => 'หมายเลขหนังสือเดินทาง บิดา',
            'father_citizen_id' => 'เลขบัตรประจำตัว บิดา',
            'father_alien_ref_id' => 'เลขอ้างอิงคนต่างด้าว บิดา',
            'father_full_name' => 'ชื่อ-นามสกุล บิดา',
            'mother_passport_no' => 'หมายเลขหนังสือเดินทาง มารดา',
            'mother_citizen_id' => 'เลขบัตรประจำตัว มารดา',
            'mother_alien_ref_id' => 'เลขอ้างอิงคนต่างด้าว มารดา',
            'mother_full_name' => 'ชื่อ-นามสกุล มารดา',
            'created_at' => 'วันที่สร้าง',
        ];
    }
}
