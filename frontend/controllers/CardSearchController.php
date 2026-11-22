<?php

namespace frontend\controllers;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use frontend\models\Fwfdatabase;

/**
 * ค้นหาบัตรประกันสุขภาพแรงงานต่างด้าวจากตาราง fwfdatabase
 * รองรับทั้งผู้ใช้งานจากหน้า Login (Guest) และเจ้าหน้าที่ทุกหน่วยบริการที่เข้าสู่ระบบแล้ว
 */
class CardSearchController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?', '@'], // เปิดให้เข้าได้ทั้งจากหน้า Login (?) และหลังล็อกอิน (@)
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $request = Yii::$app->request;
        $isGuest = (bool)Yii::$app->user->isGuest;

        // สลับ Layout: ถ้ายังไม่ล็อกอินใช้ main-login เพื่อไม่ให้เรียก user->identity ใน header.php
        $this->layout = $isGuest ? 'main-login' : 'main';

        $rawCid = trim((string)($request->get('cid') ?? ''));
        $tokens = $this->parseSearchTokens($rawCid);
        $primaryKeyword = $tokens[0] ?? '';

        $searched = ($rawCid !== '');
        $errorMsg = '';
        $rows = [];
        $labels = [];
        $summary = [
            'total' => 0,
            'hasActive' => false,
            'latestStatus' => 'none',
            'latestStatusLabel' => '-',
            'latestDaysNote' => '',
            'latestStartThai' => '-',
            'latestEndThai' => '-',
            'latestHosp' => '-',
            'patientName' => '-',
            'nationality' => '-',
            'latestTimeRegis' => '-',
            'matchedCidTokens' => [],
        ];

        if (class_exists(Fwfdatabase::class)) {
            $modelInstance = new Fwfdatabase();
            $labels = $modelInstance->attributeLabels();
        }

        if ($searched) {
            $minLen = $isGuest ? 6 : 4;
            if ($primaryKeyword === '' || mb_strlen($primaryKeyword, 'UTF-8') < $minLen) {
                $errorMsg = $isGuest
                    ? 'สำหรับการตรวจสอบโดยไม่เข้าสู่ระบบ กรุณาระบุเลข 13 หลัก, เลขพาสปอร์ต, รหัส F04... หรือเลข RA... ให้ครบถ้วน'
                    : 'กรุณาระบุรหัสประจำตัวอย่างน้อย 4 ตัวอักษรเพื่อค้นหา';
            } else {
                $result = $this->searchByCid($tokens, $labels, $isGuest);
                $rows = $result['rows'] ?? [];
                $summary = $result['summary'] ?? $summary;
            }
        }

        $viewParams = [
            'isGuest' => $isGuest,
            'rawCid' => $rawCid,
            'cleanCid' => $primaryKeyword,
            'tokens' => $tokens,
            'searched' => $searched,
            'errorMsg' => $errorMsg,
            'rows' => $rows,
            'labels' => $labels,
            'summary' => $summary,
        ];

        if ($request->isAjax || (string)($request->get('ajax') ?? '') === '1') {
            return $this->renderPartial('_results', $viewParams);
        }

        return $this->render('index', $viewParams);
    }

    /**
     * @return string[]
     */
    private function parseSearchTokens(string $rawInput): array
    {
        if ($rawInput === '') {
            return [];
        }

        $parts = explode(',', $rawInput);
        $tokens = [];
        foreach ($parts as $part) {
            $cleaned = preg_replace('/[\s\-]+/u', '', trim($part)) ?? '';
            $cleaned = strtoupper($cleaned);
            if ($cleaned !== '' && mb_strlen($cleaned, 'UTF-8') >= 4) {
                $tokens[$cleaned] = $cleaned;
            }
        }

        if (empty($tokens)) {
            $fallback = strtoupper(preg_replace('/[\s\-,]+/u', '', $rawInput) ?? '');
            if ($fallback !== '') {
                $tokens[$fallback] = $fallback;
            }
        }

        return array_values($tokens);
    }

    /**
     * @return array<int, array{value: string, type: string, matched: bool}>
     */
    private function splitStoredCidColumn(string $storedCid, array $searchTokens, bool $isGuest): array
    {
        if ($storedCid === '') {
            return [];
        }

        $parts = explode(',', $storedCid);
        $items = [];
        $seen = [];

        foreach ($parts as $p) {
            $val = trim($p);
            if ($val === '' || $val === '-') {
                continue;
            }
            $upperVal = strtoupper(preg_replace('/[\s\-]+/u', '', $val) ?? $val);
            if (isset($seen[$upperVal])) {
                continue;
            }
            $seen[$upperVal] = true;

            $isMatched = false;
            foreach ($searchTokens as $tk) {
                if ($tk === '') {
                    continue;
                }
                if ($isGuest ? ($upperVal === $tk) : str_contains($upperVal, $tk)) {
                    $isMatched = true;
                    break;
                }
            }

            $items[] = [
                'value' => $val,
                'type' => $this->classifyIdentifierType($upperVal),
                'matched' => $isMatched,
            ];
        }

        return $items;
    }

    private function classifyIdentifierType(string $upperVal): string
    {
        if (preg_match('/^\d{13}$/', $upperVal)) {
            return 'เลข 13 หลัก';
        }
        if (preg_match('/^F\d{8,}$/', $upperVal)) {
            return 'PeriodID/FWF';
        }
        if (str_starts_with($upperVal, 'RA') && strlen($upperVal) >= 10) {
            return 'เลขอ้างอิง RA';
        }
        return 'Passport/CI';
    }

    private function searchByCid(array $tokens, array $labels, bool $isGuest): array
    {
        $db = Yii::$app->db;
        $tableSchema = $db->schema->getTableSchema('fwfdatabase');
        $colNames = $tableSchema !== null ? $tableSchema->columnNames : [];

        $cidCol = $this->detectColumn($colNames, ['CID', 'cid']) ?? 'CID';
        $periodIdCol = $this->detectColumn($colNames, ['PeriodID', 'periodid']);
        $startCol = $this->detectColumn($colNames, ['PeriodStartDate', 'startdate']);
        $endCol = $this->detectColumn($colNames, ['PeriodEndDate', 'enddate']);
        $hospCol = $this->detectColumn($colNames, ['FundHolderID', 'hospcode', 'hoscode']);
        $fnameCol = $this->detectColumn($colNames, ['fname']);
        $lnameCol = $this->detectColumn($colNames, ['lname']);
        $timeRegisCol = $this->detectColumn($colNames, ['timeregis']);
        $regisDateCol = $this->detectColumn($colNames, ['regisdate']);
        $cardPriceCol = $this->detectColumn($colNames, ['card_price']);
        $exsFileCol = $this->detectColumn($colNames, ['exsfile']);

        $orConditions = ['or'];
        foreach ($tokens as $i => $tk) {
            $paramExact = ":exact_{$i}";
            $escapedLike = strtr($tk, ['%' => '\%', '_' => '\_', '\\' => '\\\\']);
            $paramLike = ":like_{$i}";

            if ($isGuest) {
                // โหมดยังไม่ล็อกอิน: บังคับตรงรหัสใดรหัสหนึ่งในลูกน้ำแบบเต็มตัว (PDPA Safe)
                $orConditions[] = new Expression(
                    "(UPPER([[{$cidCol}]]) LIKE {$paramLike} AND FIND_IN_SET({$paramExact}, REPLACE(REPLACE(UPPER([[{$cidCol}]]), ' ', ''), '-', '')) > 0)",
                    [
                        $paramLike => '%' . $escapedLike . '%',
                        $paramExact => $tk,
                    ]
                );
            } else {
                // โหมดล็อกอินแล้ว: รองรับการค้นหาบางส่วน
                $orConditions[] = new Expression(
                    "UPPER([[{$cidCol}]]) LIKE {$paramLike}",
                    [$paramLike => '%' . $escapedLike . '%']
                );
            }

            if ($periodIdCol !== null) {
                $orConditions[] = [$periodIdCol => $tk];
            }
        }

        $query = (new Query())
            ->from('fwfdatabase')
            ->where($orConditions);

        $orderBy = [];
        if ($endCol !== null) {
            $orderBy[$endCol] = SORT_DESC;
        }
        if (in_array('fwfid', $colNames, true)) {
            $orderBy['fwfid'] = SORT_DESC;
        }
        if (!empty($orderBy)) {
            $query->orderBy($orderBy);
        }

        $rawRows = $query->limit($isGuest ? 20 : 100)->all($db);

        $hospMap = [];
        if ($hospCol !== null && !empty($rawRows)) {
            $codes = [];
            foreach ($rawRows as $r) {
                $val = trim((string)($r[$hospCol] ?? ''));
                if ($val !== '') {
                    $codes[$val] = $val;
                }
            }
            if (!empty($codes)) {
                $hospMap = $this->fetchHospitalNames(array_values($codes));
            }
        }

        $today = date('Y-m-d');
        $enrichedRows = [];
        $hasActive = false;

        $customLabels = [
            'fwfid' => 'รหัสอ้างอิงระบบ (fwfid)',
            'PeriodID' => 'เลขที่สิทธิ/งวด (PeriodID)',
            'PeriodStartDate' => 'วันเริ่มคุ้มครอง (PeriodStartDate)',
            'PeriodEndDate' => 'วันสิ้นสุดคุ้มครอง (PeriodEndDate)',
            'CID' => 'รหัสประจำตัวทั้งหมดในระบบ (CID)',
            'FundHolderID' => 'รหัสหน่วยบริการเจ้าของกองทุน (FundHolderID)',
            'fname' => 'ชื่อผู้ประกันตน (fname)',
            'lname' => 'สัญชาติ (lname)',
            'timeregis' => 'ประเภทบัตร / มติ ครม. (timeregis)',
            'regisdate' => 'วันที่ขึ้นทะเบียน (regisdate)',
            'exsfile' => 'แหล่งข้อมูลนำเข้า (exsfile)',
            'card_price' => 'ราคาบัตร (card_price)',
            'pp' => 'งบ P&P (pp)',
            'man_ssj' => 'บริหารจัดการ สสจ. (man_ssj)',
            'man_sp' => 'บริหารจัดการ สป. (man_sp)',
            'hc_sp' => 'ค่าบริการสุขภาพ สป. (hc_sp)',
            'claim' => 'งบเคลมค่ารักษา (claim)',
        ];

        foreach ($rawRows as $idx => $row) {
            $storedCid = trim((string)($row[$cidCol] ?? ''));
            $cidParts = $this->splitStoredCidColumn($storedCid, $tokens, $isGuest);

            $startRaw = $startCol !== null ? trim((string)($row[$startCol] ?? '')) : '';
            $endRaw = $endCol !== null ? trim((string)($row[$endCol] ?? '')) : '';

            $statusInfo = $this->evaluateCoverageStatus($startRaw, $endRaw, $today);
            if (($statusInfo['code'] ?? '') === 'active') {
                $hasActive = true;
            }

            $hCode = $hospCol !== null ? trim((string)($row[$hospCol] ?? '')) : '';
            $hName = $hCode !== '' ? ($hospMap[$hCode] ?? '') : '';
            $hospDisplay = $hCode !== ''
                ? ($hName !== '' ? "{$hCode} - {$hName}" : $hCode)
                : '-';

            $ptNameRaw = $fnameCol !== null ? trim((string)($row[$fnameCol] ?? '')) : '';
            $ptNameClean = trim(preg_replace('/\s*-\s*$/u', '', $ptNameRaw) ?? '');
            $ptName = $ptNameClean !== '' ? $ptNameClean : ($ptNameRaw !== '' ? $ptNameRaw : '-');

            $nationality = $lnameCol !== null ? trim((string)($row[$lnameCol] ?? '')) : '';
            $nationality = $nationality !== '' ? $nationality : '-';

            $periodIdVal = $periodIdCol !== null ? trim((string)($row[$periodIdCol] ?? '')) : '-';
            $timeRegisVal = $timeRegisCol !== null ? trim((string)($row[$timeRegisCol] ?? '')) : '-';
            $regisDateRaw = $regisDateCol !== null ? trim((string)($row[$regisDateCol] ?? '')) : '';
            $exsFileVal = $exsFileCol !== null ? trim((string)($row[$exsFileCol] ?? '')) : '-';
            $cardPriceVal = $cardPriceCol !== null && ($row[$cardPriceCol] ?? '') !== ''
                ? number_format((float)$row[$cardPriceCol], 2)
                : '-';

            $detailItems = [];
            if (!$isGuest) {
                foreach ($row as $k => $v) {
                    $keyStr = (string)$k;
                    $label = (string)($customLabels[$keyStr] ?? $labels[$keyStr] ?? $keyStr);
                    $valStr = ($v === null || $v === '') ? '-' : (string)$v;
                    if ($keyStr === $hospCol && $hName !== '') {
                        $valStr = "{$valStr} ({$hName})";
                    }
                    $detailItems[] = [
                        'key' => $keyStr,
                        'label' => $label,
                        'value' => $valStr,
                    ];
                }
            }

            $enrichedRows[] = [
                'fwfid' => (string)($row['fwfid'] ?? ($idx + 1)),
                'periodId' => $periodIdVal !== '' ? $periodIdVal : '-',
                'storedCid' => $storedCid,
                'cidParts' => $cidParts,
                'patientName' => $ptName,
                'nationality' => $nationality,
                'hospCode' => $hCode,
                'hospDisplay' => $hospDisplay,
                'timeRegis' => $timeRegisVal !== '' ? $timeRegisVal : '-',
                'regisDateThai' => $this->formatThaiDate($regisDateRaw),
                'exsFile' => $exsFileVal !== '' ? $exsFileVal : '-',
                'cardPrice' => $cardPriceVal,
                'startDateThai' => $this->formatThaiDate($startRaw),
                'endDateThai' => $this->formatThaiDate($endRaw),
                'statusCode' => $statusInfo['code'] ?? 'unknown',
                'statusLabel' => $statusInfo['label'] ?? 'ไม่ระบุวันคุ้มครอง',
                'daysNote' => $statusInfo['daysNote'] ?? '',
                'details' => $detailItems,
            ];
        }

        $first = $enrichedRows[0] ?? null;
        foreach ($enrichedRows as $er) {
            if (($er['statusCode'] ?? '') === 'active') {
                $first = $er;
                break;
            }
        }

        return [
            'rows' => $enrichedRows,
            'summary' => [
                'total' => count($enrichedRows),
                'hasActive' => $hasActive,
                'latestStatus' => (string)($first['statusCode'] ?? 'none'),
                'latestStatusLabel' => (string)($first['statusLabel'] ?? '-'),
                'latestDaysNote' => (string)($first['daysNote'] ?? ''),
                'latestStartThai' => (string)($first['startDateThai'] ?? '-'),
                'latestEndThai' => (string)($first['endDateThai'] ?? '-'),
                'latestHosp' => (string)($first['hospDisplay'] ?? '-'),
                'patientName' => (string)($first['patientName'] ?? '-'),
                'nationality' => (string)($first['nationality'] ?? '-'),
                'latestTimeRegis' => (string)($first['timeRegis'] ?? '-'),
                'matchedCidTokens' => $first['cidParts'] ?? [],
            ],
        ];
    }

    private function detectColumn(array $availableCols, array $candidates): ?string
    {
        $lowerMap = [];
        foreach ($availableCols as $col) {
            $lowerMap[strtolower((string)$col)] = (string)$col;
        }
        foreach ($candidates as $cand) {
            $key = strtolower($cand);
            if (isset($lowerMap[$key])) {
                return $lowerMap[$key];
            }
        }
        return null;
    }

    private function fetchHospitalNames(array $codes): array
    {
        $db = Yii::$app->db;
        $hospSchema = $db->schema->getTableSchema('chospital2');
        if ($hospSchema === null) {
            return [];
        }

        $cols = $hospSchema->columnNames;
        $codeCol = $this->detectColumn($cols, ['hospcode', 'hoscode']);
        $nameCol = $this->detectColumn($cols, ['hosname', 'hospname', 'name']);

        if ($codeCol === null || $nameCol === null) {
            return [];
        }

        $list = (new Query())
            ->select([$codeCol, $nameCol])
            ->from('chospital2')
            ->where(['in', $codeCol, $codes])
            ->all($db);

        $map = [];
        foreach ($list as $item) {
            $c = trim((string)($item[$codeCol] ?? ''));
            $n = trim((string)($item[$nameCol] ?? ''));
            if ($c !== '') {
                $map[$c] = $n;
            }
        }
        return $map;
    }

    private function evaluateCoverageStatus(string $startRaw, string $endRaw, string $today): array
    {
        $start = $this->normalizeIsoDate($startRaw);
        $end = $this->normalizeIsoDate($endRaw);

        if ($start === null && $end === null) {
            return [
                'code' => 'unknown',
                'label' => 'ไม่ระบุวันคุ้มครอง',
                'daysNote' => '',
            ];
        }

        $todayTs = strtotime($today);
        $startTs = $start !== null ? strtotime($start) : null;
        $endTs = $end !== null ? strtotime($end) : null;

        if ($startTs !== null && $startTs > $todayTs) {
            $diff = (int)round(($startTs - $todayTs) / 86400);
            return [
                'code' => 'upcoming',
                'label' => 'รอเริ่มคุ้มครอง',
                'daysNote' => "อีก {$diff} วันเริ่มสิทธิ",
            ];
        }

        if ($endTs !== null && $endTs < $todayTs) {
            $diff = (int)round(($todayTs - $endTs) / 86400);
            return [
                'code' => 'expired',
                'label' => 'หมดอายุแล้ว',
                'daysNote' => "หมดอายุมาแล้ว {$diff} วัน",
            ];
        }

        $remainNote = '';
        if ($endTs !== null) {
            $remain = (int)round(($endTs - $todayTs) / 86400);
            $remainNote = $remain === 0 ? 'คุ้มครองวันสุดท้าย' : "คงเหลือ {$remain} วัน";
        }

        return [
            'code' => 'active',
            'label' => 'กำลังคุ้มครอง',
            'daysNote' => $remainNote,
        ];
    }

    private function normalizeIsoDate(string $raw): ?string
    {
        if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
            return null;
        }
        $datePart = substr($raw, 0, 10);
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datePart, $m)) {
            return null;
        }
        $year = (int)$m[1];
        if ($year >= 2400) {
            $year -= 543;
        }
        return sprintf('%04d-%s-%s', $year, $m[2], $m[3]);
    }

    private function formatThaiDate(string $raw): string
    {
        $iso = $this->normalizeIsoDate($raw);
        if ($iso === null) {
            return '-';
        }
        [$y, $m, $d] = explode('-', $iso);
        $thaiYear = (int)$y + 543;
        return "{$d}/{$m}/{$thaiYear}";
    }
}
