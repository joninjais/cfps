<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\SqlCode */

$this->title = 'แก้ไขคำสั่ง SQL: ' . $model->title;
?>
<div class="sql-code-update">
    <div class="page-header" style="border-bottom:2px solid #605ca8; margin-bottom:20px;">
        <h3 style="color:#605ca8;">
            <i class="fa fa-pencil"></i> แก้ไขคำสั่ง SQL
            <small><?= \yii\helpers\Html::encode($model->title) ?></small>
        </h3>
    </div>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
