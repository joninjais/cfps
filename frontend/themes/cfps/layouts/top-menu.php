<?php
use yii\helpers\Html;
use yii\helpers\Url;

$hoscode = Yii::$app->session['hoscode'];
$isSsj   = Yii::$app->facilityAccess->isProvincialAdmin();

$menus = [
    [
        'label' => 'นำเข้าข้อมูล',
        'icon'  => 'fa-cloud-upload',
        'items' => array_merge(
            $isSsj ? [
                ['label' => 'อัปโหลดไฟล์ FWF', 'desc' => 'นำเข้าข้อมูล FWF', 'icon' => 'fa-upload', 'url' => Url::to(['/xlsfileuploads/create', 'typefile' => 'fwfdata'])],
            ] : [
                ['label' => 'อัปโหลดไฟล์ OP PP', 'desc' => 'ข้อมูลผู้ป่วยนอกและส่งเสริมสุขภาพ', 'icon' => 'fa-upload', 'url' => Url::to(['/xlsfileuploads/create', 'typefile' => 'op pp'])],
                ['label' => 'อัปโหลดไฟล์ IP', 'desc' => 'ข้อมูลผู้ป่วยใน', 'icon' => 'fa-upload', 'url' => Url::to(['/xlsfileuploads/create', 'typefile' => 'ip'])],
                ['label' => 'อัปโหลดข้อมูลกรมธรรม์ (FDH)', 'desc' => 'นำเข้าข้อมูลกรมธรรม์', 'icon' => 'fa-upload', 'url' => Url::to(['/xlsfileuploads/create', 'typefile' => 'insurance'])],
            ],
            [['label' => 'ประวัตินำเข้าข้อมูล', 'desc' => 'ตรวจสอบรายการไฟล์ที่เคยนำเข้า', 'icon' => 'fa-history', 'url' => Url::to(['/xlsfileuploads/index'])]]
        ),
    ],
    [
        'label' => 'ตรวจสอบสิทธิ',
        'icon'  => 'fa-check-circle',
        'items' => [
            ['label' => 'ตรวจสอบสิทธิกองทุนประกันสุขภาพคนต่างด้าว', 'desc' => 'ระบบตรวจสอบสิทธิ FWF', 'icon' => 'fa-external-link', 'url' => 'https://fwf.moph.go.th/PublicCIDCheck', 'external' => true],
            ['label' => 'ตรวจสอบสิทธิผ่านระบบ FDH', 'desc' => 'ระบบกระทรวงสาธารณสุข', 'icon' => 'fa-external-link', 'url' => 'https://fdh.moph.go.th/fwf/eligibility-check', 'external' => true],
        ],
    ],
    [
        'label' => 'รายงานและตรวจสอบข้อมูล',
        'icon'  => 'fa-bar-chart',
        'items' => array_merge(
            [
                ['label' => 'Dashboard', 'desc' => 'ภาพรวมข้อมูลและผลการดำเนินงาน', 'icon' => 'fa-tachometer', 'url' => Url::to(['/site/dashboard'])],
                ['label' => 'ตรวจสอบข้อมูล Fwfdatabase', 'desc' => 'ค้นหาข้อมูลในฐาน FWF', 'icon' => 'fa-database', 'url' => Url::to(['/fwfdatabase/index'])],
            ],
            $isSsj ? [
                ['label' => 'ตรวจสอบข้อมูลกรมธรรม์', 'desc' => 'ข้อมูลประกันสุขภาพ', 'icon' => 'fa-file-text-o', 'url' => Url::to(['/insurancepolicies/index'])],
                ['label' => 'รายงานสรุปการขายบัตรต่างด้าว', 'desc' => 'รายงานสรุปการขายบัตรต่างด้าว', 'icon' => 'fa-file-text-o', 'url' => Url::to(['/insurancepolicies/report'])],
                ['label' => 'บันทึกค่ารักษาส่งต่อ (นค.1)', 'desc' => 'รายงานข้อมูลการส่งต่อ', 'icon' => 'fa-exchange', 'url' => Url::to(['/refer-claim/index'])],
                ] : [
                    ['label' => 'ตรวจสอบข้อมูล OP PP', 'desc' => 'ข้อมูลผู้ป่วยนอกและส่งเสริมสุขภาพ', 'icon' => 'fa-stethoscope', 'url' => Url::to(['/opdatainfo/index'])],
                    ['label' => 'ตรวจสอบข้อมูล IP', 'desc' => 'ข้อมูลผู้ป่วยใน', 'icon' => 'fa-bed', 'url' => Url::to(['/ipdatainfo/index'])],
                    ['label' => 'ตรวจสอบข้อมูลกรมธรรม์', 'desc' => 'ข้อมูลประกันสุขภาพ', 'icon' => 'fa-file-text-o', 'url' => Url::to(['/insurancepolicies/index'])],
                    ['label' => 'รายงานสรุปการขายบัตรต่างด้าว', 'desc' => 'รายงานสรุปการขายบัตรต่างด้าว', 'icon' => 'fa-file-text-o', 'url' => Url::to(['/insurancepolicies/report'])],
                ['label' => 'ลูกหนี้ตามจ่าย', 'desc' => 'ติดตามข้อมูลการเบิกจ่าย', 'icon' => 'fa-file-text-o', 'url' => Url::to(['/deptor/index'])],
            ],
            [['label' => 'กองทุน Claim ตามปีงบประมาณ', 'desc' => 'ดูรายงานแยกตามปีงบประมาณ', 'icon' => 'fa-calendar-o', 'url' => Url::to(['/fwf-fy-claim/index'])]]
        ),
    ],
    [
        'label' => 'คู่มือและเครื่องมือ',
        'icon'  => 'fa-book',
        'items' => [
            ['label' => 'คู่มือการใช้งาน', 'desc' => 'แนะนำขั้นตอนการใช้งานระบบ', 'icon' => 'fa-book', 'url' => Url::to('@web/manual.html'), 'external' => true],
            ['label' => $isSsj ? 'จัดการคำสั่ง SQL นำเข้าข้อมูล' : 'คำสั่ง SQL นำเข้าข้อมูล', 'desc' => 'ดูคำสั่งสำหรับนำเข้าข้อมูล', 'icon' => 'fa-code', 'url' => Url::to(['/sqlcode/index'])],
            ['label' => 'นโยบาย PDPA', 'desc' => 'แนวทางการคุ้มครองข้อมูลส่วนบุคคล', 'icon' => 'fa-shield', 'url' => Url::to('@web/pdpa.html'), 'external' => true],
        ],
    ],    
];
?>
<nav class="cfps-topmenu" aria-label="เมนูหลัก">
    <div class="cfps-topmenu__inner">
        <?= Html::a(
            '<i class="fa fa-home" aria-hidden="true"></i><span>หน้าแรก</span>',
            ['/site/index'],
            array_filter([
                'class' => 'cfps-nav-toggle',
                'aria-current' => Yii::$app->controller->route === 'site/index' ? 'page' : null,
            ])
        ) ?>
        <?php foreach ($menus as $menu): ?>
            <div class="dropdown cfps-nav-group">
                <button type="button" class="cfps-nav-toggle dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa <?= $menu['icon'] ?>" aria-hidden="true"></i>
                    <span><?= Html::encode($menu['label']) ?></span>
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu cfps-nav-menu">
                    <?php foreach ($menu['items'] as $item): ?>
                        <li>
                            <?= Html::a(
                                '<i class="fa ' . $item['icon'] . '" aria-hidden="true"></i>'
                                . '<span>' . Html::encode($item['label']) . '<small>' . Html::encode($item['desc']) . '</small></span>',
                                $item['url'],
                                !empty($item['external']) ? ['target' => '_blank', 'rel' => 'noopener noreferrer'] : []
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</nav>
