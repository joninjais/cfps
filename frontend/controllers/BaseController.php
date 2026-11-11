<?php

namespace frontend\controllers;

use Yii;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use frontend\models\User;
use yii\web\ForbiddenHttpException;

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

    /** FundHolderID ที่ไม่ใช้เป็นตัวเลือกแรกเมื่อมีหลาย period ซ้อนกัน */
    const DEPRIORITIZED_FUND_HOLDERS = ['10739', '10740'];

    protected function isProvincialAdmin(): bool
    {
        return Yii::$app->facilityAccess->isProvincialAdmin();
    }

    protected function facilityCodesSql(bool $forWrite = false): string
    {
        if ($this->isProvincialAdmin()) {
            $codes = Yii::$app->facilityAccess->provincialFacilityCodes();
        } else {
            $codes = Yii::$app->facilityAccess->visibleFacilityCodes($forWrite);
        }

        if ($codes === []) {
            throw new ForbiddenHttpException('ไม่พบขอบเขตหน่วยบริการที่อนุญาต');
        }

        return implode(', ', array_map(
            static fn($code) => Yii::$app->db->quoteValue($code),
            $codes
        ));
    }

    /**
     * ตรวจสิทธิ์การนำเข้าไฟล์ xlsfileuploads
     * - บัญชีทั่วไป: ไฟล์และทุกแถวต้องอยู่ในหน่วยบริการตนเอง
     * - ผู้ดูแลระบบระดับจังหวัด: ไฟล์และทุกแถวต้องอยู่ในจังหวัดที่รับผิดชอบ
     * คืนข้อความผิดพลาด หรือ null ถ้าผ่าน
     */
    protected function checkImportOwnership($xlsdata, array $hospcodes): ?string
    {
        if (empty($xlsdata)) {
            return 'ไม่พบไฟล์ที่ต้องการนำเข้า';
        }

        $isAdmin = $this->isProvincialAdmin();
        $mine = trim((string)Yii::$app->session->get('hoscode'));
        $allowedCodes = $isAdmin
            ? Yii::$app->facilityAccess->provincialFacilityCodes()
            : [$mine];
        $normalize = static function ($value): string {
            $value = trim((string)$value);
            return ctype_digit($value) ? str_pad($value, 5, '0', STR_PAD_LEFT) : $value;
        };
        $allowedCodes = array_map($normalize, $allowedCodes);

        if ($mine === '' || !in_array($normalize($xlsdata['hoscode'] ?? ''), $allowedCodes, true)) {
            return 'ไม่มีสิทธิ์นำเข้าไฟล์นี้';
        }
        foreach (array_keys($hospcodes) as $hc) {
            if (!in_array($normalize($hc), $allowedCodes, true)) {
                return 'ไม่นำเข้าข้อมูล: พบรหัสหน่วยบริการ ' . trim((string)$hc)
                    . ' ในไฟล์ ซึ่งอยู่นอกขอบเขตหน่วยบริการที่อนุญาต';
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

            $user = User::findIdentity($session->get('userid'));
            if ($user === null || (string)$user->user !== (string)$session->get('user')) {
                $session->destroy();
                $session->open();
                $session->setFlash('error', 'บัญชีนี้ยังไม่ได้รับอนุมัติหรือถูกระงับ กรุณาติดต่อผู้ดูแลระบบ');
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }
            $session->set('hoscode', $user->hoscode);

            if (Yii::$app->user->isGuest && !Yii::$app->user->login($user, 0)) {
                Yii::error('ไม่สามารถกู้คืนสถานะการยืนยันตัวตนของผู้ใช้ ' . $user->userid, __METHOD__);
                throw new ForbiddenHttpException('ไม่สามารถยืนยันตัวตนของบัญชีนี้ได้');
            }

            try {
                Yii::$app->facilityAccess->requireAnyRole(['provincialAdmin', 'userManager', 'hospitalStaff', 'pcuStaff']);
                Yii::$app->facilityAccess->requirePermission('accessApplication');
                Yii::$app->facilityAccess->requireFacilityRoleMatches();
            } catch (ForbiddenHttpException $exception) {
                Yii::$app->session->setFlash('error', $exception->getMessage());
                Yii::$app->user->logout(false);
                $session->destroy();
                $session->open();
                Yii::$app->response->redirect(['/site/login'])->send();
                return false;
            }

            }

            return parent::beforeAction($action);
    }
}
