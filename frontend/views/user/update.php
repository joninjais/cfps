<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */

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
                <?= $form->field($model, 'orgname')->textInput(['maxlength' => 255, 'placeholder' => 'ชื่อหน่วยบริการ (ถ้ามี)']) ?>
                <?= $form->field($model, 'hoscode')->textInput(['maxlength' => 5]) ?>
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
