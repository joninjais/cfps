<?php

use frontend\assets\SignupAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model frontend\models\SignupForm */

SignupAsset::register($this);
$this->title = 'สมัครบัญชีผู้ใช้งาน';
?>
<main class="signup-page">
    <div class="signup-card">
        <header class="signup-header">
            <div class="signup-brand">
                <?= Html::img('@web/img/logoMOPH.png', ['alt' => '', 'class' => 'signup-logo']) ?>
                <div>
                    <span class="signup-brand-name">Foreigner work fund</span>
                    <span class="signup-brand-location">Phang-nga</span>
                </div>
            </div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>กรอกข้อมูลเพื่อส่งคำขอสมัครเข้าใช้งานระบบ</p>
        </header>

        <div class="signup-notice">
            <strong>ขั้นตอนหลังลงทะเบียน</strong>
            บัญชีของท่านจะเข้าใช้งานได้หลังจากผู้ดูแลอนุมัติ
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'form-signup',
            'options' => ['data-facility-url' => Url::to(['/site/facility-name'])],
        ]); ?>

        <section class="signup-section" aria-labelledby="signup-personal-title">
            <div class="signup-section-heading">
                <span class="signup-section-number" aria-hidden="true">1</span>
                <div><h2 id="signup-personal-title">ข้อมูลส่วนตัว</h2><p>ข้อมูลสำหรับติดต่อและระบุตัวตน</p></div>
            </div>
            <div class="row">
                <div class="col-sm-6"><?= $form->field($model, 'fname')->textInput(['maxlength' => 100, 'autocomplete' => 'given-name']) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'lname')->textInput(['maxlength' => 100, 'autocomplete' => 'family-name']) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'email')->input('email', ['maxlength' => 255, 'autocomplete' => 'email']) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'phonenumber')->input('tel', ['maxlength' => 10, 'autocomplete' => 'tel']) ?></div>
            </div>
        </section>

        <section class="signup-section" aria-labelledby="signup-facility-title">
            <div class="signup-section-heading">
                <span class="signup-section-number" aria-hidden="true">2</span>
                <div><h2 id="signup-facility-title">ข้อมูลหน่วยบริการ</h2><p>ระบุรหัสหน่วยบริการเพื่อค้นหาข้อมูล</p></div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($model, 'hoscode')->textInput([
                        'maxlength' => 5, 'inputmode' => 'numeric', 'placeholder' => 'รหัสหน่วยบริการ 5 หลัก',
                        'aria-describedby' => 'signup-facility-status',
                    ]) ?>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="signup-facility-name">ชื่อหน่วยบริการ</label>
                        <input type="text" id="signup-facility-name" class="form-control" readonly placeholder="ระบบแสดงชื่อหน่วยบริการ">
                    </div>
                </div>
                <div class="col-sm-12">
                    <?= Html::activeHiddenInput($model, 'requestedRole', ['id' => 'signupform-requestedrole']) ?>
                    <div class="form-group<?= $model->hasErrors('requestedRole') ? ' has-error' : '' ?>">
                        <label for="signup-requested-role-label">ประเภทผู้ใช้งานที่ขอ</label>
                        <input type="text" id="signup-requested-role-label" class="form-control" readonly
                            value="<?= Html::encode([
                                'provincialAdmin' => 'เจ้าหน้าที่ สสจ.',
                                'hospitalStaff' => 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
                                'pcuStaff' => 'เจ้าหน้าที่ รพ.สต.',
                            ][$model->requestedRole] ?? '') ?>"
                            placeholder="ระบบกำหนดจากรหัสหน่วยบริการ">
                        <?php if ($model->hasErrors('requestedRole')): ?>
                            <div class="help-block"><?= Html::encode($model->getFirstError('requestedRole')) ?></div>
                        <?php endif; ?>
                    </div>
                    <p id="signup-facility-status" class="signup-lookup-status" role="status" aria-live="polite"></p>
                </div>
            </div>
        </section>

        <section class="signup-section" aria-labelledby="signup-account-title">
            <div class="signup-section-heading">
                <span class="signup-section-number" aria-hidden="true">3</span>
                <div><h2 id="signup-account-title">ข้อมูลเข้าสู่ระบบ</h2><p>ตั้งชื่อผู้ใช้และรหัสผ่านสำหรับเข้าใช้งาน</p></div>
            </div>
            <div class="row">
                <div class="col-sm-12"><?= $form->field($model, 'username')->textInput(['maxlength' => 50, 'autocomplete' => 'username']) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'password')->passwordInput([
                    'minlength' => 8, 'autocomplete' => 'new-password', 'value' => '', 'aria-describedby' => 'signup-password-hint',
                ]) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'passwordConfirm')->passwordInput([
                    'minlength' => 8, 'autocomplete' => 'new-password', 'value' => '',
                ]) ?></div>
            </div>
            <p id="signup-password-hint" class="signup-password-hint">รหัสผ่านอย่างน้อย 8 ตัวอักษร มีตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลข</p>
        </section>

        <div class="signup-actions">
            <?= Html::submitButton('ส่งคำขอสมัคร', ['class' => 'btn signup-submit', 'name' => 'signup-button']) ?>
            <?= Html::a('กลับไปหน้าเข้าสู่ระบบ', ['/site/login'], ['class' => 'signup-back']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</main>
