<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use frontend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController — ระบบจัดการผู้ใช้งาน (เฉพาะ admin hoscode=00064)
 */
class UserController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $session = Yii::$app->session;
                            return !empty($session['hoscode']) && $session['hoscode'] === '00064';
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->session->setFlash('error', 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้');
                    return $action->controller->redirect(['/site/index']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /** -------------------------------- INDEX -------------------------------- */
    public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
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
            $model->orgname     = $data['orgname'] ?? '';
            $model->phonenumber = $data['phonenumber'] ?? '';
            $model->email       = $data['email'] ?? '';
            $model->newPassword        = $data['newPassword'] ?? '';
            $model->newPasswordConfirm = $data['newPasswordConfirm'] ?? '';

            if (empty($model->newPassword)) {
                $model->addError('newPassword', 'กรุณาระบุรหัสผ่าน');
            }

            if (!$model->hasErrors() && $model->validate()) {
                $model->setPassword($model->newPassword);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'เพิ่มผู้ใช้สำเร็จ');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /** -------------------------------- UPDATE ------------------------------- */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('User', []);
            $model->fname       = trim($data['fname'] ?? $model->fname);
            $model->lname       = trim($data['lname'] ?? $model->lname);
            $model->hoscode     = $data['hoscode'] ?? $model->hoscode;
            $model->orgname     = $data['orgname'] ?? $model->orgname;
            $model->phonenumber = $data['phonenumber'] ?? $model->phonenumber;
            $model->email       = $data['email'] ?? $model->email;
            $model->newPassword        = $data['newPassword'] ?? '';
            $model->newPasswordConfirm = $data['newPasswordConfirm'] ?? '';

            $isValid = $model->validate(['fname', 'lname', 'hoscode', 'orgname', 'phonenumber', 'email']);
            if (!empty($model->newPassword)) {
                $isValid = $model->validate(['newPassword', 'newPasswordConfirm']) && $isValid;
            }

            if ($isValid) {
                if (!empty($model->newPassword)) {
                    $model->setPassword($model->newPassword);
                }
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'บันทึกสำเร็จ');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /** -------------------------------- DELETE ------------------------------- */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Prevent deleting own account
        $session = Yii::$app->session;
        if ($model->user === $session['user']) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบผู้ใช้สำเร็จ');
        return $this->redirect(['index']);
    }

    /** -------------------------------- RESET PW ----------------------------- */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);

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
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลผู้ใช้');
    }
}
