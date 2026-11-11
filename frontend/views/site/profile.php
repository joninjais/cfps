<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user frontend\models\User */
/* @var $error string|null */
/* @var $success string|null */
/* @var $facilityName string|null */

$this->title = 'ข้อมูลส่วนตัว';
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-user-circle-o"></i> ข้อมูลส่วนตัว</h1>
        <ol class="breadcrumb">
            <li><?= Html::a('<i class="fa fa-home"></i> หน้าหลัก', ['/site/index']) ?></li>
            <li class="active">ข้อมูลส่วนตัว</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-edit"></i> แก้ไขข้อมูลส่วนตัว</h3>
                    </div>
                    <div class="box-body">

                        <?php if ($success): ?>
                            <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= Html::encode($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?= $error ?></div>
                        <?php endif; ?>

                        <?php $form = \yii\widgets\ActiveForm::begin(['action' => ['/site/profile'], 'method' => 'post']); ?>

                        <div class="form-group">
                            <label class="control-label">ชื่อผู้ใช้ (ไม่สามารถเปลี่ยนได้)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" value="<?= Html::encode($user->user) ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label required" for="fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" id="fname" name="fname" class="form-control"
                                           value="<?= Html::encode($user->fname) ?>" maxlength="100" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" id="lname" name="lname" class="form-control"
                                           value="<?= Html::encode($user->lname) ?>" maxlength="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="orgname">ชื่อหน่วยบริการ (อ้างอิงจากรหัสสถานพยาบาล)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-hospital-o"></i></span>
                                <input type="text" id="orgname" class="form-control"
                                       value="<?= Html::encode($facilityName ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label">รหัสสถานพยาบาล (ไม่สามารถเปลี่ยนได้)</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" class="form-control" value="<?= Html::encode($user->hoscode) ?>" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="phonenumber">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="tel" id="phonenumber" name="phonenumber" class="form-control"
                                       value="<?= Html::encode($user->phonenumber) ?>" maxlength="10" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="email">อีเมล <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="<?= Html::encode($user->email) ?>" maxlength="255" required>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <?= Html::submitButton('<i class="fa fa-save"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('<i class="fa fa-key"></i> เปลี่ยนรหัสผ่าน', ['/site/change-password'], ['class' => 'btn btn-warning']) ?>
                            <?= Html::a('<i class="fa fa-history"></i> ประวัติการเข้าใช้', ['/site/login-history'], ['class' => 'btn btn-default']) ?>
                        </div>

                        <?php \yii\widgets\ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
