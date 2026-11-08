<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $grouped array สัญลักษณ์ HIS => SqlCode[] */
/* @var $isAdmin bool ผู้ใช้ hoscode 00064 */

$this->title = 'คำสั่ง SQL นำเข้าข้อมูล';
?>

<div class="sqlcode-index">

    <div class="page-header" style="border-bottom:2px solid #605ca8; margin-bottom:24px;">
        <h2 style="color:#605ca8;"><i class="fa fa-database"></i> คำสั่ง SQL นำเข้าข้อมูล</h2>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= Html::encode($msg) ?>
        </div>
    <?php endforeach; ?>

    <?php if ($isAdmin): ?>
    <div class="mb-4">
        <?= Html::a('<i class="fa fa-plus"></i> เพิ่มคำสั่ง SQL', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($grouped)): ?>
        <div class="alert alert-info"><i class="fa fa-info-circle"></i> ยังไม่มีข้อมูล</div>
    <?php else: ?>

    <?php $modalIndex = 0; foreach ($grouped as $his => $items): ?>
        <div class="panel panel-default" style="border-left:4px solid #605ca8; margin-bottom:24px;">
            <div class="panel-heading" style="background:#605ca8; color:#fff; font-size:16px; font-weight:bold;">
                <i class="fa fa-server"></i> <?= Html::encode($his) ?>
                <span class="badge" style="background:#fff; color:#605ca8; margin-left:8px;"><?= count($items) ?> คำสั่ง</span>
            </div>
            <div class="panel-body">
                <div class="row">
                <?php foreach ($items as $model): $modalIndex++; ?>
                    <div class="col-md-6 col-lg-4" style="margin-bottom:16px;">
                        <div class="panel <?= $model->type === 'OP' ? 'panel-info' : 'panel-warning' ?>" style="height:100%; margin-bottom:0;">
                            <div class="panel-heading">
                                <span class="label <?= $model->type === 'OP' ? 'label-info' : 'label-warning' ?>" style="font-size:13px;">
                                    <?= Html::encode($model->type) ?>
                                </span>
                                <strong style="margin-left:6px; font-size:14px;"><?= Html::encode($model->title) ?></strong>
                            </div>
                            <div class="panel-body" style="padding:10px 14px;">
                                <?php if ($model->note): ?>
                                    <p class="text-muted" style="font-size:12px; margin-bottom:8px;">
                                        <i class="fa fa-sticky-note-o"></i> <?= Html::encode($model->note) ?>
                                    </p>
                                <?php endif; ?>
                                <button type="button"
                                    class="btn btn-primary btn-sm btn-block"
                                    data-toggle="modal"
                                    data-target="#sqlModal<?= $modalIndex ?>">
                                    <i class="fa fa-eye"></i> ดู SQL
                                </button>
                                <?php if ($isAdmin): ?>
                                <div style="margin-top:6px;">
                                    <?= Html::a('<i class="fa fa-pencil"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-default btn-sm btn-block']) ?>
                                    <?= Html::a('<i class="fa fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger btn-sm btn-block',
                                        'style' => 'margin-top:4px;',
                                        'data-method' => 'post',
                                        'data-confirm' => 'ยืนยันการลบ?',
                                    ]) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal สำหรับ SQL #<?= $modalIndex ?> -->
                    <div class="modal fade" id="sqlModal<?= $modalIndex ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background:<?= $model->type === 'OP' ? '#31b0d5' : '#ec971f' ?>; color:#fff;">
                                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
                                    <h4 class="modal-title">
                                        <i class="fa fa-database"></i> <?= Html::encode($his) ?> —
                                        <span class="label label-default"><?= Html::encode($model->type) ?></span>
                                        <?= Html::encode($model->title) ?>
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <div style="text-align:right; margin-bottom:8px;">
                                        <button class="btn btn-success btn-sm sql-copy-btn"
                                            data-target="sqlText<?= $modalIndex ?>">
                                            <i class="fa fa-copy"></i> คัดลอก SQL
                                        </button>
                                    </div>
                                    <pre id="sqlText<?= $modalIndex ?>"
                                        style="max-height:460px; overflow:auto; background:#1e1e2e; color:#cdd6f4; border-radius:6px; padding:16px; font-size:12.5px; line-height:1.6;"><?= Html::encode($model->sql_command) ?></pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                        <i class="fa fa-times"></i> ปิด
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div><!-- /modal -->

                <?php endforeach; ?>
                </div><!-- /.row -->
            </div><!-- /.panel-body -->
        </div><!-- /.panel -->
    <?php endforeach; ?>

    <?php endif; ?>

</div><!-- /.sqlcode-index -->

<?php $this->registerCss("
.sqlcode-index pre { white-space: pre-wrap; word-break: break-all; }
"); ?>

<?php $this->registerJs(<<<JS
\$(document).on('click', '.sql-copy-btn', function () {
    var targetId = \$(this).data('target');
    var text = document.getElementById(targetId).innerText;
    var btn = this;
    function done() {
        \$(btn).html('<i class="fa fa-check"></i> คัดลอกแล้ว!').removeClass('btn-success').addClass('btn-default');
        setTimeout(function () {
            \$(btn).html('<i class="fa fa-copy"></i> คัดลอก SQL').removeClass('btn-default').addClass('btn-success');
        }, 2000);
    }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(done, done);
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        done();
    }
});
JS
); ?>
