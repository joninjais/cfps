<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use frontend\models\UserSearch;
use frontend\models\RegistrationWindowForm;
use frontend\components\RegistrationWindow;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\base\InvalidArgumentException;
use Throwable;

/**
 * UserController — ระบบจัดการผู้ใช้งานสำหรับผู้ดูแลระดับจังหวัด
 */
class UserController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                    'toggle-admin' => ['POST'],
                    'registration-close' => ['POST'],
                ],
            ],
        ];
    }

    public function actionRegistration()
    {
        $model = new RegistrationWindowForm();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
            RegistrationWindow::open($model->minutes());
            Yii::$app->session->setFlash('success', 'เปิดรับสมัครแล้ว ลิงก์ของรอบก่อนหน้าใช้ไม่ได้');
            return $this->redirect(['registration']);
        }
        return $this->render('registration', [
            'model' => $model,
            'window' => RegistrationWindow::current(),
        ]);
    }

    public function actionRegistrationClose()
    {
        RegistrationWindow::close();
        Yii::$app->session->setFlash('success', 'ปิดรับสมัครแล้ว');
        return $this->redirect(['registration']);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        Yii::$app->facilityAccess->requirePermission('manageUsers');
        return true;
    }

    /** -------------------------------- INDEX -------------------------------- */
    public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $roleLabels = [];
        $models = $dataProvider->getModels();
        $adminUserIds = [];
        $userManagerIds = [];
        $hoscodes = array_values(array_unique(array_filter(array_map(
            static fn($user) => trim((string)$user->hoscode),
            $models
        ))));
        $facilityRows = $hoscodes === [] ? [] : (new \yii\db\Query())
            ->select(['hospcode', 'hosname'])
            ->from('chospital2')
            ->where(['hospcode' => $hoscodes])
            ->indexBy('hospcode')
            ->all();
        $facilityNames = array_map(
            static fn($facility) => $facility['hosname'],
            $facilityRows
        );
        foreach ($models as $user) {
            $roles = Yii::$app->authManager->getRolesByUser((string)$user->userid);
            unset($roles['userManager']);
            $role = reset($roles);
            $roleLabels[$user->userid] = $this->roleLabel($role === false ? null : $role->name);
            $isProvincialAdmin = Yii::$app->authManager->getAssignment('provincialAdmin', (string)$user->userid) !== null;
            $isUserManager = Yii::$app->authManager->getAssignment('userManager', (string)$user->userid) !== null;
            if ($isProvincialAdmin || $isUserManager) {
                $adminUserIds[] = (int)$user->userid;
            }
            if ($isUserManager) {
                $userManagerIds[] = (int)$user->userid;
            }
        }

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'roleLabels' => $roleLabels,
            'facilityNames' => $facilityNames,
            'adminUserIds' => $adminUserIds,
            'userManagerIds' => $userManagerIds,
            'canManageAdminRoles' => $this->isDesignatedAdmin(),
            'registrationWindow' => RegistrationWindow::current(),
        ]);
    }

    /** -------------------------------- CREATE ------------------------------- */
    public function actionCreate()
    {
        $model = new User();
        // newPassword required on create
        $model->scenario = 'default';

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('User', []);
            $model->user        = trim($data['user'] ?? '');
            $model->fname       = trim($data['fname'] ?? '');
            $model->lname       = trim($data['lname'] ?? '');
            $model->hoscode     = $data['hoscode'] ?? '';
            $model->orgname = Yii::$app->facilityAccess->facilityName((string)($data['hoscode'] ?? '')) ?? '';
            $model->phonenumber = $data['phonenumber'] ?? '';
            $model->email       = $data['email'] ?? '';
            $model->requested_role = Yii::$app->facilityAccess->expectedStaffRole((string)$model->hoscode) ?? '';
            $model->approval_status = User::APPROVAL_APPROVED;
            $model->approved_by = Yii::$app->user->id;
            $model->approved_at = time();
            $model->newPassword        = $data['newPassword'] ?? '';
            $model->newPasswordConfirm = $data['newPasswordConfirm'] ?? '';

            if ($model->orgname === '') {
                $model->addError('hoscode', 'ไม่พบชื่อหน่วยบริการจากรหัสสถานพยาบาล');
            }
            if ($model->requested_role === '') {
                $model->addError('hoscode', 'ไม่สามารถระบุประเภทผู้ใช้งานจากรหัสสถานพยาบาลได้');
            } elseif ($model->requested_role === 'provincialAdmin') {
                $model->addError('hoscode', 'บัญชีผู้ดูแลระบบกำหนดได้โดยผู้ดูแลรหัส 00064 เท่านั้น');
            }

            if (empty($model->newPassword)) {
                $model->addError('newPassword', 'กรุณาระบุรหัสผ่าน');
            }

            if (!$model->hasErrors() && $model->validate()) {
                $model->setPassword($model->newPassword);
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if (!$model->save(false)) {
                        throw new \RuntimeException('ไม่สามารถบันทึกบัญชีผู้ใช้ได้');
                    }
                    $this->assignFacilityRole($model);
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'เพิ่มผู้ใช้สำเร็จ');
                    return $this->redirect(['index']);
                } catch (Throwable $exception) {
                    $transaction->rollBack();
                    Yii::error($exception->getMessage(), __METHOD__);
                    $model->addError('requested_role', 'ไม่สามารถกำหนดสิทธิ์ให้ผู้ใช้ได้ กรุณาลองอีกครั้ง');
                }
            }
        }

        $facilityName = Yii::$app->facilityAccess->facilityName((string)$model->hoscode);
        $model->orgname = $facilityName ?? '';
        return $this->render('create', ['model' => $model, 'facilityName' => $facilityName]);
    }

    /** -------------------------------- UPDATE ------------------------------- */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->assertCanManageTarget($model);
        $isProvincialAdminAccount = Yii::$app->authManager->getAssignment(
            'provincialAdmin',
            (string)$model->userid
        ) !== null;
        $isUserManagerAccount = Yii::$app->authManager->getAssignment(
            'userManager',
            (string)$model->userid
        ) !== null;
        $isAdminAccount = $isProvincialAdminAccount;
        $originalHoscode = (string)$model->hoscode;
        $originalRequestedRole = $model->requested_role;
        $isProtectedAdminAccount = $isProvincialAdminAccount && $originalHoscode === '00064';
        $isSelf = (string)$model->userid === (string)Yii::$app->user->id;
        $canChangeFacilityCode = $this->isDesignatedAdmin() || ($isUserManagerAccount && $isSelf);
        if (!$isAdminAccount) {
            $model->requested_role = Yii::$app->facilityAccess->expectedStaffRole($originalHoscode) ?? '';
        }
        $model->orgname = Yii::$app->facilityAccess->facilityName((string)$model->hoscode) ?? '';

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('User', []);
            $model->fname       = trim($data['fname'] ?? $model->fname);
            $model->lname       = trim($data['lname'] ?? $model->lname);
            $model->hoscode = $canChangeFacilityCode
                ? ($data['hoscode'] ?? $model->hoscode)
                : $originalHoscode;
            $model->phonenumber = $data['phonenumber'] ?? $model->phonenumber;
            $model->email       = $data['email'] ?? $model->email;
            $requestedRole = $data['requested_role'] ?? $model->requested_role;
            $adminAccountRoleChanged = false;
            if ($isAdminAccount) {
                $adminAccountRoleChanged = ($isProtectedAdminAccount && (string)$model->hoscode !== $originalHoscode)
                    || ($requestedRole !== $originalRequestedRole
                        && !($originalRequestedRole === null && $requestedRole === ''));
                if ($isProtectedAdminAccount) {
                    $model->hoscode = $originalHoscode;
                }
                $model->requested_role = $originalRequestedRole;
            } else {
                $model->requested_role = Yii::$app->facilityAccess->expectedStaffRole((string)$model->hoscode) ?? '';
            }
            $facilityName = Yii::$app->facilityAccess->facilityName((string)$model->hoscode);
            $model->orgname = $facilityName ?? '';
            $model->newPassword        = $data['newPassword'] ?? '';
            $model->newPasswordConfirm = $data['newPasswordConfirm'] ?? '';

            $isValid = $model->validate(['fname', 'lname', 'hoscode', 'orgname', 'phonenumber', 'email', 'requested_role']);
            if (!empty($model->newPassword)) {
                $isValid = $model->validate(['newPassword', 'newPasswordConfirm']) && $isValid;
            }
            $expectedRole = Yii::$app->facilityAccess->expectedStaffRole((string)$model->hoscode);
            if (!$isAdminAccount) {
                if ($expectedRole === null || $expectedRole === 'provincialAdmin') {
                    $model->addError('requested_role', 'ไม่สามารถระบุประเภทผู้ใช้งานจากรหัสสถานพยาบาลได้');
                    $isValid = false;
                } else {
                    $model->requested_role = $expectedRole;
                }
            } elseif ($isProvincialAdminAccount && !$isProtectedAdminAccount && $expectedRole === null) {
                $model->addError('hoscode', 'รหัสสถานพยาบาลต้องอยู่ในจังหวัดที่รับผิดชอบ');
                $isValid = false;
            }
            if ($facilityName === null) {
                $model->addError('hoscode', 'ไม่พบชื่อหน่วยบริการจากรหัสสถานพยาบาล');
                $isValid = false;
            }
            if ($adminAccountRoleChanged) {
                $model->addError('requested_role', 'ไม่สามารถเปลี่ยนหน่วยบริการหรือประเภทสิทธิ์ของผู้ดูแลจังหวัดได้');
                $isValid = false;
            }

            if ($isValid) {
                if (!empty($model->newPassword)) {
                    $model->setPassword($model->newPassword);
                }
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if (!$model->save(false)) {
                        throw new \RuntimeException('ไม่สามารถบันทึกบัญชีผู้ใช้ได้');
                    }
                    if ($model->approval_status === User::APPROVAL_APPROVED && !$isAdminAccount) {
                        $this->assignFacilityRole($model);
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกสำเร็จ');
                    return $this->redirect(['index']);
                } catch (Throwable $exception) {
                    $transaction->rollBack();
                    Yii::error($exception->getMessage(), __METHOD__);
                    $model->addError('requested_role', 'ไม่สามารถบันทึกการเปลี่ยนแปลงสิทธิ์ได้ กรุณาลองอีกครั้ง');
                }
            }
        }

        $roles = Yii::$app->authManager->getRolesByUser((string)$model->userid);
        $role = reset($roles);

        return $this->render('update', [
            'model' => $model,
            'isProvincialAdminAccount' => $isAdminAccount,
            'isProtectedAdminAccount' => $isProtectedAdminAccount,
            'canChangeFacilityCode' => $canChangeFacilityCode && !$isProtectedAdminAccount,
            'currentRoleLabel' => $this->roleLabel($role === false ? null : $role->name),
            'facilityName' => Yii::$app->facilityAccess->facilityName((string)$model->hoscode),
        ]);
    }

    /** -------------------------------- DELETE ------------------------------- */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->assertCanManageTarget($model);

        // Prevent deleting own account
        if ((string)$model->userid === (string)Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->authManager->revokeAll((string)$model->userid);
            $model->is_deleted = User::DELETED;
            if (!$model->save(false, ['is_deleted'])) {
                throw new \RuntimeException('ไม่สามารถลบบัญชีผู้ใช้ได้');
            }
            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'ลบผู้ใช้ไม่สำเร็จ กรุณาลองอีกครั้ง');
            return $this->redirect(['index']);
        }
        Yii::$app->session->setFlash('success', 'ลบผู้ใช้สำเร็จ');
        return $this->redirect(['index']);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $this->assertCanManageTarget($model);
        $roleName = Yii::$app->request->post('role', $model->requested_role);
        if ($model->approval_status !== User::APPROVAL_PENDING
            || !in_array($roleName, ['hospitalStaff', 'pcuStaff'], true)) {
            throw new ForbiddenHttpException('คำขอหรือประเภทผู้ใช้งานไม่ถูกต้อง');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->requested_role = $roleName;
            $model->approval_status = User::APPROVAL_APPROVED;
            $model->approved_by = Yii::$app->user->id;
            $model->approved_at = time();
            if (!$model->save(false, ['requested_role', 'approval_status', 'approved_by', 'approved_at'])) {
                throw new \RuntimeException('ไม่สามารถอนุมัติบัญชีได้');
            }
            $this->assignFacilityRole($model);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'อนุมัติบัญชีผู้ใช้แล้ว');
        } catch (Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'อนุมัติบัญชีไม่สำเร็จ กรุณาตรวจสอบข้อมูลและลองอีกครั้ง');
        }

        return $this->redirect(['index']);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $this->assertCanManageTarget($model);
        if ($model->approval_status !== User::APPROVAL_PENDING) {
            throw new ForbiddenHttpException('บัญชีนี้ไม่ได้อยู่ในสถานะรออนุมัติ');
        }

        $model->approval_status = User::APPROVAL_REJECTED;
        $model->approved_by = Yii::$app->user->id;
        $model->approved_at = time();
        if (!$model->save(false, ['approval_status', 'approved_by', 'approved_at'])) {
            Yii::error('ไม่สามารถปฏิเสธบัญชี userid=' . $model->userid, __METHOD__);
            Yii::$app->session->setFlash('error', 'ปฏิเสธคำขอไม่สำเร็จ กรุณาลองอีกครั้ง');
        } else {
            Yii::$app->authManager->revokeAll((string)$model->userid);
            Yii::$app->session->setFlash('success', 'ปฏิเสธคำขอสมัครแล้ว');
        }

        return $this->redirect(['index']);
    }

    public function actionToggleAdmin($id)
    {
        if (!$this->isDesignatedAdmin()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแลระบบรหัส 00064 เท่านั้นที่กำหนด role userManager ได้');
        }

        $model = $this->findModel($id);
        $userId = (string)$model->userid;
        if ((string)$model->hoscode === '00064'
            || $model->approval_status !== User::APPROVAL_APPROVED
            || !Yii::$app->facilityAccess->canManageUsersAtFacility((string)$model->hoscode)) {
            throw new ForbiddenHttpException('ไม่สามารถเปลี่ยนสิทธิ์บัญชีนี้ได้');
        }

        $grantValue = Yii::$app->request->post('grant');
        if (!in_array($grantValue, ['0', '1'], true)) {
            throw new ForbiddenHttpException('คำสั่งเปลี่ยนสิทธิ์ไม่ถูกต้อง');
        }
        $grantAdmin = $grantValue === '1';
        $expectedRole = Yii::$app->facilityAccess->expectedStaffRole((string)$model->hoscode);
        if (!in_array($expectedRole, ['hospitalStaff', 'pcuStaff'], true)) {
            throw new ForbiddenHttpException('ไม่สามารถระบุประเภทผู้ใช้จากรหัสสถานพยาบาลได้');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('userManager');
            if ($role === null) {
                throw new \RuntimeException('ไม่พบ role ผู้ดูแลจัดการผู้ใช้');
            }
            $facilityRole = $auth->getRole($expectedRole);
            if ($facilityRole === null) {
                throw new \RuntimeException('ไม่พบ role ตามประเภทหน่วยบริการ');
            }

            $auth->revokeAll($userId);
            $model->requested_role = $expectedRole;
            if (!$model->save(false, ['requested_role'])) {
                throw new \RuntimeException('ไม่สามารถบันทึกประเภทผู้ใช้งานได้');
            }
            if ($auth->assign($facilityRole, $userId) === null
                || ($grantAdmin && $auth->assign($role, $userId) === null)) {
                throw new \RuntimeException('ไม่สามารถกำหนดสิทธิ์ผู้ใช้งานได้');
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', $grantAdmin
                ? 'กำหนด role userManager ให้ผู้ใช้แล้ว'
                : 'ถอน role userManager แล้ว และคืนสิทธิ์ตามรหัสสถานพยาบาล');
        } catch (Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'เปลี่ยนสิทธิ์ผู้ดูแลไม่สำเร็จ กรุณาลองอีกครั้ง');
        }

        return $this->redirect(['index']);
    }

    /** -------------------------------- RESET PW ----------------------------- */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);
        $this->assertCanManageTarget($model);

        if (Yii::$app->request->isPost) {
            $newPass     = Yii::$app->request->post('newPassword', '');
            $confirmPass = Yii::$app->request->post('newPasswordConfirm', '');

            if (strlen($newPass) < 8) {
                Yii::$app->session->setFlash('error', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
            } elseif (!preg_match('/[A-Z]/', $newPass)) {
                Yii::$app->session->setFlash('error', 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว');
            } elseif (!preg_match('/[a-z]/', $newPass)) {
                Yii::$app->session->setFlash('error', 'รหัสผ่านต้องมีตัวพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว');
            } elseif (!preg_match('/[0-9]/', $newPass)) {
                Yii::$app->session->setFlash('error', 'รหัสผ่านต้องมีตัวเลข (0-9) อย่างน้อย 1 ตัว');
            } elseif ($newPass !== $confirmPass) {
                Yii::$app->session->setFlash('error', 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน');
            } else {
                $model->setPassword($newPass);
                $model->must_change_password = 1; // ให้ผู้ใช้ตั้งรหัสใหม่เองเมื่อเข้าสู่ระบบครั้งถัดไป
                $model->save(false, ['pass', 'must_change_password']);
                Yii::$app->session->setFlash('success', 'รีเซ็ตรหัสผ่านสำเร็จ — ผู้ใช้จะต้องตั้งรหัสผ่านใหม่เมื่อเข้าสู่ระบบ');
                return $this->redirect(['index']);
            }
        }

        return $this->render('reset-password', ['model' => $model]);
    }

    /** -------------- Helpers ------------------------------------------------ */
    protected function findModel($id)
    {
        if (($model = User::findOne(['userid' => $id, 'is_deleted' => User::NOT_DELETED])) !== null) {
            if (Yii::$app->facilityAccess->canManageUsersAtFacility((string)$model->hoscode)) {
                return $model;
            }
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลผู้ใช้');
    }

    private function assertCanManageTarget(User $model): void
    {
        if ($this->isDesignatedAdmin() || (string)$model->userid === (string)Yii::$app->user->id) {
            return;
        }

        $auth = Yii::$app->authManager;
        if ((string)$model->hoscode === '00064'
            || $auth->getAssignment('provincialAdmin', (string)$model->userid) !== null
            || $auth->getAssignment('userManager', (string)$model->userid) !== null) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์จัดการบัญชีผู้ดูแลระบบอื่น');
        }
    }

    private function assignFacilityRole(User $model): void
    {
        $expectedRole = Yii::$app->facilityAccess->expectedStaffRole((string)$model->hoscode);
        if (!in_array($expectedRole, ['hospitalStaff', 'pcuStaff'], true)
            || $model->requested_role !== $expectedRole) {
            throw new InvalidArgumentException('ประเภทผู้ใช้ไม่ถูกต้อง');
        }

        $role = Yii::$app->authManager->getRole($model->requested_role);
        if ($role === null) {
            throw new \RuntimeException('ไม่พบ role ที่ต้องการกำหนด');
        }

        $auth = Yii::$app->authManager;
        $keepUserManager = $auth->getAssignment('userManager', (string)$model->userid) !== null;
        $auth->revokeAll((string)$model->userid);
        if ($auth->assign($role, (string)$model->userid) === null) {
            throw new \RuntimeException('ไม่สามารถกำหนด role ให้บัญชีได้');
        }
        $managerRole = $auth->getRole('userManager');
        if ($keepUserManager && ($managerRole === null || $auth->assign($managerRole, (string)$model->userid) === null)) {
            throw new \RuntimeException('ไม่สามารถคงสิทธิ์จัดการผู้ใช้ได้');
        }
    }

    private function roleLabel(?string $roleName): string
    {
        return [
            'provincialAdmin' => 'เจ้าหน้าที่ สสจ.',
            'userManager' => 'ผู้ดูแลจัดการผู้ใช้',
            'hospitalStaff' => 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
            'pcuStaff' => 'เจ้าหน้าที่ รพ.สต.',
        ][$roleName] ?? 'ยังไม่กำหนดบทบาท';
    }

    private function isDesignatedAdmin(): bool
    {
        $identity = Yii::$app->user->identity;

        return $identity !== null
            && (string)$identity->hoscode === '00064'
            && Yii::$app->authManager->getAssignment('provincialAdmin', (string)$identity->userid) !== null;
    }
}
