<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\OpdatainfoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="opdata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'opid') ?>

    <?= $form->field($model, 'hospcode') ?>

    <?= $form->field($model, 'dateserv') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'fund') ?>

    <?php // echo $form->field($model, 'cid') ?>

    <?php // echo $form->field($model, 'hn') ?>

    <?php // echo $form->field($model, 'seq') ?>

    <?php // echo $form->field($model, 'icd10') ?>

    <?php // echo $form->field($model, 'm1') ?>

    <?php // echo $form->field($model, 'm2') ?>

    <?php // echo $form->field($model, 'm3') ?>

    <?php // echo $form->field($model, 'm4') ?>

    <?php // echo $form->field($model, 'm5') ?>

    <?php // echo $form->field($model, 'm6') ?>

    <?php // echo $form->field($model, 'm7') ?>

    <?php // echo $form->field($model, 'm8') ?>

    <?php // echo $form->field($model, 'm9') ?>

    <?php // echo $form->field($model, 'm10') ?>

    <?php // echo $form->field($model, 'm11') ?>

    <?php // echo $form->field($model, 'm12') ?>

    <?php // echo $form->field($model, 'm13') ?>

    <?php // echo $form->field($model, 'm14') ?>

    <?php // echo $form->field($model, 'm15') ?>

    <?php // echo $form->field($model, 'm16') ?>

    <?php // echo $form->field($model, 'alltotal') ?>

    <?php // echo $form->field($model, 'FundHolderID') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
