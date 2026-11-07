<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
//use kartik\date\DatePicker;
use karatae99\datepicker\DatePicker;
use yii\db\ActiveQuery;
use kartik\export\ExportMenu;
use yii\helpers\Json;
use frontend\models\Masclient;
//check สิทธการเข้าใช้
use frontend\models\Sysmember;

	$session = Yii::$app->session;
	$checklogin = Sysmember::findOne(['id'=>$session['userid'],]);
	if(!$checklogin){
		echo "<script>window.location='index.php?r=site/login';</script>";
	}
//check สิทธการเข้าใช้

$this->title = 'QOF : PHANGNGA';
$this->params['breadcrumbs'][] = $this->title;
/* @var $this yii\web\View */
?>
<h1>แฟ้ม Person</h1>
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
?>
    <div class='well'>
<?php  $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
<div class="panel-body">
<div class="col-lg-4">

<?php /*  echo Html::dropDownList('CUP_CODE', 'AMPHUR',
      ArrayHelper::map(Masclient::find()->all(), 'CUP_CODE', 'AMPHUR'))
      */
      ?>

    </div>
    <div class="col-lg-3">



<?php

echo '<label class="control-label">ระหว่างวันที่:</label>';
echo DatePicker::widget([
    'name' => 'date1',
    'value' => $date1,
    'template' => '{addon}{input}',
	'language' => 'th', // Thai B.E.
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
]);

?>   


    </div>
    <div class="col-lg-3">
    <?php

echo '<label class="control-label">ถึง:</label>';
echo DatePicker::widget([
    'name' => 'date2',
    'value' => $date2,
    'template' => '{addon}{input}',
	'language' => 'th', // Thai B.E.
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
]);

?>



    <div class="col-lg-3"><br><button class='btn btn-success'> ค้นหา </button> </div>


</div>

<?php  Activeform::end(); ?>
</div>
<h3 align = 'right'>      
    <?php

echo "&nbsp;ส่งออก ข้อมูล <i class='fa fa-fw fa-arrow-circle-right'></i>&nbsp;".ExportMenu::widget([
    'dataProvider' => $dataProvider2,
    'target' => ExportMenu::TARGET_BLANK,
    'exportConfig' => [
        ExportMenu::FORMAT_TEXT => false,
        ExportMenu::FORMAT_CSV => false,
        ExportMenu::FORMAT_EXCEL => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_HTML => false,
        
    ]
]);

?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3> 
<?php

//echo yii\grid\GridView::widget([
    echo \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider1,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
        //'responsive' => TRUE,
        //'hover' => TRUE,
        //'floatHeader' => TRUE,
        'panel' => [
            //'before' => $sql,
            'after'=>'ประมวลผล ณ '.date('d-m-Y H:i:s'),
            //'type' => \kartik\grid\GridView::TYPE_SUCCESS

        //'after'=>''
        ],
        'tableOptions' =>['class' => 'table table-striped table-bordered'],
        'rowOptions' => function ($model, $index, $widget, $grid) {

            if ($index%2 == 0) {
                return ['class' => 'info'];
            } else {
                return [];
            }
         },       
        'toolbar' =>  [
            //'{export}',
            '{toggleData}',
        ],        
        'export' => [
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_BLANK
        ],
    ]);

?>
<script type="text/javascript">
				function showhide(id) {
				var e = document.getElementById(id);
				e.style.display = (e.style.display == 'block') ? 'none' : 'block';
				}
</script>
<ul class="nav nav-pills"><li role="presentation" class="active"><a href="javascript:showhide('sql')">code sql</a></li></ul>
<div id="sql" style="display:none;">
	<p><div class="alert alert-danger"><?php echo $sql ?></div></p>
</div>
