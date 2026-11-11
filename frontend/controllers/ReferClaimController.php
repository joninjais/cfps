<?php

namespace frontend\controllers;

use Yii;
use frontend\models\ReferClaim;
use frontend\models\ReferClaimSearch;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * ReferClaimController — บันทึก/แก้ไข/ลบ ข้อมูลตาราง refer_claim
 * ใช้ได้เฉพาะผู้ใช้ที่ hoscode = 00064 (สสจ.)
 */
class ReferClaimController extends BaseController
{
    const ADMIN_HOSCODE = '00064';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => ['delete' => ['POST'], 'approve' => ['POST'], 'note' => ['POST']],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->session->get('hoscode') !== self::ADMIN_HOSCODE) {
            Yii::$app->session->setFlash('error', 'เฉพาะผู้ใช้ สสจ. (00064) เท่านั้นที่มีสิทธิ์ใช้งานส่วนนี้');
            Yii::$app->response->redirect(['/site/index'])->send();
            return false;
        }

        return true;
    }

    public function actionIndex()
    {
        // จำ URL (รวมตัวกรอง/เลขหน้า) เพื่อกลับมาหลังเปลี่ยนสถานะอนุมัติจากหน้ารายการ
        Url::remember(Yii::$app->request->url, 'refer-claim-index');

        $searchModel  = new ReferClaimSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'hospitals'    => $this->hospitalList(),
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model'     => $this->findModel($id),
            'hospitals' => $this->hospitalList(),
        ]);
    }

    public function actionCreate()
    {
        $model = new ReferClaim();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลสำเร็จ');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model'     => $model,
            'hospitals' => $this->hospitalList(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลสำเร็จ');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model'     => $model,
            'hospitals' => $this->hospitalList(),
        ]);
    }

    /**
     * บันทึกผลการอนุมัติ จ่าย/ปฏิเสธ (status ว่าง = ล้างผลอนุมัติ)
     */
    public function actionApprove($id)
    {
        $model  = $this->findModel($id);
        $status = trim((string)Yii::$app->request->post('status', ''));

        if (!$model->setApproval($status)) {
            Yii::$app->session->setFlash('error', 'สถานะการอนุมัติไม่ถูกต้อง');
        } elseif ($model->save(false)) {
            Yii::$app->session->setFlash('success', $status === '' ? 'ล้างผลการอนุมัติแล้ว' : 'บันทึกผลการอนุมัติ "' . $status . '" แล้ว');
        }

        // เปลี่ยนจากหน้า index → กลับ index เดิม (ใช้ URL ที่จำไว้ในเซสชัน ไม่รับ URL จากผู้ใช้)
        if (Yii::$app->request->post('from') === 'index') {
            return $this->redirect(Url::previous('refer-claim-index') ?: ['index']);
        }

        return $this->redirect(['view', 'id' => $model->rcid]);
    }

    public function actionNote($id)
    {
        $model = $this->findModel($id);
        $model->note = trim((string)Yii::$app->request->post('note', '')) ?: null;

        if (!$model->validate(['note'])) {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        } elseif ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'บันทึกหมายเหตุแล้ว');
        } else {
            Yii::error('ไม่สามารถบันทึกหมายเหตุของ refer_claim rcid=' . $model->rcid, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกหมายเหตุไม่สำเร็จ กรุณาลองอีกครั้ง');
        }

        if (Yii::$app->request->post('from') === 'index') {
            return $this->redirect(Url::previous('refer-claim-index') ?: ['index']);
        }

        return $this->redirect(['view', 'id' => $model->rcid]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบข้อมูลสำเร็จ');
        return $this->redirect(['index']);
    }

    /**
     * รายชื่อหน่วยบริการ [hospcode => "hospcode hosname"] สำหรับ dropdown
     */
    private function hospitalList()
    {
        $rows = Yii::$app->db->createCommand(
            'SELECT hospcode, hosname FROM chospital2 ORDER BY hospcode'
        )->queryAll();

        $list = [];
        foreach ($rows as $r) {
            $list[$r['hospcode']] = $r['hospcode'] . ' ' . $r['hosname'];
        }
        return $list;
    }

    protected function findModel($id)
    {
        if (($model = ReferClaim::findOne((int)$id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
    }
}
