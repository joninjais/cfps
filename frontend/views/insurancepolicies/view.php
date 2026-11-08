<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Insurancepolicies */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Insurancepolicies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="insurancepolicies-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'agency_code',
            'agency_name',
            'province',
            'f_code',
            'passport_no',
            'citizen_id',
            'alien_ref_id',
            'title_name',
            'full_name',
            'nationality',
            'ethnicity',
            'hn_code',
            'purchase_date',
            'effective_date',
            'expiry_date',
            'policy_status',
            'card_price',
            'coverage_period:ntext',
            'father_passport_no',
            'father_citizen_id',
            'father_alien_ref_id',
            'father_full_name',
            'mother_passport_no',
            'mother_citizen_id',
            'mother_alien_ref_id',
            'mother_full_name',
            'created_at',
        ],
    ]) ?>

</div>
