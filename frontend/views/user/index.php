<?php

use frontend\assets\UserAjaxAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $registrationWindow */

$this->title = 'จัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = $this->title;

UserAjaxAsset::register($this);
?>

<div class="user-index cfps-user-ajax-scope" data-index-url="<?= Html::encode(Url::to(['index'])) ?>">
    <div class="cfps-user-page-header">
        <h1 class="cfps-user-page-title">
            <i class="fa fa-users"></i> <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <!-- แถบสถานะเวลาเปิดรับลงทะเบียน (_registration-status.php ถ้ามีตัวแปรส่งมาจาก Controller) -->
    <?php if (is_file(__DIR__ . '/_registration-status.php')): ?>
        <div id="user-registration-banner">
            <?= $this->render('_registration-status', ['window' => $registrationWindow]) ?>
        </div>
    <?php endif; ?>

    <!-- ปุ่มคำสั่งหลัก (เปิดใน Modal ทั้งคู่) -->
    <div class="cfps-user-main-actions">
        <?= Html::a(
            '<i class="fa fa-plus"></i> เพิ่มผู้ใช้',
            ['create'],
            [
                'class' => 'btn btn-success js-user-modal',
                'data-modal-title' => 'เพิ่มผู้ใช้งานใหม่',
            ]
        ) ?>

        <?= Html::a(
            '<i class="fa fa-clock-o"></i> ตั้งค่าเปิดรับสมัคร',
            ['registration'],
            [
                'class' => 'btn btn-default js-user-modal',
                'data-modal-title' => 'ตั้งค่าเวลาเปิดรับลงทะเบียนผู้ใช้งาน',
            ]
        ) ?>
    </div>

    <!-- การ์ดตาราง Custom HTML + Pure AJAX -->
    <div id="user-table-card" class="cfps-user-card">
        <?= $this->render('_table', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>

    <!-- หน้าต่าง Modal สำหรับเพิ่ม/แก้ไขผู้ใช้ รีเซ็ตรหัสผ่าน และตั้งค่าเปิดรับสมัคร -->
    <?php
    Modal::begin([
        'id' => 'cfps-user-modal',
        'header' => '<h4 class="modal-title">จัดการข้อมูลผู้ใช้งาน</h4>',
        'options' => [
            'tabindex' => false, // ป้องกันการบล็อกช่องค้นหา Select2
        ],
    ]);
    echo '<div class="cfps-modal-loading">กำลังโหลด...</div>';
    Modal::end();
    ?>
</div>
