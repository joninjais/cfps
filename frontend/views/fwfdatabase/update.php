<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfdatabase */

$this->title = 'Update Fwfdatabase: ' . $model->fwfid;
$this->params['breadcrumbs'][] = ['label' => 'Fwfdatabases', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fwfid, 'url' => ['view', 'id' => $model->fwfid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="fwfdatabase-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
