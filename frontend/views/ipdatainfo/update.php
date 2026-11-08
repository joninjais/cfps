<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Ipdata */

$this->title = 'Update Ipdata: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Ipdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->ipid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ipdata-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
