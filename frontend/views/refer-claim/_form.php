<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use karatae99\datepicker\DatePicker;
use frontend\models\ReferClaim;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReferClaim */
/* @var $hospitals array */
?>

<div class="refer-claim-form">

    <?php $form = ActiveForm::begin(); ?>

    <h3>ส่วนหัวเอกสาร</h3>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'hospcode')->widget(Select2::class, [
                'data'          => $hospitals,
                'options'       => ['placeholder' => 'เลือก รพ.ต้นทาง'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'serv_hospcode')->widget(Select2::class, [
                'data'          => $hospitals,
                'options'       => ['placeholder' => 'เลือกหน่วยบริการที่รักษา'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'book_number')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-3">
            <?= $form->field($model, 'book_date')->widget(DatePicker::class, [
                'language'      => 'th', // ปี พ.ศ.
                'options'       => [
                    'value'        => ReferClaim::isoToThai($model->book_date),
                    'autocomplete' => 'off',
                    'placeholder'  => 'วว/ดด/ปปปป',
                ],
                'clientOptions' => [
                    'autoclose'      => true,
                    'format'         => 'dd/mm/yyyy',
                    'todayHighlight' => true,
                ],
            ]) ?>
        </div>
    </div>

    <h3>ข้อมูลผู้ป่วยและการส่งต่อ</h3>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'dateserv')->widget(DatePicker::class, [
                'language'      => 'th', // ปี พ.ศ.
                'options'       => [
                    'value'        => ReferClaim::isoToThai($model->dateserv),
                    'autocomplete' => 'off',
                    'placeholder'  => 'วว/ดด/ปปปป',
                ],
                'clientOptions' => [
                    'autoclose'      => true,
                    'format'         => 'dd/mm/yyyy',
                    'todayHighlight' => true,
                ],
            ]) ?>
        </div>
        <div class="col-md-3"><?= $form->field($model, 'refer_number')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'hn')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'seq_an')->textInput(['maxlength' => true]) ?></div>
    </div>

    <h3>ข้อมูลผู้ป่วย</h3>
    <div class="row">
        <div class="col-md-4"><?= $form->field($model, 'cid')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'sex')->dropDownList(ReferClaim::sexList(), ['prompt' => '-- เลือก --']) ?></div>
        <div class="col-md-2"><?= $form->field($model, 'p_age')->input('number', ['min' => 0, 'max' => 150]) ?></div>
    </div>

    <h3>ข้อมูลการรักษา</h3>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'p_visit_type')->dropDownList(ReferClaim::visitTypeList(), ['prompt' => '-- เลือกประเภท --']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'discharge_date_ipd')->widget(DatePicker::class, [
                'language'      => 'th',
                'options'       => [
                    'value'        => ReferClaim::isoToThai($model->discharge_date_ipd),
                    'autocomplete' => 'off',
                    'placeholder'  => 'วว/ดด/ปปปป',
                ],
                'clientOptions' => [
                    'autoclose'      => true,
                    'format'         => 'dd/mm/yyyy',
                    'todayHighlight' => true,
                ],
            ]) ?>
        </div>
        <div class="col-md-3"><?= $form->field($model, 'adj_rw')->textInput(['maxlength' => true]) ?></div>
    </div>

    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'icd10')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'icd9')->textInput(['maxlength' => true]) ?></div>
    </div>

    <h3>รายการค่ารักษาพยาบาล</h3>
    <div class="row">
        <?php foreach (['cost_lab', 'cost_xray', 'cost_radiother', 'cost_drug', 'cost_other'] as $attr): ?>
            <div class="col-md-2">
                <?= $form->field($model, $attr)->input('number', ['step' => '0.01', 'min' => 0, 'class' => 'form-control cost-input']) ?>
            </div>
        <?php endforeach; ?>
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label"><?= Html::encode($model->getAttributeLabel('cost_total')) ?></label>
                <input type="text" id="cost-total-display" class="form-control" readonly
                       value="<?= number_format((float)$model->cost_total, 2, '.', '') ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"><?= $form->field($model, 'central_pay')->input('number', ['step' => '0.01', 'min' => 0]) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'claim_amount')->input('number', ['step' => '0.01', 'min' => 0]) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'pay_total')->input('number', ['step' => '0.01', 'min' => 0]) ?></div>
    </div>

    <div class="row">
        <div class="col-md-12"><?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?></div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
(function () {
    var inputs = document.querySelectorAll('.cost-input');
    var out = document.getElementById('cost-total-display');
    function calc() {
        var sum = 0;
        inputs.forEach(function (el) { sum += parseFloat(el.value) || 0; });
        out.value = sum.toFixed(2);
    }
    inputs.forEach(function (el) { el.addEventListener('input', calc); });
    calc();
})();
JS
);
