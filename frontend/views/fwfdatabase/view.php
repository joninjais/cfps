<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfdatabase */

$this->title = $model->fwfid;
$this->params['breadcrumbs'][] = ['label' => 'Fwfdatabases', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fwfdatabase-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->fwfid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->fwfid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'fwfid',
            'PeriodID',
            'PeriodStartDate',
            'PeriodEndDate',
            'CID',
            'FundHolderID',
            'fname',
            'lname',
            'timeregis',
        ],
    ]) ?>

</div>
