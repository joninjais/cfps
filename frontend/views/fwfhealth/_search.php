<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\FwfhealthSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="fwfhealth-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'fwfhealth_id') ?>

    <?= $form->field($model, 'datev') ?>

    <?= $form->field($model, 'hcode') ?>

    <?= $form->field($model, 'cid') ?>

    <?= $form->field($model, 'prefix') ?>

    <?php // echo $form->field($model, 'fname') ?>

    <?php // echo $form->field($model, 'lname') ?>

    <?php // echo $form->field($model, 'birthday') ?>

    <?php // echo $form->field($model, 'nation') ?>

    <?php // echo $form->field($model, 'sex') ?>

    <?php // echo $form->field($model, 'address') ?>

    <?php // echo $form->field($model, 'company') ?>

    <?php // echo $form->field($model, 'bossname') ?>

    <?php // echo $form->field($model, 'tel') ?>

    <?php // echo $form->field($model, 'hn') ?>

    <?php // echo $form->field($model, 'card_id') ?>

    <?php // echo $form->field($model, 'typehealth_id') ?>

    <?php // echo $form->field($model, 'diesease_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
