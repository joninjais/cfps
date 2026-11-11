<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $totals array */
/* @var $byPrice array */
/* @var $byAgency array */
/* @var $byMonth array */

$this->title = 'Dashboard : สรุปข้อมูลกรมธรรม์';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลกรมธรรม์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$months = [
    10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.', 1 => 'ม.ค.',
    2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.',
    6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
];
$agencyFilter = $isAdmin && $selectedAgency !== '' ? ['agency' => $selectedAgency] : [];

$this->registerCss('
.db-filter { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 18px 22px; margin-bottom: 24px; }
.db-filter h2 { font-size: 1.15rem; font-weight: 700; color: #00796b; margin: 0 0 12px; }
.db-filter-row { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.db-filter .btn:focus-visible { outline: 3px solid #005c53; outline-offset: 2px; }
.db-filter .btn.active { background: #00796b; color: #fff; border-color: #00796b; }
.db-filter-period { margin: 0; color: #555; }
.db-table-scroll { overflow-x: auto; }
.db-agency-form { display: flex; flex-wrap: wrap; align-items: end; gap: 10px; margin-bottom: 16px; }
.db-agency-field { flex: 1 1 260px; max-width: 500px; }
.db-agency-form label { display: block; }
.db-agency-form select:focus-visible { outline: 3px solid #005c53; outline-offset: 2px; }
');

// --- Prepare chart data ---
$priceCounts  = [];
$priceLabels  = [];
foreach ($byPrice as $row) {
    $label = 'ราคา ' . number_format((float)$row['card_price'], 2) . ' บ.';
    if (!in_array($label, $priceLabels)) $priceLabels[] = $label;
    $priceCounts[$label] = ($priceCounts[$label] ?? 0) + (int)$row['cnt'];
}

$monthLabels  = [];
$monthCounts  = [];
$monthAmounts = [];
foreach (array_reverse($byMonth) as $row) {
    $monthLabels[]  = $row['ym'];
    $monthCounts[]  = (int)$row['cnt'];
    $monthAmounts[] = (float)$row['total_amount'];
}

$this->registerJs("
var ctxPie = document.getElementById('chartByPrice').getContext('2d');
new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: " . json_encode(array_values($priceLabels)) . ",
        datasets: [{
            data: " . json_encode(array_values($priceCounts)) . ",
            backgroundColor: ['#009688','#26a69a','#4db6ac','#80cbc4','#00796b','#80deea','#00838f','#006064'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 14 } } } }
    }
});

var ctxBar = document.getElementById('chartByMonth').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: " . json_encode($monthLabels) . ",
        datasets: [{
            label: 'จำนวนกรมธรรม์',
            data: " . json_encode($monthCounts) . ",
            backgroundColor: '#009688',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { font: { size: 13 } } }, x: { ticks: { font: { size: 12 } } } }
    }
});
", \yii\web\View::POS_END);
?>

<?php $this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['position' => \yii\web\View::POS_HEAD]); ?>

<style>
.db-page { font-family: 'Sarabun','Prompt',Arial,sans-serif; padding: 16px; font-size: 1rem; color: #1a1a1a; }
.db-title { font-size: 1.4rem; font-weight: 700; color: #00796b; margin-bottom: 18px; }
.db-cards { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 28px; }
.db-card {
    flex: 1; min-width: 180px; background: #fff;
    border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.08);
    padding: 22px 24px; text-align: center;
}
.db-card .num { font-size: 2.1rem; font-weight: 700; color: #009688; line-height: 1; }
.db-card .lbl { font-size: 1rem; color: #555; margin-top: 6px; }
.db-section { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 20px 22px; margin-bottom: 28px; }
.db-section h3 { font-size: 1.15rem; font-weight: 700; color: #00796b; border-bottom: 2px solid #b2dfdb; padding-bottom: 8px; margin-bottom: 16px; }
.db-charts { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 28px; }
.db-chart-box { flex: 1; min-width: 280px; background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 20px 22px; }
.db-chart-box h3 { font-size: 1.1rem; font-weight: 700; color: #00796b; margin-bottom: 14px; }
table.db-table { width: 100%; border-collapse: collapse; font-size: 1rem; }
table.db-table th { background: #009688; color: #fff; padding: 9px 12px; text-align: center; font-weight: 600; }
table.db-table td { padding: 8px 12px; border-bottom: 1px solid #e0f2f1; }
table.db-table tr:nth-child(even) td { background: #f5fffe; }
table.db-table tr:hover td { background: #e0f7fa; }
table.db-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; }
table.db-table tfoot td { background: #00796b; color: #fff; font-weight: 700; }
.status-active { color: #009688; font-weight: 600; }
.status-other  { color: #888; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; background: #009688; color: #fff; border: none; border-radius: 8px; padding: 8px 20px; font-size: 1rem; font-weight: 600; text-decoration: none; margin-bottom: 18px; }
.btn-back:hover { background: #00796b; color: #fff; text-decoration: none; }
</style>

<div class="db-page">
    <a href="<?= Url::to(['insurancepolicies/index']) ?>" class="btn-back"><i class="fa fa-arrow-left"></i> กลับหน้ารายการ</a>
    <div class="db-title"><i class="fa fa-bar-chart"></i> Dashboard : สรุปข้อมูลกรมธรรม์</div>

    <section class="db-filter" aria-labelledby="policy-period-heading">
        <?php if ($isAdmin): ?>
            <?= Html::beginForm(['dashboard'], 'get', ['class' => 'db-agency-form']) ?>
                <?= Html::hiddenInput('fiscal_year', $fiscalYear) ?>
                <?php if ($showAll): ?>
                    <?= Html::hiddenInput('all', '1') ?>
                <?php else: ?>
                    <?php if ($month !== null): ?>
                        <?= Html::hiddenInput('month', $month) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="db-agency-field">
                    <?= Html::label('หน่วยงาน / โรงพยาบาล', 'policy-agency') ?>
                    <?= Html::dropDownList('agency', $selectedAgency, $agencies, [
                        'id' => 'policy-agency', 'class' => 'form-control', 'prompt' => 'ทุกหน่วยงาน',
                    ]) ?>
                </div>
                <?= Html::submitButton('แสดงข้อมูล', ['class' => 'btn btn-success']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <h2 id="policy-period-heading"><i class="fa fa-calendar-o" aria-hidden="true"></i> ปีงบประมาณ <?= Html::encode($fiscalYear) ?></h2>
        <div class="db-filter-row" aria-label="เลือกปีงบประมาณ">
            <?php for ($i = 0; $i < 6; $i++): $y = $currentFiscalYear - $i; ?>
                <?= Html::a('ปีงบ ' . $y, array_merge(['dashboard', 'fiscal_year' => $y], $agencyFilter), [
                    'class' => 'btn btn-success' . (!$showAll && $month === null && $y === $fiscalYear ? ' active' : ''),
                    'aria-current' => !$showAll && $month === null && $y === $fiscalYear ? 'true' : null,
                ]) ?>
            <?php endfor; ?>
            <?= Html::a('ทุกช่วงเวลา', array_merge(['dashboard', 'all' => 1, 'fiscal_year' => $fiscalYear], $agencyFilter), [
                'class' => 'btn btn-default' . ($showAll ? ' active' : ''),
                'aria-current' => $showAll ? 'true' : null,
            ]) ?>
        </div>
        <div class="db-filter-row" aria-label="เลือกเดือนในปีงบประมาณ">
            <?= Html::a('ทั้งปีงบ ' . $fiscalYear, array_merge(['dashboard', 'fiscal_year' => $fiscalYear], $agencyFilter), [
                'class' => 'btn btn-default' . (!$showAll && $month === null ? ' active' : ''),
                'aria-current' => !$showAll && $month === null ? 'true' : null,
            ]) ?>
            <?php if ($fiscalYear > 2444): ?>
                <?= Html::a('<i class="fa fa-chevron-left" aria-hidden="true"></i>', array_merge(['dashboard', 'fiscal_year' => $fiscalYear - 1], $agencyFilter), [
                    'class' => 'btn btn-default', 'title' => 'ปีงบก่อน', 'aria-label' => 'ปีงบก่อน',
                ]) ?>
            <?php endif; ?>
            <?php foreach ($months as $number => $label): ?>
                <?php $monthYear = $fiscalYear - ($number >= 10 ? 1 : 0); ?>
                <?= Html::a($label . ' ' . substr((string)$monthYear, -2), array_merge(['dashboard', 'fiscal_year' => $fiscalYear, 'month' => $number], $agencyFilter), [
                    'class' => 'btn btn-default' . (!$showAll && $month === $number ? ' active' : ''),
                    'aria-current' => !$showAll && $month === $number ? 'true' : null,
                    'aria-label' => $label . ' พ.ศ. ' . $monthYear,
                ]) ?>
            <?php endforeach; ?>
            <?php if ($fiscalYear < 10541): ?>
                <?= Html::a('<i class="fa fa-chevron-right" aria-hidden="true"></i>', array_merge(['dashboard', 'fiscal_year' => $fiscalYear + 1], $agencyFilter), [
                    'class' => 'btn btn-default', 'title' => 'ปีงบถัดไป', 'aria-label' => 'ปีงบถัดไป',
                ]) ?>
            <?php endif; ?>
        </div>
        <p class="db-filter-period">
            <?php if ($showAll): ?>
                วันที่ซื้อกรมธรรม์: ทุกช่วงเวลา (รวมรายการที่ไม่มีวันที่ซื้อ)
            <?php else: ?>
                วันที่ซื้อกรมธรรม์: <?= Html::encode($start->format('d/m/') . ((int)$start->format('Y') + 543)) ?>
                ถึง <?= Html::encode($end->modify('-1 day')->format('d/m/') . ((int)$end->modify('-1 day')->format('Y') + 543)) ?> (พ.ศ.)
            <?php endif; ?>
        </p>
    </section>

    <!-- Summary Cards -->
    <div class="db-cards">
        <div class="db-card">
            <div class="num"><?= number_format((int)$totals['cnt']) ?></div>
            <div class="lbl">จำนวนกรมธรรม์ทั้งหมด (ฉบับ)</div>
        </div>
        <div class="db-card">
            <div class="num"><?= number_format((float)$totals['total_amount'], 2) ?></div>
            <div class="lbl">มูลค่ารวมทั้งหมด (บาท)</div>
        </div>
        <div class="db-card">
            <div class="num"><?= count(array_unique(array_column($byAgency, 'agency_code'))) ?></div>
            <div class="lbl">จำนวนหน่วยงาน</div>
        </div>
        <div class="db-card">
            <div class="num"><?= count(array_unique(array_column($byPrice, 'card_price'))) ?></div>
            <div class="lbl">ประเภทราคาบัตร</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="db-charts">
        <div class="db-chart-box">
            <h3><i class="fa fa-pie-chart"></i> สัดส่วนตามราคาบัตร</h3>
            <canvas id="chartByPrice" height="200"></canvas>
        </div>
        <div class="db-chart-box">
            <h3><i class="fa fa-bar-chart"></i> จำนวนกรมธรรม์รายเดือน</h3>
            <canvas id="chartByMonth" height="200"></canvas>
        </div>
    </div>

    <!-- Table: by card_price + policy_status -->
    <div class="db-section">
        <h3><i class="fa fa-table"></i> สรุปจำนวน แยกตามราคาบัตร และสถานะ</h3>
        <div class="db-table-scroll">
        <table class="db-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ราคาบัตร (บาท)</th>
                    <th>สถานะกรมธรรม์</th>
                    <th>จำนวน (ฉบับ)</th>
                    <th>มูลค่ารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; $gtCnt = 0; $gtAmt = 0; ?>
                <?php foreach ($byPrice as $row): ?>
                    <?php $gtCnt += $row['cnt']; $gtAmt += $row['total_amount']; ?>
                    <tr>
                        <td class="num-cell"><?= $i++ ?></td>
                        <td class="num-cell"><?= number_format((float)$row['card_price'], 2) ?></td>
                        <td>
                            <span class="<?= strtolower($row['policy_status']) === 'active' ? 'status-active' : 'status-other' ?>">
                                <?= Html::encode($row['policy_status']) ?>
                            </span>
                        </td>
                        <td class="num-cell"><?= number_format((int)$row['cnt']) ?></td>
                        <td class="num-cell"><?= number_format((float)$row['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;">รวมทั้งหมด</td>
                    <td class="num-cell"><?= number_format($gtCnt) ?></td>
                    <td class="num-cell"><?= number_format($gtAmt, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <!-- Table: by agency + card_price -->
    <div class="db-section">
        <h3><i class="fa fa-building-o"></i> สรุปจำนวน แยกตามหน่วยงาน และราคาบัตร</h3>
        <div style="overflow-x:auto;">
        <table class="db-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>รหัสหน่วยงาน</th>
                    <th>หน่วยงาน</th>
                    <th>ราคาบัตร (บาท)</th>
                    <th>จำนวน (ฉบับ)</th>
                    <th>มูลค่ารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php $j = 1; $gtCnt2 = 0; $gtAmt2 = 0; $lastAgency = null; ?>
                <?php foreach ($byAgency as $row): ?>
                    <?php $gtCnt2 += $row['cnt']; $gtAmt2 += $row['total_amount']; ?>
                    <tr>
                        <td class="num-cell"><?= $j++ ?></td>
                        <td><?= Html::encode($row['agency_code']) ?></td>
                        <td><?= Html::encode($row['agency_name']) ?></td>
                        <td class="num-cell"><?= number_format((float)$row['card_price'], 2) ?></td>
                        <td class="num-cell"><?= number_format((int)$row['cnt']) ?></td>
                        <td class="num-cell"><?= number_format((float)$row['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">รวมทั้งหมด</td>
                    <td class="num-cell"><?= number_format($gtCnt2) ?></td>
                    <td class="num-cell"><?= number_format($gtAmt2, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <!-- Table: by month -->
    <div class="db-section">
        <h3><i class="fa fa-calendar"></i> สรุปรายเดือน<?= $showAll ? ' (ล่าสุด 24 เดือน)' : ' ตามช่วงเวลาที่เลือก' ?></h3>
        <div style="overflow-x:auto;">
        <table class="db-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>เดือน/ปี</th>
                    <th>จำนวน (ฉบับ)</th>
                    <th>มูลค่ารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php $k = 1; $gtCnt3 = 0; $gtAmt3 = 0; ?>
                <?php foreach (array_reverse($byMonth) as $row): ?>
                    <?php $gtCnt3 += $row['cnt']; $gtAmt3 += $row['total_amount']; ?>
                    <tr>
                        <td class="num-cell"><?= $k++ ?></td>
                        <td><?= Html::encode($row['ym']) ?></td>
                        <td class="num-cell"><?= number_format((int)$row['cnt']) ?></td>
                        <td class="num-cell"><?= number_format((float)$row['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right;">รวม</td>
                    <td class="num-cell"><?= number_format($gtCnt3) ?></td>
                    <td class="num-cell"><?= number_format($gtAmt3, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>
