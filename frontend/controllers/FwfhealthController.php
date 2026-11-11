<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Fwfhealth;
use frontend\models\FwfhealthSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * FwfhealthController implements the CRUD actions for Fwfhealth model.
 */
class FwfhealthController extends BaseController
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
                    'delete' => ['POST'],
                    'import' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Fwfhealth models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FwfhealthSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Fwfhealth model.
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

    public function actionImport($id)
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');

        $xlsid = (int)Yii::$app->request->post('id', $id);

        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();
        $ownershipError = $this->checkImportOwnership($xlsdata, []);
        if ($ownershipError !== null || ($xlsdata['typefileupload'] ?? null) !== 'fwfhealth') {
            Yii::$app->session->addFlash('error', $ownershipError ?? 'ประเภทไฟล์ไม่รองรับการนำเข้าข้อมูลบัตร');
            return $this->redirect(['index']);
        }

        $file = Yii::getAlias('@webroot') . '/uploads/' . basename($xlsdata['exsfile']);
        try {
            $inputFile = IOFactory::identify($file);
            $objReader = IOFactory::createReader($inputFile);
            $objPHPExcel = $objReader->load($file);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->addFlash('error', 'ไม่สามารถอ่านไฟล์นำเข้าได้');
            return $this->redirect(['index']);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $hospcodes = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row)[0] ?? [];
            $hcode = trim((string)($rowData[1] ?? ''));
            if (ctype_digit($hcode)) {
                $hcode = str_pad($hcode, 5, '0', STR_PAD_LEFT);
            }
            if ($hcode !== '') {
                $hospcodes[$hcode] = true;
            }
        }
        $ownershipError = $this->checkImportOwnership($xlsdata, $hospcodes);
        if ($ownershipError !== null) {
            Yii::$app->session->addFlash('error', $ownershipError);
            return $this->redirect(['index']);
        }

        Yii::$app->db->createCommand()->update(
            'xlsfileuploads',
            ['startdatetime' => date('Y-m-d H:i:s')],
            ['xlsid' => $xlsid]
        )->execute();

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row)[0] ?? [];

/*
                    $ad_y = substr($rowData[1],0,4);
                    $ad_m = substr($data[$key][0][1],5,2); 
                    $ad_d = substr($data[$key][0][1],8,2);
                    $addate = ($ad_y-543).'-'.$ad_m.'-'.$ad_d; 
                  
                    $dc_y = substr($data[$key][0][2],0,4);
                    $dc_m = substr($data[$key][0][2],5,2); 
                    $dc_d = substr($data[$key][0][2],8,2);
                    $dcdate = ($dc_y-543).'-'.$dc_m.'-'.$dc_d; 
*/
                    $datev = $rowData[0];
                    $hcode = trim((string)($rowData[1] ?? ''));
                    if (ctype_digit($hcode)) {
                        $hcode = str_pad($hcode, 5, '0', STR_PAD_LEFT);
                    }
                    $cid = $rowData[2];
                    $prefix = $rowData[3];
                    $fname = $rowData[4];
                    $lname = $rowData[5];
                    $birthday = $rowData[6];
                    $nation = $rowData[7];
                    $type_mg = $rowData[8];
                    $sex = $rowData[9];
                    $address = $rowData[10];
                    $company = $rowData[11];
                    $bossname = $rowData[12];
                    $telephone = $rowData[13];
                    $hn = $rowData[14];
                    $vn = $rowData[15];
                    $card_id = $rowData[16];
                    $typehealth_id = $rowData[18];
                    $diesease_id = $rowData[19];



                    if(!empty($cid)&&!empty($hn)&&!empty($datev)){
                        if(strlen($cid)==13){
                        $datev= date("Y-m-d", strtotime($datev));

                        $exists = (new \yii\db\Query())
                            ->from('fwfhealth')
                            ->where([
                                'cid' => $cid,
                                'hn' => $hn,
                                'datev' => $datev,
                                'vn' => $vn,
                            ])
                            ->exists();
    
                        if (!$exists) {
                        
                        Yii::$app->db->createCommand()->insert('fwfhealth', [
                            'datev' => date("Y-m-d", strtotime($datev)),
                            'hcode' => $hcode,                            
                            'cid' => $cid,
                            'prefix' => $prefix,
                            'fname' => $fname,
                            'lname' => $lname,
                            'birthday' => date("Y-m-d", strtotime($birthday)),
                            'nation' => $nation,
                            'type_mg' => $type_mg,
                            'sex' => $sex,
                            'address' => $address,
                            'company' => $company,
                            'bossname' => $bossname,
                            'tel' => $telephone,
                            'hn' => $hn,
                            'vn' => $vn,
                            'card_id' => $card_id,                        
                            'typehealth_id' => $typehealth_id,
                            'diesease_id' => $diesease_id,
                            'exsfile' => $xlsdata['exsfile'],
                        ])->execute();
    
                        } //if(empty($querycheckfwf[0]['cid'])&&($querycheckfwf[0]['hn'])&&($querycheckfwf[0]['datev'])){

                    
                    }//  if(strlen($cid)==13){
                    else{ $hosb="ccccc"; } 
                    } //if $cid


        }
        Yii::$app->db->createCommand()->update(
            'xlsfileuploads',
            ['enddatetime' => date('Y-m-d H:i:s'), 'importstatus' => 1],
            ['xlsid' => $xlsid]
        )->execute();
                return $this->redirect(['fwfhealth/index']);

/*
                return $this->render('import',[
                    'xlsid' => $xlsid,
                    'xlsdata' => $xlsdata,
                ]);
*/
    } 

    /**
     * Creates a new Fwfhealth model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $model = new Fwfhealth();

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->facilityAccess->canAccessFacility((string)$model->hcode, true)) {
                throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->fwfhealth_id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Fwfhealth model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->facilityAccess->canAccessFacility((string)$model->hcode, true)) {
                throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->fwfhealth_id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Fwfhealth model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Fwfhealth model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Fwfhealth the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Fwfhealth::findOne($id)) !== null) {
            if (Yii::$app->facilityAccess->canAccessFacility((string)$model->hcode)) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
