<?php
use yii\helpers\Html;
use yii\helpers\Url;
use karatae99\datepicker\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\models\Masclient;
use kartik\widgets\DepDrop;
/*
$HOSPCODE=(ArrayHelper::map(Masclient::find()
                  ->where(['CUP_CODE'=>'11354'])
                  ->all(), 'HOSPCODE','HOSPNAME'));
echo print_r($HOSPCODE);     
*/
?>

    <div class='well'>
<?php  $form = ActiveForm::begin(); ?>
<div class="panel-body">
<div class="col-lg-12">

<?php /*  echo Html::dropDownList('CUP_CODE', 'AMPHUR',
      ArrayHelper::map(Masclient::find()->all(), 'CUP_CODE', 'AMPHUR'))
      */
      ?>    
        <?= $form->field($model, 'CUP_CODE')
                 ->dropdownList(ArrayHelper::map(Masclient::find()                 
                 ->all(), 'CUP_CODE', 'AMPHUR'),['CUP_CODE'=>'ddl-CUP_CODE','prompt'=>'เลือกอำเภอ..'])->label('อำเภอ/เขต') ?>

         <?php /*
         echo $form->field($model, 'HOSPCODE')
                  ->dropdownList(ArrayHelper::map(Masclient::find()
                  ->where(['CUP_CODE'=>'11354'])
                  ->all(), 'HOSPCODE','HOSPNAME'),['prompt'=>'ทั้งหมด..'])->label('เครือข่ายบริการ')*/ ?>
                                
<?php

echo $form->field($model, 'HOSPCODE')->widget(DepDrop::classname(), [
     'options'=>['CUP_CODE'=>'ddl-HOSPCODE'],
     'data'=> $HOSPCODE,
     'pluginOptions'=>[
         'depends'=>['ddl-CUP_CODE'],
         'placeholder'=>'เลือกเหตุการณ์ที่เกิดขึ้น...',
         'url'=>Url::to(['/site/getcupcode'])
     ]
 ])->label('เครือข่ายบริการ'); 

// print_r($model->dtevt);
print_r($HOSPCODE);

?>                      

    </div>
    <div class="col-lg-3">

<?php
/*
echo '<label class="control-label">ระหว่างวันที่:</label>';
echo DatePicker::widget([
    'model' => $model,
    'attribute' => 'date1',
    'template' => '{addon}{input}',
	'language' => 'th', // Thai B.E.
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
]);
*/
?>

    </div>
    <div class="col-lg-3">

<?php
/*
echo '<label class="control-label">ถึง:</label>';
echo DatePicker::widget([
    'model' => $model,
    'attribute' => 'date2',
    'template' => '{addon}{input}',
	'language' => 'th', // Thai B.E.
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ]
]);
*/
?>



    <div class="col-lg-3"><button class='btn btn-success'> ค้นหา </button> </div>


</div>

<?php  Activeform::end(); ?>
</div>