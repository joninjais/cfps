<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */
/* @var $isProvincialAdminAccount bool */
/* @var $isProtectedAdminAccount bool */
/* @var $canChangeFacilityCode bool */
/* @var $currentRoleLabel string */
/* @var $facilityName string|null */

$this->title = 'แก้ไขผู้ใช้: ' . $model->getDisplayName();
?>
<div class="user-update">
    <h3><i class="fa fa-user-edit"></i> <?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-body">
                <?= $form->field($model, 'user')->textInput(['maxlength' => 50, 'disabled' => true]) ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'fname')->textInput(['maxlength' => 100, 'placeholder' => 'ชื่อ']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'lname')->textInput(['maxlength' => 100, 'placeholder' => 'นามสกุล']) ?></div>
                </div>
                <?= $form->field($model, 'hoscode')->textInput([
                    'maxlength' => 5,
                    'readonly' => !$canChangeFacilityCode || $isProtectedAdminAccount,
                ]) ?>
                <?php if ($isProtectedAdminAccount): ?>
                    <p class="help-block">บัญชีผู้ดูแลรหัส 00064 ใช้เป็นบัญชีหลักและไม่สามารถเปลี่ยนรหัสสถานพยาบาลได้</p>
                <?php elseif ($canChangeFacilityCode): ?>
                    <p class="help-block">เปลี่ยนได้เฉพาะรหัสสถานพยาบาลในจังหวัดที่รับผิดชอบ</p>
                <?php elseif ($isProvincialAdminAccount): ?>
                    <p class="help-block">เปลี่ยนรหัสสถานพยาบาลได้เฉพาะบัญชีของตนเองและภายในจังหวัดที่รับผิดชอบ</p>
                <?php else: ?>
                    <p class="help-block">เปลี่ยนรหัสสถานพยาบาลของผู้ใช้อื่นไม่ได้</p>
                <?php endif; ?>
                <?= $form->field($model, 'orgname')->textInput([
                    'maxlength' => 255,
                    'readonly' => true,
                    'value' => $facilityName ?? '',
                    'placeholder' => 'ระบบแสดงชื่อจากรหัสสถานพยาบาล',
                ]) ?>
                <div class="form-group">
                    <label>บทบาท/สิทธิ์ปัจจุบัน</label>
                    <p class="form-control-static"><?= Html::encode($currentRoleLabel) ?></p>
                </div>
                <?php if ($isProvincialAdminAccount): ?>
                    <p class="help-block">บัญชีผู้ดูแลจังหวัดไม่สามารถเปลี่ยนบทบาทจากหน้านี้ได้</p>
                <?php else: ?>
                    <?= Html::activeHiddenInput($model, 'requested_role', ['id' => 'user-requested_role']) ?>
                    <div class="form-group">
                        <label for="user-requested-role-label">บทบาทที่ต้องการกำหนด</label>
                        <input type="text" id="user-requested-role-label" class="form-control" readonly
                            value="<?= Html::encode([
                                'provincialAdmin' => 'เจ้าหน้าที่ สสจ.',
                                'hospitalStaff' => 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
                                'pcuStaff' => 'เจ้าหน้าที่ รพ.สต.',
                            ][$model->requested_role] ?? '') ?>"
                            placeholder="ระบบกำหนดจากรหัสสถานพยาบาล">
                        <p class="help-block">ระบบกำหนดประเภทผู้ใช้งานตามรหัสสถานพยาบาลและข้อมูล CUP</p>
                        <?php if ($model->hasErrors('requested_role')): ?>
                            <div class="help-block"><?= Html::encode($model->getFirstError('requested_role')) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?= $form->field($model, 'phonenumber')->textInput(['maxlength' => 10]) ?>
                <?= $form->field($model, 'email')->input('email') ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">

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
                        if (roleInput.length) {
                            roleInput.val(result.role || '');
                            roleLabel.val(roleLabels[result.role] || '');
                        }
                    }
                });
        });
        codeInput.trigger('change');
    })();
");
?>
