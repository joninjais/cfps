<?php

namespace frontend\controllers;

use Yii;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * BaseController — เป็น base class ของทุก controller ใน frontend
 * ทำหน้าที่ตรวจสอบ session ทุก action:
 *   1. ถ้ายังไม่ล็อกอิน → redirect /site/login
 *   2. ถ้า session หมดอายุ (4 ชม.) → ทำลาย session แล้ว redirect /site/login
 */
class BaseController extends \yii\web\Controller
{
    /**
     * Override ใน subclass เพื่อระบุ action IDs ที่ไม่ต้องการ auth
     * เช่น ['login', 'error', 'captcha']
     */
    protected function getPublicActionIds(): array
    {
        return [];
    }

    /** รหัสสัญชาติ (nation) ที่อนุญาตให้นำเข้า */
    const ALLOWED_NATIONS = ['048', '265', '056', '266', '046', '057', '264','208'];

    /**
     * แปลงค่า nation จาก Excel เป็นรหัส 3 หลัก (Excel อาจอ่านเป็นตัวเลข เช่น 48)
     * คืน null ถ้าว่างหรือไม่อยู่ในรายการที่อนุญาต
     */
    protected function normalizeNation($value): ?string
    {
        $value = trim((string)$value);
        if (!preg_match('/^\d{1,3}$/', $value)) {
            return null;
        }
        $value = str_pad($value, 3, '0', STR_PAD_LEFT);
        return in_array($value, self::ALLOWED_NATIONS, true) ? $value : null;
    }

    /** รหัสหน่วยบริการของสำนักงานระดับจังหวัด (ผู้ดูแลระบบ) */
    const PROVINCIAL_HOSCODE = '00064';

    /** FundHolderID ที่ไม่ใช้เป็นตัวเลือกแรกเมื่อมีหลาย period ซ้อนกัน */
    const DEPRIORITIZED_FUND_HOLDERS = ['10739', '10740'];

    protected function isProvincialAdmin(): bool
    {
        return trim((string)Yii::$app->session->get('hoscode')) === self::PROVINCIAL_HOSCODE;
    }

    /**
     * ตรวจสิทธิ์การนำเข้าไฟล์ xlsfileuploads
     * - บัญชีทั่วไป: ไฟล์ต้องเป็นของ hoscode ตัวเอง และทุกแถวใน Excel ต้องมี hospcode ตรงกับ hoscode ตัวเอง
     * - บัญชี 00064: นำเข้าได้ทุกหน่วยบริการ
     * คืนข้อความผิดพลาด หรือ null ถ้าผ่าน
     */
    protected function checkImportOwnership($xlsdata, array $hospcodes): ?string
    {
        if (empty($xlsdata)) {
            return 'ไม่พบไฟล์ที่ต้องการนำเข้า';
        }
        if ($this->isProvincialAdmin()) {
            return null;
        }
        $mine = trim((string)Yii::$app->session->get('hoscode'));
        if ($mine === '' || trim((string)($xlsdata['hoscode'] ?? '')) !== $mine) {
            return 'ไม่มีสิทธิ์นำเข้าไฟล์นี้';
        }
        $norm = function ($v) {
            $v = trim((string)$v);
            // Excel อาจอ่านรหัสเป็นตัวเลข (9113) จึงเติม 0 ให้ครบ 5 หลักก่อนเทียบ
            return ctype_digit($v) ? str_pad($v, 5, '0', STR_PAD_LEFT) : $v;
        };
        foreach (array_keys($hospcodes) as $hc) {
            if ($norm($hc) !== $norm($mine)) {
                return 'ไม่นำเข้าข้อมูล: พบรหัสหน่วยบริการ ' . trim((string)$hc)
                    . ' ในไฟล์ ซึ่งไม่ตรงกับหน่วยบริการของท่าน (' . $mine . ')';
            }
        }
        return null;
    }

    /**
     * แปลงค่าวันที่จาก Excel (serial number หรือข้อความ) เป็น Y-m-d
     * คืน null ถ้าว่าง อ่านไม่ได้ หรืออยู่นอกช่วงปีที่สมเหตุสมผล
     * ปี พ.ศ. (>= 2400) จะถูกแปลงเป็น ค.ศ.
     */
    protected function parseExcelDate($raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (is_numeric($raw) && $raw > 1000) {
            $ts = ExcelDate::excelToTimestamp((float)$raw);
        } else {
            $str = trim((string)$raw);
            if ($str === '') {
                return null;
            }
            $ts = strtotime($str);
            if ($ts === false) {
                return null;
            }
        }
        $year = (int)date('Y', $ts);
        if ($year >= 2400) {
            $ts   = strtotime('-543 years', $ts);
            $year = (int)date('Y', $ts);
        }
        if ($year < 1990 || $year > (int)date('Y') + 1) {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    /**
     * เลือก FundHolderID จาก period ที่ตรงกับวันบริการ (ใช้ร่วมกันทั้ง OP และ IP)
     * เลือกตัวแรกที่ไม่ใช่ 10739/10740 ถ้าไม่มีให้ใช้ตัวแรกของรายการ
     */
    protected function pickFundHolderId(array $matched): string
    {
        foreach ($matched as $m) {
            $fhid = trim((string)$m['FundHolderID']);
            if ($fhid !== '' && !in_array($fhid, self::DEPRIORITIZED_FUND_HOLDERS, true)) {
                return $fhid;
            }
        }
        $first = trim((string)($matched[0]['FundHolderID'] ?? ''));
        return $first === '' ? 'ccccc' : $first;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!in_array($action->id, $this->getPublicActionIds(), true)) {
            $session = Yii::$app->session;

            // ยังไม่ได้ล็อกอิน
            if (empty($session['user'])) {
                $session->open();
                $session->setFlash('error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }

            // Session หมดอายุ (ตรวจสอบด้วย login_time ที่เก็บเอง)
            // ถ้าไม่มี login_time ถือว่าหมดอายุ (ไม่ใช่ข้ามการตรวจสอบ)
            $loginTime = $session->get('login_time');
            if ($loginTime === null || (time() - (int)$loginTime) > 14400) {
                $session->destroy();
                $session->open();
                $session->setFlash('error', 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }
        }

        return parent::beforeAction($action);
    }
}
