<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'เข้าสู่ระบบ | CFPS พังงา';
$webroot = Yii::getAlias('@frontend/web');
$cssVer = is_file($webroot . '/css/cfps-login.css') ? filemtime($webroot . '/css/cfps-login.css') : 1;
$jsVer  = is_file($webroot . '/js/cfps-login.js') ? filemtime($webroot . '/js/cfps-login.js') : 1;
$this->registerCssFile('@web/css/cfps-login.css?v=' . $cssVer);
$this->registerJsFile('@web/js/cfps-login.js?v=' . $jsVer, ['position' => \yii\web\View::POS_END]);
?>
<div class="login-box">
    <div class="cfps-login-card">
        <div class="cfps-login-topbar"></div>
        <div class="cfps-login-inner">
            <div class="cfps-login-brand">
                <img src="<?= Url::to('@web/img/logoMOPH.png') ?>" alt="MOPH">
                <div>
                    <h1 class="cfps-login-brand-title">โปรแกรมเคลมต่างด้าว</h1>
                    <p class="cfps-login-brand-sub">Foreigner Work Fund · จ.พังงา</p>
                </div>
            </div>

            <h2 class="cfps-login-heading">เข้าสู่ระบบสำหรับเจ้าหน้าที่</h2>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'enableClientValidation' => true,
            ]); ?>

            <?= $form->field($model, 'username', [
                'options' => ['class' => 'form-group cfps-login-field'],
                'template' => '{label}<div class="cfps-input-wrap"><i class="fa fa-user cfps-input-icon"></i>{input}</div>{error}',
            ])->textInput([
                'autofocus' => true,
                'placeholder' => 'ระบุชื่อผู้ใช้งาน (Username)',
                'autocomplete' => 'username',
            ])->label('ชื่อผู้ใช้งาน') ?>

            <?= $form->field($model, 'password', [
                'options' => ['class' => 'form-group cfps-login-field'],
                'template' => '{label}<div class="cfps-input-wrap"><i class="fa fa-lock cfps-input-icon"></i>{input}<button type="button" class="cfps-pw-toggle" data-toggle-password="loginform-password" aria-label="แสดงหรือซ่อนรหัสผ่าน"><i class="fa fa-eye"></i></button></div><div id="cfps-capslock-hint" class="cfps-capslock-hint"><i class="fa fa-exclamation-triangle"></i> เปิดปุ่ม Caps Lock ค้างไว้อยู่</div>{error}',
            ])->passwordInput([
                'placeholder' => 'ระบุรหัสผ่าน',
                'autocomplete' => 'current-password',
            ])->label('รหัสผ่าน') ?>

            <?= Html::submitButton('<i class="fa fa-sign-in"></i> เข้าสู่ระบบ', [
                'class' => 'btn btn-primary cfps-login-submit',
                'name' => 'login-button',
            ]) ?>

            <?php ActiveForm::end(); ?>

            <?php if (is_file(Yii::getAlias('@frontend/views/site/_registration-link.php'))): ?>
                <div style="margin-top: 14px;">
                    <?= $this->render('@frontend/views/site/_registration-link') ?>
                </div>
            <?php endif; ?>

            <div class="cfps-login-footer">
                <?= Html::a('<i class="fa fa-book"></i> คู่มือการใช้งาน', ['@web/manual.html'], ['target' => '_blank', 'rel' => 'noopener']) ?>
                <?= Html::a('<i class="fa fa-shield"></i> นโยบาย PDPA', ['@web/pdpa.html'], ['target' => '_blank', 'rel' => 'noopener']) ?>
            </div>
        </div>
    </div>
</div>