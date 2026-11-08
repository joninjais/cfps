<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Ipdata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipdata-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hospcode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'admitdate')->textInput() ?>

    <?= $form->field($model, 'dischartdate')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fund')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'an')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icd10')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icd9')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'los')->textInput() ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'adjrw')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'costadjrw')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
