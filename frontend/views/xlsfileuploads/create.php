<?php

use yii\helpers\Html;

$data = Yii::$app->request->get();
$typefile = isset($data['typefile']) ? $data['typefile'] : '';

/* @var $this yii\web\View */
/* @var $model frontend\models\Xlsfileuploads */
if($typefile=='fwfhealth'){
    $st1="บัตรประกันสุขภาพ";
}else{
    $st1=$typefile;
}

$this->title = 'Step 1 : เลือกไฟล์ Upload ข้อมูล '.$typefile;
$this->params['breadcrumbs'][] = ['label' => 'Upload file ข้อมูล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="xlsfileuploads-create">
    <div class="container">
        <div class="row mt-20" style="margin-top: 40px;">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-body">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php
        if($typefile=='insurance'){
            echo "        <p>การนำเข้าข้อมูลข้อมูลกรมธรรม์ (FDH) ให้ Downlaod จาก 
        <a href=\"https://fdh.moph.go.th/fwf/login\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"fa fa-fw fa-external-link\"></i> https://fdh.moph.go.th/fwf/login</a>    
        ไปที่ <span>กรมธรรม์ > กรมธรรม์ทั้งหมด > ส่งออกข้อมูล</span>  <br>แล้วนำไฟล์ที่ได้มา Upload ในระบบนี้โดยไม่มีการแก้ไขข้อมูลในไฟล์</p>";
        }
        ?>

        <?= $this->render('_form', [
            'model' => $model,
            'typefile' => $typefile,
            'st1' => $st1,
        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
