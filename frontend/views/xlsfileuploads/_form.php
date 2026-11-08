<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$types = require __DIR__ . '/_types.php';
$typeOptions = [];
foreach ($types as $key => $type) {
    $typeOptions[$key] = $type['label'];
}
$selectedType = $model->typefileupload ?: Yii::$app->request->get('typefile', '');
$isNew = $model->isNewRecord;
?>
<div class="xls-import-form">
    <?php $form = ActiveForm::begin([
        'id' => 'xls-upload-form',
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'enableClientScript' => false,
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $form->errorSummary($model, ['header' => '<strong>กรุณาตรวจสอบข้อมูล</strong>']) ?>
    <?= $form->field($model, 'user')->hiddenInput(['value' => Yii::$app->session->get('user')])->label(false) ?>
    <?= $form->field($model, 'hoscode')->hiddenInput(['value' => Yii::$app->session->get('hoscode')])->label(false) ?>

    <?= $form->field($model, 'typefileupload')->dropDownList($typeOptions, [
        'prompt' => 'เลือกประเภทข้อมูล',
        'value' => $selectedType,
        'required' => true,
        'id' => 'xls-file-type',
    ])->label('ประเภทข้อมูล') ?>

    <?php if ($isNew): ?>
        <?= $form->field($model, 'exsfile')->fileInput([
            'id' => 'xls-file-input',
            'accept' => '.xls,.xlsx',
            'required' => true,
            'aria-describedby' => 'xls-file-help xls-file-name',
        ])->label('ไฟล์ Excel')->hint('รองรับไฟล์ .xls และ .xlsx') ?>
        <p id="xls-file-help" class="xls-import-muted">เลือกไฟล์ให้ตรงกับประเภทข้อมูล จากนั้นกดอัปโหลดเพื่อไปยังขั้นตอนนำเข้า</p>
        <p id="xls-file-name" class="xls-import-file-name" aria-live="polite"></p>
    <?php else: ?>
        <p class="xls-import-muted">ไฟล์ปัจจุบัน: <?= Html::encode($model->exsfile) ?> · หากต้องการเปลี่ยนไฟล์ กรุณาอัปโหลดไฟล์ใหม่</p>
    <?php endif; ?>

    <div class="xls-import-actions">
        <?= Html::submitButton($isNew ? 'อัปโหลดไฟล์' : 'บันทึกการเปลี่ยนแปลง', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('กลับไปรายการไฟล์', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
    <p id="xls-upload-progress" class="xls-import-muted" aria-live="polite"></p>
    <?php ActiveForm::end(); ?>
</div>
<?php
$this->registerJs(<<<'JS'
(function () {
    var file = document.getElementById('xls-file-input');
    var name = document.getElementById('xls-file-name');
    var form = document.getElementById('xls-upload-form');
    var progress = document.getElementById('xls-upload-progress');
    if (file && name) {
        file.addEventListener('change', function () {
            name.textContent = file.files.length ? 'ไฟล์ที่เลือก: ' + file.files[0].name : '';
        });
    }
    if (form && file && progress) {
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true;
            progress.textContent = 'กำลังอัปโหลดไฟล์ กรุณารอสักครู่';
        });
        window.addEventListener('pageshow', function () {
            var button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = false;
            progress.textContent = '';
        });
    }
})();
JS);
