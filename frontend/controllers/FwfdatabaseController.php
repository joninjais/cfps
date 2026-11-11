<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Fwfdatabase;
use frontend\models\FwfdatabaseSearch;
use frontend\models\Xlsfileuploads;
use frontend\models\XlsfileuploadsSearch;
use frontend\models\User;
use frontend\models\Opdata;
use frontend\models\Ipdata;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * FwfdatabaseController implements the CRUD actions for Fwfdatabase model.
 */
class FwfdatabaseController extends BaseController
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
     * Lists all Fwfdatabase models.
     * @return mixed
     */
    public function actionIndex()
    {
        $data = Yii::$app->request->post();
        $del = $data['del'] ?? '';
        $chk = $data['chk'] ?? '';
        if ($del === 'deletedata' || $chk === 'checkdata') {
            Yii::$app->facilityAccess->requirePermission('manageFwfData');
        }

        $searchModel = new FwfdatabaseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $facilityCodes = $this->isProvincialAdmin()
            ? Yii::$app->facilityAccess->provincialFacilityCodes()
            : Yii::$app->facilityAccess->visibleFacilityCodes();
        $facilityCodesSql = $this->facilityCodesSql();

        $session = Yii::$app->session;
        $checklogin = User::findOne(['user'=>$session['user'],]);

        //ลบข้อมูล
        if($del==="deletedata"){

            $query_del = Yii::$app->db->createCommand()->delete(
                'fwfdatabase',
                ['FundHolderID' => $facilityCodes]
            )->execute();
            $result="ลบข้อมูลสำเร็จ";
        }else{
         
            $result="ลบข้อมูลไม่สำเร็จ"; }


        //ปรับปรุงข้อมูล
        if($chk==="checkdata"){

            $db = Yii::$app->db;

            // Step 1: แตก CID comma-separated → temp table ที่มี index (ทำใน PHP)
            $db->createCommand("DROP TEMPORARY TABLE IF EXISTS tmp_fwf_cid")->execute();
            $db->createCommand("
                CREATE TEMPORARY TABLE tmp_fwf_cid (
                    cid        VARCHAR(20)  NOT NULL,
                    FundHolderID    VARCHAR(50),
                    PeriodStartDate DATE,
                    PeriodEndDate   DATE,
                    INDEX idx_cid (cid)
                )
            ")->execute();

            $fwfRows = (new \yii\db\Query())
                ->select(['CID', 'FundHolderID', 'PeriodStartDate', 'PeriodEndDate'])
                ->from('fwfdatabase')
                ->where(['FundHolderID' => $facilityCodes])
                ->all();

            $insertRows = [];
            foreach ($fwfRows as $row) {
                foreach (array_map('trim', explode(',', $row['CID'])) as $cid) {
                    if ($cid !== '') {
                        $insertRows[] = [$cid, $row['FundHolderID'], $row['PeriodStartDate'], $row['PeriodEndDate']];
                    }
                }
            }

            if (!empty($insertRows)) {
                $db->createCommand()->batchInsert(
                    'tmp_fwf_cid',
                    ['cid', 'FundHolderID', 'PeriodStartDate', 'PeriodEndDate'],
                    $insertRows
                )->execute();

                // Step 2: UPDATE โดยใช้ indexed JOIN แทน FIND_IN_SET
                $db->createCommand("
                    UPDATE opdata
                    INNER JOIN tmp_fwf_cid AS fwf
                        ON opdata.cid = fwf.cid
                        AND opdata.dateserv BETWEEN fwf.PeriodStartDate AND fwf.PeriodEndDate
                    SET opdata.FundHolderID = fwf.FundHolderID
                    WHERE (opdata.FundHolderID = 'ccccc' OR opdata.FundHolderID = '')
                    AND opdata.hospcode IN ({$facilityCodesSql})
                    AND LENGTH(opdata.fund) = 5
                    AND LENGTH(opdata.cid) = 13
                    AND opdata.icd10 <> ''
                    AND opdata.icd10 <> '{}'
                ")->execute();

                $db->createCommand("
                    UPDATE ipdata
                    INNER JOIN tmp_fwf_cid AS fwf
                        ON ipdata.cid = fwf.cid
                        AND ipdata.admitdate BETWEEN fwf.PeriodStartDate AND fwf.PeriodEndDate
                    SET ipdata.FundHolderID = fwf.FundHolderID
                    WHERE (ipdata.FundHolderID = 'ccccc' OR ipdata.FundHolderID = '')
                    AND ipdata.hospcode IN ({$facilityCodesSql})
                    AND LENGTH(ipdata.fund) = 5
                    AND LENGTH(ipdata.cid) = 13
                    AND ipdata.icd10 <> ''
                    AND ipdata.icd10 <> '{}'
                ")->execute();
            }

            $db->createCommand("DROP TEMPORARY TABLE IF EXISTS tmp_fwf_cid")->execute();

            }else{
             
                $result="ปรับปรุงข้อมูลไม่สำเร็จ"; }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'result' => $result,
        ]);
    }

    /**
     * Displays a single Fwfdatabase model.
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
     * Creates a new Fwfdatabase model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $model = new Fwfdatabase();

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->facilityAccess->canAccessFacility((string)$model->FundHolderID, true)) {
                throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }
            if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->fwfid]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }    public function actionImport($id)
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');

        $xlsid = (int)Yii::$app->request->post('id', $id);
        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();
        $ownershipError = $this->checkImportOwnership($xlsdata, []);
        if ($ownershipError !== null || ($xlsdata['typefileupload'] ?? null) !== 'fwfdata') {
            Yii::$app->session->addFlash('error', $ownershipError ?? 'ประเภทไฟล์ไม่รองรับการนำเข้าข้อมูล FWF');
            return $this->redirect(['index']);
        }

        $file = Yii::getAlias('@webroot') . '/uploads/' . basename($xlsdata['exsfile']);
        if (!is_file($file)) {
            Yii::$app->session->addFlash('error', 'ไม่พบไฟล์ Excel ในระบบ');
            return $this->redirect(['index']);
        }
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
        $data = [];
        $hospcodes = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row)[0] ?? [];
            if (trim((string)($rowData[0] ?? '')) === '') {
                continue;
            }
            $data[] = [$rowData];
            $facilityCode = trim((string)($rowData[4] ?? ''));
            if ($facilityCode !== '') {
                $hospcodes[$facilityCode] = true;
            }
        }
        if ($data === []) {
            Yii::$app->session->addFlash('error', 'ไม่พบข้อมูลในไฟล์นำเข้า');
            return $this->redirect(['index']);
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

        foreach ($data as $key => $val) {
                    $source_s = $data[$key][0][1];
                    $date_s = new \DateTime($source_s);
                    $sdate = $date_s->format('Y-m-d'); 

                    $source_e = $data[$key][0][2];
                    $date_e = new \DateTime($source_e);
                    $edate = $date_e->format('Y-m-d');

                    $source_regis = $data[$key][0][8];
                    $date_regis = new \DateTime($source_regis);
                    $regisdate_cv = $date_regis->format('Y-m-d');

                    $PeriodID = $data[$key][0][0];
                    $PeriodStartDate = $sdate;  
                    $PeriodEndDate = $edate;
                    $CID = $data[$key][0][3];
                    $FundHolderID = trim((string)$data[$key][0][4]);
                    if (ctype_digit($FundHolderID)) {
                        $FundHolderID = str_pad($FundHolderID, 5, '0', STR_PAD_LEFT);
                    }
                    // $fname = $data[$key][0][5];
                    $fname = $data[$key][0][5] ?? '-';
                    // $lname = $data[$key][0][6];
                    $lname = $data[$key][0][6] ?? '-';
                    $timeregis = $data[$key][0][7];
                    
                    $regisdate = $regisdate_cv;



                    if(!empty($PeriodID)&&!empty($PeriodStartDate)&&!empty($PeriodEndDate)&&!empty($CID)&&!empty($FundHolderID)){ $checkFwfdatabase = Fwfdatabase::findOne([
                        'PeriodID'=>$PeriodID,
                        'CID'=>$CID,
                        'FundHolderID'=>$FundHolderID,
                        'PeriodStartDate'=>$PeriodStartDate,
                        'PeriodEndDate'=>$PeriodEndDate
                    ]);
                    }//ตรวจสอบข้อมูลถ้ายังไม่มีข้อมูลให้เพิ่มข้อมูลไปยังตาราง

                        if(empty($checkFwfdatabase)){
                    Yii::$app->db->createCommand()->insert('fwfdatabase', [
                        'PeriodID' => $PeriodID,
                        #'PeriodStartDate' => $PeriodStartDate,
                        'PeriodStartDate' => date("Y-m-d", strtotime($PeriodStartDate)),
                        #'PeriodEndDate' => $PeriodEndDate,
                        'PeriodEndDate' => date("Y-m-d", strtotime($PeriodEndDate)),
                        'CID' => $CID,
                        'FundHolderID' => $FundHolderID,
                        'fname' => $fname,
                        'lname' => $lname,
                        'timeregis' => $timeregis,
                        'regisdate' => $regisdate,
                        'exsfile' => $xlsdata['exsfile'],
                    ])->execute();
                    }//if
                    else{
                    //ลบข้อมูลเก่า
                    \Yii::$app->db
                    ->createCommand()
                    ->delete('fwfdatabase', [
                        'PeriodID'=>$PeriodID,
                        'CID'=>$CID,
                        'FundHolderID'=>$FundHolderID,
                        'PeriodStartDate'=>$PeriodStartDate,
                        'PeriodEndDate'=>$PeriodEndDate
                        ])
                    ->execute(); 
                    //เพิ่มข้อมูล
                    Yii::$app->db->createCommand()->insert('fwfdatabase', [
                        'PeriodID' => $PeriodID,
                        #'PeriodStartDate' => $PeriodStartDate,
                        'PeriodStartDate' => date("Y-m-d", strtotime($PeriodStartDate)),
                        #'PeriodEndDate' => $PeriodEndDate,
                        'PeriodEndDate' => date("Y-m-d", strtotime($PeriodEndDate)),
                        'CID' => $CID,
                        'FundHolderID' => $FundHolderID,
                        'fname' => $fname,
                        'lname' => $lname,
                        'timeregis' => $timeregis,
                        'regisdate' => $regisdate,
                        'exsfile' => $xlsdata['exsfile'],
                    ])->execute();
                    }
                  }//foreach    
                  $endimpdat = date('Y-m-d H:i:s');
                  Yii::$app->db->createCommand()->update(
                      'xlsfileuploads',
                      ['enddatetime' => $endimpdat, 'importstatus' => 1],
                      ['xlsid' => $xlsid]
                  )->execute();
                return $this->redirect(['fwfdatabase/index']);

/*
                return $this->render('import',[
                    'xlsid' => $xlsid,
                    'xlsdata' => $xlsdata,
                ]);
*/
    } 



    /**
     * Updates an existing Fwfdatabase model.
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
            if (!Yii::$app->facilityAccess->canAccessFacility((string)$model->FundHolderID, true)) {
                throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->fwfid]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Fwfdatabase model.
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
     * Finds the Fwfdatabase model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Fwfdatabase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Fwfdatabase::findOne($id)) !== null) {
            if (Yii::$app->facilityAccess->canAccessFacility((string)$model->FundHolderID)) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
