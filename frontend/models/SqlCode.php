<?php

namespace frontend\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * โมเดลสำหรับตาราง sql_code เก็บคำสั่ง SQL แยกตาม HIS และประเภท
 *
 * @property int    $id
 * @property string $his          ชื่อระบบ HIS
 * @property string $type         ประเภท: OP หรือ IP
 * @property string $title        ชื่อ/คำอธิบาย
 * @property string $sql_command  คำสั่ง SQL
 * @property string $note         หมายเหตุ
 * @property int    $created_at
 * @property int    $updated_at
 */
class SqlCode extends \yii\db\ActiveRecord
{
    /** ประเภทผู้ป่วยนอก */
    const TYPE_OP = 'OP';
    /** ประเภทผู้ป่วยใน */
    const TYPE_IP = 'IP';

    /**
     * {@inheritdoc}
     * คืนชื่อตารางในฐานข้อมูล
     */
    public static function tableName()
    {
        return '{{%sql_code}}';
    }

    /**
     * ใช้ TimestampBehavior บันทึกเวลาสร้าง/แก้ไขอัตโนมัติ
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * กำหนดกฎการตรวจสอบข้อมูล
     */
    public function rules()
    {
        return [
            [['his', 'type', 'title', 'sql_command'], 'required'],
            [['his', 'title'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 10],
            [['type'], 'in', 'range' => [self::TYPE_OP, self::TYPE_IP]],
            [['sql_command', 'note'], 'string'],
        ];
    }

    /**
     * กำหนด label ของแต่ละฟิลด์
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ลำดับ',
            'his'         => 'ระบบ HIS',
            'type'        => 'ประเภท',
            'title'       => 'ชื่อ/คำอธิบาย',
            'sql_command' => 'คำสั่ง SQL',
            'note'        => 'หมายเหตุ',
            'created_at'  => 'วันที่สร้าง',
            'updated_at'  => 'แก้ไขล่าสุด',
        ];
    }

    /**
     * คืน label ประเภทเป็นภาษาไทย
     */
    public function getTypeLabel()
    {
        return $this->type === self::TYPE_OP ? 'ผู้ป่วยนอก (OP)' : 'ผู้ป่วยใน (IP)';
    }

    /**
     * คืน badge class ตามประเภท
     */
    public function getTypeBadgeClass()
    {
        return $this->type === self::TYPE_OP ? 'label-info' : 'label-warning';
    }
}
