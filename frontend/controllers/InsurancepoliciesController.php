<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Insurancepolicies;
use frontend\models\InsurancepoliciesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;
use yii\db\Query;
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
                    'import'          => ['POST'],
                ],
            ],
        ];
    }

    public function actionClearPolicies()
    {
        $isAdmin = $this->isProvincialAdmin();

        if ($isAdmin) {
            Yii::$app->facilityAccess->requirePermission('manageInsurancePolicies');
            $deleted = Yii::$app->db->createCommand()->delete(
                'insurance_policies',
                ['agency_code' => Yii::$app->facilityAccess->provincialFacilityCodes()]
            )->execute();
        } else {
            $codes = Yii::$app->facilityAccess->visibleFacilityCodes(true);
            $deleted = Yii::$app->db->createCommand()->delete(
                'insurance_policies',
                ['agency_code' => $codes]
            )->execute();
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
        $hoscode = trim((string)$session['hoscode']);
        $isAdmin = $this->isProvincialAdmin();
        if ($hoscode === '') {
            throw new ForbiddenHttpException('ไม่พบรหัสหน่วยงานใน session');
        }

        $today = new \DateTimeImmutable('today');
        $currentFiscalYear = (int)$today->format('Y') + ((int)$today->format('n') >= 10 ? 1 : 0) + 543;
        $yearParam = Yii::$app->request->get('fiscal_year', (string)$currentFiscalYear);
        $monthParam = Yii::$app->request->get('month', '');
        $allParam = Yii::$app->request->get('all', '');
        $agencyParam = Yii::$app->request->get('agency', '');
        if (!is_string($yearParam) || !preg_match('/^\d{4,5}$/D', $yearParam)
            || (int)$yearParam < 2444 || (int)$yearParam > 10541
            || !is_string($monthParam) || ($monthParam !== '' && !preg_match('/^(?:[1-9]|1[0-2])$/D', $monthParam))
            || !is_string($allParam) || !in_array($allParam, ['', '1'], true)
            || ($allParam === '1' && $monthParam !== '')) {
            throw new BadRequestHttpException('ปีงบประมาณหรือเดือนที่เลือกไม่ถูกต้อง');
        }
        if (!is_string($agencyParam) || mb_strlen($agencyParam) > 20) {
            throw new BadRequestHttpException('รหัสหน่วยงานไม่ถูกต้อง');
        }
        if (!$isAdmin && $agencyParam !== '') {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์เลือกหน่วยงานอื่น');
        }
        $agencies = [];
        $agencyCodes = $isAdmin
            ? Yii::$app->facilityAccess->provincialFacilityCodes()
            : Yii::$app->facilityAccess->visibleFacilityCodes();
        if ($isAdmin) {
            $agencyRows = (new Query())->select([
                'agency_code',
                'agency_name' => new Expression('MAX(agency_name)'),
            ])->from('insurance_policies')
                ->where(['agency_code' => $agencyCodes])
                ->andWhere(['<>', 'agency_code', ''])
                ->groupBy('agency_code')
                ->orderBy(['agency_code' => SORT_ASC])->all();
            foreach ($agencyRows as $agencyRow) {
                $code = $agencyRow['agency_code'];
                $agencies[$code] = $code . ($agencyRow['agency_name'] ? ' - ' . $agencyRow['agency_name'] : '');
            }
            if ($agencyParam !== '' && !array_key_exists($agencyParam, $agencies)) {
                throw new BadRequestHttpException('ไม่พบหน่วยงานที่เลือก');
            }
        }
        $fiscalYear = (int)$yearParam;
        $month = $monthParam === '' ? null : (int)$monthParam;
        $showAll = $allParam === '1';
        $year = $fiscalYear - 543;
        $start = null;
        $end = null;
        if (!$showAll) {
            $start = $month === null
                ? new \DateTimeImmutable(sprintf('%04d-10-01', $year - 1))
                : new \DateTimeImmutable(sprintf('%04d-%02d-01', $year - ($month >= 10 ? 1 : 0), $month));
            $end = $month === null ? $start->modify('+1 year') : $start->modify('+1 month');
        }

        $baseQuery = (new Query())->from('insurance_policies');
        $baseQuery->andWhere(['agency_code' => $agencyCodes]);
        if ($isAdmin && $agencyParam !== '') {
            $baseQuery->andWhere(['agency_code' => $agencyParam]);
        }
        if ($start !== null) {
            $baseQuery->andWhere(['>=', 'purchase_date', $start->format('Y-m-d')])
                ->andWhere(['<', 'purchase_date', $end->format('Y-m-d')]);
        }
        $aggregates = ['cnt' => new Expression('COUNT(*)'), 'total_amount' => new Expression('COALESCE(SUM(card_price), 0)')];
        $totals = (clone $baseQuery)->select($aggregates)->one();
        $byPrice = (clone $baseQuery)->select(array_merge(['card_price', 'policy_status'], $aggregates))
            ->groupBy(['card_price', 'policy_status'])->orderBy(['card_price' => SORT_ASC, 'policy_status' => SORT_ASC])->all();
        $byAgency = (clone $baseQuery)->select(array_merge(['agency_code', 'agency_name', 'card_price'], $aggregates))
            ->groupBy(['agency_code', 'agency_name', 'card_price'])->orderBy(['agency_name' => SORT_ASC, 'card_price' => SORT_ASC])->all();
        $byMonth = (clone $baseQuery)
            ->select(array_merge(['ym' => new Expression("DATE_FORMAT(purchase_date, '%Y-%m')")], $aggregates))
            ->andWhere(['not', ['purchase_date' => null]])
            ->groupBy('ym')->orderBy(['ym' => SORT_DESC])->limit(24)->all();

        return $this->render('dashboard', [
            'totals'    => $totals,
            'byPrice'   => $byPrice,
            'byAgency'  => $byAgency,
            'byMonth'   => $byMonth,
            'isAdmin'   => $isAdmin,
            'agencies' => $agencies,
            'selectedAgency' => $agencyParam,
            'fiscalYear' => $fiscalYear,
            'currentFiscalYear' => $currentFiscalYear,
            'month' => $month,
            'showAll' => $showAll,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * รายงานสรุปการขายบัตร แยกตามหน่วยบริการ (CUP) และราคาบัตร พร้อมการจัดสรรเงิน (จากตาราง fwfdatabase)
     * ตัวกรอง date1/date2 เป็น พ.ศ. เหมือนหน้าแรก; export=1 ส่งออกเป็น Excel
     */
    public function actionReport()
    {
        $hoscode = trim((string)Yii::$app->session['hoscode']);
        if ($hoscode === '') {
            throw new ForbiddenHttpException('ไม่พบรหัสหน่วยงานใน session');
        }

        $request = Yii::$app->request;
        $today = new \DateTimeImmutable('today');
        $date1 = $request->get('date1', '');
        $date2 = $request->get('date2', '');
        if (!is_string($date1) || !is_string($date2)) {
            throw new BadRequestHttpException('ช่วงวันที่ไม่ถูกต้อง');
        }
        if ($date1 === '' && $date2 === '') {
            $fy = (int)$today->format('Y') + ((int)$today->format('n') >= 10 ? 1 : 0);
            $start = new \DateTimeImmutable(($fy - 1) . '-10-01');
            $end = new \DateTimeImmutable($fy . '-09-30');
        } else {
            $start = $this->parseThaiDate($date1);
            $end = $this->parseThaiDate($date2);
            if ($start > $end) {
                throw new BadRequestHttpException('วันที่เริ่มต้นต้องไม่เกินวันที่สิ้นสุด');
            }
        }

        $visible = $this->isProvincialAdmin()
            ? Yii::$app->facilityAccess->provincialFacilityCodes()
            : Yii::$app->facilityAccess->visibleFacilityCodes();

        $hospitals = (new Query())->select(['hospcode', 'hosname'])->from('chospital2')
            ->where(['provcode' => '82'])
            ->andWhere('hospcode = cupcode')
            ->andWhere(['<>', 'hospcode', '00064'])
            ->andWhere(['hospcode' => $visible])
            ->orderBy(['hospcode' => SORT_ASC])->all();

        $cards = (new Query())->select(['card_id', 'description', 'price' => new Expression('CAST(price AS DECIMAL(10,2))')])
            ->from('card')->orderBy(['price' => SORT_ASC, 'card_id' => SORT_ASC])->all();

        $sum = static fn(string $col) => new Expression("COALESCE(SUM($col), 0)");
        $rows = (new Query())->select([
            'FundHolderID',
            'card_price',
            'cnt' => new Expression('COUNT(*)'),
            'amount' => $sum('card_price'),
            'unit' => $sum('claim'),
            'pp' => $sum('pp'),
            'man_ssj' => $sum('man_ssj'),
            'hc_sp' => $sum('hc_sp'),
            'man_sp' => $sum('man_sp'),
        ])->from('fwfdatabase')
            ->where(['FundHolderID' => array_column($hospitals, 'hospcode')])
            ->andWhere(['>=', 'PeriodStartDate', $start->format('Y-m-d')])
            ->andWhere(['<=', 'PeriodStartDate', $end->format('Y-m-d')])
            ->groupBy(['FundHolderID', 'card_price'])->all();

        $priceToCard = [];
        foreach ($cards as $c) {
            $priceToCard[number_format((float)$c['price'], 2, '.', '')] = (int)$c['card_id'];
        }
        $data = [];
        foreach ($rows as $r) {
            $cid = $priceToCard[number_format((float)$r['card_price'], 2, '.', '')] ?? 0;
            $d = &$data[$r['FundHolderID']];
            $d['cards'][$cid] = ($d['cards'][$cid] ?? 0) + (int)$r['cnt'];
            foreach (['amount', 'unit', 'pp', 'man_ssj', 'hc_sp', 'man_sp'] as $k) {
                $d[$k] = ($d[$k] ?? 0) + (float)$r[$k];
            }
            unset($d);
        }

        if ($request->get('export') === '1') {
            return $this->exportReport($hospitals, $cards, $data, $start, $end);
        }

        return $this->render('report', [
            'hospitals' => $hospitals,
            'cards' => $cards,
            'data' => $data,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /** แปลงวันที่ พ.ศ. (yyyy-mm-dd หรือ yyyy/mm/dd) เป็น DateTimeImmutable (ค.ศ.) */
    private function parseThaiDate(string $value): \DateTimeImmutable
    {
        if (!preg_match('#^(\d{4})[-/](\d{2})[-/](\d{2})$#D', $value, $m)) {
            throw new BadRequestHttpException('รูปแบบวันที่ไม่ถูกต้อง');
        }
        $year = (int)$m[1] - 543;
        if ($year < 1 || !checkdate((int)$m[2], (int)$m[3], $year)) {
            throw new BadRequestHttpException('วันที่ไม่ถูกต้อง');
        }
        return new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $m[2], $m[3]));
    }

    private function exportReport(array $hospitals, array $cards, array $data, \DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('สรุปการขายบัตร');
        $thai = static fn(\DateTimeImmutable $d) => $d->format('d/m/') . ((int)$d->format('Y') + 543);

        $ws->setCellValueExplicit('A1', 'รายงานสรุปการขายบัตรต่างด้าว', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $ws->setCellValueExplicit('A2', 'วันที่เริ่มงวด ' . $thai($start) . ' - ' . $thai($end), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $headers = ['ลำดับ', 'รหัส', 'หน่วยบริการ'];
        foreach ($cards as $c) {
            $headers[] = number_format((float)$c['price']);
        }
        $headers = array_merge($headers, [
            'อื่นๆ', 'รวม (ใบ)', 'รวมเงิน (บาท)', 'หน่วยบริการ (Claim)',
            'สสจ. PP', 'สสจ. บริหาร', 'สป. HC', 'สป. บริหาร',
        ]);
        $colIds = array_merge(array_map('intval', array_column($cards, 'card_id')), [0]);
        $moneyKeys = ['amount', 'unit', 'pp', 'man_ssj', 'hc_sp', 'man_sp'];

        $hr = 4;
        foreach ($headers as $i => $h) {
            $ws->setCellValueExplicit([$i + 1, $hr], (string)$h, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        $tot = array_fill_keys($moneyKeys, 0);
        $totCards = [];
        $totCount = 0;
        $row = $hr;
        foreach ($hospitals as $i => $h) {
            $row++;
            $d = $data[$h['hospcode']] ?? [];
            $col = 1;
            $ws->setCellValue([$col++, $row], $i + 1);
            $ws->setCellValueExplicit([$col++, $row], (string)$h['hospcode'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $ws->setCellValueExplicit([$col++, $row], (string)$h['hosname'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            foreach ($colIds as $cid) {
                $n = $d['cards'][$cid] ?? 0;
                $totCards[$cid] = ($totCards[$cid] ?? 0) + $n;
                $ws->setCellValue([$col++, $row], $n);
            }
            $count = array_sum($d['cards'] ?? []);
            $totCount += $count;
            $ws->setCellValue([$col++, $row], $count);
            foreach ($moneyKeys as $k) {
                $tot[$k] += $d[$k] ?? 0;
                $ws->setCellValue([$col++, $row], $d[$k] ?? 0);
            }
        }
        $row++;
        $col = 1;
        $ws->setCellValueExplicit([3, $row], 'รวมทั้งหมด', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $col = 4;
        foreach ($colIds as $cid) {
            $ws->setCellValue([$col++, $row], $totCards[$cid] ?? 0);
        }
        $ws->setCellValue([$col++, $row], $totCount);
        foreach ($moneyKeys as $k) {
            $ws->setCellValue([$col++, $row], $tot[$k]);
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $ws->getStyle("A{$hr}:{$lastCol}{$hr}")->getFont()->setBold(true);
        $ws->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $moneyStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers) - 5);
        $ws->getStyle("{$moneyStart}" . ($hr + 1) . ":{$lastCol}{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        for ($i = 1; $i <= count($headers); $i++) {
            $ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'rpt');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);
        $content = file_get_contents($tmp);
        unlink($tmp);

        return Yii::$app->response->sendContentAsFile(
            $content,
            'fwf_card_sales_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.xlsx',
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
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
        Yii::$app->facilityAccess->requirePermission('manageInsurancePolicies');

        $params = Yii::$app->request->get();
        $xlsid = (int)($params['id'] ?? $id);
        if (empty($xlsid)) {
            Yii::$app->session->addFlash('error', 'ไม่พบรหัสไฟล์นำเข้า');
            return $this->redirect(['insurancepolicies/index']);
        }

        $xlsdata = (new \yii\db\Query())
            ->from('xlsfileuploads')
            ->where(['xlsid' => $xlsid])
            ->one();

        if (empty($xlsdata) || empty($xlsdata['exsfile'])) {
            Yii::$app->session->addFlash('error', 'ไม่พบข้อมูลไฟล์ที่ต้องการนำเข้า');
            return $this->redirect(['insurancepolicies/index']);
        }
        $ownershipError = $this->checkImportOwnership($xlsdata, []);
        if ($ownershipError !== null || $xlsdata['typefileupload'] !== 'insurance') {
            Yii::$app->session->addFlash('error', $ownershipError ?? 'ประเภทไฟล์ไม่รองรับการนำเข้ากรมธรรม์');
            return $this->redirect(['insurancepolicies/index']);
        }

        Yii::$app->db->createCommand()->update(
            'xlsfileuploads',
            ['startdatetime' => date('Y-m-d H:i:s')],
            ['xlsid' => $xlsid]
        )->execute();

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

        // รูปแบบใหม่มีข้อมูลวันเกิด ที่อยู่ และการจัดสรรเงินเพิ่มจากรูปแบบเดิม
        $isNewFormat = false;
        if ($headerRow > 0) {
            $headerCols = $sheet->rangeToArray('A' . $headerRow . ':AR' . $headerRow, null, true, true, false);
            $headerData = $headerCols[0] ?? [];
            $isNewFormat = (isset($headerData[11]) && mb_strpos((string)$headerData[11], 'เกิด') !== false);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($row = $startRow; $row <= $highestRow; $row++) {
                $cols = $sheet->rangeToArray('A' . $row . ':AV' . $row, null, true, true, false);
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
                    // รูปแบบใหม่: A-AR หรือ A-AV เมื่อมีข้อมูลนายจ้าง
                    $hnCode        = $this->normalizeText($rowData[13] ?? null);
                    $purchaseDate  = $this->parsePolicyDate($rowData[22] ?? null);
                    $effectiveDate = $this->parsePolicyDate($rowData[23] ?? null);
                    $expiryDate    = $this->parsePolicyDate($rowData[24] ?? null);
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
                    $purchaseDate  = $this->parsePolicyDate($rowData[12] ?? null);
                    $effectiveDate = $this->parsePolicyDate($rowData[13] ?? null);
                    $expiryDate    = $this->parsePolicyDate($rowData[14] ?? null);
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

                if ($agencyCode !== null) {
                    $agencyCode = trim($agencyCode);
                    if (ctype_digit($agencyCode)) {
                        $agencyCode = str_pad($agencyCode, 5, '0', STR_PAD_LEFT);
                    }
                    if (!Yii::$app->facilityAccess->canAccessFacility($agencyCode)) {
                        throw new \RuntimeException('รหัสหน่วยบริการอยู่นอกขอบเขตจังหวัดในแถว ' . $row);
                    }
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

                if ($isNewFormat) {
                    $birthDate = $this->parsePolicyDate($rowData[11] ?? null);
                    if ($this->normalizeText($rowData[11] ?? null) !== null && $birthDate === null) {
                        throw new \RuntimeException('วันเกิดไม่ถูกต้องในแถว ' . $row);
                    }
                    $insertData += [
                        'birth_date' => $birthDate,
                        'age_at_purchase' => $this->normalizeText($rowData[12] ?? null),
                        'house_no' => $this->normalizeText($rowData[14] ?? null),
                        'village_no' => $this->normalizeText($rowData[15] ?? null),
                        'alley' => $this->normalizeText($rowData[16] ?? null),
                        'road' => $this->normalizeText($rowData[17] ?? null),
                        'address_province' => $this->normalizeText($rowData[18] ?? null),
                        'subdistrict' => $this->normalizeText($rowData[19] ?? null),
                        'district' => $this->normalizeText($rowData[20] ?? null),
                        'postal_code' => $this->normalizeText($rowData[21] ?? null),
                        'reference_no' => $this->normalizeText($rowData[26] ?? null),
                        'occupation' => $this->normalizeText($rowData[44] ?? null),
                        'employer_name' => $this->normalizeText($rowData[45] ?? null),
                        'employer_address' => $this->normalizeText($rowData[46] ?? null),
                        'employer_phone' => $this->normalizeText($rowData[47] ?? null),
                    ];
                    $allocationColumns = [
                        28 => 'allocation_service_unit',
                        29 => 'allocation_provincial_pp',
                        30 => 'allocation_provincial_admin',
                        31 => 'allocation_provincial_total',
                        32 => 'allocation_central_hc',
                        33 => 'allocation_central_admin',
                        34 => 'allocation_central_total',
                    ];
                    foreach ($allocationColumns as $index => $column) {
                        $value = $rowData[$index] ?? null;
                        $amount = $this->parseDecimal($value);
                        if ($this->normalizeText($value) !== null && $amount === null) {
                            throw new \RuntimeException('ยอดจัดสรรไม่ถูกต้องในแถว ' . $row . ' คอลัมน์ ' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1));
                        }
                        $insertData[$column] = $amount;
                    }
                }

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

    private function parsePolicyDate($value)
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

        if ($model->load(Yii::$app->request->post())) {
            if (!$this->isProvincialAdmin()) {
                $model->agency_code = trim((string)Yii::$app->session->get('hoscode'));
                $model->agency_name = (string)(new Query())
                    ->select('hosname')
                    ->from('chospital2')
                    ->where(['hospcode' => $model->agency_code])
                    ->scalar();
            } elseif (!Yii::$app->facilityAccess->canAccessFacility((string)$model->agency_code, true)) {
                throw new ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
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
        $model = $this->findModel($id, true);

        if ($model->load(Yii::$app->request->post())) {
            if (!$this->isProvincialAdmin()) {
                $model->agency_code = trim((string)Yii::$app->session->get('hoscode'));
            } elseif (!Yii::$app->facilityAccess->canAccessFacility((string)$model->agency_code, true)) {
                throw new ForbiddenHttpException('ไม่มีสิทธิ์จัดการข้อมูลหน่วยบริการนี้');
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
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
        $model = $this->findModel($id, true);
        if (!$model->delete()) {
            Yii::error('ไม่สามารถลบกรมธรรม์ id=' . $model->id, __METHOD__);
            throw new \yii\web\ServerErrorHttpException('ลบข้อมูลไม่สำเร็จ');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Insurancepolicies model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Insurancepolicies the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, bool $forWrite = false)
    {
        if (($model = Insurancepolicies::findOne($id)) !== null) {
            if (Yii::$app->facilityAccess->canAccessFacility((string)$model->agency_code, $forWrite)) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
