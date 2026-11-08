<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfhealth */

$this->title = $model->fwfhealth_id;
$this->params['breadcrumbs'][] = ['label' => 'Fwfhealths', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fwfhealth-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->fwfhealth_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->fwfhealth_id], [
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
            'fwfhealth_id',
            'datev',
            'hcode',
            'cid',
            'prefix',
            'fname',
            'lname',
            'birthday',
            'nation',
            'sex',
            'address',
            'company',
            'bossname',
            'tel',
            'hn',
            'card_id',
            'typehealth_id',
            'diesease_id',
        ],
    ]) ?>

</div>
