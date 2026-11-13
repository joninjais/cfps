<?php
use yii\helpers\Html;
use yii\helpers\Url;
use karatae99\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $hospitals array */
/* @var $cards array */
/* @var $data array */
/* @var $start \DateTimeImmutable */
/* @var $end \DateTimeImmutable */

$this->title = 'รายงานสรุปการขายบัตรต่างด้าว';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลกรมธรรม์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$be = static fn(\DateTimeImmutable $d) => ((int)$d->format('Y') + 543) . $d->format('-m-d');
$date1 = $be($start);
$date2 = $be($end);
$fy = (int)$end->format('Y') + ((int)$end->format('n') >= 10 ? 1 : 0) + 543;
$nowFy = (int)date('Y') + ((int)date('n') >= 10 ? 1 : 0) + 543;
$monthLabels = [10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.', 1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
    4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.'];

$this->registerCss('
.rp-page { font-family: "Sarabun","Prompt",Arial,sans-serif; padding: 16px; }
.rp-filter { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin-bottom: 16px; }
.rp-fy { margin-bottom: 8px; }
.rp-date { width: 200px; }
.rp-scroll { overflow-x: auto; }
.rp-table th, .rp-table td { white-space: nowrap; vertical-align: middle !important; }
.rp-table thead th { background: #00796b; color: #fff; text-align: center; }
.rp-table tfoot td { font-weight: 700; background: #e0f2f1; }
.rp-num { text-align: right; }
');

$fmt = static fn($v) => number_format((float)$v, 2);
$cardIds = array_map('intval', array_column($cards, 'card_id'));
$hasOther = false;
foreach ($data as $d) {
    if (!empty($d['cards'][0])) {
        $hasOther = true;
        break;
    }
}
$colIds = $hasOther ? array_merge($cardIds, [0]) : $cardIds;
$allocKeys = ['unit', 'pp', 'man_ssj', 'hc_sp', 'man_sp'];
$sum = ['count' => 0, 'amount' => 0, 'unit' => 0, 'pp' => 0, 'man_ssj' => 0, 'hc_sp' => 0, 'man_sp' => 0, 'cards' => []];
?>
<div class="rp-page">
    <h3><?= Html::encode($this->title) ?></h3>

    <div class="rp-fy">
        <?php for ($y = $nowFy; $y > $nowFy - 6; $y--): ?>
            <?= Html::a('ปีงบ ' . $y, ['insurancepolicies/report', 'date1' => ($y - 1) . '-10-01', 'date2' => $y . '-09-30'],
                ['class' => 'btn btn-success' . ($y === $fy ? ' active' : '')]) ?>
        <?php endfor; ?>
    </div>
    <div class="rp-fy">
        <?php foreach ($monthLabels as $m => $label):
            $y = $m >= 10 ? $fy - 1 : $fy;
            $last = (int)(new \DateTimeImmutable(sprintf('%04d-%02d-01', $y - 543, $m)))->format('t');
            ?>
            <?= Html::a($label . ' ' . substr((string)$y, 2, 2),
                ['insurancepolicies/report', 'date1' => sprintf('%d-%02d-01', $y, $m), 'date2' => sprintf('%d-%02d-%02d', $y, $m, $last)],
                ['class' => 'btn btn-default']) ?>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?= Html::encode(Url::to(['insurancepolicies/report'])) ?>" class="rp-filter">
        <div class="rp-date"><?= DatePicker::widget([
            'name' => 'date1', 'value' => $date1, 'template' => '{addon}{input}', 'language' => 'th',
            'clientOptions' => ['autoclose' => true, 'format' => 'yyyy/mm/dd'],
        ]) ?></div>
        <div class="rp-date"><?= DatePicker::widget([
            'name' => 'date2', 'value' => $date2, 'template' => '{addon}{input}', 'language' => 'th',
            'clientOptions' => ['autoclose' => true, 'format' => 'yyyy/mm/dd'],
        ]) ?></div>
        <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> ค้นหา</button>
        <?= Html::a('<i class="fa fa-file-excel-o"></i> ส่งออก Excel',
            ['insurancepolicies/report', 'date1' => $date1, 'date2' => $date2, 'export' => 1],
            ['class' => 'btn btn-primary']) ?>
    </form>

    <p>วันที่เริ่มงวด: <?= Html::encode($start->format('d/m/') . ((int)$start->format('Y') + 543)) ?>
        - <?= Html::encode($end->format('d/m/') . ((int)$end->format('Y') + 543)) ?></p>

    <div class="rp-scroll">
        <table class="table table-bordered table-striped rp-table">
            <thead>
                <tr>
                    <th rowspan="2">ลำดับ</th>
                    <th rowspan="2">รหัส</th>
                    <th rowspan="2">หน่วยบริการ</th>
                    <th colspan="<?= count($colIds) ?>">จำนวนบัตรแยกตามประเภท (ใบ)</th>
                    <th rowspan="2">รวม (ใบ)</th>
                    <th rowspan="2">รวมเงิน (บาท)</th>
                    <th colspan="5">การจัดสรรเงิน (บาท)</th>
                </tr>
                <tr>
                    <?php foreach ($cards as $c): ?>
                        <th><?= number_format((float)$c['price']) ?></th>
                    <?php endforeach; ?>
                    <?php if ($hasOther): ?><th>อื่นๆ</th><?php endif; ?>
                    <th>หน่วยบริการ (Claim)</th>
                    <th>สสจ. PP</th>
                    <th>สสจ. บริหาร</th>
                    <th>สป. HC</th>
                    <th>สป. บริหาร</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$hospitals): ?>
                    <tr><td colspan="<?= count($colIds) + 10 ?>" class="text-center">ไม่พบข้อมูล</td></tr>
                <?php endif; ?>
                <?php foreach ($hospitals as $i => $h):
                    $d = $data[$h['hospcode']] ?? [];
                    $count = array_sum($d['cards'] ?? []);
                    $sum['count'] += $count;
                    $sum['amount'] += $d['amount'] ?? 0;
                    foreach ($allocKeys as $k) {
                        $sum[$k] += $d[$k] ?? 0;
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= Html::encode($h['hospcode']) ?></td>
                        <td><?= Html::encode($h['hosname']) ?></td>
                        <?php foreach ($colIds as $cid):
                            $n = $d['cards'][$cid] ?? 0;
                            $sum['cards'][$cid] = ($sum['cards'][$cid] ?? 0) + $n;
                            ?>
                            <td class="rp-num"><?= $n ? number_format($n) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="rp-num"><?= number_format($count) ?></td>
                        <td class="rp-num"><?= $fmt($d['amount'] ?? 0) ?></td>
                        <?php foreach ($allocKeys as $k): ?>
                            <td class="rp-num"><?= $fmt($d[$k] ?? 0) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-center">รวมทั้งหมด</td>
                    <?php foreach ($colIds as $cid): ?>
                        <td class="rp-num"><?= number_format($sum['cards'][$cid] ?? 0) ?></td>
                    <?php endforeach; ?>
                    <td class="rp-num"><?= number_format($sum['count']) ?></td>
                    <td class="rp-num"><?= $fmt($sum['amount']) ?></td>
                    <?php foreach ($allocKeys as $k): ?>
                        <td class="rp-num"><?= $fmt($sum[$k]) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
