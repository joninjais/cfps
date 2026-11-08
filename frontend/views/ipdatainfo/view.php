<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Ipdata */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Ipdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipdata-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->ipid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->ipid], [
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
            'ipid',
            'hospcode',
            'admitdate',
            'dischartdate',
            'name',
            'fund',
            'cid',
            'hn',
            'an',
            'icd10',
            'icd9',
            'los',
            'cost',
            'adjrw',
            'costadjrw',
            'FundHolderID',
        ],
    ]) ?>

</div>
