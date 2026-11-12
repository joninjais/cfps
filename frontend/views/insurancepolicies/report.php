<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $hospitals array */
/* @var $cards array */
/* @var $data array */
/* @var $fiscalYear int */
/* @var $currentFiscalYear int */
/* @var $month int|null */
/* @var $start \DateTimeImmutable */
/* @var $end \DateTimeImmutable */

$this->title = 'รายงานสรุปการขายบัตรต่างด้าว';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลกรมธรรม์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$months = [
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม', 1 => 'มกราคม',
    2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม',
    6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
];

$this->registerCss('
.rp-page { font-family: "Sarabun","Prompt",Arial,sans-serif; padding: 16px; }
.rp-filter { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin-bottom: 16px; }
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
$sum = ['count' => 0, 'amount' => 0, 'unit' => 0, 'provincial' => 0, 'central' => 0, 'cards' => []];
?>
<div class="rp-page">
    <h3><?= Html::encode($this->title) ?></h3>

    <form method="get" action="<?= Html::encode(Url::to(['insurancepolicies/report'])) ?>" class="rp-filter">
        <div>
            <label for="rp-year">ปีงบประมาณ</label>
            <select id="rp-year" name="fiscal_year" class="form-control">
                <?php for ($y = $currentFiscalYear; $y >= $currentFiscalYear - 4; $y--): ?>
                    <option value="<?= $y ?>"<?= $y === $fiscalYear ? ' selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label for="rp-month">เดือน</label>
            <select id="rp-month" name="month" class="form-control">
                <option value="">ทั้งปีงบประมาณ</option>
                <?php foreach ($months as $m => $label): ?>
                    <option value="<?= $m ?>"<?= $m === $month ? ' selected' : '' ?>><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">แสดงรายงาน</button>
    </form>

    <p>วันที่ซื้อกรมธรรม์: <?= Html::encode($start->format('d/m/') . ((int)$start->format('Y') + 543)) ?>
        - <?= Html::encode($end->modify('-1 day')->format('d/m/') . ((int)$end->modify('-1 day')->format('Y') + 543)) ?></p>

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
                    <th colspan="3">การจัดสรรเงิน (บาท)</th>
                </tr>
                <tr>
                    <?php foreach ($cards as $c): ?>
                        <th><?= number_format((float)$c['price']) ?></th>
                    <?php endforeach; ?>
                    <?php if ($hasOther): ?><th>อื่นๆ</th><?php endif; ?>
                    <th>หน่วยบริการ (Claim)</th>
                    <th>สสจ. (PP+บริหาร)</th>
                    <th>ส่วนกลาง (HC+บริหาร)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$hospitals): ?>
                    <tr><td colspan="<?= count($colIds) + 8 ?>" class="text-center">ไม่พบข้อมูล</td></tr>
                <?php endif; ?>
                <?php foreach ($hospitals as $i => $h):
                    $d = $data[$h['hospcode']] ?? [];
                    $count = array_sum($d['cards'] ?? []);
                    $sum['count'] += $count;
                    foreach (['amount', 'unit', 'provincial', 'central'] as $k) {
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
                        <td class="rp-num"><?= $fmt($d['unit'] ?? 0) ?></td>
                        <td class="rp-num"><?= $fmt($d['provincial'] ?? 0) ?></td>
                        <td class="rp-num"><?= $fmt($d['central'] ?? 0) ?></td>
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
                    <td class="rp-num"><?= $fmt($sum['unit']) ?></td>
                    <td class="rp-num"><?= $fmt($sum['provincial']) ?></td>
                    <td class="rp-num"><?= $fmt($sum['central']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
