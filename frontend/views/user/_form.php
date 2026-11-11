<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */
/* @var $form yii\widgets\ActiveForm */
/* @var $facilityName string|null */
?>
<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'user')->textInput(['maxlength' => 50, 'placeholder' => 'username']) ?>
            <div class="row">
                <div class="col-md-6"><?= $form->field($model, 'fname')->textInput(['maxlength' => 100, 'placeholder' => 'ชื่อ']) ?></div>
                <div class="col-md-6"><?= $form->field($model, 'lname')->textInput(['maxlength' => 100, 'placeholder' => 'นามสกุล']) ?></div>
            </div>
            <?= $form->field($model, 'hoscode')->textInput(['maxlength' => 5, 'placeholder' => 'รหัสหน่วยบริการ 5 หลัก']) ?>
            <?= $form->field($model, 'orgname')->textInput([
                'maxlength' => 255,
                'readonly' => true,
                'value' => $facilityName ?? '',
                'placeholder' => 'ระบบแสดงชื่อจากรหัสสถานพยาบาล',
            ]) ?>
            <?= Html::activeHiddenInput($model, 'requested_role', ['id' => 'user-requested_role']) ?>
            <div class="form-group">
                <label for="user-requested-role-label">ประเภทผู้ใช้งานที่ขอ</label>
                <input type="text" id="user-requested-role-label" class="form-control" readonly
                    value="<?= Html::encode([
                        'provincialAdmin' => 'เจ้าหน้าที่ สสจ.',
                        'hospitalStaff' => 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
                        'pcuStaff' => 'เจ้าหน้าที่ รพ.สต.',
                    ][$model->requested_role] ?? '') ?>"
                    placeholder="ระบบกำหนดจากรหัสสถานพยาบาล">
                <?php if ($model->hasErrors('requested_role')): ?>
                    <div class="help-block"><?= Html::encode($model->getFirstError('requested_role')) ?></div>
                <?php endif; ?>
            </div>
            <?= $form->field($model, 'phonenumber')->textInput(['maxlength' => 10]) ?>
            <?= $form->field($model, 'email')->input('email') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => 'รหัสผ่านอย่างน้อย 8 ตัวอักษร, มีตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลข']) ?>
            <?= $form->field($model, 'newPasswordConfirm')->passwordInput(['placeholder' => 'ยืนยันรหัสผ่าน']) ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                <b>ข้อกำหนดรหัสผ่าน:</b><br>
                - อย่างน้อย 8 ตัวอักษร<br>
                - มีตัวพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว<br>
                - มีตัวพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว<br>
                - มีตัวเลข (0-9) อย่างน้อย 1 ตัว<br>
                <br>
                รหัสผ่านจะถูกเข้ารหัสโดยอัตโนมัติ
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$facilityLookupUrl = Json::htmlEncode(Url::to(['/site/facility-name']));
$this->registerJs("
    (function () {
        var codeInput = $('#user-hoscode');
        var nameInput = $('#user-orgname');
        var roleInput = $('#user-requested_role');
        var roleLabel = $('#user-requested-role-label');
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
