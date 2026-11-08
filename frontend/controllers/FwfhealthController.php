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
/*
                    $ad_y = substr($data[$key][0][1],0,4);
                    $ad_m = substr($data[$key][0][1],5,2); 
                    $ad_d = substr($data[$key][0][1],8,2);
                    $addate = ($ad_y-543).'-'.$ad_m.'-'.$ad_d; 
                  
                    $dc_y = substr($data[$key][0][2],0,4);
                    $dc_m = substr($data[$key][0][2],5,2); 
                    $dc_d = substr($data[$key][0][2],8,2);
                    $dcdate = ($dc_y-543).'-'.$dc_m.'-'.$dc_d; 
*/
                    $datev = $data[$key][0][0];
                    $hcode = $data[$key][0][1];  
                    $cid = $data[$key][0][2];
                    $prefix = $data[$key][0][3];
                    $fname = $data[$key][0][4];
                    $lname = $data[$key][0][5];
                    $birthday = $data[$key][0][6];
                    $nation = $data[$key][0][7];
                    $type_mg = $data[$key][0][8];
                    $sex = $data[$key][0][9];
                    $address = $data[$key][0][10];
                    $company = $data[$key][0][11];
                    $bossname = $data[$key][0][12]; 
                    $telephone = $data[$key][0][13]; 
                    $hn = $data[$key][0][14]; 
                    $vn = $data[$key][0][15]; 
                    $card_id = $data[$key][0][16]; 
                    $typehealth_id = $data[$key][0][18]; 
                    $diesease_id = $data[$key][0][19]; 



                    if(!empty($cid)&&!empty($hn)&&!empty($datev)){
                        if(strlen($cid)==13){
                        $datev= date("Y-m-d", strtotime($datev));

                        $sqlcheckfwf = "
                        SELECT * FROM fwfhealth WHERE fwfhealth.cid = '$cid' 
                        and fwfhealth.hn = '$hn' 
                        and fwfhealth.datev = '$datev'
                        and fwfhealth.vn = '$vn'
                        ";
    
                        $querycheckfwf = Yii::$app->db->createCommand($sqlcheckfwf)->queryAll();
    
                        if(empty($querycheckfwf)){
                        
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


                  }//foreach    
                  $endimpdat=date('Y-m-d,h:i:s');
                  \Yii::$app->db->createCommand()->update('xlsfileuploads', ['enddatetime' => $endimpdat], 'xlsid = '.$xlsid)->execute();
                  \Yii::$app->db->createCommand()->update('xlsfileuploads', ['importstatus' => 1], 'xlsid = '.$xlsid)->execute();
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
        $model = new Fwfhealth();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->fwfhealth_id]);
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->fwfhealth_id]);
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
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
