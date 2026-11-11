<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\SignupForm */

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1>สมัครบัญชีผู้ใช้งาน</h1>

    <p>กรอกข้อมูลผู้ใช้และหน่วยบริการ บัญชีจะรอการอนุมัติจากสำนักงานสาธารณสุขจังหวัดก่อนเข้าใช้งาน</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'maxlength' => 50]) ?>

                <?= $form->field($model, 'fname')->textInput(['maxlength' => 100]) ?>

                <?= $form->field($model, 'lname')->textInput(['maxlength' => 100]) ?>

                <?= $form->field($model, 'email')->input('email') ?>

                <?= $form->field($model, 'hoscode')->textInput(['maxlength' => 5, 'placeholder' => 'รหัสหน่วยบริการ 5 หลัก']) ?>

                <div class="form-group">
                    <label for="signup-facility-name">ชื่อหน่วยบริการ</label>
                    <input type="text" id="signup-facility-name" class="form-control" readonly
                        placeholder="ระบบแสดงชื่อจากรหัสสถานพยาบาล">
                </div>

                <?= Html::activeHiddenInput($model, 'requestedRole', ['id' => 'signupform-requestedrole']) ?>
                <div class="form-group">
                    <label for="signup-requested-role-label">ประเภทผู้ใช้งานที่ขอ</label>
                    <input type="text" id="signup-requested-role-label" class="form-control" readonly
                        value="<?= Html::encode([
                            'provincialAdmin' => 'เจ้าหน้าที่ สสจ.',
                            'hospitalStaff' => 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
                            'pcuStaff' => 'เจ้าหน้าที่ รพ.สต.',
                        ][$model->requestedRole] ?? '') ?>"
                        placeholder="ระบบกำหนดจากรหัสสถานพยาบาล">
                    <?php if ($model->hasErrors('requestedRole')): ?>
                        <div class="help-block"><?= Html::encode($model->getFirstError('requestedRole')) ?></div>
                    <?php endif; ?>
                </div>

                <?= $form->field($model, 'password')->passwordInput(['minlength' => 8, 'autocomplete' => 'new-password']) ?>

                <div class="form-group">
                    <?= Html::submitButton('ส่งคำขอสมัคร', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php
    $facilityLookupUrl = Json::htmlEncode(Url::to(['/site/facility-name']));
    $this->registerJs("
        (function () {
            var codeInput = $('#signupform-hoscode');
            var nameInput = $('#signup-facility-name');
            var roleInput = $('#signupform-requestedrole');
            var roleLabel = $('#signup-requested-role-label');
            var roleLabels = {
                provincialAdmin: 'เจ้าหน้าที่ สสจ.',
                hospitalStaff: 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
                pcuStaff: 'เจ้าหน้าที่ รพ.สต.'
            };
            var pendingRequest = null;
            codeInput.on('input change blur', function () {
                var code = $.trim(codeInput.val());
                nameInput.val('');
                roleInput.val('');
                roleLabel.val('');
                if (pendingRequest) {
                    pendingRequest.abort();
                }
                if (!/^\\d{5}$/.test(code)) {
                    return;
                }
                pendingRequest = $.getJSON({$facilityLookupUrl}, {hoscode: code})
                    .done(function (result) {
                        if ($.trim(codeInput.val()) === code) {
                            nameInput.val(result.name || '');
                            roleInput.val(result.role || '');
                            roleLabel.val(roleLabels[result.role] || '');
                        }
                    });
            });
            codeInput.trigger('change');
        })();
    ");
    ?>
</div>
