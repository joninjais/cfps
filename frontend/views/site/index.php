<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\db\ActiveQuery;
use yii\db\Query;
use frontend\models\User;
use karatae99\datepicker\DatePicker;
// PHP 8.1+ deprecates null in number_format(); null-safe wrapper treats empty results as 0
$nf = static fn($num, int $decimals = 0, ?string $decPoint = '.', ?string $thousandsSep = ','): string
    => number_format((float)($num ?? 0), $decimals, $decPoint, $thousandsSep);
//check สิทธการเข้าใช้



$this->title = 'โปรแกรมเคลมต่างด้าว Foreigner work fund Phang-nga';
$this->params['breadcrumbs'][] = $this->title;

/* @var $this yii\web\View */
$session = Yii::$app->session;
$hoscode=$session['hoscode'];

$checklogin = User::findOne(['user'=>$session['user'],]);
if(!$checklogin){
    echo "<script>window.location='index.php?r=site/login';</script>";
}
?>
<br>
<?php
/*
$time = time();
//$time = '2015-06-03 13:25:17';
echo Yii::$app->thaiFormatter->asDate($time, 'short')."<br>";
echo Yii::$app->thaiFormatter->asDate($time, 'medium')."<br>";
echo Yii::$app->thaiFormatter->asDate($time, 'long')."<br>";
echo Yii::$app->thaiFormatter->asDate($time, 'full')."<br>";

echo Yii::$app->thaiFormatter->asDateTime($time, 'short')."<br>";
echo Yii::$app->thaiFormatter->asDateTime($time, 'medium')."<br>";
echo Yii::$app->thaiFormatter->asDateTime($time, 'long')."<br>";
echo Yii::$app->thaiFormatter->asDateTime($time, 'full')."<br>";

echo Yii::$app->thaiFormatter->asDate($time, 'php:Y-m-d')."<br>";
echo Yii::$app->thaiFormatter->asDateTime($time, 'php:Y-m-d H:i:s');
*/
#print_r($queryip_tkp);
#echo $queryip_tpk[0]['an'];


 $ssj_an=isset($queryip_ssj[0]['an']) ? ($queryip_ssj[0]['an']) : '0' ;
 $ssj_los=isset($queryip_ssj[0]['los']) ? ($queryip_ssj[0]['los']) : '0' ;
 $ssj_cost=isset($queryip_ssj[0]['cost']) ? ($queryip_ssj[0]['cost']) : '0' ;
 $ssj_adjrw=isset($queryip_ssj[0]['adjrw']) ? ($queryip_ssj[0]['adjrw']) : '0';
 $ssj_costadjrw=isset($queryip_ssj[0]['costadjrw']) ? ($queryip_ssj[0]['costadjrw']) : '0' ;

 $png_an=isset($queryip_png[0]['an']) ? ($queryip_png[0]['an']) : '0' ;
 $png_los=isset($queryip_png[0]['los']) ? ($queryip_png[0]['los']) : '0';
 $png_cost=isset($queryip_png[0]['cost']) ? ($queryip_png[0]['cost']) : '0';
 $png_adjrw=isset($queryip_png[0]['adjrw']) ? ($queryip_png[0]['adjrw']) : '0' ;
 $png_costadjrw=isset($queryip_png[0]['costadjrw']) ? ($queryip_png[0]['costadjrw']) : '0' ;

 $tpk_an= isset($queryip_tkp[0]['an']) ? ($queryip_tkp[0]['an']) : '0' ;
 $tpk_los=isset($queryip_tkp[0]['los']) ? ($queryip_tkp[0]['los']) : '0' ;
 $tpk_cost=isset($queryip_tkp[0]['cost']) ? ($queryip_tkp[0]['cost']) : '0' ;
 $tpk_adjrw=isset($queryip_tkp[0]['adjrw']) ? ($queryip_tkp[0]['adjrw']) : '0' ;
 $tpk_costadjrw=isset($queryip_tkp[0]['costadjrw']) ? ($queryip_tkp[0]['costadjrw']) : '0' ;

############# PP ######################

  $ppssj_sq=isset($querypp_ssj[0]['sq'])?($querypp_ssj[0]['sq']):'0';
  $ppssj_alltotal=isset($querypp_ssj[0]['alltotal'])?($querypp_ssj[0]['alltotal']):'0';
  $ppssj_claim=isset($querypp_ssj[0]['claim'])?($querypp_ssj[0]['claim']):'0'; 
  $pppng_sq=isset($querypp_png[0]['sq'])?($querypp_png[0]['sq']):'0';
  $pppng_alltotal=isset($querypp_png[0]['alltotal'])?($querypp_png[0]['alltotal']):'0'; 
  $pppng_claim=isset($querypp_png[0]['claim'])?($querypp_png[0]['claim']):'0';
  $pptkp_sq=isset($querypp_tkp[0]['sq'])?($querypp_tkp[0]['sq']):'0';
  $pptkp_alltotal=isset($querypp_tkp[0]['alltotal'])?($querypp_tkp[0]['alltotal']):'0'; 
  $pptkp_claim=isset($querypp_tkp[0]['claim'])?($querypp_tkp[0]['claim']):'0'; 

############# OP ######################

$opssj_sq=isset($queryop_ssj[0]['sq'])?($queryop_ssj[0]['sq']):'0';
$opssj_alltotal=isset($queryop_ssj[0]['alltotal'])?($queryop_ssj[0]['alltotal']):'0';
$opssj_claim=isset($queryop_ssj[0]['claim'])?($queryop_ssj[0]['claim']):'0'; 
$oppng_sq=isset($queryop_png[0]['sq'])?($queryop_png[0]['sq']):'0';
$oppng_alltotal=isset($queryop_png[0]['alltotal'])?($queryop_png[0]['alltotal']):'0'; 
$oppng_claim=isset($queryop_png[0]['claim'])?($queryop_png[0]['claim']):'0';
$optkp_sq=isset($queryop_tkp[0]['sq'])?($queryop_tkp[0]['sq']):'0';
$optkp_alltotal=isset($queryop_tkp[0]['alltotal'])?($queryop_tkp[0]['alltotal']):'0'; 
$optkp_claim=isset($queryop_tkp[0]['claim'])?($queryop_tkp[0]['claim']):'0'; 


function changeDate($date){
  //ใช้ Function explode ในการแยกไฟล์ ออกเป็น  Array
  $get_date = explode("-",$date);
  //กำหนดชื่อเดือนใส่ตัวแปร $month
      $month = array("01"=>"ม.ค.","02"=>"ก.พ","03"=>"มี.ค.","04"=>"เม.ย.","05"=>"พ.ค.","06"=>"มิ.ย.","07"=>"ก.ค.","08"=>"ส.ค.","09"=>"ก.ย.","10"=>"ต.ค.","11"=>"พ.ย.","12"=>"ธ.ค.");
  //month
  $get_month = $get_date["1"];
   
  //year    
  $year = $get_date["0"]+543;
   
  return $get_date["2"]." ".$month[$get_month]." ".$year;
}

$sy1=substr($date1, 0, 4);
$syt1=$sy1-543;
$sm1=substr($date1, 5, 2);
$sd1=substr($date1, 8, 2);
$sdq1=$syt1.'-'.$sm1.'-'.$sd1;
$sbd1=543+$syt1.'-'.$sm1.'-'.$sd1; //ค.ศ.
$sdate1=$sdq1; // พ.ศ.

//$dq1=วันที่เริ่มต้น
$ey2=substr($date2, 0, 4);
$eyt2=$ey2-543;
$em2=substr($date2, 5, 2);
$ed2=substr($date2, 8, 2);
$edq2=$eyt2.'-'.$em2.'-'.$ed2;
$ebd2=543+$eyt2.'-'.$em2.'-'.$ed2;
$edate2=$edq2;

  //การเรียกใช้งาน Function

//print_r($doublefwfd);
//echo $doublefwfd[0]['CID'];
//echo $doublefwfd[0]['FundHolderID'];

/*
use frontend\models\Fwfdatabase;
$Fwfall = Fwfdatabase::findAll([
  //'CID' => '0082011088593',  // 11353 11353
  //'CID' => '00000MC415090',  // 10740 11350
  //'CID' => '00000ME007258', // 10740 11350
  //'CID' => '00000MC581761', // 10740 10740
  //'CID' => '00000MC158131', // 11353
   'CID' => '0000CC6109036', // c
]);

$mt=0;
if(!empty($Fwfall)){
  foreach ($Fwfall as $key => $value) {
    echo $Fwfall[$key]['FundHolderID'];
    echo '</br>';
    
    if($Fwfall[$key]['FundHolderID'] == '10739' || $Fwfall[$key]['FundHolderID'] == '10740' ){
      $mt=$mt+1;
      
    }
    else{ $mt=$mt+0;
    
    }
    if($mt<=1 && $Fwfall[$key]['FundHolderID'] !== '10739' || $Fwfall[$key]['FundHolderID'] !== '10740' ){
    $hosb=$Fwfall[$key]['FundHolderID'];
    }
}// foreach
//echo $mt;
if($mt==2){ echo "เบิก ".$hosb=$Fwfall[0]['FundHolderID']; } else{ echo "เบิก สสจ ".$hosb; }
}
if(empty($hosb)){
  echo $hosb="not found";
}
*/

?>



<style>
/* ============================================================
   CF PS Dashboard — Visual Enhancement Styles
   ============================================================ */

/* ── Colour palette variables ──────────────────────────────── */
:root {
  --c-ssj:   #1e8449;
  --c-png:   #1a5276;
  --c-tkp:   #784212;
  --c-total: #4a235a;
  --c-recv:  #880e4f;
  --c-a:     #1e8449;
  --c-c:     #c0392b;
}

/* ── Section boxes ─────────────────────────────────────────── */
.box-main  .box-header { background: linear-gradient(135deg,#145a32,#27ae60) !important; border-bottom:0; }
.box-op    .box-header { background: linear-gradient(135deg,#0d47a1,#1976d2) !important; border-bottom:0; }
.box-pp    .box-header { background: linear-gradient(135deg,#4a235a,#7b1fa2) !important; border-bottom:0; }
.box-ip    .box-header { background: linear-gradient(135deg,#7b1f0a,#c62828) !important; border-bottom:0; }
.box-rpst  .box-header { background: linear-gradient(135deg,#0e4e1e,#2e7d32) !important; border-bottom:0; }
.box-ssj   .box-header { background: linear-gradient(135deg,#1a237e,#283593) !important; border-bottom:0; }
.box-main  .box-header *,
.box-op    .box-header *,
.box-pp    .box-header *,
.box-ip    .box-header *,
.box-rpst  .box-header *,
.box-ssj   .box-header * { color:#fff !important; }

/* ── Badge pills for sections ──────────────────────────────── */
.badge-op   { background:#1976d2; color:#fff; }
.badge-pp   { background:#7b1fa2; color:#fff; }
.badge-ip   { background:#c62828; color:#fff; }

/* ── Year / month toolbar ──────────────────────────────────── */
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

/* ── Main data tables ──────────────────────────────────────── */
.cfps-table {
  font-size: 1rem;
  margin-bottom: 0;
}
.cfps-table thead th {
  background: #37474f;
  color: #fff;
  vertical-align: middle !important;
  border-color: #546e7a;
  white-space: nowrap;
  padding: 8px 12px;
}
.cfps-table thead th.th-ssj   { background: #1a6b3c; }
.cfps-table thead th.th-png   { background: #0d47a1; }
.cfps-table thead th.th-tkp   { background: #6d3a10; }
.cfps-table thead th.th-total { background: #3d1060; }
.cfps-table thead th.th-recv  { background: #880e4f; }
.cfps-table tbody td          { vertical-align: middle; padding: 7px 10px; }
.cfps-table tbody tr:hover    { background: rgba(0,0,0,.035); }
.cfps-table .td-ssj   { color: var(--c-ssj);   font-weight: 500; text-align: right; }
.cfps-table .td-png   { color: var(--c-png);   font-weight: 500; text-align: right; }
.cfps-table .td-tkp   { color: var(--c-tkp);   font-weight: 500; text-align: right; }
.cfps-table .td-recv  { color: var(--c-recv);  font-weight: 700; text-align: right; background: #fff8e1; }
.cfps-table .td-a     { color: var(--c-a);     text-align: right; }
.cfps-table .td-c     { color: var(--c-c);     text-align: right; }
.cfps-table .td-num   { text-align: right; }
.cfps-table tr.tr-total td { background: #fff9c4 !important; font-weight: 700; }
.cfps-table td.td-hosname { max-width: 200px; }

/* ── A / C summary table ───────────────────────────────────── */
.ac-table { font-size: 1rem; }
.ac-table thead th { background: #455a64; color: #fff; white-space: nowrap; padding: 8px 12px; }
.ac-table .row-all  td { background: #e8f5e9; font-weight: 600; }
.ac-table .row-a    td { color: var(--c-a); }
.ac-table .row-a    td a { color: var(--c-a); font-weight: 600; }
.ac-table .row-c    td { color: var(--c-c); }
.ac-table .row-c    td a { color: var(--c-c); font-weight: 600; }
.ac-table .row-label td { background: #f5f5f5; font-size: 0.92rem; color: #555; padding: 3px 10px; }

/* ── Info bar above each section ───────────────────────────── */
.cfps-info-bar {
  background: #e3f2fd;
  border-left: 4px solid #1976d2;
  border-radius: 4px;
  padding: 9px 16px;
  font-size: 1rem;
  margin-bottom: 12px;
  color: #0d47a1;
}

/* ── Quick action links ────────────────────────────────────── */
.cfps-link-list a {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 5px 8px;
  border-radius: 4px;
  color: #1b5e20;
  font-size: 1rem;
  transition: background .15s;
}
.cfps-link-list a:hover { background: #e8f5e9; text-decoration: none; }
.cfps-link-list .fa { width: 16px; text-align: center; color: #2e7d32; }

/* ── Fix h5 inside table cells ─────────────────────────────── */
table.table td h5,
table.table th h5,
table.cfps-table td h5,
table.cfps-table th h5 {
  margin: 0 !important;
  padding: 0 !important;
  font-size: 1rem !important;
  font-weight: 500 !important;
  line-height: 1.4 !important;
  display: block;
}

/* ── SSJ / sub-district master table ───────────────────────── */
.ssj-table { font-size: 1rem; }
.ssj-table thead th     { vertical-align: middle !important; text-align: center; padding: 8px 10px; }
.ssj-table tbody td     { vertical-align: middle !important; padding: 6px 10px; text-align: right; }
.ssj-table tbody td:first-child { text-align: center; }
.ssj-table tbody td:nth-child(2) { text-align: left; }
.ssj-table tbody td:last-child  { background: #fff8e1 !important; color: #c2185b !important; font-weight: 700; }
.ssj-table tr:hover td  { background: rgba(0,0,0,.035); }
.ssj-table .td-recv     { background: #fff8e1 !important; font-weight: 700; text-align: right; }

/* column group headers */
.ssj-table thead th.th-ssj   { background: var(--c-ssj); }
.ssj-table thead th.th-png   { background: var(--c-png); }
.ssj-table thead th.th-tkp   { background: var(--c-tkp); }
.ssj-table thead th.th-total { background: var(--c-total); }
.ssj-table thead th.th-recv  { background: var(--c-recv); }
.ssj-table tr.tr-total td    { background: #fff9c4 !important; font-weight: 700; }

/* ── Responsive tweak for month row ───────────────────────── */
.cfps-menu-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 768px) {
  .cfps-menu-grid { grid-template-columns: 1fr; }
  .cfps-toolbar .btn-month { font-size: 0.88rem; padding: 3px 6px; }
  .ssj-table { font-size: 0.88rem; }
}

</style>

<div class="user-panel">

        <div class="col-md-12">
          <div class="box box-success box-solid box-main">
            <div class="box-header with-border">
              <h3 class="box-title">
                <i class="fa fa-fw fa-heartbeat"></i>
                <strong>CF PS</strong> &nbsp;|&nbsp; Claim Foreigner Work Fund &amp; Phang-nga System
              </h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
          <div class="box-body">
            <div class="cfps-menu-grid" aria-label="เมนูทางลัด">
              <section class="cfps-menu-card" aria-labelledby="cfps-menu-start">
                <header class="cfps-menu-heading">
                  <span class="cfps-menu-heading-icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                  <span>
                    <h4 id="cfps-menu-start">คู่มือและเครื่องมือ</h4>
                    <p>เอกสารประกอบและเครื่องมือจัดการข้อมูล</p>
                  </span>
                </header>
                <nav aria-label="คู่มือและเครื่องมือ">
                  <ul class="cfps-menu-links">
                    <li>
                      <a href="/manual.html" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-book" aria-hidden="true"></i>
                        <span>คู่มือการใช้งาน<small>แนะนำขั้นตอนการใช้งานระบบ</small></span>
                        <i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li>
                      <a href="<?= \yii\helpers\Url::to(['/sqlcode/index']) ?>">
                        <i class="fa fa-code" aria-hidden="true"></i>
                        <span><?= $hoscode == '00064' ? 'จัดการคำสั่ง SQL นำเข้าข้อมูล' : 'คำสั่ง SQL นำเข้าข้อมูล' ?><small>ดูคำสั่งสำหรับนำเข้าข้อมูล</small></span>
                        <i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li>
                      <a href="/pdpa.html" target="_blank" rel="noopener noreferrer">
                        <i class="fa fa-shield" aria-hidden="true"></i>
                        <span>นโยบาย PDPA<small>แนวทางการคุ้มครองข้อมูลส่วนบุคคล</small></span>
                        <i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i>
                      </a>
                    </li>
                  </ul>
                </nav>
              </section>

              <section class="cfps-menu-card" aria-labelledby="cfps-menu-import">
                <header class="cfps-menu-heading">
                  <span class="cfps-menu-heading-icon"><i class="fa fa-cloud-upload" aria-hidden="true"></i></span>
                  <span>
                    <h4 id="cfps-menu-import">นำเข้าข้อมูล</h4>
                    <p>เลือกประเภทไฟล์ตามข้อมูลที่ต้องการนำเข้า</p>
                  </span>
                </header>
                <nav aria-label="นำเข้าข้อมูล">
                  <ul class="cfps-menu-links">
                <?php if($hoscode == '00064'): ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/create', 'typefile' => 'fwfdata']) ?>"><i class="fa fa-upload" aria-hidden="true"></i><span>อัปโหลดไฟล์ FWF<small>นำเข้าข้อมูล FWF</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                <?php endif; ?>
                <?php if($hoscode != '00064'): ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/create', 'typefile' => 'op pp']) ?>"><i class="fa fa-upload" aria-hidden="true"></i><span>อัปโหลดไฟล์ OP PP<small>ข้อมูลผู้ป่วยนอกและส่งเสริมสุขภาพ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/create', 'typefile' => 'ip']) ?>"><i class="fa fa-upload" aria-hidden="true"></i><span>อัปโหลดไฟล์ IP<small>ข้อมูลผู้ป่วยใน</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/create', 'typefile' => 'insurance']) ?>"><i class="fa fa-upload" aria-hidden="true"></i><span>อัปโหลดข้อมูลกรมธรรม์ (FDH)<small>นำเข้าข้อมูลกรมธรรม์</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <!-- <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/create', 'typefile' => 'fwfhealth']) ?>"><i class="fa fa-upload" aria-hidden="true"></i><span>อัปโหลดไฟล์บัตรประกันสุขภาพ</span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li> -->
                <?php endif; ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/xlsfileuploads/index']) ?>"><i class="fa fa-history" aria-hidden="true"></i><span>ประวัตินำเข้าข้อมูล<small>ตรวจสอบรายการไฟล์ที่เคยนำเข้า</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                  </ul>
                </nav>
              </section>

              <section class="cfps-menu-card" aria-labelledby="cfps-menu-eligibility">
                <header class="cfps-menu-heading">
                  <span class="cfps-menu-heading-icon"><i class="fa fa-check-circle" aria-hidden="true"></i></span>
                  <span>
                    <h4 id="cfps-menu-eligibility">ตรวจสอบสิทธิ</h4>
                    <p>เชื่อมต่อระบบตรวจสอบสิทธิของหน่วยงาน</p>
                  </span>
                </header>
                <nav aria-label="ตรวจสอบสิทธิ">
                  <ul class="cfps-menu-links">
                    <li><a href="https://fwf.moph.go.th/PublicCIDCheck" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i><span>ตรวจสอบสิทธิกองทุนประกันสุขภาพคนต่างด้าว<small>ระบบตรวจสอบสิทธิ FWF</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="https://fdh.moph.go.th/fwf/eligibility-check" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i><span>ตรวจสอบสิทธิผ่านระบบ FDH<small>ระบบกระทรวงสาธารณสุข</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                  </ul>
                </nav>
              </section>

              <section class="cfps-menu-card" aria-labelledby="cfps-menu-reports">
                <header class="cfps-menu-heading">
                  <span class="cfps-menu-heading-icon"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                  <span>
                    <h4 id="cfps-menu-reports">รายงานและตรวจสอบข้อมูล</h4>
                    <p>ดูข้อมูลบริการและรายงานการเบิกจ่าย</p>
                  </span>
                </header>
                <nav aria-label="รายงานและตรวจสอบข้อมูล">
                  <ul class="cfps-menu-links cfps-menu-links--columns">
                    <li><a href="<?= \yii\helpers\Url::to(['/site/dashboard']) ?>"><i class="fa fa-tachometer" aria-hidden="true"></i><span>Dashboard<small>ภาพรวมข้อมูลและผลการดำเนินงาน</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/fwfdatabase/index']) ?>"><i class="fa fa-database" aria-hidden="true"></i><span>ตรวจสอบข้อมูล Fwfdatabase<small>ค้นหาข้อมูลในฐาน FWF</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                <?php if($hoscode != '00064'): ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/opdatainfo/index']) ?>"><i class="fa fa-stethoscope" aria-hidden="true"></i><span>ตรวจสอบข้อมูล OP PP<small>ข้อมูลผู้ป่วยนอกและส่งเสริมสุขภาพ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/ipdatainfo/index']) ?>"><i class="fa fa-bed" aria-hidden="true"></i><span>ตรวจสอบข้อมูล IP<small>ข้อมูลผู้ป่วยใน</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/insurancepolicies/index']) ?>"><i class="fa fa-file-text-o" aria-hidden="true"></i><span>ตรวจสอบข้อมูลกรมธรรม์<small>ข้อมูลประกันสุขภาพ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/deptor/index']) ?>"><i class="fa fa-file-text-o" aria-hidden="true"></i><span>ลูกหนี้ตามจ่าย<small>ติดตามข้อมูลการเบิกจ่าย</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <!-- <li><a href="<?= \yii\helpers\Url::to(['/fwfhealth/index']) ?>"><i class="fa fa-id-card-o" aria-hidden="true"></i><span>ตรวจสอบข้อมูลบัตรสุขภาพ</span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li> -->
                    <!-- <li><a href="<?= \yii\helpers\Url::to(['/fwfhealthclient/index']) ?>"><i class="fa fa-bar-chart" aria-hidden="true"></i><span>รายงานสรุปบัตรสุขภาพ</span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li> -->
                <?php endif; ?>
                <?php if($hoscode == '00064'): ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/insurancepolicies/index']) ?>"><i class="fa fa-file-text-o" aria-hidden="true"></i><span>ตรวจสอบข้อมูลกรมธรรม์<small>ข้อมูลประกันสุขภาพ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <li><a href="<?= \yii\helpers\Url::to(['/refer-claim/index']) ?>"><i class="fa fa-exchange" aria-hidden="true"></i><span>บันทึกค่ารักษาส่งต่อ (นค.1)<small>รายงานข้อมูลการส่งต่อ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                    <!-- <li><a href="<?= \yii\helpers\Url::to(['/fwfhealthms/index']) ?>"><i class="fa fa-bar-chart" aria-hidden="true"></i><span>รายงานสรุปบัตรสุขภาพ</span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li> -->
                <?php endif; ?>
                    <li><a href="<?= \yii\helpers\Url::to(['/fwf-fy-claim/index']) ?>"><i class="fa fa-calendar-o" aria-hidden="true"></i><span>กองทุน Claim ตามปีงบประมาณ<small>ดูรายงานแยกตามปีงบประมาณ</small></span><i class="fa fa-angle-right cfps-menu-arrow" aria-hidden="true"></i></a></li>
                  </ul>
                </nav>
              </section>
            </div>
            <div class="col-md-12">              
          
            <div class="row">
                    <div class="col-md-12">
                      <p class="text-muted" style="font-size:0.82rem;margin-bottom:6px;margin-top:10px;"><i class="fa fa-calendar"></i> <strong>ค้นหาแบบช่วงเวลา</strong></p>
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
                <?php  Activeform::end(); ?>	  
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
          ['/site/index', 'date1' => ($y - 1) . '-10-01', 'date2' => $y . '-09-30', 'peengob' => $y],
          ['class' => 'btn btn-success btn-fy' . ($y == $peengob ? ' active' : '')]) ?>
    <?php endfor; ?>
  </div>

  <?php /* ── Month navigation ─────────────────────────────── */ ?>
  <div>
    <?= Html::a('<i class="fa fa-fw fa-chevron-left"></i>',
        ['/site/index', 'back' => '1', 'date1' => $date1, 'date2' => $date2, 'peengob' => $peengob],
        ['class' => 'btn btn-default nav-btn', 'title' => 'ปีก่อน']) ?>
    <?php foreach ($months as [$label, $y, $mm, $lastDay]): ?>
        <?= Html::a($label . ' ' . substr((string)$y, 2, 2),
            ['/site/index', 'date1' => "{$y}-{$mm}-01", 'date2' => "{$y}-{$mm}-{$lastDay}", 'peengob' => $peengob],
            ['class' => 'btn btn-default btn-month']) ?>
    <?php endforeach; ?>
    <?= Html::a('<i class="fa fa-fw fa-chevron-right"></i>',
        ['/site/index', 'forward' => '1', 'date1' => $date1, 'date2' => $date2, 'peengob' => $peengob],
        ['class' => 'btn btn-default nav-btn', 'title' => 'ปีถัดไป']) ?>
  </div>
</div>
            </div>


            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->       
<?php if($hoscode!='00064'){ ?>

<!--OP-->
<div class="col-md-12">
          <div class="box box-success box-solid box-op">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-stethoscope"></i> รายงานสรุปรายสถานบริการ &nbsp;<span class="badge badge-op">OP</span></h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->

          <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>
          </div>
            <div class="box-body">
          <div class="col-md-5">
                        <!--Table-->
                        <table class="table table-bordered table-hover ac-table">
                        <thead>
                            <tr>
                              <th>ประเภท</th><th>ครั้ง(ทั้งหมด)</th><th>ค่ารักษาตามจริง</th><th>ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr class="row-all">
                              <td><strong>รวมทั้งหมด</strong></td>
                              <td class="text-right"><?=$nf((float)($queryop_all[0]['sq'] ?? 0),0,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($queryop_all[0]['alltotal'] ?? 0),2,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($queryop_all[0]['claim'] ?? 0),2,".",",")?></td>
                            </tr>
                            <tr class="row-a">
                              <td><span class="label label-success">A</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_a[0]['sq'] ?? 0),0,".",","), ['/opa/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_a[0]['alltotal'] ?? 0),2,".",","), ['/opa/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_a[0]['claim'] ?? 0),2,".",","), ['/opa/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                            <tr class="row-c">
                              <td><span class="label label-danger">C</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_c[0]['sq'] ?? 0),0,".",","), ['/opc/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_c[0]['alltotal'] ?? 0),2,".",","), ['/opc/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryop_c[0]['claim'] ?? 0),2,".",","), ['/opc/index' , 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                          </tbody>
                        </table>
          </div>

          <div class="col-md-7">   
                
          <table class="table table-bordered cfps-table">
                        <thead>
                            <tr>
                              <th>กองทุน</th>
                              <th class="text-right">ครั้ง</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>รพช.(สสจ)</td>
                              <td class="td-ssj"><?= $nf($opssj_sq,0,".",","); ?></td>
                              <td class="td-ssj"><?= $nf($opssj_alltotal,2,".",","); ?></td>
                              <td class="td-ssj"><?= $nf($opssj_claim,2,".",","); ?></td>
                            </tr>
                            <tr>
                              <td>รพท.พังงา</td>
                              <td class="td-png"><?= $nf($oppng_sq,0,".",","); ?></td>
                              <td class="td-png"><?= $nf($oppng_alltotal,2,".",","); ?></td>
                              <td class="td-png"><?= $nf($oppng_claim,2,".",","); ?></td>
                            </tr>
                            <tr>
                              <td>รพท.ตะกั่วป่า</td>
                              <td class="td-tkp"><?= $nf($optkp_sq ,0,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($optkp_alltotal,2,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($optkp_claim,2,".",","); ?></td>
                            </tr>
                            <tr class="tr-total">
                              <td><strong>รวม</strong></td>
                              <td class="td-num"><strong><?= $nf($sumop_sq=$opssj_sq+$optkp_sq+$oppng_sq,0,".",",") ?></strong></td>
                              <td class="td-num"><strong><?= $nf($sumop_alltotal=$opssj_alltotal+$oppng_alltotal+$optkp_alltotal,2,".",",") ?></strong></td>
                              <td class="td-num"><strong><?= $nf($sumop_claim=$opssj_claim+$oppng_claim+$optkp_claim,2,".",",") ?></strong></td>
                            </tr>
                          </tbody>
                        </table>
          </div>
          
          <div class="col-md-12 " style="margin-top: 20px;">             
          <table class="table table-striped table-hover cfps-table">
                        <thead>
                            <tr>
                              <th>รหัส</th>
                              <th>ชื่อ</th>
                              <th class="text-right">ครั้ง</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
   
                    if(!empty($queryop)){
                    
                      // for ($key=$start_record; $key <=$end_record ; $key++) { 
                      foreach ($queryop as $key => $value) {
                    ?>                    
                                    <tr>
                                    <td><?= ($queryop[$key]['hoscode']); ?></td>
                                      <td><?= ($queryop[$key]['hosname']); ?></td>
                                      <td class="td-num"><?= $nf((float)($queryop[$key]['sq'] ?? 0),0,".",","); ?></td>
                                      <td class="td-num"><?= $nf((float)($queryop[$key]['alltotal'] ?? 0),2,".",","); ?></td>
                                      <td class="td-num"><?= $nf((float)($queryop[$key]['claim'] ?? 0),2,".",","); ?></td>
                                    </tr>
                    <?php
                        
                            }
                    }
                    ?>  
                          </tbody>
                        </table>
          </div>


          
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->  

    <!--PP-->
        <div class="col-md-12">
          <div class="box box-success box-solid box-pp">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-heartbeat"></i> รายงานสรุปรายสถานบริการ &nbsp;<span class="badge badge-pp">PP</span></h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>

          <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>
          </div>

            <div class="box-body">
          <div class="col-md-5">
                        <table class="table table-bordered table-hover ac-table">
                        <thead>
                            <tr>
                              <th>ประเภท</th><th>ครั้ง(ทั้งหมด)</th><th>ค่ารักษาตามจริง</th><th>ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr class="row-all">
                              <td><strong>รวมทั้งหมด</strong></td>
                              <td class="text-right"><?=$nf((float)($querypp_all[0]['sq'] ?? 0),0,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($querypp_all[0]['alltotal'] ?? 0),2,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($querypp_all[0]['claim'] ?? 0),2,".",",")?></td>
                            </tr>
                            <tr class="row-a">
                              <td><span class="label label-success">A</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_a[0]['sq'] ?? 0),0,".",","), ['/ppa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_a[0]['alltotal'] ?? 0),2,".",","), ['/ppa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_a[0]['claim'] ?? 0),2,".",","), ['/ppa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                            <tr class="row-c">
                              <td><span class="label label-danger">C</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_c[0]['sq'] ?? 0),0,".",","), ['/ppc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_c[0]['alltotal'] ?? 0),2,".",","), ['/ppc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($querypp_c[0]['claim'] ?? 0),2,".",","), ['/ppc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                          </tbody>
                        </table>
          </div>

          <div class="col-md-7">   
                
          <table class="table table-bordered cfps-table">
                        <thead>
                            <tr>
                              <th>กองทุน</th>
                              <th class="text-right">ครั้ง</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>รพช.(สสจ)</td>
                              <td class="td-ssj"><?= $nf($ppssj_sq,0,".",","); ?></td>
                              <td class="td-ssj"><?= $nf($ppssj_alltotal,2,".",","); ?></td>
                              <td class="td-ssj"><?= $nf($ppssj_claim,2,".",","); ?></td>
                            </tr>
                            <tr>
                              <td>รพท.พังงา</td>
                              <td class="td-png"><?= $nf($pppng_sq,0,".",","); ?></td>
                              <td class="td-png"><?= $nf($pppng_alltotal,2,".",","); ?></td>
                              <td class="td-png"><?= $nf($pppng_claim,2,".",","); ?></td>
                            </tr>
                            <tr>
                              <td>รพท.ตะกั่วป่า</td>
                              <td class="td-tkp"><?= $nf($pptkp_sq ,0,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($pptkp_alltotal,2,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($pptkp_claim,2,".",","); ?></td>
                            </tr>
                            <tr class="tr-total">
                              <td><strong>รวม</strong></td>
                              <td class="td-num"><strong><?= $nf($sumpp_sq=$ppssj_sq+$pptkp_sq+$pppng_sq,0,".",",") ?></strong></td>
                              <td class="td-num"><strong><?= $nf($sumpp_alltotal=$ppssj_alltotal+$pppng_alltotal+$pptkp_alltotal,2,".",",") ?></strong></td>
                              <td class="td-num"><strong><?= $nf($sumpp_claim=$ppssj_claim+$pppng_claim+$pptkp_claim,2,".",",") ?></strong></td>
                            </tr>
                          </tbody>
                        </table>
          </div>
          
          <div class="col-md-12" style="margin-top: 20px;">             
          <table class="table table-striped table-hover cfps-table">
                        <thead>
                            <tr>
                              <th>รหัส</th>
                              <th>ชื่อ</th>
                              <th class="text-right">ครั้ง</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
   
   if(!empty($querypp)){
   
     // for ($key=$start_record; $key <=$end_record ; $key++) { 
     foreach ($querypp as $key => $value) {
   ?>                    
                   <tr>
                   <td><?= ($querypp[$key]['hoscode']); ?></td>
                     <td><?= ($querypp[$key]['hosname']); ?></td>
                     <td class="td-num"><?= $nf((float)($querypp[$key]['sq'] ?? 0),0,".",","); ?></td>
                     <td class="td-num"><?= $nf((float)($querypp[$key]['alltotal'] ?? 0),2,".",","); ?></td>
                     <td class="td-num"><?= $nf((float)($querypp[$key]['claim'] ?? 0),2,".",","); ?></td>
                   </tr>
   <?php
       
           }
   }
   ?>  
                          </tbody>
                        </table>
          </div>


          
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->  

        <div class="col-md-12">
          <div class="box box-success box-solid box-ip">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-bed"></i> รายงานสรุปรายสถานบริการ &nbsp;<span class="badge badge-ip">IP</span></h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>

            <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>

          <div class="col-md-6">

                        <table class="table table-bordered table-hover ac-table">
                        <thead>
                            <tr>
                              <th>ประเภท</th>
                              <th class="text-right">ครั้ง</th>
                              <th class="text-right">วันนอน</th>
                              <th class="text-right">ค่ารักษา</th>
                              <th class="text-right">Adj.RW</th>
                              <th class="text-right">ชดเชย</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr class="row-all">
                              <td><strong>รวมทั้งหมด</strong></td>
                              <td class="text-right"><?=$nf((float)($queryipall[0]['an'] ?? 0),0,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($queryipall[0]['los'] ?? 0),0,".",",")?></td>
                              <td class="text-right"><?=$nf((float)($queryipall[0]['cost'] ?? 0),2,".",",")?></td>
                              <td class="text-right"><?=$queryipall[0]['adjrw']?></td>
                              <td class="text-right"><?=$nf((float)($queryipall[0]['costadjrw'] ?? 0),2,".",",")?></td>
                            </tr>
                            <tr class="row-a">
                              <td><span class="label label-success">A</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_a[0]['an'] ?? 0),0,".",","), ['/ipa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_a[0]['los'] ?? 0),0,".",","), ['/ipa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_a[0]['cost'] ?? 0),2,".",","), ['/ipa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($queryip_a[0]['adjrw'], ['/ipa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_a[0]['costadjrw'] ?? 0),2,".",","), ['/ipa/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                            <tr class="row-c">
                              <td><span class="label label-danger">C</span></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_c[0]['an'] ?? 0),0,".",","), ['/ipc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_c[0]['los'] ?? 0),0,".",","), ['/ipc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_c[0]['cost'] ?? 0),2,".",","), ['/ipc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($queryip_c[0]['adjrw'], ['/ipc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                              <td class="text-right"><?=Html::a($nf((float)($queryip_c[0]['costadjrw'] ?? 0),0,".",","), ['/ipc/index', 'date1'=>$date1, 'date2'=>$date2])?></td>
                            </tr>
                          </tbody>
                        </table>
          </div>

          <div class="col-md-6">   
                    
          <table class="table table-bordered cfps-table">
                        <thead>
                            <tr>
                              <th>กองทุน</th>
                              <th class="text-right">(A)ครั้ง</th>
                              <th class="text-right">วันนอน</th>
                              <th class="text-right">ค่ารักษา</th>
                              <th class="text-right">Adj.RW</th>
                              <th class="text-right">ชดเชย</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>รพช.(สสจ)</td>
                              <td class="td-ssj"><?= $nf($ssj_an,0,".",",") ?></td>
                              <td class="td-ssj"><?= $nf($ssj_los,0,".",",") ?></td>
                              <td class="td-ssj"><?= $nf($ssj_cost,2,".",",") ?></td>
                              <td class="td-ssj"><?= $ssj_adjrw ?></td>
                              <td class="td-ssj"><?= $nf($ssj_costadjrw,2,".",",") ?></td>
                            </tr>
                            <tr>
                              <td>รพท.พังงา</td>
                              <td class="td-png"><?= $nf($png_an,0,".",","); ?></td>
                              <td class="td-png"><?= $nf($png_los,0,".",","); ?></td>
                              <td class="td-png"><?= $nf($png_cost,2,".",","); ?></td>
                              <td class="td-png"><?= $png_adjrw ?></td>
                              <td class="td-png"><?= $nf($png_costadjrw,2,".",","); ?></td>
                            </tr>
                            <tr>
                              <td>รพท.ตะกั่วป่า</td>
                              <td class="td-tkp"><?= $nf($tpk_an,0,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($tpk_los,0,".",","); ?></td>
                              <td class="td-tkp"><?= $nf($tpk_cost,2,".",","); ?></td>
                              <td class="td-tkp"><?= $tpk_adjrw ?></td>
                              <td class="td-tkp"><?= $nf($tpk_costadjrw,2,".",","); ?></td>
                            </tr>
                            <tr class="tr-total">
                              <td><strong>รวม</strong></td>
                              <td class="td-num"><strong><?= $nf($sum_an=$ssj_an+$png_an+$tpk_an,0,".",",");?></strong></td>
                              <td class="td-num"><strong><?= $nf($sum_los=$ssj_los+$png_los+$tpk_los,0,".",",");?></strong></td>
                              <td class="td-num"><strong><?= $nf($sum_cost=$ssj_cost+$png_cost+$tpk_cost,2,".",",");?></strong></td>
                              <td class="td-num"><strong><?= $sum_adjrw=$ssj_adjrw+$png_adjrw+$tpk_adjrw;?></strong></td>
                              <td class="td-num"><strong><?= $nf($sum_costadjrw=$ssj_costadjrw+$png_costadjrw+$tpk_costadjrw,2,".",",");?></strong></td>
                            </tr>
                          </tbody>
                        </table>
              </div>  

          <div class="col-md-12" style="margin-top: 20px;">             
          <table class="table table-striped table-hover cfps-table">
                        <thead>
                            <tr>
                              <th>รหัส</th>
                              <th>ชื่อ</th>
                              <th class="text-right">(A)ครั้ง</th>
                              <th class="text-right">วันนอน</th>
                              <th class="text-right">Adj.RW</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php 
   
                          if(!empty($queryip)){
                          
                            foreach ($queryip as $key => $value) {
                          ?>                    
                                          <tr>
                                          <td><?= ($queryip[$key]['hoscode']); ?></td>
                                            <td><?= ($queryip[$key]['hosname']); ?></td>
                                            <td class="td-num"><?= $nf((float)($queryip[$key]['an'] ?? 0),0,".",","); ?></td>
                                            <td class="td-num"><?= $nf((float)($queryip[$key]['los'] ?? 0),0,".",","); ?></td>
                                            <td class="td-num"><?= ($queryip[$key]['adjrw']); ?></td>
                                            <td class="td-num"><?= $nf((float)($queryip[$key]['cost'] ?? 0),2,".",","); ?></td>
                                            <td class="td-num"><?= $nf((float)($queryip[$key]['costadjrw'] ?? 0),2,".",","); ?></td>
                                          </tr>
                          <?php
                                  }
                          }
                          ?>   

                          </tbody>
                        </table>
          </div>


        
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->                     

       <!-- IP -->           

<!-- IP -->   

<!-- rpst -->
            <?php
            if($hoscode=='10739' || $hoscode=='10740'){
            ?>
              
    <!--RPST-->
    <div class="col-md-12">
          <div class="box box-success box-solid box-rpst">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-hospital-o"></i> รายงานสรุปราย รพ.สต.</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>

          <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>
          </div>

            <div class="box-body">
          
          <div class="col-md-12">             
          <table class="table table-striped table-hover cfps-table">
                        <thead>
                            <tr>
                              <th>รหัส</th>
                              <th>ชื่อ</th>
                              <th class="text-right">(A)ครั้ง</th>
                              <th class="text-right">ค่ารักษาตามจริง</th>
                              <th class="text-right">ค่ารักษา(ชดเชย)</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
   if(!empty($querysqlrpst_claim)){
     foreach ($querysqlrpst_claim as $key => $value) {
       $rpst[$key]=$querysqlrpst_claim[$key]['hospcode'];
   ?>                    
                   <tr>
                   <td><?=Html::a($querysqlrpst_claim[$key]['hospcode'], ['/rpstlist/index', 'date1'=>$date1, 'date2'=>$date2 , 'rpst'=>$rpst[$key]])?></td>
                   <td><?=Html::a($querysqlrpst_claim[$key]['hosname'], ['/rpstlist/index', 'date1'=>$date1, 'date2'=>$date2 , 'rpst'=>$rpst[$key]])?></td>
                   <td class="td-num"><?=Html::a($nf((float)($querysqlrpst_claim[$key]['seq'] ?? 0),0,".",","), ['/rpstlist/index', 'date1'=>$date1, 'date2'=>$date2 , 'rpst'=>$rpst[$key]])?></td>
                   <td class="td-num"><?=Html::a($nf((float)($querysqlrpst_claim[$key]['alltotal'] ?? 0),2,".",","), ['/rpstlist/index', 'date1'=>$date1, 'date2'=>$date2 , 'rpst'=>$rpst[$key]])?></td>
                   <td class="td-num"><?=Html::a($nf((float)($querysqlrpst_claim[$key]['claim'] ?? 0),2,".",","), ['/rpstlist/index', 'date1'=>$date1, 'date2'=>$date2 , 'rpst'=>$rpst[$key]])?></td>
                   </tr>
   <?php
           }
   }
   ?>  
                          </tbody>
                        </table>
          </div>


          
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->  

            <?php } ?>
                        <?php } ?>

              <!-- SSJ -->  
              <?php if( $hoscode=='00064' ){ ?>

                <div class="col-md-12">
          <div class="box box-success box-solid box-ssj">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-table"></i> รายงานสรุปราย รพ.</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->

          <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>
          </div>

            <div class="box-body">

            <div class="col-md-12">      
              <div class="table-responsive">        
            <table class="table table-bordered ssj-table">
                          <thead>
                            <tr>
                              <th colspan="2" rowspan="2" style="background:#37474f;color:#fff;"><strong>รพ.</strong></th>
                              <th colspan="3" class="th-ssj" style="color:#fff;">สสจ.พังงา</th>
                              <th colspan="3" class="th-png" style="color:#fff;">รพท.พังงา</th>
                              <th colspan="3" class="th-tkp" style="color:#fff;">รพท.ตะกั่วป่า</th>
                              <th colspan="3" class="th-total" style="color:#fff;">Total</th>
                              <th rowspan="2" class="th-recv" style="color:#fff;">รวมรับ</th>
                            </tr>
                            <tr>
                              <td class="th-ssj">OP</td>
                              <td class="th-ssj">PP</td>
                              <td class="th-ssj">IP</td>
                              <td class="th-png">OP</td>
                              <td class="th-png">PP</td>
                              <td class="th-png">IP</td>
                              <td class="th-tkp">OP</td>
                              <td class="th-tkp">PP</td>
                              <td class="th-tkp">IP</td>
                              <td class="th-ssj">สสจ.พังงา</td>
                              <td class="th-png">รพท.พังงา</td>
                              <td class="th-tkp">รพท.ตะกั่วป่า</td>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td><h5 align = 'center'>10739</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพท.พังงา</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_10739_ssj  =$queryssjop_10739[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10739_ssj  =$queryssjpp_10739[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_10739_ssj  =$queryssjip_10739[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_10739_10739=$queryssjop_10739[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10739_10739=$queryssjpp_10739[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_10739_10739=$queryssjip_10739[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_10739_10740=$queryssjop_10739[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10739_10740=$queryssjpp_10739[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_10739_10740=$queryssjip_10739[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_10739=$op_10739_ssj+$pp_10739_ssj+$ip_10739_ssj+$pp_10739_10739+$pp_10739_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_10739=$op_10739_10739+$ip_10739_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_10739=$op_10739_10740+$ip_10739_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum10739=$ssj_10739+$png_10739+$tkp_10739,2,".",",");?></h5></font></td>
                            </tr>  
                            <tr>
                              <td><h5 align = 'center'>10740</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพท.ตะกั่วป่า</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_10740_ssj  =$queryssjop_10740[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10740_ssj  =$queryssjpp_10740[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_10740_ssj  =$queryssjip_10740[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_10740_10739=$queryssjop_10740[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10740_10739=$queryssjpp_10740[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_10740_10739=$queryssjip_10740[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_10740_10740=$queryssjop_10740[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_10740_10740=$queryssjpp_10740[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_10740_10740=$queryssjip_10740[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_10740=$op_10740_ssj+$pp_10740_ssj+$ip_10740_ssj+$pp_10740_10739+$pp_10740_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_10740=$op_10740_10739+$ip_10740_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_10740=$op_10740_10740+$ip_10740_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum10740=$ssj_10740+$png_10740+$tkp_10740,2,".",",");?></h5></font></td>
                            </tr>   
                            <tr>
                              <td><h5 align = 'center'>11347</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.เกาะยาวชัยพัฒน์</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11347_ssj  =$queryssjop_11347[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11347_ssj  =$queryssjpp_11347[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11347_ssj  =$queryssjip_11347[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11347_10739=$queryssjop_11347[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11347_10739=$queryssjpp_11347[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11347_10739=$queryssjip_11347[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11347_10740=$queryssjop_11347[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11347_10740=$queryssjpp_11347[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11347_10740=$queryssjip_11347[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11347=$op_11347_ssj+$pp_11347_ssj+$ip_11347_ssj+$pp_11347_10739+$pp_11347_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11347=$op_11347_10739+$ip_11347_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11347=$op_11347_10740+$ip_11347_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11347=$ssj_11347+$png_11347+$tkp_11347,2,".",",");?></h5></font></td>
                            </tr>    
                            <tr>
                              <td><h5 align = 'center'>11348</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.กะปงชัยพัฒน์</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11348_ssj  =$queryssjop_11348[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11348_ssj  =$queryssjpp_11348[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11348_ssj  =$queryssjip_11348[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11348_10739=$queryssjop_11348[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11348_10739=$queryssjpp_11348[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11348_10739=$queryssjip_11348[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11348_10740=$queryssjop_11348[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11348_10740=$queryssjpp_11348[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11348_10740=$queryssjip_11348[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11348=$op_11348_ssj+$pp_11348_ssj+$ip_11348_ssj+$pp_11348_10739+$pp_11348_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11348=$op_11348_10739+$ip_11348_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11348=$op_11348_10740+$ip_11348_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11348=$ssj_11348+$png_11348+$tkp_11348,2,".",",");?></h5></font></td>
                            </tr>   
                            <tr>
                              <td><h5 align = 'center'>11349</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.ตะกั่วทุ่ง</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11349_ssj  =$queryssjop_11349[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11349_ssj  =$queryssjpp_11349[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11349_ssj  =$queryssjip_11349[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11349_10739=$queryssjop_11349[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11349_10739=$queryssjpp_11349[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11349_10739=$queryssjip_11349[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11349_10740=$queryssjop_11349[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11349_10740=$queryssjpp_11349[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11349_10740=$queryssjip_11349[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11349=$op_11349_ssj+$pp_11349_ssj+$ip_11349_ssj+$pp_11349_10739+$pp_11349_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11349=$op_11349_10739+$ip_11349_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11349=$op_11349_10740+$ip_11349_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11349=$ssj_11349+$png_11349+$tkp_11349,2,".",",");?></h5></font></td>
                            </tr>      
                            <tr>
                              <td><h5 align = 'center'>11350</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>โรงพยาบาลบางไทร</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11350_ssj  =$queryssjop_11350[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11350_ssj  =$queryssjpp_11350[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11350_ssj  =$queryssjip_11350[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11350_10739=$queryssjop_11350[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11350_10739=$queryssjpp_11350[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11350_10739=$queryssjip_11350[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11350_10740=$queryssjop_11350[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11350_10740=$queryssjpp_11350[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11350_10740=$queryssjip_11350[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11350=$op_11350_ssj+$pp_11350_ssj+$ip_11350_ssj+$pp_11350_10739+$pp_11350_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11350=$op_11350_10739+$ip_11350_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11350=$op_11350_10740+$ip_11350_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11350=$ssj_11350+$png_11350+$tkp_11350,2,".",",");?></h5></font></td>
                            </tr>                   
                            <tr>
                              <td><h5 align = 'center'>11352</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.คุระบุรีชัยพัฒน์</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11352_ssj  =$queryssjop_11352[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11352_ssj  =$queryssjpp_11352[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11352_ssj  =$queryssjip_11352[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11352_10739=$queryssjop_11352[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11352_10739=$queryssjpp_11352[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11352_10739=$queryssjip_11352[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11352_10740=$queryssjop_11352[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11352_10740=$queryssjpp_11352[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11352_10740=$queryssjip_11352[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11352=$op_11352_ssj+$pp_11352_ssj+$ip_11352_ssj+$pp_11352_10739+$pp_11352_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11352=$op_11352_10739+$ip_11352_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11352=$op_11352_10740+$ip_11352_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11352=$ssj_11352+$png_11352+$tkp_11352,2,".",",");?></h5></font></td>
                            </tr>                          
                            <tr>
                              <td><h5 align = 'center'>11353</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.ทับปุด</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11353_ssj  =$queryssjop_11353[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11353_ssj  =$queryssjpp_11353[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11353_ssj  =$queryssjip_11353[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11353_10739=$queryssjop_11353[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11353_10739=$queryssjpp_11353[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11353_10739=$queryssjip_11353[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11353_10740=$queryssjop_11353[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11353_10740=$queryssjpp_11353[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11353_10740=$queryssjip_11353[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11353=$op_11353_ssj+$pp_11353_ssj+$ip_11353_ssj+$pp_11353_10739+$pp_11353_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11353=$op_11353_10739+$ip_11353_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11353=$op_11353_10740+$ip_11353_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11353=$ssj_11353+$png_11353+$tkp_11353,2,".",",");?></h5></font></td>
                            </tr>
                            <tr>
                              <td><h5 align = 'center'>11354</h5></td>
                              <td class="text-nowrap"><h5 align = 'left'>รพ.ท้ายเหมืองชัยพัฒน์</h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($op_11354_ssj  =$queryssjop_11354[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11354_ssj  =$queryssjpp_11354[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ip_11354_ssj  =$queryssjip_11354[0]['SSJ'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op_11354_10739=$queryssjop_11354[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11354_10739=$queryssjpp_11354[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($ip_11354_10739=$queryssjip_11354[0]['10739'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op_11354_10740=$queryssjop_11354[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp_11354_10740=$queryssjpp_11354[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($ip_11354_10740=$queryssjip_11354[0]['10740'],2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ssj_11354=$op_11354_ssj+$pp_11354_ssj+$ip_11354_ssj+$pp_11354_10739+$pp_11354_10740,2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($png_11354=$op_11354_10739+$ip_11354_10739,2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($tkp_11354=$op_11354_10740+$ip_11354_10740,2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sum11354=$ssj_11354+$png_11354+$tkp_11354,2,".",",");?></h5></font></td>
                            </tr>

                            <tr class="tr-total">
                              <td colspan="2"><strong>รวม</strong></td>
                             
                              <td class="td-ssj"><?=$nf($op_10739_ssj+$op_10740_ssj+$op_11347_ssj+$op_11348_ssj+$op_11349_ssj+$op_11350_ssj+$op_11352_ssj+$op_11353_ssj+$op_11354_ssj,2,".",",");?></td>
                              <td class="td-ssj"><?=$nf($pp_10739_ssj+$pp_10740_ssj+$pp_11347_ssj+$pp_11348_ssj+$pp_11349_ssj+$pp_11350_ssj+$pp_11352_ssj+$pp_11353_ssj+$pp_11354_ssj,2,".",",");?></td>
                              <td class="td-ssj"><?=$nf($ip_10739_ssj+$ip_10740_ssj+$ip_11347_ssj+$ip_11348_ssj+$ip_11349_ssj+$ip_11350_ssj+$ip_11352_ssj+$ip_11353_ssj+$ip_11354_ssj,2,".",",");?></td>

                              <td class="td-png"><?=$nf($op_10739_10739+$op_10740_10739+$op_11347_10739+$op_11348_10739+$op_11349_10739+$op_11350_10739+$op_11352_10739+$op_11353_10739+$op_11354_10739,2,".",",");?></td>
                              <td class="td-png"><?=$nf($pp_10739_10739+$pp_10740_10739+$pp_11347_10739+$pp_11348_10739+$pp_11349_10739+$pp_11350_10739+$pp_11352_10739+$pp_11353_10739+$pp_11354_10739,2,".",",");?></td>
                              <td class="td-png"><?=$nf($ip_10739_10739+$ip_10740_10739+$ip_11347_10739+$ip_11348_10739+$ip_11349_10739+$ip_11350_10739+$ip_11352_10739+$ip_11353_10739+$ip_11354_10739,2,".",",");?></td>

                              <td class="td-tkp"><?=$nf($op_10739_10740+$op_10740_10740+$op_11347_10740+$op_11348_10740+$op_11349_10740+$op_11350_10740+$op_11352_10740+$op_11353_10740+$op_11354_10740,2,".",",");?></td>
                              <td class="td-tkp"><?=$nf($pp_10739_10740+$pp_10740_10740+$pp_11347_10740+$pp_11348_10740+$pp_11349_10740+$pp_11350_10740+$pp_11352_10740+$pp_11353_10740+$pp_11354_10740,2,".",",");?></td>
                              <td class="td-tkp"><?=$nf($ip_10739_10740+$ip_10740_10740+$ip_11347_10740+$ip_11348_10740+$ip_11349_10740+$ip_11350_10740+$ip_11352_10740+$ip_11353_10740+$ip_11354_10740,2,".",",");?></td>

                              <td class="td-ssj"><?=$nf($ssj_10739+$ssj_10740+$ssj_11347+$ssj_11348+$ssj_11349+$ssj_11350+$ssj_11352+$ssj_11353+$ssj_11354,2,".",",");?></td>
                              <td class="td-png"><?=$nf($png_10739+$png_10740+$png_11347+$png_11348+$png_11349+$png_11350+$png_11352+$png_11353+$png_11354,2,".",",");?></td>
                              <td class="td-tkp"><?=$nf($tkp_10739+$tkp_10740+$tkp_11347+$tkp_11348+$tkp_11349+$tkp_11350+$tkp_11352+$tkp_11353+$tkp_11354,2,".",",");?></td>
                              <td class="td-recv"><?=$nf($sum10739+$sum10740+$sum11347+$sum11348+$sum11349+$sum11350+$sum11352+$sum11353+$sum11354,2,".",",");?></td>
                            </tr>
                          </tbody>
                          </table>
                          </div>
            </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->  
<!-- end รพ --> 

<div class="col-md-12">
          <div class="box box-success box-solid box-ssj">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fw fa-sitemap"></i> รายงานสรุปราย รพสต.</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
            </div>

          <div class="box-body">
          <div class="col-md-12">
            <div class="cfps-info-bar"><i class="fa fa-user"></i> <?= $session['hoscode'] ?> | <?= $session['orgname'] ?> &nbsp;&nbsp;<i class="fa fa-calendar-o"></i> ข้อมูลตั้งแต่ <strong><?= changeDate($sdate1) ?></strong> ถึง <strong><?= changeDate($edate2) ?></strong></div>
          </div>
          </div>

            <div class="box-body">

            <div class="col-md-12">      
            <?php
            /*
            print_r($queryssjsubhos);
            echo '</br>';
            echo $sqlssjsubhos;
            
            print_r($queryssjpp_11354);
            echo '</br>';
            print_r($queryssjip_11354);
            echo '</br>';
            echo $queryssjop_11354[0]['SSJ'];
            */
            ?>
              <div class="table-responsive">        
            <table class="table table-bordered ssj-table">
                          <thead>
                            <tr>
                              <th colspan="2" rowspan="2" style="background:#37474f;color:#fff;"><strong>CUP รพ.</strong></th>
                              <th colspan="2" class="th-ssj" style="color:#fff;">สสจ.พังงา</th>
                              <th colspan="2" class="th-png" style="color:#fff;">รพท.พังงา</th>
                              <th colspan="2" class="th-tkp" style="color:#fff;">รพท.ตะกั่วป่า</th>
                              <th colspan="3" class="th-total" style="color:#fff;">Total</th>
                              <th rowspan="2" class="th-recv" style="color:#fff;">รวมรับ</th>
                            </tr>
                            <tr>
                              <td class="th-ssj">OP</td>
                              <td class="th-ssj">PP</td>
                              <td class="th-png">OP</td>
                              <td class="th-png">PP</td>
                              <td class="th-tkp">OP</td>
                              <td class="th-tkp">PP</td>
                              <td class="th-ssj">สสจ.พังงา</td>
                              <td class="th-png">รพท.พังงา</td>
                              <td class="th-tkp">รพท.ตะกั่วป่า</td>
                            </tr>
                          </thead>
                          <tbody>
                          <?php 
                              $sumopssj=0;
                              $sumppssj=0;
                              $sumop10739=0;
                              $sumpp10739=0;
                              $sumop10740=0;
                              $sumpp10740=0;

                              $sumsubhos_ssj=0;
                              $sumsubhos_10739=0;
                              $sumsubhos_10740=0;
                              $sumsubhos=0;
                          if(!empty($queryhosmain)){                          
                            // for ($key=$start_record; $key <=$end_record ; $key++) { 

                            foreach ($queryhosmain as $mkey => $mvalue) {

                            ?>        
                            <tr>
                              <td colspan="15"><h3><?= $queryhosmain[$mkey]['hosname']; ?><h3></td>
                            </tr>
                            <?php 
   
                            if(!empty($queryssjsubhos)){                           
                              // for ($key=$start_record; $key <=$end_record ; $key++) { 
                                
                                foreach ($queryssjsubhos as $skey => $svalue) {
                                  if($queryhosmain[$mkey]['hospcode']==$queryssjsubhos[$skey]['cupcode']  ){
                            ?>  
                            <tr>
                            <td><h5 align = 'center'><?= ($queryssjsubhos[$skey]['hospcode']); ?></h5></td>
                              <td><h5 align = 'left'><?= ($queryssjsubhos[$skey]['hosname']); ?></h5></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($opssj[$skey]=(float)($queryssjsubhos[$skey]['opSSJ'] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($ppssj[$skey]=(float)($queryssjsubhos[$skey]['ppSSJ'] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($op10739[$skey]=(float)($queryssjsubhos[$skey]['op10739'] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp10739[$skey]=(float)($queryssjsubhos[$skey]['pp10739'] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($op10740[$skey]=(float)($queryssjsubhos[$skey]['op10740'] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($pp10740[$skey]=(float)($queryssjsubhos[$skey]['pp10740'] ?? 0),2,".",",");?></h5></font></td>

                              <td><font color="#228B22"><h5 align = 'right'><?=$nf($sumpp_ssj[$skey]=(float)($opssj[$skey]+$ppssj[$skey]+$pp10739[$skey]+$pp10740[$skey] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#3300FF"><h5 align = 'right'><?=$nf($sumpng_10739[$skey]=(float)($op10739[$skey] ?? 0),2,".",",");?></h5></font></td>
                              <td><font color="#8B4513"><h5 align = 'right'><?=$nf($sumtkp_10740[$skey]=(float)($op10740[$skey] ?? 0),2,".",",");?></h5></font></td>
                              <td bgcolor="#EEDD82"><font color="#FF1493"><h5 align = 'right'><?=$nf($sumsub[$skey]=(float)($sumpp_ssj[$skey]+$sumpng_10739[$skey]+$sumtkp_10740[$skey] ?? 0),2,".",",");?></h5></font></td>
                            </tr>
                            <?php 
                              $sumopssj=$sumopssj+$opssj[$skey];
                              $sumppssj=$sumppssj+$ppssj[$skey];
                              $sumop10739=$sumop10739+$op10739[$skey];
                              $sumpp10739=$sumpp10739+$pp10739[$skey];
                              $sumop10740=$sumop10740+$op10740[$skey];
                              $sumpp10740=$sumpp10740+$pp10740[$skey];

                              $sumsubhos_ssj=$sumsubhos_ssj+$sumpp_ssj[$skey];
                              $sumsubhos_10739=$sumsubhos_10739+$sumpng_10739[$skey];
                              $sumsubhos_10740=$sumsubhos_10740+$sumtkp_10740[$skey];
                              $sumsubhos=$sumsubhos+$sumsub[$skey];

                             } // if check map


                                  }  //foreach s
                                 
                              }//if s

                                  }  //foreach m
                                    
                              }//if m
                           ?>

                            <tr class="tr-total">
                              <td colspan="2"><strong>รวม</strong></td>
                             
                              <td class="td-ssj"><?=$nf($sumopssj,2,".",",");  ?></td>
                              <td class="td-ssj"><?=$nf($sumppssj,2,".",","); ?></td>
                              <td class="td-png"><?=$nf($sumop10739,2,".",","); ?></td>
                              <td class="td-png"><?=$nf($sumpp10739,2,".",","); ?></td>
                              <td class="td-tkp"><?=$nf($sumop10740,2,".",","); ?></td>
                              <td class="td-tkp"><?=$nf($sumpp10740,2,".",","); ?></td>
                              <td class="td-ssj"><?=$nf($sumsubhos_ssj,2,".",","); ?></td>
                              <td class="td-png"><?=$nf($sumsubhos_10739,2,".",","); ?></td>
                              <td class="td-tkp"><?=$nf($sumsubhos_10740,2,".",","); ?></td>
                              <td class="td-recv"><?=$nf($sumsubhos,2,".",","); ?></td>
                            </tr>
                          </tbody>
                          </table>
                          </div>
            </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->  
              <?php } ?>

</div>