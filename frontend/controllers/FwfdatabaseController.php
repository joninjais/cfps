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
        $searchModel = new FwfdatabaseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $session = Yii::$app->session;
        $checklogin = User::findOne(['user'=>$session['user'],]);

        $data = Yii::$app->request->get();
        $del = $data['del'] ?? '';
        $chk = $data['chk'] ?? '';

        //ลบข้อมูล
        if($del==="deletedata"){

        $sql = "
        TRUNCATE TABLE fwfdatabase
        ";
        $query_del = Yii::$app->db->createCommand($sql)->execute();

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
        $model = new Fwfdatabase();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->fwfid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }    public function actionImport($id)
    {

        $xlsid = Yii::$app->request->get();
        $xlsid = $xlsid['id'] ?? '';
        $stimpdat=date('Y-m-d,h:i:s');
        \Yii::$app->db->createCommand()->update('xlsfileuploads', ['startdatetime' => $stimpdat], 'xlsid = '.$xlsid)->execute();
        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();

        try{
            $file = Yii::getAlias('@webroot').'/'.'uploads'.'/'.$xlsdata['exsfile'];
            // $inputFile = \PHPExcel_IOFactory::identify($file);
            // $objReader = \PHPExcel_IOFactory::createReader($inputFile);

            $inputFile = IOFactory::identify($file);
            $objReader = IOFactory::createReader($inputFile);               
            $objPHPExcel = $objReader->load($file);
        }catch (Exception $e){
            Yii::$app->session->addFlash('error', 'เกิดข้อผิดพลาด'. $e->getMessage());
        } 
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); //จำนวนแถวสูงสุด ในไฟล์ excel
            $highestColumn = $sheet->getHighestColumn();
    
            $objWorksheet = $objPHPExcel->getActiveSheet();


                for ($row = 2; $row <= $highestRow; $row++){ 
                    
                    $arr1[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row);
                }
                foreach($arr1 as $key => $element) {  
                    if($arr1[$key][0][0]<>''){ $hkey = $key; } 
                } 
                $highkey=$hkey+1; //จำนวนแถวสูงสุดหลังจากตัดค่าว่าง
                
                for ($row = 2; $row <= $highkey+1; $row++){ 
                    
                    $data[] = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row);
                }

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
                    $FundHolderID = $data[$key][0][4];
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
                  $endimpdat=date('Y-m-d,h:i:s');
                  \Yii::$app->db->createCommand()->update('xlsfileuploads', ['enddatetime' => $endimpdat], 'xlsid = '.$xlsid)->execute();

                  \Yii::$app->db->createCommand()->update('xlsfileuploads', ['importstatus' => 1], 'xlsid = '.$xlsid)->execute();
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->fwfid]);
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
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
