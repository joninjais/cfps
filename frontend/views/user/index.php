<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use frontend\assets\FontAwesomeAsset;
FontAwesomeAsset::register($this);
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $roleLabels array */
/* @var $facilityNames array */
/* @var $adminUserIds array */
/* @var $userManagerIds array */
/* @var $canManageAdminRoles bool */

$this->title = 'จัดการผู้ใช้งาน';
?>
<div class="user-index">
    <h3><i class="fa fa-users"></i> <?= Html::encode($this->title) ?></h3>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <?= $this->render('_registration-status', ['window' => $registrationWindow]) ?>

    <p>
        <?= Html::a('<i class="fa fa-plus"></i> เพิ่มผู้ใช้', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('ตั้งค่าเปิดรับสมัคร', ['registration'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'rowOptions' => static function ($model) use ($userManagerIds) {
            return in_array((int)$model->userid, $userManagerIds, true) ? ['class' => 'info'] : [];
        },
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
                    'update' => function ($url, $model) use ($canManageAdminRoles, $adminUserIds) {
                        if (!$canManageAdminRoles
                            && in_array((int)$model->userid, $adminUserIds, true)
                            && (string)$model->userid !== (string)Yii::$app->user->id) {
                            return '';
                        }
                        return Html::a('<span class="fa fa-pencil fa-2x"></span>', ['update', 'id' => $model->userid], ['title' => 'แก้ไข']);
                    },
                ],
            ],
            ['class'    => 'yii\grid\ActionColumn',
                // 'template' => '{reset}',
                'template'=>'<div class="btn-group btn-group-sm text-center" role="group">{reset} </div>',
                'buttons'  => [
                    'reset' => function ($url, $model) use ($canManageAdminRoles, $adminUserIds) {
                        if (!$canManageAdminRoles
                            && in_array((int)$model->userid, $adminUserIds, true)
                            && (string)$model->userid !== (string)Yii::$app->user->id) {
                            return '';
                        }
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
            [
                'attribute' => 'hoscode',
                'label' => 'รหัสสถานพยาบาล',
                'filter' => Html::activeTextInput($searchModel, 'hoscode', [
                    'class' => 'form-control',
                    'placeholder' => 'ระบุรหัสสถานพยาบาล',
                    'aria-label' => 'กรองตามรหัสสถานพยาบาล',
                ]),
            ],
            [
                'label' => 'ชื่อหน่วยบริการ',
                'value' => static function ($model) use ($facilityNames) {
                    return $facilityNames[$model->hoscode] ?? 'ไม่พบข้อมูลหน่วยบริการ';
                },
            ],
            'phonenumber',
            'email:email',
            [
                'label' => 'บทบาท/สิทธิ์',
                'format' => 'raw',
                'value' => static function ($model) use ($roleLabels, $userManagerIds) {
                    $label = Html::encode($roleLabels[$model->userid] ?? 'ยังไม่กำหนดบทบาท');
                    if (in_array((int)$model->userid, $userManagerIds, true)) {
                        return '<span class="label label-primary"><i class="fa fa-shield"></i> userManager</span> ' . $label;
                    }
                    return $label;
                },
            ],
            [
                'label' => 'สิทธิ์ userManager',
                'format' => 'raw',
                'value' => static function ($model) use ($canManageAdminRoles, $adminUserIds) {
                    if (!$canManageAdminRoles
                        || $model->approval_status !== \frontend\models\User::APPROVAL_APPROVED
                        || (string)$model->hoscode === '00064') {
                        return '';
                    }

                    $isAdmin = in_array((int)$model->userid, $adminUserIds, true);
                    $confirmation = $isAdmin
                        ? 'ถอน role userManager และคืนสิทธิ์ตามรหัสสถานพยาบาลหรือไม่?'
                        : 'กำหนด role userManager (จัดการผู้ใช้) ให้บัญชีนี้หรือไม่?';

                    return Html::beginForm(['toggle-admin', 'id' => $model->userid], 'post', [
                        'class' => 'user-admin-role-form',
                        'style' => 'margin:0',
                        'data-confirm' => $confirmation,
                    ]) . Html::checkbox('grant', $isAdmin, [
                        'value' => '1',
                        'uncheck' => '0',
                        'class' => 'user-admin-role-checkbox',
                        'label' => 'userManager',
                        'labelOptions' => ['class' => 'checkbox-inline'],
                        'aria-label' => $isAdmin ? 'ถอน role userManager' : 'กำหนด role userManager',
                    ]) . Html::endForm();
                },
            ],
            [
                'attribute' => 'approval_status',
                'label' => 'สถานะอนุมัติ',
                'value' => function ($model) {
                    return [
                        \frontend\models\User::APPROVAL_PENDING => 'รออนุมัติ',
                        \frontend\models\User::APPROVAL_APPROVED => 'อนุมัติแล้ว',
                        \frontend\models\User::APPROVAL_REJECTED => 'ปฏิเสธ',
                    ][$model->approval_status] ?? $model->approval_status;
                },
            ],
            [
                'label' => 'จัดการคำขอ',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->approval_status !== \frontend\models\User::APPROVAL_PENDING) {
                        return '';
                    }

                    return Html::a('อนุมัติ', ['approve', 'id' => $model->userid], [
                        'class' => 'btn btn-xs btn-success',
                        'data' => ['method' => 'post', 'confirm' => 'อนุมัติบัญชีนี้หรือไม่?'],
                    ]) . ' ' . Html::a('ปฏิเสธ', ['reject', 'id' => $model->userid], [
                        'class' => 'btn btn-xs btn-danger',
                        'data' => ['method' => 'post', 'confirm' => 'ปฏิเสธคำขอบัญชีนี้หรือไม่?'],
                    ]);
                },
            ],

                        [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons'  => [
                    'delete' => function ($url, $model) use ($canManageAdminRoles, $adminUserIds) {
                        if (!$canManageAdminRoles
                            && ((string)$model->hoscode === '00064'
                                || in_array((int)$model->userid, $adminUserIds, true))) {
                            return '';
                        }
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->userid], [
                            'title' => 'ลบ',
                            'data'  => ['confirm' => 'ยืนยันการลบผู้ใช้? บัญชีจะถูกซ่อนและไม่สามารถเข้าสู่ระบบได้', 'method' => 'post'],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
<?php
$this->registerJs(<<<'JS'
$('.user-admin-role-checkbox').on('change', function () {
    var checkbox = $(this);
    var form = checkbox.closest('form');
    if (!window.confirm(form.data('confirm'))) {
        checkbox.prop('checked', !checkbox.prop('checked'));
        return;
    }

    form.trigger('submit');
});
JS);
?>
