<?php
use yii\helpers\Html;
use miloschuman\highcharts\Highcharts;
use yii\web\JsExpression;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

/* @var $this yii\web\View */

//$this->title = 'HospitalOS Report';

$data = Yii::$app->request->get();
$datenow=date('Y-m-d');
$ynow=543+$y1=substr($datenow, 0, 4);
$g_year = isset($data['g_year']) ? $data['g_year'] : $ynow;

$g_y0='0'.substr($g_year,2,4);
$g_y1='1'.substr($g_year,2,4);
$nn_vm0=[];
$numi0=[];
$nn_vm1=[];
$numi1=[];
for ($i = 0; $i < count($cross_vn); $i++) {
    $vn_num[] = $cross_vn[$i]['vn'];
    $numi0[]=$i+1;
    $numi1[]=$i+1;
    if(substr($vn_num[$i],0,3)==$g_y0){
        $n_vm0=substr($vn_num[$i],3,9);
        $nn_vm0[]=(int)$n_vm0;//vn ที่ใช้ไปแล้ว
       // echo $nn_vm0;
       // echo '</br>';
    }
    if(substr($vn_num[$i],0,3)==$g_y1){
        $n_vm1=substr($vn_num[$i],3,9);
        $nn_vm1[]=(int)$n_vm1;//an ที่ใช้ไปแล้ว
       // echo $nn_vm0;
       // echo '</br>';
    }    
    
}

$mx0=max($nn_vm0);
$mx1=max($nn_vm1);

for ($i = 0; $i < $mx0; $i++) {
    $numi00[]=$i+1;
}
for ($i = 0; $i < $mx1; $i++) {
    $numi11[]=$i+1;
}

$cross_vn1 = array_diff($numi00, $nn_vm0);//vn ว่าง OPD
$cross_an = array_diff($numi11, $nn_vm1);//an ว่าง IPD
//print_r($cross_vn1);
//print_r($cross_an);
$h=6;//จำนวนหลัก
?>

  <div class="site-index">

    <div class="jumbotron">
      <h1>HospitalOS Report!</h1>
      <?php
              if(number_format($visit_master,0,".",",")==number_format($visit_slave,0,".",",")){
        ?>
        <div class="count green">
          <h3>Server Replication Database is Work!</h3>
        </div>
        <?php 
        }else{
        ?>
        <div class="count red">
          <h3>Server Replication Database is failed!</h3>
        </div>
        <?php
        }
        echo Html::a('Check Replicat Database', ['checkreplicate/index']);
        ?>
          <!-- top tiles -->
          <!-- CODE ตัวอย่าง    
		  <div class="row tile_count">
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Users</span>
              <div class="count">2500</div>
              <span class="count_bottom"><i class="green">4% </i> From last Week</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-clock-o"></i> Average Time</span>
              <div class="count">123.50</div>
              <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>3% </i> From last Week</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Males</span>
              <div class="count green">2,500</div>
              <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Females</span>
              <div class="count">4,567</div>
              <span class="count_bottom"><i class="red"><i class="fa fa-sort-desc"></i>12% </i> From last Week</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Collections</span>
              <div class="count">2,315</div>
              <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week</span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Connections</span>
              <div class="count">7,325</div>
              <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week</span>
            </div>
          </div>
          -->
          <!-- /top tiles -->
          <div class="row tile_count">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> Total Master Visit</span>
              <?php
              if(number_format($visit_master,0,".",",")==number_format($visit_slave,0,".",",")){
              ?>
                <div class="count green">
                  <?php echo number_format($visit_master,0,".",",") ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                    <?php echo number_format($visit_master,0,".",",") ?>
                  </div>
                  <?php 
              }
              ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> Total Slave Visit</span>
              <?php
              if(number_format($visit_master,0,".",",")==number_format($visit_slave,0,".",",")){
              ?>
                <div class="count green">
                  <?php echo number_format($visit_slave,0,".",",") ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                    <?php echo number_format($visit_slave,0,".",",") ?>
                  </div>
                  <?php 
              }
              ?>
            </div>

          </div>

          <!-- /top tiles -->
          <div class="row tile_count">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-user"></i> HN เป็นค่าว่าง</span>
              <?php
              if(number_format($nullvisit_hn,0,".",",")==0){
              ?>
                <div class="count green">
                  <?php echo number_format($nullvisit_hn,0,".",",") ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                    <?php echo number_format($nullvisit_hn,0,".",",") ?>
                  </div>
                  <?php 
              }
              ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-user"></i> VN เป็นค่าว่าง</span>
              <?php
              if(number_format($nullvisit_vn,0,".",",")==0){
              ?>
                <div class="count green">
                  <?php echo number_format($nullvisit_vn,0,".",",") ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                    <?php echo number_format($nullvisit_vn,0,".",",") ?>
                  </div>
                  <?php 
              }
              ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> AN หรือ VN ซ้ำ</span>
              <?php
              if(number_format($double_vn,0,".",",")==0){
              ?>
                <div class="count green">
                  <?php echo number_format($double_vn,0,".",",");  ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                  <?php echo number_format($double_vn,0,".",",").' '.Html::a("<i class='fa fa-exclamation-circle' aria-hidden='true'></i>", ['/doublevnan/index']);  ?>
                  </div>
                  <?php 
              }
              ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> ผู้ป่วยที่ต้องรวม HN</span>
              <?php
              if(number_format($doublept,0,".",",")==0){
              ?>
                <div class="count green">
                  <?php echo number_format($doublept,0,".",",");  ?>
                </div>
                <?php
              }else{
              ?>
                  <div class="count red">
                  <?php echo number_format($doublept,0,".",",").' '.Html::a("<i class='fa fa-exclamation-circle' aria-hidden='true'></i>", ['/doublepatient/index']);  ?>
                  </div>
                  <?php 
              }
              ?>
            </div>

      </div>

          <!-- /top tiles -->
          <div class="row tile_count">
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> VN ว่าง</span>  
                <div class="count green">                  
                  <?php echo count($cross_vn1).' '.Html::a("<i class='fa fa-eye' aria-hidden='true'></i>", ['/fixerror/index']);  ?>
                </div>
            </div> 
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i> AN ว่าง</span>  
                <div class="count green">
                  <?php echo count($cross_an).' '.Html::a("<i class='fa fa-eye' aria-hidden='true'></i>", ['/fixerror/index']);  ?>
                </div>
            </div>        
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i>MAX VN </span>  
                <div class="count green">
                  <?php echo Html::a($g_y0.sprintf("%0".$h."d",$mx0), ['/fixerror/index']);  ?>
                </div>
            </div> 
            <div class="col-md-3 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top">
                <i class="fa fa-clock-o"></i>MAX AN </span>  
                <div class="count green">
                  <?php echo Html::a($g_y1.sprintf("%0".$h."d",$mx1), ['/fixerror/index']);  ?>
                </div>
            </div>                                     
          </div>
