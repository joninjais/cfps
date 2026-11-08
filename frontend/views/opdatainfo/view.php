<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Opdata */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Opdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="opdata-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->opid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->opid], [
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
            'opid',
            'hospcode',
            'dateserv',
            'name',
            'fund',
            'cid',
            'hn',
            'seq',
            'icd10',
            'm1',
            'm2',
            'm3',
            'm4',
            'm5',
            'm6',
            'm7',
            'm8',
            'm9',
            'm10',
            'm11',
            'm12',
            'm13',
            'm14',
            'm15',
            'm16',
            'alltotal',
            'FundHolderID',
        ],
    ]) ?>

</div>
