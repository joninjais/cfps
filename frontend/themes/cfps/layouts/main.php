<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

if (in_array(Yii::$app->controller->action->id, ['login', 'change-password'])) {
    echo $this->render('main-login', ['content' => $content]);
} else {

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    // Register custom CFPS theme CSS (overrides AdminLTE visuals)
    $this->registerCssFile('@web/css/cfps-theme.css', [
        'depends' => ['dmstr\web\AdminLteAsset'],
        'position' => \yii\web\View::POS_END,
    ]);
    // เพิ่ม Font Awesome 6
    $this->registerCssFile('@web/css/fontawesome-all.min.css', [
        'depends' => ['dmstr\web\AdminLteAsset'],
        'position' => \yii\web\View::POS_END,
    ]);
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-green layout-top-nav">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render('header.php', ['directoryAsset' => $directoryAsset]) ?>

        <?= $this->render('content.php', ['content' => $content, 'directoryAsset' => $directoryAsset]) ?>

    </div>
    <?php $this->endBody() ?>

    <?php
    // ── Cookie Consent Banner ──────────────────────────────────────────────────
    // Show only when the visitor has not yet made a choice.
    $cookieConsent = isset($_COOKIE['cookie_consent']) ? $_COOKIE['cookie_consent'] : '';
    if ($cookieConsent === '') :
    ?>
    <!-- Cookie Consent Banner -->
    <div id="cookie-consent-banner" style="
        position:fixed;bottom:0;left:0;right:0;z-index:9999;
        background:#1a1a2e;color:#fff;padding:16px 24px;
        display:flex;align-items:center;justify-content:space-between;
        flex-wrap:wrap;gap:12px;box-shadow:0 -2px 8px rgba(0,0,0,.4);
        font-size:14px;line-height:1.5;">
        <div style="flex:1;min-width:200px;">
            <strong><i class="fa fa-cookie" style="margin-right:6px;"></i>นโยบายคุกกี้</strong>
            &nbsp;—&nbsp;
            เว็บไซต์นี้ใช้คุกกี้เพื่อวิเคราะห์การใช้งานและปรับปรุงประสบการณ์ของคุณ
            คุกกี้ที่จำเป็นจะทำงานเสมอ ส่วนคุกกี้ติดตาม (Tracking) จะทำงานก็ต่อเมื่อคุณยอมรับเท่านั้น
        </div>
        <div style="display:flex;gap:10px;flex-shrink:0;">
            <button id="cookie-reject-btn"
                onclick="setCookieConsent('rejected')"
                style="cursor:pointer;padding:8px 18px;border:1px solid #aaa;
                       border-radius:4px;background:transparent;color:#fff;font-size:14px;">
                ปฏิเสธ
            </button>
            <button id="cookie-accept-btn"
                onclick="setCookieConsent('accepted')"
                style="cursor:pointer;padding:8px 18px;border:none;border-radius:4px;
                       background:#27ae60;color:#fff;font-weight:bold;font-size:14px;">
                ยอมรับทั้งหมด
            </button>
        </div>
    </div>
    <script>
    function setCookieConsent(choice) {
        var maxAge = 365 * 24 * 60 * 60; // 1 year
        document.cookie = 'cookie_consent=' + choice
            + ';path=/;max-age=' + maxAge + ';SameSite=Lax';
        document.getElementById('cookie-consent-banner').style.display = 'none';
        if (choice === 'accepted') {
            loadTrackingScripts();
        }
    }
    // Placeholder — add your real tracking script calls here.
    function loadTrackingScripts() {
        // e.g. Google Analytics, Facebook Pixel, etc.
        // Example (uncomment and replace GA_MEASUREMENT_ID):
        // var s = document.createElement('script');
        // s.src = 'https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID';
        // s.async = true;
        // document.head.appendChild(s);
    }
    </script>
    <?php endif; ?>

    <?php if ($cookieConsent === 'accepted') : ?>
    <script>
    // Tracking scripts run only after explicit consent.
    // loadTrackingScripts() — add your GA/Pixel initialization here.
    </script>
    <?php endif; ?>

    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
