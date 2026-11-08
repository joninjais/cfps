<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Ipdata;
use frontend\models\IpdatainfoSearch;
use frontend\models\Fwfdatabase;
use frontend\models\Xlsfileuploads;
use frontend\models\XlsfileuploadsSearch;
use frontend\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
/**
 * IpdatainfoController implements the CRUD actions for Ipdata model.
 */
class IpdatainfoController extends BaseController
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
     * Lists all Ipdata models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new IpdatainfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $session = Yii::$app->session;
        $checklogin = User::findOne(['user'=>$session['user'],]);

        $data = Yii::$app->request->get();
        $del = $data['del'] ?? '';
        $chk = $data['chk'] ?? '';

        //ลบข้อมูล
        if($del==="deletedata"){

        $sql = "
        DELETE FROM `ipdata` WHERE `ipdata`.`hospcode` = ".$session['hoscode']."
        ";
        $query_del = Yii::$app->db->createCommand($sql)->execute();

                    $result="ลบข้อมูลสำเร็จ";
        }else{
         
            $result="ลบข้อมูลไม่สำเร็จ"; }

        //ปรับปรุงข้อมูล
        if($chk==="checkdata"){

            // $sql = "
            // SELECT * FROM opdata WHERE opdata.FundHolderID='ccccc'
            // ";
            // $query = Yii::$app->db->createCommand($sql)->execute();

            $session = Yii::$app->session;
            $useriofo = User::findOne(['user'=>$session['user'],]);
            $hospcode = $session['hoscode'];
            // $query=ipdata::findall(['hospcode'=>$useriofo['hoscode'],'FundHolderID'=>'ccccc' ]);            
            // $result=$query;

            $sqlupdate = "
            UPDATE ipdata
            INNER JOIN
            (
            SELECT
            fwfdatabase.PeriodStartDate,fwfdatabase.PeriodEndDate,ipdata.*,fwfdatabase.FundHolderID as fwf_FundHolderID
            FROM
            ipdata
            INNER JOIN fwfdatabase on  
                        -- ipdata.cid = fwfdatabase.CID
                        find_in_set( ipdata.cid, fwfdatabase.CID )
                        AND STR_TO_DATE( ipdata.admitdate, '%Y-%m-%d' ) BETWEEN fwfdatabase.PeriodStartDate and fwfdatabase.PeriodEndDate
                        and ipdata.icd10 <> ''
            WHERE
            (ipdata.FundHolderID = 'ccccc'
            or ipdata.FundHolderID = ''
            )
           -- and LENGTH(ipdata.fund) = 5
            and ipdata.icd10 NOT IN ('', '{}')
            ORDER BY ipdata.admitdate desc
            ) as ipdata_tmp
            on ipdata.ipid = ipdata_tmp.ipid
            and ipdata.hospcode = '$hospcode'
            set ipdata.FundHolderID = ipdata_tmp.fwf_FundHolderID
            ";
            $query_update = Yii::$app->db->createCommand($sqlupdate)->execute();

            }else{
             
                $result="ปรับปรุงข้อมูลไม่สำเร็จ"; }            
            
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'result' => $result,
        ]);
    }

    /**
     * Displays a single Ipdata model.
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
     * Creates a new Ipdata model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ipdata();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->ipid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionImport($id)
    {
        set_time_limit(0);

        $xlsid = Yii::$app->request->get();
        $xlsid = $xlsid['id'] ?? '';

        \Yii::$app->db->createCommand()->update(
            'xlsfileuploads', ['startdatetime' => date('Y-m-d,h:i:s')], 'xlsid = ' . (int)$xlsid
        )->execute();

        $xlsdata = (new \yii\db\Query())->from('xlsfileuploads')->where(['xlsid' => $xlsid])->one();

        try {
            $file = Yii::getAlias('@webroot') . '/uploads/' . $xlsdata['exsfile'];
            $objPHPExcel = IOFactory::load($file);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            return $this->redirect(['ipdatainfo/index']);
        }

        $sheet      = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // อ่าน Excel ทั้งหมดครั้งเดียว
        $sheetData = $sheet->rangeToArray('A2:' . $highestCol . $highestRow, null, true, false);

        // หาแถวสุดท้ายที่ไม่ว่าง + เก็บ hospcode ที่ไม่ซ้ำกัน
        $lastIdx   = -1;
        $hospcodes = [];
        foreach ($sheetData as $i => $row) {
            if (!empty($row[0])) {
                $lastIdx = $i;
                $hospcodes[$row[0]] = true;
            }
        }
        if ($lastIdx < 0) {
            return $this->redirect(['ipdatainfo/index']);
        }

        // --- Pre-load fwfdatabase ทั้งหมดเข้า memory ครั้งเดียว ---
        $allFwf = Yii::$app->db->createCommand(
            'SELECT CID, FundHolderID, PeriodStartDate, PeriodEndDate FROM fwfdatabase'
        )->queryAll();

        $fwfMap = [];
        foreach ($allFwf as $fwfRow) {
            foreach (array_map('trim', explode(',', $fwfRow['CID'])) as $c) {
                if ($c !== '') {
                    $fwfMap[$c][] = [
                        'FundHolderID'    => $fwfRow['FundHolderID'],
                        'PeriodStartDate' => $fwfRow['PeriodStartDate'],
                        'PeriodEndDate'   => $fwfRow['PeriodEndDate'],
                    ];
                }
            }
        }
        unset($allFwf);

        // --- Pre-load ipdata เฉพาะ hospcode ที่มีในไฟล์ Excel ---
        $hcList    = array_keys($hospcodes);
        $rawIpdata = (new \yii\db\Query())
            ->select(['an', 'hospcode', 'FundHolderID'])
            ->from('ipdata')
            ->where(['hospcode' => $hcList])
            ->all();

        $existingSet = []; // "hospcode|an|FundHolderID" => true
        $cccccSet    = []; // "hospcode|an" => true  (เฉพาะ FundHolderID='ccccc')
        foreach ($rawIpdata as $r) {
            $existingSet[$r['hospcode'] . '|' . $r['an'] . '|' . $r['FundHolderID']] = true;
            if ($r['FundHolderID'] === 'ccccc') {
                $cccccSet[$r['hospcode'] . '|' . $r['an']] = true;
            }
        }
        unset($rawIpdata);

        $insertRows  = [];
        $deletePairs = [];

        for ($i = 0; $i <= $lastIdx; $i++) {
            $row = $sheetData[$i];
            if (empty($row[0])) continue;

            $hospcode     = trim((string)$row[0]);
            $raw1 = $row[1];
            $admitdate    = (is_numeric($raw1) && $raw1 > 1000)
                ? date('Y-m-d', ExcelDate::excelToTimestamp((float)$raw1))
                : date('Y-m-d', strtotime((string)$raw1));
            $raw2 = $row[2];
            $dischartdate = (is_numeric($raw2) && $raw2 > 1000)
                ? date('Y-m-d', ExcelDate::excelToTimestamp((float)$raw2))
                : date('Y-m-d', strtotime((string)$raw2));
            $name         = $row[3];
            $fund         = $row[4];
            $cid          = trim((string)$row[5]);
            $hn           = $row[6];
            $an           = trim((string)$row[7]);
            $icd10        = str_replace('.', '', (string)$row[8]);
            $icd9         = $row[9];
            $los          = $row[10];
            $cost         = (float)str_replace(',', '', (string)$row[11]);
            $adjrw        = (float)str_replace(',', '', (string)$row[12]);
            $costadjrw    = $adjrw * 8600;

            if (empty($an) || empty($hospcode)) continue;

            // --- หา FundHolderID จาก fwfMap แทนการ query ---
            $hosb = $this->resolveFundHolderId($cid, $admitdate, $adjrw, $fwfMap);

            // เก็บคู่ที่ต้องลบ (FundHolderID='ccccc' เดิม)
            $cccccKey = $hospcode . '|' . $an;
            if (isset($cccccSet[$cccccKey])) {
                $deletePairs[] = ['an' => $an, 'hospcode' => $hospcode];
                unset($cccccSet[$cccccKey]);
                unset($existingSet[$cccccKey . '|ccccc']);
            }

            // ข้ามถ้ามีข้อมูลอยู่แล้ว
            $existKey = $hospcode . '|' . $an . '|' . $hosb;
            if (isset($existingSet[$existKey])) continue;

            $insertRows[] = [
                $hospcode, $admitdate, $dischartdate, $name, $fund,
                $cid, $hn, $an, $icd10, $icd9, $los, $cost,
                $adjrw, $costadjrw, $hosb, $xlsdata['exsfile'],
            ];
            $existingSet[$existKey] = true;
        }

        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // Batch delete 'ccccc' rows
            if (!empty($deletePairs)) {
                $byHospcode = [];
                foreach ($deletePairs as $d) {
                    $byHospcode[$d['hospcode']][] = $d['an'];
                }
                foreach ($byHospcode as $hc => $ans) {
                    foreach (array_chunk($ans, 500) as $chunk) {
                        $db->createCommand()->delete('ipdata', [
                            'and',
                            ['hospcode' => $hc],
                            ['an' => $chunk],
                            ['FundHolderID' => 'ccccc'],
                        ])->execute();
                    }
                }
            }

            // Batch INSERT ครั้งละ 500 แถว
            if (!empty($insertRows)) {
                $columns = [
                    'hospcode', 'admitdate', 'dischartdate', 'name', 'fund',
                    'cid', 'hn', 'an', 'icd10', 'icd9', 'los', 'cost',
                    'adjrw', 'costadjrw', 'FundHolderID', 'exsfile',
                ];
                foreach (array_chunk($insertRows, 500) as $chunk) {
                    $db->createCommand()->batchInsert('ipdata', $columns, $chunk)->execute();
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->addFlash('error', 'นำเข้าข้อมูลไม่สำเร็จ: ' . $e->getMessage());
        }

        $db->createCommand()->update('xlsfileuploads',
            ['enddatetime' => date('Y-m-d,h:i:s'), 'importstatus' => 1],
            'xlsid = ' . (int)$xlsid
        )->execute();

        return $this->redirect(['ipdatainfo/index']);
    } 

    public function actionReprocessCcccc()
    {
        set_time_limit(0);

        $session = Yii::$app->session;
        $hoscode = trim((string)$session['hoscode']);
        if ($hoscode === '') {
            $session->addFlash('error', 'ไม่พบรหัสหน่วยบริการ');
            return $this->redirect(['ipdatainfo/index']);
        }

        $fwfMap = $this->loadFwfMap();
        $rows = (new \yii\db\Query())
            ->select(['ipid', 'hospcode', 'admitdate', 'cid', 'adjrw'])
            ->from('ipdata')
            ->where(['hospcode' => $hoscode])
            ->andWhere(['or', ['FundHolderID' => 'ccccc'], ['FundHolderID' => '']])
            ->all();

        if (empty($rows)) {
            $session->addFlash('info', 'ไม่พบข้อมูล FundHolderID = ccccc สำหรับหน่วยบริการนี้');
            return $this->redirect(['ipdatainfo/index']);
        }

        $updated = 0;
        $stillCcccc = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($rows as $row) {
                $newFundHolderId = $this->resolveFundHolderId(
                    $row['cid'] ?? '',
                    $row['admitdate'] ?? '',
                    $row['adjrw'] ?? null,
                    $fwfMap
                );

                if ($newFundHolderId === 'ccccc' || $newFundHolderId === null || $newFundHolderId === '') {
                    $stillCcccc++;
                    continue;
                }

                Yii::$app->db->createCommand()->update(
                    'ipdata',
                    ['FundHolderID' => $newFundHolderId],
                    ['ipid' => $row['ipid']]
                )->execute();
                $updated++;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $session->addFlash('error', 'reprocess ไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['ipdatainfo/index']);
        }

        $session->addFlash('success', 'reprocess สำเร็จ: ปรับปรุง ' . $updated . ' รายการ, ยังเป็น ccccc ' . $stillCcccc . ' รายการ');
        return $this->redirect(['ipdatainfo/index']);
    }

    private function loadFwfMap()
    {
        $allFwf = Yii::$app->db->createCommand(
            'SELECT CID, FundHolderID, PeriodStartDate, PeriodEndDate FROM fwfdatabase'
        )->queryAll();

        $fwfMap = [];
        foreach ($allFwf as $fwfRow) {
            foreach (array_map('trim', explode(',', (string)$fwfRow['CID'])) as $c) {
                if ($c !== '') {
                    $fwfMap[$c][] = [
                        'FundHolderID'    => $fwfRow['FundHolderID'],
                        'PeriodStartDate' => $fwfRow['PeriodStartDate'],
                        'PeriodEndDate'   => $fwfRow['PeriodEndDate'],
                    ];
                }
            }
        }

        return $fwfMap;
    }

    private function resolveFundHolderId($cid, $admitdate, $adjrw, array $fwfMap)
    {
        $cid = trim((string)$cid);
        $admitdate = trim((string)$admitdate);
        $adjrw = (float)$adjrw;

        $hosb = 'ccccc';
        if ($cid !== '' && isset($fwfMap[$cid])) {
            $matched = [];
            foreach ($fwfMap[$cid] as $fwfRow) {
                if ($admitdate >= $fwfRow['PeriodStartDate'] && $admitdate <= $fwfRow['PeriodEndDate']) {
                    $matched[] = $fwfRow;
                }
            }

            if (!empty($matched)) {
                foreach ($matched as $m) {
                    $fhid = (string)$m['FundHolderID'];
                    if ($fhid !== '10739' && $fhid !== '10740') {
                        $hosb = $fhid;
                        break;
                    }
                }

                if ($hosb === 'ccccc') {
                    $hosb = (string)$matched[0]['FundHolderID'];
                }

                if ($adjrw > 4.0) {
                    $hosb = 'ccccc';
                }
            }
        }

        return $hosb;
    }

    /**
     * Updates an existing Ipdata model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->ipid]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Ipdata model.
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
    
    public function actionDeleteipdata()
    {

        //$this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    /**
     * Finds the Ipdata model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ipdata the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ipdata::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
