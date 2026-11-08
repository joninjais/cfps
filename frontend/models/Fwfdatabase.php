<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "{{%fwfdatabase}}".
 *
 * @property int $fwfid
 * @property string $PeriodID
 * @property string $PeriodStartDate
 * @property string $PeriodEndDate
 * @property string $CID
 * @property string $FundHolderID
 * @property string $fname
 * @property string $lname
 * @property string $timeregis
 * @property string $exsfile
 */
class Fwfdatabase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fwfdatabase}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PeriodID', 'PeriodStartDate', 'PeriodEndDate', 'CID', 'FundHolderID', 'fname', 'lname', 'timeregis'], 'required'],
            [['PeriodStartDate', 'PeriodEndDate'], 'safe'],
            [['PeriodID'], 'string', 'max' => 50],
            [['CID'], 'string', 'max' => 13],
            [['FundHolderID'], 'string', 'max' => 5],
            [['fname', 'lname', 'timeregis','exsfile'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fwfid' => 'Fwfid',
            'PeriodID' => 'PeriodID',
            'PeriodStartDate' => 'PeriodStartDate',
            'PeriodEndDate' => 'PeriodEndDate',
            'CID' => 'CID',
            'FundHolderID' => 'FundHolderID',
            'fname' => 'fname',
            'lname' => 'lname',
            'timeregis' => 'ชื่องวด',
            'exsfile' => 'ชื่อไฟล์ excel ที่นำเข้าข้อมูล',
        ];
    }
}
