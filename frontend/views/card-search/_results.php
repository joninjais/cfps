<?php

use yii\helpers\Html;

/** @var string $rawCid */
/** @var string $cleanCid */
/** @var array $tokens */
/** @var bool $searched */
/** @var string $errorMsg */
/** @var array $rows */
/** @var array $summary */

$rawCid = $rawCid ?? '';
$cleanCid = $cleanCid ?? '';
$tokens = $tokens ?? [];
$searched = $searched ?? false;
$errorMsg = $errorMsg ?? '';
$rows = $rows ?? [];
$summary = $summary ?? [];
$total = (int)($summary['total'] ?? count($rows));
?>

<?php if (!$searched): ?>
    <div class="box box-default">
        <div class="box-body text-center cfps-empty-state">
            <i class="fa fa-search fa-3x text-muted"></i>
            <h4>ระบุเลข 13 หลัก, เลข Passport (เช่น CC...), รหัส F04... หรือเลข RA... ด้านบนเพื่อค้นหา</h4>
            <p class="text-muted">สามารถตรวจสอบข้อมูลบัตรประกันสุขภาพข้ามหน่วยบริการได้ทุกแห่งในจังหวัดพังงา</p>
        </div>
    </div>
<?php elseif ($errorMsg !== ''): ?>
    <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i> <?= Html::encode($errorMsg) ?>
    </div>
<?php elseif ($total === 0): ?>
    <div class="box box-danger">
        <div class="box-body text-center cfps-empty-state">
            <i class="fa fa-times-circle fa-3x text-danger"></i>
            <h4 class="text-danger">ไม่พบข้อมูลบัตรประกันสุขภาพสำหรับคำค้นหา: <?= Html::encode($rawCid) ?></h4>
            <p class="text-muted">กรุณาตรวจสอบตัวเลข/ตัวอักษรอีกครั้ง หรือลองค้นหาด้วยเลขพาสปอร์ต / เลข F04... / เลข RA...</p>
        </div>
    </div>
<?php else: ?>
    <?php
    $hasActive = (bool)($summary['hasActive'] ?? false);
    $latestStatus = (string)($summary['latestStatus'] ?? 'none');
    $bannerClass = match ($latestStatus) {
        'active' => 'is-active',
        'upcoming' => 'is-upcoming',
        default => 'is-expired',
    };
    if ($hasActive) {
        $bannerClass = 'is-active';
    }

    $patientName = (string)($summary['patientName'] ?? '-');
    $nationality = (string)($summary['nationality'] ?? '-');
    $latestHosp = (string)($summary['latestHosp'] ?? '-');
    $latestStartThai = (string)($summary['latestStartThai'] ?? '-');
    $latestEndThai = (string)($summary['latestEndThai'] ?? '-');
    $latestStatusLabel = (string)($summary['latestStatusLabel'] ?? '-');
    $latestDaysNote = (string)($summary['latestDaysNote'] ?? '');
    $latestTimeRegis = (string)($summary['latestTimeRegis'] ?? '-');
    $matchedCidTokens = $summary['matchedCidTokens'] ?? [];
    ?>

    <!-- การ์ดสรุปสถานะสิทธิล่าสุด -->
    <div class="cfps-status-banner <?= Html::encode($bannerClass) ?>">
        <div class="cfps-status-main">
            <div class="cfps-status-badge-lg">
                <?php if ($hasActive): ?>
                    <i class="fa fa-check-circle"></i> <?= Html::encode($latestStatusLabel) ?>
                <?php elseif ($latestStatus === 'upcoming'): ?>
                    <i class="fa fa-clock-o"></i> <?= Html::encode($latestStatusLabel) ?>
                <?php else: ?>
                    <i class="fa fa-exclamation-circle"></i> <?= Html::encode($latestStatusLabel) ?>
                <?php endif; ?>
                <?php if ($latestDaysNote !== ''): ?>
                    <span class="cfps-days-pill"><?= Html::encode($latestDaysNote) ?></span>
                <?php endif; ?>
            </div>

            <div class="cfps-patient-headline">
                <strong>ชื่อผู้ประกันตน:</strong> <?= Html::encode($patientName) ?>
                <span class="cfps-divider">|</span>
                <strong>สัญชาติ:</strong> <?= Html::encode($nationality) ?>
            </div>

            <?php if (!empty($matchedCidTokens)): ?>
                <div class="cfps-cid-tags-row">
                    <?php foreach ($matchedCidTokens as $tok): ?>
                        <?php
                        $tokVal = (string)($tok['value'] ?? '');
                        $tokType = (string)($tok['type'] ?? '');
                        $isMatch = (bool)($tok['matched'] ?? false);
                        ?>
                        <span class="cfps-id-chip <?= $isMatch ? 'is-matched' : '' ?>">
                            <small class="cfps-id-chip-type"><?= Html::encode($tokType) ?>:</small>
                            <strong><?= Html::encode($tokVal) ?></strong>
                            <button type="button" class="btn-chip-copy js-copy-cid" data-cid="<?= Html::encode($tokVal) ?>" title="คัดลอกรหัสนี้">
                                <i class="fa fa-copy"></i>
                            </button>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="cfps-status-meta">
            <div><strong>หน่วยบริการเจ้าของบัตร:</strong> <?= Html::encode($latestHosp) ?></div>
            <div><strong>ช่วงคุ้มครอง:</strong> <?= Html::encode($latestStartThai) ?> ถึง <?= Html::encode($latestEndThai) ?></div>
            <div><strong>ประเภท/มติ:</strong> <?= Html::encode($latestTimeRegis) ?></div>
            <div><strong>รายการที่พบ:</strong> <?= number_format($total) ?><?= $total >= 100 ? '+' : '' ?> รายการ</div>
        </div>
    </div>

    <!-- ตารางแสดงรายการบัตรประกันสุขภาพ -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list-alt"></i> รายการบัตรประกันสุขภาพที่ค้นพบ (<?= number_format($total) ?> รายการ)
            </h3>
        </div>
        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover cfps-card-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 55px;">ลำดับ</th>
                            <th>รหัสประจำตัวในระบบ (CID)</th>
                            <th>ชื่อผู้ประกันตน</th>
                            <th class="text-center">สัญชาติ</th>
                            <th>หน่วยบริการ (FundHolder)</th>
                            <th>ประเภทบัตร / มติ ครม.</th>
                            <th class="text-right">ราคาบัตร</th>
                            <th class="text-center">วันเริ่ม - สิ้นสุดคุ้มครอง</th>
                            <th class="text-center">สถานะสิทธิ</th>
                            <th class="text-center" style="width: 95px;">ดูเต็ม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $index => $item): ?>
                            <?php
                            $stCode = (string)($item['statusCode'] ?? 'unknown');
                            $badgeCss = match ($stCode) {
                                'active' => 'cfps-badge-active',
                                'expired' => 'cfps-badge-expired',
                                'upcoming' => 'cfps-badge-upcoming',
                                default => 'cfps-badge-unknown',
                            };
                            $modalId = 'card-detail-modal-' . (int)$index;
                            $cidParts = $item['cidParts'] ?? [];
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <?php if (!empty($cidParts)): ?>
                                        <div class="cfps-table-cid-list">
                                            <?php foreach ($cidParts as $cp): ?>
                                                <?php
                                                $cpVal = (string)($cp['value'] ?? '');
                                                $cpType = (string)($cp['type'] ?? '');
                                                $cpMatch = (bool)($cp['matched'] ?? false);
                                                ?>
                                                <div class="cfps-table-cid-item <?= $cpMatch ? 'is-matched' : '' ?>">
                                                    <span class="cfps-cid-type-tag"><?= Html::encode($cpType) ?></span>
                                                    <code class="cfps-cid-code"><?= Html::encode($cpVal) ?></code>
                                                    <button type="button" class="btn btn-default btn-xs js-copy-cid" data-cid="<?= Html::encode($cpVal) ?>" title="คัดลอก">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <?= Html::encode((string)($item['storedCid'] ?? '-')) ?>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= Html::encode((string)($item['patientName'] ?? '-')) ?></strong></td>
                                <td class="text-center cfps-nowrap"><?= Html::encode((string)($item['nationality'] ?? '-')) ?></td>
                                <td><?= Html::encode((string)($item['hospDisplay'] ?? '-')) ?></td>
                                <td>
                                    <div><?= Html::encode((string)($item['timeRegis'] ?? '-')) ?></div>
                                    <?php if (($item['regisDateThai'] ?? '-') !== '-'): ?>
                                        <small class="text-muted">ขึ้นทะเบียน: <?= Html::encode((string)$item['regisDateThai']) ?> (<?= Html::encode((string)($item['exsFile'] ?? '-')) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right cfps-nowrap"><?= Html::encode((string)($item['cardPrice'] ?? '-')) ?></td>
                                <td class="text-center cfps-nowrap">
                                    <div><?= Html::encode((string)($item['startDateThai'] ?? '-')) ?></div>
                                    <div class="text-muted" style="font-size: 12px;">ถึง <?= Html::encode((string)($item['endDateThai'] ?? '-')) ?></div>
                                </td>
                                <td class="text-center cfps-nowrap">
                                    <span class="cfps-badge <?= Html::encode($badgeCss) ?>">
                                        <?= Html::encode((string)($item['statusLabel'] ?? '-')) ?>
                                    </span>
                                    <?php if (($item['daysNote'] ?? '') !== ''): ?>
                                        <div class="cfps-days-sub"><?= Html::encode((string)$item['daysNote']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-info btn-xs"
                                        data-toggle="modal"
                                        data-target="#<?= Html::encode($modalId) ?>"
                                    >
                                        <i class="fa fa-eye"></i> รายละเอียด
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal แสดงข้อมูลทุกคอลัมน์จริงใน fwfdatabase -->
    <?php foreach ($rows as $index => $item): ?>
        <?php
        $modalId = 'card-detail-modal-' . (int)$index;
        $details = $item['details'] ?? [];
        ?>
        <div class="modal fade" id="<?= Html::encode($modalId) ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header cfps-modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">
                            <i class="fa fa-id-card"></i> ข้อมูลทะเบียนบัตรประกันสุขภาพ (#<?= Html::encode((string)($item['fwfid'] ?? '')) ?>)
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <tbody>
                                    <?php foreach ($details as $d): ?>
                                        <tr>
                                            <th style="width: 38%; background: #f8fafc;">
                                                <?= Html::encode((string)($d['label'] ?? '')) ?>
                                            </th>
                                            <td style="word-break: break-all;"><?= Html::encode((string)($d['value'] ?? '-')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิดหน้าต่าง</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
