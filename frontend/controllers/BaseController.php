<?php

namespace frontend\controllers;

use Yii;

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
            $loginTime = $session->get('login_time');
            if ($loginTime !== null && (time() - $loginTime) > 14400) {
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
