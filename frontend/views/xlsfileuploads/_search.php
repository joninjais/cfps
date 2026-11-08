<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\XlsfileuploadsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="xlsfileuploads-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'xlsid') ?>

    <?= $form->field($model, 'exsfile') ?>

    <?= $form->field($model, 'hoscode') ?>

    <?= $form->field($model, 'typefileupload') ?>

    <?= $form->field($model, 'user') ?>

    <?= $form->field($model, 'uploaddatetime') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
