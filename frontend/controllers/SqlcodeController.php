<?php

namespace frontend\controllers;

use Yii;
use frontend\models\SqlCode;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class SqlcodeController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * ตรวจสอบว่า login อยู่ (ระบบนี้ใช้ session-based auth)
     */
    private function requireLogin(): void
    {
        if (empty(Yii::$app->session->get('user'))) {
            Yii::$app->response->redirect(['/site/login']);
            Yii::$app->end();
        }
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์จัดการ (เฉพาะ hoscode 00064)
     */
    private function isAdmin(): bool
    {
        return Yii::$app->session->get('hoscode') === '00064';
    }

    /**
     * แสดงรายการ SQL ทั้งหมด จัดกลุ่มตาม HIS
     */
    public function actionIndex()
    {
        $this->requireLogin();
        $models = SqlCode::find()
            ->orderBy(['his' => SORT_ASC, 'type' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // จัดกลุ่มตาม HIS
        $grouped = [];
        foreach ($models as $m) {
            $grouped[$m->his][] = $m;
        }

        return $this->render('index', [
            'grouped' => $grouped,
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    /**
     * เพิ่ม SQL ใหม่ (เฉพาะ admin)
     */
    public function actionCreate()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งานส่วนนี้');
        }

        $model = new SqlCode();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มคำสั่ง SQL เรียบร้อยแล้ว');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * แก้ไข SQL (เฉพาะ admin)
     */
    public function actionUpdate(int $id)
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งานส่วนนี้');
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขคำสั่ง SQL เรียบร้อยแล้ว');
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * ลบ SQL (เฉพาะ admin)
     */
    public function actionDelete(int $id)
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งานส่วนนี้');
        }

        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบคำสั่ง SQL เรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * ค้นหา model ด้วย id
     */
    private function findModel(int $id): SqlCode
    {
        $model = SqlCode::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
        }
        return $model;
    }
}

