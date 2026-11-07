<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Opdata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="opdata-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'hospcode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dateserv')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fund')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'hn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'seq')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icd10')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm3')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm4')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm5')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm6')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm7')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm8')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm9')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm10')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm11')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm12')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm13')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm14')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm15')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'm16')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
