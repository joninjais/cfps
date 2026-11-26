<?php

namespace frontend\components;

use Yii;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

/**
 * คอมโพเนนต์กลางสำหรับจัดการสิทธิ์ผู้ใช้งาน (RBAC) และจำกัดขอบเขตการเข้าถึงข้อมูลตามรหัสหน่วยบริการ
 * อ้างอิงโครงสร้างเครือข่ายบริการสุขภาพ (provcode, cupcode, hospcode) จากตาราง chospital2
 */
class FacilityAccess extends \yii\base\Component
{
    /**
     * ค้นหาชื่อสถานพยาบาล (hosname) จากรหัสหน่วยบริการ 5 หลัก
     * คืนค่าเป็นข้อความชื่อหน่วยบริการ หรือ null หากไม่พบข้อมูล
     */
    public function facilityName(string $hoscode): ?string
    {
        $name = (new Query())
            ->select('hosname')
            ->from('chospital2')
            ->where(['hospcode' => trim($hoscode)])
            ->scalar();

        return is_scalar($name) && trim((string)$name) !== '' ? trim((string)$name) : null;
    }

    /**
     * ตรวจสอบว่าผู้ใช้ปัจจุบันเป็นผู้ดูแลระดับจังหวัดหรือไม่
     * เป็นจริงเมื่อมีบทบาท provincialAdmin หรือเป็น userManager ที่สังกัด สสจ. (รหัส 00064)
     */
    public function isProvincialAdmin(): bool
    {
        if (Yii::$app->user->can('provincialAdmin')) {
            return true;
        }

        // userManager ที่สังกัดรหัส 00064 เห็นข้อมูลระดับจังหวัดเท่า สสจ.
        $identity = Yii::$app->user->identity;
        return Yii::$app->user->can('userManager')
            && trim((string)($identity->hoscode ?? '')) === '00064';
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์จัดการบัญชีผู้ใช้งาน (userManager) หรือไม่
     */
    public function isUserManager(): bool
    {
        return Yii::$app->user->can('userManager');
    }

    /**
     * ตรวจสอบบทบาทหรือสิทธิ์ตามชื่อที่ระบุผ่านระบบ RBAC ของ Yii
     */
    public function hasRole(string $role): bool
    {
        return Yii::$app->user->can($role);
    }

    /**
     * คำนวณบทบาทมาตรฐานที่บัญชีควรได้รับตามข้อมูลจริงในตาราง chospital2
     * - ต้องอยู่ในจังหวัดเดียวกับรหัส 00064 เท่านั้น (ไม่อยู่ในจังหวัดคืนค่า null)
     * - รหัส 00064 -> provincialAdmin
     * - รพ.แม่ข่าย (cupcode ตรงกับ hospcode) -> hospitalStaff
     * - รพ.สต.ลูกข่าย (cupcode ต่างจาก hospcode) -> pcuStaff
     */
    public function expectedStaffRole(string $hoscode): ?string
    {
        $facility = (new Query())
            ->select(['hospcode', 'cupcode', 'provcode'])
            ->from('chospital2')
            ->where(['hospcode' => trim($hoscode)])
            ->one();

        // ไม่พบรหัสสถานพยาบาลในระบบ
        if ($facility === false) {
            return null;
        }

        $facilityCode = trim((string)$facility['hospcode']);
        $cupCode = trim((string)$facility['cupcode']);
        $facilityProvince = trim((string)$facility['provcode']);

        // ดึงรหัสจังหวัดหลักของระบบจากรหัสหน่วยบริการ 00064 (สสจ.)
        $applicationProvince = (new Query())
            ->select('provcode')
            ->from('chospital2')
            ->where(['hospcode' => '00064'])
            ->scalar();

        // ปฏิเสธหากข้อมูลโครงสร้างไม่ครบ หรือสถานพยาบาลอยู่นอกจังหวัดของระบบ
        if ($facilityCode === '' || $cupCode === '' || $facilityProvince === ''
            || !is_scalar($applicationProvince)
            || trim((string)$applicationProvince) !== $facilityProvince) {
            return null;
        }

        if ($facilityCode === '00064') {
            return 'provincialAdmin';
        }

        // แยกบทบาทระหว่างโรงพยาบาลแม่ข่าย (CUP) กับหน่วยบริการปฐมภูมิลูกข่าย
        return $cupCode === $facilityCode ? 'hospitalStaff' : 'pcuStaff';
    }

    /**
     * คืนค่ารายการรหัสหน่วยบริการทั้งหมดที่ผู้มีสิทธิ์จัดการผู้ใช้สามารถดูแลบัญชีได้
     * กรณีเป็น userManager ที่ได้รับมอบหมาย (ไม่ใช่แอดมินจังหวัด) จะบังคับล็อกกับจังหวัดของ 00064 เสมอ
     */
    public function userManagementFacilityCodes(): array
    {
        return $this->facilityCodesInProvince(!$this->isProvincialAdmin() && $this->isUserManager());
    }

    /**
     * ตรวจสอบว่าผู้ใช้ปัจจุบันสามารถอนุมัติหรือแก้ไขบัญชีผู้ใช้ของรหัสหน่วยบริการที่ระบุได้หรือไม่
     */
    public function canManageUsersAtFacility(string $hoscode): bool
    {
        return in_array(trim($hoscode), $this->userManagementFacilityCodes(), true);
    }

    /**
     * ดึงรหัสหน่วยบริการทั้งหมดที่อยู่ในจังหวัดเดียวกับผู้ดูแลระบบ
     */
    public function provincialFacilityCodes(): array
    {
        return $this->facilityCodesInProvince(false);
    }

    /**
     * ดึงรายการรหัสหน่วยบริการ (hospcode) ทั้งหมดภายในจังหวัดเดียวกันจากตาราง chospital2
     * @param bool $useApplicationProvince หากเป็น true จะยึดจังหวัดของรหัส 00064 เป็นหลัก
     * @throws ForbiddenHttpException หากระบุรหัสจังหวัดไม่ได้ หรือรหัสหน่วยบริการของผู้ดูแลอยู่นอกจังหวัด
     */
    private function facilityCodesInProvince(bool $useApplicationProvince): array
    {
        $identity = Yii::$app->user->identity;
        $adminCode = trim((string)($identity->hoscode ?? Yii::$app->session->get('hoscode')));

        // เลือกรหัสตั้งต้นสำหรับค้นหาจังหวัด (00064 หรือรหัสหน่วยบริการของผู้ใช้เอง)
        $provinceLookupCode = $useApplicationProvince ? '00064' : $adminCode;
        $provinceCode = (new Query())
            ->select('provcode')
            ->from('chospital2')
            ->where(['hospcode' => $provinceLookupCode])
            ->scalar();

        if ($adminCode === '' || !is_scalar($provinceCode) || trim((string)$provinceCode) === '') {
            Yii::error('ไม่พบ provcode สำหรับบัญชีผู้ดูแล ' . $adminCode, __METHOD__);
            throw new ForbiddenHttpException('ไม่สามารถระบุขอบเขตจังหวัดของผู้ดูแลได้');
        }

        // ดึงรหัสหน่วยบริการทั้งหมดที่สังกัดรหัสจังหวัดเดียวกัน
        $codes = (new Query())
            ->select('hospcode')
            ->distinct()
            ->from('chospital2')
            ->where(['provcode' => trim((string)$provinceCode)])
            ->column();

        // ตัดช่องว่าง กรองค่าว่าง และตัดรายการซ้ำพร้อมจัดเรียงดัชนีอาร์เรย์ใหม่
        $codes = array_values(array_unique(array_filter(array_map(
            static fn($code) => trim((string)$code),
            $codes
        ))));

        // ป้องกันกรณี userManager มีรหัสหน่วยบริการตนเองหลุดออกไปนอกจังหวัดของ 00064
        if ($useApplicationProvince && !in_array($adminCode, $codes, true)) {
            throw new ForbiddenHttpException('รหัสหน่วยบริการของผู้ดูแลอยู่นอกจังหวัดที่กำหนด');
        }

        // รับประกันว่ารหัสหน่วยบริการของตัวผู้ดูแลเองรวมอยู่ในรายการเสมอ
        if (!in_array($adminCode, $codes, true)) {
            $codes[] = $adminCode;
        }

        return $codes;
    }

    /**
     * บังคับตรวจสอบ Permission ที่ระบุ หากไม่มีสิทธิ์จะหยุดทำงานและโยน HTTP 403 ทันที
     */
    public function requirePermission(string $permission): void
    {
        if (!Yii::$app->user->can($permission)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งานส่วนนี้');
        }
    }

    /**
     * บังคับว่าผู้ใช้ต้องมีบทบาทอย่างน้อย 1 บทบาทจากรายการที่กำหนด มิฉะนั้นโยน HTTP 403
     */
    public function requireAnyRole(array $roles): void
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return;
            }
        }

        throw new ForbiddenHttpException('บัญชีนี้ยังไม่ได้รับสิทธิ์เข้าใช้งานระบบ');
    }

    /**
     * ตรวจสอบความถูกต้องว่าบทบาท RBAC ของผู้ใช้งานตรงกับระดับสถานพยาบาลจริงใน chospital2
     * ป้องกันปัญหาการเปลี่ยนรหัสหน่วยบริการแล้วสิทธิ์เก่าค้างผิดประเภท
     */
    public function requireFacilityRoleMatches(): void
    {
        // ผู้ดูแลระดับจังหวัดผ่านได้ทันที
        if ($this->isProvincialAdmin()) {
            return;
        }

        $identity = Yii::$app->user->identity;
        if ($this->isUserManager()) {
            // ตรวจยืนยันว่า userManager อยู่ในขอบเขตจังหวัดที่ถูกต้อง
            $this->userManagementFacilityCodes();
            if (trim((string)($identity->hoscode ?? '')) === '00064') {
                return;
            }
        }

        // เทียบสิทธิ์ที่ถืออยู่จริงกับสิทธิ์ที่ควรจะเป็นตามโครงสร้าง CUP
        $expectedRole = $this->expectedStaffRole((string)($identity->hoscode ?? ''));
        if ($expectedRole === null || !$this->hasRole($expectedRole)) {
            throw new ForbiddenHttpException('ประเภทสิทธิ์ไม่ตรงกับข้อมูลหน่วยบริการ');
        }
    }

    /**
     * คืนค่ารายการรหัสหน่วยบริการที่ผู้ใช้งานทั่วไปมองเห็นหรือแก้ไขได้
     * - แอดมินจังหวัด: คืนอาร์เรย์ว่าง [] (ให้ไปใช้ provincialFacilityCodes แทนใน scopeQuery/canAccessFacility)
     * - โหมดเขียนข้อมูล ($forWrite = true) หรือไม่ใช่ hospitalStaff (เช่น pcuStaff): คืนเฉพาะรหัสหน่วยตนเอง [$ownCode]
     * - โหมดอ่านข้อมูล ($forWrite = false) ของ hospitalStaff: คืนรหัส รพ.แม่ข่าย และ รพ.สต.ลูกข่ายใน cupcode เดียวกัน
     */
    public function visibleFacilityCodes(bool $forWrite = false): array
    {
        if ($this->isProvincialAdmin()) {
            return [];
        }

        $identity = Yii::$app->user->identity;
        $ownCode = trim((string)($identity->hoscode ?? Yii::$app->session->get('hoscode')));
        if ($ownCode === '') {
            throw new ForbiddenHttpException('ไม่พบรหัสหน่วยบริการของบัญชี');
        }

        // หากเป็นการเพิ่ม/ลบ/แก้ไขข้อมูล หรือเป็นเจ้าหน้าที่ รพ.สต. ให้เข้าถึงได้เฉพาะหน่วยบริการตนเองเท่านั้น
        if ($forWrite || !$this->hasRole('hospitalStaff')) {
            return [$ownCode];
        }

        // กรณีเจ้าหน้าที่ รพ.แม่ข่าย (โหมดอ่านข้อมูล): ค้นหา cupcode และ provcode ของตนเอง
        $facility = (new Query())
            ->select(['cupcode', 'provcode'])
            ->from('chospital2')
            ->where(['hospcode' => $ownCode])
            ->one();
        $cupcode = trim((string)($facility['cupcode'] ?? ''));
        $provinceCode = trim((string)($facility['provcode'] ?? ''));
        if ($cupcode === '' || $provinceCode === '') {
            Yii::error('ไม่พบ cupcode สำหรับ hoscode ' . $ownCode, __METHOD__);
            throw new ForbiddenHttpException('ไม่สามารถระบุเครือข่าย CUP ของหน่วยบริการได้');
        }

        // ดึงรหัสหน่วยบริการทั้งหมดในเครือข่าย CUP และจังหวัดเดียวกัน
        $codes = (new Query())
            ->select('hospcode')
            ->distinct()
            ->from('chospital2')
            ->where(['cupcode' => $cupcode, 'provcode' => $provinceCode])
            ->column();

        $codes = array_values(array_unique(array_filter(array_map(
            static fn($code) => trim((string)$code),
            $codes
        ))));
        if (!in_array($ownCode, $codes, true)) {
            $codes[] = $ownCode;
        }

        return $codes;
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์เข้าถึงข้อมูลของรหัสหน่วยบริการที่ระบุหรือไม่ (แยกตามโหมดอ่าน/เขียน)
     */
    public function canAccessFacility(string $hoscode, bool $forWrite = false): bool
    {
        if ($this->isProvincialAdmin()) {
            return in_array(trim($hoscode), $this->provincialFacilityCodes(), true);
        }

        return in_array(trim($hoscode), $this->visibleFacilityCodes($forWrite), true);
    }

    /**
     * กรอง Query อัตโนมัติด้วยเงื่อนไขรหัสหน่วยบริการตามระดับสิทธิ์ของผู้ใช้ปัจจุบัน
     * @param \yii\db\QueryInterface $query ออบเจกต์คิวรี่ที่ต้องการกรองขอบเขต
     * @param string $column ชื่อคอลัมน์รหัสหน่วยบริการ เช่น 'hospcode' หรือ 'opdata.hospcode'
     * @param bool $forWrite กำหนดเป็น true หากต้องการจำกัดเฉพาะหน่วยบริการที่แก้ไขข้อมูลได้
     */
    public function scopeQuery($query, string $column, bool $forWrite = false): void
    {
        $codes = $this->isProvincialAdmin()
            ? $this->provincialFacilityCodes()
            : $this->visibleFacilityCodes($forWrite);
        $query->andWhere([$column => $codes]);
    }
}
