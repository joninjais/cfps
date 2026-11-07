<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\FwfhealthSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Fwfhealths';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fwfhealth-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Fwfhealth', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
                'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'fwfhealth_id',
            'datev',
            'hcode',
            'cid',
            'prefix',
            'fname',
            'lname',
            'birthday',
            // 'nation',
            // 'sex',
            // 'address',
            // 'company',
            // 'bossname',
            // 'tel',
            // 'hn',
            // 'card_id',
            // 'typehealth_id',
            // 'diesease_id',
            'exsfile',

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
