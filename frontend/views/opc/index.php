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
<h1>ข้อมูล OP ติด C</h1>
    <div class='well'>
<?php  $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
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
    <div class="col-lg-3"><button class='btn btn-success'> ค้นหา </button> </div>




</div>

<?php  Activeform::end(); ?>
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
            'before'=>'ข้อมูล OP ติด C',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
        ],
	    'responsive' => true,
        'hover' => true,
	'exportConfig' => [
        GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'ข้อมูล OP ติด C'.date('Y-d-m')],
        //GridView::PDF => ['label' => 'Export as PDF', 'filename' => 'ข้อมูล OP ติด C'.date('Y-d-m')],
        GridView::EXCEL=> ['label' => 'Export as EXCEL', 'filename' => 'ข้อมูล OP ติด C'.date('Y-d-m')],
        GridView::TEXT=> ['label' => 'Export as TEXT', 'filename' => 'ข้อมูล OP ติด C'.date('Y-d-m')],
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
