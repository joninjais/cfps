<?php

use yii\helpers\Html;
use yii\helpers\Url;
// use yii\grid\GridView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\InsurancepoliciesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$hoscode=$session['hoscode'];

$this->title = 'ข้อมูลกรมธรรม์';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
.db-page { font-family: 'Sarabun','Prompt',Arial,sans-serif; padding: 16px; font-size: 1rem; color: #1a1a1a; }
.db-title { font-size: 1.4rem; font-weight: 700; color: #00796b; margin-bottom: 18px; }
.db-cards { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 28px; }
.db-card {
    flex: 1; min-width: 180px; background: #fff;
    border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.08);
    padding: 22px 24px; text-align: center;
}
.db-card .num { font-size: 2.1rem; font-weight: 700; color: #009688; line-height: 1; }
.db-card .lbl { font-size: 1rem; color: #555; margin-top: 6px; }
.db-section { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 20px 22px; margin-bottom: 28px; }
.db-section h3 { font-size: 1.15rem; font-weight: 700; color: #00796b; border-bottom: 2px solid #b2dfdb; padding-bottom: 8px; margin-bottom: 16px; }
.db-charts { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 28px; }
.db-chart-box { flex: 1; min-width: 280px; background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 20px 22px; }
.db-chart-box h3 { font-size: 1.1rem; font-weight: 700; color: #00796b; margin-bottom: 14px; }
table.db-table { width: 100%; border-collapse: collapse; font-size: 1rem; }
table.db-table th { background: #009688; color: #fff; padding: 9px 12px; text-align: center; font-weight: 600; }
table.db-table td { padding: 8px 12px; border-bottom: 1px solid #e0f2f1; }
table.db-table tr:nth-child(even) td { background: #f5fffe; }
table.db-table tr:hover td { background: #e0f7fa; }
table.db-table .num-cell { text-align: right; font-variant-numeric: tabular-nums; }
table.db-table tfoot td { background: #00796b; color: #fff; font-weight: 700; }
.status-active { color: #009688; font-weight: 600; }
.status-other  { color: #888; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; background: #009688; color: #fff; border: none; border-radius: 8px; padding: 8px 20px; font-size: 1rem; font-weight: 600; text-decoration: none; margin-bottom: 18px; }
.btn-back:hover { background: #00796b; color: #fff; text-decoration: none; }
</style>

<div class="insurancepolicies-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php // Html::a('Create Insurancepolicies', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
    <div>
        <a href="<?= Url::to(['insurancepolicies/dashboard']) ?>" class="btn-back"> Dashboard<i class="fa fa-arrow-right"></i></a>
    </div>
    <div>
        <?= Html::beginForm(['insurancepolicies/clear-policies'], 'post', [
            'onsubmit' => 'return confirm("ยืนยันการลบข้อมูลกรมธรรม์ทั้งหมดของหน่วยบริการนี้? ไม่สามารถกู้คืนได้");',
            'style' => 'display:inline',
        ]) ?>
        <?php 
        if (!Yii::$app->facilityAccess->isProvincialAdmin()) {
            echo Html::submitButton('<i class="fa fa-trash"></i> ล้างข้อมูลกรมธรรม์', ['class' => 'btn btn-danger']);
        }
        ?>
        <?= Html::endForm() ?>
    </div>
</div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],
        'panel'=>[
            'before'=>'ข้อมูลกรมธรรม์ จาก FDH',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
                    ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
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
            //'created_at',

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
