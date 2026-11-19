<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Xlsfileuploads;
use frontend\models\XlsfileuploadsSearch;
use frontend\models\Ipdata;
use frontend\models\IpdataSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;

/**
 * XlsfileuploadsController implements the CRUD actions for Xlsfileuploads model.
 */
class XlsfileuploadsController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'   => ['POST'],
                    'reimport' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Xlsfileuploads models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new XlsfileuploadsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Reset importstatus to 0 to allow reimport (POST only).
     */
    public function actionReimport($id)
    {
        $id = (int) $id;
        $model = $this->findModel($id, true);
        if ($model->typefileupload === 'fwfhealth') {
            throw new \yii\web\BadRequestHttpException('ไม่รองรับการนำเข้าข้อมูลบัตรประกันสุขภาพแล้ว');
        }
        if ($id > 0 && $model->xlsid) {
            Yii::$app->db->createCommand()
                ->update('xlsfileuploads', ['importstatus' => 0, 'startdatetime' => null, 'enddatetime' => null], ['xlsid' => $id])
                ->execute();
        }
        return $this->redirect(['index']);
    }

    /**
     * Displays a single Xlsfileuploads model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Xlsfileuploads model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    public function actionCreate()
    {
        $model = new Xlsfileuploads();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->typefileupload === 'fwfhealth') {
                $model->addError('typefileupload', 'ไม่รองรับการนำเข้าข้อมูลบัตรประกันสุขภาพแล้ว');
            }
            if (!$this->isProvincialAdmin()) {
                $model->user = (string)Yii::$app->session->get('user');
                $model->hoscode = trim((string)Yii::$app->session->get('hoscode'));
                if (!in_array($model->typefileupload, ['op pp', 'ip', 'insurance'], true)) {
                    $model->addError('typefileupload', 'ไม่มีสิทธิ์อัปโหลดไฟล์ประเภทนี้');
                }
            } elseif (!Yii::$app->facilityAccess->canAccessFacility((string)$model->hoscode, true)) {
                $model->addError('hoscode', 'รหัสหน่วยบริการอยู่นอกขอบเขตจังหวัด');
            }
            $model->exsfile = UploadedFile::getInstance($model, 'exsfile');
            if ($model->hasErrors()) {
                // Keep the submitted values so the form can display validation errors.
            } elseif ($model->exsfile === null) {
                $model->addError('exsfile', 'กรุณาเลือกไฟล์ Excel');
            } elseif ($model->validate()) {
                $uploadedFile = $model->upload($model, 'exsfile');
                if ($uploadedFile === false) {
                    $model->addError('exsfile', 'ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองอีกครั้ง');
                } else {
                    $model->exsfile = $uploadedFile;
                    $model->uploaddatetime = date('Y-m-d H:i:s');
                    if ($model->save(false)) {
                        return $this->redirect(['view', 'id' => $model->xlsid]);
                    }
                    Yii::error('ไม่สามารถบันทึกข้อมูลไฟล์อัปโหลดได้', __METHOD__);
                    $model->addError('exsfile', 'อัปโหลดไฟล์แล้ว แต่ไม่สามารถบันทึกรายการไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ');
                }
            }
        }

        return $this->render('create', ['model' => $model]);
    }
   
    /**
     * Updates an existing Xlsfileuploads model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post())
            && $model->validate(['user', 'hoscode', 'typefileupload'])
        ) {
            if ($model->typefileupload === 'fwfhealth') {
                $model->addError('typefileupload', 'ไม่รองรับการนำเข้าข้อมูลบัตรประกันสุขภาพแล้ว');
            }
            if (!$this->isProvincialAdmin()) {
                $model->user = (string)Yii::$app->session->get('user');
                $model->hoscode = trim((string)Yii::$app->session->get('hoscode'));
                if (!in_array($model->typefileupload, ['op pp', 'ip', 'insurance'], true)) {
                    $model->addError('typefileupload', 'ไม่มีสิทธิ์เลือกไฟล์ประเภทนี้');
                }
            } elseif (!Yii::$app->facilityAccess->canAccessFacility((string)$model->hoscode, true)) {
                $model->addError('hoscode', 'รหัสหน่วยบริการอยู่นอกขอบเขตจังหวัด');
            }
            if (!$model->hasErrors() && $model->save(false, ['user', 'hoscode', 'typefileupload'])) {
                return $this->redirect(['view', 'id' => $model->xlsid]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Xlsfileuploads model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id, true);
        $type = strtolower(trim((string)$model->typefileupload));
        if ($type === 'op') {
            $type = 'op pp'; // ชนิดเดิมของข้อมูลเก่า
        }
        if ($type === 'fwfdata' || $type === 'fwfhealth') {
            Yii::$app->facilityAccess->requirePermission('manageFwfData');
        }

        $fileName = basename((string)$model->exsfile);
        if ($fileName === '' || $fileName !== $model->exsfile) {
            throw new \yii\web\ForbiddenHttpException('ชื่อไฟล์อัปโหลดไม่ถูกต้อง');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $codes = $this->isProvincialAdmin()
                ? Yii::$app->facilityAccess->provincialFacilityCodes()
                : Yii::$app->facilityAccess->visibleFacilityCodes(true);
            $criteria = ['exsfile' => $fileName];
            if ($type === 'ip' || $type === 'op pp') {
                $criteria['hospcode'] = $codes;
            } elseif ($type === 'fwfdata') {
                Yii::$app->facilityAccess->requirePermission('manageFwfData');
                $criteria['FundHolderID'] = $codes;
            } elseif (!in_array($type, ['insurance', 'fwfhealth'], true)) {
                throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์ลบข้อมูลนำเข้าประเภทนี้');
            }

            $table = [
                'ip' => 'ipdata',
                'op pp' => 'opdata',
                'fwfdata' => 'fwfdatabase',
            ][$type] ?? null;
            if ($table !== null) {
                Yii::$app->db->createCommand()->delete($table, $criteria)->execute();
            }
            if (!$model->delete()) {
                throw new \RuntimeException('ไม่สามารถลบรายการอัปโหลดได้');
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            throw $exception;
        }

        $filePath = Yii::getAlias('@webroot/uploads/' . $fileName);
        if (is_file($filePath) && !unlink($filePath)) {
            Yii::error('ไม่สามารถลบไฟล์อัปโหลด ' . $fileName, __METHOD__);
            Yii::$app->session->setFlash('error', 'ลบรายการแล้ว แต่ไม่สามารถลบไฟล์ออกจากระบบได้ กรุณาติดต่อผู้ดูแล');
            return $this->redirect(['index']);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Xlsfileuploads model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Xlsfileuploads the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, bool $forWrite = false)
    {
        if (($model = Xlsfileuploads::findOne($id)) !== null) {
            if (Yii::$app->facilityAccess->canAccessFacility((string)$model->hoscode, $forWrite)) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
