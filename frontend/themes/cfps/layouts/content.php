<?php
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
?>
<div class="content-wrapper">
    <section class="content-header">
        <?php if (isset($this->blocks['content-header'])): ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php endif; ?>
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </section>

    <section class="content">
        <?= Alert::widget() ?>
        <?= $content ?>
    </section>
</div>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>CF PS : Claim Foreigner work fund Phang-nga system</b>
    </div>
    <strong>CopyRight &copy; 2020
        <a href="#">Design By IT Team Phang-nga | Support By : กลุ่มงานประกันสุขภาพ สำนักงานสาธารณสุขจังหวัดพังงา</a>
    </strong>
</footer>
