<?php

namespace frontend\controllers;

use Yii;
use frontend\models\FwfFyClaim;
use frontend\models\User;
use yii\web\Controller;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * FwfFyClaimController
 * - index   : แสดงข้อมูลและฟอร์มกรอง
 * - import  : นำเข้าข้อมูลจาก fwfdatabase (ไม่ซ้ำ)
 * - clear   : ลบข้อมูลทั้งหมดในตาราง fwf_fy_claim
 */
class FwfFyClaimController extends BaseController
{
    // ── SQL ต้นฉบับที่ใช้ดึงข้อมูลและนำเข้า ─────────────────────────────
    private function buildSourceSql()
    {
        return "
        WITH RECURSIVE fy_range AS (
            SELECT
                CAST('2006-10-01' AS DATE) AS fy_start,
                CAST('2007-09-30' AS DATE) AS fy_end,
                2550                       AS fiscal_year
            UNION ALL
            SELECT
                DATE_ADD(fy_start, INTERVAL 1 YEAR),
                DATE_ADD(fy_end,   INTERVAL 1 YEAR),
                fiscal_year + 1
            FROM fy_range
            WHERE fy_start < '2043-10-01'
        )
        SELECT
            f.FundHolderID,
            h.hosname,
            f.CID,
            f.PeriodStartDate,
            f.PeriodEndDate,
            f.timeregis,
            fy.fiscal_year                                                          AS FiscalYear,
            fy.fy_start                                                             AS FY_Start,
            fy.fy_end                                                               AS FY_End,
            GREATEST(f.PeriodStartDate, fy.fy_start)                               AS Period_in_FY_Start,
            LEAST(f.PeriodEndDate, fy.fy_end)                                      AS Period_in_FY_End,
            DATEDIFF(
                LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                GREATEST(f.PeriodStartDate, fy.fy_start)
            )                                                                       AS Days_in_FY,
            DATEDIFF(f.PeriodEndDate, f.PeriodStartDate)                           AS DaysBetween,
            f.card_price,
            tp.claim,
            ROUND(tp.manage_ssj / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0), 4) AS manage_ssj_per_day,
            ROUND(tp.pp / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0), 4) AS pp_per_day,
            ROUND(tp.claim / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0), 4) AS claim_per_day,
            ROUND(
                tp.manage_ssj
                / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                * DATEDIFF(
                    LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                    GREATEST(f.PeriodStartDate, fy.fy_start)
                ),
            2)                                                                      AS manage_ssj_in_FY,
            ROUND(
                tp.pp
                / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                * DATEDIFF(
                    LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                    GREATEST(f.PeriodStartDate, fy.fy_start)
                ),
            2)                                                                      AS pp_in_FY,
            ROUND(
                tp.claim
                / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                * DATEDIFF(
                    LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                    GREATEST(f.PeriodStartDate, fy.fy_start)
                ),
            2)                                                                      AS claim_in_FY
        FROM fwfdatabase f
        LEFT JOIN chospital2 h  ON f.FundHolderID = h.hospcode
        LEFT JOIN (
        SELECT
            c.price AS price,
            MAX(CASE WHEN tp2.description = 'บริหารจัดการ' THEN tp2.price END) AS manage_ssj,
            MAX(CASE WHEN tp2.description = 'P&P' THEN tp2.price END) AS pp,
            MAX(CASE WHEN tp2.description = 'claim' THEN tp2.price END) AS claim
        FROM
            card c
        INNER JOIN
            typeprice tp2 ON c.card_id = tp2.card_id
        WHERE
            tp2.sentprice = 'สสจ'
        GROUP BY
            c.card_id,
            c.description,
            c.price
        ) tp ON tp.price = f.card_price
        JOIN fy_range fy
            ON f.PeriodStartDate <= fy.fy_end
            AND f.PeriodEndDate   >  fy.fy_start
        ORDER BY f.PeriodStartDate, f.CID, fy.fiscal_year
        ";
    }

    // ── actionIndex : แสดงข้อมูลในตาราง พร้อมกรองตามปีงบ / โรงพยาบาล ──────
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $checklogin = User::findOne(['user' => $session['user']]);
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        $get       = Yii::$app->request->get();
        $fyFilter  = (int)($get['fy'] ?? 0);
        $hosFilter = trim($get['hos'] ?? '');
        $message   = $get['msg'] ?? '';

        // นับแถวในตาราง
        $totalRows = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%fwf_fy_claim}}')->queryScalar();
        $lastImport = Yii::$app->db->createCommand('SELECT MAX(imported_at) FROM {{%fwf_fy_claim}}')->queryScalar();

        // รายการปีงบที่มีในตาราง
        $fyList = Yii::$app->db->createCommand('SELECT DISTINCT FiscalYear FROM {{%fwf_fy_claim}} ORDER BY FiscalYear DESC')->queryColumn();

        // รายการ FundHolderID ที่มีในตาราง
        $hosList = Yii::$app->db->createCommand(
            'SELECT DISTINCT FundHolderID, hosname FROM {{%fwf_fy_claim}} ORDER BY FundHolderID'
        )->queryAll();

        // Query แสดงผล (กรองตามเงื่อนไข)
        $where = '1=1';
        $params = [];
        if ($fyFilter) {
            $where .= ' AND FiscalYear = :fy';
            $params[':fy'] = $fyFilter;
        }
        if ($hosFilter !== '') {
            $where .= ' AND FundHolderID = :hos';
            $params[':hos'] = $hosFilter;
        }

        $countSql = "SELECT COUNT(*) FROM {{%fwf_fy_claim}} WHERE {$where}";
        $count    = Yii::$app->db->createCommand($countSql, $params)->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql'        => "SELECT * FROM {{%fwf_fy_claim}} WHERE {$where} ORDER BY FiscalYear DESC, FundHolderID, PeriodStartDate",
            'params'     => $params,
            'totalCount' => $count,
            'pagination' => ['pageSize' => 50],
            'sort'       => false,
        ]);

        // สรุปรายหน่วยบริการ (ตามตัวกรองที่เลือก)
        $summarySql = "
            SELECT
                FundHolderID,
                hosname,
                FiscalYear,
                COUNT(*)                            AS record_count,
                SUM(DaysBetween)                    AS total_days,
                SUM(Days_in_FY)                     AS total_days_in_fy,
                SUM(claim)                          AS total_claim,
                SUM(manage_ssj_in_FY)               AS total_manage_ssj_in_fy,
                SUM(pp_in_FY)                       AS total_pp_in_fy,
                SUM(claim_in_FY)                    AS total_claim_in_fy,
                MIN(PeriodStartDate)                AS min_start,
                MAX(PeriodEndDate)                  AS max_end
            FROM {{%fwf_fy_claim}}
            WHERE {$where}
            GROUP BY FundHolderID, hosname, FiscalYear
            ORDER BY FiscalYear DESC, FundHolderID
        ";
        $summaryRows = Yii::$app->db->createCommand($summarySql, $params)->queryAll();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'totalRows'    => $totalRows,
            'lastImport'   => $lastImport,
            'fyList'       => $fyList,
            'hosList'      => $hosList,
            'fyFilter'     => $fyFilter,
            'hosFilter'    => $hosFilter,
            'message'      => $message,
            'summaryRows'  => $summaryRows,
        ]);
    }

    // ── actionImport : นำเข้าข้อมูล (INSERT ... ON DUPLICATE KEY UPDATE เพื่อไม่ให้ซ้ำ) ─────────
    public function actionImport()
    {
        $session = Yii::$app->session;
        $checklogin = User::findOne(['user' => $session['user']]);
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        try {
            $sourceSql = $this->buildSourceSql();

            // INSERT IGNORE จะข้ามแถวที่มี unique key ซ้ำโดยอัตโนมัติ
            $insertSql = "
                INSERT INTO {{%fwf_fy_claim}}
                    (FundHolderID, hosname, CID, PeriodStartDate, PeriodEndDate,
                     timeregis, FiscalYear, FY_Start, FY_End,
                     Period_in_FY_Start, Period_in_FY_End,
                     Days_in_FY, DaysBetween,
                     card_price, claim, manage_ssj_per_day, pp_per_day, claim_per_day,
                     manage_ssj_in_FY, pp_in_FY, claim_in_FY)
                {$sourceSql}
                ON DUPLICATE KEY UPDATE
                    manage_ssj_per_day = VALUES(manage_ssj_per_day),
                    pp_per_day         = VALUES(pp_per_day),
                    manage_ssj_in_FY   = VALUES(manage_ssj_in_FY),
                    pp_in_FY           = VALUES(pp_in_FY),
                    imported_at        = CURRENT_TIMESTAMP
            ";

            $db = Yii::$app->db;
            $before = (int)$db->createCommand('SELECT COUNT(*) FROM {{%fwf_fy_claim}}')->queryScalar();
            $db->createCommand($insertSql)->execute();
            $after  = (int)$db->createCommand('SELECT COUNT(*) FROM {{%fwf_fy_claim}}')->queryScalar();

            $inserted = $after - $before;
            $msg = "นำเข้าสำเร็จ: เพิ่มข้อมูล {$inserted} แถว (ทั้งหมด {$after} แถวในตาราง)";
        } catch (\Exception $e) {
            $msg = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }

        return $this->redirect(['/fwf-fy-claim/index', 'msg' => $msg]);
    }

    // ── actionExport : ส่งออก Excel .xlsx ตามตัวกรอง ──────────────────────────
    public function actionExport()
    {
        $session = Yii::$app->session;
        $checklogin = User::findOne(['user' => $session['user']]);
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        // เพิ่ม memory สำหรับสร้าง xlsx ข้อมูลมาก
        ini_set('memory_limit', '2G');
        set_time_limit(300);

        $get       = Yii::$app->request->get();
        $fyFilter  = (int)($get['fy'] ?? 0);
        $hosFilter = trim($get['hos'] ?? '');

        // ── สร้าง WHERE clause ────────────────────────────────────────────────
        $where  = '1=1';
        $params = [];
        if ($fyFilter) {
            $where .= ' AND FiscalYear = :fy';
            $params[':fy'] = $fyFilter;
        }
        if ($hosFilter !== '') {
            $where .= ' AND FundHolderID = :hos';
            $params[':hos'] = $hosFilter;
        }

        $db = Yii::$app->db;

        // ── Raw data ──────────────────────────────────────────────────────────
        $rows = $db->createCommand(
            "SELECT FundHolderID, hosname, CID, PeriodStartDate, PeriodEndDate,
                    timeregis, FiscalYear, FY_Start, FY_End,
                    Period_in_FY_Start, Period_in_FY_End,
                    Days_in_FY, DaysBetween,
                    card_price, claim, claim_per_day, claim_in_FY
             FROM {{%fwf_fy_claim}}
             WHERE {$where}
             ORDER BY FiscalYear DESC, FundHolderID, PeriodStartDate",
            $params
        )->queryAll();

        // ── Summary data ──────────────────────────────────────────────────────
        $summaryRows = $db->createCommand(
            "SELECT FundHolderID, hosname, FiscalYear,
                    COUNT(*)             AS record_count,
                    SUM(Days_in_FY)      AS total_days_in_fy,
                    SUM(manage_ssj_in_FY) AS total_manage_ssj_in_fy,
                    SUM(pp_in_FY)        AS total_pp_in_fy,
                    SUM(claim_in_FY)     AS total_claim_in_fy
             FROM {{%fwf_fy_claim}}
             WHERE {$where}
             GROUP BY FundHolderID, hosname, FiscalYear
             ORDER BY FiscalYear DESC, FundHolderID",
            $params
        )->queryAll();

        // ── สร้าง Workbook โดยปิด calculation cache เพื่อประหยัด memory ───
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getCalculationEngine()->disableCalculationCache();
        $spreadsheet->getProperties()
            ->setTitle('FWF Claim แยกปีงบ')
            ->setCreator('CFPS');

        // ── สไตล์ header / total (ใช้เฉพาะแถว header+total ไม่ทำ per-row) ──
        $hdrStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E7D32']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sumHdrStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A237E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $totalStyle = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C4']],
        ];

        // ════════════════════════════════════════════════════════════════════
        // Sheet 1: Raw data (ไม่ apply style ทีละ row เพื่อลด memory)
        // ════════════════════════════════════════════════════════════════════
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('ข้อมูล Record');

        $headers1 = [
            'รหัส รพ.', 'ชื่อ รพ.', 'CID',
            'วันเริ่มงวด', 'วันสิ้นงวด', 'งวด',
            'ปีงบ', 'เริ่มต้นปีงบ', 'สิ้นสุดปีงบ',
            'ช่วงทับซ้อน (เริ่ม)', 'ช่วงทับซ้อน (สิ้น)',
            'วันในปีงบ', 'วันในงวด',
            'ราคาบัตร', 'Claim', 'Claim/วัน', 'Claim ในปีงบ',
        ];
        foreach ($headers1 as $i => $h) {
            $ws1->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $ws1->getStyle('A1:Q1')->applyFromArray($hdrStyle);

        // pre-คำนวณ totals ขณะ loop (แทน SUM formula)
        $totClaimFY = 0;
        $r = 2;
        foreach ($rows as $row) {
            $ws1->setCellValueByColumnAndRow(1,  $r, $row['FundHolderID']);
            $ws1->setCellValueByColumnAndRow(2,  $r, $row['hosname']);
            $ws1->setCellValueByColumnAndRow(3,  $r, $row['CID']);
            $ws1->setCellValueByColumnAndRow(4,  $r, $row['PeriodStartDate']);
            $ws1->setCellValueByColumnAndRow(5,  $r, $row['PeriodEndDate']);
            $ws1->setCellValueByColumnAndRow(6,  $r, $row['timeregis']);
            $ws1->setCellValueByColumnAndRow(7,  $r, (int)$row['FiscalYear']);
            $ws1->setCellValueByColumnAndRow(8,  $r, $row['FY_Start']);
            $ws1->setCellValueByColumnAndRow(9,  $r, $row['FY_End']);
            $ws1->setCellValueByColumnAndRow(10, $r, $row['Period_in_FY_Start']);
            $ws1->setCellValueByColumnAndRow(11, $r, $row['Period_in_FY_End']);
            $ws1->setCellValueByColumnAndRow(12, $r, (int)$row['Days_in_FY']);
            $ws1->setCellValueByColumnAndRow(13, $r, (int)$row['DaysBetween']);
            $ws1->setCellValueByColumnAndRow(14, $r, $row['card_price']    !== null ? (float)$row['card_price']    : '');
            $ws1->setCellValueByColumnAndRow(15, $r, $row['claim']         !== null ? (float)$row['claim']         : '');
            $ws1->setCellValueByColumnAndRow(16, $r, $row['claim_per_day'] !== null ? (float)$row['claim_per_day'] : '');
            $ws1->setCellValueByColumnAndRow(17, $r, $row['claim_in_FY']   !== null ? (float)$row['claim_in_FY']   : '');
            $totClaimFY += $row['claim_in_FY'] !== null ? (float)$row['claim_in_FY'] : 0;
            $r++;
        }

        // apply number format เป็น range แทนทีละ row
        if ($r > 2) {
            $ws1->getStyle('G2:G'.($r-1))->getNumberFormat()->setFormatCode('0');
            $ws1->getStyle('L2:M'.($r-1))->getNumberFormat()->setFormatCode('0');
            $ws1->getStyle('N2:Q'.($r-1))->getNumberFormat()->setFormatCode('#,##0.00');

            // แถว Total
            $ws1->setCellValue('A'.$r, 'รวมทั้งหมด');
            $ws1->setCellValue('Q'.$r, round($totClaimFY, 2));
            $ws1->getStyle('A'.$r.':Q'.$r)->applyFromArray($totalStyle);
            $ws1->getStyle('Q'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // auto width เฉพาะ column ที่ต้องการ
        foreach (range('A', 'Q') as $col) {
            $ws1->getColumnDimension($col)->setAutoSize(true);
        }

        // ════════════════════════════════════════════════════════════════════
        // Sheet 2: Summary
        // ════════════════════════════════════════════════════════════════════
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('สรุปรายหน่วยบริการ');

        $headers2 = ['รหัส รพ.', 'ชื่อ รพ.', 'ปีงบ', 'จำนวน Record', 'บริหารจัดการ', 'PP', 'Claim ในปีงบ'];
        foreach ($headers2 as $i => $h) {
            $ws2->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $ws2->getStyle('A1:G1')->applyFromArray($sumHdrStyle);

        $r2 = 2;
        $gtClaimFY = 0; $gtManage = 0; $gtPP = 0;
        foreach ($summaryRows as $sr) {
            $ws2->setCellValueByColumnAndRow(1, $r2, $sr['FundHolderID']);
            $ws2->setCellValueByColumnAndRow(2, $r2, $sr['hosname']);
            $ws2->setCellValueByColumnAndRow(3, $r2, (int)$sr['FiscalYear']);
            $ws2->setCellValueByColumnAndRow(4, $r2, (int)$sr['record_count']);
            $ws2->setCellValueByColumnAndRow(5, $r2, (float)$sr['total_manage_ssj_in_fy']);
            $ws2->setCellValueByColumnAndRow(6, $r2, (float)$sr['total_pp_in_fy']);
            $ws2->setCellValueByColumnAndRow(7, $r2, (float)$sr['total_claim_in_fy']);
            $gtManage += (float)$sr['total_manage_ssj_in_fy'];
            $gtPP += (float)$sr['total_pp_in_fy'];
            $gtClaimFY += (float)$sr['total_claim_in_fy'];
            $r2++;
        }
        if ($r2 > 2) {
            $ws2->getStyle('E2:G'.($r2-1))->getNumberFormat()->setFormatCode('#,##0.00');
            $ws2->setCellValue('A'.$r2, 'รวมทั้งหมด');
            $ws2->setCellValue('E'.$r2, round($gtManage, 2));
            $ws2->setCellValue('F'.$r2, round($gtPP, 2));
            $ws2->setCellValue('G'.$r2, round($gtClaimFY, 2));
            $ws2->getStyle('A'.$r2.':G'.$r2)->applyFromArray($totalStyle);
            $ws2->getStyle('E'.$r2.':G'.$r2)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $ws2->getColumnDimension($col)->setAutoSize(true);
        }

        // ── เขียน temp file แล้วส่ง (หลีกเลี่ยง ob_start กินซ้ำ) ────────────
        $fyLabel  = $fyFilter  ? "ปีงบ{$fyFilter}_" : '';
        $hosLabel = $hosFilter ? "รพ{$hosFilter}_"  : '';
        $filename = 'FWF_Claim_' . $fyLabel . $hosLabel . date('Ymd_His') . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'fwf_') . '.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($tmpFile);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return Yii::$app->response->sendFile($tmpFile, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline'   => false,
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmpFile) {
            @unlink($tmpFile);
        });
    }

    // ── actionClear : ลบข้อมูลทั้งหมดในตาราง ──────────────────────────────
    public function actionClear()
    {
        $session = Yii::$app->session;
        $checklogin = User::findOne(['user' => $session['user']]);
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        // รับ POST เท่านั้น (ป้องกัน CSRF)
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/fwf-fy-claim/index']);
        }

        try {
            $db = Yii::$app->db;
            $before = (int)$db->createCommand('SELECT COUNT(*) FROM {{%fwf_fy_claim}}')->queryScalar();
            $db->createCommand('DELETE FROM {{%fwf_fy_claim}}')->execute();
            $msg = "เคลียข้อมูลสำเร็จ: ลบ {$before} แถว";
        } catch (\Exception $e) {
            $msg = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }

        return $this->redirect(['/fwf-fy-claim/index', 'msg' => $msg]);
    }
}
