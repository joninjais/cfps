<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\Insurancepolicies */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="insurancepolicies-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'agency_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'agency_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'province')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'f_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'passport_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'citizen_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'alien_ref_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nationality')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ethnicity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'birth_date')->textInput() ?>
    <?= $form->field($model, 'age_at_purchase')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hn_code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'house_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'village_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'alley')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'road')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'address_province')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'subdistrict')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'district')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'postal_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'purchase_date')->textInput() ?>

    <?= $form->field($model, 'effective_date')->textInput() ?>

    <?= $form->field($model, 'expiry_date')->textInput() ?>

    <?= $form->field($model, 'policy_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'reference_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'card_price')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'allocation_service_unit')->textInput() ?>
    <?= $form->field($model, 'allocation_provincial_pp')->textInput() ?>
    <?= $form->field($model, 'allocation_provincial_admin')->textInput() ?>
    <?= $form->field($model, 'allocation_provincial_total')->textInput() ?>
    <?= $form->field($model, 'allocation_central_hc')->textInput() ?>
    <?= $form->field($model, 'allocation_central_admin')->textInput() ?>
    <?= $form->field($model, 'allocation_central_total')->textInput() ?>

    <?= $form->field($model, 'coverage_period')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'father_passport_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'father_citizen_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'father_alien_ref_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'father_full_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mother_passport_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mother_citizen_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mother_alien_ref_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mother_full_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'occupation')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'employer_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'employer_address')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'employer_phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
