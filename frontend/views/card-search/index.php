<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var bool $isGuest */
/** @var string $rawCid */
/** @var string $cleanCid */
/** @var array $tokens */
/** @var bool $searched */
/** @var string $errorMsg */
/** @var array $rows */
/** @var array $labels */
/** @var array $summary */

$isGuest = $isGuest ?? (bool)Yii::$app->user->isGuest;
$rawCid = $rawCid ?? '';
$cleanCid = $cleanCid ?? '';
$tokens = $tokens ?? [];
$searched = $searched ?? false;
$errorMsg = $errorMsg ?? '';
$rows = $rows ?? [];
$labels = $labels ?? [];
$summary = $summary ?? [];

$this->title = 'ค้นหาบัตรประกันสุขภาพแรงงานต่างด้าว';
if (!$isGuest) {
    $this->params['breadcrumbs'][] = $this->title;
}

$cssPath = Yii::getAlias('@webroot/css/card-search.css');
$jsPath = Yii::getAlias('@webroot/js/card-search.js');
$cssVer = is_file($cssPath) ? filemtime($cssPath) : time();
$jsVer = is_file($jsPath) ? filemtime($jsPath) : time();

$this->registerCssFile("@web/css/card-search.css?v={$cssVer}");
$this->registerJsFile("@web/js/card-search.js?v={$jsVer}", [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$searchUrl = Url::to(['/card-search/index']);
$loginUrl = Url::to(['/site/login']);
?>

<div class="card-search-page <?= $isGuest ? 'is-guest-mode' : '' ?>">
    <?php if ($isGuest): ?>
        <div class="cfps-guest-topbar">
            <div class="cfps-guest-brand">
                <strong>CFPS จังหวัดพังงา</strong> — ระบบตรวจสอบสิทธิบัตรประกันสุขภาพแรงงานต่างด้าว
            </div>
            <a href="<?= Html::encode($loginUrl) ?>" class="btn btn-default btn-sm cfps-btn-back-login">
                <i class="fa fa-sign-in"></i> กลับหน้าเข้าสู่ระบบ
            </a>
        </div>
    <?php endif; ?>

    <div class="box box-primary cfps-search-box">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-id-card"></i> ตรวจสอบสิทธิและค้นหาบัตรประกันสุขภาพ (ค้นหาได้ทุกหน่วยบริการ)
            </h3>
        </div>
        <div class="box-body">
            <form id="card-search-form" action="<?= Html::encode($searchUrl) ?>" method="get" autocomplete="off">
                <div class="cfps-cid-form-row">
                    <div class="cfps-cid-input-wrap">
                        <label for="cid-input" class="cfps-cid-label">
                            รหัสประจำตัวผู้ประกันตน (เลข 13 หลัก / Passport / เลข F04... / เลข RA...)
                        </label>
                        <div class="cfps-input-group">
                            <input
                                type="text"
                                id="cid-input"
                                name="cid"
                                class="form-control input-lg cfps-cid-input"
                                value="<?= Html::encode($rawCid) ?>"
                                placeholder="เช่น 7829900025075 หรือ CC8332811 หรือ F048822043391 หรือ RA17659..."
                                maxlength="120"
                                inputmode="text"
                                autofocus
                            >
                            <button type="button" id="btn-clear-cid" class="btn btn-default btn-lg" title="ล้างข้อมูล">
                                <i class="fa fa-times"></i> ล้าง
                            </button>
                            <button type="submit" id="btn-submit-cid" class="btn btn-primary btn-lg cfps-btn-search">
                                <i class="fa fa-search"></i> ค้นหาสิทธิ
                            </button>
                        </div>
                        <small class="text-muted cfps-search-hint">
                            <i class="fa fa-info-circle"></i>
                            <?php if ($isGuest): ?>
                                กรอกรหัสประจำตัวให้ครบถ้วนเพื่อตรวจสอบสถานะความคุ้มครองจากฐานข้อมูลกลางจังหวัดพังงา
                            <?php else: ?>
                                รองรับการค้นหาทุกรหัสในคอลัมน์ <code>CID</code> และ <code>PeriodID</code> จากทุกโรงพยาบาลในจังหวัดพังงา
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="card-search-loading" class="cfps-loading-overlay" style="display: none;">
        <i class="fa fa-spinner fa-spin fa-2x"></i> กำลังตรวจสอบข้อมูลบัตรประกันสุขภาพ...
    </div>

    <div id="card-search-results">
        <?= $this->render('_results', [
            'isGuest' => $isGuest,
            'rawCid' => $rawCid,
            'cleanCid' => $cleanCid,
            'tokens' => $tokens,
            'searched' => $searched,
            'errorMsg' => $errorMsg,
            'rows' => $rows,
            'labels' => $labels,
            'summary' => $summary,
        ]) ?>
    </div>
</div>
