<?php

namespace frontend\components;

use Yii;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class FacilityAccess extends \yii\base\Component
{
    public function facilityName(string $hoscode): ?string
    {
        $name = (new Query())
            ->select('hosname')
            ->from('chospital2')
            ->where(['hospcode' => trim($hoscode)])
            ->scalar();

        return is_scalar($name) && trim((string)$name) !== '' ? trim((string)$name) : null;
    }

    public function isProvincialAdmin(): bool
    {
        return Yii::$app->user->can('provincialAdmin');
    }

    public function isUserManager(): bool
    {
        return Yii::$app->user->can('userManager');
    }

    public function hasRole(string $role): bool
    {
        return Yii::$app->user->can($role);
    }

    public function expectedStaffRole(string $hoscode): ?string
    {
        $facility = (new Query())
            ->select(['hospcode', 'cupcode', 'provcode'])
            ->from('chospital2')
            ->where(['hospcode' => trim($hoscode)])
            ->one();

        if ($facility === false) {
            return null;
        }

        $facilityCode = trim((string)$facility['hospcode']);
        $cupCode = trim((string)$facility['cupcode']);
        $facilityProvince = trim((string)$facility['provcode']);
        $applicationProvince = (new Query())
            ->select('provcode')
            ->from('chospital2')
            ->where(['hospcode' => '00064'])
            ->scalar();
        if ($facilityCode === '' || $cupCode === '' || $facilityProvince === ''
            || !is_scalar($applicationProvince)
            || trim((string)$applicationProvince) !== $facilityProvince) {
            return null;
        }

        if ($facilityCode === '00064') {
            return 'provincialAdmin';
        }

        return $cupCode === $facilityCode ? 'hospitalStaff' : 'pcuStaff';
    }

    /** หน่วยบริการที่ผู้มีสิทธิ์ manageUsers จัดการบัญชีได้ (userManager ผูกกับจังหวัดของ 00064) */
    public function userManagementFacilityCodes(): array
    {
        return $this->facilityCodesInProvince(!$this->isProvincialAdmin() && $this->isUserManager());
    }

    public function canManageUsersAtFacility(string $hoscode): bool
    {
        return in_array(trim($hoscode), $this->userManagementFacilityCodes(), true);
    }

    public function provincialFacilityCodes(): array
    {
        return $this->facilityCodesInProvince(false);
    }

    private function facilityCodesInProvince(bool $useApplicationProvince): array
    {
        $identity = Yii::$app->user->identity;
        $adminCode = trim((string)($identity->hoscode ?? Yii::$app->session->get('hoscode')));
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

        $codes = (new Query())
            ->select('hospcode')
            ->distinct()
            ->from('chospital2')
            ->where(['provcode' => trim((string)$provinceCode)])
            ->column();

        $codes = array_values(array_unique(array_filter(array_map(
            static fn($code) => trim((string)$code),
            $codes
        ))));
        if ($useApplicationProvince && !in_array($adminCode, $codes, true)) {
            throw new ForbiddenHttpException('รหัสหน่วยบริการของผู้ดูแลอยู่นอกจังหวัดที่กำหนด');
        }
        if (!in_array($adminCode, $codes, true)) {
            $codes[] = $adminCode;
        }

        return $codes;
    }

    public function requirePermission(string $permission): void
    {
        if (!Yii::$app->user->can($permission)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งานส่วนนี้');
        }
    }

    public function requireAnyRole(array $roles): void
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return;
            }
        }

        throw new ForbiddenHttpException('บัญชีนี้ยังไม่ได้รับสิทธิ์เข้าใช้งานระบบ');
    }

    public function requireFacilityRoleMatches(): void
    {
        if ($this->isProvincialAdmin()) {
            return;
        }

        $identity = Yii::$app->user->identity;
        if ($this->isUserManager()) {
            $this->userManagementFacilityCodes();
        }

        $expectedRole = $this->expectedStaffRole((string)($identity->hoscode ?? ''));
        if ($expectedRole === null || !$this->hasRole($expectedRole)) {
            throw new ForbiddenHttpException('ประเภทสิทธิ์ไม่ตรงกับข้อมูลหน่วยบริการ');
        }
    }

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

        if ($forWrite || !$this->hasRole('hospitalStaff')) {
            return [$ownCode];
        }

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

    public function canAccessFacility(string $hoscode, bool $forWrite = false): bool
    {
        if ($this->isProvincialAdmin()) {
            return in_array(trim($hoscode), $this->provincialFacilityCodes(), true);
        }

        return in_array(trim($hoscode), $this->visibleFacilityCodes($forWrite), true);
    }

    public function scopeQuery($query, string $column, bool $forWrite = false): void
    {
        $codes = $this->isProvincialAdmin()
            ? $this->provincialFacilityCodes()
            : $this->visibleFacilityCodes($forWrite);
        $query->andWhere([$column => $codes]);
    }
}
