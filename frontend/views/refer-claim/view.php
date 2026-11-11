<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\ReferClaim;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReferClaim */
/* @var $hospitals array */

$this->title = 'ข้อมูลค่ารักษาส่งต่อ: ' . $model->refer_number;
$this->params['breadcrumbs'][] = ['label' => 'ค่ารักษาส่งต่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->refer_number;
?>
<div class="refer-claim-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->rcid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->rcid], [
            'class' => 'btn btn-danger',
            'data'  => ['confirm' => 'ยืนยันการลบข้อมูลนี้? ไม่สามารถกู้คืนได้', 'method' => 'post'],
        ]) ?>
        <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <div class="well well-sm">
        <?= Html::beginForm(['approve', 'id' => $model->rcid], 'post', ['class' => 'form-inline']) ?>
            <strong>ผลการอนุมัติ:</strong>
            <?= Html::dropDownList('status', $model->approved_status, ReferClaim::approvedStatusList(), [
                'class' => 'form-control', 'prompt' => '-- ยังไม่อนุมัติ --',
            ]) ?>
            <?= Html::submitButton('<i class="fa fa-check"></i> บันทึกผลอนุมัติ', ['class' => 'btn btn-primary']) ?>
        <?= Html::endForm() ?>
    </div>

    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'rcid',
            'book_number',
            ['attribute' => 'book_date', 'value' => ReferClaim::isoToThai($model->book_date)],
            ['attribute' => 'hospcode', 'value' => $hospitals[$model->hospcode] ?? $model->hospcode],
            ['attribute' => 'serv_hospcode', 'value' => $hospitals[$model->serv_hospcode] ?? $model->serv_hospcode],
            ['attribute' => 'dateserv', 'value' => ReferClaim::isoToThai($model->dateserv)],
            'refer_number',
            'hn',
            'seq_an',
            'cid',
            'name',
            ['attribute' => 'sex', 'value' => ReferClaim::sexList()[$model->sex] ?? null],
            'p_age',
            ['attribute' => 'p_visit_type', 'value' => ReferClaim::visitTypeList()[$model->p_visit_type] ?? $model->p_visit_type],
            ['attribute' => 'discharge_date_ipd', 'value' => ReferClaim::isoToThai($model->discharge_date_ipd)],
            'adj_rw',
            'icd10',
            'icd9',
            'cost_lab:decimal',
            'cost_xray:decimal',
            'cost_radiother:decimal',
            'cost_drug:decimal',
            'cost_other:decimal',
            'cost_total:decimal',
            'central_pay:decimal',
            'claim_amount:decimal',
            'pay_total:decimal',
            'username',
            'record_date',
            'record_time',
            'username_edit',
            'modified_date',
            'modified_time',
            'approved_status',
            'approved_by',
            ['attribute' => 'approved_date', 'value' => ReferClaim::isoToThai($model->approved_date)],
            'approved_time',
            'note',
        ],
    ]) ?>

</div>
