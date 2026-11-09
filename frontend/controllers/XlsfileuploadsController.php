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
        if ($id > 0) {
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
            $model->exsfile = UploadedFile::getInstance($model, 'exsfile');
            if ($model->exsfile === null) {
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())
            && $model->validate(['user', 'hoscode', 'typefileupload'])
            && $model->save(false, ['user', 'hoscode', 'typefileupload'])) {
            return $this->redirect(['view', 'id' => $model->xlsid]);
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
        

        $xlsid = Yii::$app->request->get();
        $xlsid = $xlsid['id'] ?? '';

        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();
        
            $data = Yii::$app->request->get();
            $del = $data['del'] ?? '';
            //ลบข้อมูล
            if(!empty($xlsid)){
            if($xlsdata['typefileupload']=='ip'){
                $sql = "
                DELETE FROM `ipdata` WHERE `ipdata`.`exsfile` = '".$xlsdata['exsfile']."'
                ";
                $query_del = Yii::$app->db->createCommand($sql)->execute();
            }
            if($xlsdata['typefileupload']=='op pp'){
                $sql = "
                DELETE FROM `opdata` WHERE `opdata`.`exsfile` = '".$xlsdata['exsfile']."'
                ";
                $query_del = Yii::$app->db->createCommand($sql)->execute();
            }
            if($xlsdata['typefileupload']=='fwfhealth'){
                $sql = "
                DELETE FROM `fwfhealth` WHERE `fwfhealth`.`exsfile` = '".$xlsdata['exsfile']."'
                ";
                $query_del = Yii::$app->db->createCommand($sql)->execute();
            }
            if($xlsdata['typefileupload']=='fwfdata'){
                $sql = "
                DELETE FROM `fwfdatabase` WHERE `fwfdatabase`.`exsfile` = '".$xlsdata['exsfile']."'
                ";
                $query_del = Yii::$app->db->createCommand($sql)->execute();
            }    
                $result="ลบข้อมูลสำเร็จ";
            }else{
                $result="ลบข้อมูลไม่สำเร็จ"; 
            }
            
        $delete = Yii::getAlias('@webroot').'/'.'uploads'.'/'.$xlsdata['exsfile'];
        @unlink($delete);

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Xlsfileuploads model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Xlsfileuploads the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Xlsfileuploads::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
