<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model frontend\models\Xlsfileuploads */
/* @var $form yii\widgets\ActiveForm */
$session = Yii::$app->session;
$user=$session['user'];
$hoscode=$session['hoscode'];
//$uploaddatetime = date('Y-m-d H:i:s');
?>

<div class="xlsfileuploads-form">

<?php $form = ActiveForm::begin([
  'id' => 'xls-upload-form',
  'enableClientValidation' => false,
  'enableAjaxValidation' => false,
  'enableClientScript' => false,
  'options' => ['enctype' => 'multipart/form-data', 'onsubmit' => 'return checkInput()']
    ]); ?>

    <?= $form->field($model, 'user')->hiddenInput(['value'=>$user])->label(false); ?>

    <?= $form->field($model, 'hoscode')->hiddenInput(['value'=>$hoscode])->label(false); ?>

    <?= $form->field($model, 'typefileupload')->hiddenInput(['value'=>$typefile])->label(false); ?>
    <?php // $form->field($model, 'uploaddatetime')->hiddenInput(['value'=>$uploaddatetime])->label(false); ?>

    <?php // echo $form->field($model, 'excelname')->textInput(['maxlength' => true]) ?>

    <div class="row">
      <div class="col-md-2">

    <?php
    /*
    echo $form->field($model, 'typefileupload')->dropDownList([
      'fwfdata' => 'ข้อมูล fwfdatabase',
      'ip' => 'ข้อมูล ip',
      'op' => 'ข้อมูล op',
      ],['prompt'=>'เลือกประเภทไฟล์'])->label('ประเภทไฟล์');
      */
    ?>

    <?php // echo $form->field($model, 'exsfile')->fileInput() ?>

    <section class="container">
      <div class="row">
        <div class="col-md-8">
          <div class="box">

            <!-- ./fileuploader view component -->
            <div class="row">
              <div class="col-sm-10">
                <span class="control-fileupload">
                  <label for="file1" class="text-left">เลือกไฟล์ EXCEL <?=" ข้อมูล ".$st1?></label>
                  <input type="file" id="file1" name="Xlsfileuploads[exsfile]">
                  <?php // echo $form->field($model, 'exsfile')->fileInput(['id'=>'file1']) ?>
                </span>
              </div>
              <div class="col-sm-2">  
                <!-- <button type="button" class="btn btn-primary btn-block">
                  <i class="icon-upload icon-white"></i> Upload
                </button> -->

                <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Upload') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



    </div>
    </div>

    <div class="form-group">
        <?php // Html::submitButton($model->isNewRecord ? Yii::t('app', 'Upload') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<!-- CSS -->
<?php $this->registerCss("
a:focus {
	outline: none !important;
	outline-offset: none !important;
}

body {
	background: #f5f6f5;
	color: #333;
}

/* helper classses */

.margin-top-20 {
	margin-top: 20px;
}

.margin-bottom-20 {
	margin-top: 20px;
}

.no-margin {
	margin: 0px;
}

/* box component */

.box {
	border-color: #e6e6e6;
	background: #FFF;
	border-radius: 6px;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
	padding: 10px;
	margin-bottom: 40px;
}

.box-center {
	margin: 20px auto;
}

/* input [type = file]
----------------------------------------------- */

input[type=file] {
	display: block !important;
	right: 1px;
	top: 1px;
	height: 34px;
	opacity: 0;
  width: 100%;
	background: none;
	position: absolute;
  overflow: hidden;
  z-index: 2;
}

.control-fileupload {
	display: block;
	border: 1px solid #d6d7d6;
	background: #FFF;
	border-radius: 4px;
	width: 100%;
	height: 36px;
	line-height: 36px;
	padding: 0px 10px 2px 10px;
  overflow: hidden;
  position: relative;
  
  &:before, input, label {
    cursor: pointer !important;
  }
  /* File upload button */
  &:before {
    /* inherit from boostrap btn styles */
    padding: 4px 12px;
    margin-bottom: 0;
    font-size: 14px;
    line-height: 20px;
    color: #333333;
    text-align: center;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
    vertical-align: middle;
    cursor: pointer;
    background-color: #f5f5f5;
    background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
    background-repeat: repeat-x;
    border: 1px solid #cccccc;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    border-bottom-color: #b3b3b3;
    border-radius: 4px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: color 0.2s ease;

    /* add more custom styles*/
    content: 'Browse';
    display: block;
    position: absolute;
    z-index: 1;
    top: 2px;
    right: 2px;
    line-height: 20px;
    text-align: center;
  }
  &:hover, &:focus {
    &:before {
      color: #333333;
      background-color: #e6e6e6;
      color: #333333;
      text-decoration: none;
      background-position: 0 -15px;
      transition: background-position 0.2s ease-out;
    }
  }
  
  label {
    line-height: 24px;
    color: #999999;
    font-size: 14px;
    font-weight: normal;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    position: relative;
    z-index: 1;
    margin-right: 90px;
    margin-bottom: 0px;
    cursor: text;
  }
}
")?>

<!-- JS -->
<?php $this->registerJs("
$(function() {
  $('input[type=file]').change(function(){
    var t = $(this).val();
    var labelText = 'File : ' + t.substr(12, t.length);
    $(this).prev('label').text(labelText);
  })
});

function checkInput() { 
  var fileInput = document.getElementById('file1');
  if (!fileInput || fileInput.files.length === 0) {
    alert('กรุณาเลือกไฟล์ EXCEL'); 
    return false; 
  }
  return true;
} 
")?>