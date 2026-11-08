<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;


/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'โปรแกรม Foreigner work fund : พังงา';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-user form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class=' form-control-feedback'><i class='fa fa-fw fa-key'></i></span>"
];
?>


<style>
.cfps-login-bg {
    min-height: 100vh;
    background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.cfps-login-box {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 6px 32px 0 rgba(0,0,0,0.10);
    padding: 36px 32px 28px 32px;
    max-width: 370px;
    width: 100%;
    margin: 32px auto;
    font-family: 'Sarabun', 'Prompt', Arial, sans-serif;
    font-size: 1.18rem; /* เพิ่มขนาดตัวอักษรหลัก */
}
.cfps-login-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 18px;
}
.cfps-login-logo img {
    width: 48px;
    height: 48px;
    display: block;
}
.cfps-login-title {
  font-size: 1.55rem; /* ใหญ่ขึ้น */
  font-weight: 700;
  color: #00796b;
  line-height: 1.3;
  text-align: left;
}
.cfps-login-box .login-box-msg {
  font-size: 1.25rem; /* ใหญ่ขึ้น */
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}
.cfps-login-box .form-group {
  margin-bottom: 20px;
}
.cfps-login-box .form-control {
  border-radius: 8px;
  font-size: 1.18rem; /* ใหญ่ขึ้น */
  padding: 14px 16px;
  border: 1px solid #b2dfdb;
  box-shadow: none;
}
.cfps-login-box .btn-primary {
  background: #009688;
  border: none;
  border-radius: 8px;
  font-size: 1.18rem; /* ใหญ่ขึ้น */
  font-weight: 600;
  padding: 12px 0;
  transition: background 0.2s;
}
.cfps-login-box .btn-primary:hover {
  background: #00796b;
}
.cfps-login-box .alert {
  font-size: 1.13rem; /* ใหญ่ขึ้น */
  border-radius: 8px;
}
</style>

<div class="cfps-login-bg">
  <div class="cfps-login-box">
    <div class="cfps-login-logo">
      <img src="<?= Yii::$app->request->baseUrl . '/img/logoMOPH.png' ?>" alt="logo">
      <div class="cfps-login-title">
        โปรแกรมเคลมต่างด้าว<br>Foreigner work fund<br>Phang-nga
      </div>
    </div>
    <div class="login-box-msg">เข้าสู่ระบบ</div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-ban"></i>
        <?= htmlspecialchars(Yii::$app->session->getFlash('error')) ?>
      </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

    <?= $form
      ->field($model, 'username', $fieldOptions1)
      ->label(false)
      ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

    <?= $form
      ->field($model, 'password', $fieldOptions2)
      ->label(false)
      ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

    <div class="row">
      <div class="col-xs-8">
        <?php // echo $form->field($model, 'rememberMe')->checkbox() ?>
      </div>
      <div class="col-xs-4">
        <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
      </div>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
