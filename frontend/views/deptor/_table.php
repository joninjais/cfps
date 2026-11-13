<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var string $type */
/** @var \yii\data\ArrayDataProvider $provider */

$id = 'deptor-' . $type;
$models = array_values($provider->getModels());
$total = $provider->getTotalCount();
$pagination = $provider->getPagination();
$offset = $pagination === false ? 0 : $pagination->getOffset();
$sort = $provider->getSort();
$directions = $sort->getAttributeOrders();
$columns = [
    'hospcode' => 'รหัสสถานพยาบาล',
    'hosname' => 'ชื่อสถานพยาบาล',
    'total_visit' => 'จำนวนครั้ง',
    'claim' => 'ยอดเบิก (บาท)',
    'alltotal' => 'ยอดรวม (บาท)',
];
$toggleUrl = Url::current([
    $type . '-all' => $pagination === false ? '0' : '1',
    $type . '-page' => null,
    '#' => $id,
]);
$formatter = Yii::$app->formatter;
?>
<section class="deptor-report deptor-report-<?= Html::encode($type) ?>" id="<?= Html::encode($id) ?>" aria-labelledby="<?= Html::encode($id) ?>-title">
    <div class="deptor-report-header">
        <div>
            <h2 id="<?= Html::encode($id) ?>-title">
                <span class="deptor-report-badge"><?= Html::encode(strtoupper($type)) ?></span>
                ข้อมูลลูกหนี้ตามจ่าย
            </h2>
            <p class="deptor-report-count">
                <?php if ($total === 0): ?>
                    ไม่พบข้อมูล
                <?php elseif ($pagination === false): ?>
                    แสดงทั้งหมด <?= $formatter->asInteger($total) ?> รายการ
                <?php else: ?>
                    แสดง <?= $formatter->asInteger($offset + 1) ?>–<?= $formatter->asInteger($offset + count($models)) ?> จาก <?= $formatter->asInteger($total) ?> รายการ
                <?php endif; ?>
            </p>
        </div>
        <div class="deptor-report-actions">
            <?= Html::a('<i class="fa fa-refresh" aria-hidden="true"></i> รีเซ็ต', ['index'], [
                'class' => 'btn btn-default btn-sm',
                'aria-label' => 'รีเซ็ตช่วงวันที่และตาราง',
            ]) ?>
            <?= Html::a(
                $pagination === false ? '<i class="fa fa-compress" aria-hidden="true"></i> แบ่งหน้า' : '<i class="fa fa-expand" aria-hidden="true"></i> ดูทั้งหมด',
                $toggleUrl,
                ['class' => 'btn btn-default btn-sm', 'aria-label' => ($pagination === false ? 'แบ่งหน้า ' : 'ดูข้อมูลทั้งหมด ') . strtoupper($type)]
            ) ?>
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-download" aria-hidden="true"></i> ส่งออกแถวที่แสดง <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <?php foreach (['csv' => 'CSV', 'xlsx' => 'Excel', 'txt' => 'Text'] as $format => $label): ?>
                        <li><?= Html::a(Html::encode($label), Url::current(['export' => $type, 'format' => $format, '#' => null])) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="table-responsive deptor-table-wrap" role="region" aria-label="ตารางข้อมูล <?= Html::encode(strtoupper($type)) ?>" tabindex="0">
        <table class="table table-hover deptor-table">
            <caption class="sr-only">ข้อมูลลูกหนี้ตามจ่าย <?= Html::encode(strtoupper($type)) ?></caption>
            <thead>
                <tr>
                    <th scope="col" class="deptor-col-number">#</th>
                    <?php foreach ($columns as $attribute => $label): ?>
                        <?php $direction = $directions[$attribute] ?? null; ?>
                        <th scope="col" class="<?= in_array($attribute, ['total_visit', 'claim', 'alltotal'], true) ? 'deptor-col-amount' : '' ?>"<?= $direction === null ? '' : ' aria-sort="' . ($direction === SORT_ASC ? 'ascending' : 'descending') . '"' ?>>
                            <?= $sort->link($attribute, ['label' => $label]) ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$models): ?>
                    <tr><td colspan="6" class="deptor-empty">ไม่พบข้อมูลในช่วงวันที่เลือก</td></tr>
                <?php endif; ?>
                <?php foreach ($models as $index => $row): ?>
                    <tr>
                        <td class="deptor-col-number"><?= $formatter->asInteger($offset + $index + 1) ?></td>
                        <td><?= Html::encode($row['hospcode'] ?? '-') ?></td>
                        <td><?= Html::encode($row['hosname'] ?? '-') ?></td>
                        <td class="deptor-col-amount"><?= $row['total_visit'] === null ? '-' : $formatter->asDecimal($row['total_visit'], 0) ?></td>
                        <td class="deptor-col-amount"><?= $row['claim'] === null ? '-' : $formatter->asDecimal($row['claim'], 2) ?></td>
                        <td class="deptor-col-amount"><?= $row['alltotal'] === null ? '-' : $formatter->asDecimal($row['alltotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="deptor-report-footer">
        <span>ประมวลผล ณ <?= Html::encode(date('Y-m-d H:i:s')) ?></span>
        <?php if ($pagination !== false): ?>
            <?= LinkPager::widget(['pagination' => $pagination, 'options' => ['class' => 'pagination pagination-sm']]) ?>
        <?php endif; ?>
    </div>
</section>
