<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReferClaim */
/* @var $hospitals array */

$this->title = 'บันทึกข้อมูลค่ารักษาส่งต่อ (นค.1)';
$this->params['breadcrumbs'][] = ['label' => 'ค่ารักษาส่งต่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'เพิ่มข้อมูล';
?>
<div class="refer-claim-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'hospitals' => $hospitals]) ?>
</div>
