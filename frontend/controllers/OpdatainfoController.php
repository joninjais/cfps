<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Opdata;
use frontend\models\OpdatainfoSearch;
use frontend\models\Fwfdatabase;
use frontend\models\Xlsfileuploads;
use frontend\models\XlsfileuploadsSearch;
// use frontend\models\Locksentday;
use frontend\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
/**
 * OpdatainfoController implements the CRUD actions for Opdata model.
 */
class OpdatainfoController extends BaseController
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
     * Lists all Opdata models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OpdatainfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $session = Yii::$app->session;
        $checklogin = User::findOne(['user'=>$session['user'],]);
        if(!$checklogin){
            echo "<script>window.location='index.php?r=site/login';</script>";
        }
        $data = Yii::$app->request->get();
        $del = $data['del'] ?? '';
        $chk = $data['chk'] ?? '';

        //ลบข้อมูล

        if($del==="deletedata"){

        $sql = "
        DELETE FROM `opdata` WHERE `opdata`.`hospcode` = ".$session['hoscode']."
        ";
        $query_del = Yii::$app->db->createCommand($sql)->execute();

                    $result="ลบข้อมูลสำเร็จ";
        }else{
         
            $result="ลบข้อมูลไม่สำเร็จ"; }

        // return $this->render('index', [
        //     'searchModel' => $searchModel,
        //     'dataProvider' => $dataProvider,
        //     'result' => $result,
        // ]);


        //ปรับปรุงข้อมูล
        if($chk==="checkdata"){

            // $sql = "
            // SELECT * FROM opdata WHERE opdata.FundHolderID='ccccc'
            // ";
            // $query = Yii::$app->db->createCommand($sql)->execute();
            $session = Yii::$app->session;
            $useriofo = User::findOne(['user'=>$session['user'],]);
            $hospcode = $session['hoscode'];


            // $query=Opdata::findall(['hospcode'=>$useriofo['hoscode'],'FundHolderID'=>'ccccc' ]);            
            // $result=$query;

            $sqlupdate = "
                update opdata
                INNER JOIN
                (
                SELECT
                    x.PeriodStartDate,
                    x.PeriodEndDate,
                    x.opid,
                    x.hospcode,
                    x.dateserv,
                    x.name,
                    x.fund,
                    x.cid,
                    x.hn,
                    x.seq,
                    x.icd10,
                    x.op_pp_type,
                    x.m1,
                    x.m2,
                    x.m3,
                    x.m4,
                    x.m5,
                    x.m6,
                    x.m7,
                    x.m8,
                    x.m9,
                    x.m10,
                    x.m11,
                    x.m12,
                    x.m13,
                    x.m14,
                    x.m15,
                    x.m16,
                    x.alltotal,
                    x.FundHolderID,
                    x.exsfile,
                    x.fwf_FundHolderID
                FROM (
                    SELECT
                        fwf.PeriodStartDate,
                        fwf.PeriodEndDate,
                        op.opid,
                        op.hospcode,
                        op.dateserv,
                        op.name,
                        op.fund,
                        op.cid,
                        op.hn,
                        op.seq,
                        op.icd10,
                        op.op_pp_type,
                        op.m1,
                        op.m2,
                        op.m3,
                        op.m4,
                        op.m5,
                        op.m6,
                        op.m7,
                        op.m8,
                        op.m9,
                        op.m10,
                        op.m11,
                        op.m12,
                        op.m13,
                        op.m14,
                        op.m15,
                        op.m16,
                        op.alltotal,
                        op.FundHolderID,
                        op.exsfile,
                        fwf.FundHolderID AS fwf_FundHolderID
                    FROM opdata AS op FORCE INDEX (idx_opdata_fundholder_fund)
                    INNER JOIN fwfdatabase AS fwf
                            ON fwf.CID = TRIM(op.cid)
                        AND fwf.CID NOT LIKE '%,%'
                        AND op.dateserv BETWEEN fwf.PeriodStartDate AND fwf.PeriodEndDate
                    WHERE op.FundHolderID IN ('ccccc', '')
                   -- AND LENGTH(op.fund) = 5
                    AND op.icd10 NOT IN ('', '{}')

                    UNION ALL

                    SELECT
                        c.PeriodStartDate,
                        c.PeriodEndDate,
                        op.opid,
                        op.hospcode,
                        op.dateserv,
                        op.name,
                        op.fund,
                        op.cid,
                        op.hn,
                        op.seq,
                        op.icd10,
                        op.op_pp_type,
                        op.m1,
                        op.m2,
                        op.m3,
                        op.m4,
                        op.m5,
                        op.m6,
                        op.m7,
                        op.m8,
                        op.m9,
                        op.m10,
                        op.m11,
                        op.m12,
                        op.m13,
                        op.m14,
                        op.m15,
                        op.m16,
                        op.alltotal,
                        op.FundHolderID,
                        op.exsfile,
                        c.FundHolderID AS fwf_FundHolderID
                    FROM (
                        SELECT PeriodStartDate, PeriodEndDate, FundHolderID, TRIM(SUBSTRING_INDEX(CID, ',', 1)) AS cid_token
                        FROM fwfdatabase
                        WHERE CID LIKE '%,%'

                        UNION ALL

                        SELECT PeriodStartDate, PeriodEndDate, FundHolderID, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(CID, ',', 2), ',', -1)) AS cid_token
                        FROM fwfdatabase
                        WHERE CID LIKE '%,%'

                        UNION ALL

                        SELECT PeriodStartDate, PeriodEndDate, FundHolderID, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(CID, ',', 3), ',', -1)) AS cid_token
                        FROM fwfdatabase
                        WHERE CID LIKE '%,%'

                        UNION ALL

                        SELECT PeriodStartDate, PeriodEndDate, FundHolderID, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(CID, ',', 4), ',', -1)) AS cid_token
                        FROM fwfdatabase
                        WHERE CID LIKE '%,%'
                    ) AS c
                    INNER JOIN opdata AS op FORCE INDEX (idx_opdata_cid_dateserv)
                            ON op.cid = c.cid_token
                        AND c.cid_token <> ''
                        AND op.dateserv BETWEEN c.PeriodStartDate AND c.PeriodEndDate
                    WHERE op.FundHolderID IN ('ccccc', '')
                   -- AND LENGTH(op.fund) = 5
                    AND op.icd10 NOT IN ('', '{}')
                ) AS x
                ORDER BY x.dateserv DESC
                ) as opdata_tmp
                on opdata.opid = opdata_tmp.opid
                    and opdata.hospcode = '$hospcode'
                set opdata.FundHolderID = opdata_tmp.fwf_FundHolderID
            ";
            $query_update = Yii::$app->db->createCommand($sqlupdate)->execute();



            }else{
             
                $result="ปรับปรุงข้อมูลไม่สำเร็จ"; }
    
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'result' => $result,
            ]);
/*
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        */
    }

    /**
     * Displays a single Opdata model.
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
     * Creates a new Opdata model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Opdata();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->opid]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    public function actionImport($id)
    {
        set_time_limit(0); // ไฟล์ใหญ่อาจใช้เวลานาน

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
            return $this->redirect(['opdatainfo/index']);
        }

        $sheet      = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // อ่านข้อมูล Excel ทั้งหมดครั้งเดียว (เร็วกว่าอ่านทีละแถวมาก)
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
            return $this->redirect(['opdatainfo/index']);
        }

        // --- Pre-load fwfdatabase ทั้งหมดเข้า memory ครั้งเดียว ---
        // สร้าง map: fwfMap[$cid][] = [FundHolderID, PeriodStartDate, PeriodEndDate]
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

        // --- Pre-load opdata เฉพาะ hospcode ที่มีในไฟล์ Excel ---
        $hcList    = array_keys($hospcodes);
        $rawOpdata = (new \yii\db\Query())
            ->select(['seq', 'hospcode', 'FundHolderID'])
            ->from('opdata')
            ->where(['hospcode' => $hcList])
            ->all();

        $existingSet = []; // "hospcode|seq|FundHolderID" => true
        $cccccSet    = []; // "hospcode|seq" => true  (เฉพาะ FundHolderID='ccccc')
        foreach ($rawOpdata as $r) {
            $existingSet[$r['hospcode'] . '|' . $r['seq'] . '|' . $r['FundHolderID']] = true;
            if ($r['FundHolderID'] === 'ccccc') {
                $cccccSet[$r['hospcode'] . '|' . $r['seq']] = true;
            }
        }
        unset($rawOpdata);

        $insertRows  = [];
        $deletePairs = []; // สำหรับลบแถว FundHolderID='ccccc'

        for ($i = 0; $i <= $lastIdx; $i++) {
            $row = $sheetData[$i];
            if (empty($row[0])) continue;

            $hospcode = $row[0];
            $raw = $row[1];
            if (is_numeric($raw) && $raw > 1000) {
                // Excel serial date (เช่น 46023) → แปลงผ่าน PhpSpreadsheet
                $dateserv = date('Y-m-d', ExcelDate::excelToTimestamp((float)$raw));
            } else {
                $dateserv = date('Y-m-d', strtotime((string)$raw));
            }
            $name     = $row[2];
            $fund     = $row[3];
            $cid      = $row[4];
            $hn       = $row[5];
            $seq      = $row[6];
            $icd10    = str_replace('.', '', (string)$row[7]);
            $ms_raw   = array_slice($row, 8, 16); // m1..m16 (index 0..15)
            // ลบ , ออกจากแต่ละค่าใน $ms_raw แล้ว cast เป็น float สำหรับ PHP 8
            $ms = array_map(function($v) {
                return (float) str_replace(',', '', (string)($v ?? ''));
            }, $ms_raw);
            $alltotal = array_sum($ms);

            if (empty($seq) || empty($hospcode)) continue;

            // --- หา FundHolderID จาก fwfMap แทนการ query ---
            $hosb = 'ccccc';
            if (!empty($cid) && isset($fwfMap[$cid])) {
                $matched = [];
                foreach ($fwfMap[$cid] as $fwfRow) {
                    if ($dateserv >= $fwfRow['PeriodStartDate'] && $dateserv <= $fwfRow['PeriodEndDate']) {
                        $matched[] = $fwfRow;
                    }
                }
                if (!empty($matched)) {
                    $mt   = 0;
                    $hosb = 'ccccc';
                    foreach ($matched as $m) {
                        $fhid = $m['FundHolderID'];
                        if ($fhid == '10739' || $fhid == '10740') {
                            $mt++;
                        }
                        // คงเงื่อนไขเดิม (operator precedence เหมือนต้นฉบับ)
                        if ($mt < 2 && $fhid !== '10739' || $fhid !== '10740') {
                            $hosb = $fhid;
                        }
                    }
                    if ($mt >= 1) {
                        $hosb = $matched[0]['FundHolderID'];
                    }
                    if (empty($hosb)) {
                        $hosb = 'ccccc';
                    }
                } else {
                    // ไม่มี period ที่ตรงกับวันบริการ (เช่น permit หมดอายุเล็กน้อย)
                    // ใช้ period ที่หมดอายุล่าสุดเป็น fallback
                    $latestRow = null;
                    $latestEnd = '';
                    foreach ($fwfMap[$cid] as $fwfRow) {
                        if ($fwfRow['PeriodEndDate'] > $latestEnd) {
                            $latestEnd = $fwfRow['PeriodEndDate'];
                            $latestRow = $fwfRow;
                        }
                    }
                    if ($latestRow !== null) {
                        $hosb = $latestRow['FundHolderID'];
                    }
                }
            }

            // เก็บคู่ที่ต้องลบ (FundHolderID='ccccc' เดิม)
            $cccccKey = $hospcode . '|' . $seq;
            if (isset($cccccSet[$cccccKey])) {
                $deletePairs[] = ['seq' => $seq, 'hospcode' => $hospcode];
                unset($cccccSet[$cccccKey]);
                unset($existingSet[$cccccKey . '|ccccc']);
            }

            // ข้ามถ้ามีข้อมูลอยู่แล้ว
            $existKey = $hospcode . '|' . $seq . '|' . $hosb;
            if (isset($existingSet[$existKey])) continue;

            $insertRows[] = [
                $hospcode, $dateserv, $name, $fund, $cid, $hn, $seq, $icd10,
                $ms[0],  $ms[1],  $ms[2],  $ms[3],  $ms[4],  $ms[5],  $ms[6],  $ms[7],
                $ms[8],  $ms[9],  $ms[10], $ms[11], $ms[12], $ms[13], $ms[14], $ms[15],
                $alltotal, $hosb, $xlsdata['exsfile'],
            ];
            $existingSet[$existKey] = true; // ป้องกัน duplicate ใน batch เดียวกัน
        }

        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // Batch delete 'ccccc' rows (จัดกลุ่มตาม hospcode ใช้ IN clause)
            if (!empty($deletePairs)) {
                $byHospcode = [];
                foreach ($deletePairs as $d) {
                    $byHospcode[$d['hospcode']][] = $d['seq'];
                }
                foreach ($byHospcode as $hc => $seqs) {
                    foreach (array_chunk($seqs, 500) as $chunk) {
                        $db->createCommand()->delete('opdata', [
                            'and',
                            ['hospcode' => $hc],
                            ['seq' => $chunk],
                            ['FundHolderID' => 'ccccc'],
                        ])->execute();
                    }
                }
            }

            // Batch INSERT ครั้งละ 500 แถว (เร็วกว่า INSERT ทีละแถวมาก)
            if (!empty($insertRows)) {
                $columns = [
                    'hospcode','dateserv','name','fund','cid','hn','seq','icd10',
                    'm1','m2','m3','m4','m5','m6','m7','m8',
                    'm9','m10','m11','m12','m13','m14','m15','m16',
                    'alltotal','FundHolderID','exsfile',
                ];
                foreach (array_chunk($insertRows, 500) as $chunk) {
                    $db->createCommand()->batchInsert('opdata', $columns, $chunk)->execute();
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

        // อัปเดต op_pp_type สำหรับแถวที่นำเข้าจากไฟล์นี้
        $db->createCommand(
            'UPDATE opdata SET op_pp_type = fn_get_op_pp_type(icd10) WHERE exsfile = :exsfile',
            [':exsfile' => $xlsdata['exsfile']]
        )->execute();

        return $this->redirect(['opdatainfo/index']);
    }    
    
    public function actoinCheck(){

        $opdata=Opdata::findall(['hospcode'=>$hospcode,'FundHolderID'=>'ccccc' ]);
        return $this->render('opdatainfo/index', [
            'opdata' => $opdata,
        ]);
    }
    /**
     * Updates an existing Opdata model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->opid]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Opdata model.
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
     * Finds the Opdata model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Opdata the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Opdata::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
