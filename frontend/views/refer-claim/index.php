<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\select2\Select2;
use frontend\models\ReferClaim;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\ReferClaimSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $hospitals array */

$this->title = 'บันทึกข้อมูลค่ารักษาส่งต่อต่างจังหวัด (นค.1)';
$this->params['breadcrumbs'][] = $this->title;

$flashes = Yii::$app->session->getAllFlashes();
?>
<div class="refer-claim-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php foreach ($flashes as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : Html::encode($type) ?>">
            <?= Html::encode(is_array($msg) ? implode(' ', $msg) : $msg) ?>
        </div>
    <?php endforeach; ?>

    <p>
        <?= Html::a('<i class="fa fa-plus"></i> เพิ่มข้อมูล', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'panel'        => [
            'before' => 'ข้อมูลค่ารักษาผู้ป่วยนอกส่งต่อ ประกันสุขภาพแรงงานต่างด้าว',
            'after'  => false,
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'hospcode',
                'value'     => function ($m) use ($hospitals) { return $hospitals[$m->hospcode] ?? $m->hospcode; },
                'filter'    => Select2::widget([
                    'model'         => $searchModel,
                    'attribute'     => 'hospcode',
                    'data'          => $hospitals,
                    'options'       => ['placeholder' => 'ทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true],
                ]),
            ],
            [
                'attribute' => 'serv_hospcode',
                'value'     => function ($m) use ($hospitals) { return $hospitals[$m->serv_hospcode] ?? $m->serv_hospcode; },
                'filter'    => Select2::widget([
                    'model'         => $searchModel,
                    'attribute'     => 'serv_hospcode',
                    'data'          => $hospitals,
                    'options'       => ['placeholder' => 'ทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true],
                ]),
            ],
            'book_number',
            ['attribute' => 'book_date', 'value' => function ($m) { return ReferClaim::isoToThai($m->book_date); }, 'filter' => false],
            ['attribute' => 'dateserv', 'value' => function ($m) { return ReferClaim::isoToThai($m->dateserv); }],
            'refer_number',
            'hn',
            'seq_an',
            'name',
            ['attribute' => 'cost_total', 'format' => ['decimal', 2], 'hAlign' => 'right', 'filter' => false],
            ['attribute' => 'claim_amount', 'format' => ['decimal', 2], 'hAlign' => 'right', 'filter' => false],

            [
                'attribute' => 'approved_status',
                'filter'    => ReferClaim::approvedStatusList(),
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'ทั้งหมด'],
                'format'    => 'raw',
                'contentOptions' => ['style' => 'min-width:130px'],
                'value'     => function ($m) {
                    // เลือกสถานะแล้วส่งฟอร์ม POST ทันที (มี CSRF) ยืนยันก่อนส่ง ยกเลิกแล้วคืนค่าเดิม
                    $color = $m->approved_status === ReferClaim::STATUS_PAID ? '#3c763d'
                           : ($m->approved_status === ReferClaim::STATUS_REJECTED ? '#a94442' : '#777');
                    return Html::beginForm(['approve', 'id' => $m->rcid], 'post', ['style' => 'margin:0'])
                        . Html::hiddenInput('from', 'index')
                        . Html::dropDownList('status', $m->approved_status, ReferClaim::approvedStatusList(), [
                            'prompt'   => 'รออนุมัติ',
                            'class'    => 'form-control input-sm',
                            'style'    => 'font-weight:bold;color:' . $color,
                            'data-old' => (string)$m->approved_status,
                            'title'    => $m->approved_by
                                ? 'โดย ' . $m->approved_by . ' เมื่อ ' . ReferClaim::isoToThai($m->approved_date) . ' ' . $m->approved_time
                                : 'ยังไม่ได้อนุมัติ',
                            'onchange' => "var t=this.options[this.selectedIndex].text;"
                                . "if(confirm('ยืนยันเปลี่ยนสถานะการอนุมัติเป็น \"'+t+'\" ?')){this.form.submit();}"
                                . "else{this.value=this.getAttribute('data-old');}",
                        ])
                        . Html::endForm();
                },
            ],
            [
                'attribute' => 'pay_total',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
                'filterInputOptions' => ['class' => 'form-control', 'type' => 'number', 'min' => 0, 'step' => '0.01'],
            ],
            [
                'attribute' => 'note',
                'format' => 'raw',
                'filter' => false,
                'contentOptions' => ['style' => 'min-width:260px'],
                'value' => static function ($m) {
                    return Html::beginForm(['note', 'id' => $m->rcid], 'post', ['style' => 'margin:0'])
                        . Html::hiddenInput('from', 'index')
                        . Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken())
                        . Html::textInput('note', $m->note, [
                            'class' => 'form-control input-sm',
                            'maxlength' => 255,
                            'aria-label' => 'หมายเหตุ: ' . (string)$m->refer_number,
                            'placeholder' => 'เพิ่มหมายเหตุ',
                        ])
                        . '<div style="margin-top:4px">'
                        . Html::submitButton('<i class="fa fa-save" aria-hidden="true"></i> บันทึก', [
                            'class' => 'btn btn-default btn-xs',
                        ])
                        . '</div>'
                        . Html::endForm();
                },
            ],
            'username',
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons'  => [
                    'delete' => function ($url) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => 'ลบ',
                            'data'  => ['confirm' => 'ยืนยันการลบข้อมูลนี้? ไม่สามารถกู้คืนได้', 'method' => 'post'],
                        ]);
                    },
                ],
                'urlCreator' => function ($action, $model) {
                    return [$action, 'id' => $model->rcid];
                },
            ],
        ],
    ]); ?>

</div>
