<?php

use frontend\assets\RegistrationStatusAsset;
use frontend\components\RegistrationWindow;
use yii\helpers\Html;
use yii\helpers\Url;

RegistrationStatusAsset::register($this);
$open = RegistrationWindow::isOpen($window);
$remaining = $open ? max(0, (int)$window['expires_at'] - time()) : 0;
$this->registerCss('.registration-status [hidden] { display: none !important; }');
?>
<div class="registration-status alert alert-info" data-remaining="<?= $remaining ?>" data-status-url="<?= Html::encode(Url::to(['/site/registration-status'])) ?>">
    <div class="registration-status-open" <?= $open ? '' : 'hidden' ?>>
        <strong>เปิดรับลงทะเบียน</strong>
        <span class="registration-status-countdown" hidden></span>
        <span class="registration-status-fallback">
            ถึง <?= $open ? Html::encode(Yii::$app->formatter->asDatetime((int)$window['expires_at'])) : '' ?>
        </span>
    </div>
    <div class="registration-status-closed" <?= $open ? 'hidden' : '' ?>>ขณะนี้ปิดรับลงทะเบียน</div>
    <div class="registration-status-error" role="status" hidden>ตรวจสอบสถานะรับสมัครไม่ได้ กำลังลองใหม่</div>
</div>
