<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use karatae99\datepicker\DatePicker;

$this->registerCssFile('@web/css/deptor-table.css?v=' . filemtime(Yii::getAlias('@webroot/css/deptor-table.css')));

// ── Helper: convert CE date to Thai display ──────────────────
function dashChangeDate($date) {
    $p = explode('-', $date);
    $months = ['01'=>'ม.ค.','02'=>'ก.พ.','03'=>'มี.ค.','04'=>'เม.ย.',
               '05'=>'พ.ค.','06'=>'มิ.ย.','07'=>'ก.ค.','08'=>'ส.ค.',
               '09'=>'ก.ย.','10'=>'ต.ค.','11'=>'พ.ย.','12'=>'ธ.ค.'];
    $y = (int)$p[0] + 543;
    return $p[2].' '.$months[$p[1]].' '.$y;
}

// ── CE view dates for display ────────────────────────────────
$sy1  = substr($date1,0,4);  $sm1  = substr($date1,5,2);  $sd1  = substr($date1,8,2);
$sdate1 = ((int)$sy1-543).'-'.$sm1.'-'.$sd1;
$ey2  = substr($date2,0,4);  $em2  = substr($date2,5,2);  $ed2  = substr($date2,8,2);
$edate2 = ((int)$ey2-543).'-'.$em2.'-'.$ed2;

$this->title = 'รายการลูกหนี้ตามจ่ายในจังหวัด';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
/* ── Reuse shared CF PS palette ────────────────────────────── */
:root {
    --c-ssj: #1e8449;
    --c-png: #1a5276;
    --c-tkp: #784212;
    --c-total: #4a235a;
    --c-recv: #880e4f;
}

.box-dash .box-header {
    background: linear-gradient(135deg, #145a32, #27ae60) !important;
    border-bottom: 0;
}

.box-dash .box-header * {
    color: #fff !important;
}

.cfps-toolbar {
    background: #f1f8e9;
    border: 1px solid #c8e6c9;
    border-radius: 8px;
    padding: 12px 16px;
    margin: 8px 0 14px;
}

.cfps-toolbar h3 {
    color: #2e7d32;
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 10px;
}

.cfps-toolbar .btn-fy {
    font-size: 1rem;
    padding: 5px 12px;
    margin: 2px;
    border-radius: 5px;
}

.cfps-toolbar .btn-month {
    font-size: 1rem;
    padding: 4px 10px;
    margin: 2px;
    border-radius: 5px;
}

.cfps-toolbar .nav-btn {
    font-size: 1rem;
    padding: 5px 12px;
}

/* ── KPI info-boxes ─────────────────────────────────────────── */
.kpi-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 12px 0 18px;
}

.kpi-box {
    flex: 1 1 200px;
    border-radius: 8px;
    padding: 16px 18px;
    color: #fff;
    min-width: 160px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .13);
}

.kpi-box .kpi-icon {
    font-size: 2.4rem;
    opacity: .25;
    float: right;
    margin-top: -6px;
}

.kpi-box .kpi-label {
    font-size: 0.88rem;
    font-weight: 600;
    letter-spacing: .5px;
    text-transform: uppercase;
    opacity: .85;
}

.kpi-box .kpi-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 4px 0;
}

.kpi-box .kpi-sub {
    font-size: 0.85rem;
    opacity: .85;
}

.kpi-op {
    background: linear-gradient(135deg, #0d47a1, #1976d2);
}

.kpi-pp {
    background: linear-gradient(135deg, #4a235a, #7b1fa2);
}

.kpi-ip {
    background: linear-gradient(135deg, #7b1f0a, #c62828);
}

.kpi-total {
    background: linear-gradient(135deg, #1b5e20, #388e3c);
}

/* ── Summary tables ─────────────────────────────────────────── */
.dash-table {
    font-size: 1rem;
    margin-bottom: 0;
}

.dash-table thead th {
    background: #37474f;
    color: #fff;
    vertical-align: middle !important;
    border-color: #546e7a;
    white-space: nowrap;
    padding: 8px 12px;
}

.dash-table thead th.th-ssj {
    background: var(--c-ssj);
}

.dash-table thead th.th-png {
    background: var(--c-png);
}

.dash-table thead th.th-tkp {
    background: var(--c-tkp);
}

.dash-table thead th.th-total {
    background: var(--c-total);
}

.dash-table thead th.th-recv {
    background: var(--c-recv);
}

.dash-table tbody td {
    vertical-align: middle;
    padding: 7px 10px;
}

.dash-table tbody tr:hover {
    background: rgba(0, 0, 0, .035);
}

.dash-table .td-ssj {
    color: var(--c-ssj);
    font-weight: 500;
    text-align: right;
}

.dash-table .td-png {
    color: var(--c-png);
    font-weight: 500;
    text-align: right;
}

.dash-table .td-tkp {
    color: var(--c-tkp);
    font-weight: 500;
    text-align: right;
}

.dash-table .td-num {
    text-align: right;
}

.dash-table .td-recv {
    color: var(--c-recv);
    font-weight: 700;
    text-align: right;
    background: #fff8e1;
}

.dash-table tr.tr-total td {
    background: #fff9c4 !important;
    font-weight: 700;
}

.ac-row-all td {
    background: #e8f5e9;
    font-weight: 600;
}

.ac-row-a td {
    color: var(--c-ssj);
}

.ac-row-c td {
    color: #c0392b;
}

.cfps-info-bar {
    background: #e3f2fd;
    border-left: 4px solid #1976d2;
    border-radius: 4px;
    padding: 9px 16px;
    font-size: 1rem;
    margin-bottom: 12px;
    color: #0d47a1;
}

.section-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 18px 0 8px;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 5px;
}

/* ── Date range info bar ──────────────────────────────────── */
.deptor-date-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(90deg, #e3f2fd 0%, #f8fafc 100%);
    border-left: 5px solid #1976d2;
    border-radius: 6px;
    padding: 10px 20px;
    margin-bottom: 16px;
    font-size: 1rem;
    box-shadow: 0 1px 4px rgba(25, 118, 210, .08);
}

.deptor-date-bar .dbar-label {
    color: #555;
    font-weight: 600;
}

.deptor-date-bar .dbar-date {
    color: #1565c0;
    font-weight: 700;
    font-size: 1.08rem;
}

.deptor-date-bar .dbar-arrow {
    color: #90a4ae;
    font-size: 0.9rem;
}

/* ── Panel header gradient ────────────────────────────────── */
.box-main>.box-header {
    background: linear-gradient(135deg, #1b5e20, #43a047) !important;
    border-bottom: 0;
}

.box-main>.box-header * {
    color: #fff !important;
}

.deptor-index {
    padding: 0 4px;
    position: relative;
    z-index: 1;
    background: #fff;
    overflow: hidden;
    margin-bottom: 30px;
}
</style>
<div class="deptor-index box box-main">
    <div class="col-md-12">
    <!-- เพิ่มระยะห่างจากด้านบน -->
        <div class="deptor-date-bar" style="margin-top:18px;">
            <i class="fa fa-dollar" style="color:#1976d2;font-size:1.2rem;"></i>
            <?= Html::encode($this->title) ?>
        </div>

            <div class="box-body">
                <div class="col-md-12">

                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-muted" style="font-size:0.82rem;margin-bottom:6px;margin-top:10px;"><i
                                    class="fa fa-calendar"></i> <strong>ค้นหาแบบช่วงเวลา</strong></p>
                        </div>
                        <?php
                  
                    $form = ActiveForm::begin([
                      'action' => ['index'],
                      'method' => 'get',
                  ]); 
                
                  ?>
                        <div class="panel-body">
                            <div class="col-lg-3">

                                <?= DatePicker::widget([
                  'name' => 'date1',
                  'value' => $date1,
                  'template' => '{addon}{input}',
                  'language' => 'th', // Thai B.E.
                      'clientOptions' => [
                          'autoclose' => true,
                          'format' => 'yyyy/mm/dd'
                      ]
              ]);?>

                            </div>
                            <div class="col-lg-3">

                                <?= DatePicker::widget([
                  'name' => 'date2',
                  'value' => $date2,
                  'template' => '{addon}{input}',
                  'language' => 'th', // Thai B.E.
                      'clientOptions' => [
                          'autoclose' => true,
                          'format' => 'yyyy/mm/dd'
                      ]
              ]);?>
                            </div>
                            <div class="col-lg-2">
                                <button class='btn btn-block btn-success'><i class="fa fa-search"></i> ค้นหา</button>
                            </div>

                        </div>
                        <?php  ActiveForm::end(); ?>
                    </div>
                    <?php
// ── Determine fiscal year cursor ($yt = CE year, $m1 = reference month) ──────
// NOTE: dates from the datepicker are in Thai BE (e.g. 2569-09-30), so they
//       need -543 to convert to CE. date('Y-m-d') is already CE – no conversion.
if (!empty($back)) {
    $yt = (int)$peengob - 543 - 1;
    $m1 = (int)substr($date2, 5, 2);
} elseif (!empty($forward)) {
    $yt = (int)$peengob - 543 + 1;
    $m1 = (int)substr($date2, 5, 2);
} elseif (!empty($date1) && !empty($date2)) {
    // Datepicker values are BE → subtract 543 to get CE
    $m1 = (int)substr($date2, 5, 2);
    $yt = !empty($peengob) ? (int)$peengob - 543 : (int)substr($date2, 0, 4) - 543;
} else {
    // No parameters: use system date (CE – no conversion needed)
    $m1 = (int)date('m');
    $yt = (int)date('Y');
}

// ── Thai fiscal year label (Buddhist Era / พ.ศ.) ─────────────────────────────
$peengob = $yt + 543;

// ── Current fiscal year for 6-year quick-select (preserves original logic) ───
$ynow = (int)date('Y') + 543;
if ($m1 <= 10) { $ynow++; }

// ── Month table: Oct–Dec → year ($peengob-1),  Jan–Sep → year $peengob ───────
$yOct   = $peengob - 1;
$yJan   = $peengob;
$months = [
    ['ต.ค.',  $yOct, '10', '31'],
    ['พ.ย.',  $yOct, '11', '30'],
    ['ธ.ค.',  $yOct, '12', '31'],
    ['ม.ค.',  $yJan, '01', '31'],
    ['ก.พ.',  $yJan, '02', '29'],
    ['มี.ค.', $yJan, '03', '31'],
    ['เม.ย.', $yJan, '04', '30'],
    ['พ.ค.',  $yJan, '05', '31'],
    ['มิ.ย.', $yJan, '06', '30'],
    ['ก.ค.',  $yJan, '07', '31'],
    ['ส.ค.',  $yJan, '08', '31'],
    ['ก.ย.',  $yJan, '09', '30'],
];
?>

                    <div class="cfps-toolbar">
                        <h3><i class="fa fa-fw fa-calendar-o"></i> ปีงบประมาณ <?= $peengob ?></h3>

                        <?php /* ── 6-year quick-select ─────────────────────────── */ ?>
                        <div style="margin-bottom:10px;">
                            <?php for ($i = 0; $i < 6; $i++): $y = $ynow - $i; ?>
                            <?= Html::a('<i class="fa fa-calendar"></i> ปีงบ ' . $y,
          ['/deptor/index', 'date1' => ($y - 1) . '-10-01', 'date2' => $y . '-09-30', 'peengob' => $y],
          ['class' => 'btn btn-success btn-fy' . ($y == $peengob ? ' active' : '')]) ?>
                            <?php endfor; ?>
                        </div>

                        <?php /* ── Month navigation ─────────────────────────────── */ ?>
                        <div>
                            <?= Html::a('<i class="fa fa-fw fa-chevron-left"></i>',
        ['/deptor/index', 'back' => '1', 'date1' => $date1, 'date2' => $date2, 'peengob' => $peengob],
        ['class' => 'btn btn-default nav-btn', 'title' => 'ปีก่อน']) ?>
                            <?php foreach ($months as [$label, $y, $mm, $lastDay]): ?>
                            <?= Html::a($label . ' ' . substr((string)$y, 2, 2),
            ['/deptor/index', 'date1' => "{$y}-{$mm}-01", 'date2' => "{$y}-{$mm}-{$lastDay}", 'peengob' => $peengob],
            ['class' => 'btn btn-default btn-month']) ?>
                            <?php endforeach; ?>
                            <?= Html::a('<i class="fa fa-fw fa-chevron-right"></i>',
        ['/deptor/index', 'forward' => '1', 'date1' => $date1, 'date2' => $date2, 'peengob' => $peengob],
        ['class' => 'btn btn-default nav-btn', 'title' => 'ปีถัดไป']) ?>
                        </div>
                    </div>
                </div>


            </div>
            <!-- /.box-body -->
    </div>

                <!-- KPI boxes: ยอดเบิกรวมแต่ละประเภท -->
                <?php
                // สรุปยอดเบิกจากข้อมูลทุกแถว ไม่ขึ้นกับหน้าที่กำลังแสดง
                $sumClaimOP = 0;
                $sumClaimPP = 0;
                $sumClaimIP = 0;
                $sumAllOP   = 0;
                $sumAllPP   = 0;
                $sumAllIP   = 0;
                foreach ($dataProvider->allModels as $row) {
                        $sumClaimOP += $row['claim'] ?? 0;
                        $sumAllOP   += $row['alltotal'] ?? 0;
                }
                foreach ($dataProvider2->allModels as $row) {
                        $sumClaimPP += $row['claim'] ?? 0;
                        $sumAllPP   += $row['alltotal'] ?? 0;
                }
                foreach ($dataProvider3->allModels as $row) {
                        $sumClaimIP += $row['claim'] ?? 0;
                        $sumAllIP   += $row['alltotal'] ?? 0;
                }
                $nf = fn($v,$d=2) => number_format($v ?? 0, $d, '.', ',');
                ?>
                <div class="col-md-12">
                    <div class="kpi-row">
                        <!-- OP -->
                        <div class="kpi-box kpi-op">
                            <span class="kpi-icon"><i class="fa fa-stethoscope"></i></span>
                            <div class="kpi-label">OP ผู้ป่วยนอก</div>
                            <div class="kpi-value"><?= $nf($sumClaimOP, 2) ?></div>
                            <div class="kpi-sub">บาท</div>
                        </div>
                        <!-- PP -->
                        <div class="kpi-box kpi-pp">
                            <span class="kpi-icon"><i class="fa fa-heartbeat"></i></span>
                            <div class="kpi-label">PP ส่งเสริม/ป้องกัน</div>
                            <div class="kpi-value"><?= $nf($sumClaimPP, 2) ?></div>
                            <div class="kpi-sub">บาท</div>
                        </div>
                        <!-- IP -->
                        <div class="kpi-box kpi-ip">
                            <span class="kpi-icon"><i class="fa fa-bed"></i></span>
                            <div class="kpi-label">IP ผู้ป่วยใน</div>
                            <div class="kpi-value"><?= $nf($sumClaimIP, 2) ?></div>
                            <div class="kpi-sub">บาท</div>
                        </div>
                        <!-- Total claim -->
                        <div class="kpi-box kpi-total">
                            <span class="kpi-icon"><i class="fa fa-money"></i></span>
                            <div class="kpi-label">รวมยอดเบิก (OP+PP+IP)</div>
                            <div class="kpi-value"><?= $nf($sumClaimOP + $sumClaimPP + $sumClaimIP, 2) ?></div>
                            <div class="kpi-sub">บาท</div>
                        </div>
                    </div>
                </div>
            <!-- /.box-header -->

    <!-- /.col -->
    <div class="col-md-12" style="margin-top:14px;background:#fff;position:relative;z-index:2;">
        <div class="deptor-date-bar">
            <i class="fa fa-calendar-o fa-fw" style="color:#1976d2;font-size:1.2rem;"></i>
            <span class="dbar-label">ช่วงวันที่</span>
            <span class="dbar-date"><?= Html::encode($dq1) ?></span>
            <span class="dbar-arrow"><i class="fa fa-long-arrow-right"></i></span>
            <span class="dbar-date"><?= Html::encode($dq2) ?></span>
        </div>
        <?= $this->render("_table", ["type" => "op", "provider" => $dataProvider]) ?>
        <?= $this->render("_table", ["type" => "pp", "provider" => $dataProvider2]) ?>
        <?= $this->render("_table", ["type" => "ip", "provider" => $dataProvider3]) ?>
    </div>
</div>
