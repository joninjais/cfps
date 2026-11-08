<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model สำหรับตาราง {{%fwf_fy_claim}}
 * เก็บข้อมูล claim แยกตามปีงบประมาณ
 */
class FwfFyClaim extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%fwf_fy_claim}}';
    }

    public function rules()
    {
        return [
            [['FundHolderID', 'CID', 'PeriodStartDate', 'PeriodEndDate', 'timeregis', 'FiscalYear', 'FY_Start', 'FY_End'], 'required'],
            [['PeriodStartDate', 'PeriodEndDate', 'FY_Start', 'FY_End', 'Period_in_FY_Start', 'Period_in_FY_End'], 'safe'],
            [['FiscalYear', 'Days_in_FY', 'DaysBetween'], 'integer'],
            [['card_price', 'claim', 'claim_per_day', 'claim_in_FY'], 'number'],
            [['FundHolderID'], 'string', 'max' => 5],
            [['hosname', 'CID', 'timeregis'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'FundHolderID'       => 'รหัสโรงพยาบาล',
            'hosname'            => 'ชื่อโรงพยาบาล',
            'CID'                => 'เลข CID',
            'PeriodStartDate'    => 'วันเริ่มต้นงวด',
            'PeriodEndDate'      => 'วันสิ้นสุดงวด',
            'timeregis'          => 'งวดที่ลงทะเบียน',
            'FiscalYear'         => 'ปีงบประมาณ',
            'FY_Start'           => 'เริ่มต้นปีงบ',
            'FY_End'             => 'สิ้นสุดปีงบ',
            'Period_in_FY_Start' => 'วันเริ่มต้นช่วงทับซ้อน',
            'Period_in_FY_End'   => 'วันสิ้นสุดช่วงทับซ้อน',
            'Days_in_FY'         => 'จำนวนวันในปีงบ',
            'DaysBetween'        => 'จำนวนวันทั้งหมด',
            'card_price'         => 'ราคาบัตร',
            'claim'              => 'ค่า Claim',
            'claim_per_day'      => 'Claim/วัน',
            'claim_in_FY'        => 'Claim ในปีงบ',
            'imported_at'        => 'นำเข้าเมื่อ',
        ];
    }
}
