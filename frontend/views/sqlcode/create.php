<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\SqlCode */

$this->title = 'เพิ่มคำสั่ง SQL';
?>
<div class="sql-code-create">
    <div class="page-header" style="border-bottom:2px solid #605ca8; margin-bottom:20px;">
        <h3 style="color:#605ca8;"><i class="fa fa-plus"></i> เพิ่มคำสั่ง SQL</h3>
    </div>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
