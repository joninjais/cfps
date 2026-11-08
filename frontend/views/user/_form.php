<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */
/* @var $form yii\widgets\ActiveForm */
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
            <?= $form->field($model, 'hoscode')->textInput(['maxlength' => 5, 'placeholder' => 'รหัสสถานพยาบาล เช่น 00064']) ?>
            <?= $form->field($model, 'orgname')->textInput(['maxlength' => 255, 'placeholder' => 'ชื่อหน่วยบริการ']) ?>
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
