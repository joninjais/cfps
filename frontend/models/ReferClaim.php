<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model สำหรับตาราง refer_claim
 * ค่ารักษาผู้ป่วยนอกส่งต่อ ประกันสุขภาพแรงงานต่างด้าว (แบบ นค.1)
 */
class ReferClaim extends ActiveRecord
{
    public static function tableName()
    {
        return 'refer_claim';
    }

    public function rules()
    {
        return [
            [['hospcode', 'serv_hospcode', 'book_number', 'book_date', 'dateserv', 'hn', 'name'], 'required'],
            [['hospcode', 'serv_hospcode'], 'string', 'max' => 20],
            [['hn', 'seq_an', 'refer_number', 'book_number'], 'string', 'max' => 50],
            [['cid', 'name', 'icd10', 'icd9', 'note', 'p_visit_type', 'adj_rw'], 'string', 'max' => 255],
            // ช่องวันที่จาก datepicker ไทยส่งมาเป็น dd/mm/พ.ศ. → แปลงเป็น Y-m-d (ค.ศ.) ก่อนตรวจสอบ
            [['dateserv', 'book_date', 'discharge_date_ipd'], 'filter', 'filter' => [self::class, 'thaiToIso']],
            [['dateserv', 'book_date', 'discharge_date_ipd'], 'date', 'format' => 'php:Y-m-d'],
            [['sex'], 'in', 'range' => ['1', '2']],
            [['p_visit_type'], 'in', 'range' => array_keys(self::visitTypeList())],
            [['p_age'], 'integer', 'min' => 0, 'max' => 150],
            [['cost_lab', 'cost_xray', 'cost_radiother', 'cost_drug', 'cost_other', 'central_pay', 'claim_amount', 'pay_total'],
                'number', 'min' => 0],
            [['cost_lab', 'cost_xray', 'cost_radiother', 'cost_drug', 'cost_other', 'central_pay', 'claim_amount', 'pay_total'],
                'default', 'value' => 0],
            [['hospcode', 'refer_number', 'serv_hospcode', 'hn', 'seq_an', 'dateserv'], 'unique',
                'targetAttribute' => ['hospcode', 'refer_number', 'serv_hospcode', 'hn', 'seq_an', 'dateserv'],
                'message' => 'มีข้อมูลนี้อยู่แล้ว (รพ.ต้นทาง + เลข refer + หน่วยบริการ + HN + VN/AN + วันที่รับบริการ ซ้ำ)'],
            [['serv_hospcode', 'seq_an'], 'unique',
                'targetAttribute' => ['serv_hospcode', 'seq_an'],
                'when' => static fn($model) => $model->seq_an !== null && $model->seq_an !== '',
                'message' => 'VN/AN นี้มีอยู่แล้วในหน่วยบริการที่เลือก'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rcid'           => 'ID',
            'hospcode'       => 'รพ.ต้นทาง (ผู้ส่งต่อ/ผู้จ่าย)',
            'serv_hospcode'  => 'หน่วยบริการที่รักษา/เรียกเก็บ',
            'book_number'    => 'เลขที่หนังสือ',
            'book_date'      => 'วันที่หนังสือ',
            'dateserv'       => 'วันที่รับบริการ',
            'hn'             => 'HN',
            'seq_an'         => 'VN/AN',
            'cid'            => 'เลขที่บัตร',
            'name'           => 'ชื่อ-สกุล',
            'refer_number'   => 'เลขที่ refer',
            'sex'            => 'เพศ',
            'p_age'          => 'อายุ (ปี)',
            'icd10'          => 'ICD-10',
            'icd9'           => 'ICD-9-CM',
            'cost_lab'       => 'ชันสูตร',
            'cost_xray'      => 'รังสี',
            'cost_radiother' => 'รังสีรักษา',
            'cost_drug'      => 'ยาและเวชภัณฑ์',
            'cost_other'     => 'บำบัดรักษาอื่นๆ',
            'cost_total'     => 'รวมทั้งสิ้น',
            'central_pay'    => 'เบิกส่วนกลาง',
            'claim_amount'   => 'จำนวนเรียกเก็บ',
            'p_visit_type'   => 'ประเภทการเข้ารับบริการ',
            'discharge_date_ipd' => 'วันที่จำหน่ายผู้ป่วยใน',
            'adj_rw'         => 'Adj. RW',
            'pay_total'      => 'จำนวนจ่าย',
            'username'       => 'ผู้บันทึก',
            'username_edit'  => 'ผู้แก้ไข',
            'record_date'    => 'วันที่บันทึก',
            'record_time'    => 'เวลาบันทึก',
            'modified_date'  => 'วันที่แก้ไข',
            'modified_time'  => 'เวลาแก้ไข',
            'approved_by'     => 'ผู้อนุมัติ',
            'approved_status' => 'สถานะการอนุมัติ',
            'approved_date'   => 'วันที่อนุมัติ',
            'approved_time'   => 'เวลาอนุมัติ',
            'note'            => 'หมายเหตุ',
        ];
    }

    /**
     * แปลง dd/mm/yyyy (พ.ศ. หรือ ค.ศ.) เป็น Y-m-d (ค.ศ.)
     * ค่าที่ไม่ตรงรูปแบบจะถูกส่งต่อโดยไม่แก้ไข เพื่อให้ validator แจ้งข้อผิดพลาด
     */
    public static function thaiToIso($value)
    {
        $value = trim((string)$value);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
            $year = (int)$m[3];
            if ($year >= 2400) {
                $year -= 543;
            }
            return sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]);
        }
        return $value;
    }

    /**
     * แปลง Y-m-d (ค.ศ.) เป็น dd/mm/yyyy (พ.ศ.) สำหรับแสดงในฟอร์ม
     * ค่าที่ไม่ตรงรูปแบบจะถูกคืนตามเดิม
     */
    public static function isoToThai($value)
    {
        if (is_string($value) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            return $m[3] . '/' . $m[2] . '/' . ((int)$m[1] + 543);
        }
        return $value;
    }

    const STATUS_PAID     = 'จ่าย';
    const STATUS_REJECTED = 'ปฏิเสธ';

    public static function approvedStatusList()
    {
        return [self::STATUS_PAID => self::STATUS_PAID, self::STATUS_REJECTED => self::STATUS_REJECTED];
    }

    /**
     * ชื่อผู้ใช้ปัจจุบัน (ใช้บันทึกผู้บันทึก/ผู้แก้ไข/ผู้อนุมัติ)
     */
    public static function currentUserName()
    {
        $session = Yii::$app->session;
        return trim((string)($session['fullname'] ?? '')) ?: (string)$session['user'];
    }

    /**
     * บันทึกผลการอนุมัติ ($status ว่าง = ล้างผลการอนุมัติ)
     * ไม่ผ่าน rules/mass assignment เพื่อไม่ให้แก้ค่าอนุมัติผ่านฟอร์มบันทึก/แก้ไข
     */
    public function setApproval($status)
    {
        if ($status === null || $status === '') {
            $this->approved_by = $this->approved_status = $this->approved_date = $this->approved_time = null;
            return true;
        }
        if (!isset(self::approvedStatusList()[$status])) {
            return false;
        }
        $this->approved_by     = self::currentUserName();
        $this->approved_status = $status;
        $this->approved_date   = date('Y-m-d');
        $this->approved_time   = date('H:i:s');
        return true;
    }

    /**
     * มีการแก้ไขข้อมูลจริงนอกเหนือจากผลอนุมัติ/ยอดรวมที่คำนวณเองหรือไม่
     * คอลัมน์ตัวเลขเทียบแบบตัวเลข ('10' เท่ากับ '10.00')
     */
    private function hasNonApprovalChanges()
    {
        $ignore  = ['approved_by', 'approved_status', 'approved_date', 'approved_time', 'cost_total'];
        $numeric = ['cost_lab', 'cost_xray', 'cost_radiother', 'cost_drug', 'cost_other',
                    'central_pay', 'claim_amount', 'pay_total', 'p_age'];

        foreach ($this->getDirtyAttributes() as $name => $value) {
            if (in_array($name, $ignore, true)) {
                continue;
            }
            $old = $this->getOldAttribute($name);
            if (in_array($name, $numeric, true) && is_numeric($value) && is_numeric($old)
                && abs((float)$value - (float)$old) < 0.004) {
                continue;
            }
            return true;
        }
        return false;
    }

    public static function sexList()
    {
        return ['1' => 'ชาย', '2' => 'หญิง'];
    }

    public static function visitTypeList()
    {
        return ['OPD' => 'ผู้ป่วยนอก (OPD)', 'IPD' => 'ผู้ป่วยใน (IPD)'];
    }

    /**
     * คำนวณยอดรวม และบันทึกผู้บันทึก/ผู้แก้ไข พร้อมวันเวลา
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // ตรวจก่อนคำนวณ cost_total เพื่อไม่ให้การคำนวณซ้ำถูกนับเป็นการแก้ไข
        $edited = !$insert && $this->hasNonApprovalChanges();

        $total = round(
            (float)$this->cost_lab + (float)$this->cost_xray + (float)$this->cost_radiother
            + (float)$this->cost_drug + (float)$this->cost_other,
            2
        );
        if ($insert || abs($total - (float)$this->cost_total) > 0.004) {
            $this->cost_total = $total;
        }

        $who = self::currentUserName();

        if ($insert) {
            $this->username    = $who;
            $this->record_date = date('Y-m-d');
            $this->record_time = date('H:i:s');
        } elseif ($edited) {
            // ประทับผู้แก้ไขเฉพาะเมื่อมีการแก้ข้อมูลจริง (การอนุมัติอย่างเดียวไม่นับเป็นการแก้ไข)
            $this->username_edit = $who;
            $this->modified_date = date('Y-m-d');
            $this->modified_time = date('H:i:s');
        }

        return true;
    }

    public function beforeValidate()
    {
        if ($this->seq_an !== null && trim((string)$this->seq_an) === '') {
            $this->seq_an = null;
        }
        if ($this->refer_number !== null && trim((string)$this->refer_number) === '') {
            $this->refer_number = null;
        }

        return parent::beforeValidate();
    }
}
