<?php

use yii\grid\GridView;
use yii\helpers\Html;

$types = require __DIR__ . '/_types.php';
$this->title = 'รายการไฟล์นำเข้า';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('@web/css/xls-import.css?v=2', ['depends' => ['frontend\assets\CfpsThemeAsset']]);
?>
<div class="xls-import-page">
    <div class="xls-import-heading">
        <div>
            <span class="xls-import-eyebrow">จัดการไฟล์ Excel</span>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>ตรวจสอบสถานะไฟล์ และดำเนินการนำเข้าข้อมูล</p>
        </div>
        <?= Html::a('<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> อัปโหลดไฟล์', ['create'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="xls-import-info">
        <strong>ขั้นตอนนำเข้า:</strong> อัปโหลดไฟล์ → ตรวจสอบรายละเอียด → กดนำเข้าข้อมูล
    </div>
    <section class="xls-import-card">
        <h2>ไฟล์ที่อัปโหลด</h2>
        <p class="xls-import-muted">แสดงเฉพาะไฟล์ของรหัสสถานบริการที่เข้าสู่ระบบ ค้นหาได้จากช่องใต้หัวตาราง และเลื่อนตารางด้านข้างเพื่อดูข้อมูลเพิ่มเติม</p>
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-bordered xls-import-table'],
                'emptyText' => 'ยังไม่มีไฟล์ที่ตรงกับเงื่อนไข',
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'exsfile',
                        'label' => 'ไฟล์ Excel',
                        'format' => 'raw',
                        'value' => static function ($model) {
                            return Html::a(Html::encode($model->exsfile), ['view', 'id' => $model->xlsid])
                                . '<br>' . Html::a('ดาวน์โหลดไฟล์', Yii::getAlias('@web') . '/uploads/' . rawurlencode($model->exsfile));
                        },
                    ],
                    'hoscode',
                    [
                        'attribute' => 'typefileupload',
                        'filter' => array_map(static function ($type) { return $type['label']; }, $types),
                        'value' => static function ($model) use ($types) {
                            return $types[$model->typefileupload]['label'] ?? $model->typefileupload;
                        },
                    ],
                    'user',
                    'uploaddatetime',
                    [
                        'attribute' => 'importstatus',
                        'filter' => false,
                        'format' => 'raw',
                        'value' => static function ($model) {
                            if ($model->typefileupload === 'fwfhealth') {
                                return '<span class="xls-import-status">ยกเลิกการใช้งาน</span>';
                            }
                            return (int)$model->importstatus === 0
                                ? '<span class="xls-import-status is-pending">รอนำเข้า</span>'
                                : '<span class="xls-import-status is-complete">นำเข้าแล้ว</span>';
                        },
                    ],
                    [
                        'label' => 'ระยะเวลานำเข้า',
                        'value' => static function ($model) {
                            $start = strtotime((string)$model->startdatetime);
                            $end = strtotime((string)$model->enddatetime);
                            if ($start === false || $end === false || $end < $start) {
                                return '—';
                            }
                            $seconds = $end - $start;
                            return intdiv($seconds, 3600) . ' ชม. '
                                . intdiv($seconds % 3600, 60) . ' น. ' . ($seconds % 60) . ' ว.';
                        },
                    ],
                    [
                        'label' => 'ดำเนินการ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $actions = [];
                            if ((int)$model->importstatus === 0) {
                                $canImport = $model->typefileupload !== 'insurance'
                                    || Yii::$app->user->can('manageInsurancePolicies');
                                if ($canImport) {
                                    $actions[] = $this->render('_import_button', [
                                        'model' => $model,
                                        'buttonClass' => 'btn btn-primary btn-sm',
                                    ]);
                                }
                            }
                            if ($model->typefileupload !== 'fwfhealth') {
                                $actions[] = Html::a('เตรียมนำเข้าอีกครั้ง', ['reimport', 'id' => $model->xlsid], [
                                    'class' => 'btn btn-default btn-sm',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'ยืนยันการเตรียมไฟล์เพื่อนำเข้าอีกครั้ง? การดำเนินการนี้ยังไม่นำเข้าข้อมูล',
                                    ],
                                ]);
                            }
                            $actions[] = Html::a('ลบ', ['delete', 'id' => $model->xlsid], [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'ยืนยันการลบไฟล์ Excel และข้อมูลในไฟล์ทั้งหมด?',
                                ],
                            ]);
                            return '<div class="xls-import-row-actions">' . implode('', $actions) . '</div>';
                        },
                    ],
                ],
            ]) ?>
        </div>
    </section>
</div>
