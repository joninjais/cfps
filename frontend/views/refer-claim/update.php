<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReferClaim */
/* @var $hospitals array */

$this->title = 'แก้ไขข้อมูลค่ารักษาส่งต่อ: ' . $model->refer_number;
$this->params['breadcrumbs'][] = ['label' => 'ค่ารักษาส่งต่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="refer-claim-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'hospitals' => $hospitals]) ?>
</div>
