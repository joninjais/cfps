<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */

$this->title = 'รีเซ็ตรหัสผ่าน: ' . $model->getDisplayName();
?>
<div class="user-reset-password">
    <h3><i class="fa fa-key"></i> <?= Html::encode($this->title) ?></h3>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-ban"></i> <?= htmlspecialchars(Yii::$app->session->getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">

            <div class="callout callout-warning" style="margin-bottom:15px;">
                <h4><i class="fa fa-info-circle"></i> เงื่อนไขรหัสผ่าน</h4>
                <ul id="pw-rules" style="padding-left:18px;margin:4px 0 0;">
                    <li id="rule-len">ความยาวอย่างน้อย <strong>8 ตัวอักษร</strong></li>
                    <li id="rule-upper">มีตัวพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว</li>
                    <li id="rule-lower">มีตัวพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว</li>
                    <li id="rule-digit">มีตัวเลข (0-9) อย่างน้อย 1 ตัว</li>
                    <li id="rule-match">รหัสผ่านและการยืนยันต้องตรงกัน</li>
                </ul>
            </div>

            <form method="post" id="reset-pw-form">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>"
                       value="<?= Yii::$app->request->csrfToken ?>">
                <div class="form-group">
                    <label>รหัสผ่านใหม่</label>
                    <input type="password" class="form-control" id="newPassword" name="newPassword"
                           required minlength="8" autocomplete="new-password" placeholder="รหัสผ่านใหม่">
                </div>
                <div class="form-group">
                    <label>ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" class="form-control" id="newPasswordConfirm" name="newPasswordConfirm"
                           required minlength="8" autocomplete="new-password" placeholder="ยืนยันรหัสผ่านใหม่">
                </div>
                <button type="submit" id="btn-save" class="btn btn-warning" disabled>
                    <i class="fa fa-key"></i> บันทึกรหัสผ่านใหม่
                </button>
                <?= Html::a('<i class="fa fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-default']) ?>
            </form>
        </div>
    </div>
</div>

<style>
#pw-rules li { color: #c0392b; }
#pw-rules li.ok { color: #27ae60; }
#pw-rules li.ok::before { content: '✓ '; font-weight: bold; }
</style>

<script>
(function () {
    var pw   = document.getElementById('newPassword');
    var pw2  = document.getElementById('newPasswordConfirm');
    var btn  = document.getElementById('btn-save');

    var rules = {
        'rule-len':   function(v) { return v.length >= 8; },
        'rule-upper': function(v) { return /[A-Z]/.test(v); },
        'rule-lower': function(v) { return /[a-z]/.test(v); },
        'rule-digit': function(v) { return /[0-9]/.test(v); },
        'rule-match': function(v) { return v.length > 0 && v === pw2.value; }
    };

    function check() {
        var v    = pw.value;
        var pass = true;
        for (var id in rules) {
            var ok = rules[id](v);
            document.getElementById(id).className = ok ? 'ok' : '';
            if (!ok) pass = false;
        }
        btn.disabled = !pass;
    }

    pw.addEventListener('input', check);
    pw2.addEventListener('input', check);
})();
</script>
