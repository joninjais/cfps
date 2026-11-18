<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * FwfFyClaimController
 * - index   : ซิงค์ข้อมูลใหม่อัตโนมัติ + กรองตามช่วงวันที่ พ.ศ. / ปีงบ / รายเดือน + แสดงเฉพาะตารางสรุปหน่วยบริการ
 * - export  : ส่งออกตารางสรุปรายหน่วยบริการเป็น Excel (.xlsx) ตามช่วงวันที่ที่เลือก
 * - import  : นำเข้าข้อมูลด้วยตนเอง (คงไว้รองรับสิทธิ์ผู้ดูแล)
 * - clear   : ล้างตารางพักและซิงค์ใหม่ตามสูตรต้นฉบับอัตโนมัติ
 */
class FwfFyClaimController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'import' => ['POST'],
                    'clear' => ['POST'],
                ],
            ],
        ];
    }

    // ── SQL ต้นฉบับ 100% ที่ใช้ดึงข้อมูลและคำนวณอัตราจากตาราง card + typeprice ──
    private function buildSourceSql(?int $afterFwfId = null): string
    {
        $facilityCodesSql = $this->facilityCodesSql();
        $incrementalWhere = ($afterFwfId !== null && $afterFwfId > 0)
            ? ' AND f.fwfid > ' . (int)$afterFwfId
            : '';

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
            fy.fiscal_year                                                            AS FiscalYear,
            fy.fy_start                                                               AS FY_Start,
            fy.fy_end                                                                 AS FY_End,
            GREATEST(f.PeriodStartDate, fy.fy_start)                                  AS Period_in_FY_Start,
            LEAST(f.PeriodEndDate, fy.fy_end)                                         AS Period_in_FY_End,
            DATEDIFF(
                LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                GREATEST(f.PeriodStartDate, fy.fy_start)
            )                                                                         AS Days_in_FY,
            DATEDIFF(f.PeriodEndDate, f.PeriodStartDate)                              AS DaysBetween,
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
            2)                                                                        AS manage_ssj_in_FY,
            ROUND(
                tp.pp
                / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                * DATEDIFF(
                    LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                    GREATEST(f.PeriodStartDate, fy.fy_start)
                ),
            2)                                                                        AS pp_in_FY,
            ROUND(
                tp.claim
                / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                * DATEDIFF(
                    LEAST(f.PeriodEndDate,   DATE_ADD(fy.fy_end, INTERVAL 1 DAY)),
                    GREATEST(f.PeriodStartDate, fy.fy_start)
                ),
            2)                                                                        AS claim_in_FY
        FROM fwfdatabase f
        LEFT JOIN chospital2 h ON f.FundHolderID = h.hospcode
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
            AND f.PeriodEndDate  >  fy.fy_start
        WHERE f.FundHolderID IN ({$facilityCodesSql}){$incrementalWhere}
        ORDER BY f.PeriodStartDate, f.CID, fy.fiscal_year
        ";
    }

    private function executeSyncInsert(?int $afterFwfId = null): void
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }
        $sourceSql = $this->buildSourceSql($afterFwfId);

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

        Yii::$app->db->createCommand($insertSql)->execute();
    }

    private function ensureAutoSync(): void
    {
        try {
            $db = Yii::$app->db;
            $facilityCodesSql = $this->facilityCodesSql();
            $scopeKey   = md5($facilityCodesSql);
            $runtimeDir = Yii::getAlias('@frontend/runtime');
            if (!is_dir($runtimeDir)) {
                mkdir($runtimeDir, 0775, true);
            }
            $stateFile = $runtimeDir . '/fwf_fy_autosync_v2.json';
            $lockFile  = $runtimeDir . '/fwf_fy_autosync.lock';

            $srcStat = $db->createCommand("
                SELECT
                    COALESCE(MAX(fwfid), 0) AS max_id,
                    COUNT(*) AS total_rows,
                    (SELECT CONCAT(COUNT(*), ':', COALESCE(SUM(CAST(price AS DECIMAL(12,2))), 0))
                     FROM typeprice WHERE sentprice = 'สสจ') AS tp_sig
                FROM fwfdatabase
                WHERE FundHolderID IN ({$facilityCodesSql})
            ")->queryOne();

            $currentMaxId = (int)($srcStat['max_id'] ?? 0);
            $currentTotal = (int)($srcStat['total_rows'] ?? 0);
            $currentTpSig = (string)($srcStat['tp_sig'] ?? '');

            if ($currentMaxId === 0 || $currentTotal === 0) {
                return;
            }

            $claimCount = (int)$db->createCommand(
                "SELECT COUNT(*) FROM {{%fwf_fy_claim}} WHERE FundHolderID IN ({$facilityCodesSql})"
            )->queryScalar();

            $allStates = (is_file($stateFile) && is_readable($stateFile))
                ? json_decode((string)file_get_contents($stateFile), true)
                : [];
            if (!is_array($allStates)) {
                $allStates = [];
            }

            $saved = $allStates[$scopeKey] ?? null;

            if (!is_array($saved) && $claimCount > 0) {
                $detectedLastId = (int)$db->createCommand("
                    SELECT COALESCE(MAX(f.fwfid), 0)
                    FROM fwfdatabase f
                    INNER JOIN (
                        SELECT FundHolderID, CID, PeriodStartDate, PeriodEndDate
                        FROM {{%fwf_fy_claim}}
                        WHERE FundHolderID IN ({$facilityCodesSql})
                        ORDER BY id DESC
                        LIMIT 500
                    ) c ON c.CID = f.CID
                       AND c.FundHolderID = f.FundHolderID
                       AND c.PeriodStartDate = f.PeriodStartDate
                       AND c.PeriodEndDate = f.PeriodEndDate
                ")->queryScalar();

                if ($detectedLastId > 0) {
                    $saved = [
                        'last_max_id' => $detectedLastId,
                        'last_total'  => $currentTotal,
                        'tp_sig'      => $currentTpSig,
                    ];
                }
            }

            $lastMaxId = (int)($saved['last_max_id'] ?? 0);
            $lastTotal = (int)($saved['last_total'] ?? -1);
            $lastTpSig = (string)($saved['tp_sig'] ?? '');

            if (
                $claimCount > 0 &&
                $currentMaxId === $lastMaxId &&
                $currentTotal === $lastTotal &&
                $currentTpSig === $lastTpSig
            ) {
                return;
            }

            $fp = is_writable($runtimeDir) ? fopen($lockFile, 'c+') : false;
            if ($fp && !flock($fp, LOCK_EX | LOCK_NB)) {
                fclose($fp);
                return;
            }

            try {
                if ($claimCount === 0 || $lastMaxId <= 0 || ($lastTotal > 0 && $currentTotal < $lastTotal) || ($lastTpSig !== '' && $currentTpSig !== $lastTpSig)) {
                    if ($lastTotal > 0 && $currentTotal < $lastTotal) {
                        $db->createCommand("DELETE FROM {{%fwf_fy_claim}} WHERE FundHolderID IN ({$facilityCodesSql})")->execute();
                    }
                    $this->executeSyncInsert(null);
                } elseif ($currentMaxId > $lastMaxId) {
                    $this->executeSyncInsert($lastMaxId);
                }

                $freshStates = (is_file($stateFile) && is_readable($stateFile))
                    ? json_decode((string)file_get_contents($stateFile), true)
                    : [];
                if (!is_array($freshStates)) {
                    $freshStates = [];
                }
                $freshStates[$scopeKey] = [
                    'last_max_id' => $currentMaxId,
                    'last_total'  => $currentTotal,
                    'tp_sig'      => $currentTpSig,
                    'synced_at'   => date('Y-m-d H:i:s'),
                ];
                if (is_writable($runtimeDir)) {
                    file_put_contents($stateFile, json_encode($freshStates, JSON_PRETTY_PRINT));
                }
            } finally {
                if ($fp) {
                    flock($fp, LOCK_UN);
                    fclose($fp);
                }
            }
        } catch (\Throwable $e) {
            Yii::error('FwfFyClaim auto-sync error: ' . $e->getMessage(), __METHOD__);
        }
    }

    // ── คำนวณช่วงวันที่ (รองรับทั้ง พ.ศ. เช่น 2569-10-01 และ ค.ศ. 2026-10-01) ──
    private function resolvePeriodFilter(array $get): array
    {
        $currentFy = ((int)date('n') >= 10) ? ((int)date('Y') + 544) : ((int)date('Y') + 543);
        $peengob   = (int)($get['peengob'] ?? $get['fy'] ?? 0);

        $rawDate1 = trim((string)($get['date1'] ?? ''));
        $rawDate2 = trim((string)($get['date2'] ?? ''));

        $ceStart = $this->parseInputDateToCe($rawDate1);
        $ceEnd   = $this->parseInputDateToCe($rawDate2);

        if ($ceStart !== null && $ceEnd !== null) {
            if ($ceStart > $ceEnd) {
                [$ceStart, $ceEnd] = [$ceEnd, $ceStart];
            }
            if ($peengob <= 0) {
                $endTs   = strtotime($ceEnd);
                $endYear = (int)date('Y', $endTs);
                $endMon  = (int)date('n', $endTs);
                $peengob = ($endMon >= 10) ? ($endYear + 544) : ($endYear + 543);
            }
        } else {
            if ($peengob <= 0) {
                $peengob = $currentFy;
            }
            $ceStart = ($peengob - 544) . '-10-01';
            $ceEnd   = ($peengob - 543) . '-09-30';
        }

        $fyFullCeStart = ($peengob - 544) . '-10-01';
        $fyFullCeEnd   = ($peengob - 543) . '-09-30';
        $isFullFy      = ($ceStart === $fyFullCeStart && $ceEnd === $fyFullCeEnd);

        return [
            'currentFy' => $currentFy,
            'peengob'   => $peengob,
            'ceStart'   => $ceStart,
            'ceEnd'     => $ceEnd,
            'date1Be'   => $this->formatCeToBe($ceStart),
            'date2Be'   => $this->formatCeToBe($ceEnd),
            'isFullFy'  => $isFullFy,
        ];
    }

    private function parseInputDateToCe(string $raw): ?string
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
            return null;
        }
        $y  = (int)$m[1];
        $mo = (int)$m[2];
        $d  = (int)$m[3];
        $yCe = ($y >= 2400) ? ($y - 543) : $y;

        if ($mo < 1 || $mo > 12 || $yCe < 1990 || $yCe > 2100) {
            return null;
        }
        $maxDay = (int)(new \DateTime(sprintf('%04d-%02d-01', $yCe, $mo)))->format('t');
        if ($d > $maxDay) {
            $d = $maxDay;
        }
        if (!checkdate($mo, $d, $yCe)) {
            return null;
        }
        return sprintf('%04d-%02d-%02d', $yCe, $mo, $d);
    }

    private function formatCeToBe(string $ceDate): string
    {
        $ts = strtotime($ceDate);
        if ($ts === false) {
            return $ceDate;
        }
        return ((int)date('Y', $ts) + 543) . date('-m-d', $ts);
    }

    // ── ดึงตารางสรุปรายหน่วยบริการ (เต็มปีงบดึงจากตารางพักทันที / ช่วงวันย่อยคำนวณตามสูตรต้นฉบับ) ──
    private function querySummaryByPeriod(array $period): array
    {
        $db = Yii::$app->db;
        $facilityCodesSql = $this->facilityCodesSql();

        if ($period['isFullFy']) {
            $sql = "
                SELECT
                    FundHolderID,
                    hosname,
                    FiscalYear,
                    COUNT(*)               AS record_count,
                    SUM(Days_in_FY)        AS total_days_in_fy,
                    SUM(manage_ssj_in_FY)  AS total_manage_ssj_in_fy,
                    SUM(pp_in_FY)          AS total_pp_in_fy,
                    SUM(claim_in_FY)       AS total_claim_in_fy
                FROM {{%fwf_fy_claim}}
                WHERE FundHolderID IN ({$facilityCodesSql})
                  AND FiscalYear = :fy
                GROUP BY FundHolderID, hosname, FiscalYear
                ORDER BY FundHolderID ASC
            ";
            return $db->createCommand($sql, [':fy' => (int)$period['peengob']])->queryAll();
        }

        // กรณีเลือกเป็นรายเดือน หรือกำหนดช่วงวันที่เอง ใช้สูตรเดียวกับ buildSourceSql() 100%
        $sql = "
            SELECT
                q.FundHolderID,
                q.hosname,
                :fy                    AS FiscalYear,
                COUNT(*)               AS record_count,
                SUM(q.Days_in_Range)   AS total_days_in_fy,
                SUM(q.manage_in_Range) AS total_manage_ssj_in_fy,
                SUM(q.pp_in_Range)     AS total_pp_in_fy,
                SUM(q.claim_in_Range)  AS total_claim_in_fy
            FROM (
                SELECT
                    f.FundHolderID,
                    h.hosname,
                    DATEDIFF(
                        LEAST(f.PeriodEndDate, DATE_ADD(:ceEnd, INTERVAL 1 DAY)),
                        GREATEST(f.PeriodStartDate, :ceStart)
                    ) AS Days_in_Range,
                    ROUND(
                        tp.manage_ssj
                        / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                        * DATEDIFF(
                            LEAST(f.PeriodEndDate, DATE_ADD(:ceEnd, INTERVAL 1 DAY)),
                            GREATEST(f.PeriodStartDate, :ceStart)
                        ),
                    2) AS manage_in_Range,
                    ROUND(
                        tp.pp
                        / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                        * DATEDIFF(
                            LEAST(f.PeriodEndDate, DATE_ADD(:ceEnd, INTERVAL 1 DAY)),
                            GREATEST(f.PeriodStartDate, :ceStart)
                        ),
                    2) AS pp_in_Range,
                    ROUND(
                        tp.claim
                        / NULLIF(DATEDIFF(f.PeriodEndDate, f.PeriodStartDate), 0)
                        * DATEDIFF(
                            LEAST(f.PeriodEndDate, DATE_ADD(:ceEnd, INTERVAL 1 DAY)),
                            GREATEST(f.PeriodStartDate, :ceStart)
                        ),
                    2) AS claim_in_Range
                FROM fwfdatabase f
                LEFT JOIN chospital2 h ON f.FundHolderID = h.hospcode
                LEFT JOIN (
                    SELECT
                        c.price AS price,
                        MAX(CASE WHEN tp2.description = 'บริหารจัดการ' THEN tp2.price END) AS manage_ssj,
                        MAX(CASE WHEN tp2.description = 'P&P' THEN tp2.price END) AS pp,
                        MAX(CASE WHEN tp2.description = 'claim' THEN tp2.price END) AS claim
                    FROM card c
                    INNER JOIN typeprice tp2 ON c.card_id = tp2.card_id
                    WHERE tp2.sentprice = 'สสจ'
                    GROUP BY c.card_id, c.description, c.price
                ) tp ON tp.price = f.card_price
                WHERE f.FundHolderID IN ({$facilityCodesSql})
                  AND f.PeriodStartDate <= :ceEnd
                  AND f.PeriodEndDate   >  :ceStart
            ) q
            GROUP BY q.FundHolderID, q.hosname
            ORDER BY q.FundHolderID ASC
        ";

        return $db->createCommand($sql, [
            ':fy'      => (int)$period['peengob'],
            ':ceStart' => $period['ceStart'],
            ':ceEnd'   => $period['ceEnd'],
        ])->queryAll();
    }

    // ── actionIndex : แสดงตัวกรองแบบ Dashboard และตารางสรุปรายหน่วยบริการ (ไม่แสดงราย Record) ──
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $username = $session->get('user') ?: (Yii::$app->user->identity->user ?? null);
        $checklogin = $username ? User::findOne(['user' => $username]) : null;
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        $this->ensureAutoSync();

        $get         = Yii::$app->request->get();
        $period      = $this->resolvePeriodFilter($get);
        $message     = (string)($get['msg'] ?? '');
        $scopeSql    = ' WHERE FundHolderID IN (' . $this->facilityCodesSql() . ')';
        $lastImport  = Yii::$app->db->createCommand('SELECT MAX(imported_at) FROM {{%fwf_fy_claim}}' . $scopeSql)->queryScalar();
        $summaryRows = $this->querySummaryByPeriod($period);
        $isAdmin     = ((string)($checklogin->hoscode ?? '') === '00064') || Yii::$app->user->can('provincialAdmin');

        return $this->render('index', [
            'period'      => $period,
            'date1'       => $period['date1Be'],
            'date2'       => $period['date2Be'],
            'peengob'     => $period['peengob'],
            'currentFy'   => $period['currentFy'],
            'isFullFy'    => $period['isFullFy'],
            'lastImport'  => $lastImport,
            'message'     => $message,
            'summaryRows' => $summaryRows,
            'isAdmin'     => $isAdmin,
        ]);
    }

    // ── actionExport : ส่งออกตารางสรุปรายหน่วยบริการเป็น Excel (.xlsx) ตามตัวกรองที่เลือก ──
    public function actionExport()
    {
        $session = Yii::$app->session;
        $username = $session->get('user') ?: (Yii::$app->user->identity->user ?? null);
        $checklogin = $username ? User::findOne(['user' => $username]) : null;
        if (!$checklogin) {
            return $this->redirect(['/site/login']);
        }

        $this->ensureAutoSync();

        $period      = $this->resolvePeriodFilter(Yii::$app->request->get());
        $summaryRows = $this->querySummaryByPeriod($period);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('สรุปข้อมูล Claim แยกรายหน่วยบริการ')
            ->setCreator('CFPS');

        $sumHdrStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A237E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $totalStyle = [
            'font'      => ['bold' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF9C4']],
        ];

        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('สรุปรายหน่วยบริการ');

        $headers = ['#', 'รหัส รพ.', 'ชื่อ รพ.', 'ปีงบ', 'จำนวน Record', 'วันในช่วงเวลา (รวม)', 'บริหารจัดการ', 'PP', 'Claim ในปีงบ'];
        foreach ($headers as $i => $h) {
            $ws->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        $ws->getStyle('A1:I1')->applyFromArray($sumHdrStyle);

        $r = 2;
        $gtRec = 0; $gtDays = 0; $gtManage = 0.0; $gtPP = 0.0; $gtClaim = 0.0;
        foreach ($summaryRows as $idx => $sr) {
            $ws->setCellValueByColumnAndRow(1, $r, $idx + 1);
            $ws->setCellValueByColumnAndRow(2, $r, $sr['FundHolderID']);
            $ws->setCellValueByColumnAndRow(3, $r, $sr['hosname']);
            $ws->setCellValueByColumnAndRow(4, $r, (int)$sr['FiscalYear']);
            $ws->setCellValueByColumnAndRow(5, $r, (int)$sr['record_count']);
            $ws->setCellValueByColumnAndRow(6, $r, (int)$sr['total_days_in_fy']);
            $ws->setCellValueByColumnAndRow(7, $r, (float)$sr['total_manage_ssj_in_fy']);
            $ws->setCellValueByColumnAndRow(8, $r, (float)$sr['total_pp_in_fy']);
            $ws->setCellValueByColumnAndRow(9, $r, (float)$sr['total_claim_in_fy']);

            $gtRec    += (int)$sr['record_count'];
            $gtDays   += (int)$sr['total_days_in_fy'];
            $gtManage += (float)$sr['total_manage_ssj_in_fy'];
            $gtPP     += (float)$sr['total_pp_in_fy'];
            $gtClaim  += (float)$sr['total_claim_in_fy'];
            $r++;
        }

        if ($r > 2) {
            $ws->getStyle('E2:F' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0');
            $ws->getStyle('G2:I' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0.00');

            $ws->setCellValue('A' . $r, 'รวมทั้งหมด');
            $ws->setCellValue('E' . $r, $gtRec);
            $ws->setCellValue('F' . $r, $gtDays);
            $ws->setCellValue('G' . $r, round($gtManage, 2));
            $ws->setCellValue('H' . $r, round($gtPP, 2));
            $ws->setCellValue('I' . $r, round($gtClaim, 2));
            $ws->getStyle('A' . $r . ':I' . $r)->applyFromArray($totalStyle);
            $ws->getStyle('E' . $r . ':F' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $ws->getStyle('G' . $r . ':I' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        foreach (range('A', 'I') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'FWF_Summary_' . $period['date1Be'] . '_to_' . $period['date2Be'] . '.xlsx';
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
            if (is_file($tmpFile)) {
                unlink($tmpFile);
            }
        });
    }

    public function actionImport()
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $session = Yii::$app->session;
        $username = $session->get('user') ?: (Yii::$app->user->identity->user ?? null);
        if (!$username || !User::findOne(['user' => $username])) {
            return $this->redirect(['/site/login']);
        }

        try {
            $this->executeSyncInsert(null);
            $stateFile = Yii::getAlias('@frontend/runtime/fwf_fy_autosync_v2.json');
            if (is_file($stateFile)) {
                unlink($stateFile);
            }
            $msg = 'ซิงค์ข้อมูลล่าสุดเรียบร้อยแล้ว';
        } catch (\Exception $e) {
            $msg = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }

        return $this->redirect(['/fwf-fy-claim/index', 'msg' => $msg]);
    }

    public function actionClear()
    {
        Yii::$app->facilityAccess->requirePermission('manageFwfData');
        $session = Yii::$app->session;
        $username = $session->get('user') ?: (Yii::$app->user->identity->user ?? null);
        if (!$username || !User::findOne(['user' => $username])) {
            return $this->redirect(['/site/login']);
        }
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/fwf-fy-claim/index']);
        }

        try {
            $db = Yii::$app->db;
            $scopeSql = 'FundHolderID IN (' . $this->facilityCodesSql() . ')';
            $db->createCommand('DELETE FROM {{%fwf_fy_claim}} WHERE ' . $scopeSql)->execute();
            $stateFile = Yii::getAlias('@frontend/runtime/fwf_fy_autosync_v2.json');
            if (is_file($stateFile)) {
                unlink($stateFile);
            }
            $msg = 'ล้างตารางพักและซิงค์ข้อมูลใหม่ตามสูตรต้นฉบับเรียบร้อยแล้ว';
        } catch (\Exception $e) {
            $msg = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }

        return $this->redirect(['/fwf-fy-claim/index', 'msg' => $msg]);
    }
}