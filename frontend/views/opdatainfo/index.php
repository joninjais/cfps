<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var frontend\models\OpdatainfoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ข้อมูลบริการผู้ป่วยนอกและส่งเสริมป้องกันโรค (OPDATA)';
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$sort = $dataProvider->getSort();
$totalCount = (int)$dataProvider->getTotalCount();
$offset = $pagination ? (int)$pagination->getOffset() : 0;

$queryParams = Yii::$app->request->queryParams;
$currentPerPage = trim((string)($queryParams['per-page'] ?? '10'));
if ($currentPerPage === '') {
    $currentPerPage = '10';
}
$isShowAll = ($pagination === false || $currentPerPage === 'all');

// สร้างลิงก์สลับ แสดงทั้งหมด <-> แบ่งหน้า โดยรักษาตัวกรองเดิมไว้ครบ
$showAllRoute = array_merge(['index'], $queryParams, ['per-page' => 'all']);
unset($showAllRoute['page']);

$pagedRoute = array_merge(['index'], $queryParams);
unset($pagedRoute['page'], $pagedRoute['per-page']);

// สร้างลิงก์กรองทุกสถานะ vs เฉพาะติด C โดยรักษาค่า per-page เดิมไว้
$allStatusRoute = ['index'];
$onlyCRoute = ['index', 'OpdatainfoSearch' => ['FundHolderID' => 'ccccc']];
if ($isShowAll) {
    $allStatusRoute['per-page'] = 'all';
    $onlyCRoute['per-page'] = 'all';
}

$identity = Yii::$app->user->identity ?? null;
$ownHoscode = trim((string)($searchModel->hospcode ?? $identity->hoscode ?? Yii::$app->session->get('hoscode') ?? ''));
$currentFundFilter = trim((string)($searchModel->FundHolderID ?? ''));

$controller = $this->context;
$reprocessAction = null;
foreach (['reprocess-ccccc', 'reprocess', 'update-fund', 'updatefund', 'check'] as $candidate) {
    $method = 'action' . str_replace(' ', '', ucwords(str_replace('-', ' ', $candidate)));
    if ($controller && method_exists($controller, $method)) {
        $reprocessAction = [$candidate];
        break;
    }
}

$sortLink = static function (string $attribute, string $label) use ($sort): string {
    if ($sort !== false && $sort !== null && isset($sort->attributes[$attribute])) {
        return $sort->link($attribute, ['label' => $label, 'class' => 'cfps-sort-link']);
    }
    return Html::encode($label);
};

$nationNames = [
    '048' => 'เมียนมา',
    '056' => 'ลาว',
    '057' => 'กัมพูชา',
    '045' => 'เวียดนาม',
    '099' => 'ไทย',
];

$this->registerCss(<<<'CSS'
.cfps-page-card {
    background: #ffffff;
    border: 1px solid #dbe2ef;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    margin-bottom: 20px;
}
.cfps-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.cfps-card-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a237e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cfps-hos-badge {
    background: #eef2ff;
    color: #1a237e;
    border: 1px solid #c7d2fe;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 700;
}
.cfps-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.cfps-filter-box {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.cfps-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 10px 12px;
}
.cfps-filter-group label {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 4px;
}
.cfps-filter-group .form-control {
    height: 34px;
    padding: 4px 10px;
    font-size: 0.92rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
.cfps-filter-actions {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}
.cfps-filter-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.cfps-summary-bar {
    padding: 10px 20px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    font-size: 0.92rem;
    color: #475569;
    border-bottom: 1px solid #eef2f7;
}
.cfps-table-wrap {
    width: 100%;
    overflow-x: auto;
}
.cfps-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.92rem;
}
.cfps-table th {
    background: #1a237e;
    color: #ffffff;
    font-weight: 600;
    padding: 10px 10px;
    white-space: nowrap;
    border-right: 1px solid rgba(255, 255, 255, 0.12);
    text-align: left;
}
.cfps-table th a.cfps-sort-link {
    color: #ffffff;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cfps-table th a.cfps-sort-link:hover {
    text-decoration: underline;
    color: #bae6fd;
}
.cfps-table td {
    padding: 9px 10px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    white-space: nowrap;
}
.cfps-table tbody tr:hover {
    background-color: #f1f5f9;
}
.cfps-table tbody tr.is-stuck-c {
    background-color: #fff8f8;
}
.cfps-table tbody tr.is-stuck-c:hover {
    background-color: #fee2e2;
}
.text-right { text-align: right !important; }
.text-center { text-align: center !important; }
.badge-fund-ok {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #e6fffa;
    color: #00695c;
    border: 1px solid #80cbc4;
    font-weight: 700;
    font-size: 0.82rem;
}
.badge-fund-c {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    font-weight: 700;
    font-size: 0.82rem;
}
.badge-sex-m { color: #1d4ed8; font-weight: 600; }
.badge-sex-f { color: #be185d; font-weight: 600; }
.cell-truncate {
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    vertical-align: bottom;
}
.cfps-pager-wrap {
    padding: 14px 20px;
    display: flex;
    justify-content: flex-end;
}
.cfps-empty-state {
    padding: 36px 16px;
    text-align: center;
    color: #64748b;
    font-size: 1rem;
}
CSS);
?>

<div class="opdata-index cfps-page-card">
    <div class="cfps-card-header">
        <h1 class="cfps-card-title">
            <span><?= Html::encode($this->title) ?></span>
            <?php if ($ownHoscode !== ''): ?>
                <span class="cfps-hos-badge">หน่วยบริการ: <?= Html::encode($ownHoscode) ?></span>
            <?php endif; ?>
        </h1>

        <div class="cfps-toolbar">
            <?= Html::a('ทุกสถานะสิทธิ', $allStatusRoute, [
                'class' => 'btn btn-sm ' . ($currentFundFilter === '' ? 'btn-primary' : 'btn-default'),
            ]) ?>
            <?= Html::a('เฉพาะเคสติด C (ccccc)', $onlyCRoute, [
                'class' => 'btn btn-sm ' . (strtolower($currentFundFilter) === 'ccccc' ? 'btn-danger' : 'btn-default'),
            ]) ?>

            <?php if ($isShowAll): ?>
                <?= Html::a('แบ่งหน้า (10 รายการ/หน้า)', $pagedRoute, [
                    'class' => 'btn btn-sm btn-primary',
                ]) ?>
            <?php else: ?>
                <?= Html::a('แสดงทั้งหมด (' . number_format($totalCount) . ' รายการ)', $showAllRoute, [
                    'class' => 'btn btn-sm btn-default',
                ]) ?>
            <?php endif; ?>

           
            <?= Html::a('ปรับปรุงข้อมูล (ปลด C)', ['index'], [
                'class' => 'btn btn-sm btn-warning',
                'data' => [
                    'method' => 'post',
                    'params' => ['chk' => 'checkdata'],
                    'confirm' => 'ยืนยันการประมวลผลจับคู่สิทธิบัตรใหม่เฉพาะรายการที่ติด C (ccccc) ของหน่วยบริการคุณหรือไม่?',
                ],
            ]) ?>
           
        </div>
    </div>

    <div class="cfps-filter-box">
        <?= Html::beginForm(['index'], 'get', ['class' => 'cfps-search-form']) ?>
        <div class="cfps-filter-grid">
            <div class="cfps-filter-group">
                <label>วันที่รับบริการ (ค.ศ.)</label>
                <?= Html::activeInput('date', $searchModel, 'dateserv', ['class' => 'form-control']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>เลขประจำตัว / รหัสบัตร (CID)</label>
                <?= Html::activeTextInput($searchModel, 'cid', ['class' => 'form-control', 'placeholder' => '13 หลัก / Passport / F0...']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>ชื่อ-สกุลผู้รับบริการ</label>
                <?= Html::activeTextInput($searchModel, 'name', ['class' => 'form-control', 'placeholder' => 'ค้นหาชื่อ...']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>HN</label>
                <?= Html::activeTextInput($searchModel, 'hn', ['class' => 'form-control', 'placeholder' => 'เลข HN']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>SEQ / VN</label>
                <?= Html::activeTextInput($searchModel, 'seq', ['class' => 'form-control', 'placeholder' => 'เลขลำดับบริการ']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>เพศ</label>
                <?= Html::activeDropDownList($searchModel, 'sex', [
                    '' => '-- ทั้งหมด --',
                    '1' => '1 - ชาย',
                    '2' => '2 - หญิง',
                ], ['class' => 'form-control']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>อายุ (ปี)</label>
                <?= Html::activeInput('number', $searchModel, 'p_age', ['class' => 'form-control', 'min' => 0, 'placeholder' => 'ระบุอายุ']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>สัญชาติ</label>
                <?= Html::activeTextInput($searchModel, 'nation', ['class' => 'form-control', 'placeholder' => 'เช่น 048, 056, 057']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>รหัสโรค (ICD-10)</label>
                <?= Html::activeTextInput($searchModel, 'icd10', ['class' => 'form-control', 'placeholder' => 'เช่น Z480, I10']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>เลขที่ Refer</label>
                <?= Html::activeTextInput($searchModel, 'refer_number', ['class' => 'form-control', 'placeholder' => 'เลขใบส่งตัว']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>รหัสหน่วยบริการที่ส่งต่อ</label>
                <?= Html::activeTextInput($searchModel, 'refertohospcode', ['class' => 'form-control', 'placeholder' => 'รหัส 5 หลัก']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>รพ.เจ้าของบัตร (FundHolderID)</label>
                <?= Html::activeTextInput($searchModel, 'FundHolderID', ['class' => 'form-control', 'placeholder' => 'รหัส รพ. หรือ ccccc']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>สิทธิจาก HIS (fund)</label>
                <?= Html::activeTextInput($searchModel, 'fund', ['class' => 'form-control', 'placeholder' => 'รหัสกองทุน HIS']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>ค่ารักษารวม (alltotal)</label>
                <?= Html::activeTextInput($searchModel, 'alltotal', ['class' => 'form-control', 'placeholder' => 'จำนวนเงิน']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>ชื่อไฟล์นำเข้า</label>
                <?= Html::activeTextInput($searchModel, 'exsfile', ['class' => 'form-control', 'placeholder' => 'ชื่อไฟล์ Excel']) ?>
            </div>
            <div class="cfps-filter-group">
                <label>จำนวนแสดงต่อหน้า</label>
                <?= Html::dropDownList('per-page', $currentPerPage, [
                    '10' => '10 รายการ/หน้า',
                    '25' => '25 รายการ/หน้า',
                    '50' => '50 รายการ/หน้า',
                    '100' => '100 รายการ/หน้า',
                    'all' => 'แสดงทั้งหมดในหน้าเดียว',
                ], ['class' => 'form-control']) ?>
            </div>
        </div>

        <div class="cfps-filter-actions">
            <div class="cfps-filter-btns">
                <?= Html::submitButton('ค้นหาข้อมูล', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
            <div class="text-muted" style="font-size: 0.85rem;">
                แสดงเฉพาะข้อมูลบริการของรหัสหน่วยบริการที่ล็อกอินอยู่เท่านั้น
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>

    <div class="cfps-summary-bar">
        <div>
            <?php if ($totalCount > 0): ?>
                แสดงรายการที่ <strong><?= number_format($offset + 1) ?></strong>
                ถึง <strong><?= number_format($offset + count($models)) ?></strong>
                จากทั้งหมด <strong><?= number_format($totalCount) ?></strong> รายการ
                <?= $isShowAll ? ' <span class="badge-fund-ok">โหมดแสดงทั้งหมด</span>' : '' ?>
            <?php else: ?>
                ไม่พบรายการข้อมูลตามเงื่อนไขที่ค้นหา
            <?php endif; ?>
        </div>
        <div>คลิกที่ชื่อคอลัมน์เพื่อเรียงลำดับข้อมูล</div>
    </div>

    <div class="cfps-table-wrap">
        <table id="opdata-custom-table" class="cfps-table">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>หน่วยบริการ</th>
                    <th><?= $sortLink('dateserv', 'วันที่รับบริการ') ?></th>
                    <th><?= $sortLink('hn', 'HN') ?></th>
                    <th><?= $sortLink('seq', 'SEQ') ?></th>
                    <th><?= $sortLink('cid', 'เลขประจำตัว (CID)') ?></th>
                    <th><?= $sortLink('name', 'ชื่อ-สกุล') ?></th>
                    <th class="text-center"><?= $sortLink('sex', 'เพศ') ?></th>
                    <th class="text-center"><?= $sortLink('p_age', 'อายุ (ปี)') ?></th>
                    <th class="text-center"><?= $sortLink('nation', 'สัญชาติ') ?></th>
                    <th><?= $sortLink('icd10', 'ICD-10') ?></th>
                    <?php for ($m = 1; $m <= 16; $m++): ?>
                        <th class="text-right"><?= $sortLink('m' . $m, 'm' . $m) ?></th>
                    <?php endfor; ?>
                    <th class="text-right"><?= $sortLink('alltotal', 'ค่ารักษารวม') ?></th>
                    <th><?= $sortLink('refer_number', 'เลขที่ Refer') ?></th>
                    <th class="text-center"><?= $sortLink('refertohospcode', 'ส่งต่อ รพ.') ?></th>
                    <th class="text-center"><?= $sortLink('fund', 'สิทธิ HIS') ?></th>
                    <th class="text-center"><?= $sortLink('FundHolderID', 'เจ้าของบัตร') ?></th>
                    <th><?= $sortLink('exsfile', 'ไฟล์นำเข้า') ?></th>
                    <th class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="35" class="cfps-empty-state">
                            ไม่พบข้อมูลผู้ป่วยนอกของหน่วยบริการตามเงื่อนไขที่ระบุ
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($models as $idx => $model): ?>
                        <?php
                        $pk = $model->opid ?? null;
                        $fundHolder = trim((string)($model->FundHolderID ?? ''));
                        $isStuckC = ($fundHolder === '' || strtolower($fundHolder) === 'ccccc');

                        $sexRaw = trim((string)($model->sex ?? ''));
                        if ($sexRaw === '1') {
                            $sexDisplay = '<span class="badge-sex-m">ชาย</span>';
                        } elseif ($sexRaw === '2') {
                            $sexDisplay = '<span class="badge-sex-f">หญิง</span>';
                        } else {
                            $sexDisplay = $sexRaw !== '' ? Html::encode($sexRaw) : '-';
                        }

                        $ageRaw = $model->p_age ?? null;
                        $ageDisplay = ($ageRaw !== null && trim((string)$ageRaw) !== '') ? Html::encode((string)$ageRaw) : '-';

                        $nationCode = trim((string)($model->nation ?? ''));
                        $nationTitle = $nationNames[$nationCode] ?? '';

                        $allTotalVal = $model->alltotal ?? 0;
                        $allTotalText = is_numeric($allTotalVal) ? number_format((float)$allTotalVal, 2) : Html::encode((string)$allTotalVal);

                        $referNo = trim((string)($model->refer_number ?? ''));
                        $referTo = trim((string)($model->refertohospcode ?? ''));
                        $exsFile = trim((string)($model->exsfile ?? ''));
                        ?>
                        <tr class="<?= $isStuckC ? 'is-stuck-c' : '' ?>">
                            <td class="text-center"><?= number_format($offset + $idx + 1) ?></td>
                            <td><?= Html::encode((string)($model->hospcode ?? '')) ?></td>
                            <td><?= Html::encode((string)($model->dateserv ?? '')) ?></td>
                            <td><?= Html::encode((string)($model->hn ?? '')) ?></td>
                            <td><?= Html::encode((string)($model->seq ?? '')) ?></td>
                            <td><?= Html::encode((string)($model->cid ?? '')) ?></td>
                            <td>
                                <span class="cell-truncate" title="<?= Html::encode((string)($model->name ?? '')) ?>">
                                    <?= Html::encode((string)($model->name ?? '')) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $sexDisplay ?></td>
                            <td class="text-center"><?= $ageDisplay ?></td>
                            <td class="text-center" title="<?= Html::encode($nationTitle) ?>">
                                <?= $nationCode !== '' ? Html::encode($nationCode) : '-' ?>
                            </td>
                            <td>
                                <span class="cell-truncate" title="<?= Html::encode((string)($model->icd10 ?? '')) ?>">
                                    <?= Html::encode((string)($model->icd10 ?? '')) ?>
                                </span>
                            </td>
                            <?php for ($m = 1; $m <= 16; $m++): ?>
                                <?php
                                $mAttr = 'm' . $m;
                                $mVal = $model->$mAttr ?? 0;
                                ?>
                                <td class="text-right">
                                    <?= is_numeric($mVal) ? number_format((float)$mVal, 2) : Html::encode((string)$mVal) ?>
                                </td>
                            <?php endfor; ?>
                            <td class="text-right"><strong><?= $allTotalText ?></strong></td>
                            <td><?= $referNo !== '' ? Html::encode($referNo) : '-' ?></td>
                            <td class="text-center"><?= $referTo !== '' ? Html::encode($referTo) : '-' ?></td>
                            <td class="text-center"><?= Html::encode((string)($model->fund ?? '-')) ?></td>
                            <td class="text-center">
                                <?php if ($isStuckC): ?>
                                    <span class="badge-fund-c"><?= Html::encode($fundHolder !== '' ? $fundHolder : 'ccccc') ?></span>
                                <?php else: ?>
                                    <span class="badge-fund-ok"><?= Html::encode($fundHolder) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="cell-truncate" title="<?= Html::encode($exsFile) ?>">
                                    <?= $exsFile !== '' ? Html::encode($exsFile) : '-' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($pk !== null): ?>
                                    <?= Html::a('ลบ', ['delete', 'id' => $pk], [
                                        'class' => 'btn btn-xs btn-danger',
                                        'data' => [
                                            'confirm' => 'ยืนยันการลบข้อมูลรายการนี้หรือไม่?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination && $pagination->getPageCount() > 1): ?>
        <div class="cfps-pager-wrap">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'prevPageLabel' => '« ก่อนหน้า',
                'nextPageLabel' => 'ถัดไป »',
                'maxButtonCount' => 7,
            ]) ?>
        </div>
    <?php endif; ?>
</div>
