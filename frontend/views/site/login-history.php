<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $records frontend\models\LoginHistory[] */

$this->title = 'ประวัติการเข้าใช้งาน';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-history"></i> ประวัติการเข้าใช้งาน</h1>
        <ol class="breadcrumb">
            <li><?= Html::a('<i class="fa fa-home"></i> หน้าหลัก', ['/site/index']) ?></li>
            <li class="active">ประวัติการเข้าใช้งาน</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">50 รายการล่าสุด</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <?php if (empty($records)): ?>
                    <div class="callout callout-info">ยังไม่มีประวัติการเข้าใช้งาน</div>
                <?php else: ?>
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>กิจกรรม</th>
                            <th>วันที่/เวลาเข้าระบบ</th>
                            <th>วันที่/เวลาออกระบบ</th>
                            <th>IP Address</th>
                            <th>อุปกรณ์</th>
                            <th>สถานะ</th>
                            <th>เหตุผล</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $i => $r): ?>
                        <tr class="<?= $r->status === 'failed' ? 'danger' : '' ?>">
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?php if (($r->action ?? 'login') === 'logout'): ?>
                                    <span class="label label-default"><i class="fa fa-sign-out"></i> ออกระบบ</span>
                                <?php else: ?>
                                    <span class="label label-primary"><i class="fa fa-sign-in"></i> เข้าระบบ</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($r->getLoginAtFormatted()) ?></td>
                            <td><?= Html::encode($r->getLogoutAtFormatted()) ?></td>
                            <td><code><?= Html::encode($r->ip_address) ?></code></td>
                            <td>
                                <?php
                                $icons = [
                                    'desktop' => 'fa-desktop',
                                    'mobile'  => 'fa-mobile',
                                    'tablet'  => 'fa-tablet',
                                ];
                                $icon = $icons[$r->device_type] ?? 'fa-question';
                                $labels = [
                                    'desktop' => 'คอมพิวเตอร์',
                                    'mobile'  => 'มือถือ',
                                    'tablet'  => 'แท็บเล็ต',
                                ];
                                $label = $labels[$r->device_type] ?? $r->device_type;
                                ?>
                                <i class="fa <?= $icon ?>"></i> <?= Html::encode($label) ?>
                            </td>
                            <td>
                                <?php if ($r->status === 'success'): ?>
                                    <span class="label label-success">สำเร็จ</span>
                                <?php else: ?>
                                    <span class="label label-danger">ล้มเหลว</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($r->fail_reason)): ?>
                                    <span class="text-danger"><?= Html::encode($r->fail_reason) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="<?= Html::encode($r->user_agent) ?>">
                                <?= Html::encode(mb_strimwidth((string)$r->user_agent, 0, 70, '…')) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <div class="box-footer">
                <small class="text-muted">
                    <i class="fa fa-info-circle"></i>
                    หากพบรายการที่ผิดปกติ เช่น IP ที่ไม่รู้จัก หรือการเข้าระบบในเวลาที่ไม่ได้ใช้งาน กรุณาติดต่อผู้ดูแลระบบทันที
                </small>
            </div>
        </div>
    </section>
</div>
