<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'กองทุน Claim ตามปีงบประมาณ';
$this->params['breadcrumbs'][] = $this->title;

$currentFy   = (int)($currentFy ?? (((int)date('n') >= 10) ? ((int)date('Y') + 544) : ((int)date('Y') + 543)));
$peengob     = (int)($peengob ?? $currentFy);
$date1       = (string)($date1 ?? (($peengob - 1) . '-10-01'));
$date2       = (string)($date2 ?? ($peengob . '-09-30'));
$isFullFy    = (bool)($isFullFy ?? true);
$message     = (string)($message ?? '');
$summaryRows = is_array($summaryRows ?? null) ? $summaryRows : [];
$isAdmin     = (bool)($isAdmin ?? false);

$lastImportDisplay = '—';
if (!empty($lastImport)) {
    $ts = strtotime((string)$lastImport);
    $lastImportDisplay = $ts ? (date('d/m/', $ts) . ((int)date('Y', $ts) + 543) . date(' H:i:s', $ts)) : (string)$lastImport;
}

// รายการปุ่มปีงบประมาณย้อนหลัง 6 ปี (เช่น 2570 .. 2565)
$fyButtons = [];
for ($y = $currentFy; $y >= $currentFy - 5; $y--) {
    $fyButtons[] = $y;
}
if (!in_array($peengob, $fyButtons, true)) {
    array_unshift($fyButtons, $peengob);
    $fyButtons = array_unique($fyButtons);
    rsort($fyButtons);
}

// สร้างรายการปุ่ม 12 เดือนของปีงบประมาณที่เลือก (ต.ค. .. ก.ย.)
$thaiMonths = [
    10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    1  => 'ม.ค.', 2  => 'ก.พ.', 3  => 'มี.ค.',
    4  => 'เม.ย.', 5  => 'พ.ค.', 6  => 'มิ.ย.',
    7  => 'ก.ค.', 8  => 'ส.ค.', 9  => 'ก.ย.',
];
$monthButtons = [];
foreach ($thaiMonths as $mNum => $mLabel) {
    $beYear = ($mNum >= 10) ? ($peengob - 1) : $peengob;
    $ceYear = $beYear - 543;
    $lastDay = (int)(new \DateTime(sprintf('%04d-%02d-01', $ceYear, $mNum)))->format('t');
    $mStartBe = sprintf('%04d-%02d-01', $beYear, $mNum);
    $mEndBe   = sprintf('%04d-%02d-%02d', $beYear, $mNum, $lastDay);
    $monthButtons[] = [
        'label'  => $mLabel . ' ' . substr((string)$beYear, -2),
        'date1'  => $mStartBe,
        'date2'  => $mEndBe,
        'active' => ($date1 === $mStartBe && $date2 === $mEndBe),
    ];
}

$prevFy = $peengob - 1;
$nextFy = $peengob + 1;

$sumRecords = 0;
$sumDaysFy  = 0;
$sumManage  = 0.0;
$sumPp      = 0.0;
$sumClaim   = 0.0;
foreach ($summaryRows as $sr) {
    $sumRecords += (int)($sr['record_count'] ?? 0);
    $sumDaysFy  += (int)($sr['total_days_in_fy'] ?? 0);
    $sumManage  += (float)($sr['total_manage_ssj_in_fy'] ?? 0);
    $sumPp      += (float)($sr['total_pp_in_fy'] ?? 0);
    $sumClaim   += (float)($sr['total_claim_in_fy'] ?? 0);
}

$badgeText = $isFullFy
    ? ('ปีงบ ' . $peengob)
    : ($date1 . ' ถึง ' . $date2);

$this->registerCss("
.fwf-filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 260px;
    gap: 14px;
    align-items: center;
    margin-bottom: 16px;
}
@media (max-width: 900px) {
    .fwf-filter-grid { grid-template-columns: 1fr; }
}
.fwf-fy-panel {
    background: #f3f9f1;
    border: 1px solid #d7ebd2;
    border-radius: 8px;
    padding: 14px 16px;
}
.fwf-fy-title {
    color: #2e7d32;
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 10px 0;
}
.fwf-fy-btn-row, .fwf-month-btn-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.fwf-fy-btn-row { margin-bottom: 10px; }
.fwf-fy-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 13px;
    border-radius: 6px;
    background: #2e7d32;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none !important;
    border: 2px solid transparent;
    transition: all .15s ease;
}
.fwf-fy-btn:hover { background: #1b5e20; color: #ffffff !important; }
.fwf-fy-btn.is-active {
    background: #1b5e20;
    border-color: #f59e0b;
    box-shadow: 0 2px 6px rgba(27, 94, 32, 0.28);
}
.fwf-m-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 11px;
    border-radius: 6px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b !important;
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none !important;
    transition: all .15s ease;
}
.fwf-m-btn:hover {
    background: #e8f5e9;
    border-color: #2e7d32;
    color: #1b5e20 !important;
}
.fwf-m-btn.is-active {
    background: #2e7d32;
    border-color: #2e7d32;
    color: #ffffff !important;
    font-weight: 700;
}
");
?>
<div class="fwf-fy-claim-index">

    <?php if ($message !== ''): ?>
        <div class="alert alert-info"><?= Html::encode($message) ?></div>
    <?php endif; ?>

    <!-- กล่องตัวกรองช่วงเวลา ปีงบประมาณ และรายเดือน (รูปแบบเดียวกับหน้า Dashboard) -->
    <div class="box box-success">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-calendar"></i> <?= Html::encode($this->title) ?> — สรุปข้อมูลกองทุนตามช่วงเวลา
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>

        <div class="box-body">
            <div style="font-size:13px; color:#64748b; margin-bottom:6px;">
                <i class="fa fa-calendar"></i> ค้นหาแบบช่วงเวลา
            </div>

            <form method="get" action="<?= Url::to(['index']) ?>">
                <input type="hidden" name="peengob" value="<?= (int)$peengob ?>">
                <div class="fwf-filter-grid">
                    <div class="input-group">
                        <span class="input-group-addon js-thai-dp-trigger" data-target="fwf-date1" style="cursor:pointer;" title="เลือกวันที่เริ่มต้น">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type="text" id="fwf-date1" name="date1" value="<?= Html::encode($date1) ?>" class="form-control" placeholder="พ.ศ.-ดด-วว เช่น 2569-10-01">
                        <input type="date" id="fwf-date1-native" style="position:absolute; opacity:0; width:1px; height:1px; pointer-events:none;">
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon js-thai-dp-trigger" data-target="fwf-date2" style="cursor:pointer;" title="เลือกวันที่สิ้นสุด">
                            <i class="fa fa-calendar"></i>
                        </span>
                        <input type="text" id="fwf-date2" name="date2" value="<?= Html::encode($date2) ?>" class="form-control" placeholder="พ.ศ.-ดด-วว เช่น 2570-09-30">
                        <input type="date" id="fwf-date2-native" style="position:absolute; opacity:0; width:1px; height:1px; pointer-events:none;">
                    </div>

                    <div>
                        <button type="submit" class="btn btn-success" style="width:100%; height:38px; font-weight:700;">
                            <i class="fa fa-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </form>

            <div class="fwf-fy-panel">
                <div class="fwf-fy-title">
                    <i class="fa fa-calendar-check-o"></i> ปีงบประมาณ <?= (int)$peengob ?>
                </div>

                <div class="fwf-fy-btn-row">
                    <?php foreach ($fyButtons as $btnFy): ?>
                        <?php
                        $fyD1 = ($btnFy - 1) . '-10-01';
                        $fyD2 = $btnFy . '-09-30';
                        $fyActive = ($date1 === $fyD1 && $date2 === $fyD2);
                        ?>
                        <?= Html::a(
                            '<i class="fa fa-calendar"></i> ปีงบ ' . (int)$btnFy,
                            ['index', 'date1' => $fyD1, 'date2' => $fyD2, 'peengob' => $btnFy],
                            ['class' => 'fwf-fy-btn' . ($fyActive ? ' is-active' : '')]
                        ) ?>
                    <?php endforeach; ?>
                </div>

                <div class="fwf-month-btn-row">
                    <?= Html::a(
                        '<i class="fa fa-chevron-left"></i>',
                        ['index', 'date1' => ($prevFy - 1) . '-10-01', 'date2' => $prevFy . '-09-30', 'peengob' => $prevFy],
                        ['class' => 'fwf-m-btn', 'title' => 'ปีงบประมาณ ' . $prevFy]
                    ) ?>

                    <?php foreach ($monthButtons as $mb): ?>
                        <?= Html::a(
                            Html::encode($mb['label']),
                            ['index', 'date1' => $mb['date1'], 'date2' => $mb['date2'], 'peengob' => $peengob],
                            ['class' => 'fwf-m-btn' . ($mb['active'] ? ' is-active' : '')]
                        ) ?>
                    <?php endforeach; ?>

                    <?= Html::a(
                        '<i class="fa fa-chevron-right"></i>',
                        ['index', 'date1' => ($nextFy - 1) . '-10-01', 'date2' => $nextFy . '-09-30', 'peengob' => $nextFy],
                        ['class' => 'fwf-m-btn', 'title' => 'ปีงบประมาณ ' . $nextFy]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- กล่องตารางสรุปข้อมูล Claim แยกรายหน่วยบริการ -->
    <div class="box" style="margin-bottom:20px;">
        <div class="box-header with-border" style="background:#1a237e; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;">
            <h3 class="box-title" style="color:#fff; margin:0;">
                <i class="fa fa-bar-chart"></i> สรุปข้อมูล Claim แยกรายหน่วยบริการ
                <span class="label" style="background:#f59e0b; color:#fff; margin-left:6px; padding:3px 9px; border-radius:4px;">
                    <?= Html::encode($badgeText) ?>
                </span>
            </h3>

            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin-left:auto;">
                <span style="font-size:12.5px; color:rgba(255,255,255,0.88);">
                    <i class="fa fa-check-circle" style="color:#81c784;"></i> ซิงค์อัตโนมัติล่าสุด: <?= Html::encode($lastImportDisplay) ?>
                </span>

                <?= Html::a(
                    '<i class="fa fa-file-excel-o"></i> ส่งออก Excel (.xlsx)',
                    ['export', 'date1' => $date1, 'date2' => $date2, 'peengob' => $peengob],
                    ['class' => 'btn btn-success btn-sm', 'data-pjax' => '0']
                ) ?>

                <?php if ($isAdmin): ?>
                    <?= Html::a(
                        '<i class="fa fa-refresh"></i> รีเซ็ตซิงค์ใหม่',
                        ['clear'],
                        [
                            'class' => 'btn btn-default btn-sm',
                            'data' => [
                                'method' => 'post',
                                'confirm' => 'ต้องการล้างตารางพักและดึงข้อมูลคำนวณใหม่ทั้งหมดตามสูตรต้นฉบับใช่หรือไม่?',
                            ],
                        ]
                    ) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="box-body" style="padding:12px;">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th style="background:#263238; color:#fff; text-align:center; width:50px;">#</th>
                            <th style="background:#263238; color:#fff; width:95px;">รหัส รพ.</th>
                            <th style="background:#263238; color:#fff;">ชื่อ รพ.</th>
                            <th style="background:#2e7d32; color:#fff; text-align:center; width:85px;">ปีงบ</th>
                            <th style="background:#263238; color:#fff; text-align:right;">จำนวน Record</th>
                            <th style="background:#263238; color:#fff; text-align:right;">วันในปีงบ (รวม)</th>
                            <th style="background:#1565c0; color:#fff; text-align:right;">บริหารจัดการ</th>
                            <th style="background:#2e7d32; color:#fff; text-align:right;">PP</th>
                            <th style="background:#880e4f; color:#fff; text-align:right;">Claim ในปีงบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summaryRows)): ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:24px; color:#64748b;">ไม่พบข้อมูลสำหรับช่วงเวลาที่เลือก</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summaryRows as $idx => $s): ?>
                                <tr>
                                    <td style="text-align:center;"><?= $idx + 1 ?></td>
                                    <td><?= Html::encode($s['FundHolderID'] ?? '') ?></td>
                                    <td><?= Html::encode($s['hosname'] ?? '') ?></td>
                                    <td style="text-align:center; color:#2e7d32; font-weight:700;"><?= (int)($s['FiscalYear'] ?? $peengob) ?></td>
                                    <td style="text-align:right;"><?= number_format((int)($s['record_count'] ?? 0)) ?></td>
                                    <td style="text-align:right;"><?= number_format((int)($s['total_days_in_fy'] ?? 0)) ?></td>
                                    <td style="text-align:right;"><?= number_format((float)($s['total_manage_ssj_in_fy'] ?? 0), 2) ?></td>
                                    <td style="text-align:right;"><?= number_format((float)($s['total_pp_in_fy'] ?? 0), 2) ?></td>
                                    <td style="text-align:right; color:#b71c1c; font-weight:700; background:#fffde7;">
                                        <?= number_format((float)($s['total_claim_in_fy'] ?? 0), 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="background:#fff9c4; font-weight:700;">
                                <td colspan="4" style="text-align:right;">รวมทั้งหมด</td>
                                <td style="text-align:right;"><?= number_format($sumRecords) ?></td>
                                <td style="text-align:right;"><?= number_format($sumDaysFy) ?></td>
                                <td style="text-align:right;"><?= number_format($sumManage, 2) ?></td>
                                <td style="text-align:right;"><?= number_format($sumPp, 2) ?></td>
                                <td style="text-align:right; color:#b71c1c;"><?= number_format($sumClaim, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
$this->registerJs("
(function() {
    function beToCe(val) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec((val || '').trim());
        if (!m) return '';
        var y = parseInt(m[1], 10);
        if (y >= 2400) y -= 543;
        return y + '-' + m[2] + '-' + m[3];
    }
    function ceToBe(val) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec((val || '').trim());
        if (!m) return '';
        var y = parseInt(m[1], 10) + 543;
        return y + '-' + m[2] + '-' + m[3];
    }
    document.querySelectorAll('.js-thai-dp-trigger').forEach(function(btn) {
        var targetId = btn.getAttribute('data-target');
        var textInput = document.getElementById(targetId);
        var dateInput = document.getElementById(targetId + '-native');
        if (!textInput || !dateInput) return;

        btn.addEventListener('click', function() {
            var ce = beToCe(textInput.value);
            if (ce) dateInput.value = ce;
            if (typeof dateInput.showPicker === 'function') {
                dateInput.showPicker();
            } else {
                dateInput.focus();
                dateInput.click();
            }
        });
        dateInput.addEventListener('change', function() {
            if (dateInput.value) {
                textInput.value = ceToBe(dateInput.value);
            }
        });
    });
})();
");
?>