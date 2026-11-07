<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfhealth */

$this->title = 'Update Fwfhealth: ' . $model->fwfhealth_id;
$this->params['breadcrumbs'][] = ['label' => 'Fwfhealths', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fwfhealth_id, 'url' => ['view', 'id' => $model->fwfhealth_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="fwfhealth-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
