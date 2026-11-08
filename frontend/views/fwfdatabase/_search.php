<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\FwfdatabaseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fwfdatabase-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'fwfid') ?>

    <?= $form->field($model, 'PeriodID') ?>

    <?= $form->field($model, 'PeriodStartDate') ?>

    <?= $form->field($model, 'PeriodEndDate') ?>

    <?= $form->field($model, 'CID') ?>

    <?php // echo $form->field($model, 'FundHolderID') ?>

    <?php // echo $form->field($model, 'fname') ?>

    <?php // echo $form->field($model, 'lname') ?>

    <?php // echo $form->field($model, 'timeregis') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
