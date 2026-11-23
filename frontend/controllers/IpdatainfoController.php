<?php

namespace frontend\controllers;

use Yii;
use frontend\components\ExcelImportHeaders;
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
                    'import' => ['POST'],
                    'reprocess-ccccc' => ['POST'],
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

        // การลบ/ปรับปรุงข้อมูลเปลี่ยนแปลงข้อมูลจริง จึงรับเฉพาะ POST (มี CSRF) เท่านั้น
        $request = Yii::$app->request;
        $del = $request->isPost ? (string)$request->post('del', '') : '';
        $chk = $request->isPost ? (string)$request->post('chk', '') : '';
        $result = '';

        //ลบข้อมูล
        if ($del === "deletedata") {
            Yii::$app->db->createCommand()->delete('ipdata', [
                'hospcode' => (string)$session['hoscode'],
            ])->execute();
            $result = "ลบข้อมูลสำเร็จ";
        }

        //ปรับปรุงข้อมูล: ใช้ตรรกะเดียวกับ reprocess-ccccc เพื่อให้ผลตรงกับตอนนำเข้า
        if ($chk === "checkdata") {
            $hospcode = trim((string)$session['hoscode']);
            if ($hospcode !== '') {
                [$updated, $still] = $this->reprocessHospital($hospcode);
                $result = 'ปรับปรุงข้อมูลสำเร็จ: ' . $updated . ' รายการ, ยังเป็น ccccc ' . $still . ' รายการ';
            }
        }

        if ($result !== '') {
            $session->addFlash('success', $result);
        }

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

        if ($model->load(Yii::$app->request->post())) {
            if (!$this->isProvincialAdmin()) {
                $model->hospcode = trim((string)Yii::$app->session->get('hoscode'));
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->ipid]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionImport($id)
    {
        set_time_limit(0);

        $xlsid = (int)$id;

        $xlsdata = (new \yii\db\Query())->from('xlsfileuploads')->where(['xlsid' => $xlsid])->one();

        if (empty($xlsdata) || ($xlsdata['typefileupload'] ?? null) !== 'ip') {
            Yii::$app->session->addFlash('error', 'ไม่พบไฟล์ หรือไม่มีสิทธิ์นำเข้าไฟล์นี้');
            return $this->redirect(['ipdatainfo/index']);
        }
        $ownershipError = $this->checkImportOwnership($xlsdata, []);
        if ($ownershipError !== null) {
            Yii::$app->session->addFlash('error', $ownershipError);
            return $this->redirect(['ipdatainfo/index']);
        }

        try {
            $file = Yii::getAlias('@webroot') . '/uploads/' . $xlsdata['exsfile'];
            $objPHPExcel = IOFactory::load($file);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            return $this->redirect(['ipdatainfo/index']);
        }

        $sheet      = $objPHPExcel->getSheet(0);
        $headerError = ExcelImportHeaders::validate($sheet, ExcelImportHeaders::IP);
        if ($headerError !== null) {
            Yii::$app->session->addFlash('error', $headerError);
            return $this->redirect(['ipdatainfo/index']);
        }

        \Yii::$app->db->createCommand()->update(
            'xlsfileuploads', ['startdatetime' => date('Y-m-d H:i:s')], ['xlsid' => $xlsid]
        )->execute();

        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // อ่าน Excel ทั้งหมดครั้งเดียว
        $sheetData = $sheet->rangeToArray('A2:' . $highestCol . $highestRow, null, true, false);

        $colRefer    = 14;
        $colReferTo  = 15;
        $colSex      = 16;
        $colAge      = 17;
        $colNation   = 18;

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

        // hospcode ในทุกแถวต้องตรงกับหน่วยบริการของผู้ใช้ (ยกเว้น 00064)
        $ownErr = $this->checkImportOwnership($xlsdata, $hospcodes);
        if ($ownErr !== null) {
            Yii::$app->session->addFlash('error', $ownErr);
            return $this->redirect(['ipdatainfo/index']);
        }

        // --- ตรวจ nation ก่อนนำเข้า: ถ้ามีแถวที่ไม่อยู่ในรายการที่อนุญาต ไม่นำเข้าทั้งไฟล์ ---
        $badNation = [];
        for ($i = 0; $i <= $lastIdx; $i++) {
            $row = $sheetData[$i];
            if (empty($row[0]) || empty(trim((string)$row[7]))) continue; // แถวที่จะถูกข้ามอยู่แล้ว (hospcode/an ว่าง)
            if ($this->normalizeNation($row[$colNation] ?? '') === null) {
                $badNation[] = ($i + 2) . ' (' . trim((string)($row[$colNation] ?? '')) . ')';
            }
        }
        if (!empty($badNation)) {
            Yii::$app->session->addFlash('error',
                'ไม่นำเข้าข้อมูล: พบค่า nation ที่ไม่ถูกต้องจำนวน ' . count($badNation)
                . ' แถว (ค่าที่อนุญาต: ' . implode(', ', self::ALLOWED_NATIONS) . ') แถวที่: '
                . implode(', ', array_slice($badNation, 0, 20))
                . (count($badNation) > 20 ? ' ...' : '')
            );
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
                    $fwfMap[mb_strtoupper($c)][] = [
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
        $badDate     = [];

        for ($i = 0; $i <= $lastIdx; $i++) {
            $row = $sheetData[$i];
            if (empty($row[0])) continue;

            $hospcode     = trim((string)$row[0]);

            // แถวที่ถูกข้ามอยู่แล้ว (an ว่าง) ไม่ต้องตรวจวันที่
            if (empty(trim((string)$row[7]))) continue;

            $admitdate    = $this->parseExcelDate($row[1]);
            $dischartdate = $this->parseExcelDate($row[2]);
            if ($admitdate === null || $dischartdate === null) {
                $badDate[] = ($i + 2) . ' (' . trim((string)$row[1]) . ' / ' . trim((string)$row[2]) . ')';
                continue;
            }
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

            // คอลัมน์ O..S: refer_number, refertohospcode, sex, p_age, nation
            $refer_number    = trim((string)($row[$colRefer] ?? ''));
            $refer_number    = $refer_number === '' ? null : mb_substr($refer_number, 0, 50);
            $refertohospcode = trim((string)($row[$colReferTo] ?? ''));
            $refertohospcode = $refertohospcode === '' ? null : mb_substr($refertohospcode, 0, 5);
            $sex             = trim((string)($row[$colSex] ?? ''));
            $sex             = in_array($sex, ['1', '2'], true) ? $sex : null;
            $p_age           = str_replace(',', '', trim((string)($row[$colAge] ?? '')));
            $p_age           = (is_numeric($p_age) && $p_age >= 0 && $p_age <= 65535) ? (int)$p_age : null;
            // Excel อาจอ่านรหัสเป็นตัวเลข (48) จึงเติม 0 ข้างหน้าให้ครบ 3 หลัก
            $nation          = $this->normalizeNation($row[$colNation] ?? '');

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
                $refer_number, $refertohospcode, $sex, $p_age, $nation,
            ];
            $existingSet[$existKey] = true;
        }

        if (!empty($badDate)) {
            Yii::$app->session->addFlash('error',
                'ไม่นำเข้าข้อมูล: พบวันที่ admit/จำหน่ายที่ว่างหรือไม่ถูกต้องจำนวน ' . count($badDate)
                . ' แถว แถวที่: ' . implode(', ', array_slice($badDate, 0, 20))
                . (count($badDate) > 20 ? ' ...' : '')
            );
            return $this->redirect(['ipdatainfo/index']);
        }

        $db          = Yii::$app->db;
        $imported    = false;
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
                    'refer_number', 'refertohospcode', 'sex', 'p_age', 'nation',
                ];
                foreach (array_chunk($insertRows, 500) as $chunk) {
                    $db->createCommand()->batchInsert('ipdata', $columns, $chunk)->execute();
                }
            }

            $transaction->commit();
            $imported = true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->addFlash('error', 'นำเข้าข้อมูลไม่สำเร็จ: ' . $e->getMessage());
        }

        // บันทึกสถานะเฉพาะเมื่อนำเข้าสำเร็จ
        if ($imported) {
            $db->createCommand()->update('xlsfileuploads',
                ['enddatetime' => date('Y-m-d H:i:s'), 'importstatus' => 1],
                ['xlsid' => $xlsid]
            )->execute();
        }

        return $this->redirect(['ipdatainfo/index']);
    } 

    public function actionReprocessCcccc()
    {
        Yii::$app->facilityAccess->requirePermission('manageOwnFacilityData');
        $session = Yii::$app->session;
        $hoscode = trim((string)$session['hoscode']);
        if ($hoscode === '') {
            $session->addFlash('error', 'ไม่พบรหัสหน่วยบริการ');
            return $this->redirect(['ipdatainfo/index']);
        }

        try {
            [$updated, $stillCcccc, $found] = $this->reprocessHospital($hoscode);
        } catch (\Throwable $e) {
            $session->addFlash('error', 'reprocess ไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['ipdatainfo/index']);
        }

        if (!$found) {
            $session->addFlash('info', 'ไม่พบข้อมูล FundHolderID = ccccc สำหรับหน่วยบริการนี้');
        } else {
            $session->addFlash('success', 'reprocess สำเร็จ: ปรับปรุง ' . $updated . ' รายการ, ยังเป็น ccccc ' . $stillCcccc . ' รายการ');
        }
        return $this->redirect(['ipdatainfo/index']);
    }

    /**
     * คำนวณ FundHolderID ใหม่ของแถว ccccc/ว่าง ของหน่วยบริการเดียว ด้วยตรรกะเดียวกับตอนนำเข้า
     * จัดกลุ่ม ipid ตาม FundHolderID ใหม่ แล้ว UPDATE ครั้งละไม่เกิน 500 รายการต่อกลุ่ม
     * คืน [จำนวนที่ปรับปรุง, จำนวนที่ยังเป็น ccccc, พบแถวหรือไม่]
     */
    private function reprocessHospital(string $hoscode): array
    {
        set_time_limit(0);

        $fwfMap = $this->loadFwfMap();
        $rows = (new \yii\db\Query())
            ->select(['ipid', 'admitdate', 'cid', 'adjrw'])
            ->from('ipdata')
            ->where(['hospcode' => $hoscode])
            ->andWhere(['or', ['FundHolderID' => 'ccccc'], ['FundHolderID' => '']])
            ->all();

        if (empty($rows)) {
            return [0, 0, false];
        }

        $groups = [];
        $stillCcccc = 0;
        $updated = 0;
        $db = Yii::$app->db;
        foreach ($rows as $row) {
            $new = $this->resolveFundHolderId(
                $row['cid'] ?? '',
                $row['admitdate'] ?? '',
                $row['adjrw'] ?? null,
                $fwfMap
            );
            if ($new === 'ccccc' || $new === '') {
                $stillCcccc++;
                continue;
            }
            $groups[$new][] = $row['ipid'];
        }

        $transaction = $db->beginTransaction();
        try {
            foreach ($groups as $fhid => $ipids) {
                foreach (array_chunk($ipids, 500) as $chunk) {
                    $updated += $db->createCommand()->update(
                        'ipdata',
                        ['FundHolderID' => (string)$fhid],
                        ['and', ['hospcode' => $hoscode], ['ipid' => $chunk]]
                    )->execute();
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return [$updated, $stillCcccc, true];
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
        $cid = mb_strtoupper(trim((string)$cid));
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
                $hosb = $this->pickFundHolderId($matched);

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
        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post())) {
            if (!$this->isProvincialAdmin()) {
                $model->hospcode = trim((string)Yii::$app->session->get('hoscode'));
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->ipid]);
            }
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
        $model = $this->findModel($id, true);
        if (!$model->delete()) {
            Yii::error('ไม่สามารถลบข้อมูล IP id=' . $model->ipid, __METHOD__);
            throw new \yii\web\ServerErrorHttpException('ลบข้อมูลไม่สำเร็จ');
        }

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
    protected function findModel($id, bool $forWrite = false)
    {
        if (($model = Ipdata::findOne($id)) !== null) {
            if (Yii::$app->facilityAccess->canAccessFacility((string)$model->hospcode, $forWrite)) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
