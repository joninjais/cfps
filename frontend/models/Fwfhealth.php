<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "{{%fwfhealth}}".
 *
 * @property int $fwfhealth_id
 * @property string $datev
 * @property string $hcode
 * @property string $cid
 * @property string $prefix
 * @property string $fname
 * @property string $lname
 * @property string $birthday
 * @property string $nation
 * @property string $sex
 * @property string $address
 * @property string $company
 * @property string $bossname
 * @property string $tel
 * @property string $hn
 * @property string $card_id ราคาบัตร
 * @property string $typehealth_id ประเภทผลตรวจสุขภาพ
 * @property string $diesease_id โรคที่เป็น
 */
class Fwfhealth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fwfhealth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['datev', 'prefix'], 'string', 'max' => 50],
            [['hcode'], 'string', 'max' => 5],
            [['cid'], 'string', 'max' => 13],
            [['fname', 'lname', 'birthday', 'nation', 'sex', 'address', 'company', 'bossname', 'tel', 'hn', 'card_id', 'typehealth_id', 'diesease_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fwfhealth_id' => 'Fwfhealth ID',
            'datev' => 'Datev',
            'hcode' => 'Hcode',
            'cid' => 'Cid',
            'prefix' => 'Prefix',
            'fname' => 'Fname',
            'lname' => 'Lname',
            'birthday' => 'Birthday',
            'nation' => 'Nation',
            'sex' => 'Sex',
            'address' => 'Address',
            'company' => 'Company',
            'bossname' => 'Bossname',
            'tel' => 'Tel',
            'hn' => 'Hn',
            'card_id' => 'ราคาบัตร',
            'typehealth_id' => 'ประเภทผลตรวจสุขภาพ',
            'diesease_id' => 'โรคที่เป็น',
        ];
    }
}
