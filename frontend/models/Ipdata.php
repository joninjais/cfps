<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "{{%ipdata}}".
 *
 * @property int $ipid
 * @property string $hospcode
 * @property string $admitdate
 * @property string $dischartdate
 * @property string $name
 * @property string $fund
 * @property string $cid
 * @property string $hn
 * @property string $an
 * @property string $icd10
 * @property string $icd9
 * @property string $cost
 * @property string $adjrw
 * @property int $los
 * @property string $costadjrw
 * @property string $refer_number
 * @property string $refertohospcode
 * @property string $sex
 * @property int $p_age
 * @property string $nation
 */
class Ipdata extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ipdata}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospcode', 'admitdate', 'dischartdate', 'name', 'hn', 'an'], 'required'],
            [['admitdate', 'dischartdate'], 'safe'],
            [['cost', 'costadjrw'], 'number'],
            [['los'], 'integer'],
            [['hospcode','FundHolderID'], 'string', 'max' => 5],
            [['name', 'fund'], 'string', 'max' => 255],
            [['cid'], 'string', 'max' => 13],
            [['hn', 'an'], 'string', 'max' => 50],
            [['icd10', 'icd9'], 'string', 'max' => 255],
            [['adjrw'], 'string', 'max' => 25],
            [['exsfile'], 'string', 'max' => 255],
            [['refer_number'], 'string', 'max' => 50],
            [['refertohospcode'], 'string', 'max' => 5],
            [['sex'], 'in', 'range' => ['1', '2']],
            [['p_age'], 'integer', 'min' => 0],
            [['nation'], 'in', 'range' => ['048', '265', '056', '266', '046', '057', '264', '208']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ipid' => 'Ipid',
            'hospcode' => 'รหัสสถานบริการ',
            'admitdate' => 'วันที่ Admit',
            'dischartdate' => 'วันที่ DC',
            'name' => 'ชื่อ นามสกุล',
            'fund' => 'สังกัด/กองทุน',
            'cid' => 'เลขที่บัตรประชาชน',
            'hn' => 'เลขที่ HN',
            'an' => 'เลขที่ AN',
            'icd10' => 'ICD10',
            'icd9' => 'ICD9',
            'cost' => 'ค่ารักษาที่เกิดขึ้นจริง',
            'adjrw' => 'Adj.RW',
            'los' => 'วันนอน',
            'costadjrw' => 'ค่ารักษาตาม Adj.RW',
            'FundHolderID' => 'รหัส รพ ที่ซื้อบัตร',
            'exsfile' => 'ชื่อไฟล์ excel ที่นำเข้าข้อมูล',
            'refer_number' => 'เลขที่ Refer',
            'refertohospcode' => 'รหัสหน่วยบริการที่ส่งต่อ',
            'sex' => 'เพศ',
            'p_age' => 'อายุ (ปี)',
            'nation' => 'สัญชาติ',
        ];
    }
}
