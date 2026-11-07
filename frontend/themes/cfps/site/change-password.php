<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $error string|null */

$this->title = 'เปลี่ยนรหัสผ่าน';
?>

<div class="login-box">
    <div class="login-logo">
        <b style="color:#000">โปรแกรมเคลมต่างด้าว<br>Foreigner work fund<br>Phang-nga</b>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg"><i class="fa fa-lock"></i> กรุณาตั้งรหัสผ่านใหม่</p>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= htmlspecialchars(Yii::$app->session->getFlash('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-ban"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="callout callout-info" style="margin-bottom:15px;">
            <h4><i class="fa fa-info"></i> เงื่อนไขรหัสผ่าน</h4>
            <ul style="padding-left:18px;margin:0;">
                <li>ความยาวอย่างน้อย <strong>8 ตัวอักษร</strong></li>
                <li>มีตัวพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว</li>
                <li>มีตัวพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว</li>
                <li>มีตัวเลข (0-9) อย่างน้อย 1 ตัว</li>
            </ul>
        </div>

        <form method="POST" action="<?= Url::to(['/site/change-password']) ?>">
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>"
                   value="<?= Yii::$app->request->csrfToken ?>">

            <div class="form-group has-feedback">
                <input type="password" name="new_password" class="form-control"
                       placeholder="รหัสผ่านใหม่" required autocomplete="new-password">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback">
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="ยืนยันรหัสผ่านใหม่" required autocomplete="new-password">
                <span class="glyphicon glyphicon-ok form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                        <i class="fa fa-save"></i> บันทึกรหัสผ่านใหม่
                    </button>
                </div>
            </div>
        </form>

        <div style="margin-top:15px;text-align:center;">
            <?= Html::a('<i class="fa fa-sign-out"></i> ออกจากระบบ', ['/logout'], ['data' => ['method' => 'post']]) ?>
        </div>
    </div>
</div>
