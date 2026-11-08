<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\User */

$this->title = 'เพิ่มผู้ใช้งาน';
?>
<div class="user-create">
    <h3><i class="fa fa-user-plus"></i> <?= Html::encode($this->title) ?></h3>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
