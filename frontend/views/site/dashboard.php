<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\User;
use karatae99\datepicker\DatePicker;

$this->title = 'Dashboard — CF PS';
$this->params['breadcrumbs'][] = $this->title;

$session = Yii::$app->session;
$hoscode = $session['hoscode'];

$checklogin = User::findOne(['user' => $session['user']]);
if (!$checklogin) {
    echo "<script>window.location='index.php?r=site/login';</script>";
}

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

// ── Per-fund extract helpers (non-SSJ) ───────────────────────
if (!Yii::$app->facilityAccess->isProvincialAdmin()) {
    $op_all   = $queryop_all[0]  ?? [];
    $op_a     = $queryop_a[0]    ?? [];
    $op_c     = $queryop_c[0]    ?? [];
    $op_ssj   = $queryop_ssj[0]  ?? [];
    $op_png   = $queryop_png[0]  ?? [];
    $op_tkp   = $queryop_tkp[0]  ?? [];

    $pp_all   = $querypp_all[0]  ?? [];
    $pp_a     = $querypp_a[0]    ?? [];
    $pp_c     = $querypp_c[0]    ?? [];
    $pp_ssj   = $querypp_ssj[0]  ?? [];
    $pp_png   = $querypp_png[0]  ?? [];
    $pp_tkp   = $querypp_tkp[0]  ?? [];

    $ip_all   = $queryipall[0]   ?? [];
    $ip_a     = $queryip_a[0]    ?? [];
    $ip_c     = $queryip_c[0]    ?? [];
    $ip_ssj   = $queryip_ssj[0]  ?? [];
    $ip_png   = $queryip_png[0]  ?? [];
    $ip_tkp   = $queryip_tkp[0]  ?? [];

    $nf = fn($v,$d=0) => number_format($v ?? 0, $d, '.', ',');
}
?>
<br>
<style>
/* ── Reuse shared CF PS palette ────────────────────────────── */
:root{--c-ssj:#1e8449;--c-png:#1a5276;--c-tkp:#784212;--c-total:#4a235a;--c-recv:#880e4f;}
.box-dash    .box-header{background:linear-gradient(135deg,#145a32,#27ae60)!important;border-bottom:0;}
.box-dash    .box-header *{color:#fff!important;}
.cfps-toolbar{background:#f1f8e9;border:1px solid #c8e6c9;border-radius:8px;padding:12px 16px;margin:8px 0 14px;}
.cfps-toolbar h3{color:#2e7d32;font-size:1.1rem;font-weight:700;margin:0 0 10px;}
.cfps-toolbar .btn-fy{font-size:1rem;padding:5px 12px;margin:2px;border-radius:5px;}
.cfps-toolbar .btn-month{font-size:1rem;padding:4px 10px;margin:2px;border-radius:5px;}
.cfps-toolbar .nav-btn{font-size:1rem;padding:5px 12px;}

/* ── KPI info-boxes ─────────────────────────────────────────── */
.kpi-row{display:flex;flex-wrap:wrap;gap:12px;margin:12px 0 18px;}
.kpi-box{flex:1 1 200px;border-radius:8px;padding:16px 18px;color:#fff;min-width:160px;
         box-shadow:0 2px 8px rgba(0,0,0,.13);}
.kpi-box .kpi-icon{font-size:2.4rem;opacity:.25;float:right;margin-top:-6px;}
.kpi-box .kpi-label{font-size:0.88rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;opacity:.85;}
.kpi-box .kpi-value{font-size:2rem;font-weight:700;line-height:1.2;margin:4px 0;}
.kpi-box .kpi-sub{font-size:0.85rem;opacity:.85;}
.kpi-op{background:linear-gradient(135deg,#0d47a1,#1976d2);}
.kpi-pp{background:linear-gradient(135deg,#4a235a,#7b1fa2);}
.kpi-ip{background:linear-gradient(135deg,#7b1f0a,#c62828);}
.kpi-total{background:linear-gradient(135deg,#1b5e20,#388e3c);}

/* ── Summary tables ─────────────────────────────────────────── */
.dash-table{font-size:1rem;margin-bottom:0;}
.dash-table thead th{background:#37474f;color:#fff;vertical-align:middle!important;border-color:#546e7a;white-space:nowrap;padding:8px 12px;}
.dash-table thead th.th-ssj{background:var(--c-ssj);}
.dash-table thead th.th-png{background:var(--c-png);}
.dash-table thead th.th-tkp{background:var(--c-tkp);}
.dash-table thead th.th-total{background:var(--c-total);}
.dash-table thead th.th-recv{background:var(--c-recv);}
.dash-table tbody td{vertical-align:middle;padding:7px 10px;}
.dash-table tbody tr:hover{background:rgba(0,0,0,.035);}
.dash-table .td-ssj{color:var(--c-ssj);font-weight:500;text-align:right;}
.dash-table .td-png{color:var(--c-png);font-weight:500;text-align:right;}
.dash-table .td-tkp{color:var(--c-tkp);font-weight:500;text-align:right;}
.dash-table .td-num{text-align:right;}
.dash-table .td-recv{color:var(--c-recv);font-weight:700;text-align:right;background:#fff8e1;}
.dash-table tr.tr-total td{background:#fff9c4!important;font-weight:700;}
.ac-row-all td{background:#e8f5e9;font-weight:600;}
.ac-row-a td{color:var(--c-ssj);}
.ac-row-c td{color:#c0392b;}
.cfps-info-bar{background:#e3f2fd;border-left:4px solid #1976d2;border-radius:4px;padding:9px 16px;
               font-size:1rem;margin-bottom:12px;color:#0d47a1;}
.section-title{font-size:1.05rem;font-weight:700;margin:18px 0 8px;color:#333;border-bottom:2px solid #e0e0e0;padding-bottom:5px;}
</style>

<div class="user-panel">
<div class="col-md-12">
  <div class="box box-success box-solid box-dash">
    <div class="box-header with-border">
      <h3 class="box-title">
        <i class="fa fa-fw fa-tachometer"></i>
        <strong>Dashboard</strong> &nbsp;|&nbsp; CF PS — สรุปข้อมูลการเรียกเก็บค่ารักษาพยาบาล
      </h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      </div>
    </div>
    <div class="box-body">

      <!-- ── Filter form ─────────────────────────────────────── -->
      <div class="row">
        <div class="col-md-12">
          <p class="text-muted" style="font-size:.82rem;margin-bottom:6px;margin-top:4px;"><i class="fa fa-calendar"></i> <strong>ค้นหาแบบช่วงเวลา</strong></p>
        </div>
        <?php $form = ActiveForm::begin(['action' => ['/site/dashboard'], 'method' => 'get']); ?>
        <div class="panel-body">
          <div class="col-lg-3">
            <?= DatePicker::widget(['name'=>'date1','value'=>$date1,'template'=>'{addon}{input}',
                'language'=>'th','clientOptions'=>['autoclose'=>true,'format'=>'yyyy/mm/dd']]) ?>
          </div>
          <div class="col-lg-3">
            <?= DatePicker::widget(['name'=>'date2','value'=>$date2,'template'=>'{addon}{input}',
                'language'=>'th','clientOptions'=>['autoclose'=>true,'format'=>'yyyy/mm/dd']]) ?>
          </div>
          <div class="col-lg-2">
            <button class="btn btn-block btn-success"><i class="fa fa-search"></i> ค้นหา</button>
          </div>
        </div>
        <?php ActiveForm::end(); ?>
      </div>

      <!-- ── Fiscal year / month toolbar ───────────────────────── -->
      <?php
      if (!empty($back)) {
          $yt = (int)$peengob - 543 - 1;
          $m1 = (int)substr($date2,5,2);
      } elseif (!empty($forward)) {
          $yt = (int)$peengob - 543 + 1;
          $m1 = (int)substr($date2,5,2);
      } elseif (!empty($date1) && !empty($date2)) {
          $m1 = (int)substr($date2,5,2);
          $yt = !empty($peengob) ? (int)$peengob - 543 : (int)substr($date2,0,4) - 543;
      } else {
          $m1 = (int)date('m');
          $yt = (int)date('Y');
      }
      $peengob = $yt + 543;
      $ynow = (int)date('Y') + 543;
      if ($m1 <= 10) { $ynow++; }
      $yOct = $peengob - 1;
      $yJan = $peengob;
      $months = [
          ['ต.ค.',$yOct,'10','31'],['พ.ย.',$yOct,'11','30'],['ธ.ค.',$yOct,'12','31'],
          ['ม.ค.',$yJan,'01','31'],['ก.พ.',$yJan,'02','29'],['มี.ค.',$yJan,'03','31'],
          ['เม.ย.',$yJan,'04','30'],['พ.ค.',$yJan,'05','31'],['มิ.ย.',$yJan,'06','30'],
          ['ก.ค.',$yJan,'07','31'],['ส.ค.',$yJan,'08','31'],['ก.ย.',$yJan,'09','30'],
      ];
      ?>
      <div class="cfps-toolbar">
        <h3><i class="fa fa-fw fa-calendar-o"></i> ปีงบประมาณ <?= $peengob ?></h3>
        <div style="margin-bottom:10px;">
          <?php for ($i = 0; $i < 6; $i++): $y = $ynow - $i; ?>
            <?= Html::a('<i class="fa fa-calendar"></i> ปีงบ '.$y,
                ['/site/dashboard','date1'=>($y-1).'-10-01','date2'=>$y.'-09-30','peengob'=>$y],
                ['class'=>'btn btn-success btn-fy'.($y==$peengob?' active':'')]) ?>
          <?php endfor; ?>
        </div>
        <div>
          <?= Html::a('<i class="fa fa-fw fa-chevron-left"></i>',
              ['/site/dashboard','back'=>'1','date1'=>$date1,'date2'=>$date2,'peengob'=>$peengob],
              ['class'=>'btn btn-default nav-btn','title'=>'ปีก่อน']) ?>
          <?php foreach ($months as [$label,$y,$mm,$lastDay]): ?>
            <?= Html::a($label.' '.substr((string)$y,2,2),
                ['/site/dashboard','date1'=>"{$y}-{$mm}-01",'date2'=>"{$y}-{$mm}-{$lastDay}",'peengob'=>$peengob],
                ['class'=>'btn btn-default btn-month']) ?>
          <?php endforeach; ?>
          <?= Html::a('<i class="fa fa-fw fa-chevron-right"></i>',
              ['/site/dashboard','forward'=>'1','date1'=>$date1,'date2'=>$date2,'peengob'=>$peengob],
              ['class'=>'btn btn-default nav-btn','title'=>'ปีถัดไป']) ?>
        </div>
      </div>

    </div><!-- /.box-body -->
  </div><!-- /.box -->
</div><!-- /.col -->

<!-- ================================================================
     NON-SSJ view  (individual hospital dashboard)
     ================================================================ -->
<?php if (!Yii::$app->facilityAccess->isProvincialAdmin()): ?>

<div class="col-md-12">
  <div class="cfps-info-bar">
    <i class="fa fa-hospital-o"></i> <?= Html::encode($session['hoscode']) ?> | <?= Html::encode($session['orgname'] ?? '') ?>
    &nbsp;&nbsp;<i class="fa fa-calendar-o"></i>
    ข้อมูลตั้งแต่ <strong><?= dashChangeDate($sdate1) ?></strong> ถึง <strong><?= dashChangeDate($edate2) ?></strong>
  </div>
</div>

<!-- ── KPI boxes ──────────────────────────────────────────────── -->
<div class="col-md-12">
  <div class="kpi-row">
    <!-- OP -->
    <div class="kpi-box kpi-op">
      <span class="kpi-icon"><i class="fa fa-stethoscope"></i></span>
      <div class="kpi-label">OP ผู้ป่วยนอก</div>
      <div class="kpi-value"><?= $nf($op_all['claim'] ?? 0, 2) ?></div>
      <div class="kpi-sub"> บาท&nbsp;|&nbsp; ชดเชย <?= $nf($op_all['sq'] ?? 0) ?> ครั้ง</div>
    </div>
    <!-- PP -->
    <div class="kpi-box kpi-pp">
      <span class="kpi-icon"><i class="fa fa-heartbeat"></i></span>
      <div class="kpi-label">PP ส่งเสริม/ป้องกัน</div>
      <div class="kpi-value"><?= $nf($pp_all['claim'] ?? 0, 2) ?> </div>
      <div class="kpi-sub"> บาท &nbsp;|&nbsp; ชดเชย <?= $nf($pp_all['sq'] ?? 0) ?> ครั้ง</div>
    </div>
    <!-- IP -->
    <div class="kpi-box kpi-ip">
      <span class="kpi-icon"><i class="fa fa-bed"></i></span>
      <div class="kpi-label">IP ผู้ป่วยใน</div>
      <div class="kpi-value"><?= $nf($ip_all['costadjrw'] ?? 0, 2) ?></div>
      <div class="kpi-sub"> บาท &nbsp;|&nbsp; AdjRW <?= $nf($ip_all['adjrw'] ?? 0, 4) ?> &nbsp;|&nbsp; <?= $nf($ip_all['an'] ?? 0) ?> AN</div>
    </div>
    <!-- Total claim -->
    <div class="kpi-box kpi-total">
      <span class="kpi-icon"><i class="fa fa-money"></i></span>
      <div class="kpi-label">รวมค่าชดเชย (OP+PP+IP)</div>
      <div class="kpi-value"><?= $nf(($op_all['claim'] ?? 0) + ($pp_all['claim'] ?? 0) + ($ip_all['costadjrw'] ?? 0), 2) ?></div>
      <div class="kpi-sub">บาท</div>
    </div>
  </div>
</div>

<!-- ── OP Summary ─────────────────────────────────────────────── -->
<div class="col-md-12">
  <div class="section-title"><i class="fa fa-stethoscope" style="color:#1976d2"></i> OP — ผู้ป่วยนอก</div>
  <div class="row">
    <div class="col-md-5">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>ประเภท</th><th class="text-right">ครั้ง</th>
          <th class="text-right">ค่ารักษาตามจริง</th><th class="text-right">ค่าชดเชย</th>
        </tr></thead>
        <tbody>
          <tr class="ac-row-all">
            <td><strong>รวมทั้งหมด</strong></td>
            <td class="td-num"><?= $nf($op_all['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($op_all['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($op_all['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-a">
            <td><?= Html::a('<span class="label label-success">A</span> รายชื่อ', ['/opa/index','date1'=>$date1,'date2'=>$date2]) ?></td>
            <td class="td-num"><?= $nf($op_a['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($op_a['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($op_a['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-c">
            <td><?= Html::a('<span class="label label-danger">C</span> รายชื่อ', ['/opc/index','date1'=>$date1,'date2'=>$date2]) ?></td>
            <td class="td-num"><?= $nf($op_c['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($op_c['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($op_c['claim'] ?? 0, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col-md-7">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>กองทุน</th><th class="text-right">ครั้ง</th>
          <th class="text-right">ค่ารักษาตามจริง</th><th class="text-right">ค่าชดเชย</th>
        </tr></thead>
        <tbody>
          <tr><td>รพช.(สสจ)</td>
            <td class="td-ssj"><?= $nf($op_ssj['sq'] ?? 0) ?></td>
            <td class="td-ssj"><?= $nf($op_ssj['alltotal'] ?? 0, 2) ?></td>
            <td class="td-ssj"><?= $nf($op_ssj['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.พังงา</td>
            <td class="td-png"><?= $nf($op_png['sq'] ?? 0) ?></td>
            <td class="td-png"><?= $nf($op_png['alltotal'] ?? 0, 2) ?></td>
            <td class="td-png"><?= $nf($op_png['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.ตะกั่วป่า</td>
            <td class="td-tkp"><?= $nf($op_tkp['sq'] ?? 0) ?></td>
            <td class="td-tkp"><?= $nf($op_tkp['alltotal'] ?? 0, 2) ?></td>
            <td class="td-tkp"><?= $nf($op_tkp['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="tr-total"><td><strong>รวม</strong></td>
            <td class="td-num"><strong><?= $nf(($op_ssj['sq']??0)+($op_png['sq']??0)+($op_tkp['sq']??0)) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($op_ssj['alltotal']??0)+($op_png['alltotal']??0)+($op_tkp['alltotal']??0),2) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($op_ssj['claim']??0)+($op_png['claim']??0)+($op_tkp['claim']??0),2) ?></strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── PP Summary ─────────────────────────────────────────────── -->
<div class="col-md-12">
  <div class="section-title"><i class="fa fa-heartbeat" style="color:#7b1fa2"></i> PP — ส่งเสริมสุขภาพ/ป้องกันโรค</div>
  <div class="row">
    <div class="col-md-5">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>ประเภท</th><th class="text-right">ครั้ง</th>
          <th class="text-right">ค่ารักษาตามจริง</th><th class="text-right">ค่าชดเชย</th>
        </tr></thead>
        <tbody>
          <tr class="ac-row-all">
            <td><strong>รวมทั้งหมด</strong></td>
            <td class="td-num"><?= $nf($pp_all['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($pp_all['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($pp_all['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-a">
            <td><?= Html::a('<span class="label label-success">A</span> รายชื่อ', ['/ppa/index','date1'=>$date1,'date2'=>$date2]) ?></td>
            <td class="td-num"><?= $nf($pp_a['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($pp_a['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($pp_a['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-c">
            <td><?= Html::a('<span class="label label-danger">C</span> รายชื่อ', ['/ppc/index','date1'=>$date1,'date2'=>$date2]) ?></td>
            <td class="td-num"><?= $nf($pp_c['sq'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($pp_c['alltotal'] ?? 0, 2) ?></td>
            <td class="td-num"><?= $nf($pp_c['claim'] ?? 0, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col-md-7">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>กองทุน</th><th class="text-right">ครั้ง</th>
          <th class="text-right">ค่ารักษาตามจริง</th><th class="text-right">ค่าชดเชย</th>
        </tr></thead>
        <tbody>
          <tr><td>รพช.(สสจ)</td>
            <td class="td-ssj"><?= $nf($pp_ssj['sq'] ?? 0) ?></td>
            <td class="td-ssj"><?= $nf($pp_ssj['alltotal'] ?? 0, 2) ?></td>
            <td class="td-ssj"><?= $nf($pp_ssj['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.พังงา</td>
            <td class="td-png"><?= $nf($pp_png['sq'] ?? 0) ?></td>
            <td class="td-png"><?= $nf($pp_png['alltotal'] ?? 0, 2) ?></td>
            <td class="td-png"><?= $nf($pp_png['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.ตะกั่วป่า</td>
            <td class="td-tkp"><?= $nf($pp_tkp['sq'] ?? 0) ?></td>
            <td class="td-tkp"><?= $nf($pp_tkp['alltotal'] ?? 0, 2) ?></td>
            <td class="td-tkp"><?= $nf($pp_tkp['claim'] ?? 0, 2) ?></td>
          </tr>
          <tr class="tr-total"><td><strong>รวม</strong></td>
            <td class="td-num"><strong><?= $nf(($pp_ssj['sq']??0)+($pp_png['sq']??0)+($pp_tkp['sq']??0)) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($pp_ssj['alltotal']??0)+($pp_png['alltotal']??0)+($pp_tkp['alltotal']??0),2) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($pp_ssj['claim']??0)+($pp_png['claim']??0)+($pp_tkp['claim']??0),2) ?></strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── IP Summary ─────────────────────────────────────────────── -->
<div class="col-md-12">
  <div class="section-title"><i class="fa fa-bed" style="color:#c62828"></i> IP — ผู้ป่วยใน</div>
  <div class="row">
    <div class="col-md-5">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>ประเภท</th><th class="text-right">AN</th><th class="text-right">LOS</th>
          <th class="text-right">AdjRW</th><th class="text-right">ค่าชดเชย (AdjRW)</th>
        </tr></thead>
        <tbody>
          <tr class="ac-row-all">
            <td><strong>รวมทั้งหมด</strong></td>
            <td class="td-num"><?= $nf($ip_all['an'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_all['los'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_all['adjrw'] ?? 0, 4) ?></td>
            <td class="td-num"><?= $nf($ip_all['costadjrw'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-a">
            <td><?= Html::a('<span class="label label-success">A</span> รายชื่อ', ['/ipdatainfo/index','date1'=>$date1,'date2'=>$date2]) ?></td>
            <td class="td-num"><?= $nf($ip_a['an'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_a['los'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_a['adjrw'] ?? 0, 4) ?></td>
            <td class="td-num"><?= $nf($ip_a['costadjrw'] ?? 0, 2) ?></td>
          </tr>
          <tr class="ac-row-c">
            <td><span class="label label-danger">C</span></td>
            <td class="td-num"><?= $nf($ip_c['an'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_c['los'] ?? 0) ?></td>
            <td class="td-num"><?= $nf($ip_c['adjrw'] ?? 0, 4) ?></td>
            <td class="td-num"><?= $nf($ip_c['costadjrw'] ?? 0, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col-md-7">
      <table class="table table-bordered dash-table">
        <thead><tr>
          <th>กองทุน</th><th class="text-right">AN</th><th class="text-right">LOS</th>
          <th class="text-right">AdjRW</th><th class="text-right">ค่าชดเชย (AdjRW)</th>
        </tr></thead>
        <tbody>
          <tr><td>รพช.(สสจ)</td>
            <td class="td-ssj"><?= $nf($ip_ssj['an'] ?? 0) ?></td>
            <td class="td-ssj"><?= $nf($ip_ssj['los'] ?? 0) ?></td>
            <td class="td-ssj"><?= $nf($ip_ssj['adjrw'] ?? 0, 4) ?></td>
            <td class="td-ssj"><?= $nf($ip_ssj['costadjrw'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.พังงา</td>
            <td class="td-png"><?= $nf($ip_png['an'] ?? 0) ?></td>
            <td class="td-png"><?= $nf($ip_png['los'] ?? 0) ?></td>
            <td class="td-png"><?= $nf($ip_png['adjrw'] ?? 0, 4) ?></td>
            <td class="td-png"><?= $nf($ip_png['costadjrw'] ?? 0, 2) ?></td>
          </tr>
          <tr><td>รพท.ตะกั่วป่า</td>
            <td class="td-tkp"><?= $nf($ip_tkp['an'] ?? 0) ?></td>
            <td class="td-tkp"><?= $nf($ip_tkp['los'] ?? 0) ?></td>
            <td class="td-tkp"><?= $nf($ip_tkp['adjrw'] ?? 0, 4) ?></td>
            <td class="td-tkp"><?= $nf($ip_tkp['costadjrw'] ?? 0, 2) ?></td>
          </tr>
          <tr class="tr-total"><td><strong>รวม</strong></td>
            <td class="td-num"><strong><?= $nf(($ip_ssj['an']??0)+($ip_png['an']??0)+($ip_tkp['an']??0)) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($ip_ssj['los']??0)+($ip_png['los']??0)+($ip_tkp['los']??0)) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($ip_ssj['adjrw']??0)+($ip_png['adjrw']??0)+($ip_tkp['adjrw']??0),4) ?></strong></td>
            <td class="td-num"><strong><?= $nf(($ip_ssj['costadjrw']??0)+($ip_png['costadjrw']??0)+($ip_tkp['costadjrw']??0),2) ?></strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── Quick links ─────────────────────────────────────────────── -->
<div class="col-md-12" style="margin-top:10px;margin-bottom:20px;">
  <?= Html::a('<i class="fa fa-fw fa-list-alt"></i> ดูรายละเอียด (Index)', ['/site/index','date1'=>$date1,'date2'=>$date2,'peengob'=>$peengob], ['class'=>'btn btn-default btn-sm']) ?>
  <?= Html::a('<i class="fa fa-fw fa-stethoscope"></i> OP Detail', ['/opa/index','date1'=>$date1,'date2'=>$date2], ['class'=>'btn btn-primary btn-sm']) ?>
  <?= Html::a('<i class="fa fa-fw fa-heartbeat"></i> PP Detail', ['/ppa/index','date1'=>$date1,'date2'=>$date2], ['class'=>'btn btn-info btn-sm', 'style'=>'background:#7b1fa2;border-color:#7b1fa2']) ?>
  <?= Html::a('<i class="fa fa-fw fa-bed"></i> IP Detail', ['/ipdatainfo/index','date1'=>$date1,'date2'=>$date2], ['class'=>'btn btn-danger btn-sm']) ?>
</div>

<?php endif; ?>

<!-- ================================================================
     SSJ view  (hoscode == '00064') — per-hospital summary
     ================================================================ -->
<?php if (Yii::$app->facilityAccess->isProvincialAdmin()): ?>

<?php
// Build lookup maps keyed by hospcode
$opMap = []; foreach ($queryssj_op as $r) { $opMap[$r['hospcode']] = $r; }
$ppMap = []; foreach ($queryssj_pp as $r) { $ppMap[$r['hospcode']] = $r; }
$ipMap = []; foreach ($queryssj_ip as $r) { $ipMap[$r['hospcode']] = $r; }

$allHosCodes = array_unique(array_merge(
    array_keys($opMap), array_keys($ppMap), array_keys($ipMap)
));
sort($allHosCodes);

// grand totals (calculate before rendering)
$tot_op_seq = $tot_op_claim = 0;
$tot_pp_seq = $tot_pp_claim = 0;
$tot_ip_an  = $tot_ip_los = $tot_ip_adjrw = $tot_ip_cost = 0;
foreach ($allHosCodes as $hc) {
  $op = $opMap[$hc] ?? [];
  $pp = $ppMap[$hc] ?? [];
  $ip = $ipMap[$hc] ?? [];
  $tot_op_seq   += $op['seq']       ?? 0;
  $tot_op_claim += $op['claim']     ?? 0;
  $tot_pp_seq   += $pp['seq']       ?? 0;
  $tot_pp_claim += $pp['claim']     ?? 0;
  $tot_ip_an    += $ip['an']        ?? 0;
  $tot_ip_los   += $ip['los']       ?? 0;
  $tot_ip_adjrw += $ip['adjrw']     ?? 0;
  $tot_ip_cost  += $ip['costadjrw'] ?? 0;
}
$nfS = fn($v,$d=0) => number_format($v ?? 0, $d, '.', ',');
?>

<div class="col-md-12">
  <div class="cfps-info-bar">
    <i class="fa fa-map-marker"></i> สสจ.พังงา — สรุปทุกโรงพยาบาล
    &nbsp;&nbsp;<i class="fa fa-calendar-o"></i>
    ข้อมูลตั้งแต่ <strong><?= dashChangeDate($sdate1) ?></strong> ถึง <strong><?= dashChangeDate($edate2) ?></strong>
  </div>
</div>



<!-- SSJ KPI boxes (derived from totals) -->
<div class="col-md-12" style="margin-top:8px;">
  <div class="kpi-row">
    <div class="kpi-box kpi-op">
      <span class="kpi-icon"><i class="fa fa-stethoscope"></i></span>
      <div class="kpi-label">OP รวมทุก รพ.</div>
      <div class="kpi-value"><?= $nfS($tot_op_claim, 2) ?></div>
      <div class="kpi-sub"> บาท&nbsp;|&nbsp; ชดเชย <?= $nfS($tot_op_seq, 0) ?> ครั้ง</div>
    </div>
    <div class="kpi-box kpi-pp">
      <span class="kpi-icon"><i class="fa fa-heartbeat"></i></span>
      <div class="kpi-label">PP รวมทุก รพ.</div>
      <div class="kpi-value"><?= $nfS($tot_pp_claim, 2) ?></div>
      <div class="kpi-sub"> บาท&nbsp;|&nbsp; ชดเชย <?= $nfS($tot_pp_seq, 0) ?> ครั้ง</div>
    </div>
    <div class="kpi-box kpi-ip">
      <span class="kpi-icon"><i class="fa fa-bed"></i></span>
      <div class="kpi-label">IP รวมทุก รพ.</div>
      <div class="kpi-value"><?= $nfS($tot_ip_cost, 2) ?></div>
      <div class="kpi-sub">บาท &nbsp;|&nbsp; AdjRW <?= $nfS($tot_ip_adjrw, 4) ?> &nbsp;|&nbsp; <?= $nfS($tot_ip_an, 0) ?> AN</div>
    </div>
    <div class="kpi-box kpi-total">
      <span class="kpi-icon"><i class="fa fa-money"></i></span>
      <div class="kpi-label">รวมค่าชดเชย OP+PP+IP</div>
      <div class="kpi-value"><?= $nfS($tot_op_claim + $tot_pp_claim + $tot_ip_cost, 2) ?></div>
      <div class="kpi-sub">บาท</div>
    </div>
  </div>
</div>

<div class="col-md-12">
  <div class="section-title"><i class="fa fa-hospital-o" style="color:#1a237e"></i> สรุปรายโรงพยาบาล</div>
  <div class="table-responsive">
    <table class="table table-bordered table-hover dash-table">
      <thead>
        <tr>
          <th rowspan="2" style="vertical-align:middle;">รหัส</th>
          <th rowspan="2" style="vertical-align:middle;">ชื่อโรงพยาบาล</th>
          <th colspan="2" class="text-center th-ssj" style="background:#0d47a1;color:#fff;">OP (ผู้ป่วยนอก)</th>
          <th colspan="2" class="text-center" style="background:#4a235a;color:#fff;">PP (ส่งเสริม)</th>
          <th colspan="4" class="text-center" style="background:#c62828;color:#fff;">IP (ผู้ป่วยใน)</th>
        </tr>
        <tr>
          <th class="text-right" style="background:#1565c0;color:#fff;">ครั้ง</th>
          <th class="text-right" style="background:#1565c0;color:#fff;">ค่าชดเชย</th>
          <th class="text-right" style="background:#6a1b9a;color:#fff;">ครั้ง</th>
          <th class="text-right" style="background:#6a1b9a;color:#fff;">ค่าชดเชย</th>
          <th class="text-right" style="background:#b71c1c;color:#fff;">AN</th>
          <th class="text-right" style="background:#b71c1c;color:#fff;">LOS</th>
          <th class="text-right" style="background:#b71c1c;color:#fff;">AdjRW</th>
          <th class="text-right" style="background:#b71c1c;color:#fff;">ค่าชดเชย</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allHosCodes as $hc):
            $op = $opMap[$hc] ?? [];
            $pp = $ppMap[$hc] ?? [];
            $ip = $ipMap[$hc] ?? [];
            $hosname = $op['hosname'] ?? $pp['hosname'] ?? $ip['hosname'] ?? $hc;
        ?>
        <tr>
          <td class="text-center"><?= Html::encode($hc) ?></td>
          <td><?= Html::encode($hosname) ?></td>
          <td class="td-num" style="color:#1565c0;"><?= $nfS($op['seq'] ?? 0) ?></td>
          <td class="td-num" style="color:#1565c0;"><?= $nfS($op['claim'] ?? 0, 2) ?></td>
          <td class="td-num" style="color:#6a1b9a;"><?= $nfS($pp['seq'] ?? 0) ?></td>
          <td class="td-num" style="color:#6a1b9a;"><?= $nfS($pp['claim'] ?? 0, 2) ?></td>
          <td class="td-num" style="color:#b71c1c;"><?= $nfS($ip['an'] ?? 0) ?></td>
          <td class="td-num" style="color:#b71c1c;"><?= $nfS($ip['los'] ?? 0) ?></td>
          <td class="td-num" style="color:#b71c1c;"><?= $nfS($ip['adjrw'] ?? 0, 4) ?></td>
          <td class="td-num td-recv"><?= $nfS($ip['costadjrw'] ?? 0, 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="tr-total">
          <td colspan="2"><strong>รวมทั้งหมด</strong></td>
          <td class="td-num"><strong><?= $nfS($tot_op_seq) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_op_claim, 2) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_pp_seq) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_pp_claim, 2) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_ip_an) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_ip_los) ?></strong></td>
          <td class="td-num"><strong><?= $nfS($tot_ip_adjrw, 4) ?></strong></td>
          <td class="td-num td-recv"><strong><?= $nfS($tot_ip_cost, 2) ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="col-md-12" style="margin-bottom:20px;">
  <?= Html::a('<i class="fa fa-fw fa-list-alt"></i> ดูรายละเอียด (Index)', ['/site/index','date1'=>$date1,'date2'=>$date2,'peengob'=>$peengob], ['class'=>'btn btn-default btn-sm']) ?>
</div>

<?php endif; ?>
</div><!-- /.user-panel -->
