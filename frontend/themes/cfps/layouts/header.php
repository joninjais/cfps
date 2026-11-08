<?php
use yii\helpers\Html;
use yii\helpers\Url;
use frontend\models\User;

$session    = Yii::$app->session;
$checklogin = User::findOne(['user' => $session['user']]);
$webPath    = Yii::$app->request->baseUrl;
?>
<header class="main-header">
    <nav class="navbar navbar-static-top">
        <div class="container-fluid">
            <!-- Brand -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#cfps-navbar" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="<?= Url::to(['/site/index']) ?>" class="navbar-brand" style="display:flex;align-items:center;gap:10px;">
                    <img src="<?= Url::to('@web/img/logoMOPH.png') ?>" width="32" height="32" style="display:inline-block;vertical-align:middle;"/>
                    <span style="display:inline-block;vertical-align:middle;font-size:1.1rem;font-weight:600;white-space:nowrap;">CF PS : Claim Foreigner work fund Phang-nga system</span>
                </a>
            </div>

            <!-- Right side menu -->
            <div class="collapse navbar-collapse " id="cfps-navbar">
                <ul class="nav navbar-nav navbar-right">

                    <?php if (!$checklogin): ?>
                        <li>
                            <?= Html::a('<i class="fa fa-sign-in"></i> เข้าสู่ระบบ', ['/site/login'], ['class' => '']) ?>
                        </li>
                    <?php else: ?>
                        <?php if (isset($session['login_time'])):
                            $expireTs = $session['login_time'] + 14400;
                        ?>
                        <li id="session-expiry-timer" style="display:flex;align-items:center;gap:6px;padding:0 8px;height:50px;">
                            <span style="display:flex;align-items:center;gap:6px;background:#e0f2f1;border-radius:16px;padding:3px 14px 3px 10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);font-size:1.08em;line-height:1.7;height:34px;margin-top:8px;">
                                <i class="fa fa-clock-o" style="color:#009688;font-size:1.18em;"></i>
                                <span id="session-timer-label" style="font-weight:600;color:#00796b;letter-spacing:0.5px;"></span>
                            </span>
                            <script>
                            (function() {
                                var expireTs = <?= (int)$expireTs ?> * 1000;
                                function updateTimer() {
                                    var now = Date.now();
                                    var diff = Math.max(0, Math.floor((expireTs - now) / 1000));
                                    var h = Math.floor(diff / 3600);
                                    var m = Math.floor((diff % 3600) / 60);
                                    var s = diff % 60;
                                    var label = (h > 0 ? h + ':' : '') + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                                    var el = document.getElementById('session-timer-label');
                                    if (diff <= 0) {
                                        el.textContent = 'หมดอายุแล้ว';
                                        el.style.color = '#c62828';
                                        el.parentNode.style.background = '#ffebee';
                                        el.parentNode.style.color = '#c62828';
                                        el.parentNode.style.border = '1px solid #c62828';
                                    } else if (diff < 300) { // < 5 นาที
                                        el.textContent = 'หมดอายุใน ' + label;
                                        el.style.color = '#e65100';
                                        el.parentNode.style.background = '#fff3e0';
                                        el.parentNode.style.color = '#e65100';
                                        el.parentNode.style.border = '1px solid #e65100';
                                    } else {
                                        el.textContent = 'หมดอายุใน ' + label;
                                        el.style.color = '#00796b';
                                        el.parentNode.style.background = '#e0f2f1';
                                        el.parentNode.style.color = '#00796b';
                                        el.parentNode.style.border = '1px solid #b2dfdb';
                                    }
                                }
                                updateTimer();
                                setInterval(updateTimer, 1000);
                            })();
                            </script>
                        </li>
                        <?php endif; ?>

                        <?php if ($session['hoscode'] === '00064'): ?>
                            <li>
                                <?= Html::a('<i class="fa fa-users"></i> จัดการผู้ใช้', ['/user/index'], ['class' => '']) ?>
                            </li>
                        <?php endif; ?>

                        <li>
                            <a href="#" style="cursor:default;">
                                <i class="fa fa-hospital-o"></i>
                                <?= ' ' . htmlspecialchars($session['hoscode']) ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" style="cursor:default;">
                                <i class="fa fa-user-circle-o"></i>
                                <?= ' ' . htmlspecialchars($checklogin ? $checklogin->getDisplayName() : '') ?>
                            </a>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-user-circle-o"></i> ข้อมูลส่วนตัว', ['/site/profile'], ['class' => '']) ?>
                        </li>
                        <!-- <li>
                            <a href="/manual.html" target="_blank"><i class="fa fa-book"></i> คู่มือการใช้งาน</a>
                        </li> -->
                        <li>
                            <!-- <a href="/pdpa.html" target="_blank"><i class="fa fa-shield"></i> นโยบาย PDPA</a> -->
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-sign-out"></i> ออกจากระบบ', ['/logout'], ['class' => '', 'data' => ['method' => 'post']]) ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
@media (max-width: 991px) {
    #session-expiry-timer {
        padding: 0 0 8px 0 !important;
        height: auto !important;
        justify-content: flex-start;
    }
    #session-expiry-timer > span {
        margin-top: 0 !important;
        font-size: 1em !important;
        padding: 6px 10px 6px 8px !important;
    }
    .navbar-nav > li {
        float: none !important;
        display: block !important;
        text-align: left;
        width: 100%;
    }
    .navbar-nav {
        margin: 0;
        width: 100%;
    }
    .navbar-header {
        width: 100%;
    }
    .navbar-brand {
        width: 80%;
        min-width: 0;
        font-size: 0.98rem !important;
        white-space: normal !important;
    }
    .navbar-toggle {
        margin-right: 10px;
    }
}
@media (max-width: 600px) {
    .navbar-header .navbar-brand span {
        font-size: 0.92rem !important;
        white-space: normal !important;
    }
    #session-expiry-timer > span {
        font-size: 0.95em !important;
        padding: 7px 7px 7px 7px !important;
    }
    .navbar-brand img {
        width: 32px !important;
        height: 32px !important;
    }
}
</style>
<script>
// Auto-collapse menu after click (for mobile)
document.addEventListener('DOMContentLoaded', function() {
    var navLinks = document.querySelectorAll('.navbar-nav > li > a');
    var navbarCollapse = document.getElementById('cfps-navbar');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992 && navbarCollapse.classList.contains('in')) {
                $(navbarCollapse).collapse('hide');
            }
        });
    });
});
</script>
