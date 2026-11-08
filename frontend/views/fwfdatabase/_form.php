<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfdatabase */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fwfdatabase-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'PeriodID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PeriodStartDate')->textInput() ?>

    <?= $form->field($model, 'PeriodEndDate')->textInput() ?>

    <?= $form->field($model, 'CID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'FundHolderID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'timeregis')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
