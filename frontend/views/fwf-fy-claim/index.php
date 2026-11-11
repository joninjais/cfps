<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use frontend\models\User;

$this->title = 'กองทุน Claim ตามปีงบประมาณ';
$this->params['breadcrumbs'][] = $this->title;

$session = Yii::$app->session;
$checklogin = User::findOne(['user' => $session['user']]);
if (!$checklogin) {
    echo "<script>window.location='" . Url::to(['/site/login']) . "';</script>";
}
?>

<div class="row">
<div class="col-md-12">

<!-- ── Header box ─────────────────────────────────────── -->
<div class="box box-success box-solid">
  <div class="box-header with-border" style="background:linear-gradient(135deg,#145a32,#27ae60)!important;">
    <h3 class="box-title" style="color:#fff;">
      <i class="fa fa-fw fa-calendar-o"></i> <?= Html::encode($this->title) ?>
    </h3>
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body">

    <!-- ── Alert message ─────────────────────────────── -->
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= strpos($message, 'ผิดพลาด') !== false ? 'danger' : 'success' ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-<?= strpos($message, 'ผิดพลาด') !== false ? 'times-circle' : 'check-circle' ?>"></i>
        <?= Html::encode($message) ?>
      </div>
    <?php endif; ?>

    <!-- ── Stat bar ───────────────────────────────────── -->
    <div class="row" style="margin-bottom:14px;">
      <div class="col-md-3 col-sm-6">
        
        <div class="info-box" style="min-height:60px;">
          <span class="info-box-icon bg-green"><i class="fa fa-table"></i></span>          
          <div class="info-box-content">
            <span class="info-box-text">แถวในตาราง</span>
            <span class="info-box-number"><?= number_format($totalRows) ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-9 col-sm-6" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding-top:8px;">
        <!-- Clear button (POST via form) -->
        <?php if (Yii::$app->user->can('manageFwfData')): ?>
        <form method="post" action="<?= Url::to(['/fwf-fy-claim/clear']) ?>" style="display:inline;"
              onsubmit="return confirm('⚠️ ยืนยันการลบข้อมูลทั้งหมด <?= number_format($totalRows) ?> แถว? ไม่สามารถกู้คืนได้');">
          <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
          <button type="submit" class="btn btn-danger" style="font-size:1rem;">
            <i class="fa fa-fw fa-trash"></i> 1.เคลียข้อมูลทั้งหมด
          </button>
        </form>
        <!-- Import button -->
        <?= Html::beginForm(['/fwf-fy-claim/import'], 'post', ['style' => 'display:inline;']) ?>
        <?= Html::submitButton(
            '<i class="fa fa-fw fa-download"></i> 2.นำเข้าข้อมูล (Import)',
            [
                'class'   => 'btn btn-primary',
                'title'   => 'นำเข้าข้อมูลจาก fwfdatabase คำนวณ claim แยกรายปีงบประมาณ (ไม่นำเข้าซ้ำ)',
                'data-confirm' => 'ยืนยันการนำเข้าข้อมูล? อาจใช้เวลาสักครู่',
                'style'   => 'font-size:1rem;',
            ]
        ) ?>
        <?= Html::endForm() ?>
        <?php endif; ?>

        <!-- Export button -->
        <?= Html::a(
            '<i class="fa fa-fw fa-file-excel-o"></i> 3.ส่งออก Excel (.xlsx)',
            array_merge(['/fwf-fy-claim/export'], $fyFilter ? ['fy' => $fyFilter] : [], $hosFilter !== '' ? ['hos' => $hosFilter] : []),
            [
                'class'  => 'btn btn-success',
                'style'  => 'font-size:1rem;',
                'title'  => 'ดาวน์โหลด Excel ตามตัวกรองที่เลือก (2 sheet: ข้อมูล Record + สรุปรายหน่วยบริการ)',
                'target' => '_blank',
            ]
        ) ?>

        <?php if (!empty($lastImport)): ?>
        <span style="font-size:1rem;color:#555;">
          <i class="fa fa-fw fa-clock-o"></i> นำเข้าข้อมูลล่าสุด: <strong><?= Html::encode(date('d/m/', strtotime($lastImport)) . (date('Y', strtotime($lastImport)) + 543) . date(' H:i:s', strtotime($lastImport))) ?></strong>
        </span>
        <?php endif; ?>

      </div>
    </div>

    <!-- ── Filter bar ─────────────────────────────────── -->
    <form method="get" action="<?= Url::to(['/fwf-fy-claim/index']) ?>" class="form-inline" style="margin-bottom:14px;gap:8px;display:flex;flex-wrap:wrap;">
      <div class="form-group">
        <label style="margin-right:4px;">ปีงบประมาณ</label>
        <select name="fy" class="form-control" style="min-width:110px;">
          <option value="0">-- ทั้งหมด --</option>
          <?php foreach ($fyList as $fy): ?>
            <option value="<?= $fy ?>" <?= $fyFilter == $fy ? 'selected' : '' ?>><?= $fy ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label style="margin-right:4px;">โรงพยาบาล</label>
        <select name="hos" class="form-control" style="min-width:160px;">
          <option value="">-- ทั้งหมด --</option>
          <?php foreach ($hosList as $h): ?>
            <option value="<?= Html::encode($h['FundHolderID']) ?>"
              <?= $hosFilter === $h['FundHolderID'] ? 'selected' : '' ?>>
              <?= Html::encode($h['FundHolderID']) ?> - <?= Html::encode($h['hosname'] ?? '-') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-default"><i class="fa fa-filter"></i> กรอง</button>
      <?= Html::a('<i class="fa fa-times"></i> ล้าง', ['/fwf-fy-claim/index'], ['class' => 'btn btn-default']) ?>
    </form>

    <!-- ── Summary table ──────────────────────────────── -->
    <?php if (!empty($summaryRows)): ?>
    <div class="box box-solid" style="margin-bottom:18px;">
      <div class="box-header with-border" style="background:linear-gradient(135deg,#1a237e,#283593);">
        <h4 class="box-title" style="color:#fff;">
          <i class="fa fa-fw fa-bar-chart"></i> สรุปข้อมูล Claim แยกรายหน่วยบริการ
          <?php if ($fyFilter): ?><span class="label label-warning" style="font-size:0.9rem;">ปีงบ <?= $fyFilter ?></span><?php endif; ?>
          <?php if ($hosFilter !== ''): ?><span class="label label-info" style="font-size:0.9rem;"><?= Html::encode($hosFilter) ?></span><?php endif; ?>
        </h4>
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
      <div class="box-body" style="padding:10px;">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" style="font-size:0.95rem;margin-bottom:0;">
            <thead>
              <tr style="background:#37474f;color:#fff;">
                <th style="white-space:nowrap;">#</th>
                <th style="white-space:nowrap;">รหัส รพ.</th>
                <th>ชื่อ รพ.</th>
                <th style="white-space:nowrap;text-align:center;background:#1a6b3c;">ปีงบ</th>
                <th style="white-space:nowrap;text-align:right;">จำนวน Record</th>
                <th style="white-space:nowrap;text-align:right;">วันในปีงบ (รวม)</th>
                <th style="white-space:nowrap;text-align:right;background:#1565c0;color:#fff;">บริหารจัดการ</th>
                <th style="white-space:nowrap;text-align:right;background:#2e7d32;color:#fff;">PP</th>
                <th style="white-space:nowrap;text-align:right;background:#880e4f;color:#fff;">Claim ในปีงบ</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $gtClaimFY = 0; $gtRecords = 0; $gtManage = 0; $gtPP = 0;
              foreach ($summaryRows as $si => $sr):
                  $gtClaimFY += (float)$sr['total_claim_in_fy'];
                  $gtRecords += (int)$sr['record_count'];
                  $gtManage += (float)$sr['total_manage_ssj_in_fy'];
                  $gtPP += (float)$sr['total_pp_in_fy'];
              ?>
              <tr>
                <td style="text-align:center;color:#888;"><?= $si + 1 ?></td>
                <td style="white-space:nowrap;"><?= Html::encode($sr['FundHolderID']) ?></td>
                <td><?= Html::encode($sr['hosname'] ?? '-') ?></td>
                <td style="text-align:center;font-weight:700;color:#1a6b3c;"><?= Html::encode($sr['FiscalYear']) ?></td>
                <td style="text-align:right;"><?= number_format((int)$sr['record_count']) ?></td>
                <td style="text-align:right;"><?= number_format((int)$sr['total_days_in_fy']) ?></td>
                <td style="text-align:right;"><?= number_format((float)$sr['total_manage_ssj_in_fy'], 2) ?></td>
                <td style="text-align:right;"><?= number_format((float)$sr['total_pp_in_fy'], 2) ?></td>
                <td style="text-align:right;font-weight:700;background:#fff8e1;color:#c2185b;"><?= number_format((float)$sr['total_claim_in_fy'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background:#fff9c4;font-weight:700;">
                <td colspan="4" style="text-align:right;">รวมทั้งหมด</td>
                <td style="text-align:right;"><?= number_format($gtRecords) ?></td>
                <td></td>
                <td style="text-align:right;"><?= number_format($gtManage, 2) ?></td>
                <td style="text-align:right;"><?= number_format($gtPP, 2) ?></td>
                <td style="text-align:right;color:#c2185b;background:#fff8e1;"><?= number_format($gtClaimFY, 2) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Data table ─────────────────────────────────── -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-striped" style="font-size:0.95rem;">
        <thead>
          <tr style="background:#37474f;color:#fff;">
            <th style="white-space:nowrap;">#</th>
            <th style="white-space:nowrap;">รหัส รพ.</th>
            <th>ชื่อ รพ.</th>
            <th style="white-space:nowrap;">CID</th>
            <th style="white-space:nowrap;">วันเริ่มงวด</th>
            <th style="white-space:nowrap;">วันสิ้นงวด</th>
            <th style="white-space:nowrap;">งวด</th>
            <th style="white-space:nowrap;text-align:center;background:#1a6b3c;">ปีงบ</th>
            <th style="white-space:nowrap;text-align:right;">วันในงวด</th>
            <th style="white-space:nowrap;text-align:right;">วันในปีงบ</th>
            <th style="white-space:nowrap;text-align:right;">ราคาบัตร</th>
            <th style="white-space:nowrap;text-align:right;">Claim</th>
            <th style="white-space:nowrap;text-align:right;background:#880e4f;color:#fff;">Claim ในปีงบ</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $models = $dataProvider->getModels();
          $pagination = $dataProvider->getPagination();
          $offset = $pagination ? $pagination->getOffset() : 0;
          if (empty($models)): ?>
            <tr><td colspan="13" class="text-center text-muted" style="padding:24px;">ไม่มีข้อมูล — กด "นำเข้าข้อมูล" เพื่อเริ่มต้น</td></tr>
          <?php else: ?>
            <?php foreach ($models as $i => $row): ?>
              <tr>
                <td style="text-align:center;color:#888;"><?= $offset + $i + 1 ?></td>
                <td style="white-space:nowrap;"><?= Html::encode($row['FundHolderID']) ?></td>
                <td><?= Html::encode($row['hosname'] ?? '-') ?></td>
                <td style="white-space:nowrap;font-family:monospace;"><?= Html::encode($row['CID']) ?></td>
                <td style="white-space:nowrap;"><?= Html::encode($row['PeriodStartDate']) ?></td>
                <td style="white-space:nowrap;"><?= Html::encode($row['PeriodEndDate']) ?></td>
                <td style="white-space:nowrap;"><?= Html::encode($row['timeregis']) ?></td>
                <td style="text-align:center;font-weight:700;color:#1a6b3c;"><?= Html::encode($row['FiscalYear']) ?></td>
                <td style="text-align:right;"><?= number_format((int)$row['DaysBetween']) ?></td>
                <td style="text-align:right;"><?= number_format((int)$row['Days_in_FY']) ?></td>
                <td style="text-align:right;"><?= $row['card_price'] !== null ? number_format((float)$row['card_price'], 2) : '-' ?></td>
                <td style="text-align:right;"><?= $row['claim'] !== null ? number_format((float)$row['claim'], 2) : '-' ?></td>
                <td style="text-align:right;font-weight:700;background:#fff8e1;color:#c2185b;">
                  <?= $row['claim_in_FY'] !== null ? number_format((float)$row['claim_in_FY'], 2) : '-' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ── Pagination ─────────────────────────────────── -->
    <?php if ($dataProvider->getPagination()): ?>
      <div style="text-align:center;margin-top:10px;">
        <?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
      </div>
    <?php endif; ?>

  </div><!-- /.box-body -->
</div><!-- /.box -->
</div><!-- /.col -->
</div><!-- /.row -->
