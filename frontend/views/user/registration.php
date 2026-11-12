<?php

use frontend\components\RegistrationWindow;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'ตั้งค่าเปิดรับสมัคร';
$open = RegistrationWindow::isOpen($window);
?>
<div class="user-registration">
    <h3><?= Html::encode($this->title) ?></h3>
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if ($open): ?>
        <p>เปิดรับสมัครถึง <?= Html::encode(Yii::$app->formatter->asDatetime((int)$window['expires_at'])) ?></p>
        <p>ลิงก์สมัคร: <?= Html::a('เปิดหน้าสมัคร', ['/site/signup', 'token' => $window['token']]) ?></p>
        <?= Html::beginForm(['registration-close'], 'post') ?>
        <?= Html::submitButton('ปิดรับสมัคร', ['class' => 'btn btn-danger', 'data-confirm' => 'ต้องการปิดรับสมัครหรือไม่?']) ?>
        <?= Html::endForm() ?>
    <?php else: ?>
        <p>ขณะนี้ปิดรับสมัคร</p>
    <?php endif; ?>
    <hr>
    <p>เลือกเวลาหรือกำหนดเองได้ไม่เกิน 1 ปี การเปิดรอบใหม่จะยกเลิกลิงก์รอบก่อนทันที</p>
    <div class="row"><div class="col-md-5">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'duration')->input('number', ['min' => 1, 'max' => 525600, 'step' => 1]) ?>
        <?= $form->field($model, 'unit')->dropDownList(['minutes' => 'นาที', 'hours' => 'ชั่วโมง']) ?>
        <p>เวลาที่ใช้บ่อย:
            <button type="button" class="btn btn-default btn-sm registration-preset" data-minutes="30">30 นาที</button>
            <button type="button" class="btn btn-default btn-sm registration-preset" data-minutes="60">1 ชั่วโมง</button>
            <button type="button" class="btn btn-default btn-sm registration-preset" data-minutes="120">2 ชั่วโมง</button>
        </p>
        <?= Html::submitButton('เปิดรับสมัคร', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-default']) ?>
        <?php ActiveForm::end(); ?>
    </div></div>
</div>
<?php
$this->registerJs("$('.registration-preset').on('click', function () {
    $('#registrationwindowform-duration').val($(this).data('minutes')).trigger('change');
    $('#registrationwindowform-unit').val('minutes').trigger('change');
});");
