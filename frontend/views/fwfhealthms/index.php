<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
//use kartik\date\DatePicker;
use karatae99\datepicker\DatePicker;
use yii\db\ActiveQuery;
//check สิทธการเข้าใช้
use frontend\models\User;
$session = Yii::$app->session;
$checklogin = User::findOne(['user'=>$session['user'],]);
if(!$checklogin){
    echo "<script>window.location='index.php?r=site/login';</script>";
}
//check สิทธการเข้าใช้

/* @var $this yii\web\View */
?>
<h1>ข้อมูล บัตรประกันสุขภาพ รายหน่วยบริการ</h1>
<div class='well'>

<?php
//ดึงข้อมูลเป็นเดือนตามปีงบ
$datenow=date('Y-m-d');
$yt=substr($datenow, 0, 4);
$m1=substr($datenow, 5, 2);
$dnow=date('Y-m-d');
$yn=substr($dnow, 0, 4);
$ynow=$yn+543;

$peengob = isset($data['peengob']) ? $data['peengob'] : $yt+543;

// $peengob=$yt+543;

echo '<h3>ปีงบ '.$peengob.'&nbsp;</h3>';
// echo Html::a('ปีงบ : '.$ynow, ['/fwfhealthms/index', 'date1' => ($ynow-1).'-10-01' , 'date2' => $ynow.'-09-30'  ,   'peengob'=>$ynow], ['class' => 'btn btn-success']).'&nbsp;'; 
// echo '</br></br>';

if(!empty($peengob)){
$ypeengob=$peengob-1; //61 ต.ค.-ธค
//$yg=substr($yg,2,4);    
$ypeengob1=$peengob; //62 ม.ค.-ก.ย.
//$yg1=substr($yg1,2,4);
if($m1<=10){ 
    $ynow=$ynow+1;

} 
if($m1>=9){
    $ynow=$ynow;

}
echo Html::a('ปีงบ : '.($ynow+1), ['/fwfhealthclient/index', 'peengob'=>($ynow+1)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.$ynow, ['/fwfhealthms/index', 'peengob'=>$ynow], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.($ynow-1), ['/fwfhealthms/index', 'peengob'=>($ynow-1)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.($ynow-2), ['/fwfhealthms/index', 'peengob'=>($ynow-2)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.($ynow-3), ['/fwfhealthms/index', 'peengob'=>($ynow-3)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.($ynow-4), ['/fwfhealthms/index', 'peengob'=>($ynow-4)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo Html::a('ปีงบ : '.($ynow-5), ['/fwfhealthms/index', 'peengob'=>($ynow-5)], ['class' => 'btn btn-success']).'&nbsp;'; 
echo '</br></br>';



}
// echo $cy.' '.$yt;

//ดึงข้อมูลเป็นเดือนตามปีงบ
?>

</div>
<?php
echo GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
                'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
        'panel'=>[
            'before'=>'ข้อมูล บัตรประกันสุขภาพ รายหน่วยบริการ',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
        ],
	    'responsive' => true,
        'hover' => true,
	'exportConfig' => [
        GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'ข้อมูล OP (A)'.date('Y-d-m')],
        //GridView::PDF => ['label' => 'Export as PDF', 'filename' => 'ข้อมูล OP (A)'.date('Y-d-m')],
        GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'ข้อมูล OP (A)'.date('Y-d-m')],
        GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'ข้อมูล OP (A)'.date('Y-d-m')],
        ],
        // set your toolbar
            'toolbar' =>  [
                ['content' =>
                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['index'], ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'รีเซ็ต')])
                ],
                '{toggleData}',
                '{export}',
            ],
        // set export properties
            'export' => [
                'fontAwesome' => true
            ],
            'pjax' => true,
            'pjaxSettings' => [
                'neverTimeout' => true,
                'beforeGrid' => '',
                'afterGrid' => '',
            ],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'hosname:text:รพ.',
                // 'c365:text:365',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c365',
                  'label' => '365',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c365"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c500:text:500',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c500',
                  'label' => '500',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c500"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c730:text:730',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c730',
                  'label' => '730',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c730"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c900:text:900',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c900',
                  'label' => '900',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c900"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c1600:text:1,600',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c1600',
                  'label' => '1,600',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c1600"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c2200:text:2,200',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c2200',
                  'label' => '2,200',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c2200"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c3200:text:3,200',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c3200',
                  'label' => '3,200',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c3200"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'c3000:text:3,000',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'c3000',
                  'label' => '3,000',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["c3000"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'total_card:text:รวม',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'total_card',
                  'label' => 'รวม(บัตร)',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["total_card"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'manages_sj:text:ค่าบริหารจัดการ',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'manages_sj',
                  'label' => 'ค่าบริหารจัดการ(สสจ.)',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["manages_sj"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'P_and_P:text:ส่งเสริม&ป้องกัน',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'P_and_P',
                  'label' => 'ส่งเสริม&ป้องกัน',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["P_and_P"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'claim:text:ค่าบริการทางการแพทย์',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'claim',
                  'label' => 'ค่าบริการทางการแพทย์',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["claim"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'total_ssj:text:รวม',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'total_ssj',
                  'label' => 'รวม(สสจ.)',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["total_ssj"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'manage_sp:text:ค่าบริหารจัดการ',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'manage_sp',
                  'label' => 'ค่าบริหารจัดการ(สป.)',
                  'format'=>'raw',
                  'value'=>function($model){
                    return  "<p align = 'right'>".number_format((float)($model["manage_sp"] ?? 0),0,".",",")."</p>";
                  }
                ],
                // 'HC:text:ค่าใช้จ่ายสูง',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'HC',
                  'label' => 'ค่าใช้จ่ายสูง',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["HC"] ?? 0),0,".",",")."</p>" ;
                  }
                ],
                // 'total_sp:text:รวม',
                [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                  'attribute' => 'total_sp',
                  'label' => 'รวม(สป.)',
                  'format'=>'raw',
                  'value'=>function($model){
                    return "<p align = 'right'>".number_format((float)($model["total_sp"] ?? 0),0,".",",")."</p>" ;
                  }
                ],

            ],

]);
?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
code sql
</button>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">code sql</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="highlight">
            <pre>
                <code>
                <?= $sql ?>  
                </code>
            </pre>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?php $this->registerCss("
.tg  {border-collapse:collapse;border-color:#aaa;border-spacing:0;}
.tg td{background-color:#fff;border-color:#aaa;border-style:solid;border-width:1px;color:#333;
  font-family:Arial, sans-serif;font-size:14px;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg th{background-color:#f38630;border-color:#aaa;border-style:solid;border-width:1px;color:#fff;
  font-family:Arial, sans-serif;font-size:14px;font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
.tg .tg-c3ow{border-color:inherit;text-align:center;vertical-align:top}
.tg .tg-dvpl{border-color:inherit;text-align:right;vertical-align:top}


@import url(https://fonts.googleapis.com/css?family=Roboto:400,500,700,300,100);

body {
  background-color: #3e94ec;
  font-family: 'Roboto', helvetica, arial, sans-serif;
  font-size: 14px;
  font-weight: 400;
  text-rendering: optimizeLegibility;
}

div.table-title {
   display: block;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;
}

.table-title h3 {
   color: #fafafa;
   font-size: 14px;
   font-weight: 400;
   font-style:normal;
   font-family: 'Roboto', helvetica, arial, sans-serif;
   text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
   text-transform:uppercase;
}


/*** Table Styles **/

.table-fill {
  background: white;
  border-radius:3px;
  border-collapse: collapse;
  height: 320px;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
  animation: float 5s infinite;
}
 
th {
  color:#D5DDE5;;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:14px;
  font-weight: 100;
  padding:24px;
  text-align:center;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;
}

th:first-child {
  border-top-left-radius:3px;
}
 
th:last-child {
  border-top-right-radius:3px;
  border-right:none;
}
  
tr {
  border-top: 1px solid #C1C3D1;
  border-bottom-: 1px solid #C1C3D1;
  color:#666B85;
  font-size:14px;
  font-weight:normal;
  text-shadow: 0 1px 1px rgba(256, 256, 256, 0.1);
}
 
tr:hover td {
  background:#4E5066;
  color:#FFFFFF;
  border-top: 1px solid #22262e;
}
 
tr:first-child {
  border-top:none;
}

tr:last-child {
  border-bottom:none;
}
 
tr:nth-child(odd) td {
  background:#EBEBEB;
}
 
tr:nth-child(odd):hover td {
  background:#4E5066;
}

tr:last-child td:first-child {
  border-bottom-left-radius:3px;
}
 
tr:last-child td:last-child {
  border-bottom-right-radius:3px;
}
 
td {
  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:14px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;
}

td:last-child {
  border-right: 0px;
}

th.text-left {
  text-align: left;
}

th.text-center {
  text-align: center;
}

th.text-right {
  text-align: right;
}

td.text-left {
  text-align: left;
}

td.text-center {
  text-align: center;
}

td.text-right {
  text-align: right;
'
")?>

<!-- JS -->
<?php $this->registerJs("
				function showhide(id) {
          var e = document.getElementById(id);
          e.style.display = (e.style.display == 'block') ? 'none' : 'block';
          }
")?>