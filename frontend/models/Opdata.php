<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "{{%opdata}}".
 *
 * @property int $opid
 * @property string $hospcode
 * @property string $dateserv
 * @property string $name
 * @property string $fund
 * @property string $cid
 * @property string $hn
 * @property string $seq
 * @property string $icd10
 * @property string $m1
 * @property string $m2
 * @property string $m3
 * @property string $m4
 * @property string $m5
 * @property string $m6
 * @property string $m7
 * @property string $m8
 * @property string $m9
 * @property string $m10
 * @property string $m11
 * @property string $m12
 * @property string $m13
 * @property string $m14
 * @property string $m15
 * @property string $m16
 * @property string $alltotal
 * @property string $FundHolderID
 * @property string $exsfile
 * @property string $refer_number
 * @property string $refertohospcode
 * @property string $sex
 * @property int $p_age
 * @property string $nation
 */
class Opdata extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%opdata}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospcode', 'dateserv', 'name', 'cid','hn','seq'], 'required'],
            [['dateserv'], 'safe'],
            [['hospcode'], 'string', 'max' => 5],
            [['name', 'fund', 'icd10','exsfile'], 'string', 'max' => 255],
            [['cid'], 'string', 'max' => 13],
            [['hn', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16'], 'string', 'max' => 50],
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
            'opid' => 'Opid',
            'hospcode' => 'รหัสสถานบริการ',
            'dateserv' => 'วันที่ รับบริการ',
            'name' => 'ชื่อแรงงาน',
            'fund' => 'สังกัด/กองทุน',
            'cid' => 'เลขที่บัตรประชาชน',
            'hn' => 'เลที่ HN',
            'seq' => 'vn',
            'icd10' => 'ICD10',
            'm1' => 'ค่าห้อง/ค่าอาหาร',
            'm2' => 'ค่าอวัยวะเทียม',
            'm3' => 'ค่ายาในรพ.',
            'm4' => 'ค่ายากลับบ้าน',
            'm5' => 'ค่าเวชภัณฑ์ที่มิใช่ยา',
            'm6' => 'ค่าบริการโลหิต',
            'm7' => 'ค่าแลป',
            'm8' => 'ค่าX-ray',
            'm9' => 'ค่าตรวจวินิจฉัยพิเศษ',
            'm10' => 'ค่าอุปกรณ์ของใช้',
            'm11' => 'ค่าทำหัตถการ และวิสัญญี',
            'm12' => 'ค่าบริการทางการพยาบาล',
            'm13' => 'ค่าบริการทางทันตกรรม',
            'm14' => 'ค่าบริการทางกายภาพบำบัด',
            'm15' => 'ค่าบริการฝังเข็ม',
            'm16' => 'ค่าบริการอื่นๆ',
            'alltotal' => 'รวมค่าบริการ',
            'FundHolderID' => 'กองทุน',
            'exsfile' => 'ชื่อไฟล์ excel ที่นำเข้าข้อมูล',
            'refer_number' => 'เลขที่ Refer',
            'refertohospcode' => 'รหัสหน่วยบริการที่ส่งต่อ',
            'sex' => 'เพศ',
            'p_age' => 'อายุ (ปี)',
            'nation' => 'สัญชาติ',
        ];
    }
}
