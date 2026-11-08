<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use app\assets\FontAwesomeAsset;
FontAwesomeAsset::register($this);
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการผู้ใช้งาน';
?>
<div class="user-index">
    <h3><i class="fa fa-users"></i> <?= Html::encode($this->title) ?></h3>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <p><?= Html::a('<i class="fa fa-plus"></i> เพิ่มผู้ใช้', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'panel' => [
            'before' => '',
            'after'  => 'ประมวลผล ณ ' . date('Y-m-d H:i:s'),
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class'    => 'yii\grid\ActionColumn',
                // 'template' => '{update} ',
                'template'=>'<div class="btn-group btn-group-sm text-center" role="group">{update} </div>',
                'buttons'  => [
                    'update' => function ($url, $model) {
                        return Html::a('<span class="fa fa-pencil fa-2x"></span>', ['update', 'id' => $model->userid], ['title' => 'แก้ไข']);
                    },
                ],
            ],
            ['class'    => 'yii\grid\ActionColumn',
                // 'template' => '{reset}',
                'template'=>'<div class="btn-group btn-group-sm text-center" role="group">{reset} </div>',
                'buttons'  => [
                    'reset' => function ($url, $model) {
                        // เปลี่ยน icon เป็นรูปกุญแจสำหรับเปลี่ยนรหัสผ่านผู้ใช้
                        return Html::a('<span class="fa fa-key fa-2x"></span>', ['reset-password', 'id' => $model->userid], ['title' => 'เปลี่ยนรหัสผ่าน']);
                    },
                ],
            ],            
            // 'userid',
            'user',
            [
                'label'  => 'ชื่อ-นามสกุล',
                'value'  => function ($model) { return trim($model->fname . ' ' . $model->lname); },
                'filter' => yii\helpers\Html::activeTextInput(new \frontend\models\UserSearch(), 'fname', ['class' => 'form-control', 'placeholder' => 'ชื่อ']),
            ],
            'hoscode',
            'orgname',
            'phonenumber',
            'email:email',

                        [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons'  => [
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->userid], [
                            'title' => 'ลบ',
                            'data'  => ['confirm' => 'ยืนยันการลบผู้ใช้?', 'method' => 'post'],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
