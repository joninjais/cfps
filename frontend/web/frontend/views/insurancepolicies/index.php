<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\InsurancepoliciesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Insurancepolicies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="insurancepolicies-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Insurancepolicies', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'agency_code',
            'agency_name',
            'province',
            'f_code',
            //'passport_no',
            //'citizen_id',
            //'alien_ref_id',
            //'title_name',
            //'full_name',
            //'nationality',
            //'ethnicity',
            //'hn_code',
            //'purchase_date',
            //'effective_date',
            //'expiry_date',
            //'policy_status',
            //'card_price',
            //'coverage_period:ntext',
            //'father_passport_no',
            //'father_citizen_id',
            //'father_alien_ref_id',
            //'father_full_name',
            //'mother_passport_no',
            //'mother_citizen_id',
            //'mother_alien_ref_id',
            //'mother_full_name',
            //'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
