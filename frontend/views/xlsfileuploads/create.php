<?php

use frontend\components\ExcelImportHeaders;
use yii\helpers\Html;

$this->title = 'อัปโหลดไฟล์ Excel';
$this->params['breadcrumbs'][] = ['label' => 'รายการไฟล์นำเข้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/xls-import.css?v=2', ['depends' => ['frontend\assets\CfpsThemeAsset']]);
?>
<div class="xls-import-page">
    <div class="xls-import-heading">
        <div>
            <span class="xls-import-eyebrow">ขั้นตอนที่ 1 จาก 2</span>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>เลือกประเภทข้อมูลและไฟล์ที่ต้องการอัปโหลด ก่อนตรวจสอบและนำเข้าข้อมูล</p>
        </div>
    </div>
    <div class="xls-import-layout">
        <section class="xls-import-card">
            <h2>เลือกไฟล์</h2>
            <p class="xls-import-muted">ตรวจสอบประเภทข้อมูลให้ตรงกับไฟล์ที่เลือก</p>
            <?= $this->render('_form', ['model' => $model]) ?>
        </section>
        <aside class="xls-import-card xls-import-guide">
            <h2>เตรียมไฟล์ก่อนอัปโหลด</h2>
            <ol>
                <li>เลือกไฟล์ Excel ประเภท .xls หรือ .xlsx</li>
                <li>สำหรับ OP / PP และ IP หัวตารางในแถวแรกต้องตรงตามชื่อ ลำดับ และตัวพิมพ์ทุกคอลัมน์ โดยไม่มีหัวคอลัมน์เพิ่มเติม</li>
                <li>หลังอัปโหลด ให้ตรวจสอบรายละเอียดแล้วกด “นำเข้าข้อมูล” อีกครั้ง</li>
            </ol>
            <p>
                ดาวน์โหลดไฟล์ตัวอย่าง:
                <?= Html::a('OP / PP.xlsx', '@web/EX-op-pp.xlsx', ['download' => 'EX-op-pp.xlsx']) ?>
                และ
                <?= Html::a('IP.xlsx', '@web/EX-ip.xlsx', ['download' => 'EX-ip.xlsx']) ?>
            </p>
            <div>
                <strong>หัวตาราง OP / PP</strong>
                <p class="xls-import-headers"><?= Html::encode(implode(' | ', ExcelImportHeaders::OP)) ?></p>
            </div>
            <div>
                <strong>หัวตาราง IP</strong>
                <p class="xls-import-headers"><?= Html::encode(implode(' | ', ExcelImportHeaders::IP)) ?></p>
            </div>
            <p class="xls-import-muted">หมายเหตุ: nation = ['048', '265', '056', '266', '046', '057', '264','208']</p>
            <p class="xls-import-muted">ระบบคำนวณ alltotal ของ OP / PP และ costadjrw ของ IP เอง แต่ยังต้องมีคอลัมน์ดังกล่าวในไฟล์</p>
            <p class="xls-import-muted">กรมธรรม์ (FDH): ส่งออกไฟล์จาก <a href="https://fdh.moph.go.th/fwf/login" target="_blank" rel="noopener noreferrer">ระบบ FDH</a> เมนูกรมธรรม์ทั้งหมด แล้วอัปโหลดโดยไม่แก้ไขไฟล์</p>
        </aside>
    </div>
</div>
