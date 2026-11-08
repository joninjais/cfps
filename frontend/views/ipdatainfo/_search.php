<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\IpdatainfoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ipdata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ipid') ?>

    <?= $form->field($model, 'hospcode') ?>

    <?= $form->field($model, 'admitdate') ?>

    <?= $form->field($model, 'dischartdate') ?>

    <?= $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'fund') ?>

    <?php // echo $form->field($model, 'cid') ?>

    <?php // echo $form->field($model, 'hn') ?>

    <?php // echo $form->field($model, 'an') ?>

    <?php // echo $form->field($model, 'icd10') ?>

    <?php // echo $form->field($model, 'icd9') ?>

    <?php // echo $form->field($model, 'los') ?>

    <?php // echo $form->field($model, 'cost') ?>

    <?php // echo $form->field($model, 'adjrw') ?>

    <?php // echo $form->field($model, 'costadjrw') ?>

    <?php // echo $form->field($model, 'FundHolderID') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
