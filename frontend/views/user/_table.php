<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var frontend\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$sort = $dataProvider->getSort();
$totalCount = $dataProvider->getTotalCount();
$count = count($models);

if ($pagination !== false && $totalCount > 0) {
    $begin = $pagination->getOffset() + 1;
    $end = $begin + $count - 1;
} else {
    $begin = $totalCount > 0 ? 1 : 0;
    $end = $count;
}

$isAllPage = ($pagination === false);
$currentParams = Yii::$app->request->getQueryParams();
$togglePageParams = $currentParams;
if ($isAllPage) {
    unset($togglePageParams['per-page']);
} else {
    $togglePageParams['per-page'] = 'all';
    unset($togglePageParams['page']);
}
$togglePageUrl = Url::to(array_merge(['index'], $togglePageParams));

$auth = Yii::$app->authManager;
$signedInIdentity = Yii::$app->user->identity ?? null;
$signedInId = $signedInIdentity ? (int)$signedInIdentity->getId() : 0;
$signedInHoscode = $signedInIdentity ? trim((string)($signedInIdentity->hoscode ?? '')) : '';
$canDelegateUserManager = ($signedInHoscode === '00064');

$roleLabels = [
    'provincialAdmin' => ['label' => 'ผู้ดูแลระดับจังหวัด (สสจ.)', 'class' => 'cfps-badge--indigo'],
    'hospitalStaff'   => ['label' => 'เจ้าหน้าที่ รพ.', 'class' => 'cfps-badge--blue'],
    'pcuStaff'        => ['label' => 'เจ้าหน้าที่ รพ.สต.', 'class' => 'cfps-badge--teal'],
];

$statusLabels = [
    'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'cfps-badge--success'],
    'pending'  => ['label' => 'รออนุมัติ', 'class' => 'cfps-badge--warning'],
    'rejected' => ['label' => 'ไม่อนุมัติ', 'class' => 'cfps-badge--danger'],
];

/**
 * สร้างลิงก์หัวตารางเรียงลำดับข้อมูล (Sort Link) โดยไม่พึ่ง GridView
 */
$renderSortHeader = function (string $attribute, string $label) use ($sort): string {
    if (!$sort || !$sort->hasAttribute($attribute)) {
        return Html::encode($label);
    }
    $orders = $sort->getAttributeOrders();
    $arrow = '';
    if (isset($orders[$attribute])) {
        $arrow = $orders[$attribute] === SORT_DESC ? ' ▼' : ' ▲';
    }
    return Html::a(
        Html::encode($label) . '<span class="cfps-sort-indicator">' . $arrow . '</span>',
        $sort->createUrl($attribute),
        ['class' => 'js-user-nav-link cfps-sort-link', 'data-sort' => $attribute]
    );
};
?>

<div id="user-ajax-payload">
    <!-- 1. แถบสรุปจำนวนข้อมูลและเครื่องมือ (Summary & Toolbar) -->
    <div id="user-table-topbar" class="cfps-user-card__topbar">
        <div class="cfps-user-summary">
            <?php if ($totalCount > 0): ?>
                แสดง <strong><?= number_format($begin) ?></strong> ถึง <strong><?= number_format($end) ?></strong>
                จากทั้งหมด <strong><?= number_format($totalCount) ?></strong> รายการ
            <?php else: ?>
                ไม่พบข้อมูลผู้ใช้งานตามเงื่อนไขที่ค้นหา
            <?php endif; ?>
        </div>

        <div class="cfps-user-toolbar">
            <button type="button" class="btn btn-default btn-sm js-reset-user-filters" title="ล้างช่องค้นหาทั้งหมด">
                <i class="fa fa-refresh"></i> ล้างตัวกรอง
            </button>
            <a href="<?= Html::encode($togglePageUrl) ?>" class="btn btn-default btn-sm js-user-nav-link">
                <i class="fa <?= $isAllPage ? 'fa-compress' : 'fa-expand' ?>"></i>
                <?= $isAllPage ? 'แบ่งหน้า (20)' : 'ทั้งหมด' ?>
            </a>
            <button type="button" class="btn btn-default btn-sm js-export-user-csv" title="ส่งออกรายการบนหน้าจอเป็นไฟล์ CSV (Excel)">
                <i class="fa fa-download"></i> ส่งออก CSV
            </button>
        </div>
    </div>

    <!-- 2. ตาราง HTML มาตรฐาน (แทนที่ GridView 100%) -->
    <div class="cfps-user-table-wrap">
        <table class="cfps-user-table" id="cfps-user-custom-table">
            <thead>
                <tr class="cfps-user-thead-titles">
                    <th class="col-num">#</th>
                    <th class="col-actions">จัดการ</th>
                    <th><?= $renderSortHeader('user', 'ชื่อผู้ใช้') ?></th>
                    <th><?= $renderSortHeader('fname', 'ชื่อ-นามสกุล') ?></th>
                    <th class="col-center">จัดการคำขอ</th>
                    <th><?= $renderSortHeader('hoscode', 'รหัสสถานพยาบาล') ?></th>
                    <th><?= $renderSortHeader('orgname', 'ชื่อหน่วยบริการ') ?></th>
                    <th>เบอร์โทรศัพท์</th>
                    <th><?= $renderSortHeader('email', 'อีเมล') ?></th>
                    <th>บทบาท/สิทธิ์</th>
                    <th class="col-center">สิทธิ์ userManager</th>
                    <th class="col-center"><?= $renderSortHeader('approval_status', 'สถานะอนุมัติ') ?></th>
                </tr>

                <!-- แถวตัวกรองค้นหาแบบ Real-time AJAX (Debounce) -->
                <tr class="cfps-user-thead-filters">
                    <td></td>
                    <td></td>
                    <td>
                        <?= Html::activeTextInput($searchModel, 'user', [
                            'class' => 'form-control input-sm js-user-filter-input',
                            'placeholder' => 'ค้นหาชื่อผู้ใช้...',
                        ]) ?>
                    </td>
                    <td>
                        <?php
                        $nameAttr = $searchModel->canGetProperty('fullname') ? 'fullname' : 'fname';
                        echo Html::activeTextInput($searchModel, $nameAttr, [
                            'class' => 'form-control input-sm js-user-filter-input',
                            'placeholder' => 'ชื่อ / นามสกุล',
                        ]);
                        ?>
                    </td>
                    <td></td>
                    <td>
                        <?= Html::activeTextInput($searchModel, 'hoscode', [
                            'class' => 'form-control input-sm js-user-filter-input',
                            'placeholder' => 'รหัส 5 หลัก',
                            'maxlength' => 5,
                        ]) ?>
                    </td>
                    <td>
                        <?php if ($searchModel->canGetProperty('orgname') && in_array('orgname', $searchModel->safeAttributes(), true)): ?>
                            <?= Html::activeTextInput($searchModel, 'orgname', [
                                'class' => 'form-control input-sm js-user-filter-input',
                                'placeholder' => 'ชื่อหน่วยบริการ',
                            ]) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array('phonenumber', $searchModel->safeAttributes(), true)): ?>
                            <?= Html::activeTextInput($searchModel, 'phonenumber', [
                                'class' => 'form-control input-sm js-user-filter-input',
                                'placeholder' => 'เบอร์โทร',
                            ]) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array('email', $searchModel->safeAttributes(), true)): ?>
                            <?= Html::activeTextInput($searchModel, 'email', [
                                'class' => 'form-control input-sm js-user-filter-input',
                                'placeholder' => 'อีเมล',
                            ]) ?>
                        <?php endif; ?>
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <?php if (in_array('approval_status', $searchModel->safeAttributes(), true)): ?>
                            <?= Html::activeDropDownList($searchModel, 'approval_status', [
                                '' => 'ทั้งหมด',
                                'pending' => 'รออนุมัติ',
                                'approved' => 'อนุมัติแล้ว',
                                'rejected' => 'ไม่อนุมัติ',
                            ], ['class' => 'form-control input-sm js-user-filter-input']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </thead>

            <tbody id="user-table-tbody">
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="12" class="cfps-user-empty">
                            <i class="fa fa-search"></i> ไม่พบรายการผู้ใช้งานที่ตรงกับเงื่อนไข
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($models as $idx => $model): ?>
                        <?php
                        $rowNo = $begin + $idx;
                        $uid = (int)$model->userid;
                        $fullName = trim(($model->fname ?? '') . ' ' . ($model->lname ?? ''));
                        $approvalStatus = (string)($model->approval_status ?? 'approved');
                        $statusMeta = $statusLabels[$approvalStatus] ?? ['label' => $approvalStatus, 'class' => 'cfps-badge--muted'];

                        // ดึงบทบาท RBAC ของผู้ใช้รายนี้
                        $assignedRoles = $auth ? array_keys($auth->getRolesByUser($uid)) : [];
                        $hasUserManager = in_array('userManager', $assignedRoles, true);
                        $primaryRoles = array_values(array_diff($assignedRoles, ['userManager']));
                        $primaryRoleKey = $primaryRoles[0] ?? ($model->requested_role ?: '');
                        $roleMeta = $roleLabels[$primaryRoleKey] ?? ['label' => ($primaryRoleKey ?: '-'), 'class' => 'cfps-badge--muted'];

                        $isSelf = ($uid === $signedInId);
                        $isTargetAdmin = ($primaryRoleKey === 'provincialAdmin' || trim((string)$model->hoscode) === '00064');
                        ?>
                        <tr data-user-id="<?= $uid ?>">
                            <td class="col-num" data-label="#"><?= $rowNo ?></td>

                            <td class="col-actions" data-label="จัดการ">
                                <div class="cfps-action-btns">
                                    <a href="<?= Url::to(['update', 'id' => $uid]) ?>"
                                       class="btn btn-xs btn-default js-user-modal"
                                       data-modal-title="แก้ไขผู้ใช้งาน: <?= Html::encode($model->user) ?>"
                                       title="แก้ไขข้อมูล">
                                        <i class="fa fa-pencil text-primary"></i>
                                    </a>

                                    <a href="<?= Url::to(['reset-password', 'id' => $uid]) ?>"
                                       class="btn btn-xs btn-default js-user-modal"
                                       data-modal-title="รีเซ็ตรหัสผ่าน: <?= Html::encode($model->user) ?>"
                                       title="รีเซ็ตรหัสผ่าน">
                                        <i class="fa fa-key text-warning"></i>
                                    </a>

                                    <?php if (!$isSelf): ?>
                                        <button type="button"
                                                class="btn btn-xs btn-default js-user-post-btn"
                                                data-url="<?= Url::to(['delete', 'id' => $uid]) ?>"
                                                data-confirm-msg="ยืนยันการลบบัญชีผู้ใช้ <?= Html::encode($model->user) ?> หรือไม่?"
                                                title="ลบผู้ใช้งาน">
                                            <i class="fa fa-trash text-danger"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td data-label="ชื่อผู้ใช้">
                                <strong class="cfps-username"><?= Html::encode($model->user) ?></strong>
                            </td>

                            <td data-label="ชื่อ-นามสกุล"><?= Html::encode($fullName !== '' ? $fullName : '-') ?></td>

                            <td class="col-center" data-label="จัดการคำขอ">
                                <?php if ($approvalStatus === 'pending'): ?>
                                    <div class="cfps-inline-btns">
                                        <button type="button"
                                                class="btn btn-xs btn-success js-user-post-btn"
                                                data-url="<?= Url::to(['approve', 'id' => $uid]) ?>"
                                                data-confirm-msg="อนุมัติบัญชีผู้ใช้ <?= Html::encode($model->user) ?> หรือไม่?">
                                            <i class="fa fa-check"></i> อนุมัติ
                                        </button>
                                        <button type="button"
                                                class="btn btn-xs btn-danger js-user-post-btn"
                                                data-url="<?= Url::to(['reject', 'id' => $uid]) ?>"
                                                data-confirm-msg="ปฏิเสธคำขอของ <?= Html::encode($model->user) ?> หรือไม่?">
                                            <i class="fa fa-times"></i> ปฏิเสธ
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td data-label="รหัสสถานพยาบาล">
                                <code><?= Html::encode($model->hoscode) ?></code>
                            </td>

                            <td data-label="ชื่อหน่วยบริการ"><?= Html::encode($model->orgname ?: '-') ?></td>

                            <td data-label="เบอร์โทรศัพท์"><?= Html::encode($model->phonenumber ?: '-') ?></td>

                            <td data-label="อีเมล"><?= Html::encode($model->email ?: '-') ?></td>

                            <td data-label="บทบาท/สิทธิ์">
                                <span class="cfps-badge <?= $roleMeta['class'] ?>">
                                    <?= Html::encode($roleMeta['label']) ?>
                                </span>
                            </td>

                            <td class="col-center" data-label="สิทธิ์ userManager">
                                <?php if ($isTargetAdmin): ?>
                                    <span class="cfps-badge cfps-badge--indigo">สิทธิ์เต็ม (สสจ.)</span>
                                <?php elseif ($canDelegateUserManager && $approvalStatus === 'approved'): ?>
                                    <button type="button"
                                            class="btn btn-xs <?= $hasUserManager ? 'btn-warning' : 'btn-default' ?> js-user-post-btn"
                                            data-url="<?= Url::to(['toggle-admin', 'id' => $uid]) ?>"
                                            data-post-params='<?= json_encode(['grant' => $hasUserManager ? 0 : 1]) ?>'
                                            data-confirm-msg="<?= $hasUserManager
                                                ? 'ถอนสิทธิ์ userManager ออกจาก ' . Html::encode($model->user) . ' หรือไม่?'
                                                : 'มอบสิทธิ์ userManager ให้กับ ' . Html::encode($model->user) . ' หรือไม่?' ?>">
                                        <i class="fa <?= $hasUserManager ? 'fa-user-times' : 'fa-user-plus' ?>"></i>
                                        <?= $hasUserManager ? 'ถอนสิทธิ์' : 'มอบสิทธิ์' ?>
                                    </button>
                                <?php else: ?>
                                    <span class="cfps-badge <?= $hasUserManager ? 'cfps-badge--teal' : 'cfps-badge--muted' ?>">
                                        <?= $hasUserManager ? 'มีสิทธิ์' : 'ไม่มี' ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="col-center" data-label="สถานะอนุมัติ">
                                <span class="cfps-badge <?= $statusMeta['class'] ?>">
                                    <?= Html::encode($statusMeta['label']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 3. แถบแบ่งหน้าด้านล่าง (Pagination Footer) -->
    <div id="user-table-footer" class="cfps-user-card__footer">
        <?php if ($pagination !== false && $pagination->getPageCount() > 1): ?>
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination pagination-sm cfps-user-pagination'],
                'linkOptions' => ['class' => 'js-user-nav-link'],
            ]) ?>
        <?php endif; ?>
    </div>
</div>
