<?php

use yii\helpers\Html;
use frontend\models\User;

                    $session    = Yii::$app->session;                                        
                    $checklogin = User::findOne(['user' => $session['user']]);

$types = require __DIR__ . '/_types.php';
$this->title = 'ตรวจสอบและนำเข้าข้อมูล';
$this->params['breadcrumbs'][] = ['label' => 'รายการไฟล์นำเข้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/xls-import.css?v=2', ['depends' => ['dmstr\web\AdminLteAsset']]);
$imported = (int)$model->importstatus !== 0;
$retired = $model->typefileupload === 'fwfhealth';
?>
<div class="xls-import-page">
    <div class="xls-import-heading">
        <div>
            <span class="xls-import-eyebrow">ขั้นตอนที่ 2 จาก 2</span>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>ตรวจสอบรายละเอียดไฟล์ก่อนนำเข้าข้อมูล</p>
        </div>
    </div>
    <section class="xls-import-card">
        <div class="xls-import-status <?= $imported ? 'is-complete' : 'is-pending' ?>">
            <?= $retired ? 'ยกเลิกการใช้งานประเภทไฟล์นี้แล้ว' : ($imported ? 'นำเข้าข้อมูลแล้ว' : 'อัปโหลดไฟล์แล้ว · รอนำเข้าข้อมูล') ?>
        </div>
        <?php if (!$imported && !$retired): ?>
            <p class="xls-import-muted">การอัปโหลดไฟล์ยังไม่ใช่การนำเข้าข้อมูล กรุณากดปุ่มนำเข้าข้อมูลเพื่อดำเนินการต่อ</p>
        <?php endif; ?>
        <section class="xls-import-details" aria-labelledby="xls-import-details-title">
            <h2 id="xls-import-details-title">รายละเอียดไฟล์</h2>
            <dl class="xls-import-detail-grid">
                <div class="xls-import-detail-item is-file">
                    <dt><span class="glyphicon glyphicon-file" aria-hidden="true"></span> ชื่อไฟล์</dt>
                    <dd class="xls-import-file-name"><?= Html::encode($model->exsfile) ?></dd>
                </div>
                <div class="xls-import-detail-item">
                    <dt><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span> รหัสหน่วยบริการ</dt>
                    <dd><?= Html::encode($model->hoscode) ?></dd>
                </div>
                <div class="xls-import-detail-item">
                    <dt><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> ประเภทข้อมูล</dt>
                    <dd><?= Html::encode($types[$model->typefileupload]['label'] ?? $model->typefileupload) ?></dd>
                </div>
                <div class="xls-import-detail-item">
                    <dt><span class="glyphicon glyphicon-user" aria-hidden="true"></span> ผู้ใช้อัปโหลดไฟล์</dt>
                    <dd><?php                     
                    echo ' ' . htmlspecialchars($checklogin ? $checklogin->getDisplayName() : '');
                    // echo Html::encode($model->user) ?></dd>
                </div>
                <div class="xls-import-detail-item">
                    <dt><span class="glyphicon glyphicon-time" aria-hidden="true"></span> วันที่และเวลาอัปโหลด</dt>
                    <dd><?= Html::encode($model->uploaddatetime) ?></dd>
                </div>
            </dl>
        </section>
        <div class="xls-import-actions">
            <?= $this->render('_import_button', [
                'model' => $model,
                'buttonClass' => 'btn btn-primary',
                'buttonLabel' => $imported ? 'นำเข้าข้อมูลอีกครั้ง' : 'นำเข้าข้อมูล',
            ]) ?>
            <?= Html::a('กลับไปรายการไฟล์', ['index'], ['class' => 'btn btn-default']) ?>
            <?= Html::a('ลบไฟล์', ['delete', 'id' => $model->xlsid], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'ยืนยันการลบไฟล์ Excel และข้อมูลในไฟล์ทั้งหมด?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </section>
</div>
