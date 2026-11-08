<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Insurancepolicies;
use frontend\models\InsurancepoliciesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * InsurancepoliciesController implements the CRUD actions for Insurancepolicies model.
 */
class InsurancepoliciesController extends BaseController
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
                    'delete'          => ['POST'],
                    'clear-policies'  => ['POST'],
                ],
            ],
        ];
    }

    public function actionClearPolicies()
    {
        $session = Yii::$app->session;
        $hoscode = $session['hoscode'];
        $isAdmin = ($hoscode === '00064');

        if ($isAdmin) {
            $deleted = Yii::$app->db->createCommand('DELETE FROM insurance_policies')->execute();
        } else {
            $deleted = Yii::$app->db->createCommand(
                'DELETE FROM insurance_policies WHERE agency_code LIKE :hoscode'
            )->bindValue(':hoscode', '%' . $hoscode . '%')->execute();
        }

        Yii::$app->session->addFlash('success', 'ลบข้อมูลกรมธรรม์สำเร็จ ' . $deleted . ' รายการ');
        return $this->redirect(['index']);
    }

    /**
     * Dashboard: สรุปการซื้อกรมธรรม์ แยกตามราคา
     */
    public function actionDashboard()
    {
        $session = Yii::$app->session;
        $hoscode = $session['hoscode'];
        $isAdmin = ($hoscode === '00064');

        $db = Yii::$app->db;

        // สรุปรวมทั้งหมด
        $totalQuery = $db->createCommand("
            SELECT COUNT(*) AS cnt, COALESCE(SUM(card_price), 0) AS total_amount
            FROM insurance_policies
            " . ($isAdmin ? "" : "WHERE agency_code LIKE :hoscode")
        );
        if (!$isAdmin) $totalQuery->bindValue(':hoscode', '%' . $hoscode . '%');
        $totals = $totalQuery->queryOne();

        // นับแยกตามราคาบัตร
        $byPriceQuery = $db->createCommand("
            SELECT card_price, COUNT(*) AS cnt, COALESCE(SUM(card_price), 0) AS total_amount,
                   policy_status
            FROM insurance_policies
            " . ($isAdmin ? "" : "WHERE agency_code LIKE :hoscode") . "
            GROUP BY card_price, policy_status
            ORDER BY card_price, policy_status
        ");
        if (!$isAdmin) $byPriceQuery->bindValue(':hoscode', '%' . $hoscode . '%');
        $byPrice = $byPriceQuery->queryAll();

        // นับแยกตามหน่วยงาน + ราคาบัตร
        $byAgencyQuery = $db->createCommand("
            SELECT agency_code, agency_name, card_price, COUNT(*) AS cnt,
                   COALESCE(SUM(card_price), 0) AS total_amount
            FROM insurance_policies
            " . ($isAdmin ? "" : "WHERE agency_code LIKE :hoscode") . "
            GROUP BY agency_code, agency_name, card_price
            ORDER BY agency_name, card_price
        ");
        if (!$isAdmin) $byAgencyQuery->bindValue(':hoscode', '%' . $hoscode . '%');
        $byAgency = $byAgencyQuery->queryAll();

        // นับแยกตามเดือน/ปี ที่ซื้อ
        $byMonthQuery = $db->createCommand("
            SELECT DATE_FORMAT(purchase_date, '%Y-%m') AS ym,
                   COUNT(*) AS cnt,
                   COALESCE(SUM(card_price), 0) AS total_amount
            FROM insurance_policies
            " . ($isAdmin ? "WHERE purchase_date IS NOT NULL" : "WHERE agency_code LIKE :hoscode AND purchase_date IS NOT NULL") . "
            GROUP BY ym
            ORDER BY ym DESC
            LIMIT 24
        ");
        if (!$isAdmin) $byMonthQuery->bindValue(':hoscode', '%' . $hoscode . '%');
        $byMonth = $byMonthQuery->queryAll();

        return $this->render('dashboard', [
            'totals'    => $totals,
            'byPrice'   => $byPrice,
            'byAgency'  => $byAgency,
            'byMonth'   => $byMonth,
            'isAdmin'   => $isAdmin,
        ]);
    }

    /**
     * Lists all Insurancepolicies models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new InsurancepoliciesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Insurancepolicies model.
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
     * Creates a new Insurancepolicies model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    public function actionImport($id)
    {
        $params = Yii::$app->request->get();
        $xlsid = (int)($params['id'] ?? $id);
        if (empty($xlsid)) {
            Yii::$app->session->addFlash('error', 'ไม่พบรหัสไฟล์นำเข้า');
            return $this->redirect(['insurancepolicies/index']);
        }

        $stimpdat = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->update('xlsfileuploads', ['startdatetime' => $stimpdat], 'xlsid = :id', [':id' => $xlsid])->execute();

        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();

        if (empty($xlsdata) || empty($xlsdata['exsfile'])) {
            Yii::$app->session->addFlash('error', 'ไม่พบข้อมูลไฟล์ที่ต้องการนำเข้า');
            return $this->redirect(['insurancepolicies/index']);
        }

        $file = Yii::getAlias('@webroot') . '/uploads/' . $xlsdata['exsfile'];
        if (!is_file($file)) {
            Yii::$app->session->addFlash('error', 'ไม่พบไฟล์ Excel ในระบบ');
            return $this->redirect(['insurancepolicies/index']);
        }

        try {
            $inputFile = IOFactory::identify($file);
            $objReader = IOFactory::createReader($inputFile);
            $objPHPExcel = $objReader->load($file);
        } catch (\Throwable $e) {
            Yii::$app->session->addFlash('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage());
            return $this->redirect(['insurancepolicies/index']);
        }

        $sheet = $objPHPExcel->getActiveSheet();
        $highestRow = (int)$sheet->getHighestRow();

        $tableSchema = Yii::$app->db->schema->getTableSchema('insurance_policies', true);
        $agencyCodeColumn = isset($tableSchema->columns['agency_code']) ? 'agency_code' : 'hcode';
        $agencyNameColumn = isset($tableSchema->columns['agency_name']) ? 'agency_name' : 'hname';

        $headerRow = 0;
        for ($row = 1; $row <= min(20, $highestRow); $row++) {
            $a = trim((string)$sheet->getCell('A' . $row)->getFormattedValue());
            $b = trim((string)$sheet->getCell('B' . $row)->getFormattedValue());
            if ($a === 'รหัสหน่วยงาน' && $b === 'หน่วยงาน') {
                $headerRow = $row;
                break;
            }
        }
        $startRow = $headerRow > 0 ? $headerRow + 1 : 2;

        $inserted = 0;
        $duplicates = 0;
        $skipped = 0;

        // ตรวจสอบ format ไฟล์จาก header row: ถ้า col 11 = "วัน/เดือน/ปี เกิด" คือ format ใหม่ (44 คอลัมน์ A-AR)
        $isNewFormat = false;
        if ($headerRow > 0) {
            $headerCols = $sheet->rangeToArray('A' . $headerRow . ':AR' . $headerRow, null, true, true, false);
            $headerData = $headerCols[0] ?? [];
            $isNewFormat = (isset($headerData[11]) && mb_strpos((string)$headerData[11], 'เกิด') !== false);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($row = $startRow; $row <= $highestRow; $row++) {
                $cols = $sheet->rangeToArray('A' . $row . ':AR' . $row, null, true, true, false);
                $rowData = $cols[0] ?? [];

                $agencyCode = $this->normalizeText($rowData[0] ?? null);
                $agencyName = $this->normalizeText($rowData[1] ?? null);
                $province = $this->normalizeText($rowData[2] ?? null);
                $fCode = $this->normalizeText($rowData[3] ?? null);
                $passportNo = $this->normalizeText($rowData[4] ?? null);
                $citizenId = $this->normalizeText($rowData[5] ?? null);
                $alienRefId = $this->normalizeText($rowData[6] ?? null);
                $titleName = $this->normalizeText($rowData[7] ?? null);
                $fullName = $this->normalizeText($rowData[8] ?? null);
                $nationality = $this->normalizeText($rowData[9] ?? null);
                $ethnicity = $this->normalizeText($rowData[10] ?? null);

                if ($isNewFormat) {
                    // Format ใหม่ (44 คอลัมน์ A-AR): มีคอลัมน์วันเกิด, อายุ, ที่อยู่, เลขอ้างอิง แทรกเข้ามา
                    $hnCode        = $this->normalizeText($rowData[13] ?? null);
                    $purchaseDate  = $this->parseExcelDate($rowData[22] ?? null);
                    $effectiveDate = $this->parseExcelDate($rowData[23] ?? null);
                    $expiryDate    = $this->parseExcelDate($rowData[24] ?? null);
                    $policyStatus  = $this->normalizeText($rowData[25] ?? null);
                    $cardPrice     = $this->parseDecimal($rowData[27] ?? null);
                    $coveragePeriod    = $this->normalizeText($rowData[35] ?? null);
                    $fatherPassportNo  = $this->normalizeText($rowData[36] ?? null);
                    $fatherCitizenId   = $this->normalizeText($rowData[37] ?? null);
                    $fatherAlienRefId  = $this->normalizeText($rowData[38] ?? null);
                    $fatherFullName    = $this->normalizeText($rowData[39] ?? null);
                    $motherPassportNo  = $this->normalizeText($rowData[40] ?? null);
                    $motherCitizenId   = $this->normalizeText($rowData[41] ?? null);
                    $motherAlienRefId  = $this->normalizeText($rowData[42] ?? null);
                    $motherFullName    = $this->normalizeText($rowData[43] ?? null);
                } else {
                    // Format เดิม (26 คอลัมน์ A-Z)
                    $hnCode        = $this->normalizeText($rowData[11] ?? null);
                    $purchaseDate  = $this->parseExcelDate($rowData[12] ?? null);
                    $effectiveDate = $this->parseExcelDate($rowData[13] ?? null);
                    $expiryDate    = $this->parseExcelDate($rowData[14] ?? null);
                    $policyStatus  = $this->normalizeText($rowData[15] ?? null);
                    $cardPrice     = $this->parseDecimal($rowData[16] ?? null);
                    $coveragePeriod    = $this->normalizeText($rowData[17] ?? null);
                    $fatherPassportNo  = $this->normalizeText($rowData[18] ?? null);
                    $fatherCitizenId   = $this->normalizeText($rowData[19] ?? null);
                    $fatherAlienRefId  = $this->normalizeText($rowData[20] ?? null);
                    $fatherFullName    = $this->normalizeText($rowData[21] ?? null);
                    $motherPassportNo  = $this->normalizeText($rowData[22] ?? null);
                    $motherCitizenId   = $this->normalizeText($rowData[23] ?? null);
                    $motherAlienRefId  = $this->normalizeText($rowData[24] ?? null);
                    $motherFullName    = $this->normalizeText($rowData[25] ?? null);
                }

                if (
                    empty($agencyCode) && empty($agencyName) && empty($fCode) && empty($passportNo)
                    && empty($citizenId) && empty($alienRefId) && empty($fullName) && empty($hnCode)
                    && empty($cardPrice) && empty($coveragePeriod)
                ) {
                    $skipped++;
                    continue;
                }

                if ($cardPrice === null || $cardPrice == 0) {
                    $skipped++;
                    continue;
                }

                if (!empty($fCode)) {
                    $isDuplicate = (new \yii\db\Query())
                        ->from('insurance_policies')
                        ->where(['f_code' => $fCode])
                        ->exists();
                } else {
                    $duplicateCondition = [
                        $agencyCodeColumn => $agencyCode,
                        'passport_no' => $passportNo,
                        'citizen_id' => $citizenId,
                        'alien_ref_id' => $alienRefId,
                        'hn_code' => $hnCode,
                        'purchase_date' => $purchaseDate,
                        'effective_date' => $effectiveDate,
                        'expiry_date' => $expiryDate,
                    ];
                    $isDuplicate = (new \yii\db\Query())
                        ->from('insurance_policies')
                        ->where($duplicateCondition)
                        ->exists();
                }

                if ($isDuplicate) {
                    $duplicates++;
                    continue;
                }

                $insertData = [
                    $agencyCodeColumn => $agencyCode,
                    $agencyNameColumn => $agencyName,
                    'province' => $province,
                    'f_code' => $fCode,
                    'passport_no' => $passportNo,
                    'citizen_id' => $citizenId,
                    'alien_ref_id' => $alienRefId,
                    'title_name' => $titleName,
                    'full_name' => $fullName,
                    'nationality' => $nationality,
                    'ethnicity' => $ethnicity,
                    'hn_code' => $hnCode,
                    'purchase_date' => $purchaseDate,
                    'effective_date' => $effectiveDate,
                    'expiry_date' => $expiryDate,
                    'policy_status' => $policyStatus,
                    'card_price' => $cardPrice,
                    'coverage_period' => $coveragePeriod,
                    'father_passport_no' => $fatherPassportNo,
                    'father_citizen_id' => $fatherCitizenId,
                    'father_alien_ref_id' => $fatherAlienRefId,
                    'father_full_name' => $fatherFullName,
                    'mother_passport_no' => $motherPassportNo,
                    'mother_citizen_id' => $motherCitizenId,
                    'mother_alien_ref_id' => $motherAlienRefId,
                    'mother_full_name' => $motherFullName,
                ];

                Yii::$app->db->createCommand()->insert('insurance_policies', $insertData)->execute();

                $inserted++;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->addFlash('error', 'นำเข้าข้อมูลไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['insurancepolicies/index']);
        }

        $endimpdat = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->update('xlsfileuploads', ['enddatetime' => $endimpdat, 'importstatus' => 1], 'xlsid = :id', [':id' => $xlsid])->execute();

        // นับจำนวนข้อมูลใน insurance_policies ก่อนนำเข้า
        $countInsurance = (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM insurance_policies')->queryScalar();

        // นับ fwfdatabase ก่อนนำเข้า
        $countFwfBefore = (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM fwfdatabase')->queryScalar();

        // ลูป CALL import_insurance_batch(500) โดยเช็คจำนวนที่เหลือใน insurance_policies จนกว่าจะหมด
        $maxIterations = (int)ceil($countInsurance / 500) + 5;
        $iteration = 0;
        try {
            while ($iteration < $maxIterations) {
                $remaining = (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM insurance_policies')->queryScalar();
                if ($remaining === 0) break;
                Yii::$app->db->createCommand('CALL import_insurance_batch(500)')->execute();
                $iteration++;
            }
        } catch (\Throwable $e) {
            Yii::$app->session->addFlash('error', 'เกิดข้อผิดพลาดขณะโยกข้อมูลไป fwfdatabase: ' . $e->getMessage());
        }

        // นับ fwfdatabase หลังนำเข้า
        $countFwfAfter = (int)Yii::$app->db->createCommand('SELECT COUNT(*) FROM fwfdatabase')->queryScalar();
        $importedRows = $countFwfAfter - $countFwfBefore;

        $this->updateFwfdatabasePrices();

        Yii::$app->session->addFlash('success', 'นำเข้าข้อมูลสำเร็จ: เพิ่มใหม่ ' . $inserted . ' รายการ, ข้ามข้อมูลซ้ำ ' . $duplicates . ' รายการ, ข้ามแถวว่าง ' . $skipped . ' รายการ, นำเข้าตาราง fwfdatabase ' . $importedRows . ' แถว จาก insurance_policies ' . $countInsurance . ' แถว');
        return $this->redirect(['insurancepolicies/index']);

/*
                return $this->render('import',[
                    'xlsid' => $xlsid,
                    'xlsdata' => $xlsdata,
                ]);
*/
    }

    private function updateFwfdatabasePrices()
    {
        try {
            Yii::$app->db->createCommand("
                UPDATE fwfdatabase
                LEFT JOIN (
                    SELECT
                        card.price,
                        MAX(CASE WHEN typeprice.description = 'บริหารจัดการ' AND typeprice.sentprice = 'สสจ' THEN CAST(typeprice.price AS DECIMAL(10,2)) ELSE 0 END) AS man_ssj,
                        MAX(CASE WHEN typeprice.description = 'P&P' AND typeprice.sentprice = 'สสจ' THEN CAST(typeprice.price AS DECIMAL(10,2)) ELSE 0 END) AS pp,
                        MAX(CASE WHEN typeprice.description = 'claim' AND typeprice.sentprice = 'สสจ' THEN CAST(typeprice.price AS DECIMAL(10,2)) ELSE 0 END) AS claim
                    FROM card
                    INNER JOIN typeprice ON card.card_id = typeprice.card_id
                    WHERE typeprice.sentprice = 'สสจ'
                    GROUP BY card.price
                ) AS ssj ON fwfdatabase.card_price = ssj.price
                LEFT JOIN (
                    SELECT
                        card.price,
                        MAX(CASE WHEN typeprice.description = 'บริหารจัดการ' AND typeprice.sentprice = 'สป' THEN CAST(typeprice.price AS DECIMAL(10,2)) ELSE 0 END) AS man_sp,
                        MAX(CASE WHEN typeprice.description = 'HC' AND typeprice.sentprice = 'สป' THEN CAST(typeprice.price AS DECIMAL(10,2)) ELSE 0 END) AS hc_sp
                    FROM card
                    INNER JOIN typeprice ON card.card_id = typeprice.card_id
                    WHERE typeprice.sentprice = 'สป'
                    GROUP BY card.price
                ) AS sp ON fwfdatabase.card_price = sp.price
                SET
                    fwfdatabase.man_ssj = ssj.man_ssj,
                    fwfdatabase.pp      = ssj.pp,
                    fwfdatabase.claim   = ssj.claim,
                    fwfdatabase.man_sp  = sp.man_sp,
                    fwfdatabase.hc_sp   = sp.hc_sp
            ")->execute();
        } catch (\Throwable $e) {
            Yii::$app->session->addFlash('warning', 'อัพเดตราคา fwfdatabase ไม่สำเร็จ: ' . $e->getMessage());
        }
    }

    private function normalizeText($value)
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string)$value);
        return $text === '' ? null : $text;
    }

    private function parseDecimal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $text = str_replace(',', '', trim((string)$value));
        return is_numeric($text) ? (float)$text : null;
    }

    private function parseExcelDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $text = trim((string)$value);
        if ($text === '') {
            return null;
        }

        $text = str_replace('.', '/', $text);
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $text, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = (int)$m[3];
            if ($year > 2400) {
                $year -= 543;
            } elseif ($year < 100) {
                $year += 2000;
            }
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        $timestamp = strtotime($text);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    public function actionCreate()
    {
        $model = new Insurancepolicies();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Insurancepolicies model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Insurancepolicies model.
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
     * Finds the Insurancepolicies model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Insurancepolicies the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Insurancepolicies::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
