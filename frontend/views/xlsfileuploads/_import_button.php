<?php

use yii\helpers\Html;

$types = require __DIR__ . '/_types.php';
$type = $types[$model->typefileupload] ?? null;
if ($type === null) {
    echo '<span class="text-muted">ไม่รองรับประเภทไฟล์นี้</span>';
    return;
}

$label = '<span class="glyphicon glyphicon-upload" aria-hidden="true"></span> '
    . Html::encode($buttonLabel ?? 'นำเข้าข้อมูล');
$route = [$type['route'], 'id' => $model->xlsid];
if ($type['post']) {
    echo Html::beginForm($route, 'post', ['class' => 'xls-import-inline'])
        . Html::hiddenInput('id', $model->xlsid)
        . Html::submitButton($label, ['class' => $buttonClass])
        . Html::endForm();
} else {
    echo Html::a($label, $route, ['class' => $buttonClass]);
}
