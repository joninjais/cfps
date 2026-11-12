<?php

use frontend\components\RegistrationWindow;
use frontend\assets\RegistrationStatusAsset;
use yii\helpers\Html;
use yii\helpers\Url;

RegistrationStatusAsset::register($this);
$window = RegistrationWindow::current();
$open = RegistrationWindow::isOpen($window);
$url = $open ? Url::to(['/site/signup', 'token' => $window['token']]) : '#';
$remaining = $open ? max(0, (int)$window['expires_at'] - time()) : 0;
$deadline = $open ? Yii::$app->formatter->asDatetime((int)$window['expires_at']) : '';
?>
<div class="registration-link registration-status" data-remaining="<?= $remaining ?>" data-status-url="<?= Html::encode(Url::to(['/site/registration-status'])) ?>">
    <div id="registration-open" class="registration-open registration-status-open" <?= $open ? '' : 'hidden' ?>>
        <div class="registration-heading">เปิดรับลงทะเบียน</div>
        <p id="registration-countdown" class="registration-countdown registration-status-countdown" hidden></p>
        <p class="registration-countdown registration-status-fallback">เปิดรับสมัครถึง <?= Html::encode($deadline) ?></p>
        <?= Html::a('ลงทะเบียนผู้ใช้งาน', $url, [
            'class' => 'btn registration-button registration-status-link',
            'id' => 'registration-link',
        ]) ?>
    </div>
    <p id="registration-status-error" class="registration-error registration-status-error" role="status" hidden>ตรวจสอบสถานะรับสมัครไม่ได้ กำลังลองใหม่</p>
</div>
<?php
$this->registerCss(<<<CSS
.registration-link {
    margin-top: 20px;
    text-align: center;
}
.registration-open {
    padding: 16px;
    border: 1px solid #b2dfdb;
    border-radius: 10px;
    background: #f0faf8;
}
.registration-link [hidden] {
    display: none !important;
}
.registration-heading {
    color: #00695c;
    font-weight: 700;
    font-size: 1.15em;
}
.registration-countdown {
    margin: 5px 0 12px;
    color: #385e59;
    font-size: 1em;
    font-variant-numeric: tabular-nums;
}
.registration-link .registration-button {
    display: inline-block;
    padding: 9px 18px;
    border: 1px solid #009688;
    border-radius: 8px;
    background: #fff;
    color: #00796b;
    font-weight: 600;
    text-decoration: none;
}
.registration-link .registration-button:hover,
.registration-link .registration-button:focus {
    background: #00796b;
    border-color: #00796b;
    color: #fff;
}
.registration-link .registration-button:focus-visible {
    outline: 3px solid #80cbc4;
    outline-offset: 3px;
}
.registration-error {
    margin: 0;
    color: #a94442;
}
CSS);
