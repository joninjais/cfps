<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\InsurancepoliciesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="insurancepolicies-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'agency_code') ?>

    <?= $form->field($model, 'agency_name') ?>

    <?= $form->field($model, 'province') ?>

    <?= $form->field($model, 'f_code') ?>

    <?php // echo $form->field($model, 'passport_no') ?>

    <?php // echo $form->field($model, 'citizen_id') ?>

    <?php // echo $form->field($model, 'alien_ref_id') ?>

    <?php // echo $form->field($model, 'title_name') ?>

    <?php // echo $form->field($model, 'full_name') ?>

    <?php // echo $form->field($model, 'nationality') ?>

    <?php // echo $form->field($model, 'ethnicity') ?>

    <?php // echo $form->field($model, 'hn_code') ?>

    <?php // echo $form->field($model, 'purchase_date') ?>

    <?php // echo $form->field($model, 'effective_date') ?>

    <?php // echo $form->field($model, 'expiry_date') ?>

    <?php // echo $form->field($model, 'policy_status') ?>

    <?php // echo $form->field($model, 'card_price') ?>

    <?php // echo $form->field($model, 'coverage_period') ?>

    <?php // echo $form->field($model, 'father_passport_no') ?>

    <?php // echo $form->field($model, 'father_citizen_id') ?>

    <?php // echo $form->field($model, 'father_alien_ref_id') ?>

    <?php // echo $form->field($model, 'father_full_name') ?>

    <?php // echo $form->field($model, 'mother_passport_no') ?>

    <?php // echo $form->field($model, 'mother_citizen_id') ?>

    <?php // echo $form->field($model, 'mother_alien_ref_id') ?>

    <?php // echo $form->field($model, 'mother_full_name') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
